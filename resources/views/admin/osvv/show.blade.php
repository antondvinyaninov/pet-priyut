@extends('admin.layout')

@section('header', 'Детали заявки ОСВВ №' . $osvvRequest->id)

@section('content')
    <div class="space-y-6">
        <!-- Верхняя панель с кнопками действий и статусом -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Заявка #{{ $osvvRequest->id }}
                    </h3>
                    <div class="flex space-x-2">
                        <a href="{{ route('admin.osvv.edit', $osvvRequest) }}" class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-white/20 active:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                            </svg>
                            Редактировать
                        </a>
                        @if($osvvRequest->status != 'cancelled' && $osvvRequest->captureActs->isEmpty())
                        <a href="{{ route('admin.osvv.acts.create', ['osvv_request_id' => $osvvRequest->id]) }}" class="inline-flex items-center px-4 py-2 bg-green-600/20 backdrop-blur-sm border border-green-400/30 rounded-md font-medium text-xs text-green-100 uppercase tracking-widest hover:bg-green-600/30 active:bg-green-600/40 focus:outline-none focus:ring-2 focus:ring-green-400/50 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Создать акт отлова
                        </a>
                        @elseif($osvvRequest->captureActs->isNotEmpty())
                        <a href="{{ route('admin.osvv.acts.show', $osvvRequest->captureActs->first()->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600/20 backdrop-blur-sm border border-blue-400/30 rounded-md font-medium text-xs text-blue-100 uppercase tracking-widest hover:bg-blue-600/30 active:bg-blue-600/40 focus:outline-none focus:ring-2 focus:ring-blue-400/50 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Просмотр акта отлова
                        </a>
                        @endif
                        <a href="{{ route('admin.osvv.index') }}" class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-white/20 active:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                            </svg>
                            К списку заявок
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Объединённая сводка по заявке -->
            <div class="px-6 py-4">
                <!-- Информация о статусе с возможностью изменения -->
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Текущий статус</h4>
                            @php
                                $statusClass = 'bg-gray-100 text-gray-800';
                                $statusText = 'Неизвестно';
                                $statusIcon = 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
                                $progressPercent = 0;
                                
                                switch($osvvRequest->status) {
                                    case 'new':
                                        $statusClass = 'bg-blue-100 text-blue-800 border-blue-200';
                                        $statusText = 'Новая заявка';
                                        $statusIcon = 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z';
                                        $progressPercent = 10;
                                        break;
                                    case 'processing':
                                        $statusClass = 'bg-yellow-100 text-yellow-800 border-yellow-200';
                                        $statusText = 'В обработке';
                                        $statusIcon = 'M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12';
                                        $progressPercent = 20;
                                        break;
                                    case 'capture_scheduled':
                                        $statusClass = 'bg-purple-100 text-purple-800 border-purple-200';
                                        $statusText = 'Запланирован отлов';
                                        $statusIcon = 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z';
                                        $progressPercent = 30;
                                        break;
                                    case 'captured':
                                        $statusClass = 'bg-indigo-100 text-indigo-800 border-indigo-200';
                                        $statusText = 'Животное отловлено';
                                        $statusIcon = 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z';
                                        $progressPercent = 40;
                                        break;
                                    case 'in_shelter':
                                        $statusClass = 'bg-cyan-100 text-cyan-800 border-cyan-200';
                                        $statusText = 'В приюте';
                                        $statusIcon = 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6';
                                        $progressPercent = 50;
                                        break;
                                    case 'sterilized':
                                        $statusClass = 'bg-teal-100 text-teal-800 border-teal-200';
                                        $statusText = 'Стерилизовано';
                                        $statusIcon = 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z';
                                        $progressPercent = 60;
                                        break;
                                    case 'vaccinated':
                                        $statusClass = 'bg-green-100 text-green-800 border-green-200';
                                        $statusText = 'Вакцинировано';
                                        $statusIcon = 'M15.58 15.58A8.252 8.252 0 0112 16.5c-3.9 0-7.18-2.67-8.08-6.26-.19-.83.52-1.24 1.11-.72l.99 1.03c.83.87 2.21.91 3.07.13l.45-.39c.44-.36.70-.92.73-1.51l.14-2.5-2.09-.99a1 1 0 00-1.2.32C7.51 5.03 8.52 5 12 5c.5 0 .94.03 1.35.08.21-.95.86-1.76 1.74-2.12.3-.15.6-.22.9-.22.74 0 1.4.45 1.67 1.11.7 1.61-.93 3.2-2.5 3.32l-1.12 7.69a.69.69 0 001.31.39l.59-.89c.32-.5.96-.69 1.5-.51m-8.39-6a3.6 3.6 0 00-3.23 3.5c0 .6.18 1.16.5 1.63m9.77-5.39l.58-.76a1.5 1.5 0 012.4 1.8l-3.38 4.5a1.5 1.5 0 01-2.4 0L13 11.8a1.5 1.5 0 012.4-1.8l.56.75';
                                        $progressPercent = 70;
                                        break;
                                    case 'ready_for_return':
                                        $statusClass = 'bg-lime-100 text-lime-800 border-lime-200';
                                        $statusText = 'Готово к возврату';
                                        $statusIcon = 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z';
                                        $progressPercent = 80;
                                        break;
                                    case 'returned':
                                        $statusClass = 'bg-amber-100 text-amber-800 border-amber-200';
                                        $statusText = 'Возвращено';
                                        $statusIcon = 'M15 19l-7-7 7-7';
                                        $progressPercent = 90;
                                        break;
                                    case 'completed':
                                        $statusClass = 'bg-emerald-100 text-emerald-800 border-emerald-200';
                                        $statusText = 'Завершено';
                                        $statusIcon = 'M5 13l4 4L19 7';
                                        $progressPercent = 100;
                                        break;
                                    case 'cancelled':
                                        $statusClass = 'bg-red-100 text-red-800 border-red-200';
                                        $statusText = 'Отменено';
                                        $statusIcon = 'M6 18L18 6M6 6l12 12';
                                        $progressPercent = 0;
                                        break;
                                }
                            @endphp
                            <span class="px-4 py-2 inline-flex items-center text-sm leading-5 font-semibold rounded-full border {{ $statusClass }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $statusIcon }}" />
                                </svg>
                                {{ $statusText }}
                            </span>
                        </div>
                        <div>
                            <button type="button" id="changeStatusBtn" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                                </svg>
                                Изменить статус
                            </button>
                        </div>
                    </div>
                    
                    <!-- Прогресс-бар состояния заявки -->
                    @if($osvvRequest->status != 'cancelled')
                    <div class="mb-4">
                        <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 overflow-hidden">
                            <div class="bg-indigo-600 h-2.5 rounded-full" style="width: {{ $progressPercent }}%"></div>
                        </div>
                        <div class="flex justify-between text-xs text-gray-500 mt-1">
                            <span>Новая</span>
                            <span>В работе</span>
                            <span>Завершено</span>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Форма изменения статуса (скрыта по умолчанию) -->
                    <div id="statusForm" class="mt-4 hidden">
                        <form action="{{ route('admin.osvv.change-status', $osvvRequest) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="col-span-3">
                                    <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="new" {{ $osvvRequest->status === 'new' ? 'selected' : '' }}>Новая заявка</option>
                                        <option value="processing" {{ $osvvRequest->status === 'processing' ? 'selected' : '' }}>В обработке</option>
                                        <option value="capture_scheduled" {{ $osvvRequest->status === 'capture_scheduled' ? 'selected' : '' }}>Запланирован отлов</option>
                                        <option value="captured" {{ $osvvRequest->status === 'captured' ? 'selected' : '' }}>Животное отловлено</option>
                                        <option value="in_shelter" {{ $osvvRequest->status === 'in_shelter' ? 'selected' : '' }}>В приюте</option>
                                        <option value="sterilized" {{ $osvvRequest->status === 'sterilized' ? 'selected' : '' }}>Стерилизовано</option>
                                        <option value="vaccinated" {{ $osvvRequest->status === 'vaccinated' ? 'selected' : '' }}>Вакцинировано</option>
                                        <option value="ready_for_return" {{ $osvvRequest->status === 'ready_for_return' ? 'selected' : '' }}>Готово к возврату</option>
                                        <option value="returned" {{ $osvvRequest->status === 'returned' ? 'selected' : '' }}>Возвращено</option>
                                        <option value="completed" {{ $osvvRequest->status === 'completed' ? 'selected' : '' }}>Завершено</option>
                                        <option value="cancelled" {{ $osvvRequest->status === 'cancelled' ? 'selected' : '' }}>Отменено</option>
                                    </select>
                                </div>
                                <div class="col-span-1">
                                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300">
                                        Сохранить
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Ключевые даты и показатели заявки -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <!-- Дата создания заявки -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
                        <div class="flex items-center justify-center mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            <h5 class="text-sm font-medium text-blue-700">Дата создания</h5>
                            <button type="button" id="editDateBtn" class="ml-2 text-xs text-blue-600 hover:text-blue-800">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                </svg>
                            </button>
                        </div>
                        <div class="text-lg font-bold text-blue-900">{{ $osvvRequest->created_at->format('d.m.Y') }}</div>
                        <div class="text-xs text-blue-600">{{ $osvvRequest->created_at->format('H:i') }}</div>
                        <div class="text-xs text-blue-500 mt-1">{{ $osvvRequest->created_at->diffForHumans() }}</div>
                    </div>

                    <!-- Дедлайн выезда -->
                    <div class="
                        @if($osvvRequest->deadline_date)
                            @if($osvvRequest->deadline_date < now() && !in_array($osvvRequest->status, ['completed', 'cancelled']))
                                bg-red-50 border-red-200
                            @elseif($osvvRequest->deadline_date < now()->addDay() && !in_array($osvvRequest->status, ['completed', 'cancelled']))
                                bg-orange-50 border-orange-200
                            @else
                                bg-emerald-50 border-emerald-200
                            @endif
                        @else
                            bg-gray-50 border-gray-200
                        @endif
                        border rounded-lg p-4 text-center
                    ">
                        <div class="flex items-center justify-center mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 
                                @if($osvvRequest->deadline_date)
                                    @if($osvvRequest->deadline_date < now() && !in_array($osvvRequest->status, ['completed', 'cancelled']))
                                        text-red-600
                                    @elseif($osvvRequest->deadline_date < now()->addDay() && !in_array($osvvRequest->status, ['completed', 'cancelled']))
                                        text-orange-600
                                    @else
                                        text-emerald-600
                                    @endif
                                @else
                                    text-gray-400
                                @endif
                                mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h5 class="text-sm font-medium 
                                @if($osvvRequest->deadline_date)
                                    @if($osvvRequest->deadline_date < now() && !in_array($osvvRequest->status, ['completed', 'cancelled']))
                                        text-red-700
                                    @elseif($osvvRequest->deadline_date < now()->addDay() && !in_array($osvvRequest->status, ['completed', 'cancelled']))
                                        text-orange-700
                                    @else
                                        text-emerald-700
                                    @endif
                                @else
                                    text-gray-600
                                @endif
                            ">Дедлайн</h5>
                        </div>
                        @if($osvvRequest->deadline_date)
                            <div class="text-lg font-bold 
                                @if($osvvRequest->deadline_date < now() && !in_array($osvvRequest->status, ['completed', 'cancelled']))
                                    text-red-900
                                @elseif($osvvRequest->deadline_date < now()->addDay() && !in_array($osvvRequest->status, ['completed', 'cancelled']))
                                    text-orange-900
                                @else
                                    text-emerald-900
                                @endif
                            ">{{ $osvvRequest->deadline_date->format('d.m.Y') }}</div>
                            <div class="text-xs 
                                @if($osvvRequest->deadline_date < now() && !in_array($osvvRequest->status, ['completed', 'cancelled']))
                                    text-red-600
                                @elseif($osvvRequest->deadline_date < now()->addDay() && !in_array($osvvRequest->status, ['completed', 'cancelled']))
                                    text-orange-600
                                @else
                                    text-emerald-600
                                @endif
                            ">
                                @if($osvvRequest->deadline_date < now() && !in_array($osvvRequest->status, ['completed', 'cancelled']))
                                    Просрочен
                                @elseif($osvvRequest->deadline_date < now()->addDay() && !in_array($osvvRequest->status, ['completed', 'cancelled']))
                                    Срочно!
                                @else
                                    {{ $osvvRequest->deadline_date->diffForHumans() }}
                                @endif
                            </div>
                        @else
                            <div class="text-lg font-bold text-gray-400">—</div>
                            <div class="text-xs text-gray-500">Без дедлайна</div>
                        @endif
                    </div>

                    <!-- Дата последнего выезда -->
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 text-center">
                        <div class="flex items-center justify-center mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                            </svg>
                            <h5 class="text-sm font-medium text-purple-700">Последний выезд</h5>
                        </div>
                        @if($osvvRequest->departure_date)
                            <div class="text-lg font-bold text-purple-900">{{ \Carbon\Carbon::parse($osvvRequest->departure_date)->format('d.m.Y') }}</div>
                            <div class="text-xs text-purple-600">{{ \Carbon\Carbon::parse($osvvRequest->departure_date)->format('H:i') }}</div>
                            <div class="text-xs text-purple-500 mt-1">{{ \Carbon\Carbon::parse($osvvRequest->departure_date)->diffForHumans() }}</div>
                        @else
                            <div class="text-lg font-bold text-gray-400">—</div>
                            <div class="text-xs text-gray-500">Нет выездов</div>
                        @endif
                    </div>

                    <!-- Статус заявки -->
                    <div class="
                        @switch($osvvRequest->status)
                            @case('new') bg-blue-50 border-blue-200 @break
                            @case('in_progress') bg-yellow-50 border-yellow-200 @break
                            @case('completed') bg-green-50 border-green-200 @break
                            @case('cancelled') bg-red-50 border-red-200 @break
                            @default bg-gray-50 border-gray-200
                        @endswitch
                        border rounded-lg p-4 text-center
                    ">
                        <div class="flex items-center justify-center mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 
                                @switch($osvvRequest->status)
                                    @case('new') text-blue-600 @break
                                    @case('in_progress') text-yellow-600 @break
                                    @case('completed') text-green-600 @break
                                    @case('cancelled') text-red-600 @break
                                    @default text-gray-600
                                @endswitch
                                mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <h5 class="text-sm font-medium 
                                @switch($osvvRequest->status)
                                    @case('new') text-blue-700 @break
                                    @case('in_progress') text-yellow-700 @break
                                    @case('completed') text-green-700 @break
                                    @case('cancelled') text-red-700 @break
                                    @default text-gray-700
                                @endswitch
                            ">Статус</h5>
                        </div>
                        <div class="text-lg font-bold 
                            @switch($osvvRequest->status)
                                @case('new') text-blue-900 @break
                                @case('in_progress') text-yellow-900 @break
                                @case('completed') text-green-900 @break
                                @case('cancelled') text-red-900 @break
                                @default text-gray-900
                            @endswitch
                        ">
                            @switch($osvvRequest->status)
                                @case('new') Новая @break
                                @case('in_progress') В работе @break
                                @case('completed') Выполнена @break
                                @case('cancelled') Отменена @break
                                @default {{ $osvvRequest->status }}
                            @endswitch
                        </div>
                        <div class="text-xs 
                            @switch($osvvRequest->status)
                                @case('new') text-blue-600 @break
                                @case('in_progress') text-yellow-600 @break
                                @case('completed') text-green-600 @break
                                @case('cancelled') text-red-600 @break
                                @default text-gray-600
                            @endswitch
                        ">{{ $osvvRequest->updated_at->diffForHumans() }}</div>
                    </div>
                </div>

                <!-- Дополнительные показатели -->
                <div class="hidden grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
                    <!-- Количество животных -->
                    <div class="text-center p-3 bg-amber-50 rounded-lg border border-amber-200">
                        <div class="text-xl font-bold text-amber-600">{{ $osvvRequest->animals_count ?? 1 }}</div>
                        <div class="text-xs text-amber-700 font-medium">{{ trans_choice('Животное|Животных|Животных', $osvvRequest->animals_count ?? 1) }}</div>
                    </div>

                    <!-- Количество адресов -->
                    @php $totalAddresses = 1 + ($osvvRequest->locations ? count($osvvRequest->locations) : 0); @endphp
                    <div class="text-center p-3 bg-indigo-50 rounded-lg border border-indigo-200">
                        <div class="text-xl font-bold text-indigo-600">{{ $totalAddresses }}</div>
                        <div class="text-xs text-indigo-700 font-medium">{{ trans_choice('Адрес|Адреса|Адресов', $totalAddresses) }}</div>
                    </div>

                    <!-- Статус укуса -->
                    <div class="text-center p-3 {{ $osvvRequest->has_bite ? 'bg-red-50 border-red-200' : 'bg-gray-50 border-gray-200' }} rounded-lg border">
                        <div class="text-xl font-bold {{ $osvvRequest->has_bite ? 'text-red-600' : 'text-gray-400' }}">
                            {{ $osvvRequest->has_bite ? '⚠️' : '✅' }}
                        </div>
                        <div class="text-xs {{ $osvvRequest->has_bite ? 'text-red-700' : 'text-gray-600' }} font-medium">
                            {{ $osvvRequest->has_bite ? 'Был укус' : 'Без укуса' }}
                        </div>
                    </div>

                    <!-- Количество актов отлова -->
                    <div class="text-center p-3 bg-cyan-50 rounded-lg border border-cyan-200">
                        <div class="text-xl font-bold text-cyan-600">{{ $osvvRequest->captureActs->count() }}</div>
                        <div class="text-xs text-cyan-700 font-medium">{{ trans_choice('Акт|Акта|Актов', $osvvRequest->captureActs->count()) }} отлова</div>
                    </div>

                    <!-- Автор заявки -->
                    <div class="text-center p-3 bg-teal-50 rounded-lg border border-teal-200">
                        <div class="text-sm font-bold text-teal-600 truncate">
                            {{ $osvvRequest->user ? $osvvRequest->user->name : 'Система' }}
                        </div>
                        <div class="text-xs text-teal-700 font-medium">Автор заявки</div>
                    </div>
                </div>

                <!-- Быстрая навигация по разделам -->
                <div class="flex flex-wrap gap-2 mb-6">
                    <a href="#contacts" class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700 hover:bg-indigo-200 transition-colors duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Контакты
                    </a>
                    
                    <a href="#location" class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 hover:bg-emerald-200 transition-colors duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        </svg>
                        Местоположение
                    </a>
                    
                    <a href="#animals" class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 hover:bg-amber-200 transition-colors duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                        Животные
                    </a>
                    
                    <a href="#departures" class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700 hover:bg-purple-200 transition-colors duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                        </svg>
                        Выезды
                    </a>
                    
                    <a href="#history" class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700 hover:bg-blue-200 transition-colors duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        История
                    </a>
                    
                    <a href="#notes" class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-orange-100 text-orange-700 hover:bg-orange-200 transition-colors duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Заметки
                    </a>
                    
                    <a href="#acts" class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-cyan-100 text-cyan-700 hover:bg-cyan-200 transition-colors duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Документы
                    </a>
                </div>

                <!-- Форма редактирования даты создания (скрыта по умолчанию) -->
                <div id="dateForm" class="hidden mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <form action="{{ route('admin.osvv.update-created-at', $osvvRequest) }}" method="POST" class="flex items-end space-x-2">
                        @csrf
                        @method('PUT')
                        <div class="flex-grow">
                            <label for="edit_created_at" class="block text-sm font-medium text-blue-700 mb-1">Новая дата создания:</label>
                            <input type="datetime-local" name="created_at" id="edit_created_at" value="{{ $osvvRequest->created_at->format('Y-m-d\TH:i') }}" class="block w-full rounded-md border-blue-300 bg-white text-blue-900 placeholder-blue-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 py-2 text-sm">
                        </div>
                        <div>
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500">
                                Сохранить
                            </button>
                        </div>
                        <div>
                            <button type="button" id="cancelEditDateBtn" class="inline-flex items-center px-4 py-2 border border-blue-300 text-sm font-medium rounded-md text-blue-700 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500">
                                Отмена
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Двухколоночная структура: карточки слева, история работы справа -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Левая колонка: информация о заявке -->
            <div class="lg:col-span-6 space-y-6">
                <!-- Основная информация о заявке -->
                <div class="space-y-6">
                    <!-- Карточка с контактными данными -->
                    <div id="contacts" class="bg-white shadow rounded-lg overflow-hidden">
                        <div class="border-b border-gray-200">
                            <div class="px-6 py-4 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <h4 class="text-base font-semibold text-gray-800">Контактные данные</h4>
                            </div>
                        </div>
                        <div class="px-6 py-4">
                            <dl>
                                <div class="flex py-2">
                                    <dt class="w-1/3 flex items-center text-sm font-medium text-gray-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        ФИО:
                                    </dt>
                                    <dd class="w-2/3 text-sm text-gray-900 font-medium">{{ $osvvRequest->contact_name }}</dd>
                                </div>
                                <div class="flex py-2 border-t border-gray-100">
                                    <dt class="w-1/3 flex items-center text-sm font-medium text-gray-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                        </svg>
                                        Телефон:
                                    </dt>
                                    <dd class="w-2/3 text-sm text-gray-900 font-medium">
                                        <a href="tel:{{ $osvvRequest->contact_phone }}" class="text-indigo-600 hover:text-indigo-800">
                                            {{ $osvvRequest->contact_phone }}
                                        </a>
                                    </dd>
                                </div>
                                @if($osvvRequest->contact_email)
                                <div class="flex py-2 border-t border-gray-100">
                                    <dt class="w-1/3 flex items-center text-sm font-medium text-gray-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        Email:
                                    </dt>
                                    <dd class="w-2/3 text-sm break-all">
                                        <a href="mailto:{{ $osvvRequest->contact_email }}" class="text-indigo-600 hover:text-indigo-800">
                                            {{ $osvvRequest->contact_email }}
                                        </a>
                                    </dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                    
                    <!-- Карточка с адресом и картой -->
                    <div id="location" class="bg-white shadow rounded-lg overflow-hidden">
                        <div class="border-b border-gray-200">
                            <div class="px-6 py-4 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <h4 class="text-base font-semibold text-gray-800">Местоположение</h4>
                            </div>
                        </div>
                        <div class="px-6 py-4">
                            <dl>
                                <div class="flex py-2">
                                    <dt class="w-1/3 flex items-center text-sm font-medium text-gray-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        </svg>
                                        Адрес:
                                    </dt>
                                    <dd class="w-2/3 text-sm text-gray-900 font-medium">{{ $osvvRequest->location_address }}</dd>
                                </div>
                                
                                @if($osvvRequest->location_landmark)
                                <div class="flex py-2 border-t border-gray-100">
                                    <dt class="w-1/3 flex items-center text-sm font-medium text-gray-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m-6 12V10a2 2 0 012-2h2a2 2 0 012 2v8m-3-4v4" />
                                        </svg>
                                        Ориентир:
                                    </dt>
                                    <dd class="w-2/3 text-sm text-gray-900 font-medium">{{ $osvvRequest->location_landmark }}</dd>
                                </div>
                                @endif
                                
                                @if($osvvRequest->district)
                                <div class="flex py-2 border-t border-gray-100">
                                    <dt class="w-1/3 flex items-center text-sm font-medium text-gray-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                        </svg>
                                        Район:
                                    </dt>
                                    <dd class="w-2/3 text-sm text-gray-900 font-medium">{{ $osvvRequest->district }}</dd>
                                </div>
                                @endif
                                
                                <!-- Дополнительные адреса -->
                                @if($osvvRequest->locations && count($osvvRequest->locations) > 0)
                                <div class="py-2 border-t border-gray-100">
                                    <dt class="flex items-center text-sm font-medium text-gray-500 mb-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3v8m0 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v8a2 2 0 002 2h2zm0 0h2a2 2 0 002-2v-1" />
                                        </svg>
                                        Дополнительные адреса:
                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                                            {{ count($osvvRequest->locations) }} адр.
                                        </span>
                                    </dt>
                                    <dd class="space-y-3">
                                        @foreach($osvvRequest->locations as $index => $location)
                                        <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <div class="flex items-center mb-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                        </svg>
                                                        <span class="text-sm font-medium text-gray-700">Адрес {{ $index + 2 }}</span>
                                                    </div>
                                                    <p class="text-sm text-gray-900 font-medium">{{ $location['address'] }}</p>
                                                    @if(!empty($location['landmark']))
                                                    <p class="text-xs text-gray-600 mt-1 flex items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m-6 12V10a2 2 0 012-2h2a2 2 0 012 2v8m-3-4v4m0 0L9 7" />
                                                        </svg>
                                                        {{ $location['landmark'] }}
                                                    </p>
                                                    @endif
                                                    @if(!empty($location['latitude']) && !empty($location['longitude']))
                                                    <p class="text-xs text-gray-500 mt-1 flex items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                                        </svg>
                                                        {{ round($location['latitude'], 6) }}, {{ round($location['longitude'], 6) }}
                                                    </p>
                                                    @endif
                                                </div>
                                                @if(!empty($location['latitude']) && !empty($location['longitude']))
                                                <div class="ml-3 flex-shrink-0">
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                        На карте
                                                    </span>
                                                </div>
                                                @else
                                                <div class="ml-3 flex-shrink-0">
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        Без координат
                                                    </span>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                        @endforeach
                                    </dd>
                                </div>
                                @endif
                            </dl>
                            <!-- Интерактивная карта для просмотра местоположения -->
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <h5 class="text-sm font-medium text-gray-500 mb-2 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                    </svg>
                                    Карта:
                                </h5>
                                
                                <!-- Легенда карты -->
                                @if($osvvRequest->locations && count($osvvRequest->locations) > 0)
                                <div class="mb-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
                                    <div class="flex items-center space-x-4 text-xs">
                                        <div class="flex items-center">
                                            <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                                            <span class="text-gray-700">Основной адрес</span>
                                        </div>
                                        <div class="flex items-center">
                                            <div class="w-3 h-3 bg-purple-500 rounded-full mr-2"></div>
                                            <span class="text-gray-700">Дополнительные адреса ({{ count($osvvRequest->locations) }})</span>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                
                                <div id="map" class="w-full h-64 rounded-lg border border-gray-300 overflow-hidden"></div>
                                
                                @if($osvvRequest->latitude && $osvvRequest->longitude)
                                <div class="mt-3 grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="latitude" class="block text-xs font-medium text-gray-500">Широта</label>
                                        <input type="text" id="latitude" name="latitude" value="{{ number_format($osvvRequest->latitude, 6) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm" readonly>
                                    </div>
                                    <div>
                                        <label for="longitude" class="block text-xs font-medium text-gray-500">Долгота</label>
                                        <input type="text" id="longitude" name="longitude" value="{{ number_format($osvvRequest->longitude, 6) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm" readonly>
                                    </div>
                                </div>
                                
                                <div class="mt-3 flex items-center space-x-3">
                                    <button type="button" id="saveLocation" class="inline-flex items-center px-3 py-1.5 border border-indigo-300 shadow-sm text-xs font-medium rounded text-indigo-700 bg-indigo-50 hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Сохранить координаты
                                    </button>
                                    <a href="https://maps.google.com/?q={{ $osvvRequest->latitude }},{{ $osvvRequest->longitude }}" target="_blank" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                        Google Maps
                                    </a>
                                </div>
                                @else
                                <div class="mt-3 text-center p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-yellow-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    </svg>
                                    <p class="text-sm text-yellow-700 font-medium">Координаты не указаны</p>
                                    <p class="text-xs text-yellow-600 mt-1">Кликните на карте, чтобы указать местоположение</p>
                                    
                                    <!-- Скрытые поля для новых координат -->
                                    <div class="hidden mt-3 grid grid-cols-2 gap-4" id="coordinatesFields">
                                        <div>
                                            <label for="latitude_new" class="block text-xs font-medium text-gray-500">Широта</label>
                                            <input type="text" id="latitude_new" name="latitude_new" value="" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm" readonly>
                                        </div>
                                        <div>
                                            <label for="longitude_new" class="block text-xs font-medium text-gray-500">Долгота</label>
                                            <input type="text" id="longitude_new" name="longitude_new" value="" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm" readonly>
                                        </div>
                                    </div>
                                    
                                    <div class="hidden mt-3" id="saveLocationContainer">
                                        <button type="button" id="saveLocationNew" class="inline-flex items-center px-3 py-1.5 border border-indigo-300 shadow-sm text-xs font-medium rounded text-indigo-700 bg-indigo-50 hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Сохранить координаты
                                        </button>
                                    </div>
                                </div>
                                @endif
                            </div>                         </div>
                    </div>

                    <!-- Карточка с источником заявки -->
                    <div class="bg-white shadow rounded-lg overflow-hidden">
                        <div class="border-b border-gray-200">
                            <div class="px-6 py-4 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <h4 class="text-base font-semibold text-gray-800">Источник заявки</h4>
                            </div>
                        </div>
                        <div class="px-6 py-4">
                            <dl>
                                @if($osvvRequest->source_type)
                                <div class="flex py-2">
                                    <dt class="w-1/3 flex items-center text-sm font-medium text-gray-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Источник:
                                    </dt>
                                    <dd class="w-2/3 text-sm text-gray-900 font-medium">
                                        @switch($osvvRequest->source_type)
                                            @case('district_office')
                                                📋 Управа района
                                                @break
                                            @case('telegram')
                                                📱 Телеграм
                                                @break
                                            @case('vkontakte')
                                                🌐 ВКонтакте
                                                @break
                                            @case('phone')
                                                📞 Телефон
                                                @break
                                            @case('media')
                                                📺 СМИ
                                                @break
                                            @case('other')
                                                📝 Прочие источники
                                                @break
                                            @default
                                                {{ $osvvRequest->source_type }}
                                        @endswitch
                                    </dd>
                                </div>
                                @endif
                                
                                @if($osvvRequest->source_district)
                                <div class="flex py-2 border-t border-gray-100">
                                    <dt class="w-1/3 flex items-center text-sm font-medium text-gray-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        Управа района:
                                    </dt>
                                    <dd class="w-2/3 text-sm text-gray-900 font-medium">{{ $osvvRequest->source_district }}</dd>
                                </div>
                                @endif
                                
                                @if($osvvRequest->aurora_number)
                                <div class="flex py-2 border-t border-gray-100">
                                    <dt class="w-1/3 flex items-center text-sm font-medium text-gray-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                        </svg>
                                        Номер в Авроре:
                                    </dt>
                                    <dd class="w-2/3 text-sm text-gray-900 font-medium">{{ $osvvRequest->aurora_number }}</dd>
                                </div>
                                @endif
                                
                                @if($osvvRequest->case_description)
                                <div class="py-2 border-t border-gray-100">
                                    <dt class="flex items-center text-sm font-medium text-gray-500 mb-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Описание случая:
                                    </dt>
                                    <dd class="text-sm text-gray-900 bg-gray-50 p-3 rounded-md">{{ $osvvRequest->case_description }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <!-- Карточка с информацией о животном -->
                    <div id="animals" class="bg-white shadow rounded-lg overflow-hidden">
                        <div class="border-b border-gray-200">
                            <div class="px-6 py-4 flex items-center">
                                @if($osvvRequest->animal_type === 'cat')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 12.5l6-6.5 2 1L12 7l4 4-1.5 1.5M20 12v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5c0-1.1.9-2 2-2h1V9a1 1 0 011-1h1a1 1 0 011 1v1h4V9a1 1 0 011-1h1a1 1 0 011 1v1h1a2 2 0 012 2z" />
                                    </svg>
                                @elseif($osvvRequest->animal_type === 'dog')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                @endif
                                <h4 class="text-base font-semibold text-gray-800">Информация о животном</h4>
                            </div>
                        </div>
                        <div class="px-6 py-4">
                            <dl>
                                <!-- Основные характеристики -->
                                <div class="flex py-2">
                                    <dt class="w-1/3 flex items-center text-sm font-medium text-gray-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                        </svg>
                                        Вид:
                                    </dt>
                                    <dd class="w-2/3 text-sm text-gray-900 font-medium">
                                        @if($osvvRequest->animal_type === 'cat')
                                            🐱 Кошка
                                        @elseif($osvvRequest->animal_type === 'dog')
                                            🐶 Собака
                                        @elseif($osvvRequest->animal_type === 'other' && $osvvRequest->animal_type_other)
                                            🐾 {{ $osvvRequest->animal_type_other }}
                                        @else
                                            <span class="text-gray-400">Не указано</span>
                                        @endif
                                    </dd>
                                </div>
                                
                                <div class="flex py-2 border-t border-gray-100">
                                    <dt class="w-1/3 flex items-center text-sm font-medium text-gray-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        Пол:
                                    </dt>
                                    <dd class="w-2/3 text-sm text-gray-900 font-medium">
                                        @if($osvvRequest->animal_gender === 'male')
                                            ♂️ Самец
                                        @elseif($osvvRequest->animal_gender === 'female')
                                            ♀️ Самка
                                        @else
                                            ❓ Неизвестно
                                        @endif
                                    </dd>
                                </div>
                                
                                <div class="flex py-2 border-t border-gray-100">
                                    <dt class="w-1/3 flex items-center text-sm font-medium text-gray-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        Возраст:
                                    </dt>
                                    <dd class="w-2/3 text-sm text-gray-900 font-medium">
                                        @if($osvvRequest->animal_age)
                                            {{ $osvvRequest->animal_age }}
                                        @else
                                            <span class="text-gray-400">Не указан</span>
                                        @endif
                                    </dd>
                                </div>
                                
                                <div class="flex py-2 border-t border-gray-100">
                                    <dt class="w-1/3 flex items-center text-sm font-medium text-gray-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                        </svg>
                                        Количество:
                                    </dt>
                                    <dd class="w-2/3 text-sm text-gray-900 font-medium">{{ $osvvRequest->animals_count ?? 1 }}</dd>
                                </div>
                                
                                <!-- Особые условия -->
                                <div class="py-2 border-t border-gray-100">
                                    <dt class="flex items-center text-sm font-medium text-gray-500 mb-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Особые условия:
                                    </dt>
                                    <dd class="space-y-2">
                                        <div class="flex items-center justify-between p-2 rounded-md {{ $osvvRequest->has_bite ? 'bg-red-50 border border-red-200' : 'bg-gray-50 border border-gray-200' }}">
                                            <span class="flex items-center text-sm">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 {{ $osvvRequest->has_bite ? 'text-red-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                                Был укус
                                            </span>
                                            <span class="text-sm font-medium {{ $osvvRequest->has_bite ? 'text-red-600' : 'text-gray-400' }}">
                                                {{ $osvvRequest->has_bite ? 'Да' : 'Нет' }}
                                            </span>
                                        </div>
                                        
                                        <div class="flex items-center justify-between p-2 rounded-md {{ $osvvRequest->is_pregnant ? 'bg-pink-50 border border-pink-200' : 'bg-gray-50 border border-gray-200' }}">
                                            <span class="flex items-center text-sm">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 {{ $osvvRequest->is_pregnant ? 'text-pink-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                                </svg>
                                                Беременность
                                            </span>
                                            <span class="text-sm font-medium {{ $osvvRequest->is_pregnant ? 'text-pink-600' : 'text-gray-400' }}">
                                                {{ $osvvRequest->is_pregnant ? 'Да' : 'Нет' }}
                                            </span>
                                        </div>
                                        
                                        <div class="flex items-center justify-between p-2 rounded-md {{ $osvvRequest->has_tags ? 'bg-blue-50 border border-blue-200' : 'bg-gray-50 border border-gray-200' }}">
                                            <span class="flex items-center text-sm">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 {{ $osvvRequest->has_tags ? 'text-blue-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                </svg>
                                                Есть метки/бирки
                                            </span>
                                            <span class="text-sm font-medium {{ $osvvRequest->has_tags ? 'text-blue-600' : 'text-gray-400' }}">
                                                {{ $osvvRequest->has_tags ? 'Да' : 'Нет' }}
                                            </span>
                                        </div>
                                        
                                        <div class="flex items-center justify-between p-2 rounded-md {{ $osvvRequest->requires_video ? 'bg-purple-50 border border-purple-200' : 'bg-gray-50 border border-gray-200' }}">
                                            <span class="flex items-center text-sm">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 {{ $osvvRequest->requires_video ? 'text-purple-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                </svg>
                                                Требуется видеофиксация
                                            </span>
                                            <span class="text-sm font-medium {{ $osvvRequest->requires_video ? 'text-purple-600' : 'text-gray-400' }}">
                                                {{ $osvvRequest->requires_video ? 'Да' : 'Нет' }}
                                            </span>
                                        </div>
                                    </dd>
                                </div>
                                
                                <!-- Описание животного -->
                                <div class="py-2 border-t border-gray-100">
                                    <dt class="flex items-center text-sm font-medium text-gray-500 mb-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Описание животного:
                                    </dt>
                                    <dd class="text-sm text-gray-900">
                                        @if($osvvRequest->animal_description)
                                            <div class="bg-gray-50 p-3 rounded-md border border-gray-200">{{ $osvvRequest->animal_description }}</div>
                                        @else
                                            <div class="bg-gray-50 p-3 rounded-md border border-gray-200 text-gray-400 italic">Описание не указано</div>
                                        @endif
                                    </dd>
                                </div>
                                
                                <!-- Медиафайлы животного -->
                                <div class="py-2 border-t border-gray-100">
                                    <dt class="flex items-center text-sm font-medium text-gray-500 mb-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        Фото/видео животного:
                                    </dt>
                                    <dd class="text-sm">
                                        @php
                                            $animalPhotos = $osvvRequest->animal_photos 
                                                ? (is_array($osvvRequest->animal_photos) ? $osvvRequest->animal_photos : json_decode($osvvRequest->animal_photos, true)) 
                                                : [];
                                        @endphp
                                        @if(!empty($animalPhotos))
                                            <div class="text-green-600 font-medium mb-2">📷 Загружено файлов: {{ count($animalPhotos) }}</div>
                                            <div class="text-xs text-gray-500">Файлы отображаются в разделе "Галерея фотографий" ниже</div>
                                        @else
                                            <div class="bg-gray-50 p-3 rounded-md border border-gray-200 text-gray-400 italic">Медиафайлы не загружены</div>
                                        @endif
                                    </dd>
                                </div>
                                
                                <!-- Документы по укусу -->
                                <div class="py-2 border-t border-gray-100">
                                    <dt class="flex items-center text-sm font-medium text-gray-500 mb-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 {{ $osvvRequest->has_bite ? 'text-red-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Документы по укусу:
                                    </dt>
                                    <dd class="text-sm space-y-2">
                                        @if($osvvRequest->has_bite)
                                            @php
                                                $medicalFiles = $osvvRequest->bite_medical_files 
                                                    ? (is_array($osvvRequest->bite_medical_files) ? $osvvRequest->bite_medical_files : json_decode($osvvRequest->bite_medical_files, true)) 
                                                    : [];
                                                $evidenceFiles = $osvvRequest->bite_evidence_files 
                                                    ? (is_array($osvvRequest->bite_evidence_files) ? $osvvRequest->bite_evidence_files : json_decode($osvvRequest->bite_evidence_files, true)) 
                                                    : [];
                                            @endphp
                                            
                                            <div class="flex items-center justify-between p-2 bg-red-50 rounded-md border border-red-200">
                                                <span class="text-red-700">📄 Медицинские справки:</span>
                                                <span class="text-red-600 font-medium">{{ count($medicalFiles) }} файл(ов)</span>
                                            </div>
                                            
                                            <div class="flex items-center justify-between p-2 bg-red-50 rounded-md border border-red-200">
                                                <span class="text-red-700">📸 Фото/видео фиксация:</span>
                                                <span class="text-red-600 font-medium">{{ count($evidenceFiles) }} файл(ов)</span>
                                            </div>
                                            
                                            @if(empty($medicalFiles) && empty($evidenceFiles))
                                            <div class="text-xs text-gray-500 italic">Документы по укусу не загружены</div>
                                            @else
                                            <div class="text-xs text-gray-500">Файлы отображаются в разделе "Документы по укусу" ниже</div>
                                            @endif
                                        @else
                                            <div class="bg-gray-50 p-3 rounded-md border border-gray-200 text-gray-400 italic">Укуса не было - документы не требуются</div>
                                        @endif
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Карточка с фотографиями животного -->
                    @if($osvvRequest->animal_photos && count($osvvRequest->animal_photos) > 0)
                    <div class="bg-white shadow rounded-lg overflow-hidden">
                        <div class="border-b border-gray-200">
                            <div class="px-6 py-4 flex items-center justify-between">
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <h4 class="text-base font-semibold text-gray-800">Фотографии животного</h4>
                                </div>
                                <span class="text-sm text-gray-500">{{ count($osvvRequest->animal_photos) }} файл(ов)</span>
                            </div>
                        </div>
                        <div class="px-6 py-4">
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                @foreach($osvvRequest->animal_photos as $index => $photo)
                                <div class="relative group">
                                    @php
                                        $extension = pathinfo($photo, PATHINFO_EXTENSION);
                                        $isVideo = in_array(strtolower($extension), ['mp4', 'mov', 'avi']);
                                    @endphp
                                    
                                    @if($isVideo)
                                    <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
                                        <video class="w-full h-full object-cover" controls>
                                            <source src="{{ Storage::url($photo) }}" type="video/{{ $extension }}">
                                            Ваш браузер не поддерживает видео.
                                        </video>
                                    </div>
                                    @else
                                    <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden cursor-pointer" onclick="openImageModal('{{ Storage::url($photo) }}')">
                                        <img src="{{ Storage::url($photo) }}" alt="Фото животного {{ $index + 1 }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-200">
                                    </div>
                                    @endif
                                    
                                    <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button type="button" onclick="deleteFile('{{ $osvvRequest->id }}', 'animal_photos', {{ $index }})" class="bg-red-500 hover:bg-red-600 text-white rounded-full p-1 text-xs">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Карточка с документами по укусу -->
                    @if($osvvRequest->has_bite && (($osvvRequest->bite_medical_files && count($osvvRequest->bite_medical_files) > 0) || ($osvvRequest->bite_evidence_files && count($osvvRequest->bite_evidence_files) > 0)))
                    <div class="bg-white shadow rounded-lg overflow-hidden">
                        <div class="border-b border-gray-200">
                            <div class="px-6 py-4 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <h4 class="text-base font-semibold text-gray-800">Документы по укусу</h4>
                            </div>
                        </div>
                        <div class="px-6 py-4">
                            @if($osvvRequest->bite_medical_files && count($osvvRequest->bite_medical_files) > 0)
                            <div class="mb-4">
                                <h5 class="text-sm font-medium text-gray-500 mb-2 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Медицинские справки:
                                </h5>
                                <div class="space-y-2">
                                    @foreach($osvvRequest->bite_medical_files as $index => $file)
                                    <div class="flex items-center justify-between p-3 bg-red-50 rounded-md">
                                        <div class="flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <span class="text-sm text-gray-700">Медсправка {{ $index + 1 }}</span>
                                        </div>
                                        <div class="flex space-x-2">
                                            <a href="{{ Storage::url($file) }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 text-sm">Открыть</a>
                                            <button type="button" onclick="deleteFile('{{ $osvvRequest->id }}', 'bite_medical_files', {{ $index }})" class="text-red-600 hover:text-red-800 text-sm">Удалить</button>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            @if($osvvRequest->bite_evidence_files && count($osvvRequest->bite_evidence_files) > 0)
                            <div>
                                <h5 class="text-sm font-medium text-gray-500 mb-2 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Фото/видео фиксация:
                                </h5>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                    @foreach($osvvRequest->bite_evidence_files as $index => $file)
                                    <div class="relative group">
                                        @php
                                            $extension = pathinfo($file, PATHINFO_EXTENSION);
                                            $isVideo = in_array(strtolower($extension), ['mp4', 'mov', 'avi']);
                                        @endphp
                                        
                                        @if($isVideo)
                                        <div class="aspect-video bg-gray-100 rounded-lg overflow-hidden">
                                            <video class="w-full h-full object-cover" controls>
                                                <source src="{{ Storage::url($file) }}" type="video/{{ $extension }}">
                                                Ваш браузер не поддерживает видео.
                                            </video>
                                        </div>
                                        @else
                                        <div class="aspect-video bg-gray-100 rounded-lg overflow-hidden cursor-pointer" onclick="openImageModal('{{ Storage::url($file) }}')">
                                            <img src="{{ Storage::url($file) }}" alt="Фиксация укуса {{ $index + 1 }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-200">
                                        </div>
                                        @endif
                                        
                                        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button type="button" onclick="deleteFile('{{ $osvvRequest->id }}', 'bite_evidence_files', {{ $index }})" class="bg-red-500 hover:bg-red-600 text-white rounded-full p-1 text-xs">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Карточка с файлами по укусу -->
                    @if($osvvRequest->has_bite && ($osvvRequest->bite_medical_files || $osvvRequest->bite_evidence_files))
                    <div class="bg-white shadow rounded-lg overflow-hidden">
                        <div class="border-b border-gray-200">
                            <div class="px-6 py-4 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <h4 class="text-base font-semibold text-gray-800">Документы по укусу</h4>
                            </div>
                        </div>
                        <div class="px-6 py-4">
                            @if($osvvRequest->bite_medical_files && count($osvvRequest->bite_medical_files) > 0)
                            <div class="mb-4">
                                <h5 class="text-sm font-medium text-gray-500 mb-2">Медицинские справки:</h5>
                                <div class="space-y-2">
                                    @foreach($osvvRequest->bite_medical_files as $index => $file)
                                    <div class="flex items-center p-2 bg-red-50 rounded-md">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <a href="{{ asset('storage/' . $file) }}" target="_blank" class="text-sm text-red-600 hover:text-red-800 font-medium">
                                            Справка {{ $index + 1 }}
                                        </a>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                            
                            @if($osvvRequest->bite_evidence_files && count($osvvRequest->bite_evidence_files) > 0)
                            <div>
                                <h5 class="text-sm font-medium text-gray-500 mb-2">Фото/видео фиксация:</h5>
                                <div class="space-y-2">
                                    @foreach($osvvRequest->bite_evidence_files as $index => $file)
                                    <div class="flex items-center p-2 bg-red-50 rounded-md">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <a href="{{ asset('storage/' . $file) }}" target="_blank" class="text-sm text-red-600 hover:text-red-800 font-medium">
                                            Файл {{ $index + 1 }}
                                        </a>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Правая колонка: история работы с заявкой (комментарии) -->
            <div class="lg:col-span-6">
                <!-- История работы с заявкой (комментарии) -->
                <div id="history" class="bg-white shadow rounded-lg overflow-hidden sticky top-6">
                    <div class="border-b border-gray-200">
                        <div class="px-6 py-4 flex items-center justify-between">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <h4 class="text-base font-semibold text-gray-800">История работы с заявкой</h4>
                            </div>
                            <span class="bg-gray-100 text-gray-700 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $osvvRequest->comments->count() }}</span>
                        </div>
                    </div>
                    
                    <!-- Блок управления выездами -->
                    <div id="departures" class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                        <div class="flex justify-between items-center mb-4">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <h4 class="text-sm font-semibold text-gray-800">Выезды по заявке</h4>
                            </div>
                            
                            <div class="flex items-center space-x-3">
                                <div class="flex items-center">
                                    <span class="text-lg font-semibold mr-1 text-indigo-600">{{ $osvvRequest->departures_count ?? 0 }}</span>
                                    <span class="text-xs text-gray-600">{{ trans_choice('выезд|выезда|выездов', $osvvRequest->departures_count ?? 0) }}</span>
                                </div>
                                
                                @if($osvvRequest->has_bite)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    Укус
                                </span>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Прогресс-бар выполнения выездов (для заявок с укусами) -->
                        @if($osvvRequest->has_bite && $osvvRequest->max_departures)
                        <div class="mb-3">
                            <div class="flex justify-between text-xs text-gray-500 mb-1">
                                <span>Выполнено:</span>
                                <span>{{ $osvvRequest->departures_count }} из {{ $osvvRequest->max_departures }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700 overflow-hidden">
                                <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ min(100, ($osvvRequest->departures_count / $osvvRequest->max_departures) * 100) }}%"></div>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Информация о запланированном выезде, если есть -->
                        @if($osvvRequest->next_departure_date)
                        <div class="flex items-start p-3 bg-blue-50 rounded-md border border-blue-200 mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500 mr-2 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <div>
                                <h5 class="text-sm font-medium text-blue-700">Запланирован выезд:</h5>
                                <p class="text-sm text-blue-600 font-medium mt-1">{{ $osvvRequest->next_departure_date->format('d.m.Y H:i') }}</p>
                                <p class="text-xs text-blue-500 mt-1">
                                    {{ $osvvRequest->next_departure_date->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Кнопки управления выездами -->
                        <div class="flex space-x-2">
                            <button type="button" id="registerDepartureBtn" class="flex-1 inline-flex justify-center items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Зарегистрировать выезд
                            </button>
                            
                            <button type="button" id="scheduleDepartureBtn" class="flex-1 inline-flex justify-center items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Запланировать выезд
                            </button>
                        </div>
                        
                        <!-- Форма регистрации выезда (скрыта по умолчанию) -->
                        <div id="registerDepartureForm" class="hidden mt-3 border rounded-md border-gray-300 p-4 bg-white">
                            <h5 class="text-sm font-semibold text-gray-700 mb-3">Регистрация выезда</h5>
                            <form action="{{ route('admin.osvv.register-departure', $osvvRequest) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="space-y-3">
                                    <div>
                                        <label for="departure_date" class="block text-sm font-medium text-gray-700">Дата выезда</label>
                                        <input type="datetime-local" id="departure_date" name="departure_date" value="{{ now()->format('Y-m-d\TH:i') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                    
                                    <!-- Секция отлова животного -->
                                    <div class="border rounded-md border-gray-200 p-3 bg-gray-50">
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex items-center">
                                                <input 
                                                    type="checkbox" 
                                                    id="capturedDep" 
                                                    name="captured" 
                                                    value="1"
                                                    class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                                                >
                                                <label for="capturedDep" class="ml-2 text-sm font-medium text-gray-700">
                                                    🎯 Животное отловлено
                                                </label>
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Автоматическое создание животных в системе
                                            </div>
                                        </div>
                                        
                                        <div id="captureDetailsDep" class="hidden space-y-3 bg-green-50 border border-green-200 rounded-md p-3">
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                <div>
                                                    <label for="animalsCountDep" class="block text-sm font-medium text-green-700">
                                                        Количество отловленных животных
                                                    </label>
                                                    <input 
                                                        type="number" 
                                                        id="animalsCountDep" 
                                                        name="animals_count" 
                                                        min="1" 
                                                        max="20" 
                                                        value="{{ $osvvRequest->animals_count ?? 1 }}"
                                                        class="mt-1 block w-full rounded-md border-green-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm"
                                                    >
                                                </div>
                                                <div class="flex items-end">
                                                    <div class="text-sm text-green-600">
                                                        <div class="flex items-center">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                            <span class="text-xs font-medium">Статус → "Отловлено"</span>
                                                        </div>
                                                        <div class="flex items-center mt-1">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                            </svg>
                                                            <span class="text-xs font-medium">Создание животных</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label for="departure_notes" class="block text-sm font-medium text-gray-700">Примечания к выезду</label>
                                        <textarea id="departure_notes" name="departure_notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Опишите результаты выезда..."></textarea>
                                        
                                        <!-- Шаблоны примечаний к выезду -->
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            <button type="button" class="template-btn inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-indigo-500" data-template="Животное с биркой, не проявляет агрессию. Заявка закрыта.">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1 text-cyan-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                </svg>
                                                С биркой
                                            </button>
                                            <button type="button" class="template-btn inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-indigo-500" data-template="По указанному адресу животные не обнаружены. Требуется повторный выезд.">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                </svg>
                                                Не обнаружено
                                            </button>
                                            <button type="button" class="template-btn inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-indigo-500" data-template="Произведен отлов {{ $osvvRequest->animals_count ?? 1 }} животного. Отправлено в приют для стерилизации.">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v10.764a1 1 0 01-1.447.894L15 18M5 18l4.553-2.276A1 1 0 0110 14.618V3.382a1 1 0 00-1.447-.894L5 4m0 14V4m0 0L9 7" />
                                                </svg>
                                                Отловлено
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Загрузка видео -->
                                    <div class="mt-3 border rounded-md border-gray-200 p-3 bg-gray-50">
                                        <div class="flex items-center justify-between mb-2">
                                            <h5 class="text-sm font-medium text-gray-700">Прикрепить видеофиксацию</h5>
                                        </div>
                                        <div class="mt-1 flex justify-center px-6 pt-3 pb-4 border-2 border-gray-300 border-dashed rounded-md">
                                            <div class="space-y-1 text-center">
                                                <svg class="mx-auto h-10 w-10 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                                <div class="flex text-sm text-gray-600 justify-center">
                                                    <label for="video-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                                        <span>Загрузить файл</span>
                                                        <input id="video-upload" name="video_file" type="file" accept="video/*" class="sr-only">
                                                    </label>
                                                </div>
                                                <p class="text-xs text-gray-500">MP4, MOV, AVI до 100MB</p>
                                            </div>
                                        </div>
                                        <div id="video-preview" class="mt-2 hidden">
                                            <div class="flex items-center justify-between bg-white p-2 rounded-md border border-gray-200">
                                                <div class="flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v10.764a1 1 0 01-1.447.894L15 18M5 18l4.553-2.276A1 1 0 0110 14.618V3.382a1 1 0 00-1.447-.894L5 4m0 14V4m9 4v4" />
                                                    </svg>
                                                    <span id="video-name" class="text-sm text-gray-700 truncate max-w-xs"></span>
                                                </div>
                                                <button type="button" id="remove-video" class="text-gray-400 hover:text-gray-500">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center">
                                        <input id="requires_video" name="requires_video" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" {{ $osvvRequest->requires_video ? 'checked' : '' }}>
                                        <label for="requires_video" class="ml-2 block text-sm text-gray-700">Требуется видеофиксация</label>
                                    </div>
                                    <div class="flex justify-end space-x-2">
                                        <button type="button" id="cancelRegisterDepartureBtn" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            Отмена
                                        </button>
                                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            Сохранить
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Форма планирования выезда (скрыта по умолчанию) -->
                        <div id="scheduleDepartureForm" class="hidden mt-3 border rounded-md border-gray-300 p-4 bg-white">
                            <h5 class="text-sm font-semibold text-gray-700 mb-3">Планирование выезда</h5>
                            <form action="{{ route('admin.osvv.schedule-departure', $osvvRequest) }}" method="POST">
                                @csrf
                                <div class="space-y-3">
                                    <div>
                                        <label for="next_departure_date" class="block text-sm font-medium text-gray-700">Дата следующего выезда</label>
                                        <input type="datetime-local" id="next_departure_date" name="next_departure_date" value="{{ $osvvRequest->next_departure_date ? $osvvRequest->next_departure_date->format('Y-m-d\TH:i') : now()->addDays(1)->format('Y-m-d\TH:i') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                    <div class="flex justify-end space-x-2">
                                        <button type="button" id="cancelScheduleDepartureBtn" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            Отмена
                                        </button>
                                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            Сохранить
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    

                    
                    <!-- Форма добавления комментария -->
                    <div id="notes" class="px-6 py-4 bg-gray-50 border-b border-gray-100">
                        <h4 class="text-sm font-medium text-gray-700 mb-3">Добавить комментарий</h4>
                        <form action="{{ route('admin.osvv.comment', $osvvRequest) }}" method="POST" class="space-y-3">
                            @csrf
                            <div>
                                <textarea name="comment" rows="3" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Напишите комментарий..." required></textarea>
                            </div>
                            
                            <!-- Кнопка для загрузки медиафайлов (для будущей функциональности) -->
                            <div id="mediaUploadForm" class="hidden border rounded-md border-gray-300 p-3 bg-white">
                                <div class="flex items-center justify-between mb-2">
                                    <h5 class="text-sm font-medium text-gray-700">Прикрепить файлы</h5>
                                    <button type="button" id="closeMediaUploadBtn" class="text-gray-400 hover:text-gray-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">Тип файла</label>
                                    <select class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                        <option value="photo">Фото</option>
                                        <option value="video">Видео</option>
                                        <option value="document">Документ</option>
                                    </select>
                                </div>
                                <div class="mt-2">
                                    <label class="block text-sm font-medium text-gray-700">Файл</label>
                                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                        <div class="space-y-1 text-center">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <div class="flex text-sm text-gray-600">
                                                <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                                    <span>Загрузить файл</span>
                                                    <input id="file-upload" name="file-upload" type="file" class="sr-only">
                                                </label>
                                                <p class="pl-1">или перетащите</p>
                                            </div>
                                            <p class="text-xs text-gray-500">PNG, JPG, GIF до 10MB</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex justify-between">
                                <div>
                                    <button type="button" id="showMediaUploadBtn" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                        </svg>
                                        Прикрепить файл
                                    </button>
                                </div>
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                    </svg>
                                    Отправить
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Список комментариев в виде таймлайна -->
                    <div class="relative">
                        <div class="absolute left-8 top-0 bottom-0 w-0.5 bg-gray-200"></div>
                        <div class="divide-y divide-gray-100">
                            <!-- Акты отлова в истории -->
                            <div id="acts">
                            @foreach($osvvRequest->captureActs as $act)
                                <div class="px-6 py-4 relative">
                                    <div class="flex items-start">
                                        <div class="h-6 w-6 rounded-full bg-green-600 flex items-center justify-center flex-shrink-0 z-10">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <div class="ml-6 flex-1">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <span class="text-sm font-medium text-gray-900">{{ $act->creator ? $act->creator->name : 'Система' }}</span>
                                                </div>
                                                <span class="text-xs text-gray-500">{{ $act->created_at->format('d.m.Y H:i') }}</span>
                                            </div>
                                            <div class="mt-1 text-sm text-gray-700 bg-green-50 border border-green-200 p-3 rounded-lg">
                                                <div class="flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                    <span class="font-medium text-green-800">[АКТ ОТЛОВА]</span>
                                                </div>
                                                <div class="mt-1">
                                                    <p><strong>Акт №{{ $act->act_number }}</strong> создан</p>
                                                    <p class="text-sm text-gray-600 mt-1">Дата отлова: {{ $act->capture_date ? \Carbon\Carbon::parse($act->capture_date)->format('d.m.Y') : 'Не указана' }}</p>
                                                    @if($act->animals_count)
                                                        <p class="text-sm text-gray-600">Отловлено животных: {{ $act->animals_count }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <!-- Кнопка для просмотра акта -->
                                            <div class="mt-2 flex justify-end">
                                                <a href="{{ route('admin.osvv.acts.show', $act->id) }}" class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-700 hover:text-green-800 bg-green-100 hover:bg-green-200 rounded-md transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    Просмотреть акт
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <!-- Остальные комментарии (от новых к старым) -->
                            @forelse($osvvRequest->comments->sortByDesc('created_at') as $comment)
                                <div class="px-6 py-4 relative">
                                    <div class="flex items-start">
                                        <div class="h-6 w-6 rounded-full bg-indigo-500 flex items-center justify-center flex-shrink-0 z-10">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div class="ml-6 flex-1">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <span class="text-sm font-medium text-gray-900">{{ $comment->user->name }}</span>
                                                </div>
                                                <span class="text-xs text-gray-500">{{ $comment->created_at->format('d.m.Y H:i') }}</span>
                                            </div>
                                            <div class="mt-1 text-sm text-gray-700 bg-gray-50 p-3 rounded-lg whitespace-pre-line">
                                                {{ $comment->comment }}
                                            </div>
                                            
                                            <!-- Отображение видео, если есть -->
                                            @if(str_contains($comment->comment, 'Зарегистрирован выезд') && str_contains($comment->comment, '📹 Видеофиксация загружена'))
                                                @php
                                                    // Извлекаем номер выезда из комментария
                                                    preg_match('/выезд №(\d+)/', $comment->comment, $matches);
                                                    $departureNumber = $matches[1] ?? null;
                                                    
                                                    // Получаем видео для этого выезда
                                                    $videos = $osvvRequest->departure_videos ?? [];
                                                    $departureVideo = collect($videos)->firstWhere('departure_number', (int)$departureNumber);
                                                @endphp
                                                
                                                @if($departureVideo)
                                                <div class="mt-3 p-3 bg-indigo-50 border border-indigo-200 rounded-md">
                                                    <div class="flex items-center justify-between">
                                                        <div class="flex items-center">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v10.764a1 1 0 01-1.447.894L15 18M5 18l4.553-2.276A1 1 0 0110 14.618V3.382a1 1 0 00-1.447-.894L5 4m0 14V4m9 4v4" />
                                                            </svg>
                                                            <span class="text-sm font-medium text-indigo-700">Видеофиксация выезда</span>
                                                        </div>
                                                        <a href="{{ asset('storage/' . $departureVideo['path']) }}" target="_blank" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                            Смотреть
                                                        </a>
                                                    </div>
                                                </div>
                                                @endif
                                            @endif
                                            
                                            <!-- Кнопки действий для комментария (пример для будущей функциональности) -->
                                            <div class="mt-2 flex justify-end space-x-2">
                                                <button type="button" class="inline-flex items-center px-2 py-1 text-xs font-medium text-gray-700 hover:text-indigo-600">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                                    </svg>
                                                    Поделиться
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <!-- Если нет комментариев -->
                                <div class="px-6 py-8 text-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    <p class="mt-4 text-gray-500">История действий пуста. Добавьте первый комментарий.</p>
                                </div>
                            @endforelse
                            
                            <!-- Первая запись о регистрации заявки (всегда отображается в конце истории) -->
                            <div class="px-6 py-4 relative">
                                <div class="flex items-start">
                                    <div class="h-6 w-6 rounded-full bg-green-500 flex items-center justify-center flex-shrink-0 z-10">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                    </div>
                                    <div class="ml-6 flex-1">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <span class="text-sm font-medium text-gray-900">Система</span>
                                            </div>
                                            <span class="text-xs text-gray-500">{{ $osvvRequest->created_at->format('d.m.Y H:i') }}</span>
                                        </div>
                                        <div class="mt-1 text-sm text-gray-700 bg-blue-50 border border-blue-100 p-3 rounded-lg whitespace-pre-line">
                                            [РЕГИСТРАЦИЯ] Заявка №{{ $osvvRequest->id }} зарегистрирована в системе.
                                            @if($osvvRequest->user)
                                                <br>Источник: {{ $osvvRequest->user->name }}
                                            @endif
                                            @if($osvvRequest->has_bite)
                                                <br><span class="text-red-600 font-medium">⚠️ Заявка с укусом, требуется срочная обработка!</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Подключение API Яндекс.Карт -->
    <script src="https://api-maps.yandex.ru/2.1/?apikey=aba2bc56-907f-41a7-9377-d32e69eff205&lang=ru_RU"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const changeStatusBtn = document.getElementById('changeStatusBtn');
            const statusForm = document.getElementById('statusForm');
            
            changeStatusBtn.addEventListener('click', function() {
                statusForm.classList.toggle('hidden');
            });
            
            // Функционал редактирования даты создания
            const editDateBtn = document.getElementById('editDateBtn');
            const dateForm = document.getElementById('dateForm');
            const cancelEditDateBtn = document.getElementById('cancelEditDateBtn');
            
            editDateBtn.addEventListener('click', function() {
                dateForm.classList.remove('hidden');
            });
            
            cancelEditDateBtn.addEventListener('click', function() {
                dateForm.classList.add('hidden');
            });
            
            // Функционал работы с прикреплением файлов
            const showMediaUploadBtn = document.getElementById('showMediaUploadBtn');
            const mediaUploadForm = document.getElementById('mediaUploadForm');
            const closeMediaUploadBtn = document.getElementById('closeMediaUploadBtn');
            
            if (showMediaUploadBtn && mediaUploadForm && closeMediaUploadBtn) {
                showMediaUploadBtn.addEventListener('click', function() {
                    mediaUploadForm.classList.remove('hidden');
                });
                
                closeMediaUploadBtn.addEventListener('click', function() {
                    mediaUploadForm.classList.add('hidden');
                });
            }
            
            // Получаем текстовое поле для комментариев
            const commentTextarea = document.querySelector('textarea[name="comment"]');
            
            // Инициализация карты Яндекс
            if (document.getElementById('map')) {
                // Загрузка API Яндекс.Карт
                ymaps.ready(initMap);
                
                function initMap() {
                    // Определение начальных координат (основной адрес)
                    let latitude = {{ $osvvRequest->latitude ?? 51.661535 }}; // Воронеж по умолчанию
                    let longitude = {{ $osvvRequest->longitude ?? 39.200287 }};
                    
                    // Создание карты
                    const myMap = new ymaps.Map('map', {
                        center: [latitude, longitude],
                        zoom: 14,
                        controls: ['zoomControl', 'fullscreenControl']
                    });
                    
                    // Массив для хранения всех меток
                    const allPlacemarks = [];
                    
                    // Создание метки для основного адреса, если есть координаты
                    if ({{ $osvvRequest->latitude && $osvvRequest->longitude ? 'true' : 'false' }}) {
                        const mainPlacemark = new ymaps.Placemark([latitude, longitude], {
                            hintContent: 'Основной адрес',
                            balloonContent: `
                                <div>
                                    <strong>Основной адрес</strong><br>
                                    {{ $osvvRequest->location_address }}<br>
                                    @if($osvvRequest->location_landmark)
                                    <em>Ориентир: {{ $osvvRequest->location_landmark }}</em><br>
                                    @endif
                                    <small>Координаты: ${latitude.toFixed(6)}, ${longitude.toFixed(6)}</small>
                                </div>
                            `
                        }, {
                            preset: 'islands#redDotIcon',
                            draggable: true
                        });
                        
                        myMap.geoObjects.add(mainPlacemark);
                        allPlacemarks.push(mainPlacemark);
                        
                        // Обработчик перетаскивания основной метки
                        mainPlacemark.events.add('dragend', function() {
                            const newCoords = mainPlacemark.geometry.getCoordinates();
                            updateCoordinateInputs(newCoords[0], newCoords[1]);
                        });
                    }
                    
                    // Добавление меток для дополнительных адресов
                    @if($osvvRequest->locations && count($osvvRequest->locations) > 0)
                    const additionalLocations = @json($osvvRequest->locations);
                    
                    additionalLocations.forEach(function(location, index) {
                        if (location.latitude && location.longitude) {
                            const additionalPlacemark = new ymaps.Placemark(
                                [parseFloat(location.latitude), parseFloat(location.longitude)], 
                                {
                                    hintContent: `Дополнительный адрес ${index + 2}`,
                                    balloonContent: `
                                        <div>
                                            <strong>Дополнительный адрес ${index + 2}</strong><br>
                                            ${location.address}<br>
                                            ${location.landmark ? `<em>Ориентир: ${location.landmark}</em><br>` : ''}
                                            <small>Координаты: ${parseFloat(location.latitude).toFixed(6)}, ${parseFloat(location.longitude).toFixed(6)}</small>
                                        </div>
                                    `
                                }, 
                                {
                                    preset: 'islands#violetDotIcon',
                                    draggable: false
                                }
                            );
                            
                            myMap.geoObjects.add(additionalPlacemark);
                            allPlacemarks.push(additionalPlacemark);
                        }
                    });
                    @endif
                    
                    // Если есть несколько меток, подстраиваем масштаб карты под все точки
                    if (allPlacemarks.length > 1) {
                        const bounds = myMap.geoObjects.getBounds();
                        if (bounds) {
                            myMap.setBounds(bounds, {
                                checkZoomRange: true,
                                zoomMargin: 50
                            });
                        }
                    }
                    
                    // Обработчик клика по карте (только для основного адреса)
                    myMap.events.add('click', function(e) {
                        const coords = e.get('coords');
                        
                        // Находим основную метку
                        let mainPlacemark = null;
                        for (let i = 0; i < allPlacemarks.length; i++) {
                            if (allPlacemarks[i].options.get('preset') === 'islands#redDotIcon') {
                                mainPlacemark = allPlacemarks[i];
                                break;
                            }
                        }
                        
                        // Если основная метка уже есть, удаляем ее
                        if (mainPlacemark) {
                            myMap.geoObjects.remove(mainPlacemark);
                            const index = allPlacemarks.indexOf(mainPlacemark);
                            if (index > -1) {
                                allPlacemarks.splice(index, 1);
                            }
                        }
                        
                        // Создаем новую основную метку на месте клика
                        const newMainPlacemark = new ymaps.Placemark(coords, {
                            hintContent: 'Основной адрес (новый)',
                            balloonContent: `
                                <div>
                                    <strong>Основной адрес (новый)</strong><br>
                                    <small>Координаты: ${coords[0].toFixed(6)}, ${coords[1].toFixed(6)}</small>
                                </div>
                            `
                        }, {
                            preset: 'islands#redDotIcon',
                            draggable: true
                        });
                        
                        myMap.geoObjects.add(newMainPlacemark);
                        allPlacemarks.push(newMainPlacemark);
                        
                        // Обновляем поля ввода координат
                        updateCoordinateInputs(coords[0], coords[1]);
                        
                        // Обработчик перетаскивания новой метки
                        newMainPlacemark.events.add('dragend', function() {
                            const newCoords = newMainPlacemark.geometry.getCoordinates();
                            updateCoordinateInputs(newCoords[0], newCoords[1]);
                        });
                    });
                    
                    // Функция обновления полей ввода координат
                    function updateCoordinateInputs(lat, lng) {
                        // Проверяем, есть ли уже координаты в заявке
                        const existingLatField = document.getElementById('latitude');
                        const existingLngField = document.getElementById('longitude');
                        
                        if (existingLatField && existingLngField) {
                            // Обновляем существующие поля
                            existingLatField.value = lat.toFixed(6);
                            existingLngField.value = lng.toFixed(6);
                        } else {
                            // Используем новые поля для незаданных координат
                            const newLatField = document.getElementById('latitude_new');
                            const newLngField = document.getElementById('longitude_new');
                            const coordinatesFields = document.getElementById('coordinatesFields');
                            const saveLocationContainer = document.getElementById('saveLocationContainer');
                            
                            if (newLatField && newLngField) {
                                newLatField.value = lat.toFixed(6);
                                newLngField.value = lng.toFixed(6);
                                
                                // Показываем поля координат и кнопку сохранения
                                if (coordinatesFields) coordinatesFields.classList.remove('hidden');
                                if (saveLocationContainer) saveLocationContainer.classList.remove('hidden');
                            }
                        }
                    }
                    
                    // Обработчик кнопки сохранения
                    document.getElementById('saveLocation').addEventListener('click', function() {
                        const latitude = document.getElementById('latitude').value;
                        const longitude = document.getElementById('longitude').value;
                        
                        if (!latitude || !longitude) {
                            alert('Сначала выберите точку на карте');
                            return;
                        }
                        
                        // Отправка AJAX запроса для сохранения координат
                        fetch('{{ route('admin.osvv.update-coordinates', $osvvRequest) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                latitude: latitude,
                                longitude: longitude
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('Координаты успешно сохранены');
                            } else {
                                alert('Ошибка: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Ошибка:', error);
                            alert('Произошла ошибка при сохранении координат');
                        });
                    });
                    
                    // Обработчик кнопки сохранения новых координат
                    const saveLocationNewBtn = document.getElementById('saveLocationNew');
                    if (saveLocationNewBtn) {
                        saveLocationNewBtn.addEventListener('click', function() {
                            const latitude = document.getElementById('latitude_new').value;
                            const longitude = document.getElementById('longitude_new').value;
                            
                            if (!latitude || !longitude) {
                                alert('Сначала выберите точку на карте');
                                return;
                            }
                            
                            // Отправка AJAX запроса для сохранения координат
                            fetch('{{ route('admin.osvv.update-coordinates', $osvvRequest) }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    latitude: latitude,
                                    longitude: longitude
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    alert('Координаты успешно сохранены');
                                    // Перезагружаем страницу, чтобы показать обновленную карту
                                    location.reload();
                                } else {
                                    alert('Ошибка: ' + data.message);
                                }
                            })
                            .catch(error => {
                                console.error('Ошибка:', error);
                                alert('Произошла ошибка при сохранении координат');
                            });
                        });
                    }
                }
            }
            
            // Управление формами для выездов
            const registerDepartureBtn = document.getElementById('registerDepartureBtn');
            const registerDepartureForm = document.getElementById('registerDepartureForm');
            const cancelRegisterDepartureBtn = document.getElementById('cancelRegisterDepartureBtn');
            
            const scheduleDepartureBtn = document.getElementById('scheduleDepartureBtn');
            const scheduleDepartureForm = document.getElementById('scheduleDepartureForm');
            const cancelScheduleDepartureBtn = document.getElementById('cancelScheduleDepartureBtn');
            
            if (registerDepartureBtn && registerDepartureForm && cancelRegisterDepartureBtn) {
                registerDepartureBtn.addEventListener('click', function() {
                    registerDepartureForm.classList.remove('hidden');
                    if (scheduleDepartureForm) {
                        scheduleDepartureForm.classList.add('hidden');
                    }
                });
                
                cancelRegisterDepartureBtn.addEventListener('click', function() {
                    registerDepartureForm.classList.add('hidden');
                });
            }
            
            if (scheduleDepartureBtn && scheduleDepartureForm && cancelScheduleDepartureBtn) {
                scheduleDepartureBtn.addEventListener('click', function() {
                    scheduleDepartureForm.classList.remove('hidden');
                    if (registerDepartureForm) {
                        registerDepartureForm.classList.add('hidden');
                    }
                });
                
                cancelScheduleDepartureBtn.addEventListener('click', function() {
                    scheduleDepartureForm.classList.add('hidden');
                });
            }
            
            // Обработка кнопок шаблонов в форме регистрации выезда
            const templateButtons = document.querySelectorAll('.template-btn');
            const departureNotesTextarea = document.getElementById('departure_notes');
            
            if (templateButtons.length > 0 && departureNotesTextarea) {
                templateButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const template = this.getAttribute('data-template');
                        departureNotesTextarea.value = template;
                        departureNotesTextarea.focus();
                    });
                });
            }
            
            // Обработка галочки отлова животного в форме регистрации выезда
            const capturedDepCheckbox = document.getElementById('capturedDep');
            const captureDetailsDep = document.getElementById('captureDetailsDep');
            const animalsCountDepInput = document.getElementById('animalsCountDep');
            
            if (capturedDepCheckbox && captureDetailsDep && animalsCountDepInput && departureNotesTextarea) {
                // Обработчик изменения галочки
                capturedDepCheckbox.addEventListener('change', function() {
                    if (this.checked) {
                        captureDetailsDep.classList.remove('hidden');
                        animalsCountDepInput.focus();
                        updateDepartureNotes();
                    } else {
                        captureDetailsDep.classList.add('hidden');
                        // Очищаем примечания если они содержат шаблон отлова
                        if (departureNotesTextarea.value.includes('Произведен отлов')) {
                            departureNotesTextarea.value = '';
                        }
                    }
                });
                
                // Обработчик изменения количества животных
                animalsCountDepInput.addEventListener('input', function() {
                    if (capturedDepCheckbox.checked) {
                        updateDepartureNotes();
                    }
                });
                
                // Функция обновления примечаний
                function updateDepartureNotes() {
                    if (capturedDepCheckbox.checked) {
                        const count = animalsCountDepInput.value || 1;
                        const template = `Произведен отлов ${count} животного${count > 1 ? 'х' : ''}. Отправлено в приют для стерилизации и вакцинации.`;
                        departureNotesTextarea.value = template;
                    }
                }
            }
            
            // Обработка загрузки видео
            const videoUploadInput = document.getElementById('video-upload');
            const videoPreview = document.getElementById('video-preview');
            const videoName = document.getElementById('video-name');
            const removeVideoBtn = document.getElementById('remove-video');
            
            if (videoUploadInput && videoPreview && videoName && removeVideoBtn) {
                videoUploadInput.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        const file = this.files[0];
                        videoName.textContent = file.name;
                        videoPreview.classList.remove('hidden');
                        
                        // Автоматически установить флаг "Требуется видеофиксация"
                        const requiresVideoCheckbox = document.getElementById('requires_video');
                        if (requiresVideoCheckbox) {
                            requiresVideoCheckbox.checked = true;
                        }
                    }
                });
                
                removeVideoBtn.addEventListener('click', function() {
                    videoUploadInput.value = '';
                    videoPreview.classList.add('hidden');
                });
            }
            
            // Функция для открытия модального окна с изображением
            window.openImageModal = function(imageSrc) {
                // Создаем модальное окно, если его еще нет
                let modal = document.getElementById('imageModal');
                if (!modal) {
                    modal = document.createElement('div');
                    modal.id = 'imageModal';
                    modal.className = 'fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden';
                    modal.innerHTML = `
                        <div class="relative max-w-4xl max-h-full p-4">
                            <img id="modalImage" src="" alt="Увеличенное изображение" class="max-w-full max-h-full object-contain">
                            <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white bg-black bg-opacity-50 rounded-full p-2 hover:bg-opacity-75">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    `;
                    document.body.appendChild(modal);
                    
                    // Закрытие по клику на фон
                    modal.addEventListener('click', function(e) {
                        if (e.target === modal) {
                            closeImageModal();
                        }
                    });
                    
                    // Закрытие по ESC
                    document.addEventListener('keydown', function(e) {
                        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                            closeImageModal();
                        }
                    });
                }
                
                // Устанавливаем изображение и показываем модальное окно
                document.getElementById('modalImage').src = imageSrc;
                modal.classList.remove('hidden');
            };
            
            // Функция для закрытия модального окна
            window.closeImageModal = function() {
                const modal = document.getElementById('imageModal');
                if (modal) {
                    modal.classList.add('hidden');
                }
            };
            
            // Функция для показа полного описания ситуации
            window.toggleFullDescription = function() {
                const fullDescription = document.getElementById('fullDescription');
                const button = event.target;
                
                if (fullDescription.classList.contains('hidden')) {
                    fullDescription.classList.remove('hidden');
                    button.textContent = 'Скрыть';
                } else {
                    fullDescription.classList.add('hidden');
                    button.textContent = 'Показать полностью';
                }
            };
            
            // Функция для показа полного результата мероприятий
            window.toggleCaptureResult = function() {
                const fullCaptureResult = document.getElementById('fullCaptureResult');
                const button = event.target;
                
                if (fullCaptureResult.classList.contains('hidden')) {
                    fullCaptureResult.classList.remove('hidden');
                    button.textContent = 'Скрыть';
                } else {
                    fullCaptureResult.classList.add('hidden');
                    button.textContent = 'Показать полностью';
                }
            };
            
            // Функция для показа полных примечаний
            window.toggleNotes = function() {
                const fullNotes = document.getElementById('fullNotes');
                const button = event.target;
                
                if (fullNotes.classList.contains('hidden')) {
                    fullNotes.classList.remove('hidden');
                    button.textContent = 'Скрыть';
                } else {
                    fullNotes.classList.add('hidden');
                    button.textContent = 'Показать полностью';
                }
            };
            
            // Функция для удаления файла
            window.deleteFile = function(requestId, fieldName, fileIndex) {
                if (!confirm('Вы уверены, что хотите удалить этот файл?')) {
                    return;
                }
                
                fetch(`/admin/osvv/${requestId}/delete-file`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        field_name: fieldName,
                        file_index: fileIndex
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Перезагружаем страницу для обновления списка файлов
                        location.reload();
                    } else {
                        alert('Ошибка при удалении файла: ' + (data.message || 'Неизвестная ошибка'));
                    }
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                    alert('Произошла ошибка при удалении файла');
                });
            };
        });
    </script>
@endsection

