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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Название роли (admin, manager, operator, etc.)
            $table->string('display_name'); // Отображаемое название
            $table->text('description')->nullable(); // Описание роли
            $table->json('permissions')->nullable(); // JSON с разрешениями
            $table->boolean('is_active')->default(true); // Активна ли роль
            $table->integer('priority')->default(0); // Приоритет роли (чем выше, тем больше прав)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
