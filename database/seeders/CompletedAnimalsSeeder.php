<?php

namespace Database\Seeders;

use App\Models\Animal;
use App\Models\AnimalStage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompletedAnimalsSeeder extends Seeder
{
    /**
     * Заполнение базы данных животными с завершенными статусами
     */
    public function run(): void
    {
        // Получаем первый доступный этап (если есть)
        $firstStage = AnimalStage::first();
        $firstStageId = $firstStage ? $firstStage->id : null;
        
        $completedAnimals = [
            // Пристроенные животные
            [
                'name' => 'Максим',
                'type' => 'dog',
                'gender' => 'male',
                'breed' => 'Хаски',
                'color' => 'Серо-белый',
                'age_months' => 18,
                'weight' => 22.0,
                'description' => 'Активный пес, нашел любящую семью. Успешно пристроен.',
                'chip_number' => '555666777888999',
                'tag_number' => 'T020',
                'cage_number' => '20',
                'current_stage_id' => $firstStageId,
                'arrived_at' => now()->subDays(45),
                'stage_started_at' => now()->subDays(45),
                'completed_at' => now()->subDays(5),
                'status' => 'adopted',
            ],
            [
                'name' => 'Василиса',
                'type' => 'cat',
                'gender' => 'female',
                'breed' => 'Русская голубая',
                'color' => 'Голубая',
                'age_months' => 14,
                'weight' => 3.2,
                'description' => 'Спокойная кошка, нашла дом у пожилой пары. Пристроена.',
                'chip_number' => '444555666777888',
                'tag_number' => 'T021',
                'cage_number' => '21',
                'current_stage_id' => $firstStageId,
                'arrived_at' => now()->subDays(30),
                'stage_started_at' => now()->subDays(30),
                'completed_at' => now()->subDays(3),
                'status' => 'adopted',
            ],
            [
                'name' => 'Граф',
                'type' => 'dog',
                'gender' => 'male',
                'breed' => 'Доберман',
                'color' => 'Черно-подпалый',
                'age_months' => 42,
                'weight' => 35.0,
                'description' => 'Благородный пес, нашел опытных хозяев. Пристроен.',
                'chip_number' => '333444555666777',
                'tag_number' => 'T022',
                'cage_number' => '22',
                'current_stage_id' => $firstStageId,
                'arrived_at' => now()->subDays(60),
                'stage_started_at' => now()->subDays(60),
                'completed_at' => now()->subDays(7),
                'status' => 'adopted',
            ],
            
            // Выпущенные животные
            [
                'name' => 'Дикий',
                'type' => 'cat',
                'gender' => 'male',
                'breed' => 'Дворовой',
                'color' => 'Серый полосатый',
                'age_months' => 36,
                'weight' => 5.5,
                'description' => 'Дикий кот, после лечения выпущен в естественную среду.',
                'chip_number' => '222333444555666',
                'tag_number' => 'T023',
                'cage_number' => '23',
                'current_stage_id' => $firstStageId,
                'arrived_at' => now()->subDays(20),
                'stage_started_at' => now()->subDays(20),
                'completed_at' => now()->subDays(2),
                'status' => 'released',
            ],
            [
                'name' => 'Вольная',
                'type' => 'dog',
                'gender' => 'female',
                'breed' => 'Дворняжка',
                'color' => 'Коричневая',
                'age_months' => 24,
                'weight' => 16.0,
                'description' => 'Бродячая собака, после лечения выпущена в свою территорию.',
                'chip_number' => '111222333444555',
                'tag_number' => 'T024',
                'cage_number' => '24',
                'current_stage_id' => $firstStageId,
                'arrived_at' => now()->subDays(25),
                'stage_started_at' => now()->subDays(25),
                'completed_at' => now()->subDays(1),
                'status' => 'released',
            ],
            
            // Дополнительные пристроенные
            [
                'name' => 'Луна',
                'type' => 'cat',
                'gender' => 'female',
                'breed' => 'Мейн-кун',
                'color' => 'Черная',
                'age_months' => 10,
                'weight' => 3.0,
                'description' => 'Красивая котенок крупной породы, быстро нашла дом.',
                'chip_number' => '999888777666555',
                'tag_number' => 'T025',
                'cage_number' => '25',
                'current_stage_id' => $firstStageId,
                'arrived_at' => now()->subDays(35),
                'stage_started_at' => now()->subDays(35),
                'completed_at' => now()->subDays(10),
                'status' => 'adopted',
            ],
            [
                'name' => 'Тоби',
                'type' => 'dog',
                'gender' => 'male',
                'breed' => 'Джек-рассел-терьер',
                'color' => 'Белый с рыжим',
                'age_months' => 16,
                'weight' => 8.5,
                'description' => 'Энергичный маленький пес, нашел активную семью.',
                'chip_number' => '888777666555444',
                'tag_number' => 'T026',
                'cage_number' => '26',
                'current_stage_id' => $firstStageId,
                'arrived_at' => now()->subDays(40),
                'stage_started_at' => now()->subDays(40),
                'completed_at' => now()->subDays(8),
                'status' => 'adopted',
            ],
            [
                'name' => 'Симба',
                'type' => 'cat',
                'gender' => 'male',
                'breed' => 'Рыжий',
                'color' => 'Рыжий',
                'age_months' => 22,
                'weight' => 4.5,
                'description' => 'Красивый рыжий кот, очень ласковый, нашел дом.',
                'chip_number' => '777666555444333',
                'tag_number' => 'T027',
                'cage_number' => '27',
                'current_stage_id' => $firstStageId,
                'arrived_at' => now()->subDays(50),
                'stage_started_at' => now()->subDays(50),
                'completed_at' => now()->subDays(12),
                'status' => 'adopted',
            ]
        ];

        foreach ($completedAnimals as $animalData) {
            Animal::create($animalData);
        }

        $this->command->info('Создано ' . count($completedAnimals) . ' животных с завершенными статусами');
    }
}
