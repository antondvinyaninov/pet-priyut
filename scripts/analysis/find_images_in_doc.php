<?php
require 'vendor/autoload.php';
use PhpOffice\PhpWord\IOFactory;

$file = 'PetBasedoc/Вакцина №1 от 12.05.25г Собак           2 шт/0014__№0014.docx';
$phpWord = IOFactory::load($file);

echo 'Поиск изображений в документе:' . PHP_EOL;
echo '===============================' . PHP_EOL;

function findImages($elements, $level = 0) {
    $indent = str_repeat('  ', $level);
    
    foreach ($elements as $elementIndex => $element) {
        $elementType = get_class($element);
        $elementType = substr($elementType, strrpos($elementType, '\\') + 1);
        
        if ($element instanceof \PhpOffice\PhpWord\Element\Image) {
            echo $indent . '✓ Изображение найдено!' . PHP_EOL;
            echo $indent . '  Позиция: ' . $elementIndex . PHP_EOL;
            echo $indent . '  Ширина: ' . $element->getWidth() . PHP_EOL;
            echo $indent . '  Высота: ' . $element->getHeight() . PHP_EOL;
            echo $indent . '---' . PHP_EOL;
        } elseif ($element instanceof \PhpOffice\PhpWord\Element\Table) {
            echo $indent . 'Таблица (строк: ' . count($element->getRows()) . ')' . PHP_EOL;
            foreach ($element->getRows() as $rowIndex => $row) {
                foreach ($row->getCells() as $cellIndex => $cell) {
                    findImages($cell->getElements(), $level + 1);
                }
            }
        } elseif ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
            foreach ($element->getElements() as $textElement) {
                if ($textElement instanceof \PhpOffice\PhpWord\Element\Image) {
                    echo $indent . '✓ Изображение в TextRun!' . PHP_EOL;
                    echo $indent . '  Позиция: ' . $elementIndex . PHP_EOL;
                }
            }
        }
    }
}

foreach ($phpWord->getSections() as $sectionIndex => $section) {
    echo PHP_EOL . 'Секция ' . ($sectionIndex + 1) . ':' . PHP_EOL;
    findImages($section->getElements());
}

echo PHP_EOL . 'Проверка завершена.' . PHP_EOL;
