<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('break_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('attendance_id');
            $table->string('category');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->smallInteger('duration_minutes')->nullable();
            $table->ipAddress('started_ip')->nullable();
            $table->timestamps();

            $table->foreign('attendance_id')->references('id')->on('attendances')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('break_logs');
    }
};
