<?php

namespace App\Helpers;

use App\Enums\BorrowStatus;

class StatusHelper
{
    /**
     * Get badge styling for borrow request status
     *
     * @param string $status
     * @param bool $isLate
     * @param bool $isDeadlineToday
     * @return array
     */
    public static function getBadgeClass(string $status, bool $isLate = false, bool $isDeadlineToday = false): array
    {
        // Handle overdue or late returns
        if ($status === BorrowStatus::OVERDUE->value || ($status === BorrowStatus::PENDING_RETURN->value && $isLate)) {
            return [
                'badge' => 'bg-red-100 text-red-800 border border-red-200',
                'icon' => 'alert-circle',
                'iconClass' => 'text-red-500',
                'iconBg' => 'bg-red-50',
                'text' => 'text-red-700',
                'isUrgent' => true,
            ];
        }

        // Handle deadline today
        if ($isDeadlineToday && in_array($status, [BorrowStatus::BORROWED->value, BorrowStatus::PENDING_RETURN->value])) {
            return [
                'badge' => 'bg-orange-100 text-orange-800 border border-orange-200',
                'icon' => 'calendar-clock',
                'iconClass' => 'text-orange-500',
                'iconBg' => 'bg-orange-50',
                'text' => 'text-orange-700',
                'isUrgent' => true,
            ];
        }

        // Status-based styling
        return match($status) {
            BorrowStatus::PENDING->value => [
                'badge' => 'bg-yellow-100 text-yellow-800 border border-yellow-200',
                'icon' => 'clock',
                'iconClass' => 'text-yellow-500',
                'iconBg' => 'bg-yellow-50',
                'text' => 'text-yellow-700',
                'isUrgent' => false,
            ],
            BorrowStatus::APPROVED->value => [
                'badge' => 'bg-green-100 text-green-800 border border-green-200',
                'icon' => 'check-circle',
                'iconClass' => 'text-green-500',
                'iconBg' => 'bg-green-50',
                'text' => 'text-green-700',
                'isUrgent' => false,
            ],
            BorrowStatus::BORROWED->value => [
                'badge' => 'bg-cyan-100 text-cyan-800 border border-cyan-200',
                'icon' => 'package-check',
                'iconClass' => 'text-cyan-500',
                'iconBg' => 'bg-cyan-50',
                'text' => 'text-cyan-700',
                'isUrgent' => false,
            ],
            BorrowStatus::PENDING_RETURN->value => [
                'badge' => 'bg-amber-100 text-amber-800 border border-amber-200',
                'icon' => 'hourglass',
                'iconClass' => 'text-amber-500',
                'iconBg' => 'bg-amber-50',
                'text' => 'text-amber-700',
                'isUrgent' => false,
            ],
            BorrowStatus::COMPLETED->value => [
                'badge' => 'bg-gray-100 text-gray-800 border border-gray-200',
                'icon' => 'check-circle-2',
                'iconClass' => 'text-gray-500',
                'iconBg' => 'bg-gray-50',
                'text' => 'text-gray-700',
                'isUrgent' => false,
            ],
            BorrowStatus::REJECTED->value => [
                'badge' => 'bg-red-100 text-red-800 border border-red-200',
                'icon' => 'x-circle',
                'iconClass' => 'text-red-500',
                'iconBg' => 'bg-red-50',
                'text' => 'text-red-700',
                'isUrgent' => false,
            ],
            default => [
                'badge' => 'bg-blue-100 text-blue-800 border border-blue-200',
                'icon' => 'info',
                'iconClass' => 'text-blue-500',
                'iconBg' => 'bg-blue-50',
                'text' => 'text-blue-700',
                'isUrgent' => false,
            ],
        };
    }

    /**
     * Get item status badge styling
     *
     * @param string $status
     * @return array
     */
    public static function getItemStatusBadge(string $status): array
    {
        return match($status) {
            'available' => [
                'badge' => 'bg-green-100 text-green-800 border border-green-200',
                'icon' => 'check-circle',
                'iconClass' => 'text-green-500',
                'label' => 'Tersedia',
            ],
            'maintenance' => [
                'badge' => 'bg-orange-100 text-orange-800 border border-orange-200',
                'icon' => 'wrench',
                'iconClass' => 'text-orange-500',
                'label' => 'Maintenance',
            ],
            'unavailable' => [
                'badge' => 'bg-red-100 text-red-800 border border-red-200',
                'icon' => 'x-circle',
                'iconClass' => 'text-red-500',
                'label' => 'Tidak Tersedia',
            ],
            default => [
                'badge' => 'bg-gray-100 text-gray-800 border border-gray-200',
                'icon' => 'help-circle',
                'iconClass' => 'text-gray-500',
                'label' => 'Unknown',
            ],
        };
    }
}
