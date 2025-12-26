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
        Schema::table('departure_route_requests', function (Blueprint $table) {
            // Статус выполнения заявки
            $table->enum('execution_status', ['pending', 'visited', 'completed', 'failed', 'cancelled', 'no_animals_found'])
                  ->default('pending')->after('notes');
            
            // Результат выполнения
            $table->enum('execution_result', ['success', 'partial_success', 'no_result', 'failed', 'cancelled'])
                  ->nullable()->after('execution_status');
            
            // Детали выполнения
            $table->text('execution_notes')->nullable()->after('execution_result');
            $table->datetime('executed_at')->nullable()->after('execution_notes');
            $table->integer('animals_captured')->default(0)->after('executed_at');
            $table->json('execution_photos')->nullable()->after('animals_captured');
            
            // Индексы
            $table->index('execution_status');
            $table->index('executed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departure_route_requests', function (Blueprint $table) {
            // Удаляем индексы
            $table->dropIndex(['execution_status']);
            $table->dropIndex(['executed_at']);
            
            // Удаляем поля
            $table->dropColumn([
                'execution_status',
                'execution_result',
                'execution_notes',
                'executed_at',
                'animals_captured',
                'execution_photos'
            ]);
        });
    }
};
