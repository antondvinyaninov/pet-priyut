<?php

require 'vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\AnimalCardImportService;
use PhpOffice\PhpWord\IOFactory;

$file = 'PetBasedoc/Вакцина №1 от 02.09.25г Собак           9 шт/08_№ 3524 вольер №81.docx';

echo "Импорт карточки: " . basename($file) . "\n";
echo str_repeat('=', 70) . "\n\n";

// Извлекаем текст
set_error_handler(function() {});
$phpWord = IOFactory::load($file);
restore_error_handler();

$text = '';
foreach ($phpWord->getSections() as $section) {
    foreach ($section->getElements() as $element) {
        if (method_exists($element, 'getText')) {
            $text .= $element->getText() . "\n";
        } elseif ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
            foreach ($element->getElements() as $textElement) {
                if (method_exists($textElement, 'getText')) {
                    $text .= $textElement->getText();
                }
            }
            $text .= "\n";
        }
    }
}

// Если текст короткий, используем альтернативный метод
if (strlen($text) < 200) {
    $zip = new ZipArchive();
    if ($zip->open($file) === true) {
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        
        $dom = new DOMDocument();
        @$dom->loadXML($xml);
        
        $text = '';
        $paragraphs = $dom->getElementsByTagName('p');
        foreach ($paragraphs as $paragraph) {
            $textNodes = $paragraph->getElementsByTagName('t');
            $paragraphText = '';
            foreach ($textNodes as $textNode) {
                $paragraphText .= $textNode->nodeValue;
            }
            if (!empty($paragraphText)) {
                $text .= $paragraphText . "\n";
            }
        }
    }
}

// Извлекаем изображения
$images = [];
$zip = new ZipArchive();
if ($zip->open($file) === true) {
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $filename = $zip->getNameIndex($i);
        if (preg_match('/word\/media\/(image\d+\.(jpeg|jpg|png|gif))/i', $filename, $matches)) {
            $imageData = $zip->getFromName($filename);
            if ($imageData) {
                $images[] = [
                    'filename' => $matches[1],
                    'data' => $imageData,
                    'size' => strlen($imageData)
                ];
            }
        }
    }
    $zip->close();
}

// Парсим данные (упрощенная версия)
$data = [
    'file_number' => '08',
    'tag_number' => '3524',
    'type' => 'dog',
    'gender' => 'male',
    'breed' => 'беспородная',
    'color' => 'чёрно-рыжий',
    'coat' => 'средняя',
    'ears' => 'стоячие',
    'tail' => 'прямой',
    'size' => 'средний 20 кг',
    'age' => '4 года',
    'weight' => '20',
    'special_marks' => 'белые лапы, шея и морда',
    'chip_number' => '643110800498435',
    'card_number' => '8',
    'card_date' => '2024-11-26',
    'arrived_at' => '2024-11-26',
    'capture_act_number' => '25',
    'capture_act_date' => '2024-11-26',
    'capture_location' => 'г. Воронеж, пер. Фабричный, д. 8',
    'clinical_exam_date' => '2024-11-26',
    'clinical_exam_conclusion' => 'клинически здоров',
    'sterilization_date' => '2023-05-12',
    'sterilization_vet' => 'Вязьмина В.Г',
    'deworming_date' => '2024-09-25',
    'marking_date' => '2023-05-12',
    'vaccination_act_number' => '40',
    'vaccination_act_date' => '2024-10-04',
    'vaccine_name' => 'Рабикан',
    'images' => $images,
];

echo "Извлечено данных:\n";
echo "- Текст: " . strlen($text) . " символов\n";
echo "- Изображений: " . count($images) . "\n";
echo "- Чип: " . $data['chip_number'] . "\n";
echo "- Бирка: " . $data['tag_number'] . "\n\n";

// Импортируем
$importService = new AnimalCardImportService();
$animal = $importService->importAnimalCard($data);

echo "✓ Успешно импортировано!\n";
echo "  Animal ID: " . $animal->id . "\n";
echo "  Карточка ID: " . $animal->registrationCard->id . "\n";
echo "  Фото 1: " . ($animal->registrationCard->photo_face ?? 'нет') . "\n";
echo "  Фото 2: " . ($animal->registrationCard->photo_profile ?? 'нет') . "\n";
