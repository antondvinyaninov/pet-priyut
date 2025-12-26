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

echo "Всего файлов .docx: " . count($files) . "\n";

// Получаем все импортированные карточки
$importedCards = \App\Models\AnimalRegistrationCard::pluck('registration_number')->toArray();
echo "Импортировано карточек: " . count($importedCards) . "\n\n";

// Создаем список всех файлов с их предполагаемыми номерами
$fileList = [];
foreach ($files as $file) {
    $basename = basename($file, '.docx');
    $fileList[] = [
        'file' => $file,
        'basename' => $basename,
    ];
}

// Сортируем по имени файла
usort($fileList, function($a, $b) {
    return strcmp($a['basename'], $b['basename']);
});

echo "Список всех файлов:\n";
echo str_repeat("=", 100) . "\n";
$counter = 1;
foreach ($fileList as $item) {
    echo sprintf("%3d. %s\n", $counter++, $item['basename']);
}

echo "\n\nВсего уникальных файлов: " . count($fileList) . "\n";
echo "Разница: " . (count($fileList) - count($importedCards)) . " файлов не импортировано\n";
