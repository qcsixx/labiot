<?php

namespace App\Console;

use App\Enums\BorrowStatus;
use App\Models\BorrowRequest;
use App\Models\Notification;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Log waktu sistem untuk debugging
        Log::info('Scheduler running at: ' . now()->format('Y-m-d H:i:s'));
        
        // Jalankan command utama pengiriman email pengingat setiap menit
        $schedule->command('borrow:send-reminders')
                ->everyMinute()
                ->timezone('Asia/Jakarta')
                ->appendOutputTo(storage_path('logs/borrow-reminders.log'))
                ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}