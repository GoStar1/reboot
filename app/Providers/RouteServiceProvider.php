<?php

namespace App\Providers;

use App\Libraries\AuthRouteMethods;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Auth;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard/main';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        Route::mixin(new AuthRouteMethods);
//        $this->configureRateLimiting();
        $this->routes(function () {
            Route::group([
                'namespace' => '\App\Http\Controllers',
                'middleware' => 'web'
            ], function () {
                Route::middleware('auth')
                    ->group(base_path('routes/admin.php'));
                Route::adminAuth();
            });
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
//    protected function configureRateLimiting(): void
//    {
//        RateLimiter::for('api', function (Request $request) {
//            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
//        });
//    }
}
