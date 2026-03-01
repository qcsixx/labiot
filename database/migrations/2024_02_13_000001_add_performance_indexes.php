<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes to borrow_requests table for better query performance
        Schema::table('borrow_requests', function (Blueprint $table) {
            $table->index('status', 'idx_borrow_requests_status');
            $table->index('user_id', 'idx_borrow_requests_user_id');
            $table->index('item_id', 'idx_borrow_requests_item_id');
            $table->index('created_at', 'idx_borrow_requests_created_at');
            $table->index('return_deadline', 'idx_borrow_requests_return_deadline');

            // Composite indexes for common query patterns
            $table->index(['status', 'user_id'], 'idx_borrow_requests_status_user');
            $table->index(['status', 'return_deadline'], 'idx_borrow_requests_status_deadline');
        });

        // Add indexes to items table
        Schema::table('items', function (Blueprint $table) {
            $table->index('status', 'idx_items_status');
            $table->index('category', 'idx_items_category');
            $table->index('created_at', 'idx_items_created_at');

            // Composite index for filtering available items
            $table->index(['status', 'quantity'], 'idx_items_status_quantity');
        });

        // Add indexes to notifications table
        Schema::table('notifications', function (Blueprint $table) {
            $table->index('user_id', 'idx_notifications_user_id');
            $table->index('created_at', 'idx_notifications_created_at');
        });

        // Add indexes to item_tracking table
        Schema::table('item_tracking', function (Blueprint $table) {
            $table->index('borrow_request_id', 'idx_item_tracking_borrow_request');
            $table->index('tracked_by', 'idx_item_tracking_tracked_by');
            $table->index('tracking_date', 'idx_item_tracking_date');
        });

        // Add indexes to users table
        Schema::table('users', function (Blueprint $table) {
            $table->index('role', 'idx_users_role');
            $table->index('status', 'idx_users_status');
            $table->index('email_verified_at', 'idx_users_email_verified');

            // Composite index for active users query
            $table->index(['role', 'status'], 'idx_users_role_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('borrow_requests', function (Blueprint $table) {
            $table->dropIndex('idx_borrow_requests_status');
            $table->dropIndex('idx_borrow_requests_user_id');
            $table->dropIndex('idx_borrow_requests_item_id');
            $table->dropIndex('idx_borrow_requests_created_at');
            $table->dropIndex('idx_borrow_requests_return_deadline');
            $table->dropIndex('idx_borrow_requests_status_user');
            $table->dropIndex('idx_borrow_requests_status_deadline');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->dropIndex('idx_items_status');
            $table->dropIndex('idx_items_category');
            $table->dropIndex('idx_items_created_at');
            $table->dropIndex('idx_items_status_quantity');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('idx_notifications_user_id');
            $table->dropIndex('idx_notifications_created_at');
        });

        Schema::table('item_tracking', function (Blueprint $table) {
            $table->dropIndex('idx_item_tracking_borrow_request');
            $table->dropIndex('idx_item_tracking_tracked_by');
            $table->dropIndex('idx_item_tracking_date');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_role');
            $table->dropIndex('idx_users_status');
            $table->dropIndex('idx_users_email_verified');
            $table->dropIndex('idx_users_role_status');
        });
    }
};
