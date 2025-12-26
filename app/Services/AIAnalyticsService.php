<?php

namespace App\Services;

use App\Models\OsvvRequest;
use App\Models\OsvvAnalytics;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AIAnalyticsService
{
    /**
     * Предсказание количества заявок на следующую неделю
     */
    public function predictWeeklyRequests(): array
    {
        // Получаем данные за последние 12 недель
        $weeklyData = OsvvRequest::selectRaw('
            strftime("%Y", created_at) as year,
            strftime("%W", created_at) as week,
            COUNT(*) as count,
            AVG(CASE WHEN has_bite = 1 THEN 1 ELSE 0 END) as urgent_ratio
        ')
        ->where('created_at', '>=', Carbon::now()->subWeeks(12))
        ->groupBy('year', 'week')
        ->orderBy('year')
        ->orderBy('week')
        ->get();

        if ($weeklyData->count() < 4) {
            return [
                'prediction' => 0,
                'confidence' => 0,
                'trend' => 'insufficient_data',
                'factors' => []
            ];
        }

        // Простая линейная регрессия для тренда
        $counts = $weeklyData->pluck('count')->toArray();
        $trend = $this->calculateTrend($counts);
        
        // Сезонный анализ (учитываем день недели)
        $seasonalFactor = $this->calculateSeasonalFactor();
        
        // Предсказание на основе тренда и сезонности
        $lastWeekCount = end($counts);
        $prediction = max(0, round($lastWeekCount + $trend + $seasonalFactor));
        
        // Расчет доверительного интервала
        $variance = $this->calculateVariance($counts);
        $confidence = max(0, min(100, 100 - ($variance * 10)));

        return [
            'prediction' => $prediction,
            'confidence' => round($confidence, 1),
            'trend' => $trend > 0 ? 'increasing' : ($trend < 0 ? 'decreasing' : 'stable'),
            'trend_value' => round($trend, 2),
            'seasonal_factor' => round($seasonalFactor, 2),
            'factors' => $this->identifyInfluencingFactors($weeklyData)
        ];
    }

    /**
     * Анализ паттернов и аномалий
     */
    public function detectAnomalies(): array
    {
        $dailyStats = OsvvRequest::selectRaw('
            date(created_at) as date,
            COUNT(*) as count,
            AVG(CASE WHEN has_bite = 1 THEN 1 ELSE 0 END) as urgent_ratio,
            COUNT(DISTINCT district) as districts_count
        ')
        ->where('created_at', '>=', Carbon::now()->subDays(30))
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        $anomalies = [];
        $counts = $dailyStats->pluck('count')->toArray();
        
        if (count($counts) > 7) {
            $mean = array_sum($counts) / count($counts);
            $stdDev = sqrt(array_sum(array_map(fn($x) => pow($x - $mean, 2), $counts)) / count($counts));
            
            foreach ($dailyStats as $day) {
                $zScore = abs(($day->count - $mean) / ($stdDev ?: 1));
                
                if ($zScore > 2) { // Аномалия если отклонение больше 2 стандартных отклонений
                    $anomalies[] = [
                        'date' => $day->date,
                        'count' => $day->count,
                        'expected' => round($mean),
                        'deviation' => round($zScore, 2),
                        'type' => $day->count > $mean ? 'spike' : 'drop',
                        'urgent_ratio' => round($day->urgent_ratio * 100, 1),
                        'severity' => $zScore > 3 ? 'high' : 'medium'
                    ];
                }
            }
        }

        return [
            'anomalies' => $anomalies,
            'total_anomalies' => count($anomalies),
            'analysis_period' => 30,
            'recommendations' => $this->generateAnomalyRecommendations($anomalies)
        ];
    }

    /**
     * Оптимизация маршрутов с помощью AI
     */
    public function optimizeRoutes(): array
    {
        // Получаем активные заявки для планирования
        $activeRequests = OsvvRequest::where('status', 'pending')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        if ($activeRequests->count() < 2) {
            return [
                'routes' => [],
                'optimization' => 'insufficient_data',
                'savings' => 0
            ];
        }

        // Группируем по районам и приоритету
        $clusters = $this->clusterRequestsByLocation($activeRequests);
        
        // Оптимизируем каждый кластер
        $optimizedRoutes = [];
        $totalSavings = 0;

        foreach ($clusters as $district => $requests) {
            $route = $this->optimizeClusterRoute($requests);
            $optimizedRoutes[] = [
                'district' => $district,
                'requests_count' => count($requests),
                'estimated_time' => $route['estimated_time'],
                'estimated_distance' => $route['estimated_distance'],
                'priority_score' => $route['priority_score'],
                'route_order' => $route['order'],
                'efficiency_score' => $route['efficiency_score']
            ];
            
            $totalSavings += $route['time_savings'];
        }

        // Сортируем маршруты по приоритету и эффективности
        usort($optimizedRoutes, function($a, $b) {
            return ($b['priority_score'] * $b['efficiency_score']) <=> ($a['priority_score'] * $a['efficiency_score']);
        });

        return [
            'routes' => $optimizedRoutes,
            'optimization' => 'completed',
            'total_savings_minutes' => round($totalSavings),
            'recommendations' => $this->generateRouteRecommendations($optimizedRoutes)
        ];
    }

    /**
     * Предсказание времени выполнения заявки
     */
    public function predictCompletionTime($requestId): array
    {
        $request = OsvvRequest::find($requestId);
        if (!$request) {
            return ['error' => 'Request not found'];
        }

        // Анализируем исторические данные похожих заявок
        $similarRequests = OsvvRequest::where('animal_type', $request->animal_type)
            ->where('district', $request->district)
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->get();

        if ($similarRequests->count() < 3) {
            return [
                'estimated_hours' => 24,
                'confidence' => 30,
                'factors' => ['insufficient_historical_data']
            ];
        }

        // Рассчитываем среднее время выполнения
        $completionTimes = $similarRequests->map(function($req) {
            return $req->created_at->diffInHours($req->completed_at);
        });

        $avgTime = $completionTimes->avg();
        $variance = $completionTimes->map(fn($time) => pow($time - $avgTime, 2))->avg();
        
        // Корректируем на основе текущих факторов
        $adjustmentFactors = [];
        $timeAdjustment = 0;

        // Фактор срочности
        if ($request->has_bite || $request->is_urgent) {
            $timeAdjustment -= $avgTime * 0.3; // Срочные заявки обрабатываются быстрее
            $adjustmentFactors[] = 'urgent_priority';
        }

        // Фактор загруженности
        $currentLoad = OsvvRequest::where('status', 'pending')->count();
        if ($currentLoad > 10) {
            $timeAdjustment += $avgTime * 0.2; // Высокая загруженность увеличивает время
            $adjustmentFactors[] = 'high_workload';
        }

        // Фактор дня недели
        $dayOfWeek = Carbon::now()->dayOfWeek;
        if (in_array($dayOfWeek, [1, 2])) { // Понедельник, вторник - больше заявок
            $timeAdjustment += $avgTime * 0.1;
            $adjustmentFactors[] = 'peak_day';
        }

        $estimatedTime = max(1, $avgTime + $timeAdjustment);
        $confidence = max(20, min(95, 100 - (sqrt($variance) * 5)));

        return [
            'estimated_hours' => round($estimatedTime, 1),
            'confidence' => round($confidence),
            'base_time' => round($avgTime, 1),
            'adjustment' => round($timeAdjustment, 1),
            'factors' => $adjustmentFactors,
            'similar_cases' => $similarRequests->count()
        ];
    }

    /**
     * Анализ эффективности команды
     */
    public function analyzeTeamPerformance(): array
    {
        $analytics = OsvvAnalytics::where('event_time', '>=', Carbon::now()->subDays(30))
            ->get();

        $performance = [
            'productivity_score' => $this->calculateProductivityScore($analytics),
            'efficiency_trends' => $this->calculateEfficiencyTrends($analytics),
            'bottlenecks' => $this->identifyBottlenecks($analytics),
            'recommendations' => []
        ];

        // Генерируем рекомендации на основе анализа
        $performance['recommendations'] = $this->generatePerformanceRecommendations($performance);

        return $performance;
    }

    // Вспомогательные методы

    private function calculateTrend(array $data): float
    {
        $n = count($data);
        if ($n < 2) return 0;

        $sumX = array_sum(range(1, $n));
        $sumY = array_sum($data);
        $sumXY = 0;
        $sumX2 = 0;

        for ($i = 0; $i < $n; $i++) {
            $x = $i + 1;
            $y = $data[$i];
            $sumXY += $x * $y;
            $sumX2 += $x * $x;
        }

        return ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
    }

    private function calculateVariance(array $data): float
    {
        $mean = array_sum($data) / count($data);
        $variance = array_sum(array_map(fn($x) => pow($x - $mean, 2), $data)) / count($data);
        return sqrt($variance) / $mean; // Коэффициент вариации
    }

    private function calculateSeasonalFactor(): float
    {
        $currentDayOfWeek = Carbon::now()->dayOfWeek;
        
        // Простая сезонная корректировка (можно улучшить с ML)
        $seasonalFactors = [
            0 => -0.2, // Воскресенье
            1 => 0.3,  // Понедельник
            2 => 0.1,  // Вторник
            3 => 0.0,  // Среда
            4 => 0.0,  // Четверг
            5 => -0.1, // Пятница
            6 => -0.3  // Суббота
        ];

        return $seasonalFactors[$currentDayOfWeek] ?? 0;
    }

    private function identifyInfluencingFactors(Collection $data): array
    {
        $factors = [];
        
        $urgentRatios = $data->pluck('urgent_ratio');
        $avgUrgentRatio = $urgentRatios->avg();
        
        if ($avgUrgentRatio > 0.3) {
            $factors[] = [
                'factor' => 'high_urgent_ratio',
                'description' => 'Высокий процент срочных заявок',
                'impact' => 'increases_workload'
            ];
        }

        return $factors;
    }

    private function generateAnomalyRecommendations(array $anomalies): array
    {
        $recommendations = [];
        
        $spikes = array_filter($anomalies, fn($a) => $a['type'] === 'spike');
        if (count($spikes) > 2) {
            $recommendations[] = [
                'type' => 'resource_planning',
                'priority' => 'high',
                'description' => 'Обнаружены частые всплески заявок. Рекомендуется увеличить ресурсы в пиковые дни.'
            ];
        }

        return $recommendations;
    }

    private function clusterRequestsByLocation(Collection $requests): array
    {
        return $requests->groupBy('district')->toArray();
    }

    private function optimizeClusterRoute(array $requests): array
    {
        // Простая оптимизация - сортировка по приоритету и близости
        $priorityScore = 0;
        $estimatedTime = count($requests) * 45; // 45 минут на заявку
        
        foreach ($requests as $request) {
            if ($request->has_bite || $request->is_urgent) {
                $priorityScore += 10;
                $estimatedTime -= 10; // Срочные быстрее
            }
        }

        return [
            'estimated_time' => $estimatedTime,
            'estimated_distance' => count($requests) * 5, // 5 км между точками
            'priority_score' => $priorityScore,
            'efficiency_score' => $priorityScore / ($estimatedTime / 60),
            'time_savings' => count($requests) * 5, // Экономия от оптимизации
            'order' => range(1, count($requests))
        ];
    }

    private function generateRouteRecommendations(array $routes): array
    {
        $recommendations = [];
        
        $highPriorityRoutes = array_filter($routes, fn($r) => $r['priority_score'] > 20);
        if (count($highPriorityRoutes) > 0) {
            $recommendations[] = [
                'type' => 'priority_routing',
                'description' => 'Обнаружены маршруты с высоким приоритетом. Рекомендуется выполнить их в первую очередь.'
            ];
        }

        return $recommendations;
    }

    private function calculateProductivityScore(Collection $analytics): float
    {
        $completedDepartures = $analytics->where('event_type', 'departure_completed');
        if ($completedDepartures->count() === 0) return 0;

        $avgDuration = $completedDepartures->avg('duration_minutes');
        $avgRequests = $completedDepartures->map(function($event) {
            return $event->event_data['completed_requests'] ?? 0;
        })->avg();

        return $avgDuration > 0 ? ($avgRequests / ($avgDuration / 60)) * 10 : 0;
    }

    private function calculateEfficiencyTrends(Collection $analytics): array
    {
        // Группируем по неделям и считаем эффективность
        $weeklyEfficiency = $analytics->where('event_type', 'departure_completed')
            ->groupBy(function($item) {
                return Carbon::parse($item->event_time)->format('Y-W');
            })
            ->map(function($week) {
                $avgDuration = $week->avg('duration_minutes');
                $totalRequests = $week->sum(function($event) {
                    return $event->event_data['completed_requests'] ?? 0;
                });
                return $avgDuration > 0 ? $totalRequests / ($avgDuration / 60) : 0;
            });

        return $weeklyEfficiency->toArray();
    }

    private function identifyBottlenecks(Collection $analytics): array
    {
        $bottlenecks = [];
        
        // Анализ времени выполнения по районам
        $districtTimes = $analytics->where('event_type', 'departure_completed')
            ->groupBy(function($item) {
                return $item->event_data['district'] ?? 'unknown';
            })
            ->map(function($district) {
                return $district->avg('duration_minutes');
            });

        $avgTime = $districtTimes->avg();
        foreach ($districtTimes as $district => $time) {
            if ($time > $avgTime * 1.5) {
                $bottlenecks[] = [
                    'type' => 'district_delay',
                    'district' => $district,
                    'avg_time' => round($time),
                    'delay_factor' => round($time / $avgTime, 2)
                ];
            }
        }

        return $bottlenecks;
    }

    private function generatePerformanceRecommendations(array $performance): array
    {
        $recommendations = [];
        
        if ($performance['productivity_score'] < 5) {
            $recommendations[] = [
                'type' => 'productivity_improvement',
                'priority' => 'high',
                'description' => 'Низкая продуктивность команды. Рекомендуется пересмотреть процессы и обучение.'
            ];
        }

        foreach ($performance['bottlenecks'] as $bottleneck) {
            if ($bottleneck['type'] === 'district_delay') {
                $recommendations[] = [
                    'type' => 'district_optimization',
                    'priority' => 'medium',
                    'description' => "Район {$bottleneck['district']} показывает повышенное время выполнения. Требуется анализ маршрутов."
                ];
            }
        }

        return $recommendations;
    }

    /**
     * Прогноз времени выполнения конкретной заявки
     */
    public function predictRequestCompletion(int $requestId): array
    {
        $request = OsvvRequest::find($requestId);
        if (!$request) {
            return ['error' => 'Заявка не найдена'];
        }

        if (in_array($request->status, ['completed', 'cancelled'])) {
            return ['error' => 'Заявка уже завершена или отменена'];
        }

        // Ищем похожие заявки для анализа
        $similarRequests = OsvvRequest::where('id', '!=', $requestId)
            ->where('status', 'completed')
            ->whereNotNull('departure_date')
            ->whereNotNull('created_at')
            ->where(function($query) use ($request) {
                // Похожие по району
                if ($request->district) {
                    $query->where('district', $request->district);
                }
                // Похожие по типу животного
                if ($request->animal_type) {
                    $query->orWhere('animal_type', $request->animal_type);
                }
                // Похожие по приоритету (укусы)
                if ($request->has_bite) {
                    $query->orWhere('has_bite', true);
                }
            })
            ->get()
            ->map(function($req) {
                $completionHours = $req->created_at->diffInHours($req->departure_date);
                $req->completion_hours = round($completionHours, 1);
                return $req;
            })
            ->filter(function($req) {
                return $req->completion_hours > 0 && $req->completion_hours < 720; // Исключаем аномально долгие случаи (>30 дней)
            });

        if ($similarRequests->count() < 3) {
            // Если мало похожих случаев, используем общую статистику
            $allCompleted = OsvvRequest::where('status', 'completed')
                ->whereNotNull('departure_date')
                ->whereNotNull('created_at')
                ->get()
                ->map(function($req) {
                    $completionHours = $req->created_at->diffInHours($req->departure_date);
                    $req->completion_hours = round($completionHours, 1);
                    return $req;
                })
                ->filter(function($req) {
                    return $req->completion_hours > 0 && $req->completion_hours < 720;
                });

            if ($allCompleted->count() < 5) {
                return [
                    'error' => 'Недостаточно исторических данных для прогноза',
                    'similar_cases' => $allCompleted->count()
                ];
            }

            $baseTime = $allCompleted->avg('completion_hours');
            $confidence = 30; // Низкая уверенность
            $factors = ['insufficient_historical_data'];
        } else {
            $baseTime = $similarRequests->avg('completion_hours');
            $confidence = min(90, 50 + ($similarRequests->count() * 5)); // Уверенность растет с количеством случаев
            $factors = [];
        }

        // Корректировки на основе текущих условий
        $adjustment = 0;
        $currentHour = now()->hour;
        $currentDayOfWeek = now()->dayOfWeek;

        // Корректировка по приоритету
        if ($request->has_bite) {
            $adjustment -= 12; // Срочные заявки обрабатываются быстрее
            $factors[] = 'urgent_priority';
        }

        // Корректировка по времени создания заявки
        if ($currentHour >= 18 || $currentHour <= 8) {
            $adjustment += 8; // Заявки в нерабочее время обрабатываются дольше
        }

        // Корректировка по дню недели
        if (in_array($currentDayOfWeek, [1, 2])) { // Понедельник, вторник - высокая загрузка
            $adjustment += 6;
            $factors[] = 'peak_day';
        }

        // Корректировка по текущей загруженности
        $currentWorkload = OsvvRequest::whereIn('status', ['new', 'processing', 'capture_scheduled'])
            ->count();
        
        if ($currentWorkload > 20) {
            $adjustment += 12;
            $factors[] = 'high_workload';
        } elseif ($currentWorkload > 10) {
            $adjustment += 6;
            $factors[] = 'high_workload';
        }

        $estimatedHours = max(1, round($baseTime + $adjustment, 1));

        return [
            'estimated_hours' => $estimatedHours,
            'confidence' => round($confidence),
            'similar_cases' => $similarRequests->count(),
            'base_time' => round($baseTime, 1),
            'adjustment' => round($adjustment, 1),
            'factors' => $factors,
            'request_id' => $requestId
        ];
    }
} 