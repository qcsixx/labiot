<?php

namespace App\Models;

use App\Enums\BorrowStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class BorrowRequest extends Model
{
    use HasFactory;
    
    /**
     * Nama tabel yang terkait dengan model ini.
     *
     * @var string
     */
    protected $table = 'borrow_requests';
    
    /**
     * Nama primary key tabel.
     *
     * @var string
     */
    protected $primaryKey = 'request_id';
    
    /**
     * Kolom yang dapat diisi.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'item_id',
        'quantity',
        'borrow_date',
        'return_deadline',
        'status',
        'approval_date',
        'borrowed_at',
        'purpose',
        'return_date',
        'return_status',
        'notes',
    ];
    
    /**
     * Kolom-kolom yang harus dikonversi ke tipe data tertentu.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'borrow_date' => 'date',
        'return_deadline' => 'date',
        'approval_date' => 'date',
        'borrowed_at' => 'datetime',
        'return_date' => 'date',
        'status' => BorrowStatus::class,
    ];
    
    /**
     * Get user yang meminjam.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    /**
     * Get item yang dipinjam.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }
    
    /**
     * Get notifikasi untuk peminjaman ini.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'request_id', 'request_id');
    }
    
    /**
     * Get pelacakan item untuk peminjaman ini.
     */
    public function itemTrackings(): HasMany
    {
        return $this->hasMany(ItemTracking::class, 'borrow_request_id', 'request_id');
    }
    
    /**
     * Get admin yang menyetujui peminjaman.
     */
    public function approvalAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approval_admin_id');
    }
    
    /**
     * Get admin yang menangani pengembalian.
     */
    public function returnAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'return_admin_id');
    }
    
    /**
     * Send notification based on current status
     * 
     * @param string $message Custom message (optional)
     * @return Notification
     */
    public function sendStatusNotification(string $message = null): Notification
    {
        if (!$message) {
            $message = $this->generateStatusMessage();
        }
        
        return Notification::create([
            'user_id' => $this->user_id,
            'status' => $this->status->value,
            'request_id' => $this->request_id,
            'message' => $message,
        ]);
    }
    
    /**
     * Generate message based on current status
     * 
     * @return string
     */
    protected function generateStatusMessage(): string
    {
        $itemName = $this->item->name;
        
        return match($this->status) {
            BorrowStatus::PENDING => "Permintaan peminjaman {$itemName} telah diajukan dan menunggu persetujuan",
            BorrowStatus::APPROVED => "Permintaan peminjaman {$itemName} telah disetujui",
            BorrowStatus::BORROWED => "Barang {$itemName} telah diambil dan sedang dalam peminjaman",
            BorrowStatus::REJECTED => "Permintaan peminjaman {$itemName} telah ditolak",
            BorrowStatus::PENDING_RETURN => $this->isLate()
                ? "Pengajuan pengembalian {$itemName} telah dibuat dan menunggu verifikasi. Anda terlambat mengembalikan barang ini"
                : "Pengajuan pengembalian {$itemName} telah dibuat dan menunggu verifikasi",
            BorrowStatus::OVERDUE => "Peminjaman {$itemName} telah melewati batas waktu pengembalian ({$this->return_deadline->format('d/m/Y')}). Segera kembalikan untuk menghindari sanksi",
            BorrowStatus::COMPLETED => $this->return_status === 'late' 
                ? "Peminjaman {$itemName} telah selesai dengan keterlambatan. Terima kasih atas kerjasamanya" 
                : "Peminjaman {$itemName} telah selesai. Terima kasih atas kerjasamanya",
        };
    }
    
    /**
     * Send notification for specific event
     * 
     * @param string $event
     * @param string $message
     * @return Notification
     */
    public function sendEventNotification(string $event, string $message = null): Notification
    {
        if (!$message) {
            $message = $this->generateEventMessage($event);
        }
        
        // Tentukan status notifikasi berdasarkan status peminjaman saat ini
        $status = $this->status->value;
        
        return Notification::create([
            'user_id' => $this->user_id,
            'status' => $status,
            'request_id' => $this->request_id,
            'message' => $message,
        ]);
    }
    
    /**
     * Generate message for specific event
     * 
     * @param string $event
     * @return string
     */
    protected function generateEventMessage(string $event): string
    {
        $itemName = $this->item->name;
        $returnDeadline = $this->return_deadline->format('d/m/Y');
        
        return match($event) {
            'item_borrowed' => "Barang {$itemName} telah diambil dan sedang digunakan",
            'return_reminder' => "Pengingat: Barang {$itemName} harus dikembalikan paling lambat besok ({$returnDeadline})",
            'return_overdue' => "Barang {$itemName} telah melewati batas waktu pengembalian ({$returnDeadline})",
            'return_rejected' => "Pengajuan pengembalian barang {$itemName} ditolak oleh admin. Mohon periksa catatan pada detail peminjaman",
            'completed_late' => "Peminjaman {$itemName} telah selesai dengan keterlambatan",
            default => "Notifikasi terkait peminjaman {$itemName}",
        };
    }
    
    /**
     * Helper method untuk mendapatkan status sebagai string
     * 
     * @return string
     */
    public function getStatusAsString(): string
    {
        return $this->status->value;
    }
    
    /**
     * Helper method untuk menampilkan status yang mudah dibaca
     * 
     * @return string
     */
    public function getStatusLabel(): string
    {
        $statusMap = [
            'pending' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'borrowed' => 'Sedang Dipinjam',
            'pending-return' => 'Pengajuan Pengembalian',
            'completed' => 'Selesai',
            'rejected' => 'Ditolak',
            'overdue' => 'Melewati Deadline'
        ];
        
        $status = is_object($this->status) ? $this->status->value : $this->status;
        
        return $statusMap[$status] ?? 'Status Tidak Diketahui';
    }
    
    /**
     * Helper method untuk mengecek apakah peminjaman terlambat
     * 
     * @return bool
     */
    public function isLate(): bool
    {
        // Jika sudah selesai, cek dari return_status
        if (is_object($this->status) && $this->status->value === 'completed') {
            return $this->return_status === 'late';
        } 
        
        // Jika pending-return, cek dari return_status juga
        if (is_object($this->status) && $this->status->value === 'pending-return') {
            return $this->return_status === 'late';
        }
        
        // Jika status overdue, otomatis terlambat
        if (is_object($this->status) && $this->status->value === 'overdue') {
            return true;
        }
        
        // Jika belum selesai, cek dari tanggal deadline
        // Terlambat hanya jika hari ini > deadline (setelah lewat hari H)
        $today = now()->startOfDay();
        $deadline = $this->return_deadline->startOfDay();
        
        return $today > $deadline; // Hari H tidak dianggap terlambat
    }
    
    /**
     * Helper method untuk mengecek apakah peminjaman tinggal 1 hari lagi deadline
     * 
     * @return bool
     */
    public function isOneDay(): bool
    {
        // Jika status bukan borrowed, return false
        if ($this->status !== BorrowStatus::BORROWED) {
            return false;
        }
        
        // Hitung selisih hari antara sekarang dengan deadline
        $today = now()->startOfDay();
        $deadline = $this->return_deadline->endOfDay();
        
        // Jika selisih hari adalah 1, maka tinggal 1 hari lagi
        return $today->diffInDays($deadline) === 1;
    }
    
    /**
     * Helper method untuk mengecek apakah hari ini adalah tanggal deadline peminjaman
     * 
     * @return bool
     */
    public function isDeadlineToday(): bool
    {
        // Jika status bukan borrowed, return false
        if ($this->status !== BorrowStatus::BORROWED) {
            return false;
        }
        
        // Bandingkan tanggal hari ini dengan deadline
        $today = now()->startOfDay();
        $deadline = $this->return_deadline->startOfDay();
        
        // Return true jika tanggal deadline sama dengan hari ini
        return $today->equalTo($deadline);
    }
}
