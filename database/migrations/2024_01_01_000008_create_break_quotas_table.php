<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('break_quotas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('division_id')->nullable();
            $table->uuid('shift_id')->nullable();
            $table->string('category');
            $table->smallInteger('quota_minutes');
            $table->smallInteger('global_minutes_limit')->default(60);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('division_id')->references('id')->on('divisions')->nullOnDelete();
            $table->foreign('shift_id')->references('id')->on('shifts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('break_quotas');
    }
};
