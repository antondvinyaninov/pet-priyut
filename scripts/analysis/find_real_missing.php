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
        $allFiles[] = [
            'path' => $file->getPathname(),
            'basename' => basename($file->getPathname(), '.docx')
        ];
    }
}

echo "Всего файлов: " . count($allFiles) . "\n";

// Получаем все номера карточек из базы
$importedNumbers = \App\Models\AnimalRegistrationCard::pluck('registration_number')->toArray();
echo "Импортировано карточек: " . count($importedNumbers) . "\n\n";

// Создаем список всех номеров из файлов (с нормализацией)
$filesByNumber = [];
foreach ($allFiles as $fileInfo) {
    $basename = $fileInfo['basename'];
    
    // Извлекаем номер из начала имени файла
    if (preg_match('/^(\d+)/', $basename, $matches)) {
        $number = $matches[1];
        
        // Создаем варианты номера (с нулями и без)
        $variants = [
            $number, // Как есть
            ltrim($number, '0') ?: '0', // Без ведущих нулей
            str_pad(ltrim($number, '0') ?: '0', 4, '0', STR_PAD_LEFT), // С 4 нулями
        ];
        
        $filesByNumber[$number] = [
            'file' => $fileInfo,
            'variants' => array_unique($variants)
        ];
    }
}

echo "Файлов с номерами: " . count($filesByNumber) . "\n\n";

// Находим недостающие (проверяем все варианты)
$missing = [];
foreach ($filesByNumber as $number => $data) {
    $found = false;
    
    foreach ($data['variants'] as $variant) {
        if (in_array($variant, $importedNumbers, true)) {
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        $missing[$number] = $data['file'];
    }
}

// Сортируем
ksort($missing);

echo "Недостающих файлов: " . count($missing) . "\n";
echo str_repeat("=", 100) . "\n";

$counter = 1;
foreach ($missing as $number => $fileInfo) {
    echo sprintf("%2d. №%s: %s\n", $counter++, $number, $fileInfo['basename']);
}

if (!empty($missing)) {
    echo "\n\nКоманды для импорта:\n";
    echo str_repeat("=", 100) . "\n";
    foreach ($missing as $number => $fileInfo) {
        echo 'php artisan animals:import-cards --file="' . $fileInfo['path'] . '"' . "\n";
    }
}
