<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;
    
    /**
     * Nama tabel yang terkait dengan model ini.
     *
     * @var string
     */
    protected $table = 'items';
    
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
        'name',
        'category',
        'quantity',
        'image',
        'status',
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
     * Kolom yang akan dikonversi ke Carbon instance.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];
    
    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        // Set zona waktu saat membuat atau memperbarui model
        static::creating(function ($model) {
            $model->created_at = now()->setTimezone('Asia/Jakarta');
        });
        
        static::updating(function ($model) {
            $model->updated_at = now()->setTimezone('Asia/Jakarta');
            \Log::debug("Item updating: ID={$model->id}, Old quantity={$model->getOriginal('quantity')}, New quantity={$model->quantity}");
        });
    }
    
    /**
     * Get URL gambar item yang benar.
     */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            // Log untuk debugging
            \Log::debug("Generating image URL for item #{$this->id}, path: {$this->image}");
            
            // Check if image path already has storage/ prefix
            if (strpos($this->image, 'storage/') === 0) {
                return url($this->image);
            }
            
            // If image is in items directory without storage prefix
            if (strpos($this->image, 'items/') === 0) {
                return url('storage/' . $this->image);
            }
            
            // For paths that are just filenames
            if (strpos($this->image, '/') === false) {
                return url('storage/items/' . $this->image);
            }
            
            // Default case - assume it's a path relative to storage/app/public
            return url('storage/' . $this->image);
        }
        
        // Return default image if no image is found
        return url('images/default-item.jpg');
    }
    
    /**
     * Get permintaan peminjaman untuk item ini.
     */
    public function borrowRequests(): HasMany
    {
        return $this->hasMany(BorrowRequest::class, 'item_id', 'id');
    }
}
