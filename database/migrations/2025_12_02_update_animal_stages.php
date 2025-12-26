<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Деактивируем все этапы
        DB::table('animal_stages')->update(['is_active' => false]);

        // 1. Карантин
        DB::table('animal_stages')->updateOrInsert(
            ['slug' => 'quarantine'],
            [
                'name' => 'Карантин',
                'order' => 1,
                'is_active' => true,
                'duration_days' => 14,
                'description' => 'Карантин и первичный осмотр',
                'color' => '#EF4444',
                'is_final' => false,
                'updated_at' => now()
            ]
        );

        // 2. Стерилизация
        DB::table('animal_stages')->updateOrInsert(
            ['slug' => 'sterilization'],
            [
                'name' => 'Стерилизация',
                'order' => 2,
                'is_active' => true,
                'duration_days' => 7,
                'description' => 'Стерилизация и послеоперационный уход',
                'color' => '#8B5CF6',
                'is_final' => false,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // 3. Вакцинация
        DB::table('animal_stages')->updateOrInsert(
            ['slug' => 'vaccination'],
            [
                'name' => 'Вакцинация',
                'order' => 3,
                'is_active' => true,
                'duration_days' => 3,
                'description' => 'Вакцинация и маркировка',
                'color' => '#10B981',
                'is_final' => false,
                'updated_at' => now()
            ]
        );

        // 4. Готов к выпуску
        DB::table('animal_stages')->updateOrInsert(
            ['slug' => 'ready_for_release'],
            [
                'name' => 'Готов к выпуску',
                'order' => 4,
                'is_active' => true,
                'duration_days' => null,
                'description' => 'Животное готово к возврату в среду обитания',
                'color' => '#3B82F6',
                'is_final' => true,
                'updated_at' => now()
            ]
        );

        // Удаляем дубликаты и ненужные этапы
        DB::table('animal_stages')
            ->whereNotIn('slug', ['quarantine', 'sterilization', 'vaccination', 'ready_for_release'])
            ->delete();
    }

    public function down(): void
    {
        // Восстанавливаем все этапы
        DB::table('animal_stages')->update(['is_active' => true]);
    }
};
