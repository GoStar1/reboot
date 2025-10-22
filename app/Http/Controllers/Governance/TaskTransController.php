<?php

namespace App\Http\Controllers\Governance;

use App\Enums\Chain;
use App\Http\Controllers\Controller;
use App\Models\BlockChain\Task;
use App\Models\BlockChain\TaskData;
use App\Models\BlockChain\TaskTrans;
use App\Models\Robot\Template;
use App\Services\SystemUtils;
use Arr;
use Illuminate\Http\Request;

class TaskTransController extends Controller
{
    public function index(Request $request)
    {
        $chain = Chain::tryFrom($request->input('chain'));
        $keyword = $request->input('keyword');
        $status = $request->input('status');
        $template_id = $request->input('template_id');
        $task_data_id = $request->input('task_data_id');
        $task_id = $request->input('task_id');
        $query = new TaskTrans;
        !SystemUtils::isNull($status) && $query = $query->where('status', $status);
        $keyword && $query = $query->where('from', $keyword);
        $template_id && $query = $query->where('template_id', $template_id);
        $task_data_id && $query = $query->where('task_data_id', $task_data_id);
        $task_id && $query = $query->where('task_id', $task_id);
        $chain && $query = $query->where('chain', $chain);
        $list = $query->orderByDesc('task_trans_id')->paginate()->toArray();
        $_template_dict = (new Template)
            ->get(['name', 'template_id', 'chain'])
            ->toArray();
        $template_dict = [];
        $template_list_dict = collect($_template_dict)->pluck('name', 'template_id')->toArray();
        foreach ($_template_dict as $item) {
            $arr = Arr::get($template_dict, $item['chain'], []);
            $arr[] = [
                'key' => $item['name'],
                'value' => $item['template_id'],
            ];
            $template_dict[$item['chain']] = $arr;
        }
        $_task_data_dict = (new TaskData)
            ->get(['task_data_id', 'name', 'chain'])
            ->toArray();
        $task_data_dict = [];
        $task_data_list_dict = collect($_task_data_dict)->pluck('name', 'task_data_id')->toArray();
        foreach ($_task_data_dict as $item) {
            $arr = Arr::get($task_data_dict, $item['chain'], []);
            $arr[] = [
                'key' => $item['name'],
                'value' => $item['task_data_id'],
            ];
            $task_data_dict[$item['chain']] = $arr;
        }
        foreach ($list['data'] as &$item) {
            $item['chain'] = Chain::from($item['chain']);
        }
        unset($item);
        return view('governance.task_trans.index', [
            'active_menu' => 'governance.taskTrans',
            'title' => 'Task Trans',
            'list' => $list,
            'keyword' => $keyword,
            'status' => $status,
            'chain' => $chain,
            'template_dict' => $template_dict,
            'task_data_dict' => $task_data_dict,
            'template_list_dict' => $template_list_dict,
            'task_data_list_dict' => $task_data_list_dict,
            'task_data_id' => $task_data_id,
            'template_id' => $template_id,
            'status_dict' => TaskTrans::$status_dict,
            'task_id' => $task_id,
        ]);
    }


    public function detail(Request $request)
    {
        $task_trans_id = (int)$request->input('task_trans_id');
        $trans = TaskTrans::find($task_trans_id);
        $template_name = (new Template)
            ->where('template_id', $trans['template_id'])
            ->value('name');
        $task_data_name = (new TaskData)
            ->where('task_data_id', $trans['task_data_id'])
            ->value('name');
        $min_gas_price = Task::find($trans->task_id)->min_gas_price;
        return view('governance.task_trans.detail', [
            'item' => $trans,
            'min_gas_price' => $min_gas_price,
            'task_data_name' => $task_data_name,
            'template_name' => $template_name,
        ]);
    }

    public function edit(Request $request)
    {
        $task_trans_id = (int)$request->input('task_trans_id');
        $trans = TaskTrans::find($task_trans_id);
        return view('governance.task_trans.edit', [
            'item' => $trans,
            'active_menu' => 'governance.taskTrans',
            'title' => 'Task Trans',
        ]);
    }

    public function save(Request $request)
    {
        $task_trans_id = (int)$request->input('task_trans_id');
        $execute_time = $request->input('execute_time');
        $execute_time = strtotime($execute_time);
        $ret = TaskTrans::getQuery()->where('task_trans_id', $task_trans_id)
            ->update([
                'execute_time' => $execute_time,
            ]);
        return $ret ? $this->success() : $this->error(1, 'failed');
    }


    public function cancel(Request $request)
    {
        $task_trans_id = (int)$request->input('task_trans_id');
        $trans = TaskTrans::find($task_trans_id);
        if ($trans->status != TaskTrans::STATUS_WAIT) {
            return $this->error(1, 'failed');
        }
        if ($trans->trans_hash) {
            return $this->error(1, 'transaction is submit');
        }
        $lock = \Cache::lock('execute-task-' . $task_trans_id, 120);
        if (!$lock->get()) {
            return $this->error(1, 'transaction is processing');
        }
        $trans->status = TaskTrans::STATUS_CANCELED;
        $ret = $trans->save();
        $lock->release();
        return $ret ? $this->success() : $this->error(1, 'failed');
    }
}
