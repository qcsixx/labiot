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
        Schema::create('borrow_requests', function (Blueprint $table) {
            $table->id('request_id');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->integer('quantity');
            $table->date('borrow_date');
            $table->date('return_deadline');
            $table->enum('status', ['pending', 'approved', 'borrowed', 'pending-return', 'completed', 'rejected', 'overdue'])->default('pending');
            $table->date('approval_date')->nullable();
            $table->timestamp('borrowed_at')->nullable();
            $table->text('purpose');
            $table->date('return_date')->nullable();
            $table->enum('return_status', ['returned', 'late'])->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrow_requests');
    }
}; 