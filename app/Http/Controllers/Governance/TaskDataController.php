<?php

namespace App\Http\Controllers\Governance;

use App\Enums\Chain;
use App\Http\Controllers\Controller;
use App\Models\BlockChain\Account;
use App\Models\BlockChain\Assets;
use App\Models\BlockChain\TaskData;
use App\Models\BlockChain\TaskDataAccount;
use App\Models\BlockChain\Token;
use App\Models\Robot\Template;
use App\Providers\RouteServiceProvider;
use App\Services\SystemUtils;
use Illuminate\Http\Request;
use App\Exceptions\ShowMsgException;
use Arr;
use DB;
use Exception;
use Log;

class TaskDataController extends Controller
{
    public function index(Request $request)
    {
        $chain = Chain::tryFrom($request->input('chain'));
        $keyword = $request->input('keyword');
        $status = $request->input('status');
        $query = new TaskData;
        !SystemUtils::isNull($status) && $query = $query->where('status', $status);
        $keyword && $query = $query->where('name', 'like', '%' . $keyword . '%');
        $chain && $query = $query->where('chain', $chain);
        $list = $query->orderByDesc('task_data_id')->paginate()->toArray();
        foreach ($list['data'] as &$item) {
            $item['chain'] = Chain::from($item['chain']);
        }
        unset($item);
        return view('governance.task_data.index', [
            'active_menu' => 'governance.taskData',
            'title' => 'Task Data',
            'list' => $list,
            'keyword' => $keyword,
            'status' => $status,
            'chain' => $chain,
        ]);
    }

    public function account(Request $request)
    {
        $task_data_id = (int)$request->input('task_data_id');
        $token_id = $request->input('token_id');
        $keyword = $request->input('keyword');
        if (!$task_data_id) {
            return response()->redirectTo(RouteServiceProvider::HOME);
        }
        $query = new TaskDataAccount;
        $keyword && $query = $query->where('address', $keyword);
        $list = $query
            ->where('task_data_id', $task_data_id)
            ->orderByDesc('task_data_id')
            ->paginate()->toArray();
        $assets = null;
        $accounts = Arr::pluck($list['data'], 'account_id');
        if ($token_id) {
            $assets = Assets::whereIn('account_id', $accounts)
                ->where('token_id', $token_id)->get(['balance', 'account_id'])
                ->pluck('balance', 'account_id')
                ->toArray();
        }
        $balances = Account::whereIn('account_id', $accounts)
            ->get(['account_id', 'balance'])
            ->pluck('balance', 'account_id')->toArray();
        foreach ($list['data'] as &$item) {
            $item['balance'] = Arr::get($balances, $item['account_id']);
            if ($assets) {
                $item['asset_balance'] = Arr::get($assets, $item['account_id']);
            }
        }
        unset($item);
        $task_data = TaskData::find($task_data_id);
        $token_dict = (new Token)
            ->where('chain', $task_data->chain)
            ->get(['token_id', 'name'])
            ->pluck('name', 'token_id')
            ->toArray();
        return view('governance.task_data.account', [
            'active_menu' => 'governance.taskData',
            'title' => 'Task Data Account',
            'list' => $list,
            'keyword' => $keyword,
            'token_dict' => $token_dict,
            'token_id' => $token_id,
            'chain' => $task_data->chain,
            'task_data_id' => $task_data_id,
        ]);
    }

    public function createView(Request $request)
    {
        $accountArgs = $request->input('accountArgs');
        parse_str($accountArgs, $accountArr);
        $chain = Chain::tryFrom(Arr::get($accountArr, 'chain'));
        if (!$chain) {
            return response()->redirectTo(route('task_data.index'));
        }
        list($template_dict, $method_data) = (new TaskController)->getTemplateData($chain);
        return view('governance.task_data.create', [
            'active_menu' => 'governance.taskData',
            'title' => 'Create Task Data',
            'accountArgs' => $accountArgs,
            'chain' => $chain,
            'template_dict' => $template_dict,
            'method_data' => $method_data,
        ]);
    }

    /**
     * @param Request $request
     * @return int|mixed
     * @throws ShowMsgException
     */
    public function createTaskData(Request $request)
    {
        $name = $request->input('name');
        $chain = Chain::tryFrom($request->input('chain'));
        if (!$chain) {
            throw new ShowMsgException("chain must selected", 1);
        }
        if (TaskData::where('name', $name)
            ->first()) {
            throw new ShowMsgException("name exists", 1);
        }
        {
            $accountArgs = $request->input('accountArgs');
            parse_str($accountArgs, $accountArr);
            list($query) = AccountController::getAccountModel($accountArr);
            $count = $query->count();
            $account_list = $query->get(['address', 'accounts.account_id'])->toArray();
        }
        $task_data = new TaskData;
        $task_data->forceFill([
            'chain' => $chain,
            'name' => $name,
            'accounts' => $count,
            'tasks' => 0,
        ])->save();
        foreach ($account_list as &$item) {
            $item['task_data_id'] = $task_data->task_data_id;
            $item['data'] = '{}';
        }
        unset($item);
        foreach (array_chunk($account_list, 50) as $chunk) {
            TaskDataAccount::insert($chunk);
        }
        return $task_data->task_data_id;
    }

    public function createData(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $task_data_id = $this->createTaskData($request);
                $call_data = $request->input('call_data');
                $templates = $request->input('template');
                $make_order = $request->input('make_order');
                $take_order = $request->input('take_order');
                $request->offsetSet('task_data_id', $task_data_id);
                (new TaskController())->createTask($request);
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


    public function abi(Request $request)
    {
        $template_id = $request->input('template_id');
        $method = $request->input('method');
        $abi = (new Template)
            ->where('template_id', $template_id)
            ->value('abi');
        foreach ($abi as $func) {
            if (\Arr::get($func, 'name') == $method) {
                return $this->success($func);
            }
        }
        return $this->error(10000, "not found");
    }


    public function iFrame(Request $request)
    {
        $task_data_id = (int)$request->input('task_data_id');
        $chain = Chain::tryFrom($request->input('chain'));
        $balance = $request->input('balance');
        $keyword = $request->input('keyword');
        $task_data_name = '';
        if ($task_data_id) {
            $query = (new TaskDataAccount)
                ->leftJoin('accounts', 'accounts.account_id', '=', 'task_data_accounts.account_id')
                ->where('task_data_id', $task_data_id);
            $task_data = TaskData::find($task_data_id);
            $chain = $task_data['chain'];
            if (!$task_data) {
                return response()->redirectTo('/404');
            }
            $task_data_name = $task_data['name'];
        } else {
            $accountArgs = $request->input();
            list($query) = AccountController::getAccountModel($accountArgs);
        }
        $list = $query->orderByDesc('accounts.account_id')->paginate()->toArray();
        return view('governance.task_data.iframe_account', [
            'hide_wrapper' => true,
            'list' => $list,
            'keyword' => $keyword,
            'balance' => $balance,
            'chain' => $chain,
            'bool_dict' => Account::$bool_dict,
            'task_data_id' => $task_data_id,
            'task_data_name' => $task_data_name,
        ]);
    }
}
