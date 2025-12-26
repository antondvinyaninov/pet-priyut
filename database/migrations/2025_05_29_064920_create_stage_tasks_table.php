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
        Schema::create('stage_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stage_id')->constrained('animal_stages')->onDelete('cascade');
            $table->string('name'); // Название задачи
            $table->text('description')->nullable(); // Описание задачи
            $table->integer('order')->default(0); // Порядок выполнения
            $table->boolean('is_required')->default(true); // Обязательная ли задача
            $table->boolean('is_active')->default(true); // Активна ли задача
            $table->json('metadata')->nullable(); // Дополнительные данные (например, параметры)
            $table->timestamps();
            
            // Индексы
            $table->index(['stage_id', 'order', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stage_tasks');
    }
};
