<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class WebSchedulerController extends Controller
{
    /**
     * Menjalankan scheduler Laravel melalui HTTP request
     * Method ini akan dipanggil secara otomatis oleh JavaScript di frontend
     */
    public function runScheduler()
    {
        try {
            // Cek apakah ini adalah waktu untuk menjalankan pengiriman email (jam 7 pagi)
            $shouldSendEmail = false;
            $now = now();
            
            // Buat key cache berdasarkan tanggal hari ini (YYYY-MM-DD)
            $today = $now->format('Y-m-d');
            $cacheKey = 'scheduler_last_run_' . $today;
            
            // Jika sekarang jam 7 pagi (07:00-07:59) dan hari ini belum mengirim email
            if ($now->hour === 7 && !Cache::has($cacheKey)) {
                $shouldSendEmail = true;
                Log::info('Waktu pengiriman email harian (07:00-07:59) terdeteksi');
            }
            
            // Jika bukan waktunya, kembalikan respons tanpa menjalankan command
            if (!$shouldSendEmail) {
                Log::info('Scheduler skip: Bukan jam 7 pagi atau sudah dijalankan hari ini');
                return response()->json([
                    'success' => true, 
                    'message' => 'Tidak perlu mengirim email, bukan jam 7 atau sudah dijalankan',
                    'current_time' => $now->format('H:i:s'),
                    'next_run' => 'Besok jam 07:00'
                ]);
            }
            
            // Jika sudah waktunya, jalankan pengiriman email
            Log::info('Scheduler dijalankan via WebSchedulerController (jam 7 pagi)');
            $output = Artisan::call('borrow:send-reminders');
            
            // Simpan timestamp terakhir kali dijalankan (seharian)
            Cache::put($cacheKey, now(), Carbon::tomorrow());
            
            // Log untuk pencatatan
            Log::info('Email pengingat berhasil dikirim (jam 7 pagi)', [
                'artisan_output' => Artisan::output(),
                'time' => now()->toDateTimeString(),
                'cache_key' => $cacheKey
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Email reminders sent successfully',
                'output' => Artisan::output(),
                'timestamp' => now()->toDateTimeString()
            ]);
        } catch (\Exception $e) {
            Log::error('Web scheduler failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Scheduler execution failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 