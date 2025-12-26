<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OsvvRequest;
use App\Models\OsvvComment;
use Carbon\Carbon;

class ImportOsvvFromCsv extends Command
{
    protected $signature = 'osvv:import-csv {file=jurnal.csv}';
    protected $description = 'Импорт заявок ОСВВ из CSV файла';

    public function handle()
    {
        $filePath = base_path($this->argument('file'));
        
        if (!file_exists($filePath)) {
            $this->error("Файл не найден: {$filePath}");
            return 1;
        }

        $this->info("Начинаем импорт из {$filePath}...");
        
        $file = fopen($filePath, 'r');
        
        // Пропускаем заголовок
        $header = fgetcsv($file);
        
        $imported = 0;
        $errors = 0;
        
        while (($row = fgetcsv($file)) !== false) {
            try {
                // Пропускаем пустые строки
                if (empty($row[0]) || trim($row[0]) === '') {
                    continue;
                }
                
                $this->importRow($row);
                $imported++;
                
                if ($imported % 10 == 0) {
                    $this->info("Импортировано: {$imported} записей");
                }
                
            } catch (\Exception $e) {
                $errors++;
                $this->error("Ошибка в строке {$row[0]}: " . $e->getMessage());
            }
        }
        
        fclose($file);
        
        $this->info("Импорт завершен!");
        $this->info("Успешно импортировано: {$imported}");
        $this->info("Ошибок: {$errors}");
        
        return 0;
    }
    
    private function importRow($row)
    {
        // Парсим данные из CSV
        $number = trim($row[0]);
        $requestDate = $this->parseDate($row[1]);
        $contactName = trim($row[2]);
        $contactPhone = $this->cleanPhone($row[3]);
        $description = trim($row[4]);
        $location = trim($row[5]);
        $animalsCount = $this->parseAnimalsCount($row[6]);
        $district = trim($row[7]);
        $hasBite = $this->parseBoolean($row[8]);
        $isPregnant = $this->parseBoolean($row[9]);
        $isControl = $this->parseBoolean($row[10]);
        $deadline = $this->parseDate($row[11]);
        $departureDates = trim($row[12]);
        $departureResult = trim($row[13]);
        $departureNotes = trim($row[14]);
        
        // Парсим выезды
        $departures = $this->parseDepartures($departureDates, $departureNotes);
        $departuresCount = count($departures);
        
        // Определяем статус
        $status = $this->determineStatus($departureResult, $departureNotes, $departuresCount);
        
        // Создаем заявку
        $request = OsvvRequest::create([
            'contact_name' => $contactName ?: 'Не указано',
            'contact_phone' => $contactPhone ?: 'Не указан',
            'contact_email' => null,
            'case_description' => $description,
            'animal_type' => 'dog',
            'animal_description' => $this->buildAnimalDescription($description, $isPregnant, $animalsCount),
            'location_address' => $location,
            'animals_count' => $animalsCount,
            'district' => $district,
            'has_bite' => $hasBite,
            'status' => $status,
            'deadline_date' => $deadline,
            'departure_date' => $this->parseFirstDepartureDate($departureDates),
            'departure_notes' => $this->buildDepartureNotes($departureDates, $departureResult, $departureNotes),
            'capture_result' => $departureResult,
            'notes' => $this->buildNotes($isControl, $isPregnant),
            'created_at' => $requestDate ?: now(),
            'updated_at' => $requestDate ?: now(),
        ]);
        
        // Создаем комментарии для каждого выезда
        $this->createDepartureComments($request, $departures);
        
        return $request;
    }
    
    private function parseDate($dateStr)
    {
        if (empty($dateStr) || trim($dateStr) === '') {
            return null;
        }
        
        try {
            // Формат: 16.09.2025
            return Carbon::createFromFormat('d.m.Y', trim($dateStr))->startOfDay();
        } catch (\Exception $e) {
            return null;
        }
    }
    
    private function cleanPhone($phone)
    {
        // Убираем все кроме цифр
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        
        if (strlen($cleaned) === 11 && $cleaned[0] === '8') {
            $cleaned = '7' . substr($cleaned, 1);
        }
        
        return $cleaned;
    }
    
    private function parseAnimalsCount($countStr)
    {
        $countStr = mb_strtolower(trim($countStr));
        
        if ($countStr === 'стая') {
            return 5; // По умолчанию для стаи
        }
        
        // Извлекаем число
        if (preg_match('/(\d+)/', $countStr, $matches)) {
            return (int)$matches[1];
        }
        
        return 1;
    }
    
    private function parseBoolean($value)
    {
        $value = mb_strtoupper(trim($value));
        return $value === 'TRUE' || $value === '1';
    }
    
