@extends('admin.layout')

@section('header')
    Выполнение маршрута: {{ $route->name }}
@endsection

@section('content')
    <div class="space-y-6">
        <!-- Информация о маршруте -->
        <div class="bg-white shadow rounded-lg">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-bold text-white">{{ $route->name }}</h3>
                    <div class="flex items-center space-x-3">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-white/20 text-white">
                            {{ $route->completion_status_name }}
                        </span>
                        @if($route->completion_percentage > 0)
                            <span class="text-white text-sm">{{ $route->completion_percentage }}%</span>
                        @endif
                    </div>
                </div>
                
                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 text-white">
                    <div>
                        <span class="text-white/70 text-sm">План:</span>
                        <div class="font-medium">{{ $route->departurePlan->name }}</div>
                    </div>
                    <div>
                        <span class="text-white/70 text-sm">Дата:</span>
                        <div class="font-medium">
                            {{ $route->planned_date ? $route->planned_date->format('d.m.Y') : $route->departurePlan->planned_date->format('d.m.Y') }}
                        </div>
                    </div>
                    <div>
                        <span class="text-white/70 text-sm">Время:</span>
                        <div class="font-medium">{{ $route->start_time ? $route->start_time->format('H:i') : 'Не указано' }}</div>
                    </div>
                </div>
            </div>
            
            <!-- Навигация -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex space-x-3">
                        <a href="{{ route('admin.departure-planner.view', $route->departurePlan) }}" 
                           class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-gray-700">
                            ← Вернуться к плану
                        </a>
                    </div>
                    
                    <div class="flex items-center space-x-4 text-sm text-gray-600">
                        @if($route->assignedUser)
                            <div>Отловщик: <span class="font-medium">{{ $route->assignedUser->name }}</span></div>
                        @endif
                        @if($route->driverUser)
                            <div>Водитель: <span class="font-medium">{{ $route->driverUser->name }}</span></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Статистика выполнения -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <span class="text-blue-600 font-medium text-sm">📋</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">Всего заявок</div>
                        <div class="text-2xl font-bold text-blue-600" id="total-requests">{{ $route->routeRequests->count() }}</div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <span class="text-green-600 font-medium text-sm">✅</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">Выполнено</div>
                        <div class="text-2xl font-bold text-green-600" id="completed-requests">{{ $route->routeRequests->whereIn('execution_status', ['completed', 'visited'])->count() }}</div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                            <span class="text-red-600 font-medium text-sm">❌</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">Не выполнено</div>
                        <div class="text-2xl font-bold text-red-600" id="failed-requests">{{ $route->routeRequests->where('execution_status', 'failed')->count() }}</div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                            <span class="text-yellow-600 font-medium text-sm">🐕</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">Отловлено</div>
                        <div class="text-2xl font-bold text-yellow-600" id="animals-captured">{{ $route->routeRequests->sum('animals_captured') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Список заявок -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Заявки в маршруте ({{ $route->routeRequests->count() }})</h3>
            </div>
            
            <div class="divide-y divide-gray-200" id="requests-list">
                @foreach($route->routeRequests->sortBy('sequence_order') as $routeRequest)
                    @php $request = $routeRequest->osvvRequest @endphp
                    <div class="p-6 hover:bg-gray-50" data-request-id="{{ $routeRequest->id }}">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-full 
                                            @if($routeRequest->execution_status === 'pending') bg-gray-100 text-gray-600
                                            @elseif($routeRequest->execution_status === 'visited') bg-blue-100 text-blue-600
                                            @elseif($routeRequest->execution_status === 'completed') bg-green-100 text-green-600
                                            @elseif($routeRequest->execution_status === 'failed') bg-red-100 text-red-600
                                            @elseif($routeRequest->execution_status === 'no_animals_found') bg-yellow-100 text-yellow-600
                                            @else bg-gray-100 text-gray-600 @endif">
                                            {{ $loop->iteration }}
                                        </span>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-lg font-medium text-gray-900">ОСВВ #{{ $request->id }}</h4>
                                        <p class="text-sm text-gray-600">{{ $request->applicant_name ?? 'Заявитель не указан' }}</p>
                                        <p class="text-sm text-gray-500 mt-1">{{ $request->location_address }}</p>
                                    </div>
                                </div>
                                
                                @if($routeRequest->execution_status !== 'pending')
                                    <div class="mt-3 ml-11">
                                        <div class="text-sm space-y-1">
                                            <div>
                                                <span class="font-medium">Статус:</span>
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                                    @if($routeRequest->execution_status === 'visited') bg-blue-100 text-blue-800
                                                    @elseif($routeRequest->execution_status === 'completed') bg-green-100 text-green-800
                                                    @elseif($routeRequest->execution_status === 'failed') bg-red-100 text-red-800
                                                    @elseif($routeRequest->execution_status === 'no_animals_found') bg-yellow-100 text-yellow-800
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    {{ $routeRequest->execution_status_name }}
                                                </span>
                                            </div>
                                            
                                            @if($routeRequest->animals_captured > 0)
                                                <div><span class="font-medium">Отловлено животных:</span> {{ $routeRequest->animals_captured }}</div>
                                            @endif
                                            
                                            @if($routeRequest->executed_at)
                                                <div><span class="font-medium">Время выполнения:</span> {{ $routeRequest->executed_at->format('d.m.Y H:i') }}</div>
                                            @endif
                                            
                                            @if($routeRequest->execution_notes)
                                                <div><span class="font-medium">Заметки:</span> {{ $routeRequest->execution_notes }}</div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                @if($routeRequest->execution_status === 'pending' && $route->completion_status === 'in_progress')
                                    <button onclick="markRequestExecution({{ $routeRequest->id }})" 
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                        ✅ Отметить выполнение
                                    </button>
                                @elseif($routeRequest->execution_status !== 'pending')
                                    <button onclick="editRequestExecution({{ $routeRequest->id }})" 
                                            class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        ✏️ Редактировать
                                    </button>
                                @endif
                                
                                <div class="text-sm text-gray-500">
                                    ~{{ $routeRequest->estimated_time ?? 60 }} мин
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Модальное окно для отметки выполнения заявки -->
    <div id="executionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Отметка выполнения заявки</h3>
                </div>
                
                <form id="executionForm" class="px-6 py-4">
                    <div class="space-y-4">
                        <div>
                            <label for="execution_status" class="block text-sm font-medium text-gray-700">Статус выполнения</label>
                            <select name="execution_status" id="execution_status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                <option value="visited">Посещено</option>
                                <option value="completed">Выполнено</option>
                                <option value="failed">Не выполнено</option>
                                <option value="no_animals_found">Животные не найдены</option>
                                <option value="cancelled">Отменено</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="execution_result" class="block text-sm font-medium text-gray-700">Результат</label>
                            <select name="execution_result" id="execution_result" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                <option value="">Выберите результат</option>
                                <option value="success">Успешно</option>
                                <option value="partial_success">Частично выполнено</option>
                                <option value="no_result">Без результата</option>
                                <option value="failed">Не удалось</option>
                                <option value="cancelled">Отменено</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="animals_captured" class="block text-sm font-medium text-gray-700">Количество отловленных животных</label>
                            <input type="number" name="animals_captured" id="animals_captured" min="0" value="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                        </div>
                        
                        <div>
                            <label for="execution_notes" class="block text-sm font-medium text-gray-700">Заметки</label>
                            <textarea name="execution_notes" id="execution_notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" placeholder="Дополнительная информация о выполнении..."></textarea>
                        </div>
                    </div>
                </form>
                
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" onclick="closeExecutionModal()" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Отмена
                    </button>
                    <button type="button" onclick="submitExecution()" class="px-4 py-2 bg-green-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-green-700">
                        Сохранить
                    </button>
                </div>
            </div>
        </div>
    </div>


@endsection

@push('scripts')
<script>
let currentRequestId = null;



function markRequestExecution(requestId) {
    currentRequestId = requestId;
    document.getElementById('executionModal').classList.remove('hidden');
}

function editRequestExecution(requestId) {
    // Для редактирования можно загрузить существующие данные
    currentRequestId = requestId;
    document.getElementById('executionModal').classList.remove('hidden');
}

function closeExecutionModal() {
    currentRequestId = null;
    document.getElementById('executionModal').classList.add('hidden');
    document.getElementById('executionForm').reset();
}

function submitExecution() {
    if (!currentRequestId) return;
    
    const form = document.getElementById('executionForm');
    const formData = new FormData(form);
    
    const data = {
        execution_status: formData.get('execution_status'),
        execution_result: formData.get('execution_result'),
        execution_notes: formData.get('execution_notes'),
        animals_captured: parseInt(formData.get('animals_captured')) || 0
    };
    
    fetch(`/admin/route-execution/request/${currentRequestId}/mark`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeExecutionModal();
            location.reload();
        } else {
            alert('Ошибка: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        alert('Произошла ошибка при отметке выполнения');
    });
}

// Обновление статистики каждые 30 секунд
setInterval(function() {
    fetch(`/admin/route-execution/route/{{ $route->id }}/stats`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('total-requests').textContent = data.total_requests;
            document.getElementById('completed-requests').textContent = data.completed_requests;
            document.getElementById('failed-requests').textContent = data.failed_requests;
            document.getElementById('animals-captured').textContent = data.total_animals_captured;
        })
        .catch(error => console.error('Ошибка при обновлении статистики:', error));
}, 30000);
</script>
@endpush 