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
        Schema::create('capture_acts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('osvv_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('act_number')->unique();
            $table->date('capture_date');
            $table->time('capture_time')->nullable();
            $table->string('capture_location');
            $table->string('animal_type')->nullable(); // вид животного
            $table->string('animal_gender')->nullable(); // пол животного
            $table->string('animal_breed')->nullable(); // порода
            $table->string('animal_color')->nullable(); // окрас
            $table->string('animal_size')->nullable(); // размер
            $table->text('animal_features')->nullable(); // особые приметы
            $table->string('animal_behavior')->nullable(); // особенности поведения
            $table->string('capturing_method')->nullable(); // способ отлова
            $table->text('notes')->nullable(); // примечания
            $table->string('status')->default('created'); // статус акта: создан, выполнен, отменен
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capture_acts');
    }
};
