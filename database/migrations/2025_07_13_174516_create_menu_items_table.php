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
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Название пункта меню
            $table->string('route')->nullable(); // Маршрут (если это простая ссылка)
            $table->string('icon'); // Иконка (эмодзи)
            $table->integer('order')->default(0); // Порядок отображения
            $table->boolean('is_active')->default(true); // Активен ли пункт
            $table->boolean('is_submenu')->default(false); // Это подменю?
            $table->unsignedBigInteger('parent_id')->nullable(); // ID родительского пункта
            $table->string('submenu_id')->nullable(); // ID подменю для JavaScript
            $table->timestamps();
            
            // Внешний ключ для родительского пункта
            $table->foreign('parent_id')->references('id')->on('menu_items')->onDelete('cascade');
            
            // Индекс для сортировки
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
