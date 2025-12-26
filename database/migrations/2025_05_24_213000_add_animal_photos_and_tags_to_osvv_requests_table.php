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
            $table->json('animal_photos')->nullable()->after('animal_description')
                ->comment('Фотографии животного (JSON массив путей к файлам)');
            
            $table->boolean('has_tags')->default(false)->after('is_pregnant')
                ->comment('Наличие бирок у животного');
            
            $table->text('tags_description')->nullable()->after('has_tags')
                ->comment('Описание бирок (номер, цвет, состояние и т.д.)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('osvv_requests', function (Blueprint $table) {
            $table->dropColumn(['animal_photos', 'has_tags', 'tags_description']);
        });
    }
}; 