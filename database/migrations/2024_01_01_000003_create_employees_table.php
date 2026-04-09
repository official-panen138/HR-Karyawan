<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('full_name', 150);
            $table->string('religion')->default('islam');
            $table->date('birth_date');
            $table->string('bank_account', 30)->nullable();
            $table->string('bank_name', 50)->nullable();
            $table->date('joined_at');
            $table->decimal('initial_salary', 15, 2)->default(0);
            $table->decimal('current_salary', 15, 2)->default(0);
            $table->uuid('position_id');
            $table->uuid('division_id');
            $table->string('status')->default('active');
            $table->text('photo_path')->nullable();
            $table->timestamps();

            $table->foreign('position_id')->references('id')->on('positions');
            $table->foreign('division_id')->references('id')->on('divisions');
        });

        // Add FK from users to employees
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
        });
        Schema::dropIfExists('employees');
    }
};
