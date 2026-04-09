<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('division_id')->nullable();
            $table->string('name', 50);
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('crosses_midnight')->default(false);
            $table->string('color', 7)->default('#3B82F6');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('division_id')->references('id')->on('divisions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
