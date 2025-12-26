<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Загружаем конфигурацию Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🚀 Создание тестовых данных аналитики...\n";

// Получаем существующие заявки
$requests = DB::table('osvv_requests')->limit(10)->get();

if ($requests->isEmpty()) {
    echo "❌ Нет заявок для создания аналитики. Сначала создайте заявки.\n";
    exit(1);
}

$analyticsData = [];
$eventTypes = [
    'departure_started',
    'departure_completed', 
    'status_changed',
    'request_created',
    'request_completed'
];

$districts = ['Центральный', 'Советский', 'Левобережный', 'Коминтерновский', 'Ленинский'];

foreach ($requests as $request) {
    $requestId = $request->id;
    $baseTime = Carbon::parse($request->created_at);
    
    // 1. Событие создания заявки
    $analyticsData[] = [
        'request_id' => $requestId,
        'event_type' => 'request_created',
        'event_data' => json_encode([
            'animal_type' => $request->animal_type ?? 'собака',
            'district' => $request->district ?? $districts[array_rand($districts)],
            'has_bite' => (bool)($request->has_bite ?? rand(0, 1)),
            'is_pregnant' => (bool)($request->is_pregnant ?? rand(0, 1)),
            'animals_count' => $request->animals_count ?? rand(1, 3),
            'source_type' => $request->source_type ?? 'phone',
        ]),
        'event_time' => $baseTime,
        'user_id' => 'system',
        'latitude' => $request->latitude ?? (51.6 + (rand(-100, 100) / 1000)),
        'longitude' => $request->longitude ?? (39.2 + (rand(-100, 100) / 1000)),
        'duration_minutes' => null,
        'notes' => 'Автоматически созданная заявка',
        'created_at' => now(),
        'updated_at' => now(),
    ];
    
    // 2. Событие начала выезда (через 1-3 дня)
    $departureStartTime = $baseTime->copy()->addDays(rand(1, 3))->addHours(rand(8, 16));
    $analyticsData[] = [
        'request_id' => $requestId,
        'event_type' => 'departure_started',
        'event_data' => json_encode([
            'district' => $request->district ?? $districts[array_rand($districts)],
            'planned_requests' => rand(1, 4),
            'start_time' => $departureStartTime->format('Y-m-d H:i:s'),
        ]),
        'event_time' => $departureStartTime,
        'user_id' => 'catcher_' . rand(1, 3),
        'latitude' => $request->latitude ?? (51.6 + (rand(-100, 100) / 1000)),
        'longitude' => $request->longitude ?? (39.2 + (rand(-100, 100) / 1000)),
        'duration_minutes' => null,
        'notes' => 'Начало выезда отловщика',
        'created_at' => now(),
        'updated_at' => now(),
    ];
    
    // 3. Событие завершения выезда (через 2-6 часов после начала)
    $departureEndTime = $departureStartTime->copy()->addHours(rand(2, 6));
    $completedRequests = rand(1, 3);
    $distance = rand(15, 50); // км
    $duration = $departureStartTime->diffInMinutes($departureEndTime);
    
    $analyticsData[] = [
        'request_id' => $requestId,
        'event_type' => 'departure_completed',
        'event_data' => json_encode([
            'completed_requests' => $completedRequests,
            'distance' => $distance,
            'efficiency_score' => round($completedRequests / ($duration / 60), 2),
            'district' => $request->district ?? $districts[array_rand($districts)],
            'requests_count' => $completedRequests,
        ]),
        'event_time' => $departureEndTime,
        'user_id' => 'catcher_' . rand(1, 3),
        'latitude' => $request->latitude ?? (51.6 + (rand(-100, 100) / 1000)),
        'longitude' => $request->longitude ?? (39.2 + (rand(-100, 100) / 1000)),
        'duration_minutes' => $duration,
        'notes' => "Выезд завершен. Обработано заявок: {$completedRequests}, пройдено: {$distance} км",
        'created_at' => now(),
        'updated_at' => now(),
    ];
    
    // 4. Изменение статуса (если заявка была обработана)
    if (rand(0, 1)) {
        $statusChangeTime = $departureEndTime->copy()->addMinutes(rand(10, 60));
        $analyticsData[] = [
            'request_id' => $requestId,
            'event_type' => 'status_changed',
            'event_data' => json_encode([
                'old_status' => 'in_progress',
                'new_status' => 'completed',
                'district' => $request->district ?? $districts[array_rand($districts)],
            ]),
            'event_time' => $statusChangeTime,
            'user_id' => 'admin_' . rand(1, 2),
            'latitude' => null,
            'longitude' => null,
            'duration_minutes' => null,
            'notes' => 'Заявка завершена после успешного отлова',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        // 5. Завершение заявки
        $completionTime = $statusChangeTime->copy()->addMinutes(rand(5, 30));
        $processingTime = $baseTime->diffInMinutes($completionTime);
        
        $analyticsData[] = [
            'request_id' => $requestId,
            'event_type' => 'request_completed',
            'event_data' => json_encode([
                'processing_time_minutes' => $processingTime,
                'district' => $request->district ?? $districts[array_rand($districts)],
                'departures_count' => 1,
                'had_bite' => (bool)($request->has_bite ?? rand(0, 1)),
                'was_pregnant' => (bool)($request->is_pregnant ?? rand(0, 1)),
            ]),
            'event_time' => $completionTime,
            'user_id' => 'system',
            'latitude' => $request->latitude ?? (51.6 + (rand(-100, 100) / 1000)),
            'longitude' => $request->longitude ?? (39.2 + (rand(-100, 100) / 1000)),
            'duration_minutes' => $processingTime,
            'notes' => "Заявка полностью обработана за {$processingTime} минут",
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

// Добавляем дополнительные события за последние 30 дней
for ($i = 0; $i < 20; $i++) {
    $randomRequest = $requests->random();
    $randomDate = Carbon::now()->subDays(rand(1, 30));
    $district = $districts[array_rand($districts)];
    
    $analyticsData[] = [
        'request_id' => $randomRequest->id,
        'event_type' => $eventTypes[array_rand($eventTypes)],
        'event_data' => json_encode([
            'district' => $district,
            'efficiency_score' => rand(10, 30) / 10,
            'completed_requests' => rand(1, 4),
            'distance' => rand(10, 60),
        ]),
        'event_time' => $randomDate,
        'user_id' => 'user_' . rand(1, 5),
        'latitude' => 51.6 + (rand(-100, 100) / 1000),
        'longitude' => 39.2 + (rand(-100, 100) / 1000),
        'duration_minutes' => rand(60, 300),
        'notes' => 'Тестовое событие для аналитики',
        'created_at' => now(),
        'updated_at' => now(),
    ];
}

// Вставляем данные в базу
try {
    DB::table('osvv_analytics')->insert($analyticsData);
    echo "✅ Создано " . count($analyticsData) . " записей аналитики\n";
    
    // Показываем статистику
    $stats = DB::table('osvv_analytics')
        ->selectRaw('event_type, count(*) as count')
        ->groupBy('event_type')
        ->get();
    
    echo "\n📊 Статистика созданных событий:\n";
    foreach ($stats as $stat) {
        echo "  - {$stat->event_type}: {$stat->count} событий\n";
    }
    
    echo "\n🎯 Система аналитики готова к использованию!\n";
    echo "Перейдите на http://127.0.0.1:8000/admin/analytics для просмотра\n";
    
} catch (Exception $e) {
    echo "❌ Ошибка при создании данных: " . $e->getMessage() . "\n";
    exit(1);
} 