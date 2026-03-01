<?php
namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Untuk setiap request, atur session cookie berdasarkan guard
        $this->app->booted(function () {
            // Konfigurasi cookie session admin
            Auth::viaRequest('admin-session', function ($request) {
                if (Auth::hasUser() && Auth::user()->role === 'admin') {
                    // Set cookie session admin
                    Config::set('session.cookie', 'admin_session');
                    return Auth::user();
                }
                
                return null;
            });
            
            // Konfigurasi cookie session user
            Auth::viaRequest('user-session', function ($request) {
                if (Auth::hasUser() && Auth::user()->role === 'user') {
                    // Set cookie session user
                    Config::set('session.cookie', 'user_session');
                    return Auth::user();
                }
                
                return null;
            });
        });
    }
} 