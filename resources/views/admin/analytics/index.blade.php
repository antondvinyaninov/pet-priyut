@extends('admin.layout')

@section('header', '🤖 AI-Аналитика эффективности')

@section('content')
<div class="space-y-6">
    <!-- Фильтры по датам и AI-кнопки -->
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex flex-wrap gap-4 items-end justify-between">
            <form method="GET" class="flex flex-wrap gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Дата от</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" 
                           class="border border-gray-300 rounded-md px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Дата до</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" 
                           class="border border-gray-300 rounded-md px-3 py-2">
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Обновить
                </button>
                <a href="{{ route('admin.analytics.export') }}?date_from={{ $dateFrom }}&date_to={{ $dateTo }}" 
                   class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    📊 Экспорт CSV
                </a>
            </form>
            
            <!-- AI-кнопки -->
            <div class="flex gap-2">
                <button onclick="loadAIPredictions()" 
                        class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors">
                    🧠 AI-Прогноз
                </button>
                <button onclick="loadAIAnomalies()" 
                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                    🔍 Детекция аномалий
                </button>
                <button onclick="loadAIOptimization()" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors">
                    🚀 Оптимизация
                </button>
            </div>
        </div>
    </div>

    <!-- AI-блоки (скрыты по умолчанию) -->
    <div id="ai-predictions" class="hidden bg-gradient-to-r from-purple-50 to-blue-50 shadow rounded-lg p-6 border-l-4 border-purple-500">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            🧠 AI-Прогнозы и предсказания
            <span class="ml-2 text-sm text-gray-500">(загружается...)</span>
        </h3>
        <div id="ai-predictions-content" class="space-y-4">
            <div class="animate-pulse">
                <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                <div class="h-4 bg-gray-200 rounded w-1/2"></div>
            </div>
        </div>
    </div>

    <div id="ai-anomalies" class="hidden bg-gradient-to-r from-red-50 to-orange-50 shadow rounded-lg p-6 border-l-4 border-red-500">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            🔍 Детекция аномалий
            <span class="ml-2 text-sm text-gray-500">(загружается...)</span>
        </h3>
        <div id="ai-anomalies-content" class="space-y-4">
            <div class="animate-pulse">
                <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                <div class="h-4 bg-gray-200 rounded w-1/2"></div>
            </div>
        </div>
    </div>

    <div id="ai-optimization" class="hidden bg-gradient-to-r from-indigo-50 to-cyan-50 shadow rounded-lg p-6 border-l-4 border-indigo-500">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            🚀 AI-Оптимизация маршрутов
            <span class="ml-2 text-sm text-gray-500">(загружается...)</span>
        </h3>
        <div id="ai-optimization-content" class="space-y-4">
            <div class="animate-pulse">
                <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                <div class="h-4 bg-gray-200 rounded w-1/2"></div>
            </div>
        </div>
    </div>

    <!-- Основные метрики -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Всего заявок</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $totalRequests }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Выполнено</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $completedRequests }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Процент выполнения</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $completionRate }}%</p>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Среднее время</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        {{ $efficiency['avg_processing_time'] ? round($efficiency['avg_processing_time'] / 60, 1) : 0 }}ч
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Рекомендации по улучшению -->
    @if(!empty($suggestions))
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
            </svg>
            🚀 Рекомендации по улучшению эффективности
        </h3>
        
        <div class="space-y-4">
            @foreach($suggestions as $suggestion)
            <div class="border-l-4 p-4 rounded-r-lg
                @if($suggestion['priority'] === 'critical') border-red-500 bg-red-50
                @elseif($suggestion['priority'] === 'high') border-orange-500 bg-orange-50
                @else border-yellow-500 bg-yellow-50
                @endif">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        @if($suggestion['priority'] === 'critical')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                🚨 Критично
                            </span>
                        @elseif($suggestion['priority'] === 'high')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                ⚠️ Высокий
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                💡 Средний
                            </span>
                        @endif
                    </div>
                    <div class="ml-3 flex-1">
                        <h4 class="text-sm font-medium text-gray-900">{{ $suggestion['title'] }}</h4>
                        <p class="mt-1 text-sm text-gray-600">{{ $suggestion['description'] }}</p>
                        <p class="mt-2 text-sm font-medium text-gray-800">
                            <strong>Действие:</strong> {{ $suggestion['action'] }}
                        </p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Статистика по районам и эффективность маршрутов -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Эффективность по районам -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">📍 Эффективность по районам</h3>
            
            @if(!empty($routeStats))
            <div class="space-y-3">
                @foreach($routeStats as $district => $stats)
                <div class="border rounded-lg p-4">
                    <div class="flex justify-between items-start mb-2">
                        <h4 class="font-medium text-gray-900">{{ $district }}</h4>
                        <span class="px-2 py-1 text-xs rounded-full
                            @if($stats['efficiency_score'] >= 2) bg-green-100 text-green-800
                            @elseif($stats['efficiency_score'] >= 1) bg-yellow-100 text-yellow-800
                            @else bg-red-100 text-red-800
                            @endif">
                            Эффективность: {{ $stats['efficiency_score'] }}
                        </span>
                    </div>
                    <div class="grid grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600">Выездов:</span>
                            <span class="font-medium">{{ $stats['count'] }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Среднее время:</span>
                            <span class="font-medium">{{ round($stats['avg_time'] / 60, 1) }}ч</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Заявок/выезд:</span>
                            <span class="font-medium">{{ $stats['avg_requests'] }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-gray-500 text-center py-8">Нет данных о завершенных выездах за выбранный период</p>
            @endif
        </div>

        <!-- Статистика по приоритетам -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">⚡ Обработка по приоритетам</h3>
            
            <div class="space-y-4">
                @foreach($priorityStats as $priority => $stats)
                <div class="border rounded-lg p-4">
                    <div class="flex justify-between items-center mb-2">
                        <h4 class="font-medium text-gray-900">
                            @if($priority === 'urgent') 🚨 Срочные
                            @elseif($priority === 'normal') 📋 Обычные
                            @else 📝 Низкий приоритет
                            @endif
                        </h4>
                        <span class="text-sm text-gray-600">{{ $stats['count'] }} заявок</span>
                    </div>
                    <div class="text-sm">
                        <span class="text-gray-600">Среднее время обработки:</span>
                        <span class="font-medium">{{ round($stats['avg_response_time'] / 60, 1) }} часов</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Статистика по типам животных и районам -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- По типам животных -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">🐕 Статистика по животным</h3>
            
            <div class="space-y-3">
                @foreach($animalStats as $animal)
                <div class="flex justify-between items-center p-3 border rounded-lg">
                    <div>
                        <span class="font-medium">{{ $animal->animal_type ?: 'Не указано' }}</span>
                        <span class="text-sm text-gray-600 ml-2">({{ $animal->count }} заявок)</span>
                    </div>
                    <div class="text-right">
                        <span class="text-sm font-medium">{{ round($animal->completion_rate, 1) }}%</span>
                        <div class="w-20 bg-gray-200 rounded-full h-2 mt-1">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $animal->completion_rate }}%"></div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- По районам -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">🏘️ Статистика по районам</h3>
            
            <div class="space-y-3">
                @foreach($districtStats as $district)
                <div class="flex justify-between items-center p-3 border rounded-lg">
                    <div>
                        <span class="font-medium">{{ $district->district ?: 'Не указан' }}</span>
                        <span class="text-sm text-gray-600 ml-2">({{ $district->total }} заявок)</span>
                    </div>
                    <div class="text-right">
                        <span class="text-sm font-medium">{{ $district->completion_rate }}%</span>
                        <div class="w-20 bg-gray-200 rounded-full h-2 mt-1">
                            <div class="bg-green-600 h-2 rounded-full" style="width: {{ $district->completion_rate }}%"></div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Статистика по укусам -->
    <div class="bg-white shadow rounded-lg p-6 border-l-4 border-red-500">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            🚨 Статистика по укусам
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-red-50 rounded-lg p-4">
                <div class="text-sm text-red-600 font-medium mb-1">Всего случаев</div>
                <div class="text-3xl font-bold text-red-700">{{ $biteStats['total_bites'] }}</div>
            </div>
            
            <div class="bg-green-50 rounded-lg p-4">
                <div class="text-sm text-green-600 font-medium mb-1">Решено</div>
                <div class="text-3xl font-bold text-green-700">{{ $biteStats['resolved_bites'] }}</div>
            </div>
            
            <div class="bg-yellow-50 rounded-lg p-4">
                <div class="text-sm text-yellow-600 font-medium mb-1">В работе</div>
                <div class="text-3xl font-bold text-yellow-700">{{ $biteStats['pending_bites'] }}</div>
            </div>
            
            <div class="bg-blue-50 rounded-lg p-4">
                <div class="text-sm text-blue-600 font-medium mb-1">Процент решения</div>
                <div class="text-3xl font-bold text-blue-700">{{ $biteStats['resolution_rate'] }}%</div>
            </div>
        </div>
        
        @if($biteStats['avg_response_time'])
        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
            <div class="text-sm text-gray-600 mb-1">Среднее время реакции на укус</div>
            <div class="text-2xl font-bold text-gray-900">
                {{ round($biteStats['avg_response_time'], 1) }} дней
            </div>
        </div>
        @endif
        
        @if($biteStats['bites_by_district']->isNotEmpty())
        <div>
            <h4 class="font-semibold text-gray-900 mb-3">Распределение по районам</h4>
            <div class="space-y-2">
                @foreach($biteStats['bites_by_district'] as $district)
                <div class="flex justify-between items-center p-3 border rounded-lg hover:bg-gray-50">
                    <span class="font-medium">{{ $district->district ?: 'Не указан' }}</span>
                    <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium">
                        {{ $district->count }} случаев
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- График трендов эффективности -->
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">📈 Тренды эффективности</h3>
        
        <div class="mb-4">
            <button onclick="loadEfficiencyTrends(7)" class="px-3 py-1 text-sm bg-blue-100 text-blue-800 rounded mr-2">7 дней</button>
            <button onclick="loadEfficiencyTrends(30)" class="px-3 py-1 text-sm bg-blue-100 text-blue-800 rounded mr-2">30 дней</button>
            <button onclick="loadEfficiencyTrends(90)" class="px-3 py-1 text-sm bg-blue-100 text-blue-800 rounded">90 дней</button>
        </div>
        
        <div id="trendsChart" class="h-64 flex items-center justify-center text-gray-500">
            Нажмите на кнопку выше для загрузки графика трендов
        </div>
    </div>

    <!-- Детальная аналитика маршрутов -->
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">🗺️ Анализ оптимизации маршрутов</h3>
        
        <button onclick="loadRouteOptimizationData()" class="mb-4 px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
            Загрузить анализ маршрутов
        </button>
        
        <div id="routeOptimizationData" class="space-y-4">
            <!-- Данные загружаются динамически -->
        </div>
    </div>
