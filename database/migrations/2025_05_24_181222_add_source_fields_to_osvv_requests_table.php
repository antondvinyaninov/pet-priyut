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
            $table->enum('source_type', [
                'district_office', // Управа района
                'telegram',        // Телеграм
                'vkontakte',      // ВКонтакте
                'media',          // СМИ
                'other'           // Прочие источники
            ])->nullable()->after('case_description');
            
            $table->string('source_district', 100)->nullable()->after('source_type')
                ->comment('Район управы (только для source_type = district_office)');
            
            $table->string('aurora_number', 50)->nullable()->after('source_district')
                ->comment('Номер из программы Аврора (только для source_type = district_office)');
            
            $table->text('source_details')->nullable()->after('aurora_number')
                ->comment('Дополнительные детали источника');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('osvv_requests', function (Blueprint $table) {
            $table->dropColumn(['source_type', 'source_district', 'aurora_number', 'source_details']);
        });
    }
};
