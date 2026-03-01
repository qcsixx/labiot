<?php

namespace App\Helpers;

use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class NotificationHelper
{
    /**
     * Mengembalikan gaya CSS sesuai status notifikasi
     *
     * @param string $status Status notifikasi
     * @return array Array berisi kelas CSS untuk badge, teks, latar belakang ikon, dan jenis ikon
     */
    public static function getStyleByStatus($status)
    {
        return match($status) {
            'pending' => [
                'badge' => 'bg-blue-100 text-blue-800',
                'text' => 'text-blue-700',
                'iconBg' => 'bg-blue-50',
                'icon' => 'clock',
                'badgeText' => 'MENUNGGU',
            ],
            'approved' => [
                'badge' => 'bg-green-100 text-green-800',
                'text' => 'text-green-700',
                'iconBg' => 'bg-green-50',
                'icon' => 'check-circle',
                'badgeText' => 'DISETUJUI',
            ],
            'borrowed' => [
                'badge' => 'bg-cyan-100 text-cyan-800',
                'text' => 'text-cyan-700',
                'iconBg' => 'bg-cyan-50',
                'icon' => 'box',
                'badgeText' => 'DIPINJAM',
            ],
            'overdue' => [
                'badge' => 'bg-red-100 text-red-800',
                'text' => 'text-red-700',
                'iconBg' => 'bg-red-50',
                'icon' => 'alert-triangle',
                'badgeText' => 'TERLAMBAT',
            ],
            'pending-return' => [
                'badge' => 'bg-amber-100 text-amber-800',
                'text' => 'text-amber-700',
                'iconBg' => 'bg-amber-50',
                'icon' => 'rotate-ccw',
                'badgeText' => 'PENGEMBALIAN',
            ],
            'completed' => [
                'badge' => 'bg-slate-100 text-slate-800',
                'text' => 'text-slate-700',
                'iconBg' => 'bg-slate-50',
                'icon' => 'check',
                'badgeText' => 'SELESAI',
            ],
            'rejected' => [
                'badge' => 'bg-gray-100 text-gray-800',
                'text' => 'text-gray-700',
                'iconBg' => 'bg-gray-50',
                'icon' => 'x-circle',
                'badgeText' => 'DITOLAK',
            ],
            default => [
                'badge' => 'bg-blue-100 text-blue-800',
                'text' => 'text-blue-700',
                'iconBg' => 'bg-blue-50',
                'icon' => 'bell',
                'badgeText' => 'INFO',
            ],
        };
    }

    /**
     * Mendapatkan warna berdasarkan status peminjaman untuk tampilan yang konsisten
     *
     * @param string $status Status peminjaman
     * @param bool $isLate Flag apakah peminjaman terlambat
     * @param bool $isToday Flag apakah hari ini deadline
     * @param string $message Pesan notifikasi
     * @return array Array berisi kelas CSS untuk tampilan konsisten
     */
    public static function getBorrowStatusStyle($status, $isLate = false, $isToday = false, $message = '')
    {
        // Periksa isi pesan notifikasi untuk menentukan warna yang tepat
        // Gunakan status overdue hanya untuk notifikasi overdue asli
        if ($status === 'overdue') {
            return [
                'badge' => 'bg-red-100 text-red-800',
                'text' => 'text-red-700',
                'iconBg' => 'bg-red-50',
                'icon' => 'alert-triangle',
                'badgeText' => 'TERLAMBAT',
                'isUrgent' => true,
            ];
        }

        // Untuk notifikasi pengingat deadline hari ini, gunakan warna kuning/amber
        if ($status === 'borrowed' && $message && strpos($message, 'Hari ini peminjaman') !== false) {
            return [
                'badge' => 'bg-amber-100 text-amber-800',
                'text' => 'text-amber-700',
                'iconBg' => 'bg-amber-50',
                'icon' => 'alarm-clock',
                'badgeText' => 'DEADLINE HARI INI',
                'isUrgent' => true,
            ];
        }

        // Status standar sesuai dengan nilai status
        return self::getStyleByStatus($status);
    }

    /**
     * Create a borrow request notification
     *
     * @param int $userId User ID to receive notification
     * @param string $message Notification message
     * @param string $status Borrow status
     * @param int|null $borrowRequestId Related borrow request ID
     * @return \App\Models\Notification|null
     */
    public static function createBorrowNotification($userId, $message, $status, $borrowRequestId = null)
    {
        try {
            return Notification::create([
                'user_id' => $userId,
                'message' => $message,
                'status' => $status,
                'borrow_request_id' => $borrowRequestId,
                'is_read' => false,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create borrow notification', [
                'user_id' => $userId,
                'status' => $status,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Create a return reminder notification
     *
     * @param int $userId User ID to receive notification
     * @param string $message Notification message
     * @param int $borrowRequestId Related borrow request ID
     * @return \App\Models\Notification|null
     */
    public static function createReturnReminder($userId, $message, $borrowRequestId)
    {
        return self::createBorrowNotification($userId, $message, 'borrowed', $borrowRequestId);
    }

    /**
     * Create an overdue notification
     *
     * @param int $userId User ID to receive notification
     * @param string $message Notification message
     * @param int $borrowRequestId Related borrow request ID
     * @return \App\Models\Notification|null
     */
    public static function createOverdueNotification($userId, $message, $borrowRequestId)
    {
        return self::createBorrowNotification($userId, $message, 'overdue', $borrowRequestId);
    }

    /**
     * Create a deadline today notification
     *
     * @param int $userId User ID to receive notification
     * @param string $message Notification message
     * @param int $borrowRequestId Related borrow request ID
     * @return \App\Models\Notification|null
     */
    public static function createDeadlineNotification($userId, $message, $borrowRequestId)
    {
        return self::createBorrowNotification($userId, $message, 'borrowed', $borrowRequestId);
    }

    /**
     * Create an approval notification
     *
     * @param int $userId User ID to receive notification
     * @param string $itemName Item name
     * @param int $borrowRequestId Related borrow request ID
     * @return \App\Models\Notification|null
     */
    public static function createApprovalNotification($userId, $itemName, $borrowRequestId)
    {
        $message = __('messages.notification.borrow_approved') . ": {$itemName}";
        return self::createBorrowNotification($userId, $message, 'approved', $borrowRequestId);
    }

    /**
     * Create a rejection notification
     *
     * @param int $userId User ID to receive notification
     * @param string $itemName Item name
     * @param int $borrowRequestId Related borrow request ID
     * @return \App\Models\Notification|null
     */
    public static function createRejectionNotification($userId, $itemName, $borrowRequestId)
    {
        $message = __('messages.notification.borrow_rejected') . ": {$itemName}";
        return self::createBorrowNotification($userId, $message, 'rejected', $borrowRequestId);
    }
}
