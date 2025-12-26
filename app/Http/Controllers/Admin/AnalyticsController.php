<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OsvvAnalytics;
use App\Models\OsvvRequest;
use App\Services\AIAnalyticsService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    protected $aiService;

    public function __construct(AIAnalyticsService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function index(Request $request)
    {
        // По умолчанию показываем данные за последние 3 месяца
        $dateFrom = $request->get('date_from', Carbon::now()->subMonths(3)->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));
        
        try {
            // Получаем основную аналитику
            $efficiency = OsvvAnalytics::getProcessingEfficiency($dateFrom, $dateTo);
            $routeStats = OsvvAnalytics::getRouteEfficiency($dateFrom, $dateTo);
            $priorityStats = OsvvAnalytics::getPriorityHandlingStats($dateFrom, $dateTo);
            $suggestions = OsvvAnalytics::getImprovementSuggestions($dateFrom, $dateTo);
        } catch (\Exception $e) {
            // Если есть ошибка в аналитике, используем пустые данные
            $efficiency = ['avg_processing_time' => 0];
            $routeStats = [];
            $priorityStats = [];
            $suggestions = [];
        }
        
        // Дополнительная статистика
        $totalRequests = OsvvRequest::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        $completedRequests = OsvvRequest::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'completed')->count();
        
        $completionRate = $totalRequests > 0 ? round(($completedRequests / $totalRequests) * 100, 2) : 0;
        
        // Статистика по типам животных
        $animalStats = OsvvRequest::whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('animal_type, count(*) as count, avg(CASE WHEN status = "completed" THEN 1 ELSE 0 END) * 100 as completion_rate')
            ->groupBy('animal_type')
            ->get();
        
        // Статистика по районам
        $districtStats = OsvvRequest::whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('district, count(*) as total, sum(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed')
            ->groupBy('district')
            ->get()
            ->map(function ($item) {
                $item->completion_rate = $item->total > 0 ? round(($item->completed / $item->total) * 100, 2) : 0;
                return $item;
            });
        
        // Статистика по укусам
        $biteStats = [
            'total_bites' => OsvvRequest::whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('has_bite', true)
                ->count(),
            'resolved_bites' => OsvvRequest::whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('has_bite', true)
                ->whereIn('status', ['captured', 'completed'])
                ->count(),
            'pending_bites' => OsvvRequest::whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('has_bite', true)
                ->whereNotIn('status', ['captured', 'completed', 'cancelled'])
                ->count(),
            'bites_by_district' => OsvvRequest::whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('has_bite', true)
                ->selectRaw('district, count(*) as count')
                ->groupBy('district')
                ->orderBy('count', 'desc')
                ->get(),
            'avg_response_time' => OsvvRequest::whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('has_bite', true)
                ->whereNotNull('departure_date')
                ->where('departure_date', '>=', 'created_at')
                ->selectRaw('AVG((JULIANDAY(departure_date) - JULIANDAY(created_at))) as avg_days')
                ->value('avg_days')
        ];
        
        $biteStats['resolution_rate'] = $biteStats['total_bites'] > 0 
            ? round(($biteStats['resolved_bites'] / $biteStats['total_bites']) * 100, 2) 
            : 0;
        
        return view('admin.analytics.index', compact(
            'efficiency',
            'routeStats',
            'priorityStats',
            'suggestions',
            'totalRequests',
            'completedRequests',
            'completionRate',
            'animalStats',
            'districtStats',
            'biteStats',
            'dateFrom',
            'dateTo'
        ));
    }
    
    public function logDepartureStart(Request $request)
    {
        $requestId = $request->get('request_id');
        $zoneData = $request->get('zone_data', []);
        
        OsvvAnalytics::logEvent($requestId, 'departure_started', [
            'zone_data' => $zoneData,
            'start_time' => now(),
            'planned_requests' => $zoneData['requests_count'] ?? 0,
            'district' => $zoneData['district'] ?? null
        ], [
            'latitude' => $request->get('latitude'),
            'longitude' => $request->get('longitude')
        ]);
        
        return response()->json(['success' => true]);
    }
    
    public function logDepartureComplete(Request $request)
    {
        $requestId = $request->get('request_id');
        $duration = $request->get('duration_minutes');
        $completedRequests = $request->get('completed_requests', 0);
        $distance = $request->get('distance', 0);
        $notes = $request->get('notes');
        
        OsvvAnalytics::logEvent($requestId, 'departure_completed', [
            'completed_requests' => $completedRequests,
            'distance' => $distance,
            'efficiency_score' => $duration > 0 ? round($completedRequests / ($duration / 60), 2) : 0,
            'district' => $request->get('district'),
            'requests_count' => $completedRequests
        ], [
            'duration_minutes' => $duration,
            'latitude' => $request->get('latitude'),
            'longitude' => $request->get('longitude'),
            'notes' => $notes
        ]);
        
        return response()->json(['success' => true]);
    }
    
    public function logStatusChange(Request $request)
    {
        $requestId = $request->get('request_id');
        $oldStatus = $request->get('old_status');
        $newStatus = $request->get('new_status');
        
        OsvvAnalytics::logEvent($requestId, 'status_changed', [
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'change_reason' => $request->get('reason')
        ]);
        
        return response()->json(['success' => true]);
    }
    
    public function getEfficiencyTrends(Request $request)
    {
        $days = $request->get('days', 30);
        $dateFrom = Carbon::now()->subDays($days);
        
        $trends = OsvvAnalytics::selectRaw('
            DATE(event_time) as date,
            COUNT(*) as total_events,
            AVG(duration_minutes) as avg_duration,
            COUNT(CASE WHEN event_type = "departure_completed" THEN 1 END) as completed_departures
        ')
        ->where('event_time', '>=', $dateFrom)
        ->groupBy('date')
        ->orderBy('date')
        ->get();
        
        return response()->json($trends);
    }
    
    public function getRouteOptimizationData(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->subDays(7)->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));
        
        // Анализ маршрутов за период
        $routeData = OsvvAnalytics::where('event_type', 'departure_completed')
            ->whereBetween('event_time', [$dateFrom, $dateTo])
            ->with('request')
            ->get()
            ->groupBy(function ($item) {
                return $item->event_data['district'] ?? 'Неизвестно';
            })
            ->map(function ($districtEvents, $district) {
                $totalTime = $districtEvents->sum('duration_minutes');
                $totalRequests = $districtEvents->sum(function ($event) {
                    return $event->event_data['completed_requests'] ?? 0;
                });
                $totalDistance = $districtEvents->sum(function ($event) {
                    return $event->event_data['distance'] ?? 0;
                });
                
                return [
                    'district' => $district,
                    'departures_count' => $districtEvents->count(),
                    'total_time' => $totalTime,
                    'total_requests' => $totalRequests,
                    'total_distance' => $totalDistance,
                    'avg_time_per_departure' => $districtEvents->count() > 0 ? round($totalTime / $districtEvents->count(), 2) : 0,
                    'avg_requests_per_departure' => $districtEvents->count() > 0 ? round($totalRequests / $districtEvents->count(), 2) : 0,
                    'efficiency_score' => $totalTime > 0 ? round($totalRequests / ($totalTime / 60), 2) : 0,
                    'improvement_potential' => $this->calculateImprovementPotential($districtEvents)
                ];
            });
        
        return response()->json($routeData->values());
    }
    
    private function calculateImprovementPotential($events)
    {
        if ($events->count() < 2) {
            return 'Недостаточно данных';
        }
        
        $efficiencyScores = $events->map(function ($event) {
            $duration = $event->duration_minutes ?? 0;
            $requests = $event->event_data['completed_requests'] ?? 0;
            return $duration > 0 ? $requests / ($duration / 60) : 0;
        });
        
        $avgEfficiency = $efficiencyScores->avg();
        $maxEfficiency = $efficiencyScores->max();
        
        if ($maxEfficiency > $avgEfficiency * 1.3) {
            return 'Высокий потенциал улучшения';
        } elseif ($maxEfficiency > $avgEfficiency * 1.1) {
            return 'Средний потенциал улучшения';
        } else {
            return 'Стабильная эффективность';
        }
    }
    
    public function exportAnalytics(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));
        
        $analytics = OsvvAnalytics::whereBetween('event_time', [$dateFrom, $dateTo])
            ->with('request')
            ->orderBy('event_time', 'desc')
            ->get();
        
        $filename = 'analytics_' . $dateFrom . '_to_' . $dateTo . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($analytics) {
            $file = fopen('php://output', 'w');
            
            // Заголовки CSV
            fputcsv($file, [
                'ID события',
                'ID заявки',
                'Тип события',
                'Время события',
                'Длительность (мин)',
                'Район',
                'Координаты',
                'Заметки'
            ]);
            
            foreach ($analytics as $event) {
                fputcsv($file, [
                    $event->id,
                    $event->request_id,
                    $event->event_type,
                    $event->event_time->format('Y-m-d H:i:s'),
                    $event->duration_minutes,
                    $event->event_data['district'] ?? '',
                    ($event->latitude && $event->longitude) ? $event->latitude . ', ' . $event->longitude : '',
                    $event->notes
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    // AI-методы

    public function aiPredictions(Request $request)
    {
        try {
            $predictions = $this->aiService->predictWeeklyRequests();
            $anomalies = $this->aiService->detectAnomalies();
            $teamPerformance = $this->aiService->analyzeTeamPerformance();
            
            return response()->json([
                'predictions' => $predictions,
                'anomalies' => $anomalies,
                'team_performance' => $teamPerformance
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Ошибка AI-анализа: ' . $e->getMessage()
            ], 500);
        }
    }

    public function aiRouteOptimization(Request $request)
    {
        try {
            $optimization = $this->aiService->optimizeRoutes();
            return response()->json($optimization);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Ошибка оптимизации маршрутов: ' . $e->getMessage()
            ], 500);
        }
    }

    public function aiPredictCompletion(Request $request, $requestId)
    {
        try {
            $prediction = $this->aiService->predictRequestCompletion($requestId);
            return response()->json($prediction);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Ошибка предсказания времени: ' . $e->getMessage()
            ], 500);
        }
    }

    public function aiAnomalyDetection(Request $request)
    {
        try {
            $anomalies = $this->aiService->detectAnomalies();
            return response()->json($anomalies);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Ошибка детекции аномалий: ' . $e->getMessage()
            ], 500);
        }
    }
} 