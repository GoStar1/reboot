<?php

namespace App\Models\Robot;

use Illuminate\Database\Eloquent\Model;

class Withdraw extends Model
{
    const STATUS_WAIT = 1;
    const STATUS_SUCCESS = 2;
    const STATUS_FAILED = 3;

    protected $casts = [
        'headers' => 'array',
        'extra' => 'encrypted:array',
        'req' => 'array',
        'response' => 'array',
    ];

    const TYPE_GATE = 1;
    const TYPE_BINANCE = 2;
}
