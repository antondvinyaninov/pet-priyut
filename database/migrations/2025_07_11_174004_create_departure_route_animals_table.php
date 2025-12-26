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
        Schema::create('departure_route_animals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departure_route_id')->constrained('departure_routes')->cascadeOnDelete();
            $table->foreignId('animal_id')->constrained('animals')->cascadeOnDelete();
            $table->integer('sequence_order')->default(1); // Порядок обработки животного в маршруте
            $table->integer('estimated_time')->default(30); // Оценочное время на животное (минуты)
            $table->text('notes')->nullable(); // Примечания к животному в контексте маршрута
            $table->timestamps();
            
            // Уникальный индекс - одно животное не может быть в нескольких маршрутах одновременно
            $table->unique('animal_id');
            $table->index(['departure_route_id', 'sequence_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departure_route_animals');
    }
};
