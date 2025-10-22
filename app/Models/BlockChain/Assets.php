<?php

namespace App\Models\BlockChain;

use Illuminate\Database\Eloquent\Model;

class Assets extends Model
{
    protected $primaryKey = 'asset_id';

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];
}
