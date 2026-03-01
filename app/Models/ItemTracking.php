<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class ItemTracking extends Model
{
    use HasFactory;
    
    /**
     * Nama tabel yang terkait dengan model ini.
     *
     * @var string
     */
    protected $table = 'item_tracking';
    
    /**
     * Nama primary key tabel.
     *
     * @var string
     */
    protected $primaryKey = 'tracking_id';
    
    /**
     * Kolom yang dapat diisi.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'borrow_request_id',
        'location',
        'latitude',
        'longitude',
        'notes',
        'photo',
        'tracked_by',
        'tracking_date'
    ];
    
    /**
     * Kolom-kolom yang harus dikonversi ke tipe data tertentu.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tracking_date' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    /**
     * Konfigurasi zona waktu untuk model ini.
     *
     * @var string
     */
    protected $timezone = 'Asia/Jakarta';
    
    /**
     * Format waktu untuk model ini.
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';
    
    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        // Set zona waktu saat membuat atau memperbarui model
        static::creating(function ($model) {
            $model->created_at = now()->setTimezone('Asia/Jakarta');
            $model->updated_at = now()->setTimezone('Asia/Jakarta');
            
            if (!$model->tracking_date) {
                $model->tracking_date = now()->setTimezone('Asia/Jakarta');
            }
        });
        
        static::updating(function ($model) {
            $model->updated_at = now()->setTimezone('Asia/Jakarta');
        });
    }
    
    /**
     * Get permintaan peminjaman terkait.
     */
    public function borrowRequest(): BelongsTo
    {
        return $this->belongsTo(BorrowRequest::class, 'borrow_request_id', 'request_id');
    }

    /**
     * Get user yang melaporkan tracking ini.
     */
    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tracked_by', 'id');
    }
    
    /**
     * Get gambar tracking dengan URL yang benar.
     */
    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            // Log untuk debugging
            Log::debug("Generating photo URL for tracking #{$this->tracking_id}, path: {$this->photo}");
            
            // Check if photo path already has storage/ prefix
            if (strpos($this->photo, 'storage/') === 0) {
                return url($this->photo);
            }
            
            // If photo is in item_tracking directory without storage prefix
            if (strpos($this->photo, 'item_tracking/') === 0) {
                return url('storage/' . $this->photo);
            }
            
            // For backwards compatibility with older paths
            if (strpos($this->photo, 'tracking_photos/') === 0) {
                return url('storage/' . $this->photo);
            }
            
            // For paths that are just filenames
            if (strpos($this->photo, '/') === false) {
                return url('storage/item_tracking/' . $this->photo);
            }
            
            // Default case - assume it's a path relative to storage/app/public
            return url('storage/' . $this->photo);
        }
        
        return null;
    }
}
