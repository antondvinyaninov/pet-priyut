<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('employee_number')->unique(); // Табельный номер
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_name')->nullable();
            $table->string('position'); // Должность
            $table->string('department'); // Отдел
            $table->foreignId('supervisor_id')->nullable()->constrained('employees')->onDelete('set null'); // Руководитель
            $table->date('hire_date'); // Дата приема на работу
            $table->date('termination_date')->nullable(); // Дата увольнения
            $table->string('employment_type')->default('full_time'); // full_time, part_time, contract
            $table->decimal('salary', 10, 2)->nullable(); // Оклад
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('passport_series')->nullable();
            $table->string('passport_number')->nullable();
            $table->string('inn')->nullable(); // ИНН
            $table->string('snils')->nullable(); // СНИЛС
            $table->text('notes')->nullable(); // Примечания
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['department', 'is_active']);
            $table->index(['supervisor_id']);
            $table->index(['employment_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
