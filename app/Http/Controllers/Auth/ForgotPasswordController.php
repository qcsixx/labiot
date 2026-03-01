<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ForgotPasswordController extends Controller
{
    /**
     * Menampilkan form lupa password.
     */
    public function showLinkRequestForm()
    {
        Log::debug('Menampilkan form lupa password, status:', ['has_status' => session()->has('status')]);
        return view('auth.lupa-password'); // Sesuaikan dengan lokasi file di views/auth
    }
    
    /**
     * Menampilkan halaman sukses pengiriman email.
     */
    public function showResetLinkSentPage()
    {
        return view('auth.password-reset-success');
    }
    
    /**
     * Mengirim email reset password.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $status = Password::sendResetLink($request->only('email'));
        
        Log::debug('Password reset status:', ['status' => $status]);
        
        if ($status === Password::RESET_LINK_SENT) {
            Log::debug('Redirect ke halaman sukses');
            // Redirect ke halaman sukses terpisah
            return redirect()->route('password.sent');
        } else {
            Log::debug('Form tetap muncul dengan error:', ['error' => __($status)]);
            return back()->withErrors(['email' => __($status)]);
        }
    }

    /**
     * Menampilkan form reset password.
     */
    public function showResetForm(Request $request, $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email
        ]);
    }

    /**
     * Proses reset password.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }
}
