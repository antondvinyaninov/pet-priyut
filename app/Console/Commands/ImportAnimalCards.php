<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextRun;
use App\Services\AnimalCardImportService;

class ImportAnimalCards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'animals:import-cards {--limit=5 : Количество файлов для обработки} {--test : Тестовый режим без сохранения} {--file= : Конкретный файл для импорта}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Импорт карточек животных из папки PetBasedoc';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $basePath = base_path('PetBasedoc');
        
        if (!is_dir($basePath)) {
            $this->error("Папка PetBasedoc не найдена!");
            return 1;
        }

        $specificFile = $this->option('file');
        $limit = (int) $this->option('limit');
        $isTest = $this->option('test');
        
        if ($specificFile) {
            // Импорт конкретного файла
            if (!file_exists($specificFile)) {
                $this->error("Файл не найден: {$specificFile}");
                return 1;
            }
            $files = [$specificFile];
            $this->info("Импорт конкретного файла: " . basename($specificFile));
        } else {
            // Сканирование всей папки
            $this->info("Начинаем сканирование папки PetBasedoc...");
            
            $files = $this->findDocxFiles($basePath);
            
            $this->info("Найдено файлов: " . count($files));
            
            if ($limit > 0) {
                $files = array_slice($files, 0, $limit);
                $this->info("Обрабатываем первые {$limit} файлов...");
            }
        }
        
        $processed = 0;
        $errors = 0;
        
        foreach ($files as $file) {
            try {
                $this->info("\n" . str_repeat('=', 60));
                $this->info("Обработка: " . basename($file));
                
                $data = $this->parseDocxFile($file);
                
                if ($data) {
                    $this->displayAnimalData($data);
                    
                    if (!$isTest) {
                        try {
                            $importService = new AnimalCardImportService();
                            $animal = $importService->importAnimalCard($data);
                            $cardNumber = $data['card_number'] ?? 'авто';
                            $this->info("✓ Сохранено: Animal ID = {$animal->id} (карточка №{$cardNumber})");
                            $processed++;
                        } catch (\Exception $e) {
                            $this->error("✗ Ошибка сохранения: " . $e->getMessage());
                            $errors++;
                        }
                    } else {
                        $cardNumber = $data['card_number'] ?? 'авто';
                        $this->comment("Тестовый режим - данные не сохранены (будет ID = {$cardNumber})");
                        $processed++;
                    }
                } else {
                    $this->warn("Не удалось извлечь данные из файла");
                    $errors++;
                }
                
            } catch (\Exception $e) {
                $this->error("Ошибка при обработке файла: " . $e->getMessage());
                $errors++;
            }
        }
        
        $this->info("\n" . str_repeat('=', 60));
        $this->info("Обработано успешно: {$processed}");
        $this->error("Ошибок: {$errors}");
        
        return 0;
    }
    
    /**
     * Найти все .docx файлы в папке
     */
    private function findDocxFiles(string $path): array
    {
        $files = [];
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'docx') {
                $files[] = $file->getPathname();
            }
        }
        
        return $files;
    }
    
    /**
     * Парсинг .docx файла
     */
    private function parseDocxFile(string $filePath): ?array
    {
        $text = null;
        $fileName = basename($filePath, '.docx');
        $folderName = basename(dirname($filePath));
        
        // Сначала пробуем основной метод PHPWord
        try {
            // Подавляем все ошибки и предупреждения
            set_error_handler(function() {});
            
            $phpWord = IOFactory::load($filePath);
            $text = $this->extractText($phpWord);
            
            restore_error_handler();
            
        } catch (\Exception $e) {
            restore_error_handler();
            // Если не получилось, пробуем альтернативный метод
        }
        
        // Если основной метод не сработал или извлек мало текста, используем альтернативный
        if (empty($text) || strlen($text) < 200) {
            try {
                $altText = $this->extractTextAlternative($filePath);
                // Используем альтернативный текст, если он длиннее
                if (strlen($altText) > strlen($text)) {
                    $text = $altText;
                }
            } catch (\Exception $e2) {
                // Игнорируем ошибку, используем то что есть
            }
        }
        
        if (empty($text)) {
            $this->warn("Не удалось извлечь текст из файла: " . basename($filePath));
            return null;
        }
        
        // Извлекаем изображения
        $images = $this->extractImages($filePath);
        
        // Парсим данные из текста документа
        $data = $this->parseAnimalData($text, $fileName, $folderName);
        $data['images'] = $images;
        $data['file_path'] = $filePath;
        
        return $data;
    }
    
    /**
     * Конвертация EMF/WMF в PNG с помощью LibreOffice
     */
    private function convertEmfToPng(string $imageData, string $extension): ?string
    {
        // Создаем временные файлы
        $tempDir = sys_get_temp_dir();
        $uniqueId = uniqid();
        $inputFile = $tempDir . '/emf_' . $uniqueId . '.' . $extension;
        $outputDir = $tempDir . '/emf_convert_' . $uniqueId;
        
        try {
            // Создаем директорию для вывода
            @mkdir($outputDir, 0755, true);
            
            // Сохраняем исходный файл
            file_put_contents($inputFile, $imageData);
            
            // Конвертируем с помощью LibreOffice (headless mode)
            $command = sprintf(
                'libreoffice --headless --convert-to png --outdir %s %s 2>&1',
                escapeshellarg($outputDir),
                escapeshellarg($inputFile)
            );
            
            exec($command, $output, $returnCode);
            
            // Ищем созданный PNG файл
            $outputFile = $outputDir . '/emf_' . $uniqueId . '.png';
            
            if (file_exists($outputFile)) {
                $pngData = file_get_contents($outputFile);
                
                // Оптимизируем размер с помощью ImageMagick (если доступен)
                $optimizedFile = $tempDir . '/optimized_' . $uniqueId . '.png';
                $optimizeCommand = sprintf(
                    'convert %s -resize 800x800\> -quality 85 %s 2>&1',
                    escapeshellarg($outputFile),
                    escapeshellarg($optimizedFile)
                );
                
                exec($optimizeCommand, $optimizeOutput, $optimizeReturn);
                
                if ($optimizeReturn === 0 && file_exists($optimizedFile)) {
                    $pngData = file_get_contents($optimizedFile);
                    @unlink($optimizedFile);
                }
                
                // Удаляем временные файлы
                @unlink($inputFile);
                @unlink($outputFile);
                @rmdir($outputDir);
                
                return $pngData;
            }
            
            // Удаляем временные файлы в случае ошибки
            @unlink($inputFile);
            @array_map('unlink', glob($outputDir . '/*'));
            @rmdir($outputDir);
            
            return null;
        } catch (\Exception $e) {
            // Удаляем временные файлы
            @unlink($inputFile);
            @array_map('unlink', glob($outputDir . '/*'));
            @rmdir($outputDir);
            
            throw $e;
        }
    }
    
    /**
     * Извлечение изображений из .docx файла
     */
    private function extractImages(string $filePath): array
    {
        $images = [];
        
        try {
            $zip = new \ZipArchive();
            if ($zip->open($filePath) === true) {
                // Ищем все изображения в папке word/media/
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $filename = $zip->getNameIndex($i);
                    
                    // Поддерживаем различные форматы изображений
                    if (preg_match('/word\/media\/(image\d+\.(jpeg|jpg|png|gif|bmp|tiff|tif|webp|emf|wmf))/i', $filename, $matches)) {
                        $imageData = $zip->getFromName($filename);
                        if ($imageData) {
                            $extension = strtolower($matches[2]);
                            
                            // EMF и WMF - векторные форматы Windows, конвертируем в PNG
                            if (in_array($extension, ['emf', 'wmf'])) {
                                $this->line("🔄 Конвертация {$extension} → PNG: {$matches[1]}");
                                
                                try {
                                    $convertedData = $this->convertEmfToPng($imageData, $extension);
                                    if ($convertedData) {
                                        $images[] = [
                                            'filename' => str_replace('.' . $extension, '.png', $matches[1]),
                                            'data' => $convertedData,
                                            'size' => strlen($convertedData),
                                            'extension' => 'png'
                                        ];
                                        $this->info("✓ Конвертировано успешно");
                                    } else {
                                        $this->warn("✗ Не удалось конвертировать");
                                    }
                                } catch (\Exception $e) {
                                    $this->warn("✗ Ошибка конвертации: " . $e->getMessage());
                                }
                                continue;
                            }
                            
                            $images[] = [
                                'filename' => $matches[1],
                                'data' => $imageData,
                                'size' => strlen($imageData),
                                'extension' => $extension
                            ];
                        }
                    }
                }
                $zip->close();
            }
        } catch (\Exception $e) {
            // Игнорируем ошибки с изображениями
        }
        
        return $images;
    }
    
    /**
     * Альтернативный способ извлечения текста из .docx
     */
    private function extractTextAlternative(string $filePath): ?string
    {
        $zip = new \ZipArchive();
        if ($zip->open($filePath) === true) {
            $xml = $zip->getFromName('word/document.xml');
            $zip->close();
            
            if ($xml) {
                // Парсим XML для правильного извлечения текста
                $dom = new \DOMDocument();
                @$dom->loadXML($xml);
                
                $text = '';
                
                // Извлекаем текст из параграфов
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
    
    /**
     * Извлечение текста из документа
     */
    private function extractText($phpWord): string
    {
        $text = '';
        
        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getText')) {
                    $text .= $element->getText() . "\n";
                } elseif ($element instanceof TextRun) {
                    foreach ($element->getElements() as $textElement) {
                        if (method_exists($textElement, 'getText')) {
                            $text .= $textElement->getText();
                        }
                    }
                    $text .= "\n";
                }
            }
        }
        
        return $text;
    }
    
    /**
     * Парсинг данных о животном
     */
    private function parseAnimalData(string $text, string $fileName, string $folderName): array
    {
        $data = [
            'file_name' => $fileName,
            'folder_name' => $folderName,
            'raw_text' => $text,
            'text_length' => strlen($text),
        ];
        
        // Извлекаем данные из имени файла
        // Формат может быть: "08_№ 3524 вольер №81" или "300_№ 3159 вольер №47"
        
        // Номер файла (первое число)
        if (preg_match('/^(\d+)_/', $fileName, $matches)) {
            $data['file_number'] = $matches[1];
        }
        
        // Номер бирки (после №)
        if (preg_match('/_?№\s*(\d+)/', $fileName, $matches)) {
            $data['tag_number_from_file'] = $matches[1];
        }
        
        // Извлекаем информацию о вакцинации из имени папки
        if (preg_match('/Вакцина\s*№(\d+)\s*от\s*([\d.]+)/ui', $folderName, $matches)) {
            $data['vaccine_number'] = $matches[1];
            $data['vaccine_date'] = $this->parseDate($matches[2]);
        }
        
        // Нормализуем текст: убираем лишние пробелы и переносы строк внутри полей
        // Но сохраняем структуру для парсинга по строкам
        $normalizedText = preg_replace('/\s+/', ' ', $text);
        
        // Парсим текст документа для извлечения данных
        $lines = explode("\n", $text);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Пропускаем пустые строки
            if (empty($line)) continue;
            
            // Номер карточки в начале документа
            // Формат: "№ 0034 14.05.2025 г." или "№ 8      26 ноября 2024г."
            if (preg_match('/№\s*(\d+)\s+([\d.]+)\s*г/ui', $line, $matches)) {
                if (!isset($data['card_number'])) {
                    $data['card_number'] = $matches[1];
                }
                $data['card_date'] = $this->parseDate($matches[2]);
            }
            
            // Альтернативный формат с текстовой датой
            if (preg_match('/№\s*(\d+)\s+([\d\s]+(?:января|февраля|марта|апреля|мая|июня|июля|августа|сентября|октября|ноября|декабря)\s+\d{4})/ui', $line, $matches)) {
                if (!isset($data['card_number'])) {
                    $data['card_number'] = $matches[1];
                }
                $dateText = trim($matches[2]);
                $parsedDate = $this->parseDate($dateText);
                if ($parsedDate) {
                    $data['card_date'] = $parsedDate;
                }
            }
            
            // Категория животного
            if (preg_match('/категория\s+животного[:\s]+(собака|кошка|кот)/ui', $line, $matches)) {
                $data['type'] = mb_strtolower($matches[1]) === 'собака' ? 'dog' : 'cat';
            }
            
            // Дата поступления
            if (preg_match('/дата\s+поступления[:\s]+([\d.]+)/ui', $line, $matches)) {
                $data['arrived_at'] = $this->parseDate($matches[1]);
            }
            
            // Дата поступления в текстовом формате
            if (!isset($data['arrived_at']) && preg_match('/дата\s+поступления[:\s]+([\d\s]+(?:января|февраля|марта|апреля|мая|июня|июля|августа|сентября|октября|ноября|декабря)\s+\d{4})/ui', $line, $matches)) {
                $data['arrived_at'] = $this->parseDate($matches[1]);
            }
            
            // Пол
            if (preg_match('/пол[:\s]+(кобель|сука|самец|самка|кот|кошка)/ui', $line, $matches)) {
                $gender = mb_strtolower($matches[1]);
                if (in_array($gender, ['кобель', 'самец', 'кот'])) {
                    $data['gender'] = 'male';
                } else {
                    $data['gender'] = 'female';
                }
            }
            
            // Порода
            if (preg_match('/порода[:\s]+(.+)/ui', $line, $matches)) {
                $data['breed'] = trim($matches[1]);
            }
            
            // Окрас
            if (preg_match('/окрас[:\s]+(.+)/ui', $line, $matches)) {
                $data['color'] = trim($matches[1]);
            }
            
            // Шерсть
            if (preg_match('/шерсть[:\s]+(.+)/ui', $line, $matches)) {
                $data['coat'] = trim($matches[1]);
            }
            
            // Уши
            if (preg_match('/уши[:\s]+(.+)/ui', $line, $matches)) {
                $data['ears'] = trim($matches[1]);
            }
            
            // Хвост
            if (preg_match('/хвост[:\s]+(.+)/ui', $line, $matches)) {
                $data['tail'] = trim($matches[1]);
            }
            
            // Размер и вес (могут быть в одной строке: "средний 20 кг")
            if (preg_match('/размер[:\s]+(.+)/ui', $line, $matches)) {
                $sizeText = trim($matches[1]);
                $data['size'] = $sizeText;
                
                // Извлекаем вес из размера, если он там есть
                if (preg_match('/([\d.,]+)\s*кг/ui', $sizeText, $weightMatches)) {
                    $data['weight'] = str_replace(',', '.', $weightMatches[1]);
                    // Убираем вес из размера
                    $data['size'] = trim(preg_replace('/([\d.,]+)\s*кг/ui', '', $sizeText));
                }
            }
            
            // Возраст
            if (preg_match('/возраст[:\s\(]*примерный[:\s\)]*[:\s]+(.+)/ui', $line, $matches)) {
                $data['age'] = trim($matches[1]);
            }
            
            // Вес (отдельное поле)
            if (!isset($data['weight']) && preg_match('/вес[:\s]+([\d.,]+)/ui', $line, $matches)) {
                $data['weight'] = str_replace(',', '.', $matches[1]);
            }
            
            // Чип
            if (preg_match('/чип[:\s№]+(.+)/ui', $line, $matches)) {
                $chip = trim($matches[1]);
                if (!in_array(mb_strtolower($chip), ['нет', '-', ''])) {
                    $data['chip_number'] = $chip;
                }
            }
            
            // Бирка
            if (preg_match('/бирка[:\s№]+(.+)/ui', $line, $matches)) {
                $tag = trim($matches[1]);
                if (!in_array(mb_strtolower($tag), ['нет', '-', ''])) {
                    $data['tag_number'] = $tag;
                }
            }
            
            // Кличка
            if (preg_match('/кличка[:\s]+(.+)/ui', $line, $matches)) {
                $name = trim($matches[1]);
                if (!in_array(mb_strtolower($name), ['нет', '-', '', 'без клички'])) {
                    $data['name'] = $name;
                }
            }
            
            // Особые приметы
            if (preg_match('/особые\s+приметы[:\s]+(.+)/ui', $line, $matches)) {
                $special = trim($matches[1]);
                if (!in_array(mb_strtolower($special), ['нет', '-', ''])) {
                    $data['special_marks'] = $special;
                }
            }
            
            // Акт приема-передачи
            if (preg_match('/акт\s+приёма?[-\s]*передачи(?:\s+животного)?[:\s]+(?:акт\s*)?№?\s*(\d+)\s+от\s+([\d.]+)/ui', $line, $matches)) {
                $data['capture_act_number'] = $matches[1];
                $data['capture_act_date'] = $this->parseDate($matches[2]);
                $data['capture_act_type'] = 'transfer'; // Акт приема-передачи
            }
            
            // Акт отлова
            if (preg_match('/акт\s+отлова[:\s]+(?:акт\s*)?№?\s*(\d+)\s+от\s+([\d.]+)/ui', $line, $matches)) {
                $data['capture_act_number'] = $matches[1];
                $data['capture_act_date'] = $this->parseDate($matches[2]);
                $data['capture_act_type'] = 'capture'; // Акт отлова
            }
            
            // Адрес отлова
            if (preg_match('/адрес\s+и\s+описание\s+места\s+отлова[:\s]+(.+)/ui', $line, $matches)) {
                $data['capture_location'] = trim($matches[1]);
            }
            
            // Клинический осмотр
            if (preg_match('/дата\s+клинического\s+осмотра[,\s]+заключение[:\s]+([\d.]+)\s*г?\.?\s+(.+)/ui', $line, $matches)) {
                $data['clinical_exam_date'] = $this->parseDate($matches[1]);
                $data['clinical_exam_conclusion'] = trim($matches[2]);
            }
            
            // Информация об агрессии
            if (preg_match('/информация\s+о\s+наличии.*агрессивного\s+поведения[:\s]+(.+)/ui', $line, $matches)) {
                $data['aggression_notes'] = trim($matches[1]);
            }
            
            // Коррекция поведения
            if (preg_match('/мероприятиях\s+по\s+корректировке\s+поведения[:\s]+(.+)/ui', $line, $matches)) {
                $data['behavior_correction'] = trim($matches[1]);
            }
            
            // Дегельминтизация
            if (preg_match('/дата\s+дегельминтизации[:\s]+([\d.]+)/ui', $line, $matches)) {
                $data['deworming_date'] = $this->parseDate($matches[1]);
            }
            
            // Стерилизация
            if (preg_match('/дата\s+стерилизации[:\s]+([\d.]+)/ui', $line, $matches)) {
                $data['sterilization_date'] = $this->parseDate($matches[1]);
            }
            
            // Ветеринар, проводивший стерилизацию
            if (preg_match('/специалиста.*произведшего\s+операцию\s+стерилизации[:\s]+(.+)/ui', $line, $matches)) {
                $vet = trim($matches[1]);
                if (!empty($vet)) {
                    $data['sterilization_vet'] = $vet;
                }
            }
            
            // Если ветеринар на следующей строке (после пустого поля)
            if (!isset($data['sterilization_vet']) && preg_match('/Ф\.И\.О\.\s+специалиста.*стерилизации[:\s]*$/ui', $line)) {
                // Запоминаем, что следующая непустая строка - это ветеринар
                $data['_next_is_vet'] = true;
            }
            
            // Если предыдущая строка была заголовком ветеринара
            if (isset($data['_next_is_vet']) && !empty($line) && !preg_match('/дата\s+маркирования/ui', $line)) {
                $data['sterilization_vet'] = trim($line);
                unset($data['_next_is_vet']);
            }
            
            // Дата маркирования
            if (preg_match('/дата\s+маркирования[:\s]+([\d.]+)/ui', $line, $matches)) {
                $data['marking_date'] = $this->parseDate($matches[1]);
            }
            
            // Вакцинация из документа (приоритетнее чем из папки)
            if (preg_match('/вакцинация[,\s]+вид\s+прививки[,\s]+акт[:\s\(]+дата[,\s]+№[:\s\)]+№?\s*(\d+)\s+от\s+([\d.]+)\s*г?\.?\s+(.+?)(?:серия|дата\s+дегельминтизации|$)/ui', $line, $matches)) {
                $data['vaccination_act_number'] = $matches[1];
                $data['vaccination_act_date'] = $this->parseDate($matches[2]);
                $data['vaccine_name'] = trim($matches[3]);
            }
            
            // Номер бирки из текста
            if (preg_match('/№\s+бирки\s*\(клейма\)[:\s]+№?\s*(\d+)/ui', $line, $matches)) {
                $data['tag_number'] = $matches[1];
            }
            
            // Если бирка не найдена в тексте, используем из имени файла
            if (!isset($data['tag_number']) && isset($data['tag_number_from_file'])) {
                $data['tag_number'] = $data['tag_number_from_file'];
            }
            
            // Номер чипа из текста
            if (preg_match('/№\s+чипа[:\s]+([\d\s]+)/ui', $line, $matches)) {
                $chip = preg_replace('/\s+/', '', trim($matches[1]));
                if (!empty($chip) && $chip !== '-') {
                    $data['chip_number'] = $chip;
                }
            }
            
            // ВСД (Ветеринарный сопроводительный документ)
            if (preg_match('/ВСД\s*\(дата[,\s]+№\)[:\s]+(?:от\s+)?([\d.]+)\s*г?\.?\s*№?\s*(\d+)/ui', $line, $matches)) {
                $data['vsd_date'] = $this->parseDate($matches[1]);
                $data['vsd_number'] = $matches[2];
            }
            
            // Альтернативный формат ВСД
            if (preg_match('/ВСД\s*\(дата[,\s]+№\)[:\s]+№?\s*(\d+)\s+от\s+([\d.]+)/ui', $line, $matches)) {
                $data['vsd_number'] = $matches[1];
                $data['vsd_date'] = $this->parseDate($matches[2]);
            }
            
            // Наличие/отсутствие немотивированной агрессивности, акт
            if (preg_match('/наличие\/отсутствие\s+немотивированной\s+агрессивности[,\s]+акт[:\s]+(.+)/ui', $line, $matches)) {
                $aggression_act = trim($matches[1]);
                if (!in_array(mb_strtolower($aggression_act), ['нет', '-', '–', ''])) {
                    $data['aggression_act'] = $aggression_act;
                }
            }
            
            // Выбытие
            if (preg_match('/выбытие\s*\(причина[,\s]+дата\)[:\s]+(.+)/ui', $line, $matches)) {
                $outcome = trim($matches[1]);
                if (!in_array($outcome, ['–', '-', ''])) {
                    $data['outcome'] = $outcome;
                }
            }
            
            // Ветеринарный сопроводительный документ
            if (preg_match('/ветеринарный\s+сопроводительный\s+документ\s*\(дата[,\s]+№\)[:\s]+(.+)/ui', $line, $matches)) {
                $vsd_doc = trim($matches[1]);
                if (!in_array($vsd_doc, ['–', '-', ''])) {
                    $data['veterinary_document'] = $vsd_doc;
                }
            }
            
            // Адрес возвращения (размещения)
            if (preg_match('/адрес\s+и\s+описание\s+места\s+возвращения[:\s\(]*размещения[:\s\)]*[:\s]+(.+)/ui', $line, $matches)) {
                $release = trim($matches[1]);
                if (!in_array($release, ['–', '-', ''])) {
                    $data['release_address'] = $release;
                }
            }
            
            // Альтернативный формат адреса возвращения
            if (!isset($data['release_address']) && preg_match('/адрес\s+и\s+описание\s+места\s+возвращения[:\s]+(.+)/ui', $line, $matches)) {
                $release = trim($matches[1]);
                if (!in_array($release, ['–', '-', ''])) {
                    $data['release_address'] = $release;
                }
            }
        }
        
        // Дополнительный парсинг из нормализованного текста для полей, которые могут быть разбиты на строки
        // Это поможет найти данные, которые были пропущены из-за переносов строк
        
        // Агрессивное поведение (может быть длинным и разбитым на строки)
        if (!isset($data['aggression_notes']) && preg_match('/информация\s+о\s+наличии\s*\(отсутствии\)\s+у\s+животного\s+агрессивного\s+поведения[:\s]+(.+?)(?:информация\s+о\s+мероприятиях|вакцинация|$)/uis', $normalizedText, $matches)) {
            $aggr = trim($matches[1]);
            if (!in_array(mb_strtolower($aggr), ['нет', '-', '–', ''])) {
                $data['aggression_notes'] = $aggr;
            }
        }
        
        // Коррекция поведения
        if (!isset($data['behavior_correction']) && preg_match('/информация\s+о\s+мероприятиях\s+по\s+корректировке\s+поведения\s+животного[:\s]+(.+?)(?:вакцинация|дата\s+дегельминтизации|$)/uis', $normalizedText, $matches)) {
            $corr = trim($matches[1]);
            if (!in_array(mb_strtolower($corr), ['нет', '-', '–', ''])) {
                $data['behavior_correction'] = $corr;
            }
        }
        
        // Ветеринар из нормализованного текста
        if (!isset($data['sterilization_vet']) || preg_match('/Ф\.И\.О\.\s+специалиста/ui', $data['sterilization_vet'])) {
            if (preg_match('/Ф\.И\.О\.\s+специалиста.*стерилизации[:\s]+(.+?)(?:дата\s+маркирования|№\s+бирки|$)/uis', $normalizedText, $matches)) {
                $vet = trim($matches[1]);
                if (!empty($vet) && !preg_match('/Ф\.И\.О\./ui', $vet)) {
                    $data['sterilization_vet'] = $vet;
                }
            }
        }
        
        // Адрес возвращения из нормализованного текста
        if (!isset($data['release_address']) && preg_match('/адрес\s+и\s+описание\s+места\s+возвращения\s*\(размещения\)[:\s]+(.+?)(?:данные\s+на\s+новых|для\s+юридических|$)/uis', $normalizedText, $matches)) {
            $release = trim($matches[1]);
            if (!in_array($release, ['–', '-', ''])) {
                $data['release_address'] = $release;
            }
        }
        
        return $data;
    }
    
    /**
     * Парсинг даты из различных форматов
     */
    private function parseDate(string $dateStr): ?string
    {
        // Убираем "г." и лишние пробелы
        $dateStr = trim(str_replace(['г.', 'г'], '', $dateStr));
        
        // Если это текстовая дата (например "26 ноября 2024")
        if (preg_match('/([\d]+)\s+(января|февраля|марта|апреля|мая|июня|июля|августа|сентября|октября|ноября|декабря)\s+(\d{4})/ui', $dateStr, $matches)) {
            $months = [
                'января' => '01', 'февраля' => '02', 'марта' => '03', 'апреля' => '04',
                'мая' => '05', 'июня' => '06', 'июля' => '07', 'августа' => '08',
                'сентября' => '09', 'октября' => '10', 'ноября' => '11', 'декабря' => '12'
            ];
            
            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $month = $months[mb_strtolower($matches[2])] ?? '01';
            $year = $matches[3];
            
            return "$year-$month-$day";
        }
        
        // Пробуем различные форматы
        $formats = ['d.m.Y', 'd.m.y', 'Y-m-d'];
        
        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $dateStr);
            if ($date !== false) {
                return $date->format('Y-m-d');
            }
        }
        
        return null;
    }
    
    /**
     * Отображение данных о животном
     */
    private function displayAnimalData(array $data): void
    {
        $rows = [
            ['Файл', $data['file_name'] ?? '-'],
            ['Номер файла', $data['file_number'] ?? '-'],
            ['Номер карточки', $data['card_number'] ?? '-'],
            ['Дата карточки', $data['card_date'] ?? '-'],
            ['Изображений', count($data['images'] ?? [])],
            ['---', '---'],
            ['Тип', $data['type'] ?? '-'],
            ['Кличка', $data['name'] ?? '-'],
            ['Пол', $data['gender'] ?? '-'],
            ['Порода', $data['breed'] ?? '-'],
            ['Окрас', $data['color'] ?? '-'],
            ['Шерсть', $data['coat'] ?? '-'],
            ['Уши', $data['ears'] ?? '-'],
            ['Хвост', $data['tail'] ?? '-'],
            ['Размер', $data['size'] ?? '-'],
            ['Возраст', $data['age'] ?? '-'],
            ['Вес', $data['weight'] ?? '-'],
            ['Дата поступления', $data['arrived_at'] ?? '-'],
            ['Чип', $data['chip_number'] ?? '-'],
            ['Бирка', $data['tag_number'] ?? '-'],
            ['Особые приметы', $data['special_marks'] ?? '-'],
        ];
        
        // Добавляем дополнительные поля если они есть
        if (!empty($data['capture_act_number'])) {
            $rows[] = ['---', '---'];
            $rows[] = ['Акт отлова №', $data['capture_act_number']];
            $rows[] = ['Дата акта', $data['capture_act_date'] ?? '-'];
        }
        
        if (!empty($data['capture_location'])) {
            $rows[] = ['Место отлова', mb_substr($data['capture_location'], 0, 50) . (strlen($data['capture_location']) > 50 ? '...' : '')];
        }
        
        if (!empty($data['clinical_exam_date'])) {
            $rows[] = ['---', '---'];
            $rows[] = ['Клинический осмотр', $data['clinical_exam_date']];
            if (!empty($data['clinical_exam_conclusion'])) {
                $rows[] = ['Заключение', mb_substr($data['clinical_exam_conclusion'], 0, 50) . (strlen($data['clinical_exam_conclusion']) > 50 ? '...' : '')];
            }
        }
        
        if (!empty($data['aggression_notes'])) {
            $rows[] = ['---', '---'];
            $rows[] = ['Агрессивность', mb_substr($data['aggression_notes'], 0, 50) . (strlen($data['aggression_notes']) > 50 ? '...' : '')];
        }
        
        if (!empty($data['behavior_correction'])) {
            $rows[] = ['Коррекция поведения', mb_substr($data['behavior_correction'], 0, 50) . (strlen($data['behavior_correction']) > 50 ? '...' : '')];
        }
        
        if (!empty($data['sterilization_date'])) {
            $rows[] = ['---', '---'];
            $rows[] = ['Дата стерилизации', $data['sterilization_date']];
            $rows[] = ['Ветеринар', $data['sterilization_vet'] ?? '-'];
        }
        
        // Вакцинация из документа (приоритетнее)
        if (!empty($data['vaccination_act_number'])) {
            $rows[] = ['---', '---'];
            $rows[] = ['Вакцинация акт №', $data['vaccination_act_number']];
            $rows[] = ['Дата вакцинации', $data['vaccination_act_date'] ?? '-'];
            if (!empty($data['vaccine_name'])) {
                $rows[] = ['Вакцина', $data['vaccine_name']];
            }
        } elseif (!empty($data['vaccine_number'])) {
            // Вакцинация из папки (если нет в документе)
            $rows[] = ['---', '---'];
            $rows[] = ['Вакцина № (из папки)', $data['vaccine_number']];
            $rows[] = ['Дата вакцинации', $data['vaccine_date'] ?? '-'];
        }
        
        if (!empty($data['deworming_date'])) {
            $rows[] = ['Дегельминтизация', $data['deworming_date']];
        }
        
        if (!empty($data['marking_date'])) {
            $rows[] = ['Дата маркирования', $data['marking_date']];
        }
        
        if (!empty($data['vsd_number'])) {
            $rows[] = ['---', '---'];
            $rows[] = ['ВСД №', $data['vsd_number']];
            $rows[] = ['Дата ВСД', $data['vsd_date'] ?? '-'];
        }
        
        if (!empty($data['aggression_act'])) {
            $rows[] = ['---', '---'];
            $rows[] = ['Акт агрессивности', mb_substr($data['aggression_act'], 0, 50) . (strlen($data['aggression_act']) > 50 ? '...' : '')];
        }
        
        if (!empty($data['veterinary_document'])) {
            $rows[] = ['---', '---'];
            $rows[] = ['Вет. документ', $data['veterinary_document']];
        }
        
        if (!empty($data['outcome'])) {
            $rows[] = ['---', '---'];
            $rows[] = ['Выбытие', $data['outcome']];
        }
        
        if (!empty($data['release_address'])) {
            $rows[] = ['Адрес возврата', mb_substr($data['release_address'], 0, 50) . (strlen($data['release_address']) > 50 ? '...' : '')];
        }
        
        $this->table(['Поле', 'Значение'], $rows);
    }
}
