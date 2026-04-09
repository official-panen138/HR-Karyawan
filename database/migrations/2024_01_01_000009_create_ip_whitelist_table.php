<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ip_whitelist', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->ipAddress('ip_address');
            $table->string('label', 80);
            $table->uuid('division_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->uuid('added_by');
            $table->timestamps();

            $table->foreign('division_id')->references('id')->on('divisions')->nullOnDelete();
            $table->foreign('added_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ip_whitelist');
    }
};
