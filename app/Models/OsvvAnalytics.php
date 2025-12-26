<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OsvvAnalytics extends Model
{
    use HasFactory;

    protected $table = 'osvv_analytics';

    protected $fillable = [
        'request_id',
        'event_type',
        'event_data',
        'event_time',
        'user_id',
        'latitude',
        'longitude',
        'duration_minutes',
        'notes'
    ];

    protected $casts = [
        'event_data' => 'array',
        'event_time' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8'
    ];

    // Связь с заявкой
    public function request()
    {
        return $this->belongsTo(OsvvRequest::class, 'request_id');
    }

    // Статические методы для записи событий
    public static function logEvent($requestId, $eventType, $eventData = [], $options = [])
    {
        return self::create([
            'request_id' => $requestId,
            'event_type' => $eventType,
            'event_data' => $eventData,
            'event_time' => $options['event_time'] ?? now(),
            'user_id' => $options['user_id'] ?? auth()->id(),
            'latitude' => $options['latitude'] ?? null,
            'longitude' => $options['longitude'] ?? null,
            'duration_minutes' => $options['duration_minutes'] ?? null,
            'notes' => $options['notes'] ?? null
        ]);
    }

    // Методы для аналитики
    public static function getProcessingEfficiency($dateFrom = null, $dateTo = null)
    {
        $query = self::query();
        
        if ($dateFrom) {
            $query->where('event_time', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('event_time', '<=', $dateTo);
        }

        return [
            'total_events' => $query->count(),
            'avg_processing_time' => $query->whereNotNull('duration_minutes')->avg('duration_minutes'),
            'events_by_type' => $query->groupBy('event_type')->selectRaw('event_type, count(*) as count')->pluck('count', 'event_type'),
            'daily_activity' => $query->selectRaw('DATE(event_time) as date, count(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('count', 'date')
        ];
    }

    public static function getRouteEfficiency($dateFrom = null, $dateTo = null)
    {
        $query = self::where('event_type', 'departure_completed');
        
        if ($dateFrom) {
            $query->where('event_time', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('event_time', '<=', $dateTo);
        }

        $completedDepartures = $query->get();
        
        $routeStats = [];
        foreach ($completedDepartures as $departure) {
            $data = $departure->event_data;
            $district = $data['district'] ?? 'Неизвестно';
            
            if (!isset($routeStats[$district])) {
                $routeStats[$district] = [
                    'count' => 0,
                    'total_time' => 0,
                    'total_distance' => 0,
                    'requests_processed' => 0
                ];
            }
            
            $routeStats[$district]['count']++;
            $routeStats[$district]['total_time'] += $departure->duration_minutes ?? 0;
            $routeStats[$district]['total_distance'] += $data['distance'] ?? 0;
            $routeStats[$district]['requests_processed'] += $data['requests_count'] ?? 0;
        }

        // Вычисляем средние значения
        foreach ($routeStats as $district => &$stats) {
            $stats['avg_time'] = $stats['count'] > 0 ? round($stats['total_time'] / $stats['count'], 2) : 0;
            $stats['avg_distance'] = $stats['count'] > 0 ? round($stats['total_distance'] / $stats['count'], 2) : 0;
            $stats['avg_requests'] = $stats['count'] > 0 ? round($stats['requests_processed'] / $stats['count'], 2) : 0;
            $stats['efficiency_score'] = $stats['avg_requests'] > 0 ? round($stats['avg_requests'] / ($stats['avg_time'] / 60), 2) : 0;
        }

        return $routeStats;
    }

    public static function getPriorityHandlingStats($dateFrom = null, $dateTo = null)
    {
        $query = self::whereIn('event_type', ['status_changed', 'departure_completed']);
        
        if ($dateFrom) {
            $query->where('event_time', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('event_time', '<=', $dateTo);
        }

        $events = $query->with('request')->get();
        
        $priorityStats = [
            'urgent' => ['count' => 0, 'avg_response_time' => 0, 'total_response_time' => 0],
            'normal' => ['count' => 0, 'avg_response_time' => 0, 'total_response_time' => 0],
            'low' => ['count' => 0, 'avg_response_time' => 0, 'total_response_time' => 0]
        ];

        foreach ($events as $event) {
            if (!$event->request) continue;
            
            $priority = 'normal';
            if ($event->request->has_bite || $event->request->is_urgent) {
                $priority = 'urgent';
            } elseif ($event->request->priority_level < 3) {
                $priority = 'low';
            }

            $responseTime = $event->duration_minutes ?? 0;
            $priorityStats[$priority]['count']++;
            $priorityStats[$priority]['total_response_time'] += $responseTime;
        }

        // Вычисляем средние значения
        foreach ($priorityStats as $priority => &$stats) {
            $stats['avg_response_time'] = $stats['count'] > 0 ? round($stats['total_response_time'] / $stats['count'], 2) : 0;
        }

        return $priorityStats;
    }

    public static function getImprovementSuggestions($dateFrom = null, $dateTo = null)
    {
        $suggestions = [];
        
        // Анализ эффективности маршрутов
        $routeStats = self::getRouteEfficiency($dateFrom, $dateTo);
        
        // Находим наименее эффективные районы
        $inefficientDistricts = collect($routeStats)
            ->sortBy('efficiency_score')
            ->take(3)
            ->keys()
            ->toArray();

        if (!empty($inefficientDistricts)) {
            $suggestions[] = [
                'type' => 'route_optimization',
                'priority' => 'high',
                'title' => 'Оптимизация маршрутов в районах',
                'description' => 'Районы с низкой эффективностью: ' . implode(', ', $inefficientDistricts),
                'action' => 'Пересмотреть группировку заявок и порядок объезда в этих районах'
            ];
        }

        // Анализ времени обработки приоритетных заявок
        $priorityStats = self::getPriorityHandlingStats($dateFrom, $dateTo);
        
        if ($priorityStats['urgent']['avg_response_time'] > 120) { // больше 2 часов
            $suggestions[] = [
                'type' => 'priority_handling',
                'priority' => 'critical',
                'title' => 'Медленная обработка срочных заявок',
                'description' => 'Среднее время обработки срочных заявок: ' . round($priorityStats['urgent']['avg_response_time'] / 60, 1) . ' часов',
                'action' => 'Увеличить приоритет срочных заявок в алгоритме планирования'
            ];
        }

        // Анализ загруженности по дням недели
        $dailyStats = self::selectRaw('strftime("%w", event_time) as day_of_week, count(*) as count')
            ->where('event_type', 'departure_completed')
            ->when($dateFrom, fn($q) => $q->where('event_time', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->where('event_time', '<=', $dateTo))
            ->groupBy('day_of_week')
            ->pluck('count', 'day_of_week');

        $avgDaily = $dailyStats->avg();
        $overloadedDays = $dailyStats->filter(fn($count) => $count > $avgDaily * 1.5);

        if ($overloadedDays->isNotEmpty()) {
            $dayNames = [0 => 'Воскресенье', 1 => 'Понедельник', 2 => 'Вторник', 3 => 'Среда', 4 => 'Четверг', 5 => 'Пятница', 6 => 'Суббота'];
            $overloadedDayNames = $overloadedDays->keys()->map(fn($day) => $dayNames[$day])->implode(', ');
            
            $suggestions[] = [
                'type' => 'workload_balancing',
                'priority' => 'medium',
                'title' => 'Неравномерная загрузка по дням',
                'description' => 'Перегруженные дни: ' . $overloadedDayNames,
                'action' => 'Рассмотреть возможность перераспределения нагрузки или увеличения ресурсов в эти дни'
            ];
        }

        return $suggestions;
    }
} 