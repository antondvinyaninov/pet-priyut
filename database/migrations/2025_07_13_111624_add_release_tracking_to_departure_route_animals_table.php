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
        Schema::table('departure_route_animals', function (Blueprint $table) {
            // Статус выпуска животного
            $table->enum('release_status', [
                'pending',      // Ожидает выпуска  
                'released',     // Выпущен
                'failed',       // Не удалось выпустить
                'cancelled'     // Отменен
            ])->default('pending')->after('estimated_time');
            
            // Результат выпуска
            $table->enum('release_result', [
                'success',      // Успешно выпущен
                'failed',       // Не удалось выпустить 
                'cancelled'     // Отменен
            ])->nullable()->after('release_status');
            
            // Заметки о выпуске
            $table->text('release_notes')->nullable()->after('release_result');
            
            // Время выпуска
            $table->datetime('released_at')->nullable()->after('release_notes');
            
            // Место выпуска
            $table->string('release_location')->nullable()->after('released_at');
            
            // Индексы для производительности
            $table->index('release_status');
            $table->index('released_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departure_route_animals', function (Blueprint $table) {
            $table->dropIndex(['release_status']);
            $table->dropIndex(['released_at']);
            $table->dropColumn([
                'release_status',
                'release_result', 
                'release_notes',
                'released_at',
                'release_location'
            ]);
        });
    }
};
