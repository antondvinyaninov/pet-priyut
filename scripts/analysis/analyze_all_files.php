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
$importedCards = \App\Models\AnimalRegistrationCard::pluck('registration_number')->sort()->values()->toArray();
echo "Импортировано карточек: " . count($importedCards) . "\n";
echo "Диапазон: от {$importedCards[0]} до {$importedCards[count($importedCards)-1]}\n\n";

// Анализируем все файлы
$parsedFiles = [];
$unparsedFiles = [];

foreach ($files as $file) {
    $basename = basename($file, '.docx');
    
    // Пытаемся извлечь номер разными способами
    $number = null;
    
    // Формат: "0001", "01_№ 2156", "308_№ 4235"
    if (preg_match('/^(\d+)/', $basename, $matches)) {
        $number = (int)$matches[1];
    }
    // Формат: "№179. 5433"
    elseif (preg_match('/№(\d+)/', $basename, $matches)) {
        $number = (int)$matches[1];
    }
    
    if ($number !== null) {
        $parsedFiles[$number] = $file;
    } else {
        $unparsedFiles[] = $file;
    }
}

echo "Файлов с распознанными номерами: " . count($parsedFiles) . "\n";
echo "Файлов без номера: " . count($unparsedFiles) . "\n\n";

if (!empty($unparsedFiles)) {
    echo "Файлы без распознанного номера:\n";
    echo str_repeat("=", 80) . "\n";
    foreach ($unparsedFiles as $file) {
        echo basename($file) . "\n";
    }
    echo "\n";
}

// Находим недостающие
$missing = [];
foreach ($parsedFiles as $number => $file) {
    if (!in_array($number, $importedCards)) {
        $missing[$number] = $file;
    }
}

echo "Недостающие карточки с номерами (" . count($missing) . "):\n";
echo str_repeat("=", 80) . "\n";
ksort($missing);
foreach ($missing as $number => $file) {
    echo "№{$number}: " . basename($file) . "\n";
}

// Проверяем дубликаты номеров в файлах
$duplicateFiles = [];
foreach ($parsedFiles as $number => $file) {
    if (!isset($duplicateFiles[$number])) {
        $duplicateFiles[$number] = [];
    }
    $duplicateFiles[$number][] = $file;
}
$duplicateFiles = array_filter($duplicateFiles, function($files) { return count($files) > 1; });

if (!empty($duplicateFiles)) {
    echo "\n\nДубликаты номеров в файлах:\n";
    echo str_repeat("=", 80) . "\n";
    foreach ($duplicateFiles as $number => $files) {
        echo "№{$number} (" . count($files) . " файлов):\n";
        foreach ($files as $file) {
            echo "  - " . basename($file) . "\n";
        }
    }
}
