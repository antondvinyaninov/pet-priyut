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
        Schema::create('departure_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Название плана (например, "План выездов на 10.07.2025")
            $table->date('planned_date'); // Дата выездов
            $table->enum('status', ['draft', 'approved', 'in_progress', 'completed', 'cancelled'])->default('draft');
            $table->foreignId('created_by')->constrained('users'); // Кто создал план
            $table->text('notes')->nullable(); // Общие примечания к плану
            $table->integer('total_requests')->default(0); // Общее количество заявок в плане
            $table->integer('total_routes')->default(0); // Общее количество маршрутов
            $table->integer('estimated_duration')->default(0); // Оценочное время выполнения (минуты)
            $table->timestamps();
            
            // Индексы для оптимизации запросов
            $table->index(['planned_date', 'status']);
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departure_plans');
    }
};
