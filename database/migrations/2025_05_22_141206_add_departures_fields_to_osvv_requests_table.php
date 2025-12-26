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
            $table->unsignedInteger('departures_count')->default(0)->comment('Количество выездов по заявке');
            $table->dateTime('next_departure_date')->nullable()->comment('Дата и время следующего запланированного выезда');
            $table->unsignedInteger('max_departures')->nullable()->comment('Максимальное количество выездов (для заявок с укусами)');
            $table->text('departure_notes')->nullable()->comment('Примечания к выездам');
            $table->boolean('requires_video')->default(false)->comment('Требуется видеофиксация');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('osvv_requests', function (Blueprint $table) {
            $table->dropColumn('departures_count');
            $table->dropColumn('next_departure_date');
            $table->dropColumn('max_departures');
            $table->dropColumn('departure_notes');
            $table->dropColumn('requires_video');
        });
    }
};
