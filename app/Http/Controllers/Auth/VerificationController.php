<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerificationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth')->except(['verify', 'verifySuccess']);
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only(['verify', 'resend']);
    }

    /**
     * Show the email verification notice.
     */
    public function show()
    {
        return view('auth.verify');
    }

    /**
     * Mark the authenticated user's email address as verified.
     */
    public function verify(Request $request)
    {
        // Ambil user berdasarkan id dari URL
        $user = User::find($request->route('id'));
        
        if (!$user) {
            return redirect()->route('verification.notice')->with('error', 'User tidak ditemukan.');
        }

        // Validasi hash dari URL
        if (!hash_equals(sha1($user->email), $request->route('hash'))) {
            return redirect()->route('verification.notice')->with('error', 'Link verifikasi tidak valid.');
        }

        if ($user->hasVerifiedEmail()) {
            // Jika email sudah terverifikasi, tampilkan halaman sukses
            return redirect()->route('verification.success');
        }

        // Verifikasi email
        $user->forceFill([
            'email_verified_at' => now()
        ])->save();

        // Redirect ke halaman sukses
        return redirect()->route('verification.success');
    }

    /**
     * Halaman sukses verifikasi yang akan menutup otomatis
     */
    public function verifySuccess()
    {
        return view('auth.verification-success');
    }

    /**
     * Endpoint untuk memeriksa status verifikasi email
     */
    public function checkVerificationStatus(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'verified' => $user && $user->hasVerifiedEmail()
        ]);
    }

    /**
     * Resend the email verification notification.
     */
    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('user.dashboard-user')->with('success', 'Email Anda sudah terverifikasi.');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Link verifikasi baru telah dikirim ke email Anda.');
    }
} 