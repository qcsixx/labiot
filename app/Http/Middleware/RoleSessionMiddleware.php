<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class RoleSessionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        if (Auth::user()->role !== $role) {
            // Redirect ke halaman yang sesuai dengan role user
            if (Auth::user()->role === 'admin') {
                return redirect()->route('admin.dashboard');
            } else {
                return redirect()->route('user.dashboard-user');
            }
        }
        
        // Set session cookie berdasarkan role
        if ($role === 'admin') {
            config(['session.cookie' => 'admin_session']);
        } else {
            config(['session.cookie' => 'user_session']);
        }
        
        // Regenerate session untuk keamanan jika belum dimulai
        if (!session()->isStarted()) {
            session()->start();
        }
        
        return $next($request);
    }
} 