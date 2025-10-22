<?php

namespace App\Models\BlockChain;

use App\Casts\Json;
use App\Enums\Chain;
use Illuminate\Database\Eloquent\Model;

class TaskTrans extends Model
{
    protected $primaryKey = 'task_trans_id';

    const STATUS_WAIT = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_FAILED = 2;
    const STATUS_CANCELED = 3;

    public static array $status_dict = [
        self::STATUS_WAIT => 'wait',
        self::STATUS_SUCCESS => 'success',
        self::STATUS_FAILED => 'failed',
        self::STATUS_CANCELED => 'cancelled',
    ];

    protected $casts = [
        'chain' => Chain::class,
        'args' => Json::class,
        'logs' => Json::class,
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    protected $guarded = [];
}
