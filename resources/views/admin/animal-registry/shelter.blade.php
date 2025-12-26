@extends('admin.layout')

@section('header')
    Животные в приюте
@endsection

@section('content')
    <div class="space-y-6">
        <!-- Хлебные крошки и действия -->
        <div class="bg-white shadow rounded-lg">
            <div class="bg-gradient-to-r from-purple-600 to-indigo-600 px-6 py-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V9.375c0-.621.504-1.125 1.125-1.125h.75c.621 0 1.125.504 1.125 1.125v11.25c-4.5 0-8.25-3.875-8.25-8.615 0-1.58.42-3.065 1.157-4.359" />
                        </svg>
                        Животные в приюте ({{ $animals->total() }})
                    </h3>
                    <div class="flex space-x-2">
                        <a href="{{ route('admin.animals.create') }}" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-md text-sm font-medium">
                            + Добавить животное
                        </a>
                        <a href="{{ route('admin.animal-registry.index') }}" class="text-white hover:text-purple-200">
                            ← Назад к главной
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Фильтры -->
        <div class="bg-white shadow rounded-lg p-6">
            <form method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <!-- Поиск -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Поиск</label>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Имя, порода, номер чипа, бирки, клетки..." 
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                    </div>

                    <!-- Тип -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Тип</label>
                        <select name="type" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                            <option value="">Все типы</option>
                            @foreach($types as $key => $label)
                                <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Пол -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Пол</label>
                        <select name="gender" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                            <option value="">Все</option>
                            @foreach($genders as $key => $label)
                                <option value="{{ $key }}" {{ request('gender') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Этап -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Этап</label>
                        <select name="stage" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                            <option value="">Все этапы</option>
                            @foreach($stages as $stage)
                                <option value="{{ $stage->id }}" {{ request('stage') == $stage->id ? 'selected' : '' }}>
                                    {{ $stage->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex justify-between items-center">
                    <div class="flex space-x-2">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Найти
                        </button>
                        <a href="{{ route('admin.animal-registry.shelter') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            Сбросить
                        </a>
                    </div>

                    <!-- Сортировка -->
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700">Сортировка:</label>
                        <select name="sort" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm">
                            <option value="arrived_at" {{ request('sort') === 'arrived_at' ? 'selected' : '' }}>По дате поступления</option>
                            <option value="cage_number" {{ request('sort') === 'cage_number' ? 'selected' : '' }}>По номеру клетки</option>
                            <option value="type" {{ request('sort') === 'type' ? 'selected' : '' }}>По типу</option>
                            <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>По имени</option>
                        </select>
                        <select name="direction" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm">
                            <option value="desc" {{ request('direction') === 'desc' ? 'selected' : '' }}>По убыванию</option>
                            <option value="asc" {{ request('direction') === 'asc' ? 'selected' : '' }}>По возрастанию</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>

        <!-- Таблица животных -->
        @if($animals->count() > 0)
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-purple-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-purple-800 uppercase tracking-wider">
                                    Фото
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-purple-800 uppercase tracking-wider">
                                    Животное
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-purple-800 uppercase tracking-wider">
                                    Источник
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-purple-800 uppercase tracking-wider">
                                    Физические данные
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-purple-800 uppercase tracking-wider">
                                    Этап
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-purple-800 uppercase tracking-wider">
                                    В приюте
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-purple-800 uppercase tracking-wider">
                                    Действия
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($animals as $animal)
                                <tr class="hover:bg-gray-50">
                                    <!-- Фото -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex-shrink-0 h-12 w-12">
                                            @php
                                                $displayPhoto = $animal->photo ?? ($animal->registrationCard->photo_face ?? null);
                                            @endphp
                                            @if($displayPhoto)
                                                <img src="{{ asset('storage/' . $displayPhoto) }}" 
                                                     alt="{{ $animal->name ?? 'Животное' }}"
                                                     class="h-12 w-12 rounded-lg object-cover border border-purple-200">
                                            @else
                                                <div class="h-12 w-12 rounded-lg bg-purple-100 border border-purple-200 flex items-center justify-center">
                                                    @if($animal->type === 'dog')
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-purple-600">
                                                            <path d="M11.25 16.25h1.5L12 17z"/>
                                                            <path d="M16 14v.5"/>
                                                            <path d="M4.42 11.247A13.152 13.152 0 0 0 4 14.556C4 18.728 7.582 21 12 21s8-2.272 8-6.444a11.702 11.702 0 0 0-.493-3.309"/>
                                                            <path d="M8 14v.5"/>
                                                            <path d="M8.5 8.5c-.384 1.05-1.083 2.028-2.344 2.5-1.931.722-3.576-.297-3.656-1-.113-.994 1.177-6.53 4-7 1.923-.321 3.651.845 3.651 2.235A7.497 7.497 0 0 1 14 5.277c0-1.39 1.844-2.598 3.767-2.277 2.823.47 4.113 6.006 4 7-.08.703-1.725 1.722-3.656 1-1.261-.472-1.855-1.45-2.239-2.5"/>
                                                        </svg>
                                                    @elseif($animal->type === 'cat')
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-purple-600">
                                                            <path d="M12 5c.67 0 1.35.09 2 .26 1.78-2 5.03-2.84 6.42-2.26 1.4.58-.42 7-.42 7 .57 1.07 1 2.24 1 3.44C21 17.9 16.97 21 12 21s-9-3-9-7.56c0-1.25.5-2.4 1-3.44 0 0-1.89-6.42-.5-7 1.39-.58 4.72.23 6.5 2.23A9.04 9.04 0 0 1 12 5Z"/>
                                                            <path d="M8 14v.5"/>
                                                            <path d="M16 14v.5"/>
                                                            <path d="M11.25 16.25h1.5L12 17z"/>
                                                        </svg>
                                                    @else
                                                        <span class="text-purple-600 text-lg">🐾</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    
                                    <!-- Животное -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">
                                                    @if($animal->cage_number)
                                                        Вольер №{{ $animal->cage_number }}
                                                    @elseif($animal->name)
                                                        {{ $animal->name }}
                                                    @else
                                                        Без имени
                                                    @endif
                                                </div>
                                                <div class="text-sm text-gray-600">
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                        {{ $types[$animal->type] ?? $animal->type }}
                                                    </span>
                                                    <span class="ml-2 text-gray-500">{{ $animal->genderName }}</span>
                                                </div>
                                                @if($animal->breed)
                                                    <div class="text-xs text-gray-500 mt-1">Порода: {{ $animal->breed }}</div>
                                                @endif
                                                @if($animal->color)
                                                    <div class="text-xs text-gray-500">Окрас: {{ $animal->color }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Источник -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                <div class="h-8 w-8 rounded-full bg-purple-100 flex items-center justify-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V9.375c0-.621.504-1.125 1.125-1.125h.75c.621 0 1.125.504 1.125 1.125v11.25c-4.5 0-8.25-3.875-8.25-8.615 0-1.58.42-3.065 1.157-4.359" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-purple-800">Приют</div>
                                                <div class="text-xs text-gray-500">Прямое поступление</div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Физические данные -->
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            @if($animal->age_months)
                                                <div class="flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    {{ floor($animal->age_months / 12) }}л {{ $animal->age_months % 12 }}мес
                                                </div>
                                            @endif
                                            @if($animal->weight)
                                                <div class="flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                                                    </svg>
                                                    {{ $animal->weight }} кг
                                                </div>
                                            @endif
                                            @if($animal->chip_number)
                                                <div class="text-xs text-gray-500 mt-1">Чип: {{ $animal->chip_number }}</div>
                                            @endif
                                            @if($animal->tag_number)
                                                <div class="text-xs text-gray-500">Бирка: {{ $animal->tag_number }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    
                                    <!-- Этап -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($animal->currentStage)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ $animal->currentStage->name }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Не назначен
                                            </span>
                                        @endif
                                    </td>
                                    
                                    <!-- В приюте -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @php
                                            $daysInShelter = abs(round(now()->diffInDays($animal->arrived_at)));
                                        @endphp
                                        <div class="flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a4 4 0 100-8 4 4 0 000 8zm0 0v4m-4-4h8m-4-4v4" />
                                            </svg>
                                            {{ $daysInShelter }} 
                                            @if($daysInShelter == 1)
                                                день
                                            @elseif($daysInShelter >= 2 && $daysInShelter <= 4)
                                                дня
                                            @else
                                                дней
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            с {{ $animal->arrived_at->format('d.m.Y') }}
                                        </div>
                                    </td>
                                    
                                    <!-- Действия -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('admin.animals.show', $animal) }}" 
                                               class="inline-flex items-center px-3 py-1 bg-purple-600 text-white text-xs rounded hover:bg-purple-700 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                Просмотр
                                            </a>
                                            <a href="{{ route('admin.animals.edit', $animal) }}" 
                                               class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Редактировать
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Пагинация -->
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6 rounded-lg shadow">
                {{ $animals->links() }}
            </div>
        @else
            <div class="bg-white shadow rounded-lg p-12 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V9.375c0-.621.504-1.125 1.125-1.125h.75c.621 0 1.125.504 1.125 1.125v11.25c-4.5 0-8.25-3.875-8.25-8.615 0-1.58.42-3.065 1.157-4.359" />
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Животных в приюте не найдено</h3>
                <p class="text-gray-500 mb-4">Попробуйте изменить параметры поиска или фильтры</p>
                <div class="space-x-2">
                    <a href="{{ route('admin.animal-registry.shelter') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
                        Сбросить фильтры
                    </a>
                    <a href="{{ route('admin.animals.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        Добавить животное
                    </a>
                </div>
            </div>
        @endif
    </div>
@endsection 