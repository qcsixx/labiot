<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class SessionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Gunakan event listener untuk mengatur session berdasarkan guard
        Event::listen(Login::class, function ($event) {
            $guard = Auth::getDefaultDriver();
            $user = $event->user;
            
            // Tentukan cookie berdasarkan guard/role
            if ($guard === 'web_admin' || ($user && $user->role === 'admin')) {
                config(['session.cookie' => 'admin_session']);
                session()->setId(session()->getId() . '_admin');
                Log::debug('SESSION: Set admin session cookie on login');
            } elseif ($guard === 'web_user' || ($user && $user->role === 'user')) {
                config(['session.cookie' => 'user_session']);
                session()->setId(session()->getId() . '_user');
                Log::debug('SESSION: Set user session cookie on login');
            }
        });
        
        Event::listen(Logout::class, function ($event) {
            $guard = Auth::getDefaultDriver();
            $user = $event->user;
            
            if ($guard === 'web_admin' || ($user && $user->role === 'admin')) {
                config(['session.cookie' => 'admin_session']);
                Log::debug('SESSION: Set admin session cookie on logout');
            } elseif ($guard === 'web_user' || ($user && $user->role === 'user')) {
                config(['session.cookie' => 'user_session']);
                Log::debug('SESSION: Set user session cookie on logout');
            }
        });
        
        // Middleware untuk routes admin
        Route::matched(function ($event) {
            $route = $event->route;
            $request = $event->request;
            
            if (str_contains($request->path(), 'admin')) {
                config(['session.cookie' => 'admin_session']);
                Auth::shouldUse('web_admin');
                Log::debug('SESSION: Route admin detected, using admin session');
            } elseif (str_contains($request->path(), 'user')) {
                config(['session.cookie' => 'user_session']);
                Auth::shouldUse('web_user');
                Log::debug('SESSION: Route user detected, using user session');
            }
        });
    }
}
