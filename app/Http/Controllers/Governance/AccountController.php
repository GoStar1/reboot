<?php

namespace App\Http\Controllers\Governance;

use App\Enums\Chain;
use App\Http\Controllers\Controller;
use App\Models\BlockChain\Account;
use App\Models\BlockChain\Assets;
use App\Models\BlockChain\ChainRpc;
use App\Models\BlockChain\TaskData;
use App\Models\BlockChain\Token;
use App\Services\Remote\AvaServices;
use App\Services\SystemUtils;
use App\Services\TransactionService;
use Arr;
use DB;
use Exception;
use FurqanSiddiqui\BIP39\BIP39;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Jundayw\Bip44\Bip44HierarchicalKey;
use Str;
use Web3\Eth;
use Web3\Providers\HttpProvider;

class AccountController extends Controller
{


    public static function getAccountModel($arr)
    {
        $chain = Chain::tryFrom(Arr::get($arr, 'chain'));
        $balance = Arr::get($arr, 'balance');
        $keyword = Arr::get($arr, 'keyword');
        $task_data_id = Arr::get($arr, 'task_data_id');
        $asset_type = Arr::get($arr, 'asset_type');
        $balance_type = Arr::get($arr, 'balance_type');
        $asset_min = Arr::get($arr, 'asset_min');
        $token_id = Arr::get($arr, 'token_id');
        $query = new Account;
        $invalid_keyword = false;
        if ($keyword) {
            if (Str::startsWith($keyword, '0x')) {
                $query = $query->where('address', $keyword);
            } else {
                if (preg_match('/^[0-9a-zA-Z +-]+$/', $keyword)) {
                    $query = $query->whereRaw('match (tags) against (\'' . $keyword . '\' in Boolean mode)');
                } else {
                    $invalid_keyword = true;
                }
            }
        }
        $task_data_id && $query = $query->whereIn('accounts.account_id', function ($sub) use ($task_data_id) {
            $sub->from('task_data_accounts')->where('task_data_id', $task_data_id)->select('account_id');
        });
        $chain && $query = $query->where('chain', $chain);
        if ($balance) {
            if ($balance_type == 1) {
                $query = $query->where('accounts.balance', '<=', $balance);
            } else {
                $query = $query->where('accounts.balance', '>=', $balance);
            }
        }
        if ($token_id) {
            $query->leftJoin(DB::raw('(select balance as asset_balance,account_id from assets where token_id=' . $token_id . ') a'), 'a.account_id', '=', 'accounts.account_id');
            if ($asset_min) {
                if ($asset_type == 1) {
                    $query = $query->where(function ($sub) use ($asset_min) {
                        $sub->where('asset_balance', '<=', $asset_min);
                        $sub->orWhereNull('asset_balance');
                    });
                } else {
                    $query = $query->where('asset_balance', '>=', $asset_min);
                }
            }
        }
        return [$query, $invalid_keyword];
    }

    public function index(Request $request)
    {
        $chain = Chain::tryFrom($request->input('chain'));
        $balance = $request->input('balance');
        $keyword = $request->input('keyword');
        $task_data_id = $request->input('task_data_id');
        $asset_min = $request->input('asset_min');
        $token_id = $request->input('token_id');
        $asset_type = $request->input('asset_type');
        $balance_type = $request->input('balance_type');
        list($query, $invalid_keyword) = self::getAccountModel($request->input());
        $list = $query->orderByDesc('accounts.account_id')->paginate()->toArray();
        foreach ($list['data'] as &$item) {
            $item['chain'] = Chain::from($item['chain']);
        }
        unset($item);
        $_task_data_dict = (new TaskData)
            ->get(['task_data_id', 'name', 'chain'])
            ->toArray();
        $task_data_dict = [];
        foreach ($_task_data_dict as $item) {
            $arr = Arr::get($task_data_dict, $item['chain'], []);
            $arr[] = [
                'key' => $item['name'],
                'value' => $item['task_data_id'],
            ];
            $task_data_dict[$item['chain']] = $arr;
        }
        $_token_dict = (new Token)
            ->get(['token_id', 'chain', 'name'])
            ->toArray();
        $token_dict = [];
        $token_name = '';
        foreach ($_token_dict as $item) {
            $arr = Arr::get($token_dict, $item['chain'], []);
            if ($token_id == $item['token_id']) {
                $token_name = $item['name'];
            }
            $arr[] = [
                'key' => $item['name'],
                'value' => $item['token_id'],
            ];
            $token_dict[$item['chain']] = $arr;
        }
        return view('governance.account.index', [
            'active_menu' => 'governance.account',
            'title' => 'Account',
            'list' => $list,
            'keyword' => $keyword,
            'balance' => $balance,
            'asset_min' => $asset_min,
            'chain' => $chain,
            'bool_dict' => Account::$bool_dict,
            'token_name' => $token_name,
            'token_dict' => $token_dict,
            'invalid_keyword' => $invalid_keyword,
            'task_data_id' => $task_data_id,
            'task_data_dict' => $task_data_dict,
            'token_id' => $token_id,
            'balance_type' => $balance_type,
            'asset_type' => $asset_type,
        ]);
    }

