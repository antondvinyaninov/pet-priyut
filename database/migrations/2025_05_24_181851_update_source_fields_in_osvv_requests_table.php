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
            // Удаляем старый столбец source_type
            $table->dropColumn('source_type');
        });
        
        Schema::table('osvv_requests', function (Blueprint $table) {
            // Создаем новый столбец source_type с обновленными значениями
            $table->enum('source_type', [
                'district_office', // Управа района
                'telegram',        // Телеграм
                'vkontakte',      // ВКонтакте
                'phone',          // Телефон
                'media',          // СМИ
                'other'           // Прочие источники
            ])->nullable()->after('case_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('osvv_requests', function (Blueprint $table) {
            // Удаляем новый столбец
            $table->dropColumn('source_type');
        });
        
        Schema::table('osvv_requests', function (Blueprint $table) {
            // Возвращаем старый столбец source_type
            $table->enum('source_type', [
                'district_office', // Управа района
                'telegram',        // Телеграм
                'vkontakte',      // ВКонтакте
                'media',          // СМИ
                'other'           // Прочие источники
            ])->nullable()->after('case_description');
        });
    }
};
