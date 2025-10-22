<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;

class AvaOrder extends Model
{
    const STATUS_CREATED = 0;

    const STATUS_OCCUPIED = 1;
    const STATUS_TOOK = 2;
    const STATUS_CANCELED = 3;


    public static array $status_dict = [
        self::STATUS_CREATED => 'created',
        self::STATUS_OCCUPIED => 'occupied',
        self::STATUS_TOOK => 'took',
        self::STATUS_CANCELED => 'cancelled',
    ];

}
