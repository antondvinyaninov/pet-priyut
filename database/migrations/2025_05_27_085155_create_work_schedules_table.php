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
        Schema::create('work_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('name'); // Название графика (например, "5/2", "Сменный", "Индивидуальный")
            $table->string('type')->default('standard'); // standard, shift, flexible, individual
            $table->date('start_date'); // Дата начала действия графика
            $table->date('end_date')->nullable(); // Дата окончания (null = бессрочно)
            
            // Стандартный график (пн-пт)
            $table->time('monday_start')->nullable();
            $table->time('monday_end')->nullable();
            $table->time('tuesday_start')->nullable();
            $table->time('tuesday_end')->nullable();
            $table->time('wednesday_start')->nullable();
            $table->time('wednesday_end')->nullable();
            $table->time('thursday_start')->nullable();
            $table->time('thursday_end')->nullable();
            $table->time('friday_start')->nullable();
            $table->time('friday_end')->nullable();
            $table->time('saturday_start')->nullable();
            $table->time('saturday_end')->nullable();
            $table->time('sunday_start')->nullable();
            $table->time('sunday_end')->nullable();
            
            // Время обеда
            $table->time('lunch_start')->nullable();
            $table->time('lunch_end')->nullable();
            
            // Дополнительные параметры
            $table->integer('hours_per_week')->default(40); // Часов в неделю
            $table->integer('hours_per_day')->default(8); // Часов в день
            $table->json('special_dates')->nullable(); // Особые даты (праздники, отпуска и т.д.)
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['employee_id', 'is_active']);
            $table->index(['start_date', 'end_date']);
            $table->index(['type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_schedules');
    }
};
