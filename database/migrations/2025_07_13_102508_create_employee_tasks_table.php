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
        Schema::create('employee_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Заголовок задачи
            $table->text('description')->nullable(); // Описание задачи
            $table->string('priority')->default('medium'); // Приоритет: low, medium, high, urgent
            $table->string('status')->default('pending'); // Статус: pending, in_progress, completed, cancelled
            $table->string('type')->default('general'); // Тип задачи: general, osvv, animal_care, administrative
            
            // Связи с пользователями
            $table->foreignId('assigned_to')->constrained('users')->onDelete('cascade'); // Кому назначена
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // Кто создал
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null'); // Кто назначил
            
            // Временные рамки
            $table->datetime('due_date')->nullable(); // Срок выполнения
            $table->datetime('started_at')->nullable(); // Время начала работы
            $table->datetime('completed_at')->nullable(); // Время завершения
            $table->integer('estimated_hours')->nullable(); // Оценка времени в часах
            $table->integer('actual_hours')->nullable(); // Фактическое время в часах
            
            // Дополнительные данные
            $table->text('notes')->nullable(); // Заметки исполнителя
            $table->text('completion_notes')->nullable(); // Заметки о завершении
            $table->json('attachments')->nullable(); // Прикрепленные файлы
            $table->json('tags')->nullable(); // Теги для категоризации
            
            // Связи с другими сущностями (опционально)
            $table->foreignId('osvv_request_id')->nullable()->constrained('osvv_requests')->onDelete('set null');
            $table->foreignId('animal_id')->nullable()->constrained('animals')->onDelete('set null');
            $table->foreignId('employee_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->foreignId('departure_plan_id')->nullable()->constrained('departure_plans')->onDelete('set null');
            
            // Метки времени
            $table->timestamps();
            
            // Индексы
            $table->index(['assigned_to', 'status', 'due_date']);
            $table->index(['created_by', 'created_at']);
            $table->index(['priority', 'status']);
            $table->index(['type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_tasks');
    }
};
