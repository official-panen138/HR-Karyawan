<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employee_id');
            $table->uuid('leave_type_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->smallInteger('total_days');
            $table->text('reason')->nullable();
            $table->string('status')->default('draft');
            $table->boolean('is_emergency')->default(false);
            $table->text('r2_key')->nullable();
            $table->string('contact_during_leave', 100)->nullable();
            $table->text('handover_notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('leave_type_id')->references('id')->on('leave_types');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
