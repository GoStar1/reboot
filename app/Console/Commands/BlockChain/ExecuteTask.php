<?php

namespace App\Console\Commands\BlockChain;

use App\Jobs\ExecuteTaskJob;
use App\Jobs\TransStatusJob;
use App\Models\BlockChain\TaskTrans;
use Illuminate\Cache\Lock;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Symfony\Component\Console\Input\InputOption;

class ExecuteTask extends Command
{
    protected $name = 'blockchain:task';

    use DispatchesJobs;

    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        $task_trans_id = $this->option('task_trans_id');
        $query = new TaskTrans;
        if ($task_trans_id) {
            $query = $query->where('task_trans_id', $task_trans_id);
        }
        $now = time();
        $trans = $query
            ->whereNull('trans_hash')
            ->where('status', TaskTrans::STATUS_WAIT)
            ->where('execute_time', '<', $now)
            ->orderBy('task_trans_id')
            ->get(['task_trans_id'])
            ->toArray();
        foreach ($trans as $_trans) {
            $this->dispatch(new ExecuteTaskJob($_trans['task_trans_id']));
        }
        $task_trans_id = $this->option('task_trans_id');
        $query = new TaskTrans;
        $task_trans_id && $query = $query->where('task_trans_id', $task_trans_id);
        $data = $query
            ->where('trans_hash', '<>', '')
            ->where('status', TaskTrans::STATUS_WAIT)
            ->get(['task_trans_id'])
            ->toArray();
        foreach ($data as $item) {
            $this->dispatch(new TransStatusJob($item['task_trans_id']));
        }
    }

    protected function getOptions(): array
    {
        return [
            ['task_trans_id', null, InputOption::VALUE_OPTIONAL, 'task_id'],
        ];
    }
}
