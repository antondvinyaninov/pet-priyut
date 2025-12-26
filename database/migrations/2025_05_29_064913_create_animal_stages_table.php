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
        Schema::create('animal_stages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Название этапа
            $table->string('slug')->unique(); // Уникальный идентификатор
            $table->text('description')->nullable(); // Описание этапа
            $table->string('color', 7)->default('#6B7280'); // Цвет для UI (hex)
            $table->integer('order')->default(0); // Порядок сортировки
            $table->integer('duration_days')->nullable(); // Ожидаемая длительность в днях
            $table->boolean('is_final')->default(false); // Финальный этап?
            $table->boolean('is_active')->default(true); // Активен ли этап
            $table->timestamps();
            
            // Индексы
            $table->index(['order', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('animal_stages');
    }
};
