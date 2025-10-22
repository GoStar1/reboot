<?php


namespace App\Jobs;


use App\Models\BlockChain\Account;
use App\Models\BlockChain\ChainRpc;
use App\Models\BlockChain\Task;
use App\Models\BlockChain\TaskDataAccount;
use App\Models\BlockChain\TaskTrans;
use App\Models\BlockChain\Token;
use App\Models\Order\AvaOrder;
use App\Models\Robot\Template;
use App\Services\Remote\AvaServices;
use App\Services\TransactionService;
use Arr;
use Illuminate\Bus\Queueable;
use Illuminate\Cache\Lock;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use Str;
use Web3\Contract;
use Web3\Eth;
use Web3\Providers\HttpProvider;
use Web3\Utils;

class TransStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $task_trans_id;

    public function __construct($task_trans_id)
    {
        $this->task_trans_id = $task_trans_id;
    }

    public function handle(): void
    {
        $cache_key = md5('TransStatusJob' . $this->task_trans_id);
        /**
         * @var $lock Lock
         */
        $lock = \Cache::lock($cache_key, 120);
        $lock->get(/**
         */ function () {
            $trans = TaskTrans::find($this->task_trans_id);
            if ($trans->status === TaskTrans::STATUS_SUCCESS) {
                echo 'success';
                return;
            }
            $rpc = (new ChainRpc())->getProviderUrl($trans->chain);
            $provider = new HttpProvider($rpc, 20);
            $eth = new Eth($provider);
            $services = TransactionService::instance();
            $receipt = $services->getTransactionReceipt($eth, $trans['trans_hash']);
            $data = [];
            if ($receipt) {
                $status = base_convert($receipt->status, 16, 10);
                if ($status == 0) {
                    $data['status'] = TaskTrans::STATUS_FAILED;
                } else {
                    $templates = Template::where('chain', $trans->chain)->get();
                    $signature_address_map = [];
                    foreach ($templates as $template) {
                        $contract = new Contract($provider, $template->abi);
                        $events = $contract->getEvents();
                        $ethAbi = $contract->getEthabi();
                        $signature_map = [];
                        foreach ($events as $event) {
                            $signature_map[$ethAbi->encodeEventSignature(Utils::jsonMethodToString($event))] = $event;
                        }
                        $signature_address_map[Utils::toChecksumAddress($template->contract)] = [$signature_map, $ethAbi];
                    }
                    $logs = [];
                    foreach ($receipt->logs as $log) {
                        $log_address = Utils::toChecksumAddress($log->address);
                        if (isset($signature_address_map[$log_address])) {
                            list($signature_map, $ethAbi) = $signature_address_map[$log_address];
                            $decode_log = $services->decodeEvent($log, $signature_map, $ethAbi);
                            if ($decode_log) {
                                $logs[] = $decode_log;
                            }
                        }
                    }
                    $data['logs'] = $logs;
                    $data['status'] = TaskTrans::STATUS_SUCCESS;
                    $this->saveDataToUser($trans, $logs);
                    if ($trans->template_id == 11 && $trans->method == 'executeOrder') {
                        $avaOrder = AvaOrder::where('list_id', $trans->args['list_id'])->first();
                        $avaOrder->status = AvaOrder::STATUS_TOOK;
                        $avaOrder->confirm_trans_hash = $trans->trans_hash;
                        $avaOrder->taker = $trans->from;
                        $avaOrder->save();
                        $avaServices = AvaServices::instance();
                        $token_id = Token::where('chain', $trans->chain)
                            ->where('name', $avaOrder->ticker)->value('token_id');
                        $_amount = base_convert($avaOrder->amount, 16, 10);
                        $avaServices->saveAssetOp($trans['account_id'], $token_id, true, $_amount);
                        $seller_account = Account::where('chain', $trans->chain)
                            ->where('address', $avaOrder->seller)
                            ->first();
                        $avaServices->saveAssetOp($seller_account->account_id, $token_id, false, $_amount);
                        $balance = TransactionService::instance()->getBalance($eth, $avaOrder->seller);
                        $seller_account->balance = bcdiv($balance, bcpow(10, 18), 18);
                        $seller_account->save();
                    }
                }
            } else {
                if (time() - $trans->send_trans_time > 600) {
                    $data['status'] = TaskTrans::STATUS_WAIT;
                }
            }
            $trans->forceFill($data);
            $trans->save();
            if (!isset($data['status'])) {
                echo 'return';
                return;
            }
            $task = Task::find($trans->task_id);
            if ($data['status'] == TaskTrans::STATUS_FAILED) {
                $task->failed++;
            } else {
                $task->completed++;
            }
            $task->save();
            $account = Account::find($trans['account_id']);
            $account->nonce++;
            $account->pending = Account::BOOL_NO;
            $account->task_trans_id = 0;
            try {
                $balance = TransactionService::instance()->getBalance($eth, $account->address);
                $account->balance = bcdiv($balance, bcpow(10, 18), 18);
            } catch (\Exception $e) {
                Log::error('ERROR:' . $e->getMessage() . ' ' . $e->getTraceAsString());
            }
            $account->save();
        });
    }

    protected function saveDataToUser(TaskTrans $trans, $logs): void
    {
        $task = Task::find($trans->task_id);
        if (!$task->save_data) {
            return;
        }
        $to = strtolower($trans->to);
        $newData = [];
        foreach ($task->save_data as $item) {
            if (Str::startsWith($item, 'log.')) {
                list($name, $key) = explode('.', substr($item, 4));
                foreach ($logs as $log) {
                    if (strtolower($log['contract_address']) === $to && $log['name'] === $name) {
                        $value = Arr::get($log, 'args.' . $key);
                        $newData[$item] = $value;
                    }
                }
            } else if (Str::startsWith($item, 'param.')) {
                $key = substr($item, 6);
                $newData[$item] = Arr::get($trans->args, $key);
            }
        }
        $task_account = TaskDataAccount::where('task_data_id', $trans->task_data_id)
            ->where('account_id', $trans->account_id)
            ->first();
        $task_account->data = array_merge($task_account->data, $newData);
        $task_account->save();
    }
}
