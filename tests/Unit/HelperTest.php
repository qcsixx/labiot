<?php

namespace Tests\Unit;

use App\Helpers\StatusHelper;
use App\Helpers\CacheHelper;
use App\Helpers\NotificationHelper;
use App\Enums\BorrowStatus;
use Illuminate\Support\Facades\Config;

class HelperTest extends \Tests\TestCase
{
    /**
     * Test StatusHelper returns correct badge classes.
     */
    public function test_status_helper_returns_correct_badge()
    {
        // Test Pending Status
        $result = StatusHelper::getBadgeClass('pending');
        $this->assertStringContainsString('bg-yellow-100', $result['badge']);
        $this->assertEquals('clock', $result['icon']);

        // Test Overdue Status
        $result = StatusHelper::getBadgeClass('overdue');
        $this->assertStringContainsString('bg-red-100', $result['badge']);
        $this->assertTrue($result['isUrgent']);

        // Test Late Return
        $result = StatusHelper::getBadgeClass('pending-return', true);
        $this->assertStringContainsString('bg-red-100', $result['badge']); // Should be red if late
    }

    /**
     * Test CacheHelper retrieves TTL from config or default.
     */
    public function test_cache_helper_retrieves_ttl()
    {
        // Mock config
        Config::set('app_constants.cache.ttl.admin_dashboard_stats', 400);

        $ttl = CacheHelper::getTTL(CacheHelper::ADMIN_DASHBOARD_STATS);
        $this->assertEquals(400, $ttl);

        // Test default fallback (undefined key)
        $defaultTtl = CacheHelper::getTTL('unknown_key');
        $this->assertEquals(300, $defaultTtl);
    }

    /**
     * Test NotificationHelper styling logic.
     */
    public function test_notification_helper_styling()
    {
        // Test Approved Style
        $style = NotificationHelper::getStyleByStatus('approved');
        $this->assertEquals('bg-green-100 text-green-800', $style['badge']);
        $this->assertEquals('DISETUJUI', $style['badgeText']);

        // Test Rejected Style
        $style = NotificationHelper::getStyleByStatus('rejected');
        $this->assertEquals('bg-gray-100 text-gray-800', $style['badge']);
        $this->assertEquals('x-circle', $style['icon']);
    }
}
