<?php

namespace App\Services;

use App\Models\Animal;
use App\Models\AnimalRegistrationCard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnimalCardImportService
{
    /**
     * Импорт карточки животного из распарсенных данных
     */
    public function importAnimalCard(array $data): ?Animal
    {
        try {
            return DB::transaction(function () use ($data) {
                // Создаем или находим животное
                $animal = $this->createOrUpdateAnimal($data);
                
                // Создаем или обновляем регистрационную карточку
                $this->createOrUpdateRegistrationCard($animal, $data);
                
                return $animal;
            });
        } catch (\Exception $e) {
            Log::error('Ошибка импорта карточки животного', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Создание или обновление животного
     */
    private function createOrUpdateAnimal(array $data): Animal
    {
        $arrivedAt = $data['arrived_at'] ?? now();
        
        $animalData = [
            'type' => $data['type'] ?? 'dog',
            'gender' => $data['gender'] ?? 'unknown',
            'breed' => $data['breed'] ?? null,
            'color' => $data['color'] ?? null,
            'age_months' => $this->parseAgeToMonths($data['age'] ?? null),
            'weight' => $data['weight'] ?? null,
            'chip_number' => $data['chip_number'] ?? null,
            'tag_number' => $data['tag_number'] ?? null,
            'arrived_at' => $arrivedAt,
            'stage_started_at' => $arrivedAt, // Обязательное поле
            'status' => 'active',
        ];
        
        // Если есть кличка, добавляем
        if (!empty($data['name'])) {
            $animalData['name'] = $data['name'];
        }
        
        // Ищем существующее животное ТОЛЬКО по чипу (чип уникален!)
        // Бирки могут повторяться, поэтому по ним не ищем
        $animal = null;
        
        // Ищем по чипу (только если чип указан)
        if (!empty($data['chip_number'])) {
            $animal = Animal::where('chip_number', $data['chip_number'])->first();
        }
        
        if ($animal) {
            // Обновляем существующее животное (найдено по чипу)
            $animal->update($animalData);
        } else {
            // Создаем новое животное с автоинкрементным ID
            $animal = Animal::create($animalData);
        }
        
        return $animal;
    }
    
    /**
     * Создание или обновление регистрационной карточки
     */
    private function createOrUpdateRegistrationCard(Animal $animal, array $data): AnimalRegistrationCard
    {
        // Формируем физическое описание
        $physicalDescription = [];
        if (!empty($data['breed'])) $physicalDescription[] = "Порода: " . $data['breed'];
        if (!empty($data['color'])) $physicalDescription[] = "Окрас: " . $data['color'];
        if (!empty($data['coat'])) $physicalDescription[] = "Шерсть: " . $data['coat'];
        if (!empty($data['ears'])) $physicalDescription[] = "Уши: " . $data['ears'];
        if (!empty($data['tail'])) $physicalDescription[] = "Хвост: " . $data['tail'];
        if (!empty($data['size'])) $physicalDescription[] = "Размер: " . $data['size'];
        if (!empty($data['age'])) $physicalDescription[] = "Возраст: " . $data['age'];
        
        // Генерируем уникальный номер карточки
        $baseNumber = $data['card_number'] ?? AnimalRegistrationCard::generateRegistrationNumber();
        $registrationNumber = $this->generateUniqueRegistrationNumber($baseNumber);
        
        $cardData = [
            'animal_id' => $animal->id,
            'registration_number' => $registrationNumber,
            'registration_date' => $data['card_date'] ?? $data['arrived_at'] ?? now(),
            'category' => $data['type'] === 'dog' ? 'собака' : 'кошка',
            'intake_source' => 'ОСВВ',
            'intake_location' => !empty($data['capture_location']) ? [['address' => $data['capture_location']]] : [],
            'physical_description' => implode(', ', $physicalDescription) ?: 'Не указано',
            'coat' => $data['coat'] ?? null,
            'ears' => $data['ears'] ?? null,
            'tail' => $data['tail'] ?? null,
            'size' => $data['size'] ?? null,
            'special_marks' => !empty($data['special_marks']) && $data['special_marks'] !== '-' ? [['type' => 'Общее', 'description' => $data['special_marks']]] : [],
            'chip_number_card' => $data['chip_number'] ?? null,
            'tag_number_card' => $data['tag_number'] ?? null,
            'card_status' => 'active',
        ];
        
        // Добавляем дополнительные поля
        if (!empty($data['clinical_exam_date'])) {
            $cardData['clinical_exam_date'] = $data['clinical_exam_date'];
            $cardData['clinical_exam_conclusion'] = $data['clinical_exam_conclusion'] ?? null;
        }
        
        if (!empty($data['aggression_notes'])) {
            $cardData['aggression_notes'] = $data['aggression_notes'];
        }
        
        if (!empty($data['behavior_correction'])) {
            $cardData['behavior_correction_notes'] = $data['behavior_correction'];
        }
        
        if (!empty($data['deworming_date'])) {
            $cardData['deworming_date'] = $data['deworming_date'];
        }
        
        if (!empty($data['sterilization_date'])) {
            $cardData['sterilization_date'] = $data['sterilization_date'];
            $cardData['sterilization_vet'] = $data['sterilization_vet'] ?? null;
            $cardData['reproductive_status'] = 'sterilized';
        }
        
        if (!empty($data['marking_date'])) {
            $cardData['marking_date'] = $data['marking_date'];
        }
        
        if (!empty($data['capture_act_number'])) {
            $cardData['capture_act_number'] = $data['capture_act_number'];
            $cardData['capture_act_date'] = $data['capture_act_date'] ?? null;
        }
        
        if (!empty($data['capture_location'])) {
            $cardData['capture_location_address'] = $data['capture_location'];
        }
        
        if (!empty($data['outcome'])) {
            $cardData['outcome_reason'] = $data['outcome'];
        }
        
        if (!empty($data['release_address'])) {
            $cardData['release_address'] = $data['release_address'];
        }
        
        // Добавляем информацию о вакцинации
        $vaccinations = [];
        
        // Вакцинация из текста документа (приоритет)
        if (!empty($data['vaccination_act_number']) && !empty($data['vaccination_act_date'])) {
            $vaccinations[] = [
                'date' => $data['vaccination_act_date'],
                'vaccine' => $data['vaccine_name'] ?? 'Вакцина',
                'act_number' => $data['vaccination_act_number'],
                'vet' => 'Импорт из документа'
            ];
        }
        
        // Вакцинация из папки (дополнительно)
        if (!empty($data['vaccine_number']) && !empty($data['vaccine_date'])) {
            $vaccinations[] = [
                'date' => $data['vaccine_date'],
                'vaccine' => 'Вакцина №' . $data['vaccine_number'],
                'vet' => 'Импорт из документа'
            ];
        }
        
        if (!empty($vaccinations)) {
            $cardData['vaccination_history'] = $vaccinations;
        }
        
        // Сохраняем фотографии
        if (!empty($data['images'])) {
            $photos = $this->saveAnimalPhotos($animal, $data['images']);
            if (!empty($photos[0])) {
                $cardData['photo_face'] = $photos[0];
            }
            if (!empty($photos[1])) {
                $cardData['photo_profile'] = $photos[1];
            }
        }
        
        // Ищем существующую карточку
        $card = $animal->registrationCard;
        
        if ($card) {
            // Обновляем существующую карточку
            $card->update($cardData);
        } else {
            // Создаем новую карточку
            $card = AnimalRegistrationCard::create($cardData);
        }
        
        return $card;
    }
    
    /**
     * Сохранение фотографий животного
     */
    private function saveAnimalPhotos(Animal $animal, array $images): array
    {
        $savedPhotos = [];
        
        try {
            $storageDir = storage_path('app/public/animals/' . $animal->id);
            
            // Создаем директорию если её нет
            if (!is_dir($storageDir)) {
                mkdir($storageDir, 0755, true);
            }
            
            foreach ($images as $index => $image) {
                // Определяем расширение
                $extension = isset($image['extension']) ? $image['extension'] : 'jpg';
                if (preg_match('/\.(jpeg|jpg|png|gif)$/i', $image['filename'], $matches)) {
                    $extension = strtolower($matches[1]);
                }
                
                // Генерируем имя файла
                $filename = 'photo_' . ($index + 1) . '_' . time() . '.' . $extension;
                $filepath = $storageDir . '/' . $filename;
                
                // Сохраняем файл
                if (file_put_contents($filepath, $image['data'])) {
                    $savedPhotos[] = 'animals/' . $animal->id . '/' . $filename;
                }
                
                // Ограничиваем количество фотографий
                if (count($savedPhotos) >= 2) {
                    break;
                }
            }
        } catch (\Exception $e) {
            Log::error('Ошибка сохранения фотографий животного', [
                'animal_id' => $animal->id,
                'error' => $e->getMessage()
            ]);
        }
        
        return $savedPhotos;
    }
    

    /**
     * Генерация уникального номера регистрационной карточки
     * Если номер уже существует, добавляет суффикс _2, _3 и т.д.
     */
    private function generateUniqueRegistrationNumber(string $baseNumber): string
    {
        $number = $baseNumber;
        $suffix = 2;
        
        // Проверяем существует ли карточка с таким номером
        while (AnimalRegistrationCard::where('registration_number', $number)->exists()) {
            $number = $baseNumber . '_' . $suffix;
            $suffix++;
        }
        
        return $number;
    }

    /**
     * Парсинг возраста в месяцы
     */
    private function parseAgeToMonths(?string $age): ?int
    {
        if (empty($age)) {
            return null;
        }
        
        $age = mb_strtolower($age);
        
        // Ищем годы
        if (preg_match('/(\d+)\s*(год|лет|года)/u', $age, $matches)) {
            return (int)$matches[1] * 12;
        }
        
        // Ищем месяцы
        if (preg_match('/(\d+)\s*(месяц|месяца|месяцев)/u', $age, $matches)) {
            return (int)$matches[1];
        }
        
        return null;
    }
}
