<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;
    
    /**
     * Nama tabel yang terkait dengan model ini.
     *
     * @var string
     */
    protected $table = 'notifications';
    
    /**
     * Nama primary key tabel.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    
    /**
     * Kolom yang dapat diisi.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'status',
        'request_id',
        'message',
        'sent_at',
    ];
    
    /**
     * Kolom-kolom yang harus dikonversi ke tipe data tertentu.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sent_at' => 'datetime',
    ];
    
    /**
     * Get the borrow request associated with this notification.
     */
    public function borrowRequest(): BelongsTo
    {
        return $this->belongsTo(BorrowRequest::class, 'request_id', 'request_id');
    }
    
    /**
     * Get user terkait.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
