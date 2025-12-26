@extends('admin.layout')

@section('header')
    Животные по ОСВВ
@endsection

@section('content')
    <div class="space-y-6">
        <!-- Хлебные крошки и действия -->
        <div class="bg-white shadow rounded-lg">
            <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Животные по ОСВВ ({{ $animals->total() }})
                    </h3>
                    <div class="flex space-x-2">
                        <a href="{{ route('admin.animal-registry.index') }}" class="text-white hover:text-green-200">
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
                               placeholder="Имя, номер чипа, бирки, клетки, заявки ОСВВ..." 
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                    </div>

                    <!-- Тип -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Тип</label>
                        <select name="type" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
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
                        <select name="gender" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
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
                        <select name="stage" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
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
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Найти
                        </button>
                        <a href="{{ route('admin.animal-registry.osvv') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            Сбросить
                        </a>
                    </div>

                    <!-- Сортировка -->
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700">Сортировка:</label>
                        <select name="sort" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm">
                            <option value="arrived_at" {{ request('sort') === 'arrived_at' ? 'selected' : '' }}>По дате поступления</option>
                            <option value="osvv_request_id" {{ request('sort') === 'osvv_request_id' ? 'selected' : '' }}>По номеру ОСВВ</option>
                            <option value="cage_number" {{ request('sort') === 'cage_number' ? 'selected' : '' }}>По номеру клетки</option>
                            <option value="type" {{ request('sort') === 'type' ? 'selected' : '' }}>По типу</option>
                        </select>
                        <select name="direction" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm">
                            <option value="desc" {{ request('direction') === 'desc' ? 'selected' : '' }}>По убыванию</option>
                            <option value="asc" {{ request('direction') === 'asc' ? 'selected' : '' }}>По возрастанию</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>

        <!-- Список животных -->
        @if($animals->count() > 0)
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-green-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-green-800 uppercase tracking-wider">
                                Фото
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-green-800 uppercase tracking-wider">
                                Животное
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-green-800 uppercase tracking-wider">
                                ОСВВ заявка
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-green-800 uppercase tracking-wider">
                                Этап
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-green-800 uppercase tracking-wider">
                                В приюте
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-green-800 uppercase tracking-wider">
                                Действия
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($animals as $animal)
                            <tr class="hover:bg-green-50 transition-colors">
                                <!-- Фото -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex-shrink-0">
                                        @php
                                            $displayPhoto = $animal->photo ?? ($animal->registrationCard->photo_face ?? null);
                                        @endphp
                                        @if($displayPhoto)
                                            <img src="{{ asset('storage/' . $displayPhoto) }}" 
                                                 alt="{{ $animal->name ?? 'Животное' }}"
                                                 class="w-12 h-12 rounded-lg object-cover border border-green-300">
                                        @else
                                            <div class="w-12 h-12 rounded-lg bg-green-100 border border-green-300 flex items-center justify-center">
                                                @if($animal->type === 'dog')
                                                    <!-- Иконка собаки от Lucide -->
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-600">
                                                        <path d="M11.25 16.25h1.5L12 17z"/>
                                                        <path d="M16 14v.5"/>
                                                        <path d="M4.42 11.247A13.152 13.152 0 0 0 4 14.556C4 18.728 7.582 21 12 21s8-2.272 8-6.444a11.702 11.702 0 0 0-.493-3.309"/>
                                                        <path d="M8 14v.5"/>
                                                        <path d="M8.5 8.5c-.384 1.05-1.083 2.028-2.344 2.5-1.931.722-3.576-.297-3.656-1-.113-.994 1.177-6.53 4-7 1.923-.321 3.651.845 3.651 2.235A7.497 7.497 0 0 1 14 5.277c0-1.39 1.844-2.598 3.767-2.277 2.823.47 4.113 6.006 4 7-.08.703-1.725 1.722-3.656 1-1.261-.472-1.855-1.45-2.239-2.5"/>
                                                    </svg>
                                                @elseif($animal->type === 'cat')
                                                    <!-- Иконка кошки от Lucide -->
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-600">
                                                        <path d="M12 5c.67 0 1.35.09 2 .26 1.78-2 5.03-2.84 6.42-2.26 1.4.58-.42 7-.42 7 .57 1.07 1 2.24 1 3.44C21 17.9 16.97 21 12 21s-9-3-9-7.56c0-1.25.5-2.4 1-3.44 0 0-1.89-6.42-.5-7 1.39-.58 4.72.23 6.5 2.23A9.04 9.04 0 0 1 12 5Z"/>
                                                        <path d="M8 14v.5"/>
                                                        <path d="M16 14v.5"/>
                                                        <path d="M11.25 16.25h1.5L12 17z"/>
                                                    </svg>
                                                @else
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-600">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.375a2.25 2.25 0 01-2.25-2.25V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184" />
                                                    </svg>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                <!-- Животное -->
                                <td class="px-6 py-4">
                                    <div>
                                        <div class="font-medium text-gray-900">
                                            @if($animal->cage_number)
                                                Вольер №{{ $animal->cage_number }}
                                            @else
                                                {{ $animal->name ?? 'Без имени' }}
                                            @endif
                                        </div>
                                        <div class="flex items-center space-x-2 mt-1">
                                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded">
                                                {{ $types[$animal->type] ?? $animal->type }}
                                            </span>
                                            @if($animal->breed)
                                                <span class="text-sm text-gray-600">{{ $animal->breed }}</span>
                                            @endif
                                        </div>
                                        @if($animal->age_months)
                                            <div class="text-sm text-gray-500 mt-1">
                                                Возраст: {{ floor($animal->age_months / 12) }} лет {{ $animal->age_months % 12 }} мес
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                <!-- ОСВВ заявка -->
                                <td class="px-6 py-4">
                                    @if($animal->osvvRequest)
                                        <div class="text-sm">
                                            <div class="flex items-center font-medium text-green-800">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                ОСВВ #{{ $animal->osvv_request_id }}
                                            </div>
                                            @if($animal->osvvRequest->applicant_name)
                                                <div class="text-gray-600 mt-1">{{ $animal->osvvRequest->applicant_name }}</div>
                                            @endif
                                            @if($animal->osvvRequest->location_address)
                                                <div class="text-gray-500 text-xs mt-1">{{ Str::limit($animal->osvvRequest->location_address, 40) }}</div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-sm">Нет заявки</span>
                                    @endif
                                </td>

                                <!-- Этап -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($animal->currentStage)
                                        <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded">
                                            {{ $animal->currentStage->name }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-sm">Без этапа</span>
                                    @endif
                                </td>

                                <!-- В приюте -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @php
                                        $daysInShelter = abs(round(now()->diffInDays($animal->arrived_at)));
                                    @endphp
                                    <div class="font-medium">{{ $daysInShelter }}</div>
                                    <div class="text-xs text-gray-500">
                                        @if($daysInShelter == 1)
                                            день
                                        @elseif($daysInShelter >= 2 && $daysInShelter <= 4)
                                            дня
                                        @else
                                            дней
                                        @endif
                                    </div>
                                </td>

                                <!-- Действия -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('admin.animals.show', $animal) }}" 
                                           class="inline-flex items-center px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700">
                                            Подробнее
                                        </a>
                                        @if($animal->osvvRequest)
                                            <a href="{{ route('admin.osvv.show', $animal->osvvRequest) }}" 
                                               class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">
                                                ОСВВ
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Пагинация -->
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6 rounded-lg shadow">
                {{ $animals->links() }}
            </div>
        @else
            <div class="bg-white shadow rounded-lg p-12 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Животных по ОСВВ не найдено</h3>
                <p class="text-gray-500 mb-4">Попробуйте изменить параметры поиска или фильтры</p>
                <a href="{{ route('admin.animal-registry.osvv') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    Сбросить фильтры
                </a>
            </div>
        @endif
    </div>
@endsection 