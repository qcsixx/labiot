<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Session;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? ['web'] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Penanganan multi-guard - redirect sesuai dengan guard
                if ($guard === 'web_admin') {
                    config(['session.cookie' => 'admin_session']);
                    
                    // Set session name
                    if (session()->isStarted()) {
                        session()->save();
                        session()->start();
                    }
                    
                    return redirect()->route('admin.dashboard-admin');
                } 
                elseif ($guard === 'web_user') {
                    config(['session.cookie' => 'user_session']);
                    
                    // Set session name
                    if (session()->isStarted()) {
                        session()->save();
                        session()->start();
                    }
                    
                    return redirect()->route('user.dashboard-user');
                }
                else {
                    // Penanganan untuk guard default (web) - berdasarkan role
                    $user = Auth::guard($guard)->user();
                    if ($user && $user->role === 'admin') {
                        config(['session.cookie' => 'admin_session']);
                        
                        // Set session name
                        if (session()->isStarted()) {
                            session()->save();
                            session()->start();
                        }
                        
                        return redirect()->route('admin.dashboard-admin');
                    } else {
                        config(['session.cookie' => 'user_session']);
                        
                        // Set session name
                        if (session()->isStarted()) {
                            session()->save();
                            session()->start();
                        }
                        
                        return redirect()->route('user.dashboard-user');
                    }
                }
            }
        }

        return $next($request);
    }
} 