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
        Schema::create('departure_route_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departure_route_id')->constrained('departure_routes')->cascadeOnDelete();
            $table->foreignId('osvv_request_id')->constrained('osvv_requests')->cascadeOnDelete();
            $table->integer('sequence_order')->default(1); // Порядок выполнения в маршруте
            $table->integer('estimated_time')->default(60); // Оценочное время на заявку (минуты)
            $table->text('notes')->nullable(); // Примечания к заявке в контексте маршрута
            $table->timestamps();
            
            // Уникальный индекс - одна заявка не может быть в нескольких маршрутах одновременно
            $table->unique('osvv_request_id');
            $table->index(['departure_route_id', 'sequence_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departure_route_requests');
    }
};
