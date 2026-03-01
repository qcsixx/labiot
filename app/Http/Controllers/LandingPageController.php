<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class LandingPageController extends Controller
{
    /**
     * Menampilkan halaman landing page dan memeriksa apakah perlu mengirim email
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        // Periksa apakah perlu mengirim email
        $this->checkAndSendEmails();
        
        // Tetap tampilkan halaman landing seperti biasa
        return view('landing');
    }
    
    /**
     * Memeriksa kondisi dan mengirim email jika diperlukan
     * Dengan prioritas: jam 7 pagi atau kunjungan pertama hari ini
     *
     * @return void
     */
    private function checkAndSendEmails()
    {
        try {
            $now = now();
            $today = $now->format('Y-m-d');
            $cacheKey = 'email_last_sent_date';
            
            // Prioritas 1: Jam 7 pagi (07:00-07:59)
            $isPriorityHour = ($now->hour === 7);
            
            // Prioritas 2: Baru hari ini dan belum kirim
            $lastSentDate = Cache::get($cacheKey);
            $isNewDay = ($lastSentDate !== $today);
            
            // Jika jam 7 pagi ATAU hari baru (dan belum kirim hari ini)
            // Maka kirim email
            if ($isPriorityHour || $isNewDay) {
                // Periksa sekali lagi menggunakan cache dengan format YYYY-MM-DD
                // untuk memastikan email belum dikirim hari ini
                if (!Cache::has('email_sent_' . $today)) {
                    // Jalankan command dengan quiet mode
                    Log::info('Menjalankan pengiriman email harian dari Landing Page', [
                        'time' => $now->toDateTimeString(),
                        'isPriorityHour' => $isPriorityHour,
                        'isNewDay' => $isNewDay
                    ]);
                    
                    // Kirim email dengan command Artisan
                    Artisan::call('borrow:send-reminders');
                    
                    // Catat bahwa email sudah dikirim hari ini
                    // Cache berlaku hingga tengah malam
                    $endOfDay = Carbon::now()->endOfDay();
                    Cache::put('email_sent_' . $today, true, $endOfDay);
                    Cache::put($cacheKey, $today, $endOfDay);
                    
                    // Simpan timestamp pengiriman untuk API
                    Cache::put('email_last_sent_timestamp', $now->toDateTimeString(), $endOfDay);
                    
                    Log::info('Berhasil mengirim email harian dari Landing Page', [
                        'output' => Artisan::output()
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error saat memeriksa pengiriman email dari Landing Page', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
} 