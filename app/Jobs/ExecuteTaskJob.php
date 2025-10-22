<?php

namespace App\Jobs;

use App\Enums\ConfigKey;
use App\Exceptions\ShowMsgException;
use App\Libraries\Eip712\Eip712;
use App\Models\BlockChain\Account;
use App\Models\BlockChain\Assets;
use App\Models\BlockChain\ChainRpc;
use App\Models\BlockChain\GlobalConfig;
use App\Models\BlockChain\Task;
use App\Models\BlockChain\TaskTrans;
use App\Models\Order\AvaOrder;
use App\Models\Robot\Template;
use App\Services\Remote\AvaServices;
use App\Services\TransactionService;
use Illuminate\Bus\Queueable;
use Illuminate\Cache\Lock;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;
use Log;
use Web3\Contract;
use Web3\Eth;
use Web3\Providers\HttpProvider;
use Str;
use Web3p\EthereumUtil\Util;

class ExecuteTaskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $task_trans_id;

    /**
     * Create a new job instance.
     */
    public function __construct($task_trans_id)
    {
        $this->task_trans_id = $task_trans_id;
    }

    /**
     * Execute the job.
     * @throws \Exception
     */
    public function handle(): void
    {
        /**
         * @var $lock Lock
         */
        $lock = \Cache::lock('execute-task-' . $this->task_trans_id, 120);
        $lock->get(function () {
            $task_trans = TaskTrans::find($this->task_trans_id);
            if ($task_trans->status != TaskTrans::STATUS_WAIT) {
                echo 'not wait status';
                return;
            }
            if ($task_trans->trans_hash) {
                echo 'trans hash exists';
                return;
            }
            $url = (new ChainRpc())->getProviderUrl($task_trans->chain);
            $provider = new HttpProvider($url,20);
            $eth = new Eth($provider);
            $task = Task::find($task_trans->task_id);
            $services = TransactionService::instance();
            if ($task->min_gas_price > 0) {
                $gasPrice = $services->gasPrice($eth);
                $gasPrice = $gasPrice->toString();
                $current_price = bcdiv($gasPrice, bcpow(10, 9), 2);
                if ($current_price > $task->min_gas_price) {
                    var_dump("current_price > min_gas_price: $current_price > $task->min_gas_price");
                    $task_trans->execute_time += 10 * 60;
                    $task_trans->save();
                    return;
                }
            }
            $min_balance = GlobalConfig::getValue($task_trans['chain'], ConfigKey::MIN_GAS_BALANCE);
            !$min_balance && $min_balance = '0.001';
            $account = Account::find($task_trans['account_id']);
            if ($account->pending) {
                echo 'pending';
                return;
            }
            $args = $task_trans->args;
            if ($task_trans->call_data && $args && isset($args['token_id'])) {
                $token_id = $args['token_id'];
                $asset = Assets::where('token_id', $token_id)
                    ->where('account_id', $account->account_id)
                    ->first();
                if (!$asset && $asset->balance < $args['sale_amount']) {
                    $task_trans->error = 'account:' . $account->address . ' asset:' . $args['sale_token'] . ' Insufficient balance';
                    $task_trans->retry++;
                    $task_trans->execute_time = time() + 60;
                    if ($task_trans->retry > 10 && $task_trans->status == TaskTrans::STATUS_WAIT) {
                        $task_trans->status = TaskTrans::STATUS_FAILED;
                        $task = Task::find($task_trans->task_id);
                        $task->failed++;
                        $task->save();
                    }
                    $task_trans->save();
                    return;
                }
            }
//            if ($account->balance < $min_balance) {
//                $balance = $services->getBalance($eth, $account->address);
//                $account->balance = bcdiv($balance, bcpow(10, 18), 18);
//                if ($account->balance < $min_balance) {
//                    $task_trans->error = 'Insufficient balance';
//                    $task_trans->retry++;
//                    $task_trans->execute_time = time() + 60;
//                    if ($task_trans->retry > 10 && $task_trans->status == TaskTrans::STATUS_WAIT) {
//                        $task_trans->status = TaskTrans::STATUS_FAILED;
//                        $task = Task::find($task_trans->task_id);
//                        $task->failed++;
//                        $task->save();
//                    }
//                    $task_trans->save();
//                    return;
//                }
//            }
            $account->pending = Account::BOOL_YES;
            $account->task_trans_id = $this->task_trans_id;
            $account->save();
            Log::info("[task-trans:$task_trans->task_trans_id]][request start]");
            try {
                $task_trans->retry++;
                $rpc = (new ChainRpc())->getProviderUrl($task_trans->chain);
                $provider = new HttpProvider($rpc, 20);
                $_private = hex2bin(Crypt::decryptString($account['private_key']));
                $hex_data = $task_trans->call_data;
                if ($hex_data) {
                    if (!Str::startsWith($hex_data, '0x')) {
                        $hex_data = '0x' . bin2hex($hex_data);
                    }
                    $amount = bcmul($task_trans->amount, bcpow(10, 18));
                    $amount = '0x' . base_convert($amount, 10, 16);
                    $isMakerOrder = $task_trans->call_data && $task_trans->args && isset($task_trans->args['make_order']);
                    if ($isMakerOrder) {
                        $ret = $this->getCreateTime();
                        if (!$ret) {
                            throw new ShowMsgException('avasubscription 502');
                        }
                        $listTime = $ret['data'];
                        if (!$listTime) {
                            throw new ShowMsgException('avasubscription 502');
                        }
                    }
                    $txHash = $services->sendTransactionWithData($provider, $_private, $task_trans->to, $amount, $account->nonce, $hex_data);
                    if ($isMakerOrder) {
                        $task_trans->trans_hash = $txHash;
                        $task_trans->save();
                        $services->confirmTx($eth, $txHash);
                        $this->makeOrder($task_trans, $account, $listTime);
                    }
                } else {
                    $args = $task_trans['args'];
                    $template = Template::find($task_trans['template_id']);
                    $contract = new Contract($provider, $template['abi']);
                    $contract = $contract->at($template->contract);
                    $params = array_merge([$task_trans['method']], array_values($args));
                    $txHash = $services->sendTransaction($contract, $account->nonce, $_private, $params);
                }
                $trans_hash = $txHash;
                Log::info("[task-trans:$task_trans->task_trans_id}][trans_hash:$trans_hash]");
                $task_trans->trans_hash = $trans_hash;
                $task_trans->status = TaskTrans::STATUS_WAIT;
                $task_trans->nonce = $account->nonce;
                $task_trans->error = '';
                $task_trans->send_trans_time = time();
            } catch (ShowMsgException $err) {
                $task_trans->error = $err->getMessage();
                Log::warning("[task-trans:$task_trans->task_trans_id][response:]" . $err->getMessage());
            } finally {
                if ($task_trans->retry > 10 && $task_trans->status == TaskTrans::STATUS_WAIT) {
                    $task_trans->status = TaskTrans::STATUS_FAILED;
                    $task = Task::find($task_trans->task_id);
                    $task->failed++;
                    $task->save();
                }
                $account->pending = Account::BOOL_NO;
                $account->task_trans_id = 0;
                $account->save();
                $task_trans->save();
            }
        });
    }


    /**
     * @throws RandomException
     * @throws \Exception
     */
    public function makeOrder(TaskTrans $trans, Account $account, $listTime): void
    {
        $mailTypedJson = '{"types":{"EIP712Domain":[{"name":"name","type":"string"},{"name":"version","type":"string"},{"name":"chainId","type":"uint256"},{"name":"verifyingContract","type":"address"}],"ASC20Order":[{"name":"seller","type":"address"},{"name":"creator","type":"address"},{"name":"listId","type":"bytes32"},{"name":"ticker","type":"string"},{"name":"amount","type":"uint256"},{"name":"price","type":"uint256"},{"name":"nonce","type":"uint256"},{"name":"listingTime","type":"uint64"},{"name":"expirationTime","type":"uint64"},{"name":"creatorFeeRate","type":"uint16"},{"name":"salt","type":"uint32"},{"name":"extraParams","type":"bytes"}]},"primaryType":"ASC20Order","domain":{"chainId":43114,"name":"ASC20Market","verifyingContract":"0x24e24277e2ff8828d5d2e278764ca258c22bd497","version":"1.0"},"message":{}}';
        $mailTypedJson = json_decode($mailTypedJson, true);
        $pvk = hex2bin(Crypt::decryptString($account['private_key']));
        $args = $trans->args;
        $amt = $args['sale_amount'];
        $tick = $args['sale_token'];
        $price = $args['sale_price'];
        $expirationTime = $listTime + 86400 * 30;
        $salt = $this->random(9);
        $order = [
            "seller" => $account['address'],
            "creator" => "0x24e24277e2ff8828d5d2e278764ca258c22bd497",
            "listId" => $trans->trans_hash,
            "ticker" => $tick,
            "amount" => '0x' . base_convert($amt, 10, 16),
            "price" => '0x' . base_convert(bcmul($price, bcpow(10, 18)), 10, 16),
            "nonce" => "0",
            "listingTime" => $listTime,
            "expirationTime" => $expirationTime,
            "creatorFeeRate" => 200,
            "salt" => $salt,
            "extraParams" => "0x00"
        ];
        $mailTypedJson['message'] = $order;
        $eip712 = new Eip712($mailTypedJson);
        $hashToSign = $eip712->hashTypedDataV4();
        $util = new Util();
        $sign = $util->ecsign($pvk, $hashToSign);
        $ch = curl_init();
        $req = [
            "listId" => $trans->trans_hash,
            "input" => [
                "order" => $order,
                "v" => $sign->recoveryParam - 35 > 0 ? 28 : 27,
                "r" => '0x' . $sign->r->toString(16),
                "s" => '0x' . $sign->s->toString(16),
            ]
        ];
        $headers = $this->getHeaders();
        curl_setopt($ch, CURLOPT_URL, 'https://avascriptions.com/api/order/create');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($req));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        $_result = curl_exec($ch);
        curl_close($ch);
        var_dump($_result);
        $result = json_decode($_result, true);
        if ($result['status'] != 200) {
            Log::error('[makerError][transId:' . $trans->task_trans_id . '][hash:' . $trans->trans_hash . ']');
            return;
        }
        $order = array_merge($order, [
            "v" => $sign->recoveryParam - 35 > 0 ? 28 : 27,
            "r" => '0x' . $sign->r->toString(16),
            "s" => '0x' . $sign->s->toString(16),
        ]);
        (new AvaOrder())->forceFill([
            "seller" => $order['seller'],
            "creator" => $order['creator'],
            "list_id" => $order['listId'],
            "ticker" => $order['ticker'],
            "amount" => $order['amount'],
            "price" => $order['price'],
            "nonce" => $order['nonce'],
            "listing_time" => $order['listingTime'],
            "expiration_time" => $order['expirationTime'],
            "creator_fee_rate" => $order['creatorFeeRate'],
            "salt" => $order['salt'],
            "extra_params" => $order['extraParams'],
            "v" => $order['v'],
            "r" => $order['r'],
            "s" => $order['s'],
            'status' => AvaOrder::STATUS_CREATED,
            'total_price' => bcmul($price, $amt, 18),
        ])->save();
        $trans->logs = $order;
        $trans->save();
    }

    public function getHeaders(): array
    {
        return AvaServices::instance()->getHeaders();
    }

    public function getCreateTime()
    {
        $headers = $this->getHeaders();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://avascriptions.com/api/order/timestamp');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        $_result = curl_exec($ch);
        var_dump($_result);
        curl_close($ch);
        return json_decode($_result, true);
    }


    /**
     * @param $length
     * @return string
     * @throws RandomException
     */
    protected
    function random($length): string
    {
        $ret = '';
        for ($i = 0; $i < $length; $i++) {
            if ($i == 0) {
                $ret = random_int(1, 9);
            } else {
                $ret .= random_int(0, 9);
            }
        }
        return $ret;
    }

}