</div>

@push('scripts')
<script>
function loadEfficiencyTrends(days) {
    const chartContainer = document.getElementById('trendsChart');
    chartContainer.innerHTML = '<div class="flex items-center justify-center h-full"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div></div>';
    
    fetch(`/admin/analytics/efficiency-trends?days=${days}`)
        .then(response => response.json())
        .then(data => {
            if (data.length === 0) {
                chartContainer.innerHTML = '<div class="text-center text-gray-500">Нет данных за выбранный период</div>';
                return;
            }
            
            // Простая визуализация данных
            let html = '<div class="space-y-2">';
            data.forEach(item => {
                const efficiency = item.completed_departures > 0 ? (item.completed_departures / (item.avg_duration / 60)).toFixed(2) : 0;
                html += `
                    <div class="flex justify-between items-center p-2 border rounded">
                        <span class="text-sm font-medium">${item.date}</span>
                        <div class="flex gap-4 text-sm">
                            <span>События: ${item.total_events}</span>
                            <span>Выезды: ${item.completed_departures}</span>
                            <span>Эффективность: ${efficiency}</span>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            
            chartContainer.innerHTML = html;
        })
        .catch(error => {
            console.error('Ошибка загрузки трендов:', error);
            chartContainer.innerHTML = '<div class="text-center text-red-500">Ошибка загрузки данных</div>';
        });
}

function loadRouteOptimizationData() {
    const container = document.getElementById('routeOptimizationData');
    container.innerHTML = '<div class="flex items-center justify-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div></div>';
    
    const dateFrom = '{{ $dateFrom }}';
    const dateTo = '{{ $dateTo }}';
    
    fetch(`/admin/analytics/route-optimization-data?date_from=${dateFrom}&date_to=${dateTo}`)
        .then(response => response.json())
        .then(data => {
            if (data.length === 0) {
                container.innerHTML = '<div class="text-center text-gray-500 py-8">Нет данных о завершенных маршрутах за выбранный период</div>';
                return;
            }
            
            let html = '';
            data.forEach(district => {
                const efficiencyColor = district.efficiency_score >= 2 ? 'green' : 
                                      district.efficiency_score >= 1 ? 'yellow' : 'red';
                
                html += `
                    <div class="border rounded-lg p-4">
                        <div class="flex justify-between items-start mb-3">
                            <h4 class="font-semibold text-gray-900">${district.district}</h4>
                            <span class="px-2 py-1 text-xs rounded-full bg-${efficiencyColor}-100 text-${efficiencyColor}-800">
                                ${district.improvement_potential}
                            </span>
                        </div>
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">Выездов:</span>
                                <span class="font-medium block">${district.departures_count}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Среднее время:</span>
                                <span class="font-medium block">${(district.avg_time_per_departure / 60).toFixed(1)}ч</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Заявок/выезд:</span>
                                <span class="font-medium block">${district.avg_requests_per_departure}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Эффективность:</span>
                                <span class="font-medium block">${district.efficiency_score}</span>
                            </div>
                        </div>
                        
                        <div class="mt-3 text-sm">
                            <span class="text-gray-600">Общее расстояние:</span>
                            <span class="font-medium">${district.total_distance.toFixed(1)} км</span>
                            <span class="text-gray-600 ml-4">Общее время:</span>
                            <span class="font-medium">${(district.total_time / 60).toFixed(1)} часов</span>
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
        })
        .catch(error => {
            console.error('Ошибка загрузки данных маршрутов:', error);
            container.innerHTML = '<div class="text-center text-red-500 py-8">Ошибка загрузки данных</div>';
        });
}

// AI-функции
function loadAIPredictions() {
    const container = document.getElementById('ai-predictions');
    const content = document.getElementById('ai-predictions-content');
    
    container.classList.remove('hidden');
    content.innerHTML = '<div class="animate-pulse"><div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div><div class="h-4 bg-gray-200 rounded w-1/2"></div></div>';
    
    fetch('/admin/analytics/ai-predictions')
        .then(response => response.json())
        .then(data => {
            let html = '';
            
            // Прогнозы
            if (data.predictions) {
                const pred = data.predictions;
                const trendIcon = pred.trend === 'increasing' ? '📈' : pred.trend === 'decreasing' ? '📉' : '➡️';
                const confidenceColor = pred.confidence > 70 ? 'green' : pred.confidence > 40 ? 'yellow' : 'red';
                
                html += `
                    <div class="bg-white rounded-lg p-4 border">
                        <h4 class="font-semibold text-gray-900 mb-2">📊 Прогноз заявок на следующую неделю</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">Прогноз:</span>
                                <span class="font-bold text-lg block text-purple-600">${pred.prediction} заявок</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Уверенность:</span>
                                <span class="font-medium block">
                                    <span class="px-2 py-1 rounded-full text-xs bg-${confidenceColor}-100 text-${confidenceColor}-800">
                                        ${pred.confidence}%
                                    </span>
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-600">Тренд:</span>
                                <span class="font-medium block">${trendIcon} ${pred.trend_value > 0 ? '+' : ''}${pred.trend_value}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Сезонность:</span>
                                <span class="font-medium block">${pred.seasonal_factor > 0 ? '+' : ''}${pred.seasonal_factor}</span>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            // Производительность команды
            if (data.team_performance) {
                const perf = data.team_performance;
                const scoreColor = perf.productivity_score > 8 ? 'green' : perf.productivity_score > 5 ? 'yellow' : 'red';
                
                html += `
                    <div class="bg-white rounded-lg p-4 border">
                        <h4 class="font-semibold text-gray-900 mb-2">👥 Анализ производительности команды</h4>
                        <div class="mb-3">
                            <span class="text-gray-600">Оценка продуктивности:</span>
                            <span class="font-bold text-lg ml-2">
                                <span class="px-3 py-1 rounded-full bg-${scoreColor}-100 text-${scoreColor}-800">
                                    ${perf.productivity_score.toFixed(1)}/10
                                </span>
                            </span>
                        </div>
                        
                        ${perf.bottlenecks && perf.bottlenecks.length > 0 ? `
                            <div class="mt-3">
                                <h5 class="font-medium text-gray-900 mb-2">🚧 Узкие места:</h5>
                                <div class="space-y-2">
                                    ${perf.bottlenecks.map(bottleneck => `
                                        <div class="text-sm bg-orange-50 p-2 rounded border-l-2 border-orange-400">
                                            <strong>${bottleneck.district}:</strong> 
                                            задержка в ${bottleneck.delay_factor}x раз (${bottleneck.avg_time} мин)
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                        ` : ''}
                        
                        ${perf.recommendations && perf.recommendations.length > 0 ? `
                            <div class="mt-3">
                                <h5 class="font-medium text-gray-900 mb-2">💡 AI-Рекомендации:</h5>
                                <div class="space-y-2">
                                    ${perf.recommendations.map(rec => `
                                        <div class="text-sm bg-blue-50 p-2 rounded border-l-2 border-blue-400">
                                            <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800">${rec.priority}</span>
                                            ${rec.description}
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                        ` : ''}
                    </div>
                `;
            }
            
            content.innerHTML = html || '<div class="text-gray-500">Нет данных для AI-анализа</div>';
        })
        .catch(error => {
            console.error('Ошибка AI-прогнозов:', error);
            content.innerHTML = '<div class="text-red-500">Ошибка загрузки AI-прогнозов</div>';
        });
}

function loadAIAnomalies() {
    const container = document.getElementById('ai-anomalies');
    const content = document.getElementById('ai-anomalies-content');
    
    container.classList.remove('hidden');
    content.innerHTML = '<div class="animate-pulse"><div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div><div class="h-4 bg-gray-200 rounded w-1/2"></div></div>';
    
    fetch('/admin/analytics/ai-anomaly-detection')
        .then(response => response.json())
        .then(data => {
            let html = '';
            
            if (data.anomalies && data.anomalies.length > 0) {
                html += `
                    <div class="bg-white rounded-lg p-4 border">
                        <h4 class="font-semibold text-gray-900 mb-3">
                            🚨 Обнаружено аномалий: ${data.total_anomalies} за ${data.analysis_period} дней
                        </h4>
                        <div class="space-y-3">
                `;
                
                data.anomalies.forEach(anomaly => {
                    const typeIcon = anomaly.type === 'spike' ? '📈' : '📉';
                    const severityColor = anomaly.severity === 'high' ? 'red' : 'orange';
                    
                    html += `
                        <div class="border rounded-lg p-3 bg-${severityColor}-50 border-${severityColor}-200">
                            <div class="flex justify-between items-start mb-2">
                                <span class="font-medium">${typeIcon} ${anomaly.date}</span>
                                <span class="px-2 py-1 text-xs rounded-full bg-${severityColor}-100 text-${severityColor}-800">
                                    ${anomaly.severity === 'high' ? 'Высокая' : 'Средняя'} важность
                                </span>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm">
                                <div>
                                    <span class="text-gray-600">Заявок:</span>
                                    <span class="font-medium">${anomaly.count}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Ожидалось:</span>
                                    <span class="font-medium">${anomaly.expected}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Отклонение:</span>
                                    <span class="font-medium">${anomaly.deviation}σ</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Срочных:</span>
                                    <span class="font-medium">${anomaly.urgent_ratio}%</span>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                html += '</div></div>';
                
                // Рекомендации
                if (data.recommendations && data.recommendations.length > 0) {
                    html += `
                        <div class="bg-white rounded-lg p-4 border mt-4">
                            <h4 class="font-semibold text-gray-900 mb-3">💡 AI-Рекомендации по аномалиям</h4>
                            <div class="space-y-2">
                                ${data.recommendations.map(rec => `
                                    <div class="text-sm bg-blue-50 p-3 rounded border-l-2 border-blue-400">
                                        <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800">${rec.priority}</span>
                                        <div class="mt-1">${rec.description}</div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `;
                }
            } else {
                html = '<div class="bg-white rounded-lg p-4 border text-center text-green-600">✅ Аномалий не обнаружено. Система работает стабильно.</div>';
            }
            
            content.innerHTML = html;
        })
        .catch(error => {
            console.error('Ошибка детекции аномалий:', error);
            content.innerHTML = '<div class="text-red-500">Ошибка загрузки детекции аномалий</div>';
        });
}

function loadAIOptimization() {
    const container = document.getElementById('ai-optimization');
    const content = document.getElementById('ai-optimization-content');
    
    container.classList.remove('hidden');
    content.innerHTML = '<div class="animate-pulse"><div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div><div class="h-4 bg-gray-200 rounded w-1/2"></div></div>';
    
    fetch('/admin/analytics/ai-route-optimization')
        .then(response => response.json())
        .then(data => {
            let html = '';
            
            if (data.routes && data.routes.length > 0) {
                html += `
                    <div class="bg-white rounded-lg p-4 border">
                        <h4 class="font-semibold text-gray-900 mb-3">
                            🚀 AI-Оптимизация маршрутов
                            <span class="text-sm text-green-600 ml-2">
                                Экономия: ${data.total_savings_minutes} минут
                            </span>
                        </h4>
                        <div class="space-y-3">
                `;
                
                data.routes.forEach((route, index) => {
                    const priorityColor = route.priority_score > 20 ? 'red' : route.priority_score > 10 ? 'orange' : 'green';
                    const efficiencyColor = route.efficiency_score > 2 ? 'green' : route.efficiency_score > 1 ? 'yellow' : 'red';
                    
                    html += `
                        <div class="border rounded-lg p-3">
                            <div class="flex justify-between items-start mb-2">
                                <span class="font-medium">${index + 1}. ${route.district}</span>
                                <div class="flex gap-2">
                                    <span class="px-2 py-1 text-xs rounded-full bg-${priorityColor}-100 text-${priorityColor}-800">
                                        Приоритет: ${route.priority_score}
                                    </span>
                                    <span class="px-2 py-1 text-xs rounded-full bg-${efficiencyColor}-100 text-${efficiencyColor}-800">
                                        Эффективность: ${route.efficiency_score.toFixed(1)}
                                    </span>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm">
                                <div>
                                    <span class="text-gray-600">Заявок:</span>
                                    <span class="font-medium">${route.requests_count}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Время:</span>
                                    <span class="font-medium">${Math.round(route.estimated_time / 60)} ч</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Расстояние:</span>
                                    <span class="font-medium">${route.estimated_distance} км</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Порядок:</span>
                                    <span class="font-medium">${route.route_order.length} точек</span>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                html += '</div></div>';
                
                // Рекомендации по оптимизации
                if (data.recommendations && data.recommendations.length > 0) {
                    html += `
                        <div class="bg-white rounded-lg p-4 border mt-4">
                            <h4 class="font-semibold text-gray-900 mb-3">🎯 Рекомендации по оптимизации</h4>
                            <div class="space-y-2">
                                ${data.recommendations.map(rec => `
                                    <div class="text-sm bg-indigo-50 p-3 rounded border-l-2 border-indigo-400">
                                        <strong>${rec.type}:</strong> ${rec.description}
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `;
                }
            } else {
                html = '<div class="bg-white rounded-lg p-4 border text-center text-gray-500">📋 Нет активных заявок для оптимизации маршрутов</div>';
            }
            
            content.innerHTML = html;
        })
        .catch(error => {
            console.error('Ошибка AI-оптимизации:', error);
            content.innerHTML = '<div class="text-red-500">Ошибка загрузки AI-оптимизации</div>';
        });
}
</script>
@endpush
@endsection 