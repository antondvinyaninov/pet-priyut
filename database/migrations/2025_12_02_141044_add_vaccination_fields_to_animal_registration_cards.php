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
        Schema::table('animal_registration_cards', function (Blueprint $table) {
            $table->string('vaccination_act_number')->nullable()->after('vaccination_history');
            $table->date('vaccination_act_date')->nullable()->after('vaccination_act_number');
            $table->string('vaccination_type')->nullable()->after('vaccination_act_date'); // Вид прививки (например, Рабикан)
            $table->string('vaccination_series')->nullable()->after('vaccination_type'); // Серия вакцины
            $table->string('vaccination_manufacture_date')->nullable()->after('vaccination_series'); // Дата изготовления
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('animal_registration_cards', function (Blueprint $table) {
            $table->dropColumn([
                'vaccination_act_number',
                'vaccination_act_date',
                'vaccination_type',
                'vaccination_series',
                'vaccination_manufacture_date'
            ]);
        });
    }
};
