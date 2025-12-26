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
        Schema::table('osvv_requests', function (Blueprint $table) {
            $table->integer('animals_count')->default(1)->comment('Количество животных');
            $table->string('district')->nullable()->comment('Район');
            $table->boolean('has_bite')->default(false)->comment('Был ли укус');
            $table->boolean('is_pregnant')->default(false)->comment('Беременность');
            $table->date('departure_date')->nullable()->comment('Дата выезда');
            $table->date('deadline_date')->nullable()->comment('Крайний срок выезда');
            $table->text('capture_result')->nullable()->comment('Результат проведенных мероприятий по отлову');
            
            // Индексы для ускорения поиска
            $table->index('district');
            $table->index('has_bite');
            $table->index('deadline_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('osvv_requests', function (Blueprint $table) {
            $table->dropColumn([
                'animals_count',
                'district',
                'has_bite',
                'is_pregnant',
                'departure_date',
                'deadline_date',
                'capture_result'
            ]);
        });
    }
};
