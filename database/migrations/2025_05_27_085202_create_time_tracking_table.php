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
        Schema::create('time_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('work_date'); // Дата работы
            
            // Время прихода и ухода
            $table->time('clock_in')->nullable(); // Время прихода
            $table->time('clock_out')->nullable(); // Время ухода
            $table->time('lunch_start')->nullable(); // Начало обеда
            $table->time('lunch_end')->nullable(); // Конец обеда
            
            // Расчетные поля
            $table->integer('total_minutes')->default(0); // Общее время в минутах
            $table->integer('work_minutes')->default(0); // Рабочее время (без обеда)
            $table->integer('lunch_minutes')->default(0); // Время обеда
            $table->integer('overtime_minutes')->default(0); // Сверхурочные
            $table->integer('late_minutes')->default(0); // Опоздание
            $table->integer('early_leave_minutes')->default(0); // Ранний уход
            
            // Статус дня
            $table->string('status')->default('present'); // present, absent, sick, vacation, holiday, business_trip
            $table->string('absence_reason')->nullable(); // Причина отсутствия
            
            // Дополнительная информация
            $table->text('notes')->nullable(); // Примечания
            $table->json('breaks')->nullable(); // Дополнительные перерывы
            $table->boolean('is_approved')->default(false); // Подтверждено руководителем
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            
            // Геолокация (опционально)
            $table->decimal('clock_in_lat', 10, 8)->nullable();
            $table->decimal('clock_in_lng', 11, 8)->nullable();
            $table->decimal('clock_out_lat', 10, 8)->nullable();
            $table->decimal('clock_out_lng', 11, 8)->nullable();
            
            $table->timestamps();
            
            $table->unique(['employee_id', 'work_date']);
            $table->index(['work_date']);
            $table->index(['status']);
            $table->index(['is_approved']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_tracking');
    }
};
