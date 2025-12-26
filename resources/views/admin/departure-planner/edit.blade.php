@extends('admin.layout')

@section('header', 'Редактирование плана выезда')

@section('content')
    <div class="space-y-6">
        <!-- Верхняя панель с заголовком -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                        Редактирование: {{ $plan->name }}
                    </h3>
                    <div class="flex space-x-2">
                        <a href="{{ route('admin.departure-planner.show', $plan) }}" class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-white/20 active:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                            </svg>
                            Назад к просмотру
                        </a>
                    </div>
                </div>
                <div class="mt-2">
                    <p class="text-white/70 text-sm">Редактирование плана выезда и управление маршрутами</p>
                </div>
            </div>
        
            <!-- Статус -->
            <div class="px-6 py-3 bg-white border-b border-gray-100">
                <div class="flex flex-wrap items-center gap-4">
                    @switch($plan->status)
                        @case('draft')
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                Черновик
                            </span>
                            @break
                        @case('approved')
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                Утвержден (только просмотр)
                            </span>
                            @break
                        @case('in_progress')
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                В процессе (только просмотр)
                            </span>
                            @break
                        @case('completed')
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                Завершен (только просмотр)
                            </span>
                            @break
                    @endswitch
                    
                    @if($plan->status !== 'draft')
                        <div class="text-sm text-amber-600 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.864-.833-2.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                            Редактирование доступно только для черновиков
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if($plan->status === 'draft')
            <form method="POST" action="{{ route('admin.departure-planner.update', $plan) }}">
                @csrf
                @method('PUT')
                
                <!-- Основная информация о плане -->
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-700 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Основная информация
                        </h3>
                    </div>
                    <div class="px-6 py-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Название плана <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $plan->name) }}"
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm @error('name') border-red-300 @enderror" 
                                       required>
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="planned_date" class="block text-sm font-medium text-gray-700 mb-1">
                                    Дата планирования <span class="text-red-500">*</span>
                                </label>
                                <input type="date" 
                                       id="planned_date" 
                                       name="planned_date" 
                                       value="{{ old('planned_date', $plan->planned_date ? $plan->planned_date->format('Y-m-d') : '') }}"
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm @error('planned_date') border-red-300 @enderror" 
                                       required>
                                @error('planned_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                Описание
                            </label>
                            <textarea id="description" 
                                      name="description" 
                                      rows="3"
                                      placeholder="Дополнительная информация о плане..."
                                      class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm @error('description') border-red-300 @enderror">{{ old('description', $plan->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Планировщик маршрутов -->
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-700 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m0 0L9 7" />
                            </svg>
                            Планировщик маршрутов
                        </h3>
                    </div>
                    <div class="px-6 py-4">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Доступные заявки -->
                            <div>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h4 class="text-md font-medium text-gray-900 mb-3 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        Доступные заявки ({{ $availableRequests->count() }})
                                    </h4>
                                    <div class="space-y-2 max-h-96 overflow-y-auto" id="available-requests">
                                        @foreach($availableRequests as $request)
                                            <div class="request-item bg-white border border-gray-200 rounded-md p-3 cursor-move hover:shadow-md transition-shadow" 
                                                 data-request-id="{{ $request->id }}"
                                                 draggable="true">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex-1">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            #{{ $request->id }} - {{ $request->applicant_name }}
                                                        </div>
                                                        <div class="text-xs text-gray-500">
                                                            {{ $request->address }}
                                                        </div>
                                                        <div class="text-xs text-gray-400">
                                                            {{ $request->created_at->format('d.m.Y') }}
                                                            @if($request->is_urgent)
                                                                <span class="ml-2 inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                                    Срочно
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="ml-3">
                                                        <button type="button" class="add-to-route text-indigo-600 hover:text-indigo-900 text-sm">
                                                            Добавить →
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Маршруты -->
                            <div>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="text-md font-medium text-gray-900 flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m0 0L9 7" />
                                            </svg>
                                            Маршруты
                                        </h4>
                                        <button type="button" id="add-route" class="inline-flex items-center px-3 py-1 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                                            </svg>
                                            Маршрут
                                        </button>
                                    </div>
                                    <div id="routes-container" class="space-y-4 max-h-96 overflow-y-auto">
                                        <!-- Существующие маршруты -->
                                        @foreach($plan->routes as $route)
                                            <div class="route-container bg-white border border-gray-200 rounded-md p-4" data-route-id="{{ $route->id }}">
                                                <div class="flex items-center justify-between mb-3">
                                                    <h5 class="text-sm font-medium text-gray-900">Маршрут {{ $loop->iteration }}</h5>
                                                    <button type="button" class="remove-route text-red-600 hover:text-red-900 text-sm">
                                                        Удалить
                                                    </button>
                                                </div>
                                                
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-700 mb-1">Отловщик</label>
                                                        <select name="routes[{{ $route->id }}][catcher_id]" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                                            <option value="">Выберите отловщика</option>
                                                            @foreach($catchers as $catcher)
                                                                <option value="{{ $catcher->id }}" {{ $route->catcher_id == $catcher->id ? 'selected' : '' }}>
                                                                    {{ $catcher->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-700 mb-1">Время начала</label>
                                                        <input type="time" name="routes[{{ $route->id }}][start_time]" value="{{ $route->start_time }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                                    </div>
                                                </div>
                                                
                                                <div class="route-requests min-h-20 border-2 border-dashed border-gray-300 rounded-md p-2" data-route-id="{{ $route->id }}">
                                                    @if($route->requests->count() > 0)
                                                        @foreach($route->requests as $routeRequest)
                                                            @php $request = $routeRequest->osvvRequest @endphp
                                                            <div class="request-item bg-white border border-gray-200 rounded-md p-3 cursor-move hover:shadow-md transition-shadow mb-2" 
                                                                 data-request-id="{{ $request->id }}"
                                                                 draggable="true">
                                                                <div class="flex items-center justify-between">
                                                                    <div class="flex-1">
                                                                        <div class="text-sm font-medium text-gray-900">
                                                                            #{{ $request->id }} - {{ $request->applicant_name }}
                                                                        </div>
                                                                        <div class="text-xs text-gray-500">
                                                                            {{ $request->address }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="ml-3">
                                                                        <button type="button" class="add-to-route text-red-600 hover:text-red-900 text-sm" onclick="moveRequestToAvailable(this)">
                                                                            ← Убрать
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                                <input type="hidden" name="routes[{{ $route->id }}][requests][]" value="{{ $request->id }}" data-request-id="{{ $request->id }}">
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <div class="text-center text-gray-500 text-sm empty-message">
                                                            Перетащите заявки сюда или используйте кнопку "Добавить"
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Кнопки действий -->
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-4 flex justify-end space-x-3">
                        <a href="{{ route('admin.departure-planner.show', $plan) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 font-medium text-sm transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                            Отмена
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-medium text-sm text-white hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Сохранить изменения
                        </button>
                    </div>
                </div>
            </form>
        @else
            <!-- Режим только просмотра для неизменяемых планов -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-700 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Просмотр плана (режим только чтения)
                    </h3>
                </div>
                <div class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.864-.833-2.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Редактирование недоступно</h3>
                    <p class="mt-1 text-sm text-gray-500">Этот план нельзя редактировать, так как он уже утвержден или находится в процессе выполнения.</p>
                    <div class="mt-6">
                        <a href="{{ route('admin.departure-planner.show', $plan) }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path fill-rule="evenodd" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Перейти к просмотру
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if($plan->status === 'draft')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let routeCounter = {{ $plan->routes->count() }};
                const routesContainer = document.getElementById('routes-container');
                const addRouteButton = document.getElementById('add-route');

                // Добавление нового маршрута
                addRouteButton.addEventListener('click', function() {
                    routeCounter++;
                    const routeHtml = createRouteElement(routeCounter);
                    routesContainer.insertAdjacentHTML('beforeend', routeHtml);
                    setupRouteEventListeners(routeCounter);
                });

                // Создание HTML элемента маршрута
                function createRouteElement(routeId) {
                    return `
                        <div class="route-container bg-white border border-gray-200 rounded-md p-4" data-route-id="${routeId}">
                            <div class="flex items-center justify-between mb-3">
                                <h5 class="text-sm font-medium text-gray-900">Маршрут ${routeId}</h5>
                                <button type="button" class="remove-route text-red-600 hover:text-red-900 text-sm">
                                    Удалить
                                </button>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Отловщик</label>
                                    <select name="routes[${routeId}][catcher_id]" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                        <option value="">Выберите отловщика</option>
                                        @foreach($catchers as $catcher)
                                            <option value="{{ $catcher->id }}">{{ $catcher->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Время начала</label>
                                    <input type="time" name="routes[${routeId}][start_time]" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                </div>
                            </div>
                            
                            <div class="route-requests min-h-20 border-2 border-dashed border-gray-300 rounded-md p-2" data-route-id="${routeId}">
                                <div class="text-center text-gray-500 text-sm empty-message">
                                    Перетащите заявки сюда или используйте кнопку "Добавить"
                                </div>
                            </div>
                        </div>
                    `;
                }

                // Настройка обработчиков событий для маршрута
                function setupRouteEventListeners(routeId) {
                    const routeContainer = document.querySelector(`[data-route-id="${routeId}"]`);
                    const removeButton = routeContainer.querySelector('.remove-route');
                    const requestsContainer = routeContainer.querySelector('.route-requests');

                    // Удаление маршрута
                    removeButton.addEventListener('click', function() {
                        if (confirm('Удалить маршрут?')) {
                            // Возвращаем заявки в доступные
                            const routeRequests = routeContainer.querySelectorAll('.request-item');
                            routeRequests.forEach(request => {
                                document.getElementById('available-requests').appendChild(request);
                            });
                            routeContainer.remove();
                        }
                    });

                    // Настройка Drag & Drop (аналогично странице создания)
                    setupDragAndDrop(requestsContainer, routeId);
                }

                // Настройка существующих маршрутов
                document.querySelectorAll('.route-container').forEach(function(container) {
                    const routeId = container.getAttribute('data-route-id');
                    const requestsContainer = container.querySelector('.route-requests');
                    const removeButton = container.querySelector('.remove-route');

                    if (removeButton) {
                        removeButton.addEventListener('click', function() {
                            if (confirm('Удалить маршрут?')) {
                                const routeRequests = container.querySelectorAll('.request-item');
                                routeRequests.forEach(request => {
                                    document.getElementById('available-requests').appendChild(request);
                                });
                                container.remove();
                            }
                        });
                    }

                    setupDragAndDrop(requestsContainer, routeId);
                });

                // Общая функция настройки Drag & Drop
                function setupDragAndDrop(requestsContainer, routeId) {
                    requestsContainer.addEventListener('dragover', function(e) {
                        e.preventDefault();
                        this.classList.add('border-indigo-500', 'bg-indigo-50');
                    });

                    requestsContainer.addEventListener('dragleave', function(e) {
                        this.classList.remove('border-indigo-500', 'bg-indigo-50');
                    });

                    requestsContainer.addEventListener('drop', function(e) {
                        e.preventDefault();
                        this.classList.remove('border-indigo-500', 'bg-indigo-50');
                        
                        const requestId = e.dataTransfer.getData('text/plain');
                        const requestElement = document.querySelector(`[data-request-id="${requestId}"]`);
                        
                        if (requestElement) {
                            // Скрываем сообщение о пустоте
                            const emptyMessage = this.querySelector('.empty-message');
                            if (emptyMessage) {
                                emptyMessage.style.display = 'none';
                            }
                            
                            // Добавляем заявку в маршрут
                            this.appendChild(requestElement);
                            
                            // Добавляем скрытое поле для отправки формы
                            const hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.name = `routes[${routeId}][requests][]`;
                            hiddenInput.value = requestId;
                            hiddenInput.setAttribute('data-request-id', requestId);
                            this.appendChild(hiddenInput);
                            
                            // Обновляем кнопку
                            const addButton = requestElement.querySelector('.add-to-route');
                            addButton.textContent = '← Убрать';
                            addButton.onclick = function() {
                                moveRequestToAvailable(requestElement, requestId, routeId);
                            };
                        }
                    });
                }

                // Перемещение заявки обратно в доступные
                window.moveRequestToAvailable = function(requestElement, requestId, routeId) {
                    document.getElementById('available-requests').appendChild(requestElement);
                    
                    // Удаляем скрытое поле
                    const hiddenInput = document.querySelector(`input[name="routes[${routeId}][requests][]"][data-request-id="${requestId}"]`);
                    if (hiddenInput) {
                        hiddenInput.remove();
                    }
                    
                    // Обновляем кнопку
                    const addButton = requestElement.querySelector('.add-to-route');
                    addButton.textContent = 'Добавить →';
                    addButton.onclick = null;
                    
                    // Показываем сообщение о пустоте если нужно
                    const routeContainer = document.querySelector(`[data-route-id="${routeId}"]`);
                    const requestsContainer = routeContainer.querySelector('.route-requests');
                    const requests = requestsContainer.querySelectorAll('.request-item');
                    if (requests.length === 0) {
                        const emptyMessage = requestsContainer.querySelector('.empty-message');
                        if (emptyMessage) {
                            emptyMessage.style.display = 'block';
                        }
                    }
                };

                // Drag & Drop для заявок
                document.addEventListener('dragstart', function(e) {
                    if (e.target.classList.contains('request-item')) {
                        e.dataTransfer.setData('text/plain', e.target.getAttribute('data-request-id'));
                    }
                });
            });
        </script>
    @endif
@endsection 