<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);

            // Regenerate session ID
            $request->session()->regenerate();
            
            // Pilih guard berdasarkan role
            $guard = $user->isAdmin() ? 'web_admin' : 'web_user';
            
            // Atur cookie session sesuai role
            $sessionName = config('session.cookie') . '_' . $user->role;
            config(['session.cookie' => $sessionName]);
            
            // Login dengan guard yang sesuai
            Auth::guard($guard)->login($user);

            return response()->json([
                'status' => 'success',
                'message' => 'Pendaftaran berhasil',
                'user' => new UserResource($user),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Register error: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mendaftar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Log in the user.
     */
    public function login(Request $request): JsonResponse
    {
        try {
            // Validasi input
            $request->validate([
                'email' => 'required|string',
                'password' => 'required|string|min:6'
            ]);
            
            // Cari user berdasarkan email atau NIM
            $user = User::where(function($query) use ($request) {
                $query->where('email', $request->email)
                      ->orWhere('nim', $request->email);
            })->first();

            // Log attempt
            Log::info('Login attempt', ['email' => $request->email, 'found_user' => $user ? true : false]);
            
            // Cek user ditemukan
            if (!$user) {
                Log::warning('Login failed: User not found', ['email' => $request->email]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Email/NIM tidak ditemukan',
                ], 401);
            }

            // Cek password
            if (!Hash::check($request->password, $user->password)) {
                Log::warning('Login failed: Invalid password', ['email' => $request->email]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Password salah',
                ], 401);
            }

            // Cek status user
            if ($user->status !== 'active') {
                Log::warning('Login failed: Inactive account', ['user_id' => $user->id, 'status' => $user->status]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akun Anda tidak aktif. Silakan hubungi admin.',
                ], 403);
            }

            // Regenerate session ID
            $request->session()->regenerate();
            
            // Pilih guard berdasarkan role
            $guard = $user->isAdmin() ? 'web_admin' : 'web_user';
            
            // Atur cookie session sesuai role
            if ($user->isAdmin()) {
                config(['session.cookie' => 'admin_session']);
            } else {
                config(['session.cookie' => 'user_session']);
            }
            
            // Update nama session
            if (!app()->runningInConsole()) {
                app('session')->setName(config('session.cookie'));
            }
            
            // Login dengan guard yang sesuai
            Auth::guard($guard)->login($user, $request->remember == 'true');
            
            // Update last login
            $user->last_login_at = now();
            $user->save();
            
            // Log successful login
            Log::info('Login successful', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Login berhasil',
                'user' => new UserResource($user),
                'redirect' => $user->isAdmin() ? '/admin/dashboard' : '/user/dashboard-user'
            ]);
        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage(), [
                'email' => $request->email ?? 'not provided',
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat login',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Log out the user.
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            // Deteksi role dan guard yang sedang aktif
            $role = Auth::user() ? Auth::user()->role : null;
            $guard = $role === 'admin' ? 'web_admin' : 'web_user';
            
            // Log the logout attempt
            Log::info('Logout attempt', ['user_id' => Auth::user() ? Auth::user()->id : 'unknown']);
            
            // Set cookie session sesuai role untuk memastikan kita menghapus yang benar
            if ($role === 'admin') {
                config(['session.cookie' => 'admin_session']);
            } else {
                config(['session.cookie' => 'user_session']);
            }
            
            // Update nama session
            if (!app()->runningInConsole()) {
                app('session')->setName(config('session.cookie'));
            }
            
            // Logout dari guard yang aktif
            Auth::guard($guard)->logout();
            
            // Invalidate dan regenerate session
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            Log::info('Logout successful', [
                'role' => $role
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Logout berhasil',
            ]);
        } catch (\Exception $e) {
            Log::error('Logout error: ' . $e->getMessage(), [
                'user_id' => Auth::user() ? Auth::user()->id : 'unknown',
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat logout',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user profile.
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            return response()->json([
                'status' => 'success',
                'user' => new UserResource(Auth::user()),
            ]);
        } catch (\Exception $e) {
            Log::error('Profile error: ' . $e->getMessage(), [
                'user_id' => Auth::user() ? Auth::user()->id : 'unknown',
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil profil',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
