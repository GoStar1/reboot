<?php

namespace App\Console\Commands\BlockChain;

use App\Enums\Chain;
use App\Exceptions\ShowMsgException;
use App\Models\BlockChain\ChainRpc;
use App\Services\TransactionService;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Cache;

class RpcHeartbeat extends Command
{
    protected $name = 'blockchain:rpc-heartbeat';

    use DispatchesJobs;

    public function handle(): void
    {
        $list = ChainRpc::where('heartbeat', ChainRpc::BOOL_YES)
            ->get();
        $services = TransactionService::instance();
        foreach ($list as $item) {
            $start = microtime(true);
            try {
                $number = $services->lastBlockByUrl($item->url);
                $end = microtime(true);
                $resp_time = bcsub($end, $start, 3);
                $item->forceFill([
                    'status' => ChainRpc::STATUS_ACTIVE,
                    'block_number' => $number,
                    'resp_time' => $resp_time,
                ])->save();
            } catch (ShowMsgException $err) {

            }
        }
        $chains = Chain::cases();
        foreach ($chains as $chain) {
            Cache::forget(md5(sprintf(ChainRpc::HASH_KEY, $chain->value)));
        }
    }
}
