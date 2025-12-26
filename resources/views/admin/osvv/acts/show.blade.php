@extends('admin.layout')

@section('title', 'Акт отлова #' . $act->act_number)

@section('content')
<div class="space-y-6">
    <!-- Верхняя панель с заголовком и кнопками действий -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-bold text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Акт отлова #{{ $act->act_number }}
                </h3>
                <div class="flex space-x-2">
                    <a href="{{ route('admin.osvv.acts.edit', $act->id) }}" class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-white/20 active:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                        Редактировать
                    </a>
                    <a href="{{ route('admin.osvv.acts.index') }}" class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-white/20 active:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                        </svg>
                        К списку
                    </a>
                </div>
            </div>
        </div>
        
        <div class="px-6 py-4">
            <!-- Статус акта -->
            <div class="mb-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Статус акта</h4>
                        @php
                            $statusClass = 'bg-gray-100 text-gray-800';
                            $statusIcon = 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
                            
                            switch($act->status) {
                                case 'created':
                                    $statusClass = 'bg-blue-100 text-blue-800 border-blue-200';
                                    $statusIcon = 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z';
                                    break;
                                case 'completed':
                                    $statusClass = 'bg-green-100 text-green-800 border-green-200';
                                    $statusIcon = 'M5 13l4 4L19 7';
                                    break;
                                case 'cancelled':
                                    $statusClass = 'bg-red-100 text-red-800 border-red-200';
                                    $statusIcon = 'M6 18L18 6M6 6l12 12';
                                    break;
                            }
                            
                            $statusText = App\Models\CaptureAct::getStatusList()[$act->status] ?? $act->status;
                        @endphp
                        <span class="px-4 py-2 inline-flex items-center text-sm leading-5 font-semibold rounded-full border {{ $statusClass }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $statusIcon }}" />
                            </svg>
                            {{ $statusText }}
                        </span>
                    </div>
                    
                    <!-- Действия -->
                    <div class="flex space-x-2">
                        <!-- Печать акта -->
                        <a href="{{ route('admin.osvv.acts.pdf', $act->id) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            Печать акта
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Детали акта -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- Информация о животном -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="border-b border-gray-200">
                    <div class="px-6 py-4 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        <h4 class="text-base font-semibold text-gray-800">Информация о животном</h4>
                    </div>
                </div>
                <div class="px-6 py-4">
                    <dl>
                        <!-- Вид животного -->
                        @if($act->animal_type)
                        <div class="flex py-2">
                            <dt class="w-1/3 flex items-center text-sm font-medium text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                Вид:
                            </dt>
                            <dd class="w-2/3 text-sm text-gray-900 font-medium">
                                @if($act->animal_type === 'cat')
                                    Кошка
                                @elseif($act->animal_type === 'dog')
                                    Собака
                                @else
                                    {{ $act->animal_type }}
                                @endif
                            </dd>
                        </div>
                        @endif
                        
                        <!-- Пол животного -->
                        @if($act->animal_gender)
                        <div class="flex py-2 border-t border-gray-100">
                            <dt class="w-1/3 flex items-center text-sm font-medium text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Пол:
                            </dt>
                            <dd class="w-2/3 text-sm text-gray-900 font-medium">
                                {{ $act->animal_gender === 'male' ? 'Самец' : ($act->animal_gender === 'female' ? 'Самка' : $act->animal_gender) }}
                            </dd>
                        </div>
                        @endif
                        
                        <!-- Порода -->
                        @if($act->animal_breed)
                        <div class="flex py-2 border-t border-gray-100">
                            <dt class="w-1/3 flex items-center text-sm font-medium text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                Порода:
                            </dt>
                            <dd class="w-2/3 text-sm text-gray-900 font-medium">{{ $act->animal_breed }}</dd>
                        </div>
                        @endif
                        
                        <!-- Окрас -->
                        @if($act->animal_color)
                        <div class="flex py-2 border-t border-gray-100">
                            <dt class="w-1/3 flex items-center text-sm font-medium text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                                </svg>
                                Окрас:
                            </dt>
                            <dd class="w-2/3 text-sm text-gray-900 font-medium">{{ $act->animal_color }}</dd>
                        </div>
                        @endif
                        
                        <!-- Размер -->
                        @if($act->animal_size)
                        <div class="flex py-2 border-t border-gray-100">
                            <dt class="w-1/3 flex items-center text-sm font-medium text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                </svg>
                                Размер:
                            </dt>
                            <dd class="w-2/3 text-sm text-gray-900 font-medium">
                                @if($act->animal_size === 'small')
                                    Маленький
                                @elseif($act->animal_size === 'medium')
                                    Средний
                                @elseif($act->animal_size === 'large')
                                    Крупный
                                @else
                                    {{ $act->animal_size }}
                                @endif
                            </dd>
                        </div>
                        @endif
                        
                        <!-- Способ отлова -->
                        @if($act->capturing_method)
                        <div class="flex py-2 border-t border-gray-100">
                            <dt class="w-1/3 flex items-center text-sm font-medium text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243 4.243 3 3 0 004.243-4.243zm0-5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z" />
                                </svg>
                                Способ отлова:
                            </dt>
                            <dd class="w-2/3 text-sm text-gray-900 font-medium">
                                @if($act->capturing_method === 'net')
                                    Сеть
                                @elseif($act->capturing_method === 'cage')
                                    Клетка-ловушка
                                @elseif($act->capturing_method === 'pole')
                                    Сачок
                                @elseif($act->capturing_method === 'hand')
                                    Руками
                                @else
                                    {{ $act->capturing_method }}
                                @endif
                            </dd>
                        </div>
                        @endif
                    </dl>
                    
                    <!-- Особые приметы -->
                    @if($act->animal_features)
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <h5 class="text-sm font-medium text-gray-500 mb-2 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Особые приметы:
                        </h5>
                        <div class="bg-gray-50 rounded-md p-3">
                            <p class="text-sm text-gray-700 whitespace-pre-line">{{ $act->animal_features }}</p>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Особенности поведения -->
                    @if($act->animal_behavior)
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <h5 class="text-sm font-medium text-gray-500 mb-2 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Особенности поведения:
                        </h5>
                        <div class="bg-gray-50 rounded-md p-3">
                            <p class="text-sm text-gray-700">{{ $act->animal_behavior }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Примечания -->
            @if($act->notes)
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="border-b border-gray-200">
                    <div class="px-6 py-4 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                        </svg>
                        <h4 class="text-base font-semibold text-gray-800">Примечания</h4>
                    </div>
                </div>
                <div class="px-6 py-4">
                    <div class="bg-gray-50 rounded-md p-3">
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $act->notes }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
        
        <!-- Правая колонка: основная информация -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Основная информация -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="border-b border-gray-200">
                    <div class="px-6 py-4 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h4 class="text-base font-semibold text-gray-800">Основная информация</h4>
                    </div>
                </div>
                <div class="px-6 py-4">
                    <dl>
                        <!-- Номер акта -->
                        <div class="flex py-2">
                            <dt class="w-1/3 flex items-center text-sm font-medium text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                </svg>
                                Номер:
                            </dt>
                            <dd class="w-2/3 text-sm text-gray-900 font-medium">{{ $act->act_number }}</dd>
                        </div>
                        
                        <!-- Дата создания -->
                        <div class="flex py-2 border-t border-gray-100">
                            <dt class="w-1/3 flex items-center text-sm font-medium text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Создан:
                            </dt>
                            <dd class="w-2/3 text-sm text-gray-900 font-medium">{{ $act->created_at->format('d.m.Y H:i') }}</dd>
                        </div>
                        
                        <!-- Дата и время отлова -->
                        <div class="flex py-2 border-t border-gray-100">
                            <dt class="w-1/3 flex items-center text-sm font-medium text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Отлов:
                            </dt>
                            <dd class="w-2/3 text-sm text-gray-900 font-medium">{{ $act->getFormattedCaptureDateTime() }}</dd>
                        </div>
                        
                        <!-- Отловщик -->
                        <div class="flex py-2 border-t border-gray-100">
                            <dt class="w-1/3 flex items-center text-sm font-medium text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Отловщик:
                            </dt>
                            <dd class="w-2/3 text-sm text-gray-900 font-medium">
                                {{ $act->user ? $act->user->name : 'Не назначен' }}
                            </dd>
                        </div>
                        
                        <!-- Количество отловленных животных -->
                        <div class="flex py-2 border-t border-gray-100">
                            <dt class="w-1/3 flex items-center text-sm font-medium text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                </svg>
                                Количество животных:
                            </dt>
                            <dd class="w-2/3 text-sm text-gray-900 font-medium">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $act->animals_count ?? 1 }} {{ $act->animals_count > 1 ? 'животных' : 'животное' }}
                                </span>
                                @if($act->status === 'completed' && $act->animals_count > 0)
                                    <span class="ml-2 text-xs text-green-600">✓ Добавлено в систему управления</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
            
            <!-- Адрес отлова -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="border-b border-gray-200">
                    <div class="px-6 py-4 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <h4 class="text-base font-semibold text-gray-800">Адрес отлова</h4>
                    </div>
                </div>
                <div class="px-6 py-4">
                    <p class="text-sm text-gray-700">{{ $act->capture_location }}</p>
                    
                    <!-- Связанная заявка -->
                    @if($act->osvvRequest)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <a href="{{ route('admin.osvv.show', $act->osvvRequest->id) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                            </svg>
                            Связанная заявка ОСВВ #{{ $act->osvvRequest->id }}
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 