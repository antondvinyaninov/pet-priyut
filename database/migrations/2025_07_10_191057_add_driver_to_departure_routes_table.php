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
        Schema::table('departure_routes', function (Blueprint $table) {
            $table->foreignId('driver_user_id')->nullable()->after('assigned_user_id')->constrained('users')->comment('Назначенный водитель');
            
            // Добавляем индекс для водителя
            $table->index('driver_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departure_routes', function (Blueprint $table) {
            $table->dropForeign(['driver_user_id']);
            $table->dropIndex(['driver_user_id']);
            $table->dropColumn('driver_user_id');
        });
    }
};
