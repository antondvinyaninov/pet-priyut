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

// Получаем все номера карточек из базы (с сохранением ведущих нулей!)
$importedNumbers = \App\Models\AnimalRegistrationCard::pluck('registration_number')->toArray();

echo "Импортировано карточек: " . count($importedNumbers) . "\n\n";

// Извлекаем номера из имен файлов
$fileNumbers = [];
foreach ($allFiles as $fileInfo) {
    $basename = $fileInfo['basename'];
    
    // Извлекаем номер из начала имени файла (с сохранением ведущих нулей!)
    if (preg_match('/^(\d+)/', $basename, $matches)) {
        $number = $matches[1]; // Сохраняем как есть, с нулями
        $fileNumbers[$number] = $fileInfo;
    }
}

echo "Файлов с номерами: " . count($fileNumbers) . "\n\n";

// Находим недостающие (точное сравнение строк!)
$missing = [];
foreach ($fileNumbers as $number => $fileInfo) {
    if (!in_array($number, $importedNumbers, true)) { // Строгое сравнение
        $missing[$number] = $fileInfo;
    }
}

// Сортируем по номеру
ksort($missing);

echo "Недостающих файлов: " . count($missing) . "\n";
echo str_repeat("=", 100) . "\n";

foreach ($missing as $number => $fileInfo) {
    echo "№{$number}: {$fileInfo['basename']}\n";
}

if (!empty($missing)) {
    echo "\n\nКоманды для импорта:\n";
    echo str_repeat("=", 100) . "\n";
    foreach ($missing as $number => $fileInfo) {
        echo 'php artisan animals:import-cards --file="' . $fileInfo['path'] . '"' . "\n";
    }
}
