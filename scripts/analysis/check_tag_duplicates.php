<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ПОЛНАЯ СТАТИСТИКА ===" . PHP_EOL;
echo "Всего животных: " . \App\Models\Animal::count() . PHP_EOL;
echo "Всего карточек: " . \App\Models\AnimalRegistrationCard::count() . PHP_EOL;
echo PHP_EOL;

// Животные с бирками
$withTags = \App\Models\Animal::whereNotNull('tag_number')->where('tag_number', '!=', '')->count();
echo "Животных с бирками: " . $withTags . PHP_EOL;

// Животные без бирок
$withoutTags = \App\Models\Animal::whereNull('tag_number')->orWhere('tag_number', '')->count();
echo "Животных без бирок: " . $withoutTags . PHP_EOL;
echo PHP_EOL;

// Анализ дублей по биркам
$tags = \App\Models\Animal::whereNotNull('tag_number')->where('tag_number', '!=', '')->pluck('tag_number')->toArray();
$tagCounts = array_count_values($tags);
$duplicates = array_filter($tagCounts, function($count) { return $count > 1; });

echo "Уникальных бирок: " . count($tagCounts) . PHP_EOL;
echo "Бирок с дублями: " . count($duplicates) . PHP_EOL;
echo PHP_EOL;

if (!empty($duplicates)) {
    echo "Дублирующиеся бирки:" . PHP_EOL;
    arsort($duplicates);
    foreach($duplicates as $tag => $count) {
        echo "  Бирка {$tag}: {$count} животных" . PHP_EOL;
        
        // Показываем этих животных
        $animals = \App\Models\Animal::where('tag_number', $tag)->with('registrationCard')->get();
        foreach($animals as $animal) {
            $cardNum = $animal->registrationCard->registration_number ?? 'нет';
            echo "    - ID: {$animal->id}, Карточка: {$cardNum}, Чип: {$animal->chip_number}" . PHP_EOL;
        }
        echo PHP_EOL;
    }
}
