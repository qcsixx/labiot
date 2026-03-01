<?php

namespace App\Enums;

enum BorrowStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case BORROWED = 'borrowed';
    case REJECTED = 'rejected';
    case PENDING_RETURN = 'pending-return';
    case COMPLETED = 'completed';
    case OVERDUE = 'overdue';

    /**
     * Get all values as an array
     *
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get human-readable status name for display
     *
     * @return string
     */
    public function getLabel(): string
    {
        return match($this) {
            self::PENDING => 'Menunggu Persetujuan',
            self::APPROVED => 'Disetujui',
            self::BORROWED => 'Sedang Dipinjam',
            self::REJECTED => 'Ditolak',
            self::PENDING_RETURN => 'Pengajuan Pengembalian',
            self::COMPLETED => 'Selesai',
            self::OVERDUE => 'Melewati Deadline',
        };
    }
} 