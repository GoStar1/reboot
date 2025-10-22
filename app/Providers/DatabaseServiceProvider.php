<?php

namespace App\Providers;

use Illuminate\Database\DatabaseServiceProvider as ServiceProvider;
use Log;

class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        parent::register();
    }

    public function boot()
    {
        parent::boot();
        $this->registerQueryMonitor();
    }

    protected function registerQueryMonitor()
    {
        $debug = config('app.debug');
        \DB::listen(function ($query) use ($debug) {
            if ($debug) {
                if ($query->time > 10) {
                    Log::notice('[SQL Log]' . json_encode([$query->sql, $query->bindings, $query->time], JSON_UNESCAPED_UNICODE));
                } else {
                    Log::info('[SQL Log]' . json_encode([$query->sql, $query->bindings, $query->time], JSON_UNESCAPED_UNICODE));
                }
            }
        });
    }
}
