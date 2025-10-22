<?php

namespace App\Models\BlockChain;

use App\Enums\Chain;
use App\Enums\ConfigKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;

class GlobalConfig extends Model
{
    protected $primaryKey = 'id';

    protected $casts = [
        'chain' => Chain::class,
        'key' => ConfigKey::class,
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];
    protected $guarded = [];
    public $timestamps = true;

    static function getValue(Chain $chain, ConfigKey $key)
    {
        $cached = Cache::get($key->name);
        if ($cached) {
            return msgpack_unpack($cached);
        }
        $row = self::where([
            'chain' => $chain->value,
            'key' => $key->name,
        ])->first(['value']);
        if (!$row) {
            return null;
        }
        Cache::put($key->name, msgpack_pack($row['value']), 1200);
        return $row['value'];
    }

    static function saveValue(Chain $chain, ConfigKey $key, mixed $value): bool
    {
        $map = [
            'chain' => $chain->value,
            'key' => $key->name,
        ];
        if (is_array($value)) {
            $value = json_encode($value);
        }
        $config = (new self)->where($map)->first();
        if ($config) {
            $data = array_merge($map, [
                'updated_at' => Date::now(),
                'value' => $value,
            ]);
            $ret = !!((new self)->where('id', $config['id'])
                ->update($data));
        } else {
            $data = array_merge($map, [
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
                'value' => $value,
            ]);
            $ret = (new self)->insert($data);
        }
        $ret && Cache::forget($key->name);
        return $ret;
    }
}
