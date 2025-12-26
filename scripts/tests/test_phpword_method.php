<?php

require 'vendor/autoload.php';

use PhpOffice\PhpWord\IOFactory;

$file = 'PetBasedoc/Вакцина №1 от 02.09.25г Собак           9 шт/08_№ 3524 вольер №81.docx';

echo "Тестирование PHPWord метода\n";
echo str_repeat('=', 70) . "\n\n";

try {
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
    
    echo "Длина извлеченного текста: " . strlen($text) . "\n";
    echo "Первые 500 символов:\n";
    echo str_repeat('-', 70) . "\n";
    echo substr($text, 0, 500) . "\n";
    
} catch (\Exception $e) {
    restore_error_handler();
    echo "Ошибка: " . $e->getMessage() . "\n";
}
