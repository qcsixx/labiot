<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;

class CacheHelper
{
    /**
     * Cache keys untuk berbagai data
     */
    const ADMIN_DASHBOARD_STATS = 'admin.dashboard.stats';
    const ITEMS_AVAILABLE_COUNT = 'items.available.count';
    const ITEMS_AVAILABLE_LIST = 'items.available.list';

    /**
     * Get cache TTL from config
     *
     * @param string $key Cache key name
     * @return int TTL in seconds
     */
    public static function getTTL(string $key): int
    {
        return match($key) {
            self::ADMIN_DASHBOARD_STATS => config('app_constants.cache.ttl.admin_dashboard_stats', 300),
            self::ITEMS_AVAILABLE_COUNT => config('app_constants.cache.ttl.items_available_count', 600),
            self::ITEMS_AVAILABLE_LIST => config('app_constants.cache.ttl.items_available_list', 600),
            default => 300, // Default 5 minutes
        };
    }

    /**
     * Invalidate admin dashboard cache
     * Dipanggil saat ada perubahan pada borrow requests atau items
     */
    public static function invalidateAdminDashboard(): void
    {
        Cache::forget(self::ADMIN_DASHBOARD_STATS);
    }

    /**
     * Invalidate items cache
     * Dipanggil saat ada perubahan pada items (create, update, delete)
     */
    public static function invalidateItems(): void
    {
        Cache::forget(self::ITEMS_AVAILABLE_COUNT);
        Cache::forget(self::ITEMS_AVAILABLE_LIST);
    }

    /**
     * Invalidate all dashboard caches
     * Dipanggil saat ada perubahan signifikan yang mempengaruhi semua dashboard
     */
    public static function invalidateAllDashboards(): void
    {
        self::invalidateAdminDashboard();
        self::invalidateItems();
    }

    /**
     * Clear all application caches
     */
    public static function clearAll(): void
    {
        Cache::flush();
    }
}
