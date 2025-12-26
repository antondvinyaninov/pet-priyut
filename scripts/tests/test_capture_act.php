<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\OsvvRequest;
use App\Models\CaptureAct;
use App\Models\Animal;
use App\Models\AnimalStage;
use Carbon\Carbon;

echo "🧪 Тестирование автоматического создания животных из акта отлова\n\n";

// Найдем заявку №5
$request = OsvvRequest::find(5);
if (!$request) {
    echo "❌ Заявка №5 не найдена\n";
    exit(1);
}

echo "✅ Найдена заявка №5: {$request->contact_name}\n";
echo "📊 Количество животных в заявке: {$request->animals_count}\n\n";

// Проверим, есть ли уже акт отлова
$existingAct = CaptureAct::where('osvv_request_id', $request->id)->first();
if ($existingAct) {
    echo "📋 Акт отлова уже существует: {$existingAct->act_number}\n";
    echo "📈 Статус акта: {$existingAct->status}\n";
    echo "🔢 Количество животных в акте: {$existingAct->animals_count}\n\n";
    
    // Проверим, есть ли связанные животные
    $animals = Animal::where('osvv_request_id', $request->id)->get();
    echo "🐕 Животных в системе управления: {$animals->count()}\n";
    
    if ($animals->count() > 0) {
        echo "\n📋 Список животных:\n";
        foreach ($animals as $animal) {
            echo "- ID: {$animal->id}, Имя: {$animal->name}, Тип: {$animal->type_name}, Этап: {$animal->currentStage->name}\n";
        }
    }
} else {
    echo "📝 Создаем новый акт отлова...\n";
    
    // Генерируем номер акта
    $actNumber = CaptureAct::generateActNumber();
    echo "🔖 Номер акта: {$actNumber}\n";
    
    // Создаем акт отлова
    $act = CaptureAct::create([
        'osvv_request_id' => $request->id,
        'user_id' => 1, // ID администратора
        'act_number' => $actNumber,
        'capture_date' => now(),
        'capture_time' => now()->format('H:i'),
        'capture_location' => $request->location_address,
        'animal_type' => $request->animal_type === 'cat' ? 'кошка' : 'собака',
        'animal_gender' => 'неизвестно',
        'animal_breed' => null,
        'animal_color' => 'серый',
        'animal_size' => 'средний',
        'animal_features' => 'Отловлен согласно заявке №' . $request->id,
        'animal_behavior' => 'спокойное',
        'capturing_method' => 'сеть',
        'notes' => 'Тестовый акт отлова для проверки автоматического создания животных',
        'status' => 'completed',
        'animals_count' => $request->animals_count ?? 3,
    ]);
    
    echo "✅ Акт отлова создан: {$act->act_number}\n";
    echo "🔍 Статус акта: {$act->status}\n";
    echo "🔢 Количество животных в акте: {$act->animals_count}\n";
    
    // Попробуем вызвать метод создания животных вручную
    echo "🔧 Попытка создания животных вручную...\n";
    
    // Получаем первый активный этап (карантин)
    $firstStage = \App\Models\AnimalStage::active()->ordered()->first();
    if (!$firstStage) {
        echo "❌ Не найден первый этап для животных\n";
    } else {
        echo "✅ Найден этап: {$firstStage->name}\n";
        
        // Создаем животных вручную
        for ($i = 1; $i <= $act->animals_count; $i++) {
            $animalName = $act->animals_count > 1 ? "Из акта {$act->act_number} №{$i}" : "Из акта {$act->act_number}";
            
            $animal = \App\Models\Animal::create([
                'name' => $animalName,
                'type' => 'dog',
                'gender' => 'unknown',
                'breed' => $act->animal_breed,
                'color' => $act->animal_color,
                'description' => "Животное #{$i} из акта отлова #{$act->act_number}",
                'osvv_request_id' => $act->osvv_request_id,
                'current_stage_id' => $firstStage->id,
                'arrived_at' => $act->capture_date,
                'stage_started_at' => now(),
                'status' => 'active',
            ]);
            
            echo "✅ Создано животное: {$animal->name} (ID: {$animal->id})\n";
        }
    }
}

echo "\n🎉 Тестирование завершено!\n"; 