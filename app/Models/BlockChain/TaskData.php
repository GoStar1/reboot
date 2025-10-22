<?php

namespace App\Models\BlockChain;

use App\Enums\Chain;
use Illuminate\Database\Eloquent\Model;

class TaskData extends Model
{
    protected $primaryKey = 'task_data_id';


    protected $casts = [
        'chain' => Chain::class,
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];
}
