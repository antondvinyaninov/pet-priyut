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
        Schema::create('departure_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departure_plan_id')->constrained('departure_plans')->cascadeOnDelete();
            $table->string('name'); // Название маршрута (например, "Зона 1 - Центральный район")
            $table->foreignId('assigned_user_id')->nullable()->constrained('users'); // Назначенный отловщик
            $table->time('start_time')->nullable(); // Планируемое время начала
            $table->integer('priority')->default(1); // Приоритет маршрута (1-10)
            $table->text('notes')->nullable(); // Примечания к маршруту
            $table->integer('requests_count')->default(0); // Количество заявок в маршруте
            $table->integer('estimated_duration')->default(0); // Оценочное время выполнения (минуты)
            $table->enum('status', ['pending', 'assigned', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();
            
            // Индексы
            $table->index(['departure_plan_id', 'priority']);
            $table->index('assigned_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departure_routes');
    }
};
