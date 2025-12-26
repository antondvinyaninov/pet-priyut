@extends('admin.layout')

@section('header')
    Учет животных — Карточки
@endsection

@section('content')
    <div class="space-y-6">
        

        <!-- Фильтры -->
        <div class="bg-white shadow rounded-lg p-6">
            <form method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Поиск</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Имя, чип, бирка, клетка, заявка..." class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Источник</label>
                        <select name="source" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Все</option>
                            <option value="osvv" {{ request('source') === 'osvv' ? 'selected' : '' }}>ОСВВ</option>
                            <option value="shelter" {{ request('source') === 'shelter' ? 'selected' : '' }}>Приют</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Тип</label>
                        <select name="type" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Все типы</option>
                            @foreach($types as $key => $label)
                                <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Пол</label>
                        <select name="gender" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Все</option>
                            @foreach($genders as $key => $label)
                                <option value="{{ $key }}" {{ request('gender') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Этап</label>
                        <select name="stage" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Все этапы</option>
                            @foreach($stages as $stage)
                                <option value="{{ $stage->id }}" {{ request('stage') == $stage->id ? 'selected' : '' }}>{{ $stage->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <button class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Найти</button>
                        <a href="{{ route('admin.animal-registry.cards') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Сбросить</a>
                    </div>
                    <div class="flex items-center space-x-2">
                        <label class="text-sm text-gray-700">Сортировка:</label>
                        <select name="sort" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm">
                            <option value="arrived_at" {{ request('sort') === 'arrived_at' ? 'selected' : '' }}>По дате поступления</option>
                            <option value="cage_number" {{ request('sort') === 'cage_number' ? 'selected' : '' }}>По вольеру</option>
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

        <!-- Таблица -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 table-fixed">
                <thead class="bg-indigo-50">
                    <tr>
                        <!-- № карточки (сортируемый) -->
                        <th class="px-3 py-3 text-left text-xs font-medium text-indigo-800 uppercase tracking-wider w-32">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'registration_number', 'direction' => request('sort') === 'registration_number' && request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                               class="flex items-center gap-1 hover:text-indigo-600 transition">
                                <span>№ карточки</span>
                                @if(request('sort') === 'registration_number' || !request('sort'))
                                    @if(request('direction') === 'desc')
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                                    @else
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z"/></svg>
                                    @endif
                                @else
                                    <svg class="w-4 h-4 opacity-30" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                                @endif
                            </a>
                        </th>
                        
                        <th class="px-3 py-3 text-left text-xs font-medium text-indigo-800 uppercase tracking-wider w-20">Фото</th>
                        
                        <!-- Бирка/Чип -->
                        <th class="px-3 py-3 text-left text-xs font-medium text-indigo-800 uppercase tracking-wider w-40">
                            <span>Бирка / Чип</span>
                        </th>
                        
                        <!-- Вольер (сортируемый) -->
                        <th class="px-3 py-3 text-left text-xs font-medium text-indigo-800 uppercase tracking-wider w-24">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'cage_number', 'direction' => request('sort') === 'cage_number' && request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                               class="flex items-center gap-1 hover:text-indigo-600 transition">
                                <span>Вольер</span>
                                @if(request('sort') === 'cage_number')
                                    @if(request('direction') === 'desc')
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                                    @else
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z"/></svg>
                                    @endif
                                @else
                                    <svg class="w-4 h-4 opacity-30" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                                @endif
                            </a>
                        </th>
                        
                        <!-- Кличка (сортируемая) -->
                        <th class="px-6 py-3 text-left text-xs font-medium text-indigo-800 uppercase tracking-wider">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => request('sort') === 'name' && request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                               class="flex items-center gap-1 hover:text-indigo-600 transition">
                                <span>Кличка</span>
                                @if(request('sort') === 'name')
                                    @if(request('direction') === 'desc')
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                                    @else
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z"/></svg>
                                    @endif
                                @else
                                    <svg class="w-4 h-4 opacity-30" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                                @endif
                            </a>
                        </th>
                        
                        <th class="px-6 py-3 text-left text-xs font-medium text-indigo-800 uppercase tracking-wider">Источник</th>
                        
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($animals as $animal)
                        <tr class="hover:bg-indigo-50">
                            <!-- № карточки -->
                            <td class="px-3 py-3 text-sm whitespace-nowrap font-mono">
                                @if($animal->registrationCard)
                                    <a href="{{ route('admin.animals.show', $animal) }}" class="px-2 py-1 rounded bg-blue-100 text-blue-800 hover:underline">
                                        {{ $animal->registrationCard->registration_number }}
                                    </a>
                                @else
                                    <a href="{{ route('admin.animals.show', $animal) }}" class="text-gray-400 hover:underline">—</a>
                                @endif
                            </td>
                            <!-- Фото -->
                            <td class="px-3 py-3">
                                @php
                                    // Используем основное фото, если его нет - фото морды
                                    $displayPhoto = $animal->photo ?? ($animal->registrationCard?->photo_face ?? null);
                                @endphp
                                @if($displayPhoto)
                                    <img src="{{ asset('storage/' . $displayPhoto) }}" 
                                         alt="{{ $animal->name ?? 'Животное' }}" 
                                         class="w-12 h-12 rounded object-cover border">
                                @else
                                    <div class="w-12 h-12 rounded bg-indigo-100 border flex items-center justify-center">🐾</div>
                                @endif
                            </td>
                            <!-- Бирка/Чип -->
                            <td class="px-3 py-3 text-sm">
                                <div class="space-y-1.5">
                                    <div class="flex items-center gap-1.5 text-gray-700">
                                        <span class="text-base">🏷️</span>
                                        <span>{{ $animal->tag_number ?? '—' }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-gray-600">
                                        <span class="text-base">💾</span>
                                        <span>{{ $animal->chip_number ?? '—' }}</span>
                                    </div>
                                </div>
                            </td>
                            <!-- Вольер -->
                            <td class="px-3 py-3 text-sm whitespace-nowrap">
                                @if($animal->cage_number)
                                    №{{ $animal->cage_number }}
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <!-- Кличка -->
                            <td class="px-6 py-3">
                                <div class="font-medium">
                                    <a href="{{ route('admin.animals.show', $animal) }}" class="text-indigo-700 hover:underline">
                                        {{ $animal->name ?? 'Без имени' }}
                                    </a>
                                </div>
                                <div class="text-xs text-gray-500">{{ $types[$animal->type] ?? $animal->type }}@if($animal->breed) • {{ $animal->breed }}@endif</div>
                            </td>
                            <!-- Источник -->
                            <td class="px-6 py-3 text-sm">
                                @if($animal->registrationCard && $animal->registrationCard->capture_act_number)
                                    <div class="text-gray-900">
                                        @php
                                            // Определяем тип акта по наличию ветеринара или по тексту
                                            $isTransferAct = $animal->registrationCard->sterilization_vet || 
                                                           str_contains(strtolower($animal->registrationCard->intake_source ?? ''), 'передач');
                                            $actType = $isTransferAct ? 'Акт приема-передачи' : 'Акт отлова';
                                        @endphp
                                        <span class="font-medium">{{ $actType }} №{{ $animal->registrationCard->capture_act_number }}</span>
                                        @if($animal->registrationCard->capture_act_date)
                                            <div class="text-xs text-gray-500">от {{ $animal->registrationCard->capture_act_date->format('d.m.Y') }}</div>
                                        @endif
                                        @if($isTransferAct && $animal->registrationCard->sterilization_vet)
                                            <div class="text-xs text-indigo-600 mt-1">
                                                Ветеринар: {{ $animal->registrationCard->sterilization_vet }}
                                            </div>
                                        @endif
                                    </div>
                                @elseif($animal->osvv_request_id)
                                    <span class="px-2 py-1 rounded bg-green-100 text-green-800">ОСВВ #{{ $animal->osvv_request_id }}</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                Нет записей
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="bg-white px-4 py-3 border-t border-gray-200 rounded-lg shadow">
            {{ $animals->links() }}
        </div>
    </div>
@endsection
