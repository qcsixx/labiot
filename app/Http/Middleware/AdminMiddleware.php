<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Set session cookie untuk admin
        config(['session.cookie' => 'admin_session']);
        
        // Cek autentikasi dengan guard web_admin
        if (!Auth::guard('web_admin')->check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akses ditolak. Anda tidak memiliki izin admin.',
                ], 403);
            }
            
            return redirect('/login')->with('error', 'Akses ditolak. Anda memerlukan akun admin.');
        }

        // Cek status admin - jika suspended, logout dan redirect ke login
        $admin = Auth::guard('web_admin')->user();
        if ($admin->status === 'suspended') {
            Auth::guard('web_admin')->logout();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akun Anda telah ditangguhkan. Silakan hubungi super admin.',
                ], 403);
            }
            
            return redirect('/login')->with('error', 'Akun Anda telah ditangguhkan. Silakan hubungi super admin.');
        }

        return $next($request);
    }
} 