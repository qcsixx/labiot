<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ApiController extends Controller
{
    /**
     * Get email sending status for today
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEmailStatus()
    {
        $today = now()->format('Y-m-d');
        $cacheKey = 'email_sent_' . $today;
        $sentToday = Cache::has($cacheKey);
        $nextSendTime = null;
        
        if ($sentToday) {
            // Email sudah dikirim hari ini
            $nextSendTime = Carbon::tomorrow()->setHour(7)->setMinute(0)->setSecond(0);
        } else {
            // Email belum dikirim hari ini
            $now = now();
            if ($now->hour < 7) {
                // Sebelum jam 7, kirim nanti hari ini jam 7
                $nextSendTime = Carbon::today()->setHour(7)->setMinute(0)->setSecond(0);
            } else {
                // Setelah jam 7, kirim besok
                $nextSendTime = Carbon::tomorrow()->setHour(7)->setMinute(0)->setSecond(0);
            }
        }
        
        return response()->json([
            'sent_today' => $sentToday,
            'sent_at' => $sentToday ? Cache::get('email_last_sent_timestamp') : null,
            'next_send' => $nextSendTime->toDateTimeString(),
            'next_send_human' => $nextSendTime->diffForHumans(),
            'current_time' => now()->toDateTimeString(),
        ]);
    }
} 