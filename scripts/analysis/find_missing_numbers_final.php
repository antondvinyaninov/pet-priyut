<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Получаем все номера из базы
$importedNumbers = \App\Models\AnimalRegistrationCard::pluck('registration_number')->toArray();

// Нормализуем номера (убираем ведущие нули для сравнения)
$normalizedImported = [];
foreach ($importedNumbers as $num) {
    if (preg_match('/^\d+$/', $num)) {
        $normalizedImported[] = (int)$num;
    }
}
$normalizedImported = array_unique($normalizedImported);
sort($normalizedImported);

echo "Импортировано уникальных числовых номеров: " . count($normalizedImported) . "\n";
echo "Максимальный номер: " . max($normalizedImported) . "\n\n";

// Находим пропущенные номера от 1 до максимального
$missing = [];
for ($i = 1; $i <= max($normalizedImported); $i++) {
    if (!in_array($i, $normalizedImported)) {
        $missing[] = $i;
    }
}

echo "Пропущенные номера (" . count($missing) . "):\n";
echo str_repeat("=", 100) . "\n";
foreach ($missing as $num) {
    echo $num . "\n";
}

// Теперь найдем файлы с этими номерами
echo "\n\nПоиск файлов для пропущенных номеров:\n";
echo str_repeat("=", 100) . "\n";

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

$foundFiles = [];
foreach ($missing as $num) {
    foreach ($allFiles as $fileInfo) {
        $basename = $fileInfo['basename'];
        
        // Ищем файлы начинающиеся с этого номера (с нулями или без)
        $patterns = [
            '/^0*' . $num . '[_\s№]/',  // Начинается с номера
            '/^0*' . $num . '$/',        // Только номер
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $basename)) {
                $foundFiles[$num] = $fileInfo;
                break 2;
            }
        }
    }
}

foreach ($foundFiles as $num => $fileInfo) {
    echo "\n№{$num}: {$fileInfo['basename']}\n";
    echo "  Путь: {$fileInfo['path']}\n";
}

if (!empty($foundFiles)) {
    echo "\n\nКоманды для импорта (" . count($foundFiles) . " файлов):\n";
    echo str_repeat("=", 100) . "\n";
    foreach ($foundFiles as $num => $fileInfo) {
        echo 'php artisan animals:import-cards --file="' . $fileInfo['path'] . '"' . "\n";
    }
}
