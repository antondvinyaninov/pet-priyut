@extends('admin.layout')

@section('header')
    @if(request('edit') && $departurePlan->isEditable())
        Редактирование плана выезда
    @else
        Просмотр плана выезда
    @endif
@endsection

@section('content')
    <div class="space-y-6">
        <!-- Заголовок и действия -->
        <div class="bg-white shadow rounded-lg">
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        {{ $departurePlan->name }}
                    </h3>
                    <div class="flex space-x-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            @if($departurePlan->status === 'draft') bg-gray-100 text-gray-800
                            @elseif($departurePlan->status === 'approved') bg-blue-100 text-blue-800
                            @elseif($departurePlan->status === 'in_progress') bg-yellow-100 text-yellow-800
                            @elseif($departurePlan->status === 'completed') bg-green-100 text-green-800
                            @else bg-red-100 text-red-800 @endif">
                            {{ $departurePlan->status_name }}
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div class="flex space-x-4">
                        <a href="{{ route('admin.departure-planner.index') }}" class="text-gray-600 hover:text-gray-900">
                            ← Вернуться к списку
                        </a>
                    </div>
                    <div class="flex space-x-2">
                        @if(request('edit') && $departurePlan->isEditable())
                            <a href="{{ route('admin.departure-planner.view', $departurePlan) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                                Отменить
                            </a>
                        @elseif($departurePlan->isEditable())
                            <a href="{{ route('admin.departure-planner.view', ['departurePlan' => $departurePlan, 'edit' => 1]) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                Редактировать
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if(request('edit') && $departurePlan->isEditable())
            <!-- Режим редактирования -->
            <form method="POST" action="{{ route('admin.departure-planner.update', $departurePlan) }}">
                @csrf
                @method('PUT')
                
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Основная информация</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Название плана</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $departurePlan->name) }}" 
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Дата планирования</label>
                            <input type="text" value="{{ $departurePlan->planned_date->format('d.m.Y') }}" 
                                   class="block w-full rounded-md border-gray-300 bg-gray-50" readonly>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Заметки</label>
                        <textarea id="notes" name="notes" rows="3" 
                                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $departurePlan->notes) }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Маршруты</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        В режиме быстрого редактирования можно изменить только основную информацию. 
                        Для полного редактирования маршрутов используйте страницу создания.
                    </p>
                    
                    @foreach($departurePlan->routes as $route)
                        <div class="border rounded-lg p-4 mb-4">
                            <h4 class="font-medium text-gray-900 mb-2">{{ $route->name }}</h4>
                            <p class="text-sm text-gray-600">
                                Заявок: {{ $route->routeRequests ? $route->routeRequests->count() : 0 }}
                                | Животных: {{ $route->routeAnimals ? $route->routeAnimals->count() : 0 }}
                                @if($route->assignedUser)
                                    | Отловщик: {{ $route->assignedUser->name }}
                                @endif
                                @if($route->driverUser)
                                    | Водитель: {{ $route->driverUser->name }}
                                @endif
                                @if($route->start_time)
                                    | Время: {{ $route->start_time }}
                                @endif
                            </p>
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.departure-planner.view', $departurePlan) }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                        Отмена
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Сохранить изменения
                    </button>
                </div>
            </form>
        @else
            <!-- Режим просмотра -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Основная информация</h3>
                
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Название</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $departurePlan->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Дата планирования</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $departurePlan->planned_date->format('d.m.Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Создатель</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $departurePlan->creator->name ?? 'Неизвестно' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Статус</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($departurePlan->status === 'draft') bg-gray-100 text-gray-800
                                @elseif($departurePlan->status === 'approved') bg-blue-100 text-blue-800
                                @elseif($departurePlan->status === 'in_progress') bg-yellow-100 text-yellow-800
                                @elseif($departurePlan->status === 'completed') bg-green-100 text-green-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ $departurePlan->status_name }}
                            </span>
                        </dd>
                    </div>
                    @if($departurePlan->notes)
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Заметки</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $departurePlan->notes }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Статистика</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600">{{ $departurePlan->total_routes ?? 0 }}</div>
                        <div class="text-sm text-blue-600">Маршрутов</div>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-green-600">{{ $departurePlan->total_requests ?? 0 }}</div>
                        <div class="text-sm text-green-600">Заявок</div>
                    </div>
                    <div class="bg-emerald-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-emerald-600">
                            @php
                                $totalAnimals = $departurePlan->routes->sum(function($route) {
                                    return $route->routeAnimals ? $route->routeAnimals->count() : 0;
                                });
                            @endphp
                            {{ $totalAnimals }}
                        </div>
                        <div class="text-sm text-emerald-600">Животных к выпуску</div>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-purple-600">{{ $departurePlan->estimated_duration ?? 0 }} мин</div>
                        <div class="text-sm text-purple-600">Время выполнения</div>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Маршруты ({{ $departurePlan->routes->count() }})</h3>
                    <div class="flex space-x-2">
                        <button onclick="showAddRequestModal()" 
                                class="inline-flex items-center px-3 py-1 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                            ➕ Добавить заявку
                        </button>
                    </div>
                </div>
                
                @if($departurePlan->routes->count() > 0)
                    <div class="space-y-4">
                        @foreach($departurePlan->routes as $route)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-start mb-3">
                                    <div class="flex-1">
                                        <h4 class="text-md font-medium text-gray-900">{{ $route->name }}</h4>
                                        @if($route->planned_date)
                                            <div class="text-sm text-blue-600 mt-1">
                                                📅 Планируется: {{ $route->planned_date->format('d.m.Y') }}
                                                @if($route->start_time)
                                                    в {{ $route->start_time->format('H:i') }}
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex flex-col items-end space-y-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($route->completion_status === 'not_started') bg-gray-100 text-gray-800
                                            @elseif($route->completion_status === 'in_progress') bg-blue-100 text-blue-800
                                            @elseif($route->completion_status === 'completed') bg-green-100 text-green-800
                                            @elseif($route->completion_status === 'partially_completed') bg-yellow-100 text-yellow-800
                                            @elseif($route->completion_status === 'failed') bg-red-100 text-red-800
                                            @elseif($route->completion_status === 'cancelled') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ $route->completion_status_name ?? 'Не начат' }}
                                        </span>
                                        @if($route->completion_percentage > 0)
                                            <div class="text-xs text-gray-500">
                                                Выполнено: {{ $route->completion_percentage }}%
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-3">
                                    <div>
                                        <span class="text-sm font-medium text-gray-500">Отловщик:</span>
                                        <span class="text-sm text-gray-900">{{ $route->assignedUser->name ?? 'Не назначен' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-sm font-medium text-gray-500">Водитель:</span>
                                        <span class="text-sm text-gray-900">{{ $route->driverUser->name ?? 'Не назначен' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-sm font-medium text-gray-500">Заявок:</span>
                                        <span class="text-sm text-gray-900">{{ $route->routeRequests ? $route->routeRequests->count() : 0 }}</span>
                                    </div>
                                    <div>
                                        <span class="text-sm font-medium text-gray-500">Животных:</span>
                                        <span class="text-sm text-gray-900">{{ $route->routeAnimals ? $route->routeAnimals->count() : 0 }}</span>
                                    </div>
                                    <div>
                                        <span class="text-sm font-medium text-gray-500">Время:</span>
                                        <span class="text-sm text-gray-900">{{ $route->estimated_duration ?? 0 }} мин</span>
                                    </div>
                                </div>
                                
                                @if($route->routeRequests && $route->routeRequests->count() > 0)
                                    <div class="border-t pt-3">
                                        <h5 class="text-sm font-medium text-gray-700 mb-2">Заявки в маршруте:</h5>
                                        <div class="space-y-2">
                                            @foreach($route->routeRequests->sortBy('order') as $routeRequest)
                                                @if($routeRequest->osvvRequest)
                                                    @php $request = $routeRequest->osvvRequest @endphp
                                                    <div class="bg-gray-50 p-3 rounded 
                                                        @if($routeRequest->execution_status === 'completed') bg-green-50 border border-green-200 @endif
                                                        @if($routeRequest->execution_status === 'failed') bg-red-50 border border-red-200 @endif">
                                                        <div class="flex items-start space-x-3">
                                                            <!-- Чекбокс для отметки выполнения -->
                                                            <div class="flex items-center pt-1">
                                                                <input type="checkbox" 
                                                                       id="request_{{ $routeRequest->id }}"
                                                                       onchange="toggleRequestExecution({{ $routeRequest->id }}, this.checked)"
                                                                       {{ $routeRequest->execution_status === 'completed' ? 'checked' : '' }}
                                                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                                            </div>
                                                            
                                                            <div class="flex-1">
                                                                <div class="flex justify-between items-start">
                                                                    <div>
                                                                        <div class="font-medium text-sm 
                                                                            @if($routeRequest->execution_status === 'completed') text-green-800 line-through @endif
                                                                            @if($routeRequest->execution_status === 'failed') text-red-800 @endif">
                                                                            #{{ $request->id }} - {{ $request->applicant_name }}
                                                                        </div>
                                                                        <div class="text-xs text-gray-600">{{ $request->location ?? 'Адрес не указан' }}</div>
                                                                        @if($request->is_urgent)
                                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 mt-1">
                                                                                Срочно
                                                                            </span>
                                                                        @endif
                                                                        
                                                                        @if($routeRequest->execution_status && $routeRequest->execution_status !== 'pending')
                                                                            <div class="mt-1">
                                                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                                                    @if($routeRequest->execution_status === 'completed') bg-green-100 text-green-800 @endif
                                                                                    @if($routeRequest->execution_status === 'failed') bg-red-100 text-red-800 @endif
                                                                                    @if($routeRequest->execution_status === 'visited') bg-blue-100 text-blue-800 @endif
                                                                                    @if($routeRequest->execution_status === 'cancelled') bg-gray-100 text-gray-800 @endif
                                                                                    @if($routeRequest->execution_status === 'no_animals_found') bg-yellow-100 text-yellow-800 @endif">
                                                                                    {{ $routeRequest->execution_status_name }}
                                                                                </span>
                                                                                @if($routeRequest->animals_captured > 0)
                                                                                    <span class="text-xs text-gray-600 ml-1">
                                                                                        ({{ $routeRequest->animals_captured }} животных)
                                                                                    </span>
                                                                                @endif
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                    <div class="text-right">
                                                                        <span class="text-xs text-gray-500">{{ $routeRequest->estimated_time ?? 60 }} мин</span>
                                                                        @if($routeRequest->executed_at)
                                                                            <div class="text-xs text-gray-500 mt-1">
                                                                                {{ $routeRequest->executed_at->format('H:i') }}
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                
                                                                @if($routeRequest->execution_notes)
                                                                    <div class="mt-2 text-xs text-gray-600">
                                                                        <strong>Заметки:</strong> {{ $routeRequest->execution_notes }}
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                
                                @if($route->routeAnimals && $route->routeAnimals->count() > 0)
                                    <div class="border-t pt-3">
                                        <h5 class="text-sm font-medium text-gray-700 mb-2">Животные на выпуск:</h5>
                                        <div class="space-y-2">
                                            @foreach($route->routeAnimals->sortBy('sequence_order') as $routeAnimal)
                                                @if($routeAnimal->animal)
                                                    @php $animal = $routeAnimal->animal @endphp
                                                    <div class="bg-green-50 p-3 rounded border border-green-200
                                                        @if($routeAnimal->release_status === 'released') bg-green-100 border-green-300 @endif
                                                        @if($routeAnimal->release_status === 'failed') bg-red-50 border-red-200 @endif">
                                                        <div class="flex items-start space-x-3">
                                                            <!-- Чекбокс для отметки выпуска -->
                                                            <div class="flex items-center pt-1">
                                                                <input type="checkbox" 
                                                                       id="animal_{{ $routeAnimal->id }}"
                                                                       onchange="toggleAnimalRelease({{ $routeAnimal->id }}, this.checked)"
                                                                       {{ $routeAnimal->release_status === 'released' ? 'checked' : '' }}
                                                                       class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                                            </div>
                                                            
                                                            <div class="flex-1">
                                                                <div class="flex justify-between items-start">
                                                                    <div class="flex items-center space-x-3">
                                                                        <!-- Иконка животного -->
                                                                        <div class="flex-shrink-0">
                                                                    @if($animal->type === 'dog')
                                                                        <!-- Иконка собаки от Lucide -->
                                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-700">
                                                                            <path d="M11.25 16.25h1.5L12 17z"/>
                                                                            <path d="M16 14v.5"/>
                                                                            <path d="M4.42 11.247A13.152 13.152 0 0 0 4 14.556C4 18.728 7.582 21 12 21s8-2.272 8-6.444a11.702 11.702 0 0 0-.493-3.309"/>
                                                                            <path d="M8 14v.5"/>
                                                                            <path d="M8.5 8.5c-.384 1.05-1.083 2.028-2.344 2.5-1.931.722-3.576-.297-3.656-1-.113-.994 1.177-6.53 4-7 1.923-.321 3.651.845 3.651 2.235A7.497 7.497 0 0 1 14 5.277c0-1.39 1.844-2.598 3.767-2.277 2.823.47 4.113 6.006 4 7-.08.703-1.725 1.722-3.656 1-1.261-.472-1.855-1.45-2.239-2.5"/>
                                                                        </svg>
                                                                    @elseif($animal->type === 'cat')
                                                                        <!-- Иконка кошки от Lucide -->
                                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-700">
                                                                            <path d="M12 5c.67 0 1.35.09 2 .26 1.78-2 5.03-2.84 6.42-2.26 1.4.58-.42 7-.42 7 .57 1.07 1 2.24 1 3.44C21 17.9 16.97 21 12 21s-9-3-9-7.56c0-1.25.5-2.4 1-3.44 0 0-1.89-6.42-.5-7 1.39-.58 4.72.23 6.5 2.23A9.04 9.04 0 0 1 12 5Z"/>
                                                                            <path d="M8 14v.5"/>
                                                                            <path d="M16 14v.5"/>
                                                                            <path d="M11.25 16.25h1.5L12 17z"/>
                                                                        </svg>
                                                                    @else
                                                                        <!-- Универсальная иконка для других животных -->
                                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-700">
                                                                            <path d="M20.24 12.24a6 6 0 0 0-8.49-8.49L5 10.5V19h8.5z"/>
                                                                            <line x1="16" y1="8" x2="2" y2="22"/>
                                                                            <line x1="17.5" y1="15" x2="9" y2="15"/>
                                                                        </svg>
                                                                    @endif
                                                                </div>
                                                                
                                                                        <!-- Информация о животном -->
                                                                        <div>
                                                                            <div class="font-medium text-sm text-gray-900
                                                                                @if($routeAnimal->release_status === 'released') text-green-800 line-through @endif
                                                                                @if($routeAnimal->release_status === 'failed') text-red-800 @endif">
                                                                                @if($animal->cage_number)
                                                                                    Вольер №{{ $animal->cage_number }}
                                                                                @else
                                                                                    {{ $animal->name ?? 'Без номера вольера' }}
                                                                                @endif
                                                                                <span class="text-xs text-gray-500">({{ $animal->type_name ?? $animal->type }})</span>
                                                                            </div>
                                                                            @if($animal->osvvRequest)
                                                                                <div class="text-xs text-gray-600">
                                                                                    <span class="font-medium">Заявка #{{ $animal->osvv_request_id }}</span>
                                                                                    @if($animal->osvvRequest->location_address)
                                                                                        • {{ $animal->osvvRequest->location_address }}
                                                                                    @endif
                                                                                </div>
                                                                            @endif
                                                                            <div class="text-xs text-gray-500">
                                                                                @if($animal->arrived_at)
                                                                                    @php
                                                                                        $daysInShelter = abs(round(now()->diffInDays($animal->arrived_at)));
                                                                                    @endphp
                                                                                    В приюте {{ $daysInShelter }} 
                                                                                    @if($daysInShelter == 1)
                                                                                        день
                                                                                    @elseif($daysInShelter >= 2 && $daysInShelter <= 4)
                                                                                        дня
                                                                                    @else
                                                                                        дней
                                                                                    @endif
                                                                                @else
                                                                                    В приюте (дата неизвестна)
                                                                                @endif
                                                                            </div>
                                                                            
                                                                            @if($routeAnimal->release_status && $routeAnimal->release_status !== 'pending')
                                                                                <div class="mt-1">
                                                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                                                        @if($routeAnimal->release_status === 'released') bg-green-100 text-green-800 @endif
                                                                                        @if($routeAnimal->release_status === 'failed') bg-red-100 text-red-800 @endif
                                                                                        @if($routeAnimal->release_status === 'cancelled') bg-gray-100 text-gray-800 @endif">
                                                                                        {{ $routeAnimal->release_status_name }}
                                                                                    </span>
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="text-right">
                                                                        <div class="flex flex-col items-end space-y-1">
                                                                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded">
                                                                                К выпуску
                                                                            </span>
                                                                            <span class="text-xs text-gray-500">{{ $routeAnimal->estimated_time ?? 30 }} мин</span>
                                                                            @if($routeAnimal->released_at)
                                                                                <div class="text-xs text-gray-500">
                                                                                    {{ $routeAnimal->released_at->format('H:i') }}
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                
                                                                @if($routeAnimal->release_notes)
                                                                    <div class="mt-2 text-xs text-gray-600">
                                                                        <strong>Заметки о выпуске:</strong> {{ $routeAnimal->release_notes }}
                                                                    </div>
                                                                @endif
                                                                
                                                                @if($routeAnimal->release_location)
                                                                    <div class="mt-1 text-xs text-gray-600">
                                                                        <strong>Место выпуска:</strong> {{ $routeAnimal->release_location }}
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                
                                @if($route->actual_start_time || $route->actual_end_time)
                                    <div class="border-t pt-3 mt-3">
                                        <div class="text-xs text-gray-500">
                                            @if($route->actual_start_time)
                                                Начат: {{ $route->actual_start_time->format('d.m.Y H:i') }}
                                            @endif
                                            @if($route->actual_end_time)
                                                | Завершен: {{ $route->actual_end_time->format('d.m.Y H:i') }}
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                
                                @if($route->notes)
                                    <div class="border-t pt-3 mt-3">
                                        <span class="text-sm font-medium text-gray-500">Заметки:</span>
                                        <p class="text-sm text-gray-900 mt-1">{{ $route->notes }}</p>
                                    </div>
                                @endif
                                
                                @if($route->completion_notes)
                                    <div class="border-t pt-3 mt-3">
                                        <span class="text-sm font-medium text-gray-500">Заметки о выполнении:</span>
                                        <p class="text-sm text-gray-900 mt-1">{{ $route->completion_notes }}</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                        <h3 class="text-sm font-medium text-gray-900">Нет маршрутов</h3>
                        <p class="text-sm text-gray-500">План пока не содержит маршрутов</p>
                    </div>
                @endif
            </div>
        @endif
        
        <!-- Секция задач -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                Задачи сотрудников ({{ $departurePlan->tasks->count() }})
            </h3>
            
            @if($departurePlan->tasks->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($departurePlan->tasks as $task)
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <h4 class="text-sm font-medium text-gray-900">{{ $task->title }}</h4>
                                    <p class="text-xs text-gray-600 mt-1">
                                        Назначено: {{ $task->assignedTo->name }}
                                    </p>
                                </div>
                                <div class="flex flex-col items-end space-y-1">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                        @if($task->priority === 'low') bg-green-100 text-green-800
                                        @elseif($task->priority === 'medium') bg-yellow-100 text-yellow-800
                                        @elseif($task->priority === 'high') bg-orange-100 text-orange-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ $task->priority_label }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                        @if($task->status === 'pending') bg-gray-100 text-gray-800
                                        @elseif($task->status === 'in_progress') bg-blue-100 text-blue-800
                                        @elseif($task->status === 'completed') bg-green-100 text-green-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ $task->status_label }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="space-y-2 text-xs text-gray-600">
                                @if($task->due_date)
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        Срок: {{ $task->due_date->format('d.m.Y H:i') }}
                                        @if($task->isOverdue())
                                            <span class="ml-1 text-red-600 font-medium">
                                                (Просрочено)
                                            </span>
                                        @elseif($task->isDueSoon())
                                            <span class="ml-1 text-orange-600 font-medium">
                                                (Скоро срок)
                                            </span>
                                        @endif
                                    </div>
                                @endif
                                
                                @if($task->estimated_hours)
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Оценочное время: {{ $task->estimated_hours }}ч
                                    </div>
                                @endif
                                
                                @if($task->tags && count(json_decode($task->tags)) > 0)
                                    <div class="flex items-center flex-wrap gap-1">
                                        @foreach(json_decode($task->tags) as $tag)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ $tag }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            
                            <div class="mt-3 flex justify-between items-center">
                                <a href="{{ route('admin.tasks.show', $task) }}" 
                                   class="text-indigo-600 hover:text-indigo-900 text-xs font-medium">
                                    Подробнее
                                </a>
                                @if($task->status === 'pending')
                                    <form action="{{ route('admin.tasks.start', $task) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-green-600 hover:text-green-900 text-xs font-medium">
                                            Начать
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    <h3 class="text-sm font-medium text-gray-900">Нет задач</h3>
                    <p class="text-sm text-gray-500">
                        Задачи будут созданы автоматически при утверждении плана
                    </p>
                </div>
            @endif
        </div>
    </div>

    <!-- Модальное окно для добавления заявки -->
    <div id="addRequestModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Добавить заявку в план</h3>
                </div>
                
                <form id="addRequestForm" class="px-6 py-4">
                    <div class="space-y-4">
                        <div>
                            <label for="osvv_request_id" class="block text-sm font-medium text-gray-700">Заявка ОСВВ</label>
                            <select name="osvv_request_id" id="osvv_request_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Выберите заявку</option>
                                <!-- Здесь будут загружены доступные заявки -->
                            </select>
                        </div>
                        
                        <div>
                            <label for="route_id" class="block text-sm font-medium text-gray-700">Маршрут</label>
                            <select name="route_id" id="route_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Выберите маршрут</option>
                                @foreach($departurePlan->routes as $route)
                                    <option value="{{ $route->id }}">{{ $route->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label for="estimated_time" class="block text-sm font-medium text-gray-700">Время выполнения (мин)</label>
                            <input type="number" name="estimated_time" id="estimated_time" value="60" min="1" max="300" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                </form>
                
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" onclick="closeAddRequestModal()" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Отмена
                    </button>
                    <button type="button" onclick="submitAddRequest()" class="px-4 py-2 bg-green-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-green-700">
                        Добавить
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
function toggleRequestExecution(requestId, isChecked) {
    if (isChecked) {
        // Если отмечен как выполненный, показываем простое подтверждение
        const notes = prompt('Заметки о выполнении (необязательно):');
        const animalsCount = prompt('Количество отловленных животных (0 если нет):') || '0';
        
        markRequestAsExecuted(requestId, 'completed', 'success', notes, parseInt(animalsCount));
    } else {
        // Если снимаем отметку, возвращаем в pending
        markRequestAsExecuted(requestId, 'pending', null, null, 0);
    }
}

function markRequestAsExecuted(requestId, status, result, notes, animalsCount) {
    fetch(`/admin/route-execution/request/${requestId}/mark`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            execution_status: status,
            execution_result: result,
            execution_notes: notes,
            animals_captured: animalsCount
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Обновляем страницу для отображения изменений
            location.reload();
        } else {
            alert('Ошибка: ' + data.message);
            // Возвращаем чекбокс в предыдущее состояние
            document.getElementById('request_' + requestId).checked = !document.getElementById('request_' + requestId).checked;
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        alert('Произошла ошибка при отметке выполнения');
        // Возвращаем чекбокс в предыдущее состояние
        document.getElementById('request_' + requestId).checked = !document.getElementById('request_' + requestId).checked;
    });
}

function showAddRequestModal() {
    // Загружаем доступные заявки
    loadAvailableRequests();
    document.getElementById('addRequestModal').classList.remove('hidden');
}

function closeAddRequestModal() {
    document.getElementById('addRequestModal').classList.add('hidden');
    document.getElementById('addRequestForm').reset();
}

function loadAvailableRequests() {
    fetch('/admin/osvv-requests/available')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('osvv_request_id');
            select.innerHTML = '<option value="">Выберите заявку</option>';
            
            data.requests.forEach(request => {
                const option = document.createElement('option');
                option.value = request.id;
                option.textContent = `#${request.id} - ${request.applicant_name} (${request.location})`;
                select.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Ошибка при загрузке заявок:', error);
        });
}

function submitAddRequest() {
    const form = document.getElementById('addRequestForm');
    const formData = new FormData(form);
    
    const data = {
        osvv_request_id: formData.get('osvv_request_id'),
        route_id: formData.get('route_id'),
        estimated_time: formData.get('estimated_time')
    };
    
    fetch('/admin/departure-planner/{{ $departurePlan->id }}/add-request', {
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
            closeAddRequestModal();
            location.reload();
        } else {
            alert('Ошибка: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        alert('Произошла ошибка при добавлении заявки');
    });
}

function toggleAnimalRelease(animalId, isChecked) {
    if (isChecked) {
        // Если отмечен как выпущенный, показываем диалог для ввода данных
        const notes = prompt('Заметки о выпуске (необязательно):');
        let location = null;
        
        if (confirm('Указать место выпуска?')) {
            location = prompt('Место выпуска:');
        }
        
        markAnimalAsReleased(animalId, 'released', 'success', notes, location);
    } else {
        // Если снимаем отметку, возвращаем в pending
        markAnimalAsReleased(animalId, 'pending', null, null, null);
    }
}

function markAnimalAsReleased(animalId, status, result, notes, location) {
    fetch(`/admin/route-execution/animal/${animalId}/mark-release`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            release_status: status,
            release_result: result,
            release_notes: notes,
            release_location: location
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Обновляем страницу для отображения изменений
            location.reload();
        } else {
            alert('Ошибка: ' + data.message);
            // Возвращаем чекбокс в предыдущее состояние
            document.getElementById('animal_' + animalId).checked = !document.getElementById('animal_' + animalId).checked;
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        alert('Произошла ошибка при отметке выпуска');
        // Возвращаем чекбокс в предыдущее состояние
        document.getElementById('animal_' + animalId).checked = !document.getElementById('animal_' + animalId).checked;
    });
}
</script>
@endpush