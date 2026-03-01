<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class UserMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Set session cookie untuk user
        config(['session.cookie' => 'user_session']);
        
        // Cek autentikasi dengan guard web_user
        if (!Auth::guard('web_user')->check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akses ditolak. Anda tidak memiliki izin user.',
                ], 403);
            }
            
            return redirect('/login')->with('error', 'Akses ditolak. Anda memerlukan akun user.');
        }

        // Cek status user - jika suspended, logout dan redirect ke login
        $user = Auth::guard('web_user')->user();
        if ($user->status === 'suspended') {
            Auth::guard('web_user')->logout();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akun Anda telah ditangguhkan. Silakan hubungi admin.',
                ], 403);
            }
            
            return redirect('/login')->with('error', 'Akun Anda telah ditangguhkan. Silakan hubungi admin.');
        }

        return $next($request);
    }
} 