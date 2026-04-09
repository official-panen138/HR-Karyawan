<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_type_configs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('division_id');
            $table->uuid('leave_type_id');
            $table->smallInteger('quota_days')->nullable();
            $table->smallInteger('max_concurrent')->default(2);
            $table->smallInteger('approval_levels')->default(3);
            $table->jsonb('blackout_dates')->nullable();
            $table->timestamps();

            $table->foreign('division_id')->references('id')->on('divisions')->cascadeOnDelete();
            $table->foreign('leave_type_id')->references('id')->on('leave_types')->cascadeOnDelete();
            $table->unique(['division_id', 'leave_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_type_configs');
    }
};
