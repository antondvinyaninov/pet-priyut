<?php

require 'vendor/autoload.php';

use PhpOffice\PhpWord\IOFactory;

$filePath = 'PetBasedoc/Вакцина №1 от 02.09.25г Собак           9 шт/08_№ 3524 вольер №81.docx';

echo "Парсинг файла: " . basename($filePath) . "\n";
echo str_repeat('=', 70) . "\n\n";

// Извлекаем текст альтернативным способом
$zip = new ZipArchive();
if ($zip->open($filePath) === true) {
    $xml = $zip->getFromName('word/document.xml');
    $text = strip_tags($xml);
    
    // Извлекаем изображения
    $images = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $filename = $zip->getNameIndex($i);
        if (preg_match('/word\/media\/(image\d+\.(jpeg|jpg|png|gif))/i', $filename, $matches)) {
            $imageData = $zip->getFromName($filename);
            if ($imageData) {
                $images[] = [
                    'filename' => $matches[1],
                    'size' => strlen($imageData)
                ];
            }
        }
    }
    $zip->close();
    
    // Парсим данные
    $data = [];
    $fileName = basename($filePath, '.docx');
    $folderName = basename(dirname($filePath));
    
    // Из имени файла
    if (preg_match('/(\d+)_№\s*(\d+)\s+вольер\s*№(\d+)/ui', $fileName, $matches)) {
        $data['file_number'] = $matches[1];
        $data['request_number'] = $matches[2];
        $data['cage_number'] = $matches[3];
    }
    
    // Из имени папки
    if (preg_match('/Вакцина\s*№(\d+)\s*от\s*([\d.]+)/ui', $folderName, $matches)) {
        $data['vaccine_number'] = $matches[1];
        $data['vaccine_date'] = $matches[2];
    }
    
    $lines = explode("\n", $text);
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        // Номер карточки
        if (preg_match('/^№\s*(\d+)\s+([\d\s]+(?:ноября|января|февраля|марта|апреля|мая|июня|июля|августа|сентября|октября|декабря)\s+\d{4})/ui', $line, $matches)) {
            $data['card_number'] = $matches[1];
            $data['card_date_raw'] = $matches[2];
        }
        
        // Категория
        if (preg_match('/категория\s+животного[:\s]+(собака|кошка)/ui', $line, $matches)) {
            $data['type'] = mb_strtolower($matches[1]) === 'собака' ? 'dog' : 'cat';
        }
        
        // Дата поступления
        if (preg_match('/дата\s+поступления[:\s]+([\d\s]+(?:ноября|января|февраля|марта|апреля|мая|июня|июля|августа|сентября|октября|декабря)\s+\d{4})/ui', $line, $matches)) {
            $data['arrived_at_raw'] = $matches[1];
        }
        
        // Пол
        if (preg_match('/пол[:\s]+(кобель|сука|кот|кошка)/ui', $line, $matches)) {
            $gender = mb_strtolower($matches[1]);
            $data['gender'] = in_array($gender, ['кобель', 'кот']) ? 'male' : 'female';
        }
        
        // Порода
        if (preg_match('/порода[:\s]+(.+?)(?:окрас|$)/ui', $line, $matches)) {
            $data['breed'] = trim($matches[1]);
        }
        
        // Окрас
        if (preg_match('/окрас[:\s]+(.+?)(?:шерсть|$)/ui', $line, $matches)) {
            $data['color'] = trim($matches[1]);
        }
        
        // Шерсть
        if (preg_match('/шерсть[:\s]+(.+?)(?:уши|$)/ui', $line, $matches)) {
            $data['coat'] = trim($matches[1]);
        }
        
        // Уши
        if (preg_match('/уши[:\s]+(.+?)(?:хвост|$)/ui', $line, $matches)) {
            $data['ears'] = trim($matches[1]);
        }
        
        // Хвост
        if (preg_match('/хвост[:\s]+(.+?)(?:размер|$)/ui', $line, $matches)) {
            $data['tail'] = trim($matches[1]);
        }
        
        // Размер и вес
        if (preg_match('/размер[:\s]+(.+?)(\d+)\s*кг/ui', $line, $matches)) {
            $data['size'] = trim($matches[1]);
            $data['weight'] = $matches[2];
        }
        
        // Возраст
        if (preg_match('/возраст[:\s\(]*примерный[:\s\)]*[:\s]+(.+?)(?:особые|$)/ui', $line, $matches)) {
            $data['age'] = trim($matches[1]);
        }
        
        // Особые приметы
        if (preg_match('/особые\s+приметы[:\s]+(.+?)(?:акт|$)/ui', $line, $matches)) {
            $special = trim($matches[1]);
            if (!in_array($special, ['-', '–', ''])) {
                $data['special_marks'] = $special;
            }
        }
        
        // Акт приема-передачи
        if (preg_match('/акт\s+приёма-передачи[:\s]+№\s*(\d+)\s+от\s+([\d.]+)/ui', $line, $matches)) {
            $data['capture_act_number'] = $matches[1];
            $data['capture_act_date'] = $matches[2];
        }
        
        // Адрес отлова
        if (preg_match('/адрес\s+и\s+описание\s+места\s+отлова[:\s]+(.+?)(?:дата\s+клинического|$)/ui', $line, $matches)) {
            $data['capture_location'] = trim($matches[1]);
        }
        
        // Клинический осмотр
        if (preg_match('/дата\s+клинического\s+осмотра[,\s]+заключение[:\s]+([\d\s]+(?:ноября|января|февраля|марта|апреля|мая|июня|июля|августа|сентября|октября|декабря)\s+\d{4})\s*г?\.?\s+(.+?)(?:информация|$)/ui', $line, $matches)) {
            $data['clinical_exam_date_raw'] = $matches[1];
            $data['clinical_exam_conclusion'] = trim($matches[2]);
        }
        
        // Дегельминтизация
        if (preg_match('/дата\s+дегельминтизации[:\s]+([\d.]+)/ui', $line, $matches)) {
            $data['deworming_date'] = $matches[1];
        }
        
        // Стерилизация
        if (preg_match('/дата\s+стерилизации[:\s]+([\d.]+)/ui', $line, $matches)) {
            $data['sterilization_date'] = $matches[1];
        }
        
        // Ветеринар
        if (preg_match('/специалиста.*стерилизации[:\s]+(.+?)(?:дата\s+маркирования|$)/ui', $line, $matches)) {
            $data['sterilization_vet'] = trim($matches[1]);
        }
        
        // Дата маркирования
        if (preg_match('/дата\s+маркирования[:\s]+([\d.]+)/ui', $line, $matches)) {
            $data['marking_date'] = $matches[1];
        }
        
        // Бирка
        if (preg_match('/№\s+бирки[:\s\(]*клейма[:\s\)]*[:\s]+№?\s*(\d+)/ui', $line, $matches)) {
            $data['tag_number'] = $matches[1];
        }
        
        // Чип
        if (preg_match('/№\s+чипа[:\s]+([\d\s]+)/ui', $line, $matches)) {
            $chip = preg_replace('/\s+/', '', trim($matches[1]));
            if (!empty($chip) && $chip !== '-') {
                $data['chip_number'] = $chip;
            }
        }
    }
    
    // Выводим результаты
    echo "ИЗВЛЕЧЕННЫЕ ДАННЫЕ:\n";
    echo str_repeat('-', 70) . "\n\n";
    
    echo "Из имени файла:\n";
    echo "  Номер файла: " . ($data['file_number'] ?? '-') . "\n";
    echo "  Номер заявки: " . ($data['request_number'] ?? '-') . "\n";
    echo "  Вольер: " . ($data['cage_number'] ?? '-') . "\n\n";
    
    echo "Из имени папки:\n";
    echo "  Вакцина №: " . ($data['vaccine_number'] ?? '-') . "\n";
    echo "  Дата вакцинации: " . ($data['vaccine_date'] ?? '-') . "\n\n";
    
    echo "Основная информация:\n";
    echo "  Номер карточки: " . ($data['card_number'] ?? '-') . "\n";
    echo "  Дата карточки: " . ($data['card_date_raw'] ?? '-') . "\n";
    echo "  Тип: " . ($data['type'] ?? '-') . "\n";
    echo "  Дата поступления: " . ($data['arrived_at_raw'] ?? '-') . "\n\n";
    
    echo "Описание животного:\n";
    echo "  Пол: " . ($data['gender'] ?? '-') . "\n";
    echo "  Порода: " . ($data['breed'] ?? '-') . "\n";
    echo "  Окрас: " . ($data['color'] ?? '-') . "\n";
    echo "  Шерсть: " . ($data['coat'] ?? '-') . "\n";
    echo "  Уши: " . ($data['ears'] ?? '-') . "\n";
    echo "  Хвост: " . ($data['tail'] ?? '-') . "\n";
    echo "  Размер: " . ($data['size'] ?? '-') . "\n";
    echo "  Вес: " . ($data['weight'] ?? '-') . " кг\n";
    echo "  Возраст: " . ($data['age'] ?? '-') . "\n";
    echo "  Особые приметы: " . ($data['special_marks'] ?? '-') . "\n\n";
    
    echo "Идентификация:\n";
    echo "  Чип: " . ($data['chip_number'] ?? '-') . "\n";
    echo "  Бирка: " . ($data['tag_number'] ?? '-') . "\n\n";
    
    echo "Медицинская информация:\n";
    echo "  Акт отлова №: " . ($data['capture_act_number'] ?? '-') . "\n";
    echo "  Дата акта: " . ($data['capture_act_date'] ?? '-') . "\n";
    echo "  Место отлова: " . ($data['capture_location'] ?? '-') . "\n";
    echo "  Клинический осмотр: " . ($data['clinical_exam_date_raw'] ?? '-') . "\n";
    echo "  Заключение: " . ($data['clinical_exam_conclusion'] ?? '-') . "\n";
    echo "  Дегельминтизация: " . ($data['deworming_date'] ?? '-') . "\n";
    echo "  Стерилизация: " . ($data['sterilization_date'] ?? '-') . "\n";
    echo "  Ветеринар: " . ($data['sterilization_vet'] ?? '-') . "\n";
    echo "  Маркирование: " . ($data['marking_date'] ?? '-') . "\n\n";
    
    echo "Изображения:\n";
    foreach ($images as $i => $img) {
        echo "  " . ($i+1) . ". " . $img['filename'] . " (" . round($img['size']/1024, 2) . " KB)\n";
    }
    echo "  Всего: " . count($images) . " фото\n";
}
