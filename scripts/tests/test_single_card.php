<?php

require 'vendor/autoload.php';

$file = 'PetBasedoc/Вакцина №1 от 02.09.25г Собак           9 шт/08_№ 3524 вольер №81.docx';

echo "Тестирование парсинга файла: " . basename($file) . "\n";
echo str_repeat('=', 70) . "\n\n";

// Извлекаем текст альтернативным методом
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
    
    echo "Первые 10 строк текста:\n";
    echo str_repeat('-', 70) . "\n";
    $lines = explode("\n", $text);
    foreach (array_slice($lines, 0, 15) as $i => $line) {
        echo ($i+1) . ". [" . $line . "]\n";
    }
    
    echo "\n\nТестирование регулярных выражений:\n";
    echo str_repeat('-', 70) . "\n";
    
    // Тест 1: Номер карточки
    foreach ($lines as $line) {
        if (preg_match('/№\s*(\d+)\s+([\d\s]+(?:января|февраля|марта|апреля|мая|июня|июля|августа|сентября|октября|ноября|декабря)\s+\d{4})/ui', $line, $matches)) {
            echo "✓ Номер карточки найден: " . $matches[1] . ", дата: " . $matches[2] . "\n";
            break;
        }
    }
    
    // Тест 2: Категория
    foreach ($lines as $line) {
        if (preg_match('/категория\s+животного[:\s]+(собака|кошка)/ui', $line, $matches)) {
            echo "✓ Категория найдена: " . $matches[1] . "\n";
            break;
        }
    }
    
    // Тест 3: Пол
    foreach ($lines as $line) {
        if (preg_match('/пол[:\s]+(кобель|сука|кот|кошка)/ui', $line, $matches)) {
            echo "✓ Пол найден: " . $matches[1] . "\n";
            break;
        }
    }
    
    // Тест 4: Порода
    foreach ($lines as $line) {
        if (preg_match('/порода[:\s]+(.+)/ui', $line, $matches)) {
            echo "✓ Порода найдена: " . trim($matches[1]) . "\n";
            break;
        }
    }
    
    // Тест 5: Чип
    foreach ($lines as $line) {
        if (preg_match('/№\s+чипа[:\s]+([\d\s]+)/ui', $line, $matches)) {
            $chip = preg_replace('/\s+/', '', trim($matches[1]));
            echo "✓ Чип найден: " . $chip . "\n";
            break;
        }
    }
    
    // Тест 6: Бирка
    foreach ($lines as $line) {
        if (preg_match('/№\s+бирки\s*\(клейма\)[:\s]+№?\s*(\d+)/ui', $line, $matches)) {
            echo "✓ Бирка найдена: " . $matches[1] . "\n";
            break;
        }
    }
}
