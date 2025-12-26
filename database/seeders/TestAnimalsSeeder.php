<?php

namespace Database\Seeders;

use App\Models\Animal;
use App\Models\AnimalStage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestAnimalsSeeder extends Seeder
{
    /**
     * Заполнение базы данных тестовыми животными
     */
    public function run(): void
    {
        // Получаем первый доступный этап (если есть)
        $firstStage = AnimalStage::first();
        $firstStageId = $firstStage ? $firstStage->id : null;
        
        $animals = [
            [
                'name' => 'Барсик',
                'type' => 'dog',
                'gender' => 'male',
                'breed' => 'Дворняжка',
                'color' => 'Рыжий',
                'age_months' => 24,
                'weight' => 15.5,
                'description' => 'Дружелюбная собака, любит детей. Хорошо воспитана, знает команды.',
                'chip_number' => '123456789012345',
                'tag_number' => 'T001',
                'cage_number' => '1',
                'current_stage_id' => $firstStageId,
                'arrived_at' => now()->subDays(5),
                'stage_started_at' => now()->subDays(5),
                'status' => 'active',
            ],
            [
                'name' => 'Мурка',
                'type' => 'cat',
                'gender' => 'female',
                'breed' => 'Сибирская',
                'color' => 'Серая с белым',
                'age_months' => 18,
                'weight' => 4.2,
                'description' => 'Спокойная кошка, подходит для квартиры. Стерилизована.',
                'chip_number' => '987654321098765',
                'tag_number' => 'T002',
                'cage_number' => '2',
                'current_stage_id' => $firstStageId,
                'arrived_at' => now()->subDays(10),
                'stage_started_at' => now()->subDays(10),
                'status' => 'active',
            ],
            [
                'name' => 'Рекс',
                'type' => 'dog',
                'gender' => 'male',
                'breed' => 'Немецкая овчарка',
                'color' => 'Черно-рыжий',
                'age_months' => 36,
                'weight' => 32.0,
                'description' => 'Крупная собака, требует опытного хозяина. Охранные качества.',
                'chip_number' => '456789123456789',
                'tag_number' => 'T003',
                'cage_number' => '3',
                'current_stage_id' => $firstStageId,
                'arrived_at' => now()->subDays(3),
                'stage_started_at' => now()->subDays(3),
                'status' => 'active',
            ],
            [
                'name' => 'Белка',
                'type' => 'cat',
                'gender' => 'female',
                'breed' => 'Персидская',
                'color' => 'Белая',
                'age_months' => 12,
                'weight' => 3.8,
                'description' => 'Молодая кошка, очень игривая. Длинношерстная, требует ухода.',
                'chip_number' => '654321987654321',
                'tag_number' => 'T004',
                'cage_number' => '4',
                'current_stage_id' => $firstStageId,
                'arrived_at' => now()->subDays(7),
                'stage_started_at' => now()->subDays(7),
                'status' => 'active',
            ],
            [
                'name' => 'Дружок',
                'type' => 'dog',
                'gender' => 'male',
                'breed' => 'Лабрадор',
                'color' => 'Золотистый',
                'age_months' => 60,
                'weight' => 28.5,
                'description' => 'Взрослый пес, очень дружелюбный. Подходит для семей с детьми.',
                'chip_number' => '789123456789123',
                'tag_number' => 'T005',
                'cage_number' => '5',
                'current_stage_id' => $firstStageId,
                'arrived_at' => now()->subDays(15),
                'stage_started_at' => now()->subDays(15),
                'status' => 'active',
            ],
            [
                'name' => 'Пушок',
                'type' => 'cat',
                'gender' => 'male',
                'breed' => 'Мейн-кун',
                'color' => 'Серый табби',
                'age_months' => 8,
                'weight' => 2.5,
                'description' => 'Котенок крупной породы, еще растет. Активный и любопытный.',
                'chip_number' => '321654987321654',
                'tag_number' => 'T006',
                'cage_number' => '6',
                'current_stage_id' => $firstStageId,
                'arrived_at' => now()->subDays(2),
                'stage_started_at' => now()->subDays(2),
                'status' => 'active',
            ],
            [
                'name' => 'Шарик',
                'type' => 'dog',
                'gender' => 'male',
                'breed' => 'Спаниель',
                'color' => 'Коричневый с белым',
                'age_months' => 30,
                'weight' => 18.0,
                'description' => 'Активная охотничья собака. Требует много движения и игр.',
                'chip_number' => '147258369147258',
                'tag_number' => 'T007',
                'cage_number' => '7',
                'current_stage_id' => $firstStageId,
                'arrived_at' => now()->subDays(12),
                'stage_started_at' => now()->subDays(12),
                'status' => 'active',
            ],
            [
                'name' => 'Лиса',
                'type' => 'cat',
                'gender' => 'female',
                'breed' => 'Абиссинская',
                'color' => 'Рыжая',
                'age_months' => 15,
                'weight' => 3.5,
                'description' => 'Элегантная кошка с необычным окрасом. Независимая, но ласковая.',
                'chip_number' => '963852741963852',
                'tag_number' => 'T008',
                'cage_number' => '8',
                'current_stage_id' => $firstStageId,
                'arrived_at' => now()->subDays(6),
                'stage_started_at' => now()->subDays(6),
                'status' => 'active',
            ],
            [
                'name' => 'Бобик',
                'type' => 'dog',
                'gender' => 'male',
                'breed' => 'Такса',
                'color' => 'Черный с рыжим',
                'age_months' => 48,
                'weight' => 12.0,
                'description' => 'Охотничья собака с характером. Умная, но упрямая.',
                'chip_number' => '852963741852963',
                'tag_number' => 'T009',
                'cage_number' => '9',
                'current_stage_id' => $firstStageId,
                'arrived_at' => now()->subDays(8),
                'stage_started_at' => now()->subDays(8),
                'status' => 'active',
            ],
            [
                'name' => 'Снежка',
                'type' => 'cat',
                'gender' => 'female',
                'breed' => 'Британская',
                'color' => 'Голубая',
                'age_months' => 20,
                'weight' => 4.8,
                'description' => 'Спокойная кошка британской породы. Хорошо подходит для квартиры.',
                'chip_number' => '741852963741852',
                'tag_number' => 'T010',
                'cage_number' => '10',
                'current_stage_id' => $firstStageId,
                'arrived_at' => now()->subDays(4),
                'stage_started_at' => now()->subDays(4),
                'status' => 'active',
            ]
        ];

        foreach ($animals as $animalData) {
            Animal::create($animalData);
        }

        $this->command->info('Создано ' . count($animals) . ' тестовых животных');
    }
}
