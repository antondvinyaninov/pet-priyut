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
echo "Всего животных в БД: " . \App\Models\Animal::count() . "\n\n";

// Получаем все бирки из базы
$importedTags = \App\Models\Animal::whereNotNull('tag_number')
    ->where('tag_number', '!=', '')
    ->pluck('tag_number')
    ->toArray();

echo "Животных с бирками в БД: " . count($importedTags) . "\n\n";

// Извлекаем бирки из имен файлов и ищем недостающие
$missing = [];
foreach ($allFiles as $fileInfo) {
    $basename = $fileInfo['basename'];
    
    // Ищем номер бирки в имени файла (обычно после №)
    if (preg_match('/№\s*(\d+)/', $basename, $matches)) {
        $tagNumber = $matches[1];
        
        // Проверяем есть ли эта бирка в базе
        if (!in_array($tagNumber, $importedTags)) {
            $missing[] = [
                'file' => $fileInfo['path'],
                'basename' => $basename,
                'tag' => $tagNumber
            ];
        }
    } elseif (preg_match('/^(\d+)_/', $basename, $matches)) {
        // Файлы типа "0025.docx" или "327 изменено"
        $tagNumber = $matches[1];
        
        if (!in_array($tagNumber, $importedTags) && !in_array(ltrim($tagNumber, '0'), $importedTags)) {
            $missing[] = [
                'file' => $fileInfo['path'],
                'basename' => $basename,
                'tag' => $tagNumber
            ];
        }
    }
}

echo "Недостающих файлов: " . count($missing) . "\n";
echo str_repeat("=", 100) . "\n";

foreach ($missing as $info) {
    echo "Бирка №{$info['tag']}: {$info['basename']}\n";
}

if (!empty($missing)) {
    echo "\n\nКоманды для импорта:\n";
    echo str_repeat("=", 100) . "\n";
    foreach ($missing as $info) {
        echo 'php artisan animals:import-cards --file="' . $info['file'] . '"' . "\n";
    }
}
