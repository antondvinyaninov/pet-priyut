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
            // Добавляем поле для хранения множественных адресов в JSON формате
            $table->json('locations')->nullable()->after('location_landmark');
            
            // Добавляем индекс для поиска по адресам
            $table->index(['location_address']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('osvv_requests', function (Blueprint $table) {
            $table->dropIndex(['location_address']);
            $table->dropColumn('locations');
        });
    }
};
