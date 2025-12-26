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
$importedCards = \App\Models\AnimalRegistrationCard::pluck('registration_number', 'id')->toArray();
echo "Импортировано карточек: " . count($importedCards) . "\n\n";

// Проверяем дубликаты в базе
$duplicates = array_count_values($importedCards);
$duplicates = array_filter($duplicates, function($count) { return $count > 1; });

if (!empty($duplicates)) {
    echo "Дубликаты номеров в базе:\n";
    echo str_repeat("=", 80) . "\n";
    foreach ($duplicates as $number => $count) {
        echo "№{$number}: {$count} раз\n";
        
        // Показываем ID животных с этим номером
        $animals = \App\Models\Animal::whereHas('registrationCard', function($q) use ($number) {
            $q->where('registration_number', $number);
        })->get();
        
        foreach ($animals as $animal) {
            echo "  - Animal ID: {$animal->id}, Кличка: {$animal->name}, Вольер: {$animal->cage_number}\n";
        }
    }
    echo "\n";
}

// Анализируем файлы с дубликатами номеров
$filesByNumber = [];
foreach ($files as $file) {
    $basename = basename($file, '.docx');
    
    $number = null;
    if (preg_match('/^(\d+)/', $basename, $matches)) {
        $number = (int)$matches[1];
    } elseif (preg_match('/№(\d+)/', $basename, $matches)) {
        $number = (int)$matches[1];
    }
    
    if ($number !== null) {
        if (!isset($filesByNumber[$number])) {
            $filesByNumber[$number] = [];
        }
        $filesByNumber[$number][] = $file;
    }
}

$fileDuplicates = array_filter($filesByNumber, function($files) { return count($files) > 1; });

if (!empty($fileDuplicates)) {
    echo "Дубликаты номеров в файлах:\n";
    echo str_repeat("=", 80) . "\n";
    ksort($fileDuplicates);
    foreach ($fileDuplicates as $number => $files) {
        echo "№{$number} (" . count($files) . " файлов):\n";
        foreach ($files as $file) {
            echo "  - " . basename($file) . "\n";
        }
        echo "\n";
    }
}

// Проверяем какие номера есть в файлах, но нет в базе
$allFileNumbers = array_keys($filesByNumber);
$allDbNumbers = array_values($importedCards);

$missingInDb = array_diff($allFileNumbers, $allDbNumbers);
sort($missingInDb);

if (!empty($missingInDb)) {
    echo "Номера есть в файлах, но нет в базе (" . count($missingInDb) . "):\n";
    echo str_repeat("=", 80) . "\n";
    foreach ($missingInDb as $number) {
        echo "№{$number}:\n";
        foreach ($filesByNumber[$number] as $file) {
            echo "  - " . basename($file) . "\n";
        }
    }
}
