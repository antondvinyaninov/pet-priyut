<?php

namespace Database\Seeders;

use App\Models\Animal;
use App\Models\AnimalStage;
use App\Models\OsvvRequest;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OsvvAnimalsSeeder extends Seeder
{
    /**
     * Заполнение базы данных животными из заявок ОСВВ
     */
    public function run(): void
    {
        // Получаем первый доступный этап
        $firstStage = AnimalStage::first();
        $firstStageId = $firstStage ? $firstStage->id : null;
        
        // Получаем несколько заявок ОСВВ без животных
        $osvvRequests = OsvvRequest::whereDoesntHave('animal')->take(5)->get();
        
        if ($osvvRequests->isEmpty()) {
            $this->command->info('Нет доступных заявок ОСВВ для создания животных');
            return;
        }
        
        $osvvAnimals = [];
        $cageCounter = 50; // Начинаем с номера 50 для ОСВВ животных
        
        foreach ($osvvRequests as $index => $request) {
            $animalTypes = ['dog', 'cat'];
            $genders = ['male', 'female'];
            $dogBreeds = ['Дворняжка', 'Овчарка', 'Лабрадор', 'Хаски', 'Терьер'];
            $catBreeds = ['Дворовая', 'Сибирская', 'Персидская', 'Британская', 'Сфинкс'];
            $dogColors = ['Рыжий', 'Черный', 'Белый', 'Серый', 'Коричневый', 'Пятнистый'];
            $catColors = ['Рыжий', 'Черный', 'Белый', 'Серый', 'Полосатый', 'Черепаховый'];
            
            $type = $animalTypes[array_rand($animalTypes)];
            $gender = $genders[array_rand($genders)];
            $breed = $type === 'dog' ? $dogBreeds[array_rand($dogBreeds)] : $catBreeds[array_rand($catBreeds)];
            $color = $type === 'dog' ? $dogColors[array_rand($dogColors)] : $catColors[array_rand($catColors)];
            
            $osvvAnimals[] = [
                'name' => $this->generateAnimalName($type, $gender, $index),
                'type' => $type,
                'gender' => $gender,
                'breed' => $breed,
                'color' => $color,
                'age_months' => rand(3, 84), // От 3 месяцев до 7 лет
                'weight' => $type === 'dog' ? rand(50, 400) / 10 : rand(20, 80) / 10, // 5-40 кг для собак, 2-8 кг для кошек
                'description' => $this->generateDescription($type, $request),
                'chip_number' => 'OSVV' . str_pad($request->id, 10, '0', STR_PAD_LEFT),
                'tag_number' => 'OSVV' . str_pad($request->id, 3, '0', STR_PAD_LEFT),
                'cage_number' => (string) $cageCounter++,
                'osvv_request_id' => $request->id,
                'current_stage_id' => $firstStageId,
                'arrived_at' => now()->subDays(rand(1, 14)),
                'stage_started_at' => now()->subDays(rand(1, 14)),
                'status' => 'active',
            ];
        }

        foreach ($osvvAnimals as $animalData) {
            Animal::create($animalData);
        }

        $this->command->info('Создано ' . count($osvvAnimals) . ' животных из заявок ОСВВ');
    }
    
    private function generateAnimalName($type, $gender, $index): string
    {
        $dogNamesMale = ['Архип', 'Борис', 'Валера', 'Гриша', 'Дима', 'Женя', 'Захар'];
        $dogNamesFemale = ['Алиса', 'Белла', 'Вера', 'Галя', 'Дуся', 'Елка', 'Зина'];
        $catNamesMale = ['Алекс', 'Борька', 'Васька', 'Гарик', 'Демьян', 'Ежик', 'Жорик'];
        $catNamesFemale = ['Ася', 'Буся', 'Вася', 'Глаша', 'Дуня', 'Егоза', 'Жужа'];
        
        if ($type === 'dog') {
            $names = $gender === 'male' ? $dogNamesMale : $dogNamesFemale;
        } else {
            $names = $gender === 'male' ? $catNamesMale : $catNamesFemale;
        }
        
        return $names[$index % count($names)];
    }
    
    private function generateDescription($type, $request): string
    {
        $descriptions = [
            'dog' => [
                'Найден по заявке ОСВВ, дружелюбный характер.',
                'Спасен службой ОСВВ, требует социализации.',
                'Поступил из заявки ОСВВ, активный и игривый.',
                'Животное из заявки ОСВВ, хорошо контактирует с людьми.',
                'Найден по обращению в ОСВВ, здоров и активен.',
            ],
            'cat' => [
                'Найден по заявке ОСВВ, спокойный характер.',
                'Спасен службой ОСВВ, ласковый и тихий.',
                'Поступил из заявки ОСВВ, независимый но дружелюбный.',
                'Животное из заявки ОСВВ, хорошо адаптируется.',
                'Найден по обращению в ОСВВ, здоров и активен.',
            ]
        ];
        
        $typeDescriptions = $descriptions[$type];
        $baseDescription = $typeDescriptions[array_rand($typeDescriptions)];
        
        return $baseDescription . ' Связан с заявкой ОСВВ #' . $request->id;
    }
}
