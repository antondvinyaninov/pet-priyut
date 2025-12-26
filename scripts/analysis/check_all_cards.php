<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Получаем все файлы
$allFiles = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator('PetBasedoc')
);

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'docx') {
        $allFiles[] = $file->getPathname();
    }
}

echo "Всего файлов .docx: " . count($allFiles) . "\n";

// Получаем все карточки из базы
$cards = \App\Models\AnimalRegistrationCard::with('animal')
    ->orderBy('registration_number')
    ->get();

echo "Импортировано карточек: " . count($cards) . "\n";
echo "Разница: " . (count($allFiles) - count($cards)) . " файлов не импортировано\n\n";

// Показываем все номера карточек
echo "Все номера карточек в базе:\n";
echo str_repeat("=", 100) . "\n";

$numbers = [];
foreach ($cards as $card) {
    $numbers[] = $card->registration_number;
}

sort($numbers, SORT_NATURAL);

$counter = 1;
foreach ($numbers as $num) {
    echo sprintf("%3d. %s\n", $counter++, $num);
}

// Проверяем есть ли дубликаты
$duplicates = array_count_values($numbers);
$duplicates = array_filter($duplicates, function($count) { return $count > 1; });

if (!empty($duplicates)) {
    echo "\n\nДУБЛИКАТЫ НОМЕРОВ:\n";
    echo str_repeat("=", 100) . "\n";
    foreach ($duplicates as $num => $count) {
        echo "Номер {$num}: {$count} раз\n";
    }
}

echo "\n\nПроверка: всего уникальных номеров: " . count(array_unique($numbers)) . "\n";
