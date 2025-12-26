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
        Schema::create('animal_task_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('animal_id')->constrained('animals')->onDelete('cascade');
            $table->foreignId('task_id')->constrained('stage_tasks')->onDelete('cascade');
            $table->foreignId('completed_by')->constrained('users')->onDelete('cascade'); // Кто выполнил
            $table->timestamp('completed_at'); // Когда выполнено
            $table->text('notes')->nullable(); // Заметки о выполнении
            $table->json('data')->nullable(); // Дополнительные данные (результаты анализов и т.д.)
            $table->timestamps();
            
            // Уникальность - одну задачу для одного животного можно выполнить только один раз
            $table->unique(['animal_id', 'task_id']);
            
            // Индексы
            $table->index(['animal_id', 'completed_at']);
            $table->index('completed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('animal_task_completions');
    }
};
