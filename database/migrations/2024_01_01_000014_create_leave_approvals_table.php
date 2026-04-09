<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('leave_request_id');
            $table->uuid('approver_id');
            $table->smallInteger('level');
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('acted_at')->nullable();
            $table->timestamp('reminded_at')->nullable();
            $table->bigInteger('telegram_message_id')->nullable();
            $table->string('telegram_chat_id', 30)->nullable();
            $table->timestamps();

            $table->foreign('leave_request_id')->references('id')->on('leave_requests')->cascadeOnDelete();
            $table->foreign('approver_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_approvals');
    }
};
