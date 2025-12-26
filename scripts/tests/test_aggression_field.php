<?php

$filePath = "PetBasedoc/Вакцина №1 от 02.09.25г Собак           9 шт/08_№ 3524 вольер №81.docx";

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

echo "Тестирование парсинга поля агрессивного поведения\n";
echo str_repeat('=', 70) . "\n\n";

// Парсим агрессивное поведение
if (preg_match('/информация\s+о\s+наличии\s*\(отсутствии\)\s+у\s+животного\s+агрессивного\s+поведения[:\s]+(.+?)(?:информация\s+о\s+мероприятиях|вакцинация|$)/uis', $normalizedText, $matches)) {
    $aggression = trim($matches[1]);
    echo "✅ Агрессивное поведение найдено!\n\n";
    echo "Длина текста: " . strlen($aggression) . " символов\n\n";
    echo "Полный текст:\n";
    echo str_repeat('-', 70) . "\n";
    echo $aggression . "\n";
    echo str_repeat('-', 70) . "\n\n";
    
    // Проверяем, что все даты присутствуют
    $dates = [];
    if (preg_match_all('/([\d.]+)\s*г/u', $aggression, $dateMatches)) {
        $dates = $dateMatches[1];
        echo "Найдено дат: " . count($dates) . "\n";
        foreach ($dates as $date) {
            echo "  - " . $date . "\n";
        }
    }
    
} else {
    echo "❌ Агрессивное поведение НЕ найдено\n";
}

echo "\n" . str_repeat('=', 70) . "\n";