    private function determineStatus($result, $notes, $departuresCount)
    {
        $combined = mb_strtolower($result . ' ' . $notes);
        
        // Приоритет 1: Если отловлено - всегда статус captured
        if (strpos($combined, 'отловлен') !== false) {
            return 'captured';
        }
        
        // Приоритет 2: Если больше 2 выездов - заявка закрыта
        if ($departuresCount >= 2) {
            return 'completed';
        }
        
        // Остальные статусы
        if (strpos($combined, 'не обнаружен') !== false) {
            return 'processing';
        }
        
        if (strpos($combined, 'выезд') !== false) {
            return 'capture_scheduled';
        }
        
        return 'new';
    }
    
    private function parseFirstDepartureDate($datesStr)
    {
        if (empty($datesStr)) {
            return null;
        }
        
        // Ищем первую дату в формате DD.MM.YYYY
        if (preg_match('/(\d{2}\.\d{2}\.\d{4})/', $datesStr, $matches)) {
            try {
                return Carbon::createFromFormat('d.m.Y', $matches[1])->startOfDay();
            } catch (\Exception $e) {
                return null;
            }
        }
        
        return null;
    }
    
    private function buildAnimalDescription($description, $isPregnant, $count)
    {
        $parts = [$description];
        
        if ($isPregnant) {
            $parts[] = 'Беременная';
        }
        
        if ($count > 1) {
            $parts[] = "Количество: {$count}";
        }
        
        return implode('. ', array_filter($parts));
    }
    
    private function buildDepartureNotes($dates, $result, $notes)
    {
        $parts = [];
        
        if (!empty($result)) {
            $parts[] = "Результат: {$result}";
        }
        
        if (!empty($dates)) {
            $parts[] = "Даты выездов: {$dates}";
        }
        
        if (!empty($notes)) {
            $parts[] = "Детали: {$notes}";
        }
        
        return implode("\n", array_filter($parts));
    }
    
    private function buildNotes($isControl, $isPregnant)
    {
        $parts = [];
        
        if ($isControl) {
            $parts[] = 'Требует контроля';
        }
        
        if ($isPregnant) {
            $parts[] = 'Беременное животное';
        }
        
        return implode('. ', array_filter($parts)) ?: null;
    }
    
    private function parseDepartures($datesStr, $notesStr)
    {
        $departures = [];
        
        if (empty($datesStr)) {
            return $departures;
        }
        
        // Извлекаем все даты из строки
        preg_match_all('/(\d{2}\.\d{2}\.\d{4})/', $datesStr, $dateMatches);
        $dates = $dateMatches[1];
        
        // Разбиваем заметки по датам или по разделителям
        $notesParts = [];
        if (!empty($notesStr)) {
            // Пытаемся разбить по датам в заметках
            $notesParts = preg_split('/(\d{2}\.\d{2}\.?\s)/', $notesStr, -1, PREG_SPLIT_DELIM_CAPTURE);
            
            // Если не получилось, пробуем разбить по числам (1., 2., 3.)
            if (count($notesParts) <= 1) {
                $notesParts = preg_split('/(\d+\.\s)/', $notesStr, -1, PREG_SPLIT_DELIM_CAPTURE);
            }
            
            // Если все еще не получилось, просто делим на равные части
            if (count($notesParts) <= 1 && count($dates) > 1) {
                $words = explode(' ', $notesStr);
                $chunkSize = ceil(count($words) / count($dates));
                $notesParts = array_chunk($words, $chunkSize);
                $notesParts = array_map(function($chunk) {
                    return implode(' ', $chunk);
                }, $notesParts);
            }
        }
        
        // Создаем записи о выездах
        foreach ($dates as $index => $dateStr) {
            $note = '';
            if (isset($notesParts[$index])) {
                $note = is_array($notesParts[$index]) ? implode(' ', $notesParts[$index]) : $notesParts[$index];
            } elseif (count($notesParts) === 1) {
                // Если одна заметка на все выезды
                $note = is_array($notesParts[0]) ? implode(' ', $notesParts[0]) : $notesParts[0];
            }
            
            $departures[] = [
                'date' => $dateStr,
                'note' => trim($note)
            ];
        }
        
        return $departures;
    }
    
    private function createDepartureComments($request, $departures)
    {
        if (empty($departures)) {
            return;
        }
        
        foreach ($departures as $index => $departure) {
            $departureNum = $index + 1;
            $comment = "Выезд #{$departureNum} ({$departure['date']})";
            
            if (!empty($departure['note'])) {
                $comment .= "\nРезультат: " . $departure['note'];
            }
            
            // Создаем комментарий от системы (user_id = 1)
            OsvvComment::create([
                'osvv_request_id' => $request->id,
                'user_id' => 1,
                'comment' => $comment,
                'created_at' => $this->parseDate($departure['date']) ?: $request->created_at,
                'updated_at' => $this->parseDate($departure['date']) ?: $request->created_at,
            ]);
        }
    }
}
