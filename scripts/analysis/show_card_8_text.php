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
echo $text;
