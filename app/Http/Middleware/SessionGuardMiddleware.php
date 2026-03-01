<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SessionGuardMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $guard
     */
    public function handle(Request $request, Closure $next, string $guard = 'web'): Response
    {
        // Set cookie session berdasarkan guard SEBELUM operasi autentikasi
        if ($guard === 'web_admin') {
            config(['session.cookie' => 'admin_session']);
        } else if ($guard === 'web_user') {
            config(['session.cookie' => 'user_session']);
        } else {
            config(['session.cookie' => 'laravel_session']);
        }
        
        // Pastikan kita menggunakan guard yang benar
        Auth::shouldUse($guard);
        
        // Start session jika belum dimulai
        if (!session()->isStarted()) {
            session()->start();
        }
        
        // Debugging log
        Log::debug('Session Guard Middleware', [
            'guard' => $guard,
            'cookie' => config('session.cookie'),
            'session_id' => session()->getId(),
            'path' => $request->path()
        ]);
        
        // Ambil user jika sudah terautentikasi
        if (Auth::guard($guard)->check()) {
            $user = Auth::guard($guard)->user();
            
            Log::debug('User authenticated', [
                'user_id' => $user->id,
                'role' => $user->role,
                'guard' => $guard,
                'session_id' => session()->getId()
            ]);
        }
        
        return $next($request);
    }
} 