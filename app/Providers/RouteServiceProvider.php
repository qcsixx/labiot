<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Explicit route model binding untuk ItemTracking
        Route::model('itemTracking', \App\Models\ItemTracking::class, function ($value) {
            return \App\Models\ItemTracking::where('tracking_id', $value)->firstOrFail();
        });
        
        // Explicit route model binding untuk tracking
        Route::model('tracking', \App\Models\ItemTracking::class, function ($value) {
            return \App\Models\ItemTracking::where('tracking_id', $value)->firstOrFail();
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
        
        // Daftarkan middleware aplikasi dengan benar
        $router = $this->app['router'];
        
        // Mendaftarkan middleware untuk session guard
        $router->aliasMiddleware('session.guard', \App\Http\Middleware\SessionGuardMiddleware::class);
        
        // Daripada menggunakan middleware role, gunakan guard auth yang sesuai
        $router->aliasMiddleware('role.session', \App\Http\Middleware\RoleSessionMiddleware::class);
        
        // Mendaftarkan middleware untuk admin dan user
        $router->aliasMiddleware('admin', \App\Http\Middleware\AdminMiddleware::class);
        $router->aliasMiddleware('user', \App\Http\Middleware\UserMiddleware::class);
    }
} 