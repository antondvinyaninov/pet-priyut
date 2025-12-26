<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OsvvRequest;
use Illuminate\Support\Facades\Http;

class GeocodeOsvvAddresses extends Command
{
    protected $signature = 'osvv:geocode {--limit=10 : Количество адресов для обработки за раз}';
    protected $description = 'Геокодирование адресов заявок ОСВВ через Яндекс.Карты';

    private $apiKey;
    
    public function handle()
    {
        $limit = (int) $this->option('limit');
        
        // Получаем заявки без координат
        $requests = OsvvRequest::whereNull('latitude')
            ->orWhereNull('longitude')
            ->limit($limit)
            ->get();
        
        if ($requests->isEmpty()) {
            $this->info('Все адреса уже геокодированы!');
            return 0;
        }
        
        $this->info("Найдено {$requests->count()} адресов для геокодирования...");
        
        $success = 0;
        $failed = 0;
        
        foreach ($requests as $request) {
            try {
                $this->info("Обработка заявки #{$request->id}: {$request->location_address}");
                
                $coordinates = $this->geocodeAddress($request->location_address);
                
                if ($coordinates) {
                    $request->update([
                        'latitude' => $coordinates['lat'],
                        'longitude' => $coordinates['lon']
                    ]);
                    
                    $this->info("✓ Координаты найдены: {$coordinates['lat']}, {$coordinates['lon']}");
                    $success++;
                } else {
                    $this->warn("✗ Не удалось найти координаты");
                    $failed++;
                }
                
                // Задержка между запросами (чтобы не превысить лимиты API)
                sleep(1);
                
            } catch (\Exception $e) {
                $this->error("Ошибка: " . $e->getMessage());
                $failed++;
            }
        }
        
        $this->info("\nГеокодирование завершено!");
        $this->info("Успешно: {$success}");
        $this->info("Ошибок: {$failed}");
        
        // Показываем сколько еще осталось
        $remaining = OsvvRequest::whereNull('latitude')
            ->orWhereNull('longitude')
            ->count();
        
        if ($remaining > 0) {
            $this->info("\nОсталось адресов: {$remaining}");
            $this->info("Запустите команду еще раз: php artisan osvv:geocode");
        }
        
        return 0;
    }
    
    private function geocodeAddress($address)
    {
        // Очищаем адрес от лишней информации
        $address = $this->cleanAddress($address);
        
        // Добавляем "Воронеж, Россия" к адресу если его нет
        if (stripos($address, 'воронеж') === false) {
            $address = "Воронеж, " . $address;
        }
        
        try {
            // Используем Nominatim (OpenStreetMap) - бесплатный геокодер
            $url = 'https://nominatim.openstreetmap.org/search';
            
            $params = [
                'q' => $address,
                'format' => 'json',
                'limit' => 1,
                'addressdetails' => 1,
            ];
            
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'ASUP/1.0 (Voronezh Animal Shelter Management System)'
                ])
                ->get($url, $params);
            
            if (!$response->successful()) {
                return null;
            }
            
            $data = $response->json();
            
            if (empty($data)) {
                return null;
            }
            
            $result = $data[0];
            
            return [
                'lat' => (float) $result['lat'],
                'lon' => (float) $result['lon']
            ];
            
        } catch (\Exception $e) {
            $this->error("Ошибка геокодирования: " . $e->getMessage());
            return null;
        }
    }
    
    private function cleanAddress($address)
    {
        // Убираем лишние описания
        $address = preg_replace('/,\s*территория.*/i', '', $address);
        $address = preg_replace('/,\s*ориентир.*/i', '', $address);
        $address = preg_replace('/\(.*?\)/', '', $address);
        
        // Заменяем сокращения
        $address = str_replace(['ул.', 'ул ', 'д.', 'д '], ['улица ', 'улица ', 'дом ', 'дом '], $address);
        $address = str_replace(['пр.', 'пр '], ['проспект ', 'проспект '], $address);
        $address = str_replace(['пер.', 'пер '], ['переулок ', 'переулок '], $address);
        
        // Если после названия улицы идет цифра без слова "дом", добавляем его
        // Например: "Хользунова 38" -> "Хользунова дом 38"
        $address = preg_replace('/([а-яА-Я]+)\s+(\d+)/u', '$1 дом $2', $address);
        
        // Убираем двойные "дом дом"
        $address = str_replace('дом дом', 'дом', $address);
        
        return trim($address);
    }
}
