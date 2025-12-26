<?php

require __DIR__.'/vendor/autoload.php';

$filePath = "PetBasedoc/Вакцина №1 от 02.09.25г Собак           9 шт/08_№ 3524 вольер №81.docx";

// Извлекаем текст
function extractTextAlternative($filePath) {
    $zip = new \ZipArchive();
    if ($zip->open($filePath) === true) {
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        
        if ($xml) {
            $dom = new \DOMDocument();
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
            
            return $text;
        }
    }
    
    return null;
}

$text = extractTextAlternative($filePath);
$normalizedText = preg_replace('/\s+/', ' ', $text);

echo "Тестирование парсинга карточки №8\n";
echo str_repeat('=', 70) . "\n\n";

// Тестируем парсинг агрессивного поведения
if (preg_match('/информация\s+о\s+наличии\s*\(отсутствии\)\s+у\s+животного\s+агрессивного\s+поведения[:\s]+(.+?)(?:информация\s+о\s+мероприятиях|вакцинация|$)/uis', $normalizedText, $matches)) {
    echo "✅ Агрессивное поведение найдено:\n";
    echo "   " . mb_substr(trim($matches[1]), 0, 100) . "...\n\n";
} else {
    echo "❌ Агрессивное поведение НЕ найдено\n\n";
}

// Тестируем парсинг коррекции поведения
if (preg_match('/информация\s+о\s+мероприятиях\s+по\s+корректировке\s+поведения\s+животного[:\s]+(.+?)(?:вакцинация|дата\s+дегельминтизации|$)/uis', $normalizedText, $matches)) {
    echo "✅ Коррекция поведения найдена:\n";
    echo "   " . trim($matches[1]) . "\n\n";
} else {
    echo "❌ Коррекция поведения НЕ найдена\n\n";
}

// Тестируем парсинг адреса возвращения
if (preg_match('/адрес\s+и\s+описание\s+места\s+возвращения\s*\(размещения\)[:\s]+(.+?)(?:данные\s+на\s+новых|для\s+юридических|$)/uis', $normalizedText, $matches)) {
    echo "✅ Адрес возвращения найден:\n";
    echo "   " . trim($matches[1]) . "\n\n";
} else {
    echo "❌ Адрес возвращения НЕ найден\n\n";
}

// Тестируем парсинг вакцинации
if (preg_match('/вакцинация[,\s]+вид\s+прививки[,\s]+акт[:\s\(]+дата[,\s]+№[:\s\)]+№?\s*(\d+)\s+от\s+([\d.]+)\s*г?\.?\s+(.+?)(?:серия|дата\s+дегельминтизации|$)/uis', $normalizedText, $matches)) {
    echo "✅ Вакцинация найдена:\n";
    echo "   Акт №: " . $matches[1] . "\n";
    echo "   Дата: " . $matches[2] . "\n";
    echo "   Название: " . trim($matches[3]) . "\n\n";
} else {
    echo "❌ Вакцинация НЕ найдена\n\n";
}

// Тестируем парсинг клинического осмотра
if (preg_match('/дата\s+клинического\s+осмотра[,\s]+заключение[:\s]+([\d\s]+(?:ноября|января|февраля|марта|апреля|мая|июня|июля|августа|сентября|октября|декабря)\s+\d{4})\s*г?\.?\s+(.+?)(?:информация|$)/uis', $normalizedText, $matches)) {
    echo "✅ Клинический осмотр найден:\n";
    echo "   Дата: " . trim($matches[1]) . "\n";
    echo "   Заключение: " . trim($matches[2]) . "\n\n";
} else {
    echo "❌ Клинический осмотр НЕ найден\n\n";
}

echo str_repeat('=', 70) . "\n";
