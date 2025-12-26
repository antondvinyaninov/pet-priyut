@extends('admin.layout')

@section('header', 'Планирование выезда')

@section('content')
    <div class="space-y-6">
        <!-- Верхняя панель с основной информацией -->
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Новый план выезда</h3>
                <div class="flex space-x-3">
                    <button type="button" id="add-team-btn" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Добавить экипаж
                    </button>
                    <button type="button" id="save-plan-btn" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v6a2 2 0 002 2h2m0 0h8m0 0h2a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 0V6a2 2 0 00-2-2H9a2 2 0 00-2 2v1m8 0V9a1 1 0 10-2 0V8a1 1 0 10-2 0v1" />
                        </svg>
                        Сохранить план
                    </button>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="plan_name" class="block text-sm font-medium text-gray-700 mb-1">Название плана</label>
                    <input type="text" id="plan_name" name="plan_name" 
                           value="План выезда на {{ $selectedDate->format('d.m.Y') }}"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Дата планирования</label>
                    <input type="text" value="{{ $selectedDate->format('d.m.Y') }}" 
                           class="block w-full rounded-md border-gray-300 bg-gray-50" readonly>
                    <input type="hidden" id="planned_date" value="{{ $selectedDate->format('Y-m-d') }}">
                </div>
                <div>
                    <label for="plan_notes" class="block text-sm font-medium text-gray-700 mb-1">Заметки</label>
                    <input type="text" id="plan_notes" name="plan_notes" 
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        <!-- Экипажи/Зоны - теперь сверху -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Экипажи и маршруты
                </h3>
                <p class="text-sm text-gray-600 mt-1">Создайте экипажи и распределите заявки между ними</p>
            </div>
            
            <div class="p-6">
                <!-- Сетка экипажей в 2 колонки -->
                <div id="teams-container" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Экипажи будут добавляться динамически -->
                </div>
                
                <div id="no-teams" class="text-center py-12">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Создайте первый экипаж</h3>
                    <p class="text-gray-500 mb-4">Нажмите "Добавить экипаж" чтобы начать планирование</p>
                    <button type="button" onclick="addTeam()" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Добавить экипаж
                    </button>
                </div>
            </div>
        </div>

        <!-- Секции доступных заявок и животных на выпуск в 2 колонки -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Левая колонка: Доступные заявки -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                        Доступные заявки
                        <span class="ml-2 px-2 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">
                            {{ count($availableRequests) }}
                        </span>
                    </h3>
                </div>
                
                <div class="p-4">
                    <div id="available-requests" class="grid grid-cols-1 gap-3 min-h-[200px] max-h-96 overflow-y-auto">
                        @foreach($availableRequests as $request)
                            <div class="request-card bg-white p-2 border border-gray-200 rounded-lg shadow-sm cursor-move hover:shadow-md transition-shadow" 
                                 draggable="true" 
                                 data-request-id="{{ $request->id }}"
                                 data-urgent="{{ $request->has_bite ? 'true' : 'false' }}"
                                 data-animals="{{ $request->animals_count ?? 1 }}">
                                
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center space-x-2">
                                            <span class="font-medium text-sm text-gray-900 flex-shrink-0">#{{ $request->id }}</span>
                                            <div class="flex items-center text-xs text-gray-600 min-w-0">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                <span class="truncate">{{ $request->location_address ?? ($request->location_landmark ?? 'Адрес не указан') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    @if($request->has_bite)
                                        <span class="px-1.5 py-0.5 bg-red-100 text-red-800 text-xs font-medium rounded flex-shrink-0 ml-2">
                                            Укус!
                                        </span>
                                    @endif
                                </div>

                                <div class="border-t pt-1.5 mt-1.5 text-xs text-gray-500 space-y-1">
                                    <div class="font-medium text-gray-700 text-xs">Заявитель:</div>
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <span class="truncate">{{ $request->contact_name ?? 'Не указано' }}</span>
                                    </div>
                                    @if($request->contact_phone)
                                        <div class="flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                            </svg>
                                            <span class="truncate">{{ $request->contact_phone }}</span>
                                        </div>
                                    @endif
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                        <a href="/admin/osvv/{{ $request->id }}" class="text-blue-600 hover:text-blue-800 hover:underline truncate" target="_blank">
                                            Открыть заявку
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Правая колонка: Животные на выпуск -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                        </svg>
                        Животные на выпуск
                        <span class="ml-2 px-2 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full">
                            {{ count($animalsForRelease) }}
                        </span>
                    </h3>
                </div>
                
                <div class="p-4 max-h-96 overflow-y-auto">
                    @if(count($animalsForRelease) > 0)
                        <div class="space-y-3">
                            @foreach($animalsForRelease as $animal)
                                <div class="animal-card bg-green-50 border border-green-200 rounded-lg p-3 hover:bg-green-100 transition-colors cursor-move" 
                                     draggable="true" 
                                     data-animal-id="{{ $animal->id }}" 
                                     data-type="animal">
                                    <div class="flex space-x-3">
                                        <!-- Фото животного -->
                                        <div class="flex-shrink-0">
                                            @php
                                                $displayPhoto = $animal->photo ?? ($animal->registrationCard->photo_face ?? null);
                                            @endphp
                                            @if($displayPhoto)
                                                <img src="{{ asset('storage/' . $displayPhoto) }}" 
                                                     alt="{{ $animal->name ?? 'Животное' }}"
                                                     class="w-24 h-24 rounded-lg object-cover border border-green-300 cursor-pointer hover:opacity-75 transition"
                                                     onclick="openPhotoModal('{{ asset('storage/' . $displayPhoto) }}', '')">
                                            @else
                                                <div class="w-24 h-24 rounded-lg bg-green-200 border border-green-300 flex items-center justify-center">
                                                    @if($animal->type === 'dog')
                                                        <!-- Иконка собаки от Lucide -->
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-700">
                                                            <path d="M11.25 16.25h1.5L12 17z"/>
                                                            <path d="M16 14v.5"/>
                                                            <path d="M4.42 11.247A13.152 13.152 0 0 0 4 14.556C4 18.728 7.582 21 12 21s8-2.272 8-6.444a11.702 11.702 0 0 0-.493-3.309"/>
                                                            <path d="M8 14v.5"/>
                                                            <path d="M8.5 8.5c-.384 1.05-1.083 2.028-2.344 2.5-1.931.722-3.576-.297-3.656-1-.113-.994 1.177-6.53 4-7 1.923-.321 3.651.845 3.651 2.235A7.497 7.497 0 0 1 14 5.277c0-1.39 1.844-2.598 3.767-2.277 2.823.47 4.113 6.006 4 7-.08.703-1.725 1.722-3.656 1-1.261-.472-1.855-1.45-2.239-2.5"/>
                                                        </svg>
                                                    @elseif($animal->type === 'cat')
                                                        <!-- Иконка кошки от Lucide -->
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-700">
                                                            <path d="M12 5c.67 0 1.35.09 2 .26 1.78-2 5.03-2.84 6.42-2.26 1.4.58-.42 7-.42 7 .57 1.07 1 2.24 1 3.44C21 17.9 16.97 21 12 21s-9-3-9-7.56c0-1.25.5-2.4 1-3.44 0 0-1.89-6.42-.5-7 1.39-.58 4.72.23 6.5 2.23A9.04 9.04 0 0 1 12 5Z"/>
                                                            <path d="M8 14v.5"/>
                                                            <path d="M16 14v.5"/>
                                                            <path d="M11.25 16.25h1.5L12 17z"/>
                                                        </svg>
                                                    @else
                                                        <!-- Универсальная иконка для других животных -->
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-700">
                                                            <path d="M20.24 12.24a6 6 0 0 0-8.49-8.49L5 10.5V19h8.5z"/>
                                                            <line x1="16" y1="8" x2="2" y2="22"/>
                                                            <line x1="17.5" y1="15" x2="9" y2="15"/>
                                                        </svg>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Информация о животном -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex justify-between items-start mb-2">
                                                <div>
                                                    <div class="font-medium text-sm text-gray-900">
                                                        {{ $animal->name ?? 'Без имени' }}
                                                        <span class="text-xs text-gray-500 ml-1">{{ $animal->type_name }}</span>
                                                    </div>
                                                    <div class="text-xs text-gray-600 mt-1">
                                                        Вольер: {{ $animal->cage_number ?? '—' }}
                                                    </div>
                                                </div>
                                                <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded flex-shrink-0">
                                                    К выпуску
                                                </span>
                                            </div>
                                            
                                            <div class="text-xs text-gray-600 space-y-1">
                                                @if($animal->osvvRequest)
                                                    <div class="flex items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                        </svg>
                                                        <span class="truncate">Заявка #{{ $animal->osvv_request_id }}</span>
                                                    </div>
                                                @endif
                                                
                                                <div class="flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <span class="truncate">В приюте 
                                                        @php
                                                            $daysInShelter = abs(round(now()->diffInDays($animal->arrived_at)));
                                                        @endphp
                                                        {{ $daysInShelter }} 
                                                        @if($daysInShelter == 1)
                                                            день
                                                        @elseif($daysInShelter >= 2 && $daysInShelter <= 4)
                                                            дня
                                                        @else
                                                            дней
                                                        @endif
                                                    </span>
                                                </div>
                                                
                                                @if($animal->osvvRequest && $animal->osvvRequest->location_address)
                                                    <div class="flex items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        </svg>
                                                        <span class="truncate">{{ $animal->osvvRequest->location_address }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                            </svg>
                            <p>Нет животных готовых к выпуску</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Скрытая форма для отправки данных -->
        <form id="save-form" method="POST" action="{{ route('admin.departure-planner.store') }}" style="display: none;">
            @csrf
            <input type="hidden" name="planned_date" id="form_planned_date">
            <input type="hidden" name="name" id="form_name">
            <input type="hidden" name="notes" id="form_notes">
            <input type="hidden" name="routes_data" id="form_routes_data">
        </form>

        <style>
            .request-card.dragging,
            .animal-card.dragging {
                opacity: 0.5;
                transform: rotate(5deg);
            }
            
            .team-drop-zone.drag-over {
                background-color: #f0f9ff;
                border-color: #3b82f6;
                border-style: dashed;
            }
            
            .team-card {
                transition: all 0.2s ease;
            }
            
            .team-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            }
            
            .animal-card {
                transition: all 0.2s ease;
            }
        </style>

        <script>
            let teamCounter = 0;
            let teamsData = {};

            // Инициализация
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('add-team-btn').addEventListener('click', addTeam);
                document.getElementById('save-plan-btn').addEventListener('click', savePlan);
                
                // Инициализация drag & drop для существующих заявок
                initDragAndDrop();
            });

            function addTeam() {
                teamCounter++;
                const teamId = 'team-' + teamCounter;
                
                const teamHtml = `
                    <div class="team-card border-2 border-gray-200 rounded-lg p-4 bg-gray-50" data-team-id="${teamId}">
                        <div class="flex justify-between items-center mb-4">
                            <div class="flex-1">
                                <input type="text" class="team-name block w-full border-0 text-lg font-medium text-gray-900 bg-transparent focus:ring-0" 
                                       placeholder="Название экипажа ${teamCounter}" 
                                       value="Экипаж ${teamCounter}">
                            </div>
                            <button type="button" onclick="removeTeam('${teamId}')" class="ml-2 text-red-600 hover:text-red-800">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-3 mb-4">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Отловщик</label>
                                    <select class="team-catcher block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                        <option value="">Выберите отловщика</option>
                                        @foreach($catchers as $catcher)
                                            <option value="{{ $catcher->id }}">{{ $catcher->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Водитель</label>
                                    <select class="team-driver block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                        <option value="">Выберите водителя</option>
                                        @foreach($drivers as $driver)
                                            <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="team-drop-zone border-2 border-dashed border-gray-300 rounded-lg p-4 min-h-[120px] bg-white" data-team-id="${teamId}">
                            <div class="team-requests grid grid-cols-1 gap-2">
                                <!-- Заявки будут добавляться сюда -->
                            </div>
                            <div class="empty-state text-center text-gray-500 text-sm">
                                Перетащите заявки и животных сюда
                            </div>
                        </div>
                        
                        <div class="mt-3 text-sm text-gray-600">
                            <span class="requests-count">Пусто</span>
                        </div>
                    </div>
                `;
                
                const container = document.getElementById('teams-container');
                container.insertAdjacentHTML('beforeend', teamHtml);
                
                // Скрываем placeholder
                document.getElementById('no-teams').style.display = 'none';
                
                // Инициализируем drop zone для новой команды
                initDropZone(document.querySelector(`[data-team-id="${teamId}"] .team-drop-zone`));
                
                // Инициализируем данные команды
                teamsData[teamId] = {
                    name: `Экипаж ${teamCounter}`,
                    catcher: '',
                    driver: '',
                    requests: [],
                    animals: []
                };
            }

            function removeTeam(teamId) {
                const teamElement = document.querySelector(`[data-team-id="${teamId}"]`);
                
                // Возвращаем заявки обратно в доступные
                const requests = teamElement.querySelectorAll('.request-card');
                const availableContainer = document.getElementById('available-requests');
                
                requests.forEach(request => {
                    availableContainer.appendChild(request);
                });
                
                // Возвращаем животных обратно в правую колонку
                const animals = teamElement.querySelectorAll('.animal-card');
                const animalsContainer = document.querySelector('.space-y-3'); // Контейнер животных
                
                animals.forEach(animal => {
                    if (animalsContainer) {
                        animalsContainer.appendChild(animal);
                    }
                });
                
                // Удаляем команду
                teamElement.remove();
                delete teamsData[teamId];
                
                // Показываем placeholder если команд нет
                if (Object.keys(teamsData).length === 0) {
                    document.getElementById('no-teams').style.display = 'block';
                }
            }

            function initDragAndDrop() {
                // Инициализация для существующих заявок
                document.querySelectorAll('.request-card').forEach(initDraggableItem);
                // Инициализация для животных
                document.querySelectorAll('.animal-card').forEach(initDraggableItem);
            }

            function initDraggableItem(itemElement) {
                itemElement.addEventListener('dragstart', function(e) {
                    // Передаем данные о типе и ID элемента
                    const itemData = {
                        type: this.dataset.type || 'request',
                        id: this.dataset.requestId || this.dataset.animalId
                    };
                    e.dataTransfer.setData('text/plain', JSON.stringify(itemData));
                    this.classList.add('dragging');
                });
                
                itemElement.addEventListener('dragend', function(e) {
                    this.classList.remove('dragging');
                });
            }

            function initDropZone(dropZone) {
                dropZone.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    this.classList.add('drag-over');
                });
                
                dropZone.addEventListener('dragleave', function(e) {
                    this.classList.remove('drag-over');
                });
                
                dropZone.addEventListener('drop', function(e) {
                    e.preventDefault();
                    this.classList.remove('drag-over');
                    
                    const itemData = JSON.parse(e.dataTransfer.getData('text/plain'));
                    const teamId = this.dataset.teamId;
                    
                    let itemElement;
                    if (itemData.type === 'animal') {
                        itemElement = document.querySelector(`[data-animal-id="${itemData.id}"]`);
                    } else {
                        itemElement = document.querySelector(`[data-request-id="${itemData.id}"]`);
                    }
                    
                    if (itemElement && teamId) {
                        // Добавляем элемент в команду
                        const requestsContainer = this.querySelector('.team-requests');
                        requestsContainer.appendChild(itemElement);
                        
                        // Скрываем empty state
                        this.querySelector('.empty-state').style.display = 'none';
                        
                        // Обновляем данные команды
                        if (!teamsData[teamId]) {
                            teamsData[teamId] = { requests: [], animals: [] };
                        }
                        
                        if (itemData.type === 'animal') {
                            if (!teamsData[teamId].animals) {
                                teamsData[teamId].animals = [];
                            }
                            if (!teamsData[teamId].animals.includes(itemData.id)) {
                                teamsData[teamId].animals.push(itemData.id);
                            }
                        } else {
                            if (!teamsData[teamId].requests) {
                                teamsData[teamId].requests = [];
                            }
                            if (!teamsData[teamId].requests.includes(itemData.id)) {
                                teamsData[teamId].requests.push(itemData.id);
                            }
                        }
                        
                        updateTeamStats(teamId);
                    }
                });
            }

            function updateTeamStats(teamId) {
                const teamElement = document.querySelector(`[data-team-id="${teamId}"]`);
                const requests = teamElement.querySelectorAll('.request-card');
                const animals = teamElement.querySelectorAll('.animal-card');
                
                const statsText = [];
                if (requests.length > 0) {
                    statsText.push(`Заявок: ${requests.length}`);
                }
                if (animals.length > 0) {
                    statsText.push(`Животных: ${animals.length}`);
                }
                
                const finalText = statsText.length > 0 ? statsText.join(', ') : 'Пусто';
                teamElement.querySelector('.requests-count').textContent = finalText;
            }

            function savePlan() {
                // Собираем данные формы
                const planName = document.getElementById('plan_name').value;
                const plannedDate = document.getElementById('planned_date').value;
                const planNotes = document.getElementById('plan_notes').value;
                
                if (!planName.trim()) {
                    alert('Укажите название плана');
                    return;
                }
                
                if (Object.keys(teamsData).length === 0) {
                    alert('Создайте хотя бы один экипаж');
                    return;
                }
                
                // Собираем данные команд
                const routes = [];
                
                Object.keys(teamsData).forEach(teamId => {
                    const teamElement = document.querySelector(`[data-team-id="${teamId}"]`);
                    const teamName = teamElement.querySelector('.team-name').value;
                    const catcher = teamElement.querySelector('.team-catcher').value;
                    const driver = teamElement.querySelector('.team-driver').value;
                    const requests = Array.from(teamElement.querySelectorAll('.request-card')).map(req => req.dataset.requestId);
                    const animals = Array.from(teamElement.querySelectorAll('.animal-card')).map(animal => animal.dataset.animalId);
                    
                    if (requests.length > 0 || animals.length > 0) {
                        routes.push({
                            name: teamName,
                            assigned_user_id: catcher || null,
                            driver_user_id: driver || null,
                            start_time: null,
                            priority: 5,
                            notes: '',
                            requests: requests,
                            animals: animals
                        });
                    }
                });
                
                if (routes.length === 0) {
                    alert('Добавьте заявки или животных в экипажи');
                    return;
                }
                
                // Заполняем скрытую форму
                document.getElementById('form_planned_date').value = plannedDate;
                document.getElementById('form_name').value = planName;
                document.getElementById('form_notes').value = planNotes;
                document.getElementById('form_routes_data').value = JSON.stringify(routes);
                
                // Отправляем форму
                document.getElementById('save-form').submit();
            }

            // Инициализация drop zones для всех команд
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.team-drop-zone').forEach(initDropZone);
            });

            // Модальное окно для фото
            function openPhotoModal(photoUrl, secondPhotoUrl) {
                const modal = document.getElementById('photoModal');
                const modalImage = document.getElementById('modalImage');
                modalImage.src = photoUrl;
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            function closePhotoModal() {
                const modal = document.getElementById('photoModal');
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }

            // Закрытие по Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closePhotoModal();
                }
            });
        </script>

        <!-- Модальное окно для просмотра фото -->
        <div id="photoModal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4" onclick="closePhotoModal()">
            <div class="relative max-w-4xl max-h-full" onclick="event.stopPropagation()">
                <button onclick="closePhotoModal()" class="absolute top-4 right-4 text-white bg-black bg-opacity-50 rounded-full p-2 hover:bg-opacity-75 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <img id="modalImage" src="" alt="Фото животного" class="max-w-full max-h-[90vh] object-contain rounded-lg">
            </div>
        </div>
    @endsection