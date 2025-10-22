<?php

namespace App\Http\Controllers\Governance;

use App\Enums\Chain;
use App\Exceptions\ShowMsgException;
use App\Http\Controllers\Controller;
use App\Models\BlockChain\Account;
use App\Models\BlockChain\Assets;
use App\Models\BlockChain\ChainRpc;
use App\Models\BlockChain\Task;
use App\Models\BlockChain\TaskData;
use App\Models\BlockChain\TaskDataAccount;
use App\Models\BlockChain\TaskTrans;
use App\Models\BlockChain\Token;
use App\Models\Order\AvaOrder;
use App\Models\Robot\Template;
use App\Services\SystemUtils;
use Arr;
use DB;
use Exception;
use Illuminate\Http\Request;
use Log;
use Str;
use Web3\Contracts\Ethabi;
use Web3\Contracts\Types\Address;
use Web3\Contracts\Types\Bytes;
use Web3\Contracts\Types\DynamicBytes;
use Web3\Contracts\Types\Uinteger;
use Web3\Utils;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $chain = Chain::tryFrom($request->input('chain'));
        $keyword = $request->input('keyword');
        $status = $request->input('status');
        $template_id = $request->input('template_id');
        $task_data_id = $request->input('task_data_id');
        $query = \DB::table('tasks')
            ->leftJoin('templates', 'templates.template_id', '=', 'tasks.template_id')
            ->leftJoin('task_data', 'task_data.task_data_id', '=', 'tasks.task_data_id');
        $template_id && $query = $query->where('tasks.template_id', $template_id);
        $task_data_id && $query = $query->where('tasks.task_data_id', $task_data_id);
        !SystemUtils::isNull($status) && $query = $query->where('status', $status);
        $keyword && $query = $query->where(function ($query) use ($keyword) {
            is_numeric($keyword) && $query->orWhere('task_id', $keyword);
        });
        $fields = [
            'task_id',
            'task_data.chain',
            \DB::raw('templates.name template_name'),
            \DB::raw('task_data.name task_name'),
            'method',
            'args',
            'start_time',
            'time_range',
            'failed',
            'completed',
            'accounts'
        ];
        $chain && $query = $query->where('task_data.chain', $chain);
        $list = $query->orderByDesc('task_id')->paginate(15, $fields)->toArray();
        $template_dict = (new Template)
            ->get(['name', 'template_id'])
            ->pluck('name', 'template_id');
        $task_data_dict = (new TaskData)
            ->get(['task_data_id', 'name'])
            ->pluck('name', 'task_data_id');
        foreach ($list['data'] as &$item) {
            $item['chain'] = Chain::from($item['chain']);
            $item['waiting'] = TaskTrans::where('task_id', $item['task_id'])
                ->where('status', TaskTrans::STATUS_WAIT)->count();
        }
        unset($item);
        return view('governance.task.index', [
            'active_menu' => 'governance.task',
            'title' => 'Task',
            'list' => $list,
            'keyword' => $keyword,
            'template_dict' => $template_dict,
            'task_data_dict' => $task_data_dict,
            'task_data_id' => $task_data_id,
            'template_id' => $template_id,
            'status' => $status,
            'chain' => $chain,
        ]);
    }

    public function detail(Request $request)
    {
        $task_id = (int)$request->input('task_id');
        $query = \DB::table('tasks')
            ->leftJoin('templates', 'templates.template_id', '=', 'tasks.template_id')
            ->leftJoin('task_data', 'task_data.task_data_id', '=', 'tasks.task_data_id');
        $fields = [
            'task_id',
            'task_data.chain',
            \DB::raw('templates.name template_name'),
            \DB::raw('task_data.name task_name'),
            'method',
            'args',
            'start_time',
            'time_range',
            'failed',
            'completed',
            'accounts',
            'save_data',
            'call_data',
            'min_gas_price',
        ];
        $task = $query->where('tasks.task_id', $task_id)->first($fields);
        return view('governance.task.detail', [
            'item' => $task,
        ]);
    }

    public function edit(Request $request)
    {
        $task_id = (int)$request->input('task_id');
        $task = Task::find($task_id);
        return view('governance.task.edit', [
            'item' => $task,
            'active_menu' => 'governance.task',
            'title' => 'Task',
        ]);
    }

    public function save(Request $request)
    {
        $task_id = (int)$request->input('task_id');
        $min_gas_price = $request->input('min_gas_price');
        $ret = Task::getQuery()
            ->where('task_id', $task_id)
            ->update([
                'min_gas_price' => $min_gas_price,
            ]);
        return $ret ? $this->success() : $this->error(1, 'failed');
    }

    public function getTemplateData($_chain)
    {
        $templates = (new Template)
            ->where('chain', $_chain)
            ->get(['template_id', 'name', 'abi']);
        $template_dict = $templates
            ->pluck('name', 'template_id');
        $method_data = [];
        foreach ($templates as $template) {
            $abi = $template['abi'];
            foreach ($abi as $func) {
                $arr = Arr::get($method_data, $template['template_id']);
                if ($func['type'] === 'function' && !Str::startsWith($func['name'], '_') && $func['name'] !== 'initialize') {
                    if (!in_array($func['stateMutability'], ['view', 'pure'], true)) {
                        $arr[] = $func['name'];
                    }
                }
                $method_data[$template['template_id']] = $arr;
            }
        }
        return [$template_dict, $method_data];
    }

    public function cancelTrans(Request $request)
    {
        $task_id = $request->input('task_id');
        $ret = TaskTrans::where('status', TaskTrans::STATUS_WAIT)
            ->where('task_id', $task_id)
            ->update([
                'status' => TaskTrans::STATUS_CANCELED,
            ]);
        return $ret ? $this->success() : $this->error(1, 'failed');
    }

    public function createView(Request $request)
    {
        $task_data_id = $request->input('task_data_id');
        if (!$task_data_id) {
            return response()->redirectTo(route('task.index'));
        }
        $taskData = TaskData::find($task_data_id);
        list($template_dict, $method_data) = $this->getTemplateData($taskData['chain']);
        return view('governance.task.create', [
            'active_menu' => 'governance.task',
            'title' => 'Create Task',
            'chain' => $taskData['chain'],
            'template_dict' => $template_dict,
            'method_data' => $method_data,
            'task_data_id' => $task_data_id,
            'task_data_name' => $taskData['name'],
        ]);
    }

    public function createData(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $templates = $request->input('template');
                $task_data_id = $request->input('task_data_id');
                $call_data = $request->input('call_data');
                $make_order = $request->input('make_order');
                $take_order = $request->input('take_order');
                $this->createTask($request);
                $task_data = TaskData::find($task_data_id);
                if ($call_data || $make_order || $take_order) {
                    $task_data->tasks += 1;
                } else {
                    $task_data->tasks += count($templates);
                }
                $task_data->save();
                return $this->success();
            });
        } catch (ShowMsgException $e) {
            return $this->error(1, $e->getMessage());
        } catch (Exception $e) {
            Log::error('[createData][msg:' . $e->getMessage() . ']' . $e->getTraceAsString());
            return $this->error(10000, 'internal error');
        }
    }


    /**
     * @param Request $request
     * @return void
     * @throws ShowMsgException
     */
    public function createTask(Request $request)
    {
        $chain = Chain::tryFrom($request->input('chain'));
        $templates = $request->input('template');
        $methods = $request->input('method');
        $params = $request->input('params');
        $saveData = $request->input('saveData');
        $start_time = $request->input('startTime');
        $in_range_type = $request->input('inRangeType');
        $time_range = $request->input('inRange');
        $amount = $request->input('amount');
        $call_data = $request->input('call_data');
        $min_gas_price = (float)$request->input('min_gas_price');
        $task_data_id = $request->input('task_data_id');
        $make_order = $request->input('make_order');
        $take_order = $request->input('take_order');
        if (!preg_match('/^[0-9.]*$/', $amount)) {
            throw new ShowMsgException("amount is wrong", 1);
        }
        if (!$chain) {
            throw new ShowMsgException("chain must selected", 1);
        }
        $url = (new ChainRpc)->getProviderUrl($chain);
        if (!$url) {
            throw new ShowMsgException("add RPC Node first", 1);
        }
        if (!$amount) {
            $amount = 0;
        }
        if ($start_time) {
            $start_time = strtotime($start_time);
            if ($start_time < time() - 10 * 60) {
                throw new ShowMsgException("Start time is less than current time", 1);
            }
        } else {
            $start_time = time();
        }
        if ($time_range) {
            if ($in_range_type === 'Days') {
                $time_range = bcmul($time_range, 86400);
            } else if ($in_range_type === 'Hours') {
                $time_range = bcmul($time_range, 3600);
            } else if ($in_range_type === 'Minutes') {
                $time_range = bcmul($time_range, 60);
            }
        } else {
            $time_range = 0;
        }
        $now = date('Y-m-d H:i:s');
        $account_list = (new TaskDataAccount)
            ->where('task_data_id', $task_data_id)->get(['account_id', 'address', 'data']);
        if ($make_order && $take_order) {
            throw new ShowMsgException("make and take cannot exist at the same time", 1);
        }
        if ($call_data || $make_order) {
            $this->callDataProcess($request, $chain, $amount, $start_time, $time_range, $min_gas_price, $account_list);
            return;
        } else if ($take_order) {
            $this->takeOrderProcess($request, $chain, $start_time, $time_range, $min_gas_price, $account_list);
            return;
        }
        if (!$templates) {
            throw new ShowMsgException('At least one template selected');
        }
        $trans = [];
        $template_list = [];
        foreach ($templates as $key => $template_id) {
            $template = (new Template)
                ->where('template_id', $template_id)
                ->first();
            $task = new Task();
            $task->forceFill([
                'task_data_id' => $task_data_id,
                'template_id' => $template_id,
                'method' => $methods[$key],
                'args' => $params[$key],
                'amount' => $amount,
                'save_data' => Arr::get($saveData, $key),
                'start_time' => $start_time,
                'time_range' => $time_range,
                'min_gas_price' => $min_gas_price,
            ])->save();
            $template_list[] = [
                'template_id' => $template_id,
                'contract' => $template['contract'],
                'method' => $methods[$key],
                'task_id' => $task->task_id,
                'task_data_id' => $task_data_id,
                'args' => $params[$key],
            ];
        }
        $shuffle_list = $account_list->toArray();
        shuffle($shuffle_list);
        foreach ($account_list as $a_index => $item) {
            if ($time_range == 0) {
                $execute_time = $start_time;
            } else {
                $execute_time = $start_time + rand(0, $time_range);
            }
            foreach ($template_list as $template_list_item) {
                $args = $template_list_item['args'];
                $newArgs = [];
                foreach ($args as $arg_key => &$arg) {
                    $arg = trim($arg);
                    if (preg_match('/^{{2}(.+)}{2}$/', $arg, $matches)) {
                        if ($matches[1] === 'account') {
                            $arg = $item['address'];
                        } else if (Str::startsWith($matches[1], '~log.') || Str::startsWith($matches[1], '~param.')) {
                            $arg = trim(Arr::get($shuffle_list[$a_index]['data'], substr($matches[1], 1)));
                            if (!$arg) {
                                throw new ShowMsgException('not found param ' . $matches[1] . ' --- type' . substr($matches[1], 1));
                            }
                        } else if (Str::startsWith($matches[1], 'log.') || Str::startsWith($matches[1], 'param.')) {
                            $arg = trim(Arr::get($item['data'], $matches[1]));
                            if (!$arg) {
                                throw new ShowMsgException('not found param ' . $matches[1]);
                            }
                        } else if (preg_match('/^random\((.+)\)$/', $matches[1], $matches2)) {
                            $arr = explode(',', $matches2[1]);
                            $arg = trim($arr[array_rand($arr)]);
                        } else if (preg_match('/^inRange\((.+)\)$/', $matches[1], $matches2)) {
                            $arr = explode(',', $matches2[1]);
                            $count = count($arr);
                            if (!in_array($count, [2, 3])) {
                                throw new ShowMsgException('inRange param error');
                            }
                            $step = 1;
                            if ($count === 3) {
                                list($start, $end, $step) = $arr;
                                $step = trim($step);
                            } else {
                                list($start, $end) = $arr;
                            }
                            $start = trim($start);
                            $end = trim($end);
                            if (!(is_numeric($start) && $end)) {
                                throw new ShowMsgException('inRange param error');
                            }
                            if ($start > $end) {
                                throw new ShowMsgException('inRange param error');
                            }
                            $arg = mt_rand((int)bcdiv($start, $step), (int)bcdiv($end, $step));
                            $arg = bcmul($arg, $step);
                        } else {
                            throw new ShowMsgException('unknown function ' . $matches[1]);
                        }
                        //if it is an array
                    } else if (preg_match('/^\[(.+)]$/', $arg, $matches)) {
                        $arg = explode(',', $matches[1]);
                        $arg = array_map(function ($v) {
                            return trim($v);
                        }, $arg);
                    }
                    if (Str::contains($arg_key, '.')) {
                        list($tuple_name) = explode('.', $arg_key);
                        if (isset($newArgs[$tuple_name])) {
                            $newArgs[$tuple_name][] = $arg;
                        } else {
                            $newArgs[$tuple_name] = [$arg];
                        }
                    } else {
                        $newArgs[$arg_key] = $arg;
                    }
                }
                unset($arg);

                if (Str::contains(json_encode($newArgs), '{{')) {
                    throw new ShowMsgException('include error param ' . json_encode($newArgs));
                }
                $trans[] = [
                    'chain' => $chain,
                    'task_id' => $template_list_item['task_id'],
                    'account_id' => $item['account_id'],
                    'task_data_id' => $template_list_item['task_data_id'],
                    'template_id' => $template_list_item['template_id'],
                    'execute_time' => $execute_time,
                    'amount' => $amount,
                    'nonce' => 0,
                    'from' => $item['address'],
                    'to' => $template_list_item['contract'],
                    'method' => $template_list_item['method'],
                    'args' => json_encode($newArgs),
                    'call_data' => null,
                    'trans_hash' => null,
                    'status' => TaskTrans::STATUS_WAIT,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        foreach (array_chunk($trans, 50) as $chunk) {
            TaskTrans::insert($chunk);
        }
    }


    /**
     * @throws ShowMsgException
     */
    public function callDataProcess(Request $request, Chain $chain, $amount, $start_time, $time_range, $min_gas_price, $account_list)
    {
        $sale_price = trim($request->input('sale_price'));
        $sale_amount = trim($request->input('sale_amount'));
        $sale_token = trim($request->input('sale_token'));
        $make_order = $request->input('make_order');
        $task_data_id = $request->input('task_data_id');
        $call_data = $request->input('call_data');
        $now = date('Y-m-d H:i:s');
        $args = [];
        $token_id = null;
        if ($make_order) {
            if (!$sale_price) {
                throw new ShowMsgException("sale price error", 1);
            }
            if (!$sale_amount) {
                throw new ShowMsgException("sale amount error", 1);
            }
            if (!$sale_token) {
                throw new ShowMsgException("sale token error", 1);
            }
            if ($amount > 0) {
                throw new ShowMsgException("eth amount must 0", 1);
            }
            $call_data = "data:,{\"p\":\"asc-20\",\"op\":\"list\",\"tick\":\"$sale_token\",\"amt\":\"$sale_amount\"}";
            $_token = Token::where('chain', $chain)
                ->where('name', $sale_token)
                ->first();
            if (!$_token) {
                throw new ShowMsgException('not found token');
            }
            $token_id = $_token->token_id;
            $args = [
                'sale_price' => $sale_price,
                'sale_amount' => $sale_amount,
                'sale_token' => $sale_token,
                'make_order' => $make_order,
                'token_id' => $token_id,
            ];
        }
        if (!Str::startsWith($call_data, 'data:,')) {
            throw new ShowMsgException('call data must start with "data,"');
        }
        $trans = [];
        $task = new Task();
        $task->forceFill([
            'task_data_id' => $task_data_id,
            'template_id' => 0,
            'method' => '',
            'args' => $args,
            'amount' => $amount,
            'save_data' => [],
            'start_time' => $start_time,
            'time_range' => $time_range,
            'min_gas_price' => $min_gas_price,
            'call_data' => $call_data,
        ])->save();
        foreach ($account_list as $item) {
            if ($time_range == 0) {
                $execute_time = $start_time;
            } else {
                $execute_time = $start_time + rand(0, $time_range);
            }
            if ($token_id) {
                $asset = Assets::where('token_id', $token_id)
                    ->where('account_id', $item->account_id)
                    ->first();
                if (!$asset && $asset->balance < $sale_amount) {
                    throw new ShowMsgException('account:' . $item->address . ' asset: ' . $sale_token . ' Insufficient balance');
                }
                $asset->balance = bcsub($asset->balance, $sale_amount, 18);
                $asset->save();
                $to = '0x24e24277e2ff8828d5d2e278764ca258c22bd497';
            } else {
                $to = $item['address'];
            }
            $trans[] = [
                'chain' => $chain,
                'task_id' => $task->task_id,
                'account_id' => $item['account_id'],
                'task_data_id' => $task_data_id,
                'template_id' => 0,
                'execute_time' => $execute_time,
                'amount' => $amount,
                'nonce' => 0,
                'from' => $item['address'],
                'to' => $to,
                'method' => '',
                'args' => json_encode($args),
                'call_data' => $call_data,
                'trans_hash' => null,
                'status' => TaskTrans::STATUS_WAIT,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        foreach (array_chunk($trans, 50) as $chunk) {
            TaskTrans::insert($chunk);
        }
    }

    /**
     * @throws ShowMsgException
     */
    public function takeOrderProcess(Request $request, Chain $chain, $start_time, $time_range, $min_gas_price, $account_list)
    {
        $take_count = $request->input('take_count');
        $task_data_id = $request->input('task_data_id');
        if (!$take_count) {
            throw new ShowMsgException("take count error", 1);
        }
        $orders = AvaOrder::where('status', AvaOrder::STATUS_CREATED)
            ->orderBy('total_price')
            ->limit($take_count)
            ->get()
            ->toArray();
        if (count($orders) < $take_count) {
            throw new ShowMsgException("Insufficient Ava Order quantity", 1);
        }
        shuffle($orders);
        $template_id = 11;
        $method = 'executeOrder';
        $trans = [];
        $template = (new Template)
            ->where('template_id', $template_id)
            ->first();
        $task = new Task();
        $now = date('Y-m-d H:i:s');
        $task->forceFill([
            'task_data_id' => $task_data_id,
            'template_id' => $template_id,
            'method' => $method,
            'args' => [],
            'amount' => 0,
            'save_data' => [],
            'start_time' => $start_time,
            'time_range' => $time_range,
            'min_gas_price' => $min_gas_price,
        ])->save();
        foreach ($account_list as $key => $item) {
            if ($time_range == 0) {
                $execute_time = $start_time;
            } else {
                $execute_time = $start_time + rand(0, $time_range);
            }
            if ($key >= $take_count) {
                continue;
            }
            $account = Account::find($item['account_id']);
            $order = $orders[$key];
            AvaOrder::where('id', $order['id'])
                ->update([
                    'status' => AvaOrder::STATUS_OCCUPIED,
                ]);
            if ($account->balance < $order['total_price']) {
                throw new ShowMsgException('account:' . $account->address . ' match order:' . $order['id'] . ' need ' . $order['total_price'] . ' balance:' . $account->balance, 1);
            }
            if (strtolower($account->address) == strtolower($order['seller'])) {
                throw new ShowMsgException('account:' . $account->address . ' match order:' . $order['id'] . ' same address', 1);
            }
            $call_data = $this->getCallData($order, $item['address']);
            $trans[] = [
                'chain' => $chain,
                'task_id' => $task->task_id,
                'account_id' => $item['account_id'],
                'task_data_id' => $task_data_id,
                'template_id' => $template_id,
                'execute_time' => $execute_time,
                'amount' => $order['total_price'],
                'nonce' => 0,
                'from' => $item['address'],
                'to' => $template->contract,
                'method' => $method,
                'args' => json_encode($order),
                'call_data' => $call_data,
                'trans_hash' => null,
                'status' => TaskTrans::STATUS_WAIT,
                'created_at' => $now,
                'updated_at' => $now,
            ];

        }
        foreach (array_chunk($trans, 50) as $chunk) {
            TaskTrans::insert($chunk);
        }
    }

    public function getCallData(array $order, string $receiver)
    {
        $ethAbi = new Ethabi(['uint' => new Uinteger, 'bytes' => new Bytes(), 'dy', 'address' => new Address, 'string' => new \Web3\Contracts\Types\Str, 'dynamicBytes' => new DynamicBytes,]);
        $types = ["address", "address", "bytes32", "string", "uint256", "uint256", "uint256", "uint64", "uint64", "uint16", "uint32", "bytes", "uint8", "bytes32", "bytes32"];
        $new_order = [
            $order['seller'],
            $order['creator'],
            $order['list_id'],
            $order['ticker'],
            $order['amount'],
            $order['price'],
            $order['nonce'],
            $order['listing_time'],
            $order['expiration_time'],
            $order['creator_fee_rate'],
            $order['salt'],
            $order['extra_params'],
            $order['v'],
            $order['r'],
            $order['s'],
        ];
        $other = Utils::stripZero($ethAbi->encodeParameters($types, $new_order));
        $address = Utils::stripZero($ethAbi->encodeParameters(['address'], [$receiver]));
        $functionSelector = '0xd9b3d6d0';
        $staticLength = Utils::stripZero($ethAbi->encodeParameters(['uint256'], ['64']));
        return $functionSelector . $staticLength . $address . $other;
    }

}
