<?php

namespace App\Models\BlockChain;

use App\Enums\Chain;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $primaryKey = 'account_id';
    const BOOL_NO = 0;
    const BOOL_YES = 1;

    protected $guarded = [];

    protected $casts = [
        'chain' => Chain::class,
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    public static array $bool_dict = [
        self::BOOL_YES => 'YE',
        self::BOOL_NO => 'NO',
    ];

    protected $perPage = 30;
}
