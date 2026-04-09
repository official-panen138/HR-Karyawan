<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 30)->unique();
            $table->string('name', 80);
            $table->smallInteger('default_quota_days')->nullable();
            $table->boolean('is_paid')->default(true);
            $table->boolean('requires_document')->default(false);
            $table->smallInteger('document_deadline_hours')->default(24);
            $table->boolean('allow_carry_over')->default(false);
            $table->smallInteger('max_carry_over_days')->default(0);
            $table->smallInteger('min_working_months')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};
