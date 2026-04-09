<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_leave_balances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employee_id');
            $table->uuid('leave_type_id');
            $table->smallInteger('year');
            $table->smallInteger('quota_days');
            $table->smallInteger('carry_over_days')->default(0);
            $table->smallInteger('used_days')->default(0);
            $table->smallInteger('pending_days')->default(0);
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('leave_type_id')->references('id')->on('leave_types');
            $table->unique(['employee_id', 'leave_type_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_leave_balances');
    }
};
