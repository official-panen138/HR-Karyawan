<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employee_id');
            $table->uuid('shift_id')->nullable();
            $table->date('date');
            $table->timestamp('check_in_at')->nullable();
            $table->timestamp('check_out_at')->nullable();
            $table->ipAddress('check_in_ip')->nullable();
            $table->ipAddress('check_out_ip')->nullable();
            $table->smallInteger('late_minutes')->default(0);
            $table->smallInteger('effective_minutes')->default(0);
            $table->string('status')->default('absent');
            $table->uuid('leave_request_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('shift_id')->references('id')->on('shifts')->nullOnDelete();
            $table->foreign('leave_request_id')->references('id')->on('leave_requests')->nullOnDelete();
            $table->unique(['employee_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
