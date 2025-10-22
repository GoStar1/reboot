<?php

namespace App\Models\BlockChain;

use App\Enums\Chain;
use Illuminate\Database\Eloquent\Model;

class ChainRpc extends Model
{
    const HASH_KEY = 'cache:blockchain_chain_rpcs-%s';

    const STATUS_ACTIVE = 1;
    const STATUS_INVALID = 2;

    protected $guarded = [];



    public static array $status_dict = [
        self::STATUS_ACTIVE => 'ACTIVE',
        self::STATUS_INVALID => 'INVALID',
    ];

    const BOOL_NO = 0;
    const BOOL_YES = 1;

    public static array $heartbeat_dict = [
        self::BOOL_YES => 'YES',
        self::BOOL_NO => 'NO',
    ];

    protected $casts = [
        'chain' => Chain::class,
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];


    public function getProviderUrl(Chain $chain)
    {
        $rpcs = \Cache::get(md5(sprintf(ChainRpc::HASH_KEY, $chain->value)));
        $rpcs && $rpcs = msgpack_unpack($rpcs);
        if ($rpcs) {
            return $rpcs[array_rand($rpcs)];
        }
        $rpcs = (new ChainRpc)->where('chain', $chain)
            ->where('status', ChainRpc::STATUS_ACTIVE)
            ->orderBy('priority')
            ->orderBy('resp_time')
            ->get(['url'])->pluck('url')->toArray();
        if (!$rpcs) {
            return '';
        }
        \Cache::put(md5(sprintf(ChainRpc::HASH_KEY, $chain->value)), msgpack_pack($rpcs), 600);
        return $rpcs[array_rand($rpcs)];
    }
}
