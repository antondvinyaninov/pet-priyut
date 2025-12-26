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
        Schema::create('osvv_requests', function (Blueprint $table) {
            $table->id();
            
            // Контактная информация
            $table->string('contact_name');
            $table->string('contact_phone');
            $table->string('contact_email')->nullable();
            
            // Информация о животном
            $table->enum('animal_type', ['cat', 'dog', 'other'])->default('cat');
            $table->string('animal_type_other')->nullable(); // Для других видов животных
            $table->enum('animal_gender', ['male', 'female', 'unknown'])->default('unknown');
            $table->string('animal_age')->nullable(); // Примерный возраст
            $table->text('animal_description')->nullable();
            
            // Адрес/локация
            $table->text('location_address');
            $table->string('location_landmark')->nullable(); // Ориентир
            
            // Статус заявки
            $table->enum('status', [
                'new',               // Новая заявка
                'processing',        // В обработке
                'capture_scheduled', // Запланирован отлов
                'captured',          // Животное отловлено
                'in_shelter',        // В приюте
                'sterilized',        // Стерилизовано
                'vaccinated',        // Вакцинировано
                'ready_for_return',  // Готово к возврату
                'returned',          // Возвращено
                'completed',         // Завершено
                'cancelled'          // Отменено
            ])->default('new');
            
            // Дополнительная информация
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained(); // Кто взял заявку в работу
            
            $table->timestamps();
            $table->softDeletes(); // Мягкое удаление
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('osvv_requests');
    }
};
