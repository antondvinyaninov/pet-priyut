<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Получаем все номера карточек
$allNumbers = \App\Models\AnimalRegistrationCard::pluck('registration_number')->toArray();

// Фильтруем только числовые номера
$numericNumbers = array_filter($allNumbers, function($num) {
    return is_numeric($num);
});
$numericNumbers = array_map('intval', $numericNumbers);
sort($numericNumbers);

echo "Всего карточек: " . count($allNumbers) . "\n";
echo "Числовых номеров: " . count($numericNumbers) . "\n";
echo "Максимальный номер: " . max($numericNumbers) . "\n\n";

// Находим пропущенные номера от 1 до максимального
$missing = [];
for ($i = 1; $i <= max($numericNumbers); $i++) {
    if (!in_array($i, $numericNumbers)) {
        $missing[] = $i;
    }
}

echo "Пропущенные номера (" . count($missing) . "):\n";
echo str_repeat("=", 80) . "\n";
foreach ($missing as $num) {
    echo $num . "\n";
}

// Теперь найдем файлы с этими номерами
echo "\n\nПоиск файлов для пропущенных номеров:\n";
echo str_repeat("=", 80) . "\n";

$files = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator('PetBasedoc')
);

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'docx') {
        $files[] = $file->getPathname();
    }
}

foreach ($missing as $num) {
    $found = [];
    foreach ($files as $file) {
        $basename = basename($file, '.docx');
        // Ищем файлы начинающиеся с этого номера
        if (preg_match('/^0*' . $num . '[_\s№]/', $basename) || 
            preg_match('/^0*' . $num . '$/', $basename)) {
            $found[] = $file;
        }
    }
    
    if (!empty($found)) {
        echo "\n№{$num}:\n";
        foreach ($found as $file) {
            echo "  " . basename($file) . "\n";
        }
    }
}
