<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Получаем все файлы .docx
$files = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator('PetBasedoc')
);

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'docx') {
        $files[] = $file->getPathname();
    }
}

echo "Всего файлов .docx: " . count($files) . "\n\n";

// Получаем все импортированные карточки
$importedCards = \App\Models\AnimalRegistrationCard::pluck('registration_number')->toArray();
echo "Импортировано карточек: " . count($importedCards) . "\n\n";

// Извлекаем номера из имен файлов
$fileNumbers = [];
foreach ($files as $file) {
    $basename = basename($file, '.docx');
    
    // Пытаемся извлечь номер карточки из имени файла
    // Форматы: "0001", "01_№ 2156", "308_№ 4235", и т.д.
    if (preg_match('/^(\d+)/', $basename, $matches)) {
        $number = (int)$matches[1];
        $fileNumbers[$number] = $file;
    }
}

echo "Найдено номеров в файлах: " . count($fileNumbers) . "\n\n";

// Находим недостающие
$missing = [];
foreach ($fileNumbers as $number => $file) {
    if (!in_array($number, $importedCards)) {
        $missing[$number] = $file;
    }
}

echo "Недостающие карточки (" . count($missing) . "):\n";
echo str_repeat("=", 80) . "\n";
foreach ($missing as $number => $file) {
    echo "№{$number}: {$file}\n";
}

// Также проверим, может быть есть дубликаты
$duplicates = array_count_values($importedCards);
$duplicates = array_filter($duplicates, function($count) { return $count > 1; });

if (!empty($duplicates)) {
    echo "\n\nДубликаты в базе:\n";
    echo str_repeat("=", 80) . "\n";
    foreach ($duplicates as $number => $count) {
        echo "№{$number}: {$count} раз\n";
    }
}
