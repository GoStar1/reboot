<?php

namespace App\Models\BlockChain;

use App\Enums\Chain;
use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    protected $primaryKey = 'token_id';
    protected $casts = [
        'chain' => Chain::class,
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];
}
