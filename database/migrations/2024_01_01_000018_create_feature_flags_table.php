<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_flags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('feature_key', 80)->unique();
            $table->string('label', 150);
            $table->text('description')->nullable();
            $table->jsonb('enabled_for_roles')->default('[]');
            $table->jsonb('enabled_for_users')->default('[]');
            $table->boolean('is_globally_enabled')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_flags');
    }
};
