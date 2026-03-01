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
        Schema::create('item_tracking', function (Blueprint $table) {
            $table->id('tracking_id');
            $table->foreignId('borrow_request_id')->constrained('borrow_requests', 'request_id')->onDelete('cascade');
            $table->string('location');
            $table->text('notes')->nullable(); // menggabungkan lokasi + kondisi + keterangan
            $table->string('photo')->nullable();
            $table->foreignId('tracked_by')->constrained('users', 'id');
            $table->timestamp('tracking_date');
            $table->timestamps();

            // Index untuk kolom yang sering di-query
            $table->index('location');
            $table->index('tracking_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_tracking');
    }
};
