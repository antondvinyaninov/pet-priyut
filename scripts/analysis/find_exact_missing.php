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

// Получаем все импортированные животные с их исходными файлами
$animals = \App\Models\Animal::with('registrationCard')->get();
echo "Импортировано животных: " . count($animals) . "\n\n";

// Создаем список импортированных файлов
$importedFiles = [];
foreach ($animals as $animal) {
    if ($animal->registrationCard && $animal->registrationCard->source_file) {
        $basename = basename($animal->registrationCard->source_file, '.docx');
        $importedFiles[$basename] = true;
    }
}

echo "Файлов с записью source_file: " . count($importedFiles) . "\n\n";

// Находим недостающие файлы
$missingFiles = [];
foreach ($files as $file) {
    $basename = basename($file, '.docx');
    if (!isset($importedFiles[$basename])) {
        $missingFiles[] = $file;
    }
}

echo "Недостающие файлы (" . count($missingFiles) . "):\n";
echo str_repeat("=", 100) . "\n";
foreach ($missingFiles as $file) {
    echo basename($file) . "\n";
    echo "  Путь: $file\n\n";
}
