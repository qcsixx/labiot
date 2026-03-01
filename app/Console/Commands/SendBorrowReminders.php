<?php

namespace App\Console\Commands;

use App\Mail\ReturnOverdue;
use App\Mail\BorrowStatusNotification;
use App\Models\BorrowRequest;
use App\Enums\BorrowStatus;
use App\Models\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class SendBorrowReminders extends Command
{
    protected $signature = 'borrow:send-reminders';
    protected $description = 'Kirim pengingat email untuk peminjaman barang setiap hari';

    // Batasan pengiriman email per status (dalam jam)
    private $emailFrequencyLimits = [
        'approved' => 24,     // 24 jam sekali
        'borrowed' => 72,     // 3 hari sekali
        'h_minus_one' => 24,  // 24 jam sekali 
        'deadline' => 24,     // 24 jam sekali
        'overdue' => 48,      // 2 hari sekali
        'pending_return' => 48, // 2 hari sekali
    ];

    public function handle()
    {
        try {
            $this->info('Memulai pengiriman pengingat peminjaman...');
            $count = [
                'approved' => 0,
                'borrowed' => 0,
                'h_minus_one' => 0,
                'deadline' => 0,
                'overdue' => 0,
                'pending_return' => 0,
                'skipped' => 0
            ];

            // Ambil semua permintaan peminjaman aktif 
            // (status: APPROVED, BORROWED, OVERDUE, PENDING_RETURN)
            $activeRequests = BorrowRequest::with(['user', 'item'])
                ->whereIn('status', [
                    BorrowStatus::APPROVED,
                    BorrowStatus::BORROWED,
                    BorrowStatus::OVERDUE,
                    BorrowStatus::PENDING_RETURN,
                ])
                ->whereHas('user', function ($query) {
                    $query->whereNotNull('email_verified_at'); // Hanya ke user dengan email terverifikasi
                })
                ->get();

            $this->info("Ditemukan {$activeRequests->count()} peminjaman aktif.");
            
            $today = Carbon::today();

            foreach ($activeRequests as $request) {
                try {
                    $user = $request->user;
                    
                    // Skip jika bukan user normal (admin, dll)
                    if (!$user || !$user->email || $user->role === 'admin') {
                        $this->warn("Peminjaman ID {$request->request_id}: User tidak ditemukan, tidak memiliki email, atau admin.");
                        continue;
                    }

                    // Cek status dan tanggal untuk menentukan jenis email yang dikirim
                    $daysUntilDeadline = $today->diffInDays($request->return_deadline, false);
                    $emailType = '';
                    
                    if ($request->status === BorrowStatus::APPROVED) {
                        // Status Approved: Kirim pengingat untuk mengambil barang
                        $emailType = 'approved';
                        
                    } else if ($request->status === BorrowStatus::BORROWED) {
                        // Status Borrowed
                        if ($daysUntilDeadline == 1) {
                            // H-1: Kirim pengingat bahwa besok waktu pengembalian
                            $emailType = 'h_minus_one';
                        } else if ($daysUntilDeadline == 0) {
                            // Hari H: Kirim pengingat bahwa hari ini batas pengembalian
                            $emailType = 'deadline';
                        } else if ($daysUntilDeadline < 0) {
                            // Status sudah melewati deadline, tapi tidak perlu diupdate di sini
                            // karena sudah ditangani oleh controller secara real-time
                            $emailType = 'overdue';
                        } else {
                            // Hari biasa: Kirim pengingat untuk upload pelacakan
                            $emailType = 'borrowed';
                        }
                        
                    } else if ($request->status === BorrowStatus::OVERDUE) {
                        // Status Overdue: Kirim peringatan harus mengembalikan barang
                        $emailType = 'overdue';
                    } else if ($request->status === BorrowStatus::PENDING_RETURN) {
                        // Status Pending Return: Kirim pengingat untuk menunggu verifikasi
                        $emailType = 'pending_return';
                    }
                    
                    if (!empty($emailType)) {
                        // Cek apakah email untuk permintaan ini dengan tipe ini sudah dikirim
                        $cacheKey = "email_sent_{$request->id}_{$emailType}";
                        
                        if (Cache::has($cacheKey)) {
                            $lastSent = Cache::get($cacheKey);
                            $now = now();
                            
                            // Ambil batasan waktu pengiriman untuk tipe email ini
                            $hourLimit = $this->emailFrequencyLimits[$emailType] ?? 24;
                            
                            // Jika email sudah dikirim dalam interval waktu yang ditentukan, skip
                            if ($now->diffInHours($lastSent) < $hourLimit) {
                                $this->info("Skip email {$emailType} untuk peminjaman ID {$request->id}: sudah dikirim dalam {$hourLimit} jam terakhir");
                                $count['skipped']++;
                                continue;
                            }
                        }
                        
                        // Kirim email
                        $this->sendEmail($user, $emailType, $request);
                        
                        // Simpan timestamp terakhir kali email dikirim untuk peminjaman ini
                        // Cache berlaku sesuai dengan frekuensi yang ditentukan (dalam jam)
                        $hourLimit = $this->emailFrequencyLimits[$emailType] ?? 24;
                        Cache::put($cacheKey, now(), now()->addHours($hourLimit));
                            
                        // Update counter
                        $count[$emailType]++;
                    }
                    
                    // Beri jeda untuk menghindari rate limit
                    sleep(1);
                    
                } catch (\Exception $e) {
                    Log::error("Error mengirim email untuk peminjaman ID {$request->request_id}: " . $e->getMessage());
                    $this->error("Gagal mengirim email ke {$request->user->email}: " . $e->getMessage());
                }
            }

            $summary = "Selesai mengirim email pengingat:\n" .
                "- Approved/Pengambilan: {$count['approved']}\n" .
                "- Borrowed/Pelacakan: {$count['borrowed']}\n" .
                "- H-1 Deadline: {$count['h_minus_one']}\n" .
                "- Hari H Deadline: {$count['deadline']}\n" .
                "- Overdue/Terlambat: {$count['overdue']}\n" .
                "- Pending Return: {$count['pending_return']}\n" .
                "- Dilewati (Sudah terkirim): {$count['skipped']}";
                
            $this->info($summary);
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            Log::error("Error dalam command send-reminders: " . $e->getMessage());
            $this->error("Terjadi kesalahan: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
    
    /**
     * Kirim email berdasarkan tipe menggunakan template HTML yang sudah ada
     */
    private function sendEmail($user, $emailType, $request)
    {
        try {
            // Buat URL untuk detail peminjaman
            $returnUrl = URL::route('user.detail-peminjaman', $request->request_id);
            
            if ($emailType === 'overdue') {
                    // Untuk peringatan keterlambatan, gunakan ReturnOverdue
                    Mail::to($user->email)->send(new ReturnOverdue($request, $user));
            } else {
                    // Untuk status lainnya, gunakan BorrowStatusNotification
                    $emailData = [
                        'itemName' => $request->item->name,
                        'status' => $this->mapEmailTypeToStatus($emailType),
                        'deadline' => $request->return_deadline->format('d/m/Y'),
                        'returnUrl' => $returnUrl,
                    'isLate' => $request->return_deadline < Carbon::today(),
                    'borrowDate' => $request->borrow_date->format('d/m/Y'),
                    'itemQuantity' => $request->quantity,
                    'emailType' => $emailType, // Tambahkan tipe email untuk pengkondisian di template
                    'userName' => $user->name
                    ];
                    
                    Mail::to($user->email)->send(new BorrowStatusNotification($emailData));
            }
            
            $this->info("Email {$emailType} terkirim ke {$user->email} (menggunakan template HTML)");
        } catch (\Exception $e) {
            Log::error("Error dalam pengiriman email: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Memetakan tipe email ke status untuk BorrowStatusNotification
     */
    private function mapEmailTypeToStatus($emailType): string
    {
        return match($emailType) {
            'approved' => 'approved',
            'borrowed' => 'borrowed',
            'h_minus_one' => 'h_minus_one',
            'deadline' => 'deadline',
            'pending_return' => 'pending-return',
            default => $emailType
        };
    }
} 