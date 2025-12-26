<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Получаем все файлы .docx
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

// Получаем все импортированные животные
$animals = \App\Models\Animal::with('registrationCard')->get();
echo "Импортировано животных: " . count($animals) . "\n\n";

// Создаем список всех номеров карточек из базы
$importedNumbers = [];
foreach ($animals as $animal) {
    if ($animal->registrationCard && $animal->registrationCard->registration_number) {
        $importedNumbers[] = $animal->registrationCard->registration_number;
    }
}

// Создаем список файлов с их номерами
$filesByNumber = [];
foreach ($allFiles as $file) {
    $basename = basename($file, '.docx');
    
    // Извлекаем номер из имени файла
    $number = null;
    
    // Пробуем разные форматы
    if (preg_match('/^(\d+)/', $basename, $matches)) {
        $number = $matches[1];
        // Убираем ведущие нули для сравнения
        $numberInt = (int)$matches[1];
    } elseif (preg_match('/№(\d+)/', $basename, $matches)) {
        $number = $matches[1];
        $numberInt = (int)$matches[1];
    }
    
    if ($number !== null) {
        $filesByNumber[$number] = [
            'file' => $file,
            'basename' => $basename,
            'number_int' => $numberInt
        ];
    }
}

echo "Файлов с распознанными номерами: " . count($filesByNumber) . "\n\n";

// Находим недостающие файлы
$missing = [];
foreach ($filesByNumber as $number => $info) {
    $found = false;
    
    // Проверяем разные варианты номера
    foreach ($importedNumbers as $imported) {
        if ($imported == $number || $imported == $info['number_int'] || 
            (int)$imported == $info['number_int']) {
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        $missing[] = $info;
    }
}

// Сортируем по числовому значению номера
usort($missing, function($a, $b) {
    return $a['number_int'] - $b['number_int'];
});

echo "Недостающие файлы (" . count($missing) . "):\n";
echo str_repeat("=", 100) . "\n";
foreach ($missing as $info) {
    echo sprintf("№%s: %s\n", $info['number_int'], $info['basename']);
}

// Выводим команды для импорта
if (!empty($missing)) {
    echo "\n\nКоманды для импорта:\n";
    echo str_repeat("=", 100) . "\n";
    foreach ($missing as $info) {
        echo 'php artisan animals:import-cards --file="' . $info['file'] . '"' . "\n";
    }
}
