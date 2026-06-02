<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('osvv_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('osvv_requests')->onDelete('cascade');
            $table->string('event_type'); // departure_created, departure_completed, status_changed, etc.
            $table->json('event_data'); // данные события
            $table->timestamp('event_time');
            $table->string('user_id')->nullable(); // кто выполнил действие
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->integer('duration_minutes')->nullable(); // время выполнения
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['request_id', 'event_type']);
            $table->index('event_time');
        });
    }

    public function down()
    {
        Schema::dropIfExists('osvv_analytics');
    }
}; 