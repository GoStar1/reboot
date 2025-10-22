<?php

namespace App\Models\BlockChain;

use App\Casts\Json;
use App\Enums\Chain;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $primaryKey = 'task_id';
    protected $casts = [
        'chain' => Chain::class,
        'save_data' => Json::class,
        'args' => Json::class,
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];
}
