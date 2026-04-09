<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->string('code', 20)->unique();
            $table->uuid('division_id')->nullable();
            $table->smallInteger('level')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('division_id')->references('id')->on('divisions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
