<?php
require 'vendor/autoload.php';
use PhpOffice\PhpWord\IOFactory;

$file = 'PetBasedoc/Вакцина №1 от 12.05.25г Собак           2 шт/0014__№0014.docx';
if (!file_exists($file)) {
    echo 'Файл не найден: ' . $file . PHP_EOL;
    exit(1);
}

$phpWord = IOFactory::load($file);
echo 'Структура документа карточки №0014:' . PHP_EOL;
echo '====================================' . PHP_EOL;

foreach ($phpWord->getSections() as $sectionIndex => $section) {
    echo PHP_EOL . 'Секция ' . ($sectionIndex + 1) . ':' . PHP_EOL;
    
    foreach ($section->getElements() as $elementIndex => $element) {
        $elementType = get_class($element);
        $elementType = substr($elementType, strrpos($elementType, '\\') + 1);
        
        echo '  [' . $elementIndex . '] ' . $elementType;
        
        if ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
            echo ' - ';
            foreach ($element->getElements() as $textElement) {
                if (method_exists($textElement, 'getText')) {
                    $text = $textElement->getText();
                    echo substr($text, 0, 100);
                }
            }
        } elseif ($element instanceof \PhpOffice\PhpWord\Element\Table) {
            echo ' (' . count($element->getRows()) . ' строк)';
            
            // Показываем содержимое таблицы
            echo PHP_EOL;
            foreach ($element->getRows() as $rowIndex => $row) {
                echo '    Строка ' . ($rowIndex + 1) . ': ';
                foreach ($row->getCells() as $cellIndex => $cell) {
                    echo '[';
                    foreach ($cell->getElements() as $cellElement) {
                        if ($cellElement instanceof \PhpOffice\PhpWord\Element\TextRun) {
                            foreach ($cellElement->getElements() as $textElement) {
                                if (method_exists($textElement, 'getText')) {
                                    echo $textElement->getText();
                                }
                            }
                        } elseif (method_exists($cellElement, 'getText')) {
                            echo $cellElement->getText();
                        }
                    }
                    echo '] ';
                }
                echo PHP_EOL;
            }
        }
        
        echo PHP_EOL;
    }
}
