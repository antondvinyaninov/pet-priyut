<?php

namespace App\Console\Commands;

use App\Models\OsvvRequest;
use Carbon\Carbon;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportOsvvRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'osvv:import {file=Журнал учета заявок.xlsx : Путь к Excel-файлу для импорта} {--skip-rows=0 : Количество строк для пропуска (не считая заголовки)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Импорт заявок ОСВВ из Excel-файла';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');
        $skipRows = (int)$this->option('skip-rows');
        
        if (!file_exists($filePath)) {
            $this->error("Файл не найден: {$filePath}");
            return 1;
        }
        
        $this->info("Начинаем импорт из файла: {$filePath}");
        if ($skipRows > 0) {
            $this->info("Пропускаем первые {$skipRows} строк данных");
        }
        
        try {
            // Загружаем Excel-файл
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();
            
            $this->info("Найдено {$highestRow} строк в файле");
            
            // Начинаем с 2-й строки (пропускаем заголовки) + дополнительные пропускаемые строки
            $startRow = 2 + $skipRows;
            $successCount = 0;
            $errorCount = 0;
            
            // Создаем прогресс-бар
            $bar = $this->output->createProgressBar($highestRow - $startRow + 1);
            $bar->start();
            
            for ($row = $startRow; $row <= $highestRow; $row++) {
                // Получаем данные из ячеек
                $requestNumber = $sheet->getCell('A' . $row)->getValue();
                
                // Дата поступления заявки (в Excel хранится как число, нужно преобразовать в дату)
                $excelDate = $sheet->getCell('B' . $row)->getValue();
                $createdAt = null;
                if (!empty($excelDate)) {
                    if (is_numeric($excelDate)) {
                        $createdAt = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($excelDate));
                    } else {
                        try {
                            $createdAt = Carbon::parse($excelDate);
                        } catch (\Exception $e) {
                            $this->warn("Не удалось распознать дату '{$excelDate}' в строке {$row}");
                        }
                    }
                }
                
                // ФИО заявителя и контактный телефон
                $contactName = trim($sheet->getCell('C' . $row)->getValue());
                $contactPhone = trim($sheet->getCell('D' . $row)->getValue());
                
                // Установка значений по умолчанию, если они пустые
                if (empty($contactName)) {
                    $contactName = 'Не указано (импорт)';
                }
                
                if (empty($contactPhone)) {
                    $contactPhone = 'Не указано';
                }
                
                // В колонке E содержится описание животных, используем как описание
                $animalDescription = $sheet->getCell('E' . $row)->getValue();
                if (empty($animalDescription)) {
                    $animalDescription = 'Описание отсутствует';
                }
                
                // Адрес в колонке F
                $locationAddress = $sheet->getCell('F' . $row)->getValue();
                if (empty($locationAddress)) {
                    $locationAddress = 'Адрес не указан';
                }
                
                // Количество животных (в колонке G, но может быть пустым, тогда берем из описания)
                $animalsCount = $sheet->getCell('G' . $row)->getValue();
                if (empty($animalsCount) || !is_numeric($animalsCount)) {
                    // Попытка извлечь количество из описания
                    preg_match('/(\d+)(?:\s+особ|собак)/i', $animalDescription, $matches);
                    $animalsCount = !empty($matches[1]) ? (int)$matches[1] : 1;
                }
                
                // Район в колонке H
                $district = $sheet->getCell('H' . $row)->getValue();
                if (empty($district)) {
                    $district = 'Не указан';
                }
                
                // Укус в колонке I
                $hasBiteValue = $sheet->getCell('I' . $row)->getValue();
                $hasBite = !empty($hasBiteValue) && (strtolower($hasBiteValue) === 'да' || $hasBiteValue === true);
                
                // Беременность в колонке J
                $isPregnantValue = $sheet->getCell('J' . $row)->getValue();
                $isPregnant = !empty($isPregnantValue) && (strtolower($isPregnantValue) === 'да' || $isPregnantValue === true);
                
                // Крайний срок в колонке K (может быть формулой)
                $deadlineDateValue = $sheet->getCell('K' . $row)->getCalculatedValue();
                $deadlineDate = null;
                if (!empty($deadlineDateValue) && $deadlineDateValue !== '=B' . $row . ' + 6 + IF(I' . $row . '=TRUE, -5, 0)') {
                    if (is_numeric($deadlineDateValue)) {
                        $deadlineDate = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($deadlineDateValue));
                    } else {
                        try {
                            $deadlineDate = Carbon::parse($deadlineDateValue);
                        } catch (\Exception $e) {
                            // Можно пропустить, так как мы пересчитаем этот срок
                        }
                    }
                }
                
                // Дата выезда в колонке L
                $departureDateValue = $sheet->getCell('L' . $row)->getValue();
                $departureDate = null;
                if (!empty($departureDateValue)) {
                    // Может быть несколько дат, берем первую
                    $dateStr = preg_replace('/\s+.*$/', '', $departureDateValue);
                    try {
                        $departureDate = Carbon::parse($dateStr);
                    } catch (\Exception $e) {
                        $this->warn("Не удалось распознать дату выезда '{$dateStr}' в строке {$row}");
                    }
                }
                
                // Статус в колонке M
                $status = $sheet->getCell('M' . $row)->getValue();
                
                // Результат мероприятий в колонке N
                $captureResult = $sheet->getCell('N' . $row)->getValue();
                
                // Определяем тип животного (из описания)
                $animalType = 'dog'; // По умолчанию собака
                if (stripos($animalDescription, 'кошк') !== false || stripos($animalDescription, 'кот') !== false) {
                    $animalType = 'cat';
                }
                
                // Маппинг статуса
                $statusMapped = $this->mapStatus($status);
                
                // Подготовка данных для сохранения
                $requestData = [
                    'contact_name' => $contactName,
                    'contact_phone' => $contactPhone,
                    'animal_type' => $animalType,
                    'animal_description' => $animalDescription,
                    'location_address' => $locationAddress,
                    'district' => $district,
                    'status' => $statusMapped,
                    'animals_count' => $animalsCount,
                    'has_bite' => $hasBite,
                    'is_pregnant' => $isPregnant,
                    'capture_result' => $captureResult,
                    'notes' => "Импортировано из Excel. Номер заявки: {$requestNumber}"
                ];
                
                // Добавляем даты, если они определены
                if ($createdAt) {
                    $requestData['created_at'] = $createdAt;
                }
                
                if ($departureDate) {
                    $requestData['departure_date'] = $departureDate;
                }
                
                try {
                    // Создаем заявку
                    $request = new OsvvRequest($requestData);
                    if ($deadlineDate) {
                        $request->deadline_date = $deadlineDate;
                    } else {
                        $request->calculateDeadlineDate();
                    }
                    $request->save();
                    
                    $successCount++;
                } catch (\Exception $e) {
                    $this->error("Ошибка в строке {$row}: " . $e->getMessage());
                    $errorCount++;
                }
                
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine();
            
            $this->info("Импорт завершен. Успешно импортировано: {$successCount}, с ошибками: {$errorCount}");
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Ошибка при импорте: " . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * Преобразует статус из Excel в формат системы
     */
    private function mapStatus(string $status = null): string
    {
        if (empty($status)) {
            return 'new';
        }
        
        $status = mb_strtolower(trim($status));
        
        $statusMap = [
            'исполнено' => 'completed',
            'отловлен' => 'completed',
            '1 выезд' => 'in_progress',
            '2 выезд' => 'follow_up_required',
            'новая' => 'new',
            'в работе' => 'in_progress',
            'выполнена' => 'completed',
            'отменена' => 'cancelled',
            'требуется повторный выезд' => 'follow_up_required',
            'архив' => 'archived',
        ];
        
        return $statusMap[$status] ?? 'new';
    }
} 