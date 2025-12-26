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
        Schema::table('capture_acts', function (Blueprint $table) {
            $table->integer('animals_count')->default(1)->after('status')->comment('Количество отловленных животных');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('capture_acts', function (Blueprint $table) {
            $table->dropColumn('animals_count');
        });
    }
};
