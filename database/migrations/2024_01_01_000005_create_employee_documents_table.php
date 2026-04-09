<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employee_id');
            $table->string('doc_type');
            $table->string('doc_number', 60)->nullable();
            $table->date('issued_at')->nullable();
            $table->date('expired_at')->nullable();
            $table->text('r2_key')->nullable();
            $table->string('holder')->default('staff');
            $table->string('renewal_status')->default('none');
            $table->text('notes')->nullable();
            $table->timestamp('reminded_at_90')->nullable();
            $table->timestamp('reminded_at_30')->nullable();
            $table->timestamp('reminded_at_7')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
    }
};
