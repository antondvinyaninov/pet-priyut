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
        Schema::table('departure_routes', function (Blueprint $table) {
            // Дата планирования маршрута (разные маршруты могут быть на разные даты)
            $table->date('planned_date')->nullable()->after('name');
            
            // Поля для отслеживания выполнения
            $table->datetime('actual_start_time')->nullable()->after('status');
            $table->datetime('actual_end_time')->nullable()->after('actual_start_time');
            $table->enum('completion_status', ['not_started', 'in_progress', 'completed', 'partially_completed', 'failed', 'cancelled'])
                  ->default('not_started')->after('actual_end_time');
            $table->text('completion_notes')->nullable()->after('completion_status');
            $table->integer('completion_percentage')->default(0)->after('completion_notes');
            
            // Индексы
            $table->index(['planned_date', 'status']);
            $table->index('completion_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departure_routes', function (Blueprint $table) {
            // Удаляем индексы
            $table->dropIndex(['planned_date', 'status']);
            $table->dropIndex(['completion_status']);
            
            // Удаляем поля
            $table->dropColumn([
                'planned_date',
                'actual_start_time',
                'actual_end_time',
                'completion_status',
                'completion_notes',
                'completion_percentage'
            ]);
        });
    }
};
