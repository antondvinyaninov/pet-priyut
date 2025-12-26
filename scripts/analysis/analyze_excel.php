<?php
require_once __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = 'Журнал учета заявок.xlsx';

if (!file_exists($filePath)) {
    echo "Файл не найден: {$filePath}\n";
    exit(1);
}

// Загружаем Excel-файл
$spreadsheet = IOFactory::load($filePath);
$sheet = $spreadsheet->getActiveSheet();
$highestRow = $sheet->getHighestRow();
$highestColumn = $sheet->getHighestColumn();

echo "Анализ файла: {$filePath}\n";
echo "Всего строк: {$highestRow}\n";
echo "Наивысшая колонка: {$highestColumn}\n\n";

// Выводим заголовки (первая строка)
echo "ЗАГОЛОВКИ:\n";
$columnIndex = 0;
$columnLetter = 'A';

while (true) {
    $headerValue = $sheet->getCell($columnLetter . '1')->getValue();
    if (empty($headerValue) && $columnIndex > 15) {
        break;
    }
    echo "Колонка {$columnIndex} ({$columnLetter}): {$headerValue}\n";
    $columnIndex++;
    $columnLetter++;
}

// Анализируем значения в некоторых колонках (для мапинга)
$statusValues = [];
$animalTypes = [];
$districts = [];

// Определение колонок на основе заголовков
$statusColumn = 'M'; // Предполагаемая колонка статуса
$animalTypeColumn = 'G'; // Предполагаемая колонка типа животного
$districtColumn = 'E'; // Предполагаемая колонка района

// Берем образец из первых 50 строк или всех строк, если их меньше
$sampleSize = min($highestRow, 50);
for ($row = 2; $row <= $sampleSize; $row++) {
    // Статус
    $status = $sheet->getCell($statusColumn . $row)->getValue();
    if (!empty($status) && !in_array($status, $statusValues)) {
        $statusValues[] = $status;
    }
    
    // Тип животного
    $animalType = $sheet->getCell($animalTypeColumn . $row)->getValue();
    if (!empty($animalType) && !in_array($animalType, $animalTypes)) {
        $animalTypes[] = $animalType;
    }
    
    // Район
    $district = $sheet->getCell($districtColumn . $row)->getValue();
    if (!empty($district) && !in_array($district, $districts)) {
        $districts[] = $district;
    }
}

echo "\nУНИКАЛЬНЫЕ СТАТУСЫ:\n";
foreach ($statusValues as $status) {
    echo "- {$status}\n";
}

echo "\nУНИКАЛЬНЫЕ ТИПЫ ЖИВОТНЫХ:\n";
foreach ($animalTypes as $type) {
    echo "- {$type}\n";
}

echo "\nУНИКАЛЬНЫЕ РАЙОНЫ:\n";
foreach ($districts as $district) {
    echo "- {$district}\n";
}

// Вывод примера данных из первых 5 строк
echo "\nПРИМЕР ДАННЫХ (первые 5 строк):\n";
for ($row = 2; $row <= min(6, $highestRow); $row++) {
    echo "Строка {$row}:\n";
    $columnLetter = 'A';
    for ($col = 0; $col < 15; $col++) {
        $value = $sheet->getCell($columnLetter . $row)->getValue();
        echo "  Колонка {$col} ({$columnLetter}): {$value}\n";
        $columnLetter++;
    }
    echo "\n";
} 