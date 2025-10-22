<?php

namespace App\Models\Robot;

use App\Casts\Json;
use App\Enums\Chain;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $primaryKey = 'template_id';

    protected $casts = [
        'abi' => Json::class,
        'chain' => Chain::class,
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];
}
