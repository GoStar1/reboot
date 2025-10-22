<?php

namespace App\Models\BlockChain;

use App\Casts\Json;
use Illuminate\Database\Eloquent\Model;

class TaskDataAccount extends Model
{
    protected $primaryKey = 'task_account_id';

    protected $casts = [
        'data' => Json::class,
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];
}
