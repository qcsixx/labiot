<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Menampilkan halaman login
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }
    
    /**
     * Menangani proses login
     */
    public function login(Request $request)
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
            
            // Cek user ditemukan
            if (!$user) {
                if ($request->expectsJson() || $request->header('Accept') == 'application/json') {
                    return response()->json([
                        'success' => false,
                        'error' => 'Email/NIM tidak ditemukan'
                    ]);
                }
                
                return back()->withErrors([
                    'email' => 'Email/NIM tidak ditemukan',
                ])->withInput();
            }
            
            // Cek password
            if (!Hash::check($request->password, $user->password)) {
                if ($request->expectsJson() || $request->header('Accept') == 'application/json') {
                    return response()->json([
                        'success' => false,
                        'error' => 'Password salah'
                    ]);
                }
                
                return back()->withErrors([
                    'password' => 'Password salah',
                ])->withInput();
            }
            
            // Cek status user
            if ($user->status !== 'active') {
                if ($request->expectsJson() || $request->header('Accept') == 'application/json') {
                    return response()->json([
                        'success' => false,
                        'error' => 'Akun Anda tidak aktif. Silakan hubungi admin.'
                    ]);
                }
                
                return back()->withErrors([
                    'email' => 'Akun Anda tidak aktif. Silakan hubungi admin.',
                ])->withInput();
            }
            
            // Tentukan guard dan cookie berdasarkan role
            $guard = $user->role === 'admin' ? 'web_admin' : 'web_user';
            $cookieName = $user->role === 'admin' ? 'admin_session' : 'user_session';
            
            // Pastikan kita menggunakan guard yang benar
            Auth::shouldUse($guard);
            
            // Set session cookie name dan pastikan kita memiliki ID session yang unik
            $uniqueSessionId = $user->id . '_' . $user->role . '_' . time();
            config(['session.cookie' => $cookieName]);
            session()->setId($uniqueSessionId);
            session()->migrate(true);
            
            // Login dengan guard yang sesuai
            Auth::guard($guard)->login($user, $request->boolean('remember'));
            
            // Update last login
            $user->last_login_at = now();
            $user->save();
            
            // Set cookie dengan ID session yang baru
            $cookie = cookie($cookieName, session()->getId(), 60); // 60 menit
            
            // Redirect ke dashboard sesuai role
            $redirectUrl = $user->role === 'admin' ? '/admin/dashboard-admin' : '/user/dashboard-user';
            
            // Log untuk debugging
            Log::debug('Login success', [
                'user_id' => $user->id,
                'role' => $user->role,
                'guard' => $guard,
                'session_id' => session()->getId(),
                'cookie' => $cookieName
            ]);
            
            if ($request->expectsJson() || $request->header('Accept') == 'application/json') {
                return response()->json([
                    'success' => true, 
                    'message' => 'Login berhasil',
                    'redirect' => $redirectUrl
                ])->withCookie($cookie);
            }
            
            return redirect($redirectUrl)->withCookie($cookie);
            
        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->header('Accept') == 'application/json') {
                return response()->json([
                    'success' => false,
                    'error' => collect($e->errors())->first()[0]
                ]);
            }
            
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            
            if ($request->expectsJson() || $request->header('Accept') == 'application/json') {
                return response()->json([
                    'success' => false,
                    'error' => 'Terjadi kesalahan saat login: ' . $e->getMessage()
                ]);
            }
            
            return back()->withErrors(['email' => 'Terjadi kesalahan saat login'])->withInput();
        }
    }

    /**
     * Menangani logout pengguna
     */
    public function logout(Request $request)
    {
        try {
            // Simpan referensi URL yang dipakai untuk mengetahui sumber logout
            $urlSource = $request->url();
            
            // Pastikan kita mengerjakan session yang benar
            $guard = null;
            $cookieName = null;
            
            // Deteksi guard dari URL (lebih akurat)
            if (str_contains($urlSource, '/admin/logout')) {
                $guard = 'web_admin';
                $cookieName = 'admin_session';
                config(['session.cookie' => $cookieName]);
            } elseif (str_contains($urlSource, '/user/logout')) {
                $guard = 'web_user';
                $cookieName = 'user_session';
                config(['session.cookie' => $cookieName]);
            } else {
                // Fallback: deteksi dari user yang aktif
            $user = Auth::user();
                if ($user) {
                    if ($user->role === 'admin') {
                        $guard = 'web_admin';
                        $cookieName = 'admin_session';
                    } else {
                        $guard = 'web_user';
                        $cookieName = 'user_session';
                    }
                    config(['session.cookie' => $cookieName]);
                } else {
                    // Tidak dapat mendeteksi guard
                    Log::warning('Logout dipanggil tanpa user yang aktif dan tanpa URL yang jelas');
                    return redirect()->route('login');
                }
            }
            
            // Log untuk debugging
            Log::debug('Logout process', [
                'url' => $urlSource,
                'guard' => $guard,
                'cookie' => $cookieName,
                'session_id' => session()->getId()
            ]);
            
            // Pastikan kita menggunakan guard yang benar
            Auth::shouldUse($guard);
            
            // Logout dari guard yang spesifik
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                Log::debug('Logging out user', ['user_id' => $user->id, 'role' => $user->role]);
            Auth::guard($guard)->logout();
            }
            
            // Hapus cookie session spesifik
            $cookie = cookie()->forget($cookieName);
            
            // Regenerate CSRF token untuk keamanan
            // TAPI tidak invalidate session secara keseluruhan
            $request->session()->regenerateToken();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Logout berhasil',
                    'guard' => $guard,
                ])->withCookie($cookie);
            }
            
            return redirect()->route('login')->withCookie($cookie);
        } catch (\Exception $e) {
            Log::error('Logout error: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan saat logout'
                ], 500);
            }
            
            return redirect()->route('login');
        }
    }
}
