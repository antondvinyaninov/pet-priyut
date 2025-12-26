@extends('admin.layout')

@section('header', 'Заявки ОСВВ')

@section('content')
    <div class="space-y-6">
        <!-- Верхняя панель с заголовком и кнопкой добавления -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Управление заявками ОСВВ
                    </h3>
                    <a href="{{ route('admin.osvv.create') }}" class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-white/20 active:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Добавить заявку
                    </a>
                </div>
                <div class="mt-2">
                    <p class="text-white/70 text-sm">Просмотр и управление заявками по отлову, стерилизации и вакцинации животных</p>
                </div>
            </div>
        
            <!-- Счетчики статусов -->
            <div class="px-6 py-3 bg-white border-b border-gray-100">
                <div class="flex flex-wrap gap-2">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        Новые: {{ $requests->where('status', 'new')->count() }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        В обработке: {{ $requests->where('status', 'processing')->count() }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                        На отлове: {{ $requests->whereIn('status', ['capture_scheduled', 'captured'])->count() }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        В приюте: {{ $requests->whereIn('status', ['in_shelter', 'sterilized', 'vaccinated', 'ready_for_return'])->count() }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                        Завершено: {{ $requests->where('status', 'completed')->count() }}
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Фильтры в выдвижной панели -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 flex justify-between items-center border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-700 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Фильтры
                </h3>
                <button type="button" id="toggleFilters" class="text-gray-500 hover:text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
            <div id="filterPanel" class="px-6 py-4 border-b border-gray-200 {{ request()->hasAny(['status', 'date_from', 'date_to', 'has_bite', 'is_pregnant', 'district', 'deadline_overdue']) ? '' : 'hidden' }}">
                <form action="{{ route('admin.osvv.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Статус</label>
                        <select id="status" name="status" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Все статусы</option>
                            <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>Новая заявка</option>
                            <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>В обработке</option>
                            <option value="capture_scheduled" {{ request('status') === 'capture_scheduled' ? 'selected' : '' }}>Запланирован отлов</option>
                            <option value="captured" {{ request('status') === 'captured' ? 'selected' : '' }}>Животное отловлено</option>
                            <option value="in_shelter" {{ request('status') === 'in_shelter' ? 'selected' : '' }}>В приюте</option>
                            <option value="sterilized" {{ request('status') === 'sterilized' ? 'selected' : '' }}>Стерилизовано</option>
                            <option value="vaccinated" {{ request('status') === 'vaccinated' ? 'selected' : '' }}>Вакцинировано</option>
                            <option value="ready_for_return" {{ request('status') === 'ready_for_return' ? 'selected' : '' }}>Готово к возврату</option>
                            <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>Возвращено</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Завершено</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Отменено</option>
                        </select>
                    </div>
                    <div>
                        <label for="district" class="block text-sm font-medium text-gray-700 mb-1">Район</label>
                        <input type="text" id="district" name="district" value="{{ request('district') }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div>
                        <fieldset class="mt-4">
                            <legend class="block text-sm font-medium text-gray-700 mb-1">Особые условия</legend>
                            <div class="space-y-2">
                                <div class="relative flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="has_bite" name="has_bite" type="checkbox" value="1" {{ request('has_bite') ? 'checked' : '' }} class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="has_bite" class="font-medium text-gray-700">Был укус</label>
                                    </div>
                                </div>
                                <div class="relative flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="is_pregnant" name="is_pregnant" type="checkbox" value="1" {{ request('is_pregnant') ? 'checked' : '' }} class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="is_pregnant" class="font-medium text-gray-700">Беременность</label>
                                    </div>
                                </div>
                                <div class="relative flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="deadline_overdue" name="deadline_overdue" type="checkbox" value="1" {{ request('deadline_overdue') ? 'checked' : '' }} class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="deadline_overdue" class="font-medium text-gray-700">Просрочен срок выезда</label>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                    <div>
                        <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Дата создания с</label>
                        <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div>
                        <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Дата создания по</label>
                        <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div></div>
                    <div class="col-span-1 md:col-span-3 flex justify-end space-x-2">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" clip-rule="evenodd" />
                            </svg>
                            Применить
                        </button>
                        <a href="{{ route('admin.osvv.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 font-medium text-xs uppercase tracking-widest transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                            </svg>
                            Сбросить
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Карточки статистики -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="bg-indigo-500 px-4 py-2">
                    <h4 class="text-sm font-medium text-white">Общая статистика</h4>
                </div>
                <div class="px-4 py-3">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500">Всего заявок</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $statusCounts['all'] ?? 0 }}</p>
                        </div>
                        <div class="h-10 w-10 bg-indigo-100 rounded-full flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <div class="bg-indigo-50 rounded-md p-2">
                            <p class="text-xs text-gray-500">Новые</p>
                            <p class="text-lg font-semibold text-indigo-600">{{ $statusCounts['new'] ?? 0 }}</p>
                        </div>
                        <div class="bg-indigo-50 rounded-md p-2">
                            <p class="text-xs text-gray-500">В обработке</p>
                            <p class="text-lg font-semibold text-indigo-600">{{ $statusCounts['processing'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="bg-red-500 px-4 py-2">
                    <h4 class="text-sm font-medium text-white">Требуют внимания</h4>
                </div>
                <div class="px-4 py-3">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500">Просрочен срок</p>
                            <p class="text-2xl font-semibold text-red-600">{{ $statusCounts['deadline_overdue'] ?? 0 }}</p>
                        </div>
                        <div class="h-10 w-10 bg-red-100 rounded-full flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <div class="bg-red-50 rounded-md p-2">
                            <p class="text-xs text-gray-500">С укусами</p>
                            <p class="text-lg font-semibold text-red-600">{{ $statusCounts['has_bite'] ?? 0 }}</p>
                        </div>
                        <a href="{{ route('admin.osvv.index', ['deadline_overdue' => 1]) }}" class="bg-red-50 hover:bg-red-100 rounded-md p-2 flex items-center justify-center transition">
                            <span class="text-xs text-red-600 font-medium">Просмотреть все</span>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="bg-green-500 px-4 py-2">
                    <h4 class="text-sm font-medium text-white">Список на выезд на сегодня</h4>
                </div>
                <div class="px-4 py-3">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500">Всего заявок</p>
                            <p class="text-2xl font-semibold text-green-600">{{ $todayDepartureList['total_requests'] ?? 0 }}</p>
                        </div>
                        <div class="h-10 w-10 bg-green-100 rounded-full flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <div class="bg-green-50 rounded-md p-2">
                            <p class="text-xs text-gray-500">Срочные</p>
                            <p class="text-lg font-semibold text-green-600">{{ $todayDepartureList['urgent_count'] ?? 0 }}</p>
                        </div>
                        <div class="bg-green-50 rounded-md p-2">
                            <p class="text-xs text-gray-500">На сегодня</p>
                            <p class="text-lg font-semibold text-green-600">{{ $todayDepartureList['today_count'] ?? 0 }}</p>
                        </div>
                    </div>
                    @if(isset($todayDepartureList['zones']) && count($todayDepartureList['zones']) > 0)
                        <div class="mt-4 space-y-2">
                            <button type="button" onclick="toggleDepartureList()" class="w-full text-xs text-green-600 font-medium hover:text-green-800 transition">
                                Показать маршруты ({{ count($todayDepartureList['zones']) }} зон)
                            </button>
                            <a href="{{ route('admin.osvv.departure-map') }}" class="block w-full text-center px-3 py-2 bg-green-100 text-green-700 rounded-md hover:bg-green-200 transition text-xs font-medium">
                                🗺️ Открыть на карте
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Детальный список на выезд -->
        @if(isset($todayDepartureList['zones']) && count($todayDepartureList['zones']) > 0)
            <div id="departureListDetail" class="hidden bg-white shadow rounded-lg overflow-hidden">
                <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-4">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl font-bold text-white flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                            </svg>
                            Оптимальные маршруты на {{ now()->format('d.m.Y') }}
                        </h3>
                        <button type="button" onclick="toggleDepartureList()" class="text-white/70 hover:text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="mt-2">
                        <p class="text-white/70 text-sm">Заявки сгруппированы по географическим зонам для оптимизации выездов</p>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="grid gap-6">
                        @foreach($todayDepartureList['zones'] as $index => $zone)
                            <div class="border border-gray-200 rounded-lg p-4 {{ $zone['priority_level'] >= 10 ? 'border-red-300 bg-red-50' : ($zone['priority_level'] >= 5 ? 'border-yellow-300 bg-yellow-50' : 'border-gray-200') }}">
                                <div class="flex justify-between items-start mb-3">
                                    <div class="flex items-center">
                                        <div class="flex items-center justify-center w-8 h-8 rounded-full {{ $zone['priority_level'] >= 10 ? 'bg-red-500' : ($zone['priority_level'] >= 5 ? 'bg-yellow-500' : 'bg-green-500') }} text-white font-bold text-sm mr-3">
                                            {{ $index + 1 }}
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-gray-900">
                                                Зона: {{ $zone['center_request']['district'] ?? 'Не указан' }}
                                            </h4>
                                            <p class="text-sm text-gray-600">
                                                {{ count($zone['requests']) }} заявок • 
                                                ~{{ floor($zone['estimated_time'] / 60) }}ч {{ $zone['estimated_time'] % 60 }}мин
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        @if($zone['priority_level'] >= 10)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Критично
                                            </span>
                                        @elseif($zone['priority_level'] >= 5)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Срочно
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Плановый
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="space-y-2">
                                    @foreach($zone['requests'] as $request)
                                        <div class="flex items-center justify-between p-3 bg-white rounded border border-gray-100 hover:border-gray-200 transition">
                                            <div class="flex items-center space-x-3">
                                                <div class="flex-shrink-0">
                                                    <a href="{{ route('admin.osvv.show', $request['id']) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                                        #{{ $request['id'] }}
                                                    </a>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900 truncate">
                                                        {{ $request['location_address'] }}
                                                    </p>
                                                    <p class="text-sm text-gray-500 truncate">
                                                        {{ $request['contact_name'] }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                @if($request['has_bite'])
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        <svg class="h-3 w-3 mr-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                        </svg>
                                                        Укус
                                                    </span>
                                                @endif
                                                @if($request['is_pregnant'])
                                                    <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-pink-100 text-pink-800">
                                                        <svg class="h-3 w-3 mr-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                                        </svg>
                                                        Беременность
                                                    </span>
                                                @endif
                                                
                                                @php
                                                    // Рассчитываем приоритет заявки
                                                    $priority = 1; // Базовый приоритет
                                                    
                                                    if ($request['has_bite']) {
                                                        $priority += 10; // Укусы - максимальный приоритет
                                                    }
                                                    
                                                    if ($request['deadline_date'] && $request['deadline_date'] < now()->format('Y-m-d')) {
                                                        $priority += 5; // Просроченные
                                                    }
                                                    
                                                    if ($request['deadline_date'] === now()->format('Y-m-d')) {
                                                        $priority += 3; // На сегодня
                                                    }
                                                    
                                                    if ($request['is_pregnant']) {
                                                        $priority += 2; // Беременные
                                                    }
                                                    
                                                    if ($request['animals_count'] > 1) {
                                                        $priority += 1; // Несколько животных
                                                    }
                                                    
                                                    // Определяем цвет приоритета
                                                    $priorityColor = 'bg-gray-100 text-gray-800';
                                                    if ($priority >= 10) {
                                                        $priorityColor = 'bg-red-100 text-red-800';
                                                    } elseif ($priority >= 5) {
                                                        $priorityColor = 'bg-yellow-100 text-yellow-800';
                                                    } elseif ($priority >= 3) {
                                                        $priorityColor = 'bg-blue-100 text-blue-800';
                                                    }
                                                @endphp
                                                
                                                @if($priority > 1)
                                                    <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $priorityColor }}" title="Приоритет заявки">
                                                        {{ $priority }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Список заявок -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-700">Список заявок</h3>
                    <div class="flex items-center space-x-3">
                        <!-- Переключатель режимов отображения -->
                        <div class="flex rounded-md shadow-sm" role="group">
                            <button type="button" id="tableViewBtn" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-indigo-600 rounded-l-md focus:z-10 focus:ring-2 focus:ring-indigo-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                <span class="hidden sm:inline">Таблица</span>
                            </button>
                            <button type="button" id="mapViewBtn" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50 focus:z-10 focus:ring-2 focus:ring-indigo-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                </svg>
                                <span class="hidden sm:inline">Карта</span>
                            </button>
                        </div>
                        
                        @if(request()->hasAny(['status', 'date_from', 'date_to']))
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                Включены фильтры
                            </span>
                        @endif
                        <button type="button" id="toggleColumns" class="text-gray-500 hover:text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-full p-2" title="Настроить колонки">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Карта для отображения заявок -->
            <div id="mapView" class="hidden">
                <div class="p-4">
                    <div id="requestsMap" class="w-full h-[600px] md:h-[600px] sm:h-[450px] h-[350px] rounded-lg border border-gray-300 overflow-hidden"></div>
                </div>
            </div>
            
            <!-- Табличное представление заявок -->
            <div id="tableView">
                @if($requests->isEmpty())
                    <div class="py-12 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-300 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <p class="mt-4 text-gray-500 text-lg">Заявок не найдено.</p>
                        @if(request()->hasAny(['status', 'date_from', 'date_to']))
                            <p class="mt-2 text-gray-500">Попробуйте изменить параметры фильтрации.</p>
                            <div class="mt-4">
                                <a href="{{ route('admin.osvv.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition text-sm">
                                    Сбросить фильтры
                                </a>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="overflow-x-auto lg:block hidden">
                        <table class="w-full divide-y divide-gray-200 table-fixed">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'id', 'direction' => request('direction') == 'asc' && request('sort') == 'id' ? 'desc' : 'asc']) }}" class="group flex items-center">
                                            ID
                                            <span class="ml-1">
                                                @if(request('sort') == 'id')
                                                    @if(request('direction') == 'asc')
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-500" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                                        </svg>
                                                    @else
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-500" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                        </svg>
                                                    @endif
                                                @else
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-300 group-hover:text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                    </svg>
                                                @endif
                                            </span>
                                        </a>
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-56">Контактное лицо</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Район</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-56">Адрес</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Статус</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-36">
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('direction') == 'asc' && request('sort') == 'created_at' ? 'desc' : 'asc']) }}" class="group flex items-center">
                                            Создана
                                            <span class="ml-1">
                                                @if(request('sort') == 'created_at')
                                                    @if(request('direction') == 'asc')
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-500" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                                        </svg>
                                                    @else
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-500" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                        </svg>
                                                    @endif
                                                @else
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-300 group-hover:text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                    </svg>
                                                @endif
                                            </span>
                                        </a>
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-36">
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'deadline_date', 'direction' => request('direction') == 'asc' && request('sort') == 'deadline_date' ? 'desc' : 'asc']) }}" class="group flex items-center">
                                            Срок выезда
                                            <span class="ml-1">
                                                @if(request('sort') == 'deadline_date')
                                                    @if(request('direction') == 'asc')
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-500" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                                        </svg>
                                                    @else
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-500" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                        </svg>
                                                    @endif
                                                @else
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-300 group-hover:text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                    </svg>
                                                @endif
                                            </span>
                                        </a>
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-36">
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'departure_date', 'direction' => request('direction') == 'asc' && request('sort') == 'departure_date' ? 'desc' : 'asc']) }}" class="group flex items-center">
                                            Последний выезд
                                            <span class="ml-1">
                                                @if(request('sort') == 'departure_date')
                                                    @if(request('direction') == 'asc')
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-500" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                                        </svg>
                                                    @else
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-500" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                        </svg>
                                                    @endif
                                                @else
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-300 group-hover:text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                    </svg>
                                                @endif
                                            </span>
                                        </a>
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Действия</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($requests as $request)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-4 py-4 w-20 whitespace-nowrap text-sm font-medium text-gray-900">{{ $request->id }}</td>
                                        <td class="px-4 py-4 w-56">
                                            <div class="overflow-hidden">
                                                <div class="text-sm font-medium text-gray-900 truncate">{{ $request->contact_name }}</div>
                                                <div class="text-sm text-indigo-600 truncate">
                                                    <a href="tel:{{ $request->contact_phone }}" class="hover:text-indigo-800">{{ $request->contact_phone }}</a>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 w-32">
                                            @if($request->district)
                                                <div class="flex items-center overflow-hidden">
                                                    <span class="text-sm text-gray-900 truncate">{{ $request->district }}</span>
                                                    @if($request->has_bite)
                                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                            <svg class="h-3 w-3 mr-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                            </svg>
                                                        </span>
                                                    @endif
                                                    
                                                    @if($request->is_pregnant)
                                                        <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-pink-100 text-pink-800">
                                                            <svg class="h-3 w-3 mr-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                                            </svg>
                                                            Беременность
                                                        </span>
                                                    @endif
                                                    
                                                    @php
                                                        // Рассчитываем приоритет заявки
                                                        $priority = 1; // Базовый приоритет
                                                        
                                                        if ($request->has_bite) {
                                                            $priority += 10; // Укусы - максимальный приоритет
                                                        }
                                                        
                                                        if ($request->deadline_date && $request->deadline_date < now()->format('Y-m-d')) {
                                                            $priority += 5; // Просроченные
                                                        }
                                                        
                                                        if ($request->deadline_date === now()->format('Y-m-d')) {
                                                            $priority += 3; // На сегодня
                                                        }
                                                        
                                                        if ($request->is_pregnant) {
                                                            $priority += 2; // Беременные
                                                        }
                                                        
                                                        if ($request->animals_count > 1) {
                                                            $priority += 1; // Несколько животных
                                                        }
                                                        
                                                        // Определяем цвет приоритета
                                                        $priorityColor = 'bg-gray-100 text-gray-800';
                                                        if ($priority >= 10) {
                                                            $priorityColor = 'bg-red-100 text-red-800';
                                                        } elseif ($priority >= 5) {
                                                            $priorityColor = 'bg-yellow-100 text-yellow-800';
                                                        } elseif ($priority >= 3) {
                                                            $priorityColor = 'bg-blue-100 text-blue-800';
                                                        }
                                                    @endphp
                                                    
                                                    @if($priority > 1)
                                                        <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $priorityColor }}" title="Приоритет заявки">
                                                            {{ $priority }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="flex items-center overflow-hidden">
                                                    <span class="text-sm text-gray-400">—</span>
                                                    @if($request->has_bite)
                                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                            <svg class="h-3 w-3 mr-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                            </svg>
                                                        </span>
                                                    @endif
                                                    
                                                    @if($request->is_pregnant)
                                                        <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-pink-100 text-pink-800">
                                                            <svg class="h-3 w-3 mr-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                                            </svg>
                                                            Беременность
                                                        </span>
                                                    @endif
                                                    
                                                    @php
                                                        // Рассчитываем приоритет заявки
                                                        $priority = 1; // Базовый приоритет
                                                        
                                                        if ($request->has_bite) {
                                                            $priority += 10; // Укусы - максимальный приоритет
                                                        }
                                                        
                                                        if ($request->deadline_date && $request->deadline_date < now()->format('Y-m-d')) {
                                                            $priority += 5; // Просроченные
                                                        }
                                                        
                                                        if ($request->deadline_date === now()->format('Y-m-d')) {
                                                            $priority += 3; // На сегодня
                                                        }
                                                        
                                                        if ($request->is_pregnant) {
                                                            $priority += 2; // Беременные
                                                        }
                                                        
                                                        if ($request->animals_count > 1) {
                                                            $priority += 1; // Несколько животных
                                                        }
                                                        
                                                        // Определяем цвет приоритета
                                                        $priorityColor = 'bg-gray-100 text-gray-800';
                                                        if ($priority >= 10) {
                                                            $priorityColor = 'bg-red-100 text-red-800';
                                                        } elseif ($priority >= 5) {
                                                            $priorityColor = 'bg-yellow-100 text-yellow-800';
                                                        } elseif ($priority >= 3) {
                                                            $priorityColor = 'bg-blue-100 text-blue-800';
                                                        }
                                                    @endphp
                                                    
                                                    @if($priority > 1)
                                                        <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $priorityColor }}" title="Приоритет заявки">
                                                            {{ $priority }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 w-56">
                                            <div class="text-sm text-gray-900 overflow-hidden">
                                                <span class="truncate block">{{ $request->location_address }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 w-32 whitespace-nowrap">
                                            @php
                                                $statusClass = 'bg-gray-100 text-gray-800';
                                                $statusText = 'Неизвестно';
                                                
                                                switch($request->status) {
                                                    case 'new':
                                                        $statusClass = 'bg-blue-100 text-blue-800 border-blue-200';
                                                        $statusText = 'Новая заявка';
                                                        break;
                                                    case 'processing':
                                                        $statusClass = 'bg-yellow-100 text-yellow-800 border-yellow-200';
                                                        $statusText = 'В обработке';
                                                        break;
                                                    case 'capture_scheduled':
                                                        $statusClass = 'bg-purple-100 text-purple-800 border-purple-200';
                                                        $statusText = 'Запланирован отлов';
                                                        break;
                                                    case 'captured':
                                                        $statusClass = 'bg-indigo-100 text-indigo-800 border-indigo-200';
                                                        $statusText = 'Животное отловлено';
                                                        break;
                                                    case 'in_shelter':
                                                        $statusClass = 'bg-cyan-100 text-cyan-800 border-cyan-200';
                                                        $statusText = 'В приюте';
                                                        break;
                                                    case 'sterilized':
                                                        $statusClass = 'bg-teal-100 text-teal-800 border-teal-200';
                                                        $statusText = 'Стерилизовано';
                                                        break;
                                                    case 'vaccinated':
                                                        $statusClass = 'bg-green-100 text-green-800 border-green-200';
                                                        $statusText = 'Вакцинировано';
                                                        break;
                                                    case 'ready_for_return':
                                                        $statusClass = 'bg-lime-100 text-lime-800 border-lime-200';
                                                        $statusText = 'Готово к возврату';
                                                        break;
                                                    case 'returned':
                                                        $statusClass = 'bg-amber-100 text-amber-800 border-amber-200';
                                                        $statusText = 'Возвращено';
                                                        break;
                                                    case 'completed':
                                                        $statusClass = 'bg-emerald-100 text-emerald-800 border-emerald-200';
                                                        $statusText = 'Завершено';
                                                        break;
                                                    case 'cancelled':
                                                        $statusClass = 'bg-red-100 text-red-800 border-red-200';
                                                        $statusText = 'Отменено';
                                                        break;
                                                }
                                            @endphp
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full border {{ $statusClass }}">
                                                {{ $statusText }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 w-36 whitespace-nowrap">
                                            <span class="text-sm text-gray-600">{{ $request->created_at->format('d.m.Y H:i') }}</span>
                                        </td>
                                        <td class="px-4 py-4 w-36 whitespace-nowrap">
                                            @if($request->deadline_date)
                                                <span class="text-sm {{ $request->deadline_date->isPast() && !$request->departure_date ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                                                    {{ $request->deadline_date->format('d.m.Y') }}
                                                    @if($request->deadline_date->isPast() && !$request->departure_date)
                                                        <span class="inline-flex items-center ml-1 px-1.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">!</span>
                                                    @endif
                                                </span>
                                            @else
                                                <span class="text-sm text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 w-36 whitespace-nowrap">
                                            @if($request->departure_date)
                                                <span class="text-sm text-green-600">{{ $request->departure_date->format('d.m.Y') }}</span>
                                            @else
                                                <span class="text-sm text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 w-32 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex flex-col space-y-1">
                                            <a href="{{ route('admin.osvv.show', $request) }}" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1 rounded-md transition">
                                                <span class="flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    Подробнее
                                                </span>
                                            </a>
                                                @if($request->status !== 'completed' && $request->status !== 'cancelled')
                                                <button onclick="predictCompletion({{ $request->id }})" class="text-purple-600 hover:text-purple-900 bg-purple-50 hover:bg-purple-100 px-3 py-1 rounded-md transition text-xs">
                                                    🧠 AI-прогноз
                                                </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Мобильное представление в виде карточек -->
                    <div class="lg:hidden block">
                        <div class="space-y-4 px-4 py-2">
                            @foreach($requests as $request)
                                <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                                    <div class="px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                                        <span class="font-medium text-gray-900">Заявка #{{ $request->id }}</span>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full border 
                                            @php
                                                $statusClass = 'bg-gray-100 text-gray-800';
                                                $statusText = 'Неизвестно';
                                                
                                                switch($request->status) {
                                                    case 'new':
                                                        $statusClass = 'bg-blue-100 text-blue-800 border-blue-200';
                                                        $statusText = 'Новая заявка';
                                                        break;
                                                    case 'processing':
                                                        $statusClass = 'bg-yellow-100 text-yellow-800 border-yellow-200';
                                                        $statusText = 'В обработке';
                                                        break;
                                                    case 'capture_scheduled':
                                                        $statusClass = 'bg-purple-100 text-purple-800 border-purple-200';
                                                        $statusText = 'Запланирован отлов';
                                                        break;
                                                    case 'captured':
                                                        $statusClass = 'bg-indigo-100 text-indigo-800 border-indigo-200';
                                                        $statusText = 'Животное отловлено';
                                                        break;
                                                    case 'in_shelter':
                                                        $statusClass = 'bg-cyan-100 text-cyan-800 border-cyan-200';
                                                        $statusText = 'В приюте';
                                                        break;
                                                    case 'sterilized':
                                                        $statusClass = 'bg-teal-100 text-teal-800 border-teal-200';
                                                        $statusText = 'Стерилизовано';
                                                        break;
                                                    case 'vaccinated':
                                                        $statusClass = 'bg-green-100 text-green-800 border-green-200';
                                                        $statusText = 'Вакцинировано';
                                                        break;
                                                    case 'ready_for_return':
                                                        $statusClass = 'bg-lime-100 text-lime-800 border-lime-200';
                                                        $statusText = 'Готово к возврату';
                                                        break;
                                                    case 'returned':
                                                        $statusClass = 'bg-amber-100 text-amber-800 border-amber-200';
                                                        $statusText = 'Возвращено';
                                                        break;
                                                    case 'completed':
                                                        $statusClass = 'bg-emerald-100 text-emerald-800 border-emerald-200';
                                                        $statusText = 'Завершено';
                                                        break;
                                                    case 'cancelled':
                                                        $statusClass = 'bg-red-100 text-red-800 border-red-200';
                                                        $statusText = 'Отменено';
                                                        break;
                                                }
                                            @endphp
                                            {{ $statusClass }}">
                                            {{ $statusText }}
                                        </span>
                                    </div>
                                    <div class="px-4 py-3 space-y-2">
                                        <!-- Контактные данные -->
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-500">Контактное лицо:</span>
                                            <span class="text-sm font-medium text-gray-900">{{ $request->contact_name }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-500">Телефон:</span>
                                            <a href="tel:{{ $request->contact_phone }}" class="text-sm font-medium text-indigo-600">
                                                {{ $request->contact_phone }}
                                            </a>
                                        </div>
                                        
                                        <!-- Адрес -->
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-500">Район:</span>
                                            <span class="text-sm font-medium text-gray-900">
                                                {{ $request->district ?: '—' }}
                                                @if($request->has_bite)
                                                    <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                        </svg>
                                                    </span>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-500">Адрес:</span>
                                            <span class="text-sm font-medium text-gray-900 text-right">{{ $request->location_address }}</span>
                                        </div>
                                        
                                        <!-- Даты -->
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-500">Создана:</span>
                                            <span class="text-sm font-medium text-gray-900">{{ $request->created_at->format('d.m.Y H:i') }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-500">Срок выезда:</span>
                                            <span class="text-sm font-medium {{ $request->deadline_date && $request->deadline_date->isPast() && !$request->departure_date ? 'text-red-600' : 'text-gray-900' }}">
                                                {{ $request->deadline_date ? $request->deadline_date->format('d.m.Y') : '—' }}
                                                @if($request->deadline_date && $request->deadline_date->isPast() && !$request->departure_date)
                                                    <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">!</span>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-500">Последний выезд:</span>
                                            <span class="text-sm font-medium {{ $request->departure_date ? 'text-green-600' : 'text-gray-400' }}">
                                                {{ $request->departure_date ? $request->departure_date->format('d.m.Y') : '—' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 flex justify-end">
                                        <a href="{{ route('admin.osvv.show', $request) }}" class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            Подробнее
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Пагинация -->
                    <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
                        {{ $requests->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div id="columnSettingsPanel" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">Настройка колонок</h3>
                <button type="button" class="text-gray-400 hover:text-gray-500" id="closeColumnSettings">
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="px-6 py-4">
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input id="col-id" type="checkbox" class="h-4 w-4 text-indigo-600 border-gray-300 rounded" checked>
                        <label for="col-id" class="ml-2 block text-sm text-gray-900">ID</label>
                    </div>
                    <div class="flex items-center">
                        <input id="col-contact" type="checkbox" class="h-4 w-4 text-indigo-600 border-gray-300 rounded" checked>
                        <label for="col-contact" class="ml-2 block text-sm text-gray-900">Контактное лицо</label>
                    </div>
                    <div class="flex items-center">
                        <input id="col-district" type="checkbox" class="h-4 w-4 text-indigo-600 border-gray-300 rounded" checked>
                        <label for="col-district" class="ml-2 block text-sm text-gray-900">Район</label>
                    </div>
                    <div class="flex items-center">
                        <input id="col-address" type="checkbox" class="h-4 w-4 text-indigo-600 border-gray-300 rounded" checked>
                        <label for="col-address" class="ml-2 block text-sm text-gray-900">Адрес</label>
                    </div>
                    <div class="flex items-center">
                        <input id="col-status" type="checkbox" class="h-4 w-4 text-indigo-600 border-gray-300 rounded" checked>
                        <label for="col-status" class="ml-2 block text-sm text-gray-900">Статус</label>
                    </div>
                    <div class="flex items-center">
                        <input id="col-created" type="checkbox" class="h-4 w-4 text-indigo-600 border-gray-300 rounded" checked>
                        <label for="col-created" class="ml-2 block text-sm text-gray-900">Создана</label>
                    </div>
                    <div class="flex items-center">
                        <input id="col-deadline" type="checkbox" class="h-4 w-4 text-indigo-600 border-gray-300 rounded" checked>
                        <label for="col-deadline" class="ml-2 block text-sm text-gray-900">Срок выезда</label>
                    </div>
                    <div class="flex items-center">
                        <input id="col-departure" type="checkbox" class="h-4 w-4 text-indigo-600 border-gray-300 rounded" checked>
                        <label for="col-departure" class="ml-2 block text-sm text-gray-900">Последний выезд</label>
                    </div>
                </div>
            </div>
            <div class="px-6 py-3 bg-gray-50 text-right rounded-b-lg">
                <button type="button" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500" id="saveColumnSettings">
                    Сохранить
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Функционал переключения панели фильтров
            const toggleFiltersBtn = document.getElementById('toggleFilters');
            const filterPanel = document.getElementById('filterPanel');
            
            toggleFiltersBtn.addEventListener('click', function() {
                filterPanel.classList.toggle('hidden');
                // Изменение иконки на кнопке
                const icon = this.querySelector('svg');
                if (filterPanel.classList.contains('hidden')) {
                    icon.innerHTML = `<path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />`;
                } else {
                    icon.innerHTML = `<path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 1.414z" clip-rule="evenodd" />`;
                }
            });
            
            // Функционал настройки колонок
            const toggleColumnsBtn = document.getElementById('toggleColumns');
            const columnSettingsPanel = document.getElementById('columnSettingsPanel');
            const closeColumnSettingsBtn = document.getElementById('closeColumnSettings');
            const saveColumnSettingsBtn = document.getElementById('saveColumnSettings');
            
            toggleColumnsBtn.addEventListener('click', function() {
                columnSettingsPanel.classList.remove('hidden');
            });
            
            closeColumnSettingsBtn.addEventListener('click', function() {
                columnSettingsPanel.classList.add('hidden');
            });
            
            saveColumnSettingsBtn.addEventListener('click', function() {
                const columns = {
                    'id': document.getElementById('col-id').checked,
                    'contact': document.getElementById('col-contact').checked,
                    'district': document.getElementById('col-district').checked,
                    'address': document.getElementById('col-address').checked,
                    'status': document.getElementById('col-status').checked,
                    'created': document.getElementById('col-created').checked,
                    'deadline': document.getElementById('col-deadline').checked,
                    'departure': document.getElementById('col-departure').checked
                };
                
                // Сохранение настроек колонок в localStorage
                localStorage.setItem('osvv_columns', JSON.stringify(columns));
                
                // Применение настроек
                applyColumnSettings(columns);
                
                // Закрытие панели
                columnSettingsPanel.classList.add('hidden');
            });
            
            // Загрузка сохраненных настроек колонок
            function loadColumnSettings() {
                const savedSettings = localStorage.getItem('osvv_columns');
                if (savedSettings) {
                    const columns = JSON.parse(savedSettings);
                    
                    // Применение настроек к чекбоксам
                    document.getElementById('col-id').checked = columns.id;
                    document.getElementById('col-contact').checked = columns.contact;
                    document.getElementById('col-district').checked = columns.district;
                    document.getElementById('col-address').checked = columns.address;
                    document.getElementById('col-status').checked = columns.status;
                    document.getElementById('col-created').checked = columns.created;
                    document.getElementById('col-deadline').checked = columns.deadline;
                    document.getElementById('col-departure').checked = columns.departure;
                    
                    // Применение настроек к таблице
                    applyColumnSettings(columns);
                }
            }
            
            // Применение настроек колонок
            function applyColumnSettings(columns) {
                const table = document.querySelector('table');
                const ths = table.querySelectorAll('thead th');
                const rows = table.querySelectorAll('tbody tr');
                
                // Применение для заголовков
                ths[0].style.display = columns.id ? '' : 'none';
                ths[1].style.display = columns.contact ? '' : 'none';
                ths[2].style.display = columns.district ? '' : 'none';
                ths[3].style.display = columns.address ? '' : 'none';
                ths[4].style.display = columns.status ? '' : 'none';
                ths[5].style.display = columns.created ? '' : 'none';
                ths[6].style.display = columns.deadline ? '' : 'none';
                ths[7].style.display = columns.departure ? '' : 'none';
                
                // Применение для строк
                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    cells[0].style.display = columns.id ? '' : 'none';
                    cells[1].style.display = columns.contact ? '' : 'none';
                    cells[2].style.display = columns.district ? '' : 'none';
                    cells[3].style.display = columns.address ? '' : 'none';
                    cells[4].style.display = columns.status ? '' : 'none';
                    cells[5].style.display = columns.created ? '' : 'none';
                    cells[6].style.display = columns.deadline ? '' : 'none';
                    cells[7].style.display = columns.departure ? '' : 'none';
                });
            }
            
            // Загрузка настроек при загрузке страницы
            loadColumnSettings();
            
            // Переключение между табличным и картографическим представлением
            const tableViewBtn = document.getElementById('tableViewBtn');
            const mapViewBtn = document.getElementById('mapViewBtn');
            const tableView = document.getElementById('tableView');
            const mapView = document.getElementById('mapView');
            
            tableViewBtn.addEventListener('click', function() {
                tableView.classList.remove('hidden');
                mapView.classList.add('hidden');
                
                tableViewBtn.classList.remove('bg-white', 'text-gray-700');
                tableViewBtn.classList.add('bg-indigo-600', 'text-white');
                
                mapViewBtn.classList.remove('bg-indigo-600', 'text-white');
                mapViewBtn.classList.add('bg-white', 'text-gray-700');
                
                // Сохранение выбранного режима просмотра
                localStorage.setItem('osvv_view_mode', 'table');
            });
            
            mapViewBtn.addEventListener('click', function() {
                tableView.classList.add('hidden');
                mapView.classList.remove('hidden');
                
                mapViewBtn.classList.remove('bg-white', 'text-gray-700');
                mapViewBtn.classList.add('bg-indigo-600', 'text-white');
                
                tableViewBtn.classList.remove('bg-indigo-600', 'text-white');
                tableViewBtn.classList.add('bg-white', 'text-gray-700');
                
                // Сохранение выбранного режима просмотра
                localStorage.setItem('osvv_view_mode', 'map');
                
                // Если карта еще не инициализирована
                if (!window.requestsMapInstance) {
                    initMap();
                }
            });
            
            // Загрузка последнего выбранного режима просмотра
            const savedViewMode = localStorage.getItem('osvv_view_mode');
            if (savedViewMode === 'map') {
                mapViewBtn.click();
            }
        });
        
        // Функция для переключения детального списка на выезд
        function toggleDepartureList() {
            const detailElement = document.getElementById('departureListDetail');
            if (detailElement) {
                detailElement.classList.toggle('hidden');
            }
        }
        
        // AI-функция для прогноза времени выполнения заявки
        function predictCompletion(requestId) {
            // Создаем модальное окно
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50';
            modal.innerHTML = `
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900 flex items-center">
                                🧠 AI-Прогноз времени выполнения
                            </h3>
                            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <div id="prediction-content" class="text-center py-4">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600 mx-auto"></div>
                            <p class="mt-2 text-gray-600">Анализирую данные...</p>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            // Функция закрытия модального окна
            window.closeModal = function() {
                document.body.removeChild(modal);
                delete window.closeModal;
            };
            
            // Закрытие по клику вне модального окна
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    window.closeModal();
                }
            });
            
            // Загружаем прогноз
            fetch(`/admin/analytics/ai-predict-completion/${requestId}`)
                .then(response => response.json())
                .then(data => {
                    const content = document.getElementById('prediction-content');
                    
                    if (data.error) {
                        content.innerHTML = `
                            <div class="text-red-600">
                                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="font-medium">Ошибка прогноза</p>
                                <p class="text-sm mt-1">${data.error}</p>
                            </div>
                        `;
                        return;
                    }
                    
                    const confidenceColor = data.confidence > 70 ? 'green' : data.confidence > 40 ? 'yellow' : 'red';
                    const confidenceText = data.confidence > 70 ? 'Высокая' : data.confidence > 40 ? 'Средняя' : 'Низкая';
                    
                    content.innerHTML = `
                        <div class="space-y-4">
                            <div class="bg-purple-50 rounded-lg p-4">
                                <div class="text-center">
                                    <div class="text-3xl font-bold text-purple-600 mb-1">
                                        ${data.estimated_hours} ч
                                    </div>
                                    <p class="text-sm text-gray-600">Прогнозируемое время выполнения</p>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div class="bg-gray-50 rounded p-3">
                                    <p class="text-gray-600">Уверенность AI:</p>
                                    <p class="font-medium">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-${confidenceColor}-100 text-${confidenceColor}-800">
                                            ${data.confidence}% (${confidenceText})
                                        </span>
                                    </p>
                                </div>
                                <div class="bg-gray-50 rounded p-3">
                                    <p class="text-gray-600">Похожих случаев:</p>
                                    <p class="font-medium">${data.similar_cases}</p>
                                </div>
                            </div>
                            
                            ${data.base_time ? `
                                <div class="bg-blue-50 rounded p-3 text-sm">
                                    <p class="text-gray-600 mb-1">Детали расчета:</p>
                                    <div class="space-y-1">
                                        <div class="flex justify-between">
                                            <span>Базовое время:</span>
                                            <span class="font-medium">${data.base_time} ч</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Корректировка:</span>
                                            <span class="font-medium ${data.adjustment >= 0 ? 'text-red-600' : 'text-green-600'}">
                                                ${data.adjustment >= 0 ? '+' : ''}${data.adjustment} ч
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            ` : ''}
                            
                            ${data.factors && data.factors.length > 0 ? `
                                <div class="bg-yellow-50 rounded p-3 text-sm">
                                    <p class="text-gray-600 mb-2">Учтенные факторы:</p>
                                    <div class="space-y-1">
                                        ${data.factors.map(factor => {
                                            const factorNames = {
                                                'urgent_priority': '🚨 Срочный приоритет',
                                                'high_workload': '📈 Высокая загруженность',
                                                'peak_day': '📅 Пиковый день недели',
                                                'insufficient_historical_data': '📊 Недостаточно данных'
                                            };
                                            return `<div class="text-xs bg-white px-2 py-1 rounded">${factorNames[factor] || factor}</div>`;
                                        }).join('')}
                                    </div>
                                </div>
                            ` : ''}
                            
                            <div class="text-xs text-gray-500 text-center">
                                Прогноз основан на анализе исторических данных и текущих условий
                            </div>
                        </div>
                    `;
                })
                .catch(error => {
                    console.error('Ошибка AI-прогноза:', error);
                    const content = document.getElementById('prediction-content');
                    content.innerHTML = `
                        <div class="text-red-600">
                            <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="font-medium">Ошибка загрузки</p>
                            <p class="text-sm mt-1">Не удалось получить прогноз</p>
                        </div>
                    `;
                });
        }
    </script>
    
    <!-- Подключение API Яндекс.Карт -->
    <script src="https://api-maps.yandex.ru/2.1/?apikey=aba2bc56-907f-41a7-9377-d32e69eff205&lang=ru_RU"></script>
    
    <script>
        // Инициализация карты
        function initMap() {
            ymaps.ready(function() {
                // Создаем карту с центром в указанной точке
                const map = new ymaps.Map('requestsMap', {
                    center: [51.661535, 39.200287], // Центр Воронежа по умолчанию
                    zoom: 10,
                    controls: ['zoomControl', 'searchControl', 'fullscreenControl']
                });
                
                // Создаем кластеризатор
                const clusterer = new ymaps.Clusterer({
                    preset: 'islands#blueClusterIcons',
                    groupByCoordinates: false,
                    clusterDisableClickZoom: false,
                    clusterHideIconOnBalloonOpen: false,
                    geoObjectHideIconOnBalloonOpen: false
                });
                
                // Массив для хранения меток
                const placemarks = [];
                
                // Добавляем метки на карту для каждой заявки с координатами
                @foreach($requests as $request)
                    @php
                        // Получаем все адреса заявки
                        $allAddresses = $request->getAllAddresses();
                    @endphp
                    
                    @if(count($allAddresses) > 0)
                        @foreach($allAddresses as $addressIndex => $address)
                            @if($address['latitude'] && $address['longitude'])
                                @php
                                    $isMainAddress = $address['is_primary'];
                                    $addressSuffix = $isMainAddress ? '' : '_addr_' . $addressIndex;
                                @endphp
                                
                                const placemark_{{ $request->id }}{{ $addressSuffix }} = new ymaps.Placemark(
                                    [{{ $address['latitude'] }}, {{ $address['longitude'] }}], 
                                    {
                                        balloonContentHeader: 'Заявка #{{ $request->id }}{{ $isMainAddress ? "" : " (доп. адрес " . ($addressIndex) . ")" }}',
                                        balloonContentBody: `
                                            <div class="balloon-content">
                                                <p><strong>{{ $isMainAddress ? "Основной адрес" : "Дополнительный адрес" }}:</strong> {{ $address['address'] }}</p>
                                                @if($address['landmark'])
                                                <p><strong>Ориентир:</strong> {{ $address['landmark'] }}</p>
                                                @endif
                                                <p><strong>Контакт:</strong> {{ $request->contact_name }}</p>
                                                <p><strong>Телефон:</strong> {{ $request->contact_phone }}</p>
                                                <p><strong>Статус:</strong> 
                                                    @php
                                                        $statusText = 'Неизвестно';
                                                        switch($request->status) {
                                                            case 'new': $statusText = 'Новая заявка'; break;
                                                            case 'processing': $statusText = 'В обработке'; break;
                                                            case 'capture_scheduled': $statusText = 'Запланирован отлов'; break;
                                                            case 'captured': $statusText = 'Животное отловлено'; break;
                                                            case 'in_shelter': $statusText = 'В приюте'; break;
                                                            case 'sterilized': $statusText = 'Стерилизовано'; break;
                                                            case 'vaccinated': $statusText = 'Вакцинировано'; break;
                                                            case 'ready_for_return': $statusText = 'Готово к возврату'; break;
                                                            case 'returned': $statusText = 'Возвращено'; break;
                                                            case 'completed': $statusText = 'Завершено'; break;
                                                            case 'cancelled': $statusText = 'Отменено'; break;
                                                        }
                                                    @endphp
                                                    {{ $statusText }}
                                                </p>
                                                <p class="text-sm text-gray-600 mt-2">
                                                    Всего адресов в заявке: {{ count($allAddresses) }}
                                                </p>
                                            </div>
                                        `,
                                        balloonContentFooter: `<a href="{{ route('admin.osvv.show', $request) }}" class="text-indigo-600 hover:text-indigo-900">Подробнее →</a>`,
                                        clusterCaption: 'Заявка #{{ $request->id }}{{ $isMainAddress ? "" : " (доп.)" }}',
                                        hintContent: 'Заявка #{{ $request->id }}: {{ $address["address"] }}{{ $isMainAddress ? " (основной)" : " (дополнительный)" }}'
                                    },
                                    {
                                        @php
                                            // Определяем цвет в зависимости от статуса заявки
                                            $iconColor = 'blue';
                                            switch($request->status) {
                                                case 'new': $iconColor = 'blue'; break;
                                                case 'processing': $iconColor = 'yellow'; break;
                                                case 'capture_scheduled': $iconColor = 'violet'; break;
                                                case 'captured': $iconColor = 'indigo'; break;
                                                case 'in_shelter': $iconColor = 'cyan'; break;
                                                case 'sterilized': $iconColor = 'darkGreen'; break;
                                                case 'vaccinated': $iconColor = 'green'; break;
                                                case 'ready_for_return': $iconColor = 'orange'; break;
                                                case 'returned': $iconColor = 'brown'; break;
                                                case 'completed': $iconColor = 'green'; break;
                                                case 'cancelled': $iconColor = 'red'; break;
                                            }
                                        @endphp
                                        preset: 'islands#{{ $iconColor }}{{ $isMainAddress ? "DotIcon" : "CircleIcon" }}'
                                    }
                                );
                                
                                placemarks.push(placemark_{{ $request->id }}{{ $addressSuffix }});
                            @endif
                        @endforeach
                    @endif
                @endforeach
                
                // Добавляем метки в кластеризатор
                clusterer.add(placemarks);
                
                // Добавляем кластеризатор на карту
                map.geoObjects.add(clusterer);
                
                // Центрируем и масштабируем карту так, чтобы были видны все метки
                if (placemarks.length > 0) {
                    map.setBounds(clusterer.getBounds(), {
                        checkZoomRange: true
                    });
                }
                
                // Сохраняем экземпляр карты
                window.requestsMapInstance = map;
            });
        }
    </script>
@endsection 