    public function add(Request $request)
    {
        $chainArr = $request->input('chain');
        $count = (int)$request->input('count');
        $tags = $request->input('tags');
        if ($count < 0) {
            return $this->error(1, "account count error");
        }
        if (!$chainArr) {
            return $this->error(1, "chain error");
        }
        $now = gmdate('Y-m-d H:i:s');
        try {
            $HDKey = Bip44HierarchicalKey::fromEntropy(bin2hex(BIP39::Generate(15)->generateSeed()))->derive("44'/60'/0'/0");
            $data = [];
            foreach ($chainArr as $chain) {
                $chain = Chain::tryFrom($chain);
                for ($i = 0; $i < $count; $i++) {
                    $hdChild = $HDKey->deriveChild($i);
                    $privateKey = Crypt::encryptString(bin2hex($hdChild->getPrivateKey()));
                    $address = SystemUtils::privateToAddress($hdChild->getPrivateKey(), $chain);
                    $data[] = [
                        'address' => $address,
                        'chain' => $chain,
                        'private_key' => $privateKey,
                        'tags' => $tags,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
            foreach (array_chunk($data, 50) as $arr) {
                Account::insert($arr);
            }
            return $this->success();
        } catch (Exception $e) {
            return $this->error(1, $e->getMessage());
        }
    }

    public function updateEth(Request $request)
    {
        $chain = Chain::tryFrom($request->input('chain'));
        if (!$chain) {
            return $this->error(1, 'select chain first');
        }
        $url = (new ChainRpc())->getProviderUrl($chain);
        if (!$url) {
            return $this->error(1, 'add url first');
        }
        list($query) = self::getAccountModel($request->input());
        $accounts = $query->orderByDesc('accounts.account_id')->get();
        try {
            foreach ($accounts as $account) {
                $provider = new HttpProvider($url,20);
                $eth = new Eth($provider);
                $balance = TransactionService::instance()->getBalance($eth, $account->address);
                $account->balance = bcdiv($balance, bcpow(10, 18), 18);
                $account->save();
                sleep(1);
            }
            return $this->success();
        } catch (Exception $e) {
            return $this->error(1, $e->getMessage());
        }
    }

    public function updateERC20(Request $request)
    {
        $chain = Chain::tryFrom($request->input('chain'));
        $token_id = $request->input('token_id');
        $max = ini_get('max_execution_time');
        try {
            ini_set('max_execution_time', 60 * 20);
            if (!$chain) {
                return $this->error(1, 'Select chain first');
            }
            if (!$token_id) {
                return $this->error(1, 'Select asset first');
            }
            $abi = json_decode(file_get_contents(database_path('abis/IERC20.json')));
            $url = (new ChainRpc())->getProviderUrl($chain);
            if (!$url) {
                return $this->error(1, 'add url first');
            }
            list($query) = self::getAccountModel($request->input());
            $accounts = $query->orderByDesc('account_id')->get(['address', 'accounts.account_id']);
            $token = Token::find($token_id);
            if (!$token) {
                return $this->error(1, 'Select asset first');
            }
            $avaServices = AvaServices::instance();
            if ($token->name == 'PCLUB') {
                foreach ($accounts as $account) {
                    $balance = $avaServices->subscriptionTokens($token->name, $account->address);
                    sleep(random_int(1, 5));
                    $avaServices->saveAsset($account['account_id'], $token_id, $balance);
                }
                return $this->success();
            }
            $transactionService = new TransactionService();
            foreach ($accounts as $account) {
                list($balance) = $transactionService->getResult($chain, $abi, $token['contract'], [
                    'balanceOf', $account['address'],
                ]);
                $balance = bcdiv($balance, bcpow(10, $token['decimals']), 18);
                if (bccomp($balance, str_repeat('9', 22)) === 1) {
                    $balance = str_repeat('9', 22);
                }
                $avaServices->saveAsset($account['account_id'], $token_id, $balance);
                sleep(1);
            }
            return $this->success();
        } catch (Exception $e) {
            return $this->error(1, $e->getMessage());
        } finally {
            ini_set('max_execution_time', $max);
        }
    }


    public function edit(Request $request)
    {
        $id = $request->input('id');
        $data = (new Account)->where('account_id', $id)->first();
        return view('governance.account.edit', [
            'active_menu' => 'governance.account',
            'title' => 'edit account',
            'data' => $data,
            'id' => $id,
            'bool_dict' => Account::$bool_dict,
        ]);
    }

    public function export(Request $request)
    {
        $memory_limit = ini_get('memory_limit');
        ini_set('memory_limit', '1G');
        list($query) = self::getAccountModel($request->input());
        $fields = [
            'accounts.account_id', 'chain', 'address', 'tags', 'accounts.balance', 'pending', 'task_trans_id', 'updated_at'
        ];
        $show_fields = ['ID', 'chain', 'address', 'tags', 'balance', 'pending', 'task_trans_id', 'updated_at'];
        $list = $query->orderByDesc('account_id')->get($fields)->toArray();
        $filename = 'account.csv';
        $f = fopen('php://memory', 'w');
        $delimiter = ",";
        // Set column headers
        fputcsv($f, $show_fields, $delimiter);
        // Output each row of the data, format line as csv and write to file pointer
        foreach ($list as $row) {
            $row['chain'] = Chain::from($row['chain'])->name;
            fputcsv($f, $row, $delimiter);
        }
        // Move back to beginning of file
        fseek($f, 0);
        // Set headers to download file rather than displayed
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        //output all remaining data on a file pointer
        fpassthru($f);
        ini_set('memory_limit', $memory_limit);
    }

    public function updateData(Request $request)
    {
        $id = $request->input('id');
        $pending = $request->input('pending');
        $tags = $request->input('tags');
        $data = [
            'pending' => $pending,
            'tags' => $tags,
        ];
        $ret = Account::where('account_id', $id)
            ->update($data);
        return $ret ? $this->success() : $this->error(1, 'fail');
    }
}
