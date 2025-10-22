<?php


namespace App\Services;


class Services
{
    public static function instance()
    {
        return new static();
    }

    public static function isProduction()
    {
        return \App::environment('production');
    }
}
