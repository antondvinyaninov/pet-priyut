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
        Schema::create('animals', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(); // Кличка животного
            $table->enum('type', ['dog', 'cat', 'other']); // Тип животного
            $table->enum('gender', ['male', 'female', 'unknown'])->default('unknown'); // Пол
            $table->string('breed')->nullable(); // Порода
            $table->string('color')->nullable(); // Окрас
            $table->integer('age_months')->nullable(); // Возраст в месяцах
            $table->decimal('weight', 5, 2)->nullable(); // Вес в кг
            $table->text('description')->nullable(); // Описание
            $table->string('photo')->nullable(); // Фото животного
            $table->string('chip_number')->nullable(); // Номер чипа
            $table->string('tag_number')->nullable(); // Номер бирки
            
            // Связь с заявкой ОСВВ
            $table->foreignId('osvv_request_id')->nullable()->constrained('osvv_requests')->onDelete('set null');
            
            // Текущий этап (добавим внешний ключ отдельной миграцией)
            $table->unsignedBigInteger('current_stage_id')->nullable();
            
            // Даты
            $table->timestamp('arrived_at'); // Дата поступления
            $table->timestamp('stage_started_at'); // Когда начался текущий этап
            $table->timestamp('completed_at')->nullable(); // Дата завершения всех этапов
            
            // Статус
            $table->enum('status', ['active', 'released', 'adopted', 'deceased'])->default('active');
            
            $table->timestamps();
            
            // Индексы
            $table->index(['current_stage_id', 'status']);
            $table->index('osvv_request_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('animals');
    }
};
