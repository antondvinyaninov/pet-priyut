@extends('admin.layout')

@section('header', 'Панель управления')

@section('content')
    <div class="space-y-6">
        <!-- Общая статистика -->
        <div class="bg-white shadow rounded-lg">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                <h3 class="text-xl font-bold text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    🐕 Общая статистика
                </h3>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-6">
                    <!-- Всего активных животных -->
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <div class="text-2xl font-bold text-blue-600">{{ $totalAnimals }}</div>
                                <div class="text-sm text-blue-600">Всего активных</div>
                            </div>
                        </div>
                    </div>

                    <!-- По ОСВВ -->
                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <div class="text-2xl font-bold text-green-600">{{ $animalsWithOSVV }}</div>
                                <div class="text-sm text-green-600">По ОСВВ</div>
                            </div>
                        </div>
                    </div>

                    <!-- В приюте -->
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V9.375c0-.621.504-1.125 1.125-1.125h.75c.621 0 1.125.504 1.125 1.125v11.25c-4.5 0-8.25-3.875-8.25-8.615 0-1.58.42-3.065 1.157-4.359" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <div class="text-2xl font-bold text-purple-600">{{ $animalsInShelter }}</div>
                                <div class="text-sm text-purple-600">В приюте</div>
                            </div>
                        </div>
                    </div>

                    <!-- Выпущено -->
                    <div class="bg-emerald-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <div class="text-2xl font-bold text-emerald-600">{{ $releasedAnimals }}</div>
                                <div class="text-sm text-emerald-600">Выпущено</div>
                            </div>
                        </div>
                    </div>

                    <!-- Пристроено -->
                    <div class="bg-orange-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <div class="text-2xl font-bold text-orange-600">{{ $adoptedAnimals }}</div>
                                <div class="text-sm text-orange-600">Пристроено</div>
                            </div>
                        </div>
                    </div>

                    <!-- Готовы к выпуску -->
                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <div class="text-2xl font-bold text-yellow-600">{{ $readyForRelease }}</div>
                                <div class="text-sm text-yellow-600">Готовы к выпуску</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Разделы -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Раздел ОСВВ -->
            <div class="bg-white shadow rounded-lg">
                <div class="bg-green-600 px-6 py-4">
                    <h3 class="text-lg font-semibold text-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Животные по ОСВВ
                    </h3>
                </div>
                <div class="p-6">
                    <p class="text-gray-600 mb-4">
                        Животные, поступившие по заявкам службы отлова безнадзорных животных
                    </p>
                    <div class="flex justify-between items-center mb-4">
                        <div class="text-2xl font-bold text-green-600">{{ $animalsWithOSVV }}</div>
                        <a href="{{ route('admin.animal-registry.osvv') }}" 
                           class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            Перейти к списку
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Раздел приюта -->
            <div class="bg-white shadow rounded-lg">
                <div class="bg-purple-600 px-6 py-4">
                    <h3 class="text-lg font-semibold text-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V9.375c0-.621.504-1.125 1.125-1.125h.75c.621 0 1.125.504 1.125 1.125v11.25c-4.5 0-8.25-3.875-8.25-8.615 0-1.58.42-3.065 1.157-4.359" />
                        </svg>
                        Животные в приюте
                    </h3>
                </div>
                <div class="p-6">
                    <p class="text-gray-600 mb-4">
                        Животные, поступившие напрямую в приют без связи с заявками ОСВВ
                    </p>
                    <div class="flex justify-between items-center mb-4">
                        <div class="text-2xl font-bold text-purple-600">{{ $animalsInShelter }}</div>
                        <a href="{{ route('admin.animal-registry.shelter') }}" 
                           class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
                            Перейти к списку
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Общая статистика -->
            <div class="bg-white shadow rounded-lg">
                <div class="bg-indigo-600 px-6 py-4">
                    <h3 class="text-lg font-semibold text-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                        Система
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Пользователи:</span>
                            <span class="font-semibold">{{ $totalUsers }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Заявки ОСВВ:</span>
                            <span class="font-semibold">{{ $totalOsvvRequests }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Ожидают:</span>
                            <span class="font-semibold text-yellow-600">{{ $pendingOsvvRequests }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Статистика по типам -->
        @if($animalsByType->count() > 0)
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Статистика по типам животных</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach(['dog' => 'Собаки', 'cat' => 'Кошки', 'other' => 'Другие'] as $type => $label)
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm text-gray-600">{{ $label }}</div>
                                <div class="text-2xl font-bold text-gray-900">
                                    {{ $animalsByType->get($type)->count ?? 0 }}
                                </div>
                            </div>
                            <div class="text-3xl">
                                @if($type === 'dog') 🐕
                                @elseif($type === 'cat') 🐱
                                @else 🐾
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Последние поступления -->
        @if($recentAnimals->count() > 0)
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Последние поступления</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($recentAnimals as $animal)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    @if($animal->type === 'dog')
                                        🐕
                                    @elseif($animal->type === 'cat')
                                        🐱
                                    @else
                                        🐾
                                    @endif
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">
                                        @if($animal->cage_number)
                                            Вольер №{{ $animal->cage_number }}
                                        @else
                                            {{ $animal->name ?? 'Без имени' }}
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        @if($animal->osvvRequest)
                                            ОСВВ #{{ $animal->osvv_request_id }}
                                        @else
                                            Приют
                                        @endif
                                        • Поступил 
                                        @php
                                            $daysInShelter = abs(round(now()->diffInDays($animal->arrived_at)));
                                        @endphp
                                        {{ $daysInShelter }} 
                                        @if($daysInShelter == 1)
                                            день назад
                                        @elseif($daysInShelter >= 2 && $daysInShelter <= 4)
                                            дня назад
                                        @else
                                            дней назад
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="text-sm text-gray-500">
                                    @if($animal->currentStage)
                                        {{ $animal->currentStage->name }}
                                    @else
                                        Без этапа
                                    @endif
                                </div>
                                <a href="{{ route('admin.animals.show', $animal) }}" 
                                   class="text-blue-600 hover:text-blue-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
@endsection 