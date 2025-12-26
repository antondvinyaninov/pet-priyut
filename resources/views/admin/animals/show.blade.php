@extends('admin.layout')

@section('header')
    Животное: {{ $animal->cage_number ? 'Вольер №' . $animal->cage_number : ($animal->name ?? 'Без имени') }}
@endsection

@section('content')
    <div class="space-y-6">
        <!-- Хлебные крошки -->
        <div class="bg-white shadow rounded-lg">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-2 text-white">
                        <a href="{{ route('admin.animals.index') }}" class="hover:text-blue-200">Управление животными</a>
                        <span>/</span>
                        <span class="font-semibold">
                            {{ $animal->cage_number ? 'Вольер №' . $animal->cage_number : ($animal->name ?? 'Животное #' . $animal->id) }}
                        </span>
                    </div>
                    <div class="flex space-x-2">
                        <button type="submit" form="animal-card-form" 
                                class="bg-yellow-400 text-gray-900 px-4 py-2 rounded-md hover:bg-yellow-500 transition-colors">
                            💾 Сохранить
                        </button>
                        @if($animal->osvvRequest)
                            <a href="{{ route('admin.osvv.show', $animal->osvvRequest) }}" 
                               class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors">
                                📋 Заявка ОСВВ
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Левая колонка: Основная информация -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Карточка животного -->
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-start space-x-6">
                            <!-- Фото -->
                            <div class="flex-shrink-0">
                                @php
                                    $displayPhoto = $animal->photo ?? ($animal->registrationCard->photo_face ?? null);
                                @endphp
                                @if($displayPhoto)
                                    <img src="{{ asset('storage/' . $displayPhoto) }}" 
                                         alt="{{ $animal->name ?? 'Животное' }}"
                                         class="w-32 h-32 rounded-lg object-cover border-2 border-gray-300 shadow-md cursor-pointer hover:opacity-75 transition"
                                         onclick="openPhotoModal('{{ asset('storage/' . $displayPhoto) }}', '')">
                                @else
                                    <div class="w-32 h-32 rounded-lg bg-gray-100 border-2 border-gray-300 flex items-center justify-center shadow-md">
                                        @if($animal->type === 'dog')
                                            <!-- Иконка собаки от Lucide -->
                                            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                                                <path d="M11.25 16.25h1.5L12 17z"/>
                                                <path d="M16 14v.5"/>
                                                <path d="M4.42 11.247A13.152 13.152 0 0 0 4 14.556C4 18.728 7.582 21 12 21s8-2.272 8-6.444a11.702 11.702 0 0 0-.493-3.309"/>
                                                <path d="M8 14v.5"/>
                                                <path d="M8.5 8.5c-.384 1.05-1.083 2.028-2.344 2.5-1.931.722-3.576-.297-3.656-1-.113-.994 1.177-6.53 4-7 1.923-.321 3.651.845 3.651 2.235A7.497 7.497 0 0 1 14 5.277c0-1.39 1.844-2.598 3.767-2.277 2.823.47 4.113 6.006 4 7-.08.703-1.725 1.722-3.656 1-1.261-.472-1.855-1.45-2.239-2.5"/>
                                            </svg>
                                        @elseif($animal->type === 'cat')
                                            <!-- Иконка кошки от Lucide -->
                                            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                                                <path d="M12 5c.67 0 1.35.09 2 .26 1.78-2 5.03-2.84 6.42-2.26 1.4.58-.42 7-.42 7 .57 1.07 1 2.24 1 3.44C21 17.9 16.97 21 12 21s-9-3-9-7.56c0-1.25.5-2.4 1-3.44 0 0-1.89-6.42-.5-7 1.39-.58 4.72.23 6.5 2.23A9.04 9.04 0 0 1 12 5Z"/>
                                                <path d="M8 14v.5"/>
                                                <path d="M16 14v.5"/>
                                                <path d="M11.25 16.25h1.5L12 17z"/>
                                            </svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.375a2.25 2.25 0 01-2.25-2.25V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184" />
                                            </svg>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <!-- Основная информация -->
                            <div class="flex-1">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-3">
                                    <div>
                                        <a href="{{ route('admin.animals.edit', $animal) }}" class="text-3xl font-bold text-gray-900 hover:text-blue-600">
                                            {{ $animal->name ?? 'Без имени' }}
                                        </a>
                                        <div class="mt-2 text-sm text-gray-600">№ карточки: {{ $animal->registrationCard->registration_number ?? $animal->id }}</div>
                                        <div class="mt-1 text-sm text-gray-600">Вольер: {{ $animal->cage_number ?? '—' }}</div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <div class="text-xs text-gray-500">№ бирки</div>
                                            <div class="text-sm font-medium text-gray-900">{{ $animal->tag_number ?? '—' }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-500">№ чипа</div>
                                            <div class="text-sm font-medium text-gray-900">{{ $animal->chip_number ?? '—' }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">
                                        @switch($animal->type)
                                            @case('dog') 🐕 Собака @break
                                            @case('cat') 🐱 Кошка @break
                                            @default 🐾 Другое @break
                                        @endswitch
                                    </span>
                                    <span class="px-3 py-1 text-sm font-medium rounded-full
                                        @switch($animal->status)
                                            @case('active') bg-blue-100 text-blue-800 @break
                                            @case('released') bg-green-100 text-green-800 @break
                                            @case('adopted') bg-purple-100 text-purple-800 @break
                                            @case('deceased') bg-red-100 text-red-800 @break
                                            @default bg-gray-100 text-gray-800 @break
                                        @endswitch
                                    ">
                                        @switch($animal->status)
                                            @case('active') 🔵 Активный @break
                                            @case('released') 🚗 Готов к выезду @break
                                            @case('adopted') 🏠 В приюте @break
                                            @case('deceased') 💔 Умер @break
                                            @default {{ $animal->status }} @break
                                        @endswitch
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                

                <!-- Единая карточка с редактированием -->
                    <div class="bg-white shadow rounded-lg">
                    <div class="bg-blue-600 px-6 py-3">
                            <h3 class="text-lg font-semibold text-white flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            Карточка животного
                            </h3>
                        </div>
                        <div class="p-6">
                        <form id="animal-card-form" method="POST" enctype="multipart/form-data" action="{{ route('admin.animals.update-full-card', $animal) }}">
                            @csrf
                            @method('PATCH')
                            
                            <!-- Скрытые поля для обязательных данных -->
                            <input type="hidden" name="type" value="{{ $animal->type }}">
                            <input type="hidden" name="gender" value="{{ $animal->gender }}">
                            
                            <div class="space-y-8 text-sm">
                                <!-- Идентификация -->
                                <div>
                                    <h4 class="text-base font-semibold text-gray-900 mb-3">Идентификация</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div x-data="{edit:false}">
                                            <label class="block text-xs text-gray-600">Кличка</label>
                                            <div x-show="!edit" class="flex items-center justify-between mt-1 rounded border border-transparent bg-gray-50 px-3 py-2">
                                                <span class="text-gray-900">{{ $animal->name ?? '—' }}</span>
                                                <button type="button" @click="edit=true" class="text-blue-600 text-xs">✏️</button>
                                            </div>
                                            <div x-show="edit" class="mt-1">
                                                <div class="flex gap-2">
                                                    <input id="animal-name-input" name="name" value="{{ old('name', $animal->name) }}" class="flex-1 rounded border-gray-300">
                                                    <button type="button" 
                                                            onclick="generateAnimalName('{{ $animal->type }}', '{{ $animal->gender }}', '{{ $animal->color ?? '' }}')" 
                                                            class="px-3 py-2 bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200 transition whitespace-nowrap text-sm"
                                                            title="Придумать кличку">
                                                        🎲 Придумать
                                                    </button>
                                                </div>
                                                <div class="mt-2">
                                                    <button type="button" @click="edit=false" class="text-xs text-gray-700 px-2 py-1 border rounded">Готово</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div x-data="{edit:false}">
                                            <label class="block text-xs text-gray-600">Вольер</label>
                                            <div x-show="!edit" class="flex items-center justify-between mt-1 rounded border border-transparent bg-gray-50 px-3 py-2">
                                                <span class="text-gray-900">{{ $animal->cage_number ?? '—' }}</span>
                                                <button type="button" @click="edit=true" class="text-blue-600 text-xs">✏️</button>
                                            </div>
                                            <div x-show="edit" class="mt-1">
                                                <input name="cage_number" value="{{ old('cage_number', $animal->cage_number) }}" class="w-full rounded border-gray-300">
                                                <div class="mt-2">
                                                    <button type="button" @click="edit=false" class="text-xs text-gray-700 px-2 py-1 border rounded">Готово</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div x-data="{edit:false}">
                                            <label class="block text-xs text-gray-600">№ карточки (ID)</label>
                                            <div x-show="!edit" class="flex items-center justify-between mt-1 rounded border border-transparent bg-gray-50 px-3 py-2">
                                                <span class="text-gray-900">{{ $animal->registrationCard->registration_number ?? $animal->id }}</span>
                                                <button type="button" @click="edit=true" class="text-blue-600 text-xs">✏️</button>
                                            </div>
                                            <div x-show="edit" class="mt-1">
                                                <input name="registration_number" value="{{ old('registration_number', $animal->registrationCard->registration_number ?? $animal->id) }}" class="w-full rounded border-gray-300">
                                                <div class="mt-2">
                                                    <button type="button" @click="edit=false" class="text-xs text-gray-700 px-2 py-1 border rounded">Готово</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div x-data="{edit:false}">
                                            <label class="block text-xs text-gray-600">Дата регистрации</label>
                                            <div x-show="!edit" class="flex items-center justify-between mt-1 rounded border border-transparent bg-gray-50 px-3 py-2">
                                                <span class="text-gray-900">{{ optional($animal->registrationCard->registration_date ?? $animal->created_at)->format('d.m.Y') }}</span>
                                                <button type="button" @click="edit=true" class="text-blue-600 text-xs">✏️</button>
                                            </div>
                                            <div x-show="edit" class="mt-1">
                                                <input type="date" name="registration_date" value="{{ old('registration_date', optional($animal->registrationCard->registration_date ?? $animal->created_at)->format('Y-m-d')) }}" class="w-full rounded border-gray-300">
                                                <div class="mt-2">
                                                    <button type="button" @click="edit=false" class="text-xs text-gray-700 px-2 py-1 border rounded">Готово</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div x-data="{
                                            edit: false, 
                                            value: '{{ $animal->chip_number }}', 
                                            displayValue: '{{ $animal->chip_number ?? '—' }}',
                                            async save() {
                                                try {
                                                    const response = await fetch('{{ route('admin.animals.quick-update', $animal) }}', {
                                                        method: 'PATCH',
                                                        headers: {
                                                            'Content-Type': 'application/json',
                                                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                                        },
                                                        body: JSON.stringify({ field: 'chip_number', value: this.value })
                                                    });
                                                    const data = await response.json();
                                                    if (data.success) {
                                                        this.displayValue = data.value || this.value || '—';
                                                        this.edit = false;
                                                        alert('✓ Сохранено');
                                                    } else {
                                                        alert('Ошибка: ' + data.message);
                                                    }
                                                } catch (error) {
                                                    console.error('Error:', error);
                                                    alert('Ошибка при сохранении');
                                                }
                                            }
                                        }">
                                            <label class="block text-xs text-gray-600">№ чипа</label>
                                            <div x-show="!edit" class="flex items-center justify-between mt-1 rounded border border-transparent bg-gray-50 px-3 py-2">
                                                <span class="text-gray-900" x-text="displayValue"></span>
                                                <button type="button" @click="edit=true" class="text-blue-600 text-xs">✏️</button>
                                            </div>
                                            <div x-show="edit" class="mt-1">
                                                <input x-model="value" class="w-full rounded border-gray-300">
                                                <div class="mt-2">
                                                    <button type="button" @click="save()" class="text-xs text-white bg-blue-600 px-2 py-1 rounded hover:bg-blue-700">Готово</button>
                                                    <button type="button" @click="edit=false" class="text-xs text-gray-700 px-2 py-1 border rounded ml-1">Отмена</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div x-data="{
                                            edit: false, 
                                            value: '{{ $animal->tag_number }}', 
                                            displayValue: '{{ $animal->tag_number ?? '—' }}',
                                            async save() {
                                                try {
                                                    const response = await fetch('{{ route('admin.animals.quick-update', $animal) }}', {
                                                        method: 'PATCH',
                                                        headers: {
                                                            'Content-Type': 'application/json',
                                                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                                        },
                                                        body: JSON.stringify({ field: 'tag_number', value: this.value })
                                                    });
                                                    const data = await response.json();
                                                    if (data.success) {
                                                        this.displayValue = data.value || this.value || '—';
                                                        this.edit = false;
                                                        alert('✓ Сохранено');
                                                    } else {
                                                        alert('Ошибка: ' + data.message);
                                                    }
                                                } catch (error) {
                                                    console.error('Error:', error);
                                                    alert('Ошибка при сохранении');
                                                }
                                            }
                                        }">
                                            <label class="block text-xs text-gray-600">№ бирки</label>
                                            <div x-show="!edit" class="flex items-center justify-between mt-1 rounded border border-transparent bg-gray-50 px-3 py-2">
                                                <span class="text-gray-900" x-text="displayValue"></span>
                                                <button type="button" @click="edit=true" class="text-blue-600 text-xs">✏️</button>
                                            </div>
                                            <div x-show="edit" class="mt-1">
                                                <input x-model="value" class="w-full rounded border-gray-300">
                                                <div class="mt-2">
                                                    <button type="button" @click="save()" class="text-xs text-white bg-blue-600 px-2 py-1 rounded hover:bg-blue-700">Готово</button>
                                                    <button type="button" @click="edit=false" class="text-xs text-gray-700 px-2 py-1 border rounded ml-1">Отмена</button>
                                                </div>
                                            </div>
                            </div>
                        </div>
                    </div>

                                <!-- Основные данные -->
                                <div class="pt-6 border-t border-gray-200">
                                    <h4 class="text-base font-semibold text-gray-900 mb-3">Основные данные</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div x-data="{edit:false}">
                                            <label class="block text-xs text-gray-600">Вид</label>
                                            <div x-show="!edit" class="flex items-center justify-between mt-1 rounded border border-transparent bg-gray-50 px-3 py-2">
                                                <span class="text-gray-900">
                                                    @switch($animal->type)
                                                        @case('dog') Собака @break
                                                        @case('cat') Кошка @break
                                                        @default Другое @break
                                                    @endswitch
                                                </span>
                                                <button type="button" @click="edit=true" class="text-blue-600 text-xs">✏️</button>
                                            </div>
                                            <div x-show="edit" class="mt-1">
                                                <select name="type" class="w-full rounded border-gray-300">
                                                    <option value="dog" {{ old('type', $animal->type)==='dog'?'selected':'' }}>Собака</option>
                                                    <option value="cat" {{ old('type', $animal->type)==='cat'?'selected':'' }}>Кошка</option>
                                                    <option value="other" {{ old('type', $animal->type)==='other'?'selected':'' }}>Другое</option>
                                                </select>
                                                <div class="mt-2">
                                                    <button type="button" @click="edit=false" class="text-xs text-gray-700 px-2 py-1 border rounded">Готово</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div x-data="{edit:false}">
                                            <label class="block text-xs text-gray-600">Пол</label>
                                            <div x-show="!edit" class="flex items-center justify-between mt-1 rounded border border-transparent bg-gray-50 px-3 py-2">
                                                <span class="text-gray-900">
                                                    @switch($animal->gender)
                                                        @case('male') Самец @break
                                                        @case('female') Самка @break
                                                        @default Неизвестно @break
                                                    @endswitch
                                                </span>
                                                <button type="button" @click="edit=true" class="text-blue-600 text-xs">✏️</button>
                                            </div>
                                            <div x-show="edit" class="mt-1">
                                                <select name="gender" class="w-full rounded border-gray-300">
                                                    <option value="male" {{ old('gender', $animal->gender)==='male'?'selected':'' }}>Самец</option>
                                                    <option value="female" {{ old('gender', $animal->gender)==='female'?'selected':'' }}>Самка</option>
                                                    <option value="unknown" {{ old('gender', $animal->gender)==='unknown'?'selected':'' }}>Неизвестно</option>
                                                </select>
                                                <div class="mt-2">
                                                    <button type="button" @click="edit=false" class="text-xs text-gray-700 px-2 py-1 border rounded">Готово</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div x-data="{edit:false}">
                                            <label class="block text-xs text-gray-600">Источник поступления</label>
                                            <div x-show="!edit" class="flex items-center justify-between mt-1 rounded border border-transparent bg-gray-50 px-3 py-2">
                                                <span class="text-gray-900">{{ $animal->registrationCard->intake_source ?? '—' }}</span>
                                                <button type="button" @click="edit=true" class="text-blue-600 text-xs">✏️</button>
                                            </div>
                                            <div x-show="edit" class="mt-1">
                                                <input name="intake_source" value="{{ old('intake_source', $animal->registrationCard->intake_source) }}" class="w-full rounded border-gray-300">
                                                <div class="mt-2">
                                                    <button type="button" @click="edit=false" class="text-xs text-gray-700 px-2 py-1 border rounded">Готово</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div x-data="{edit:false}">
                                            <label class="block text-xs text-gray-600">Порода</label>
                                            <div x-show="!edit" class="flex items-center justify-between mt-1 rounded border border-transparent bg-gray-50 px-3 py-2">
                                                <span class="text-gray-900">{{ $animal->breed ?? '—' }}</span>
                                                <button type="button" @click="edit=true" class="text-blue-600 text-xs">✏️</button>
                                            </div>
                                            <div x-show="edit" class="mt-1">
                                                <input name="breed" value="{{ old('breed', $animal->breed) }}" class="w-full rounded border-gray-300">
                                                <div class="mt-2">
                                                    <button type="button" @click="edit=false" class="text-xs text-gray-700 px-2 py-1 border rounded">Готово</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div x-data="{edit:false}">
                                            <label class="block text-xs text-gray-600">Окрас</label>
                                            <div x-show="!edit" class="flex items-center justify-between mt-1 rounded border border-transparent bg-gray-50 px-3 py-2">
                                                <span class="text-gray-900">{{ $animal->color ?? '—' }}</span>
                                                <button type="button" @click="edit=true" class="text-blue-600 text-xs">✏️</button>
                                            </div>
                                            <div x-show="edit" class="mt-1">
                                                <input name="color" value="{{ old('color', $animal->color) }}" class="w-full rounded border-gray-300">
                                                <div class="mt-2">
                                                    <button type="button" @click="edit=false" class="text-xs text-gray-700 px-2 py-1 border rounded">Готово</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div x-data="{edit:false}">
                                            <label class="block text-xs text-gray-600">Шерсть</label>
                                            <div x-show="!edit" class="flex items-center justify-between mt-1 rounded border border-transparent bg-gray-50 px-3 py-2">
                                                <span class="text-gray-900">{{ $animal->registrationCard->coat ?? '—' }}</span>
                                                <button type="button" @click="edit=true" class="text-blue-600 text-xs">✏️</button>
                                            </div>
                                            <div x-show="edit" class="mt-1">
                                                <input name="coat" value="{{ old('coat', $animal->registrationCard->coat) }}" class="w-full rounded border-gray-300">
                                                <div class="mt-2">
                                                    <button type="button" @click="edit=false" class="text-xs text-gray-700 px-2 py-1 border rounded">Готово</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div x-data="{edit:false}">
                                            <label class="block text-xs text-gray-600">Уши</label>
                                            <div x-show="!edit" class="flex items-center justify-between mt-1 rounded border border-transparent bg-gray-50 px-3 py-2">
                                                <span class="text-gray-900">{{ $animal->registrationCard->ears ?? '—' }}</span>
                                                <button type="button" @click="edit=true" class="text-blue-600 text-xs">✏️</button>
                                            </div>
                                            <div x-show="edit" class="mt-1">
                                                <input name="ears" value="{{ old('ears', $animal->registrationCard->ears) }}" class="w-full rounded border-gray-300">
                                                <div class="mt-2">
                                                    <button type="button" @click="edit=false" class="text-xs text-gray-700 px-2 py-1 border rounded">Готово</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div x-data="{edit:false}">
                                            <label class="block text-xs text-gray-600">Хвост</label>
                                            <div x-show="!edit" class="flex items-center justify-between mt-1 rounded border border-transparent bg-gray-50 px-3 py-2">
                                                <span class="text-gray-900">{{ $animal->registrationCard->tail ?? '—' }}</span>
                                                <button type="button" @click="edit=true" class="text-blue-600 text-xs">✏️</button>
                                            </div>
                                            <div x-show="edit" class="mt-1">
                                                <input name="tail" value="{{ old('tail', $animal->registrationCard->tail) }}" class="w-full rounded border-gray-300">
                                                <div class="mt-2">
                                                    <button type="button" @click="edit=false" class="text-xs text-gray-700 px-2 py-1 border rounded">Готово</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div x-data="{edit:false}">
                                            <label class="block text-xs text-gray-600">Размер</label>
                                            <div x-show="!edit" class="flex items-center justify-between mt-1 rounded border border-transparent bg-gray-50 px-3 py-2">
                                                <span class="text-gray-900">{{ $animal->registrationCard->size ?? '—' }}</span>
                                                <button type="button" @click="edit=true" class="text-blue-600 text-xs">✏️</button>
                                            </div>
                                            <div x-show="edit" class="mt-1">
                                                <input name="size" value="{{ old('size', $animal->registrationCard->size) }}" class="w-full rounded border-gray-300">
                                                <div class="mt-2">
                                                    <button type="button" @click="edit=false" class="text-xs text-gray-700 px-2 py-1 border rounded">Готово</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div x-data="{edit:false}">
                                            <label class="block text-xs text-gray-600">Возраст при поступлении</label>
                                            <div x-show="!edit" class="flex items-center justify-between mt-1 rounded border border-transparent bg-gray-50 px-3 py-2">
                                                <span class="text-gray-900">
                                                    @if($animal->age_months)
                                                        @if($animal->age_months >= 12)
                                                            {{ floor($animal->age_months / 12) }} {{ floor($animal->age_months / 12) == 1 ? 'год' : (floor($animal->age_months / 12) < 5 ? 'года' : 'лет') }}
                                                            @if($animal->age_months % 12 > 0)
                                                                {{ $animal->age_months % 12 }} мес
                                                            @endif
                                                        @else
                                                            {{ $animal->age_months }} мес
                                                        @endif
                                                    @else
                                                        —
                                                    @endif
                                                </span>
                                                <button type="button" @click="edit=true" class="text-blue-600 text-xs">✏️</button>
                                            </div>
                                            <div x-show="edit" class="mt-1">
                                                <input type="number" name="age_months" value="{{ old('age_months', $animal->age_months) }}" class="w-full rounded border-gray-300" placeholder="Возраст в месяцах">
                                                <div class="mt-2">
                                                    <button type="button" @click="edit=false" class="text-xs text-gray-700 px-2 py-1 border rounded">Готово</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-600">Текущий возраст</label>
                                            <div class="mt-1 rounded border border-transparent bg-indigo-50 px-3 py-2">
                                                <span class="text-gray-900 font-medium">
                                                    @php
                                                        if ($animal->age_months && $animal->arrived_at) {
                                                            // Правильный расчет: от даты поступления до сейчас
                                                            $monthsSinceArrival = $animal->arrived_at->diffInMonths(now());
                                                            $currentAgeMonths = $animal->age_months + $monthsSinceArrival;
                                                            
                                                            if ($currentAgeMonths >= 12) {
                                                                $years = floor($currentAgeMonths / 12);
                                                                $months = $currentAgeMonths % 12;
                                                                echo $years . ' ' . ($years == 1 ? 'год' : ($years < 5 ? 'года' : 'лет'));
                                                                if ($months > 0) {
                                                                    echo ' ' . $months . ' мес';
                                                                }
                                                            } else {
                                                                echo $currentAgeMonths . ' мес';
                                                            }
                                                        } else {
                                                            echo '—';
                                                        }
                                                    @endphp
                                                </span>
                                            </div>
                                        </div>
                                        <div x-data="{edit:false}">
                                            <label class="block text-xs text-gray-600">Вес (кг)</label>
                                            <div x-show="!edit" class="flex items-center justify-between mt-1 rounded border border-transparent bg-gray-50 px-3 py-2">
                                                <span class="text-gray-900">{{ $animal->weight ?? '—' }}</span>
                                                <button type="button" @click="edit=true" class="text-blue-600 text-xs">✏️</button>
                                                        </div>
                                            <div x-show="edit" class="mt-1">
                                                <input type="number" step="0.01" name="weight" value="{{ old('weight', $animal->weight) }}" class="w-full rounded border-gray-300">
                                                <div class="mt-2">
                                                    <button type="button" @click="edit=false" class="text-xs text-gray-700 px-2 py-1 border rounded">Готово</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Поступление и отлов -->
                                <div class="pt-6 border-t border-gray-200">
                                    <h4 class="text-base font-semibold text-gray-900 mb-3">Поступление и отлов</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @if($animal->osvvRequest)
                                            <div class="md:col-span-2 text-gray-700">
                                                <span class="text-xs uppercase text-gray-500">Заявка ОСВВ</span>
                                                <div>
                                                    <a href="{{ route('admin.osvv.show', $animal->osvvRequest) }}" class="text-blue-600 hover:underline">#{{ $animal->osvv_request_id }}</a>
                                                    @if($animal->osvvRequest->location_address)
                                                        — {{ $animal->osvvRequest->location_address }}
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                        <div class="md:col-span-2">
                                            <label class="block text-xs text-gray-600">Адрес и описание места отлова</label>
                                            <textarea name="capture_location_address" class="mt-1 w-full rounded border-gray-300">{{ old('capture_location_address', $animal->registrationCard->capture_location_address) }}</textarea>
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-600">Акт отлова — №</label>
                                            <input name="capture_act_number" value="{{ old('capture_act_number', $animal->registrationCard->capture_act_number) }}" class="mt-1 w-full rounded border-gray-300">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-600">Акт отлова — дата</label>
                                            <input type="date" name="capture_act_date" value="{{ old('capture_act_date', optional($animal->registrationCard->capture_act_date)->format('Y-m-d')) }}" class="mt-1 w-full rounded border-gray-300">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-600">ВСД — №</label>
                                            <input name="vet_doc_number" value="{{ old('vet_doc_number', $animal->registrationCard->vet_doc_number) }}" class="mt-1 w-full rounded border-gray-300">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-600">ВСД — дата</label>
                                            <input type="date" name="vet_doc_date" value="{{ old('vet_doc_date', optional($animal->registrationCard->vet_doc_date)->format('Y-m-d')) }}" class="mt-1 w-full rounded border-gray-300">
                                        </div>
                                    </div>
                                </div>

                                <!-- Медицинские данные -->
                                <div class="pt-6 border-t border-gray-200">
                                    <h4 class="text-base font-semibold text-gray-900 mb-3">Медицинские данные</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs text-gray-600">Дата клинического осмотра</label>
                                            <input type="date" name="clinical_exam_date" value="{{ old('clinical_exam_date', optional($animal->registrationCard->clinical_exam_date)->format('Y-m-d')) }}" class="mt-1 w-full rounded border-gray-300">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-600">Заключение клинического осмотра</label>
                                            <input name="clinical_exam_conclusion" value="{{ old('clinical_exam_conclusion', $animal->registrationCard->clinical_exam_conclusion) }}" class="mt-1 w-full rounded border-gray-300">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-xs text-gray-600">Агрессивность (наличие/отсутствие)</label>
                                            <textarea name="aggression_notes" class="mt-1 w-full rounded border-gray-300">{{ old('aggression_notes', $animal->registrationCard->aggression_notes) }}</textarea>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-xs text-gray-600">Корректировка поведения</label>
                                            <textarea name="behavior_correction_notes" class="mt-1 w-full rounded border-gray-300">{{ old('behavior_correction_notes', $animal->registrationCard->behavior_correction_notes) }}</textarea>
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-600">Дата дегельминтизации</label>
                                            <input type="date" name="deworming_date" value="{{ old('deworming_date', optional($animal->registrationCard->deworming_date)->format('Y-m-d')) }}" class="mt-1 w-full rounded border-gray-300">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-600">Дата стерилизации</label>
                                            <input type="date" name="sterilization_date" value="{{ old('sterilization_date', optional($animal->registrationCard->sterilization_date)->format('Y-m-d')) }}" class="mt-1 w-full rounded border-gray-300">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-600">Ф.И.О. ветеринара (стерилизация)</label>
                                            <input name="sterilization_vet" value="{{ old('sterilization_vet', $animal->registrationCard->sterilization_vet) }}" class="mt-1 w-full rounded border-gray-300">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-600">Дата маркирования</label>
                                            <input type="date" name="marking_date" value="{{ old('marking_date', optional($animal->registrationCard->marking_date)->format('Y-m-d')) }}" class="mt-1 w-full rounded border-gray-300">
                                        </div>
                                    </div>
                                </div>

                                <!-- Вакцинация -->
                                <div class="pt-6 border-t border-gray-200">
                                    <h4 class="text-base font-semibold text-gray-900 mb-3">Вакцинация</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs text-gray-600">Акт вакцинации — №</label>
                                            <input name="vaccination_act_number" value="{{ old('vaccination_act_number', $animal->registrationCard->vaccination_act_number) }}" class="mt-1 w-full rounded border-gray-300">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-600">Акт вакцинации — дата</label>
                                            <input type="date" name="vaccination_act_date" value="{{ old('vaccination_act_date', optional($animal->registrationCard->vaccination_act_date)->format('Y-m-d')) }}" class="mt-1 w-full rounded border-gray-300">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-600">Вид прививки (например, Рабикан)</label>
                                            <input name="vaccination_type" value="{{ old('vaccination_type', $animal->registrationCard->vaccination_type) }}" class="mt-1 w-full rounded border-gray-300" placeholder="Рабикан">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-600">Серия вакцины</label>
                                            <input name="vaccination_series" value="{{ old('vaccination_series', $animal->registrationCard->vaccination_series) }}" class="mt-1 w-full rounded border-gray-300" placeholder="№ 4003">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-600">Дата изготовления вакцины</label>
                                            <input name="vaccination_manufacture_date" value="{{ old('vaccination_manufacture_date', $animal->registrationCard->vaccination_manufacture_date) }}" class="mt-1 w-full rounded border-gray-300" placeholder="03.2024">
                                        </div>
                            </div>
                        </div>

                                <!-- Документы и выбытие -->
                                <div class="pt-6 border-t border-gray-200">
                                    <h4 class="text-base font-semibold text-gray-900 mb-3">Документы и выбытие</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs text-gray-600">Акт агрессивности — №</label>
                                            <input name="aggression_act_number" value="{{ old('aggression_act_number', $animal->registrationCard->aggression_act_number) }}" class="mt-1 w-full rounded border-gray-300">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-600">Акт агрессивности — дата</label>
                                            <input type="date" name="aggression_act_date" value="{{ old('aggression_act_date', optional($animal->registrationCard->aggression_act_date)->format('Y-m-d')) }}" class="mt-1 w-full rounded border-gray-300">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-xs text-gray-600">Выбытие (причина)</label>
                                            <input name="outcome_reason" value="{{ old('outcome_reason', $animal->registrationCard->outcome_reason) }}" class="mt-1 w-full rounded border-gray-300">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-600">Выбытие (дата)</label>
                                            <input type="date" name="outcome_date" value="{{ old('outcome_date', optional($animal->registrationCard->outcome_date)->format('Y-m-d')) }}" class="mt-1 w-full rounded border-gray-300">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-xs text-gray-600">Адрес и описание места возвращения (размещения)</label>
                                            <textarea name="release_address" class="mt-1 w-full rounded border-gray-300">{{ old('release_address', $animal->registrationCard->release_address) }}</textarea>
                                        </div>
                                    </div>
            </div>

                                <!-- Фотографии -->
                                <div class="pt-6">
                                    <div class="rounded-lg border border-gray-200 overflow-hidden">
                                        <div class="bg-gray-100 px-4 py-2 flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                <path d="M3 7a2 2 0 012-2h2l1-2h6l1 2h2a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                                                <circle cx="12" cy="13" r="4"/>
                                            </svg>
                                            <h4 class="text-base font-semibold text-gray-900">Фотографии</h4>
                                        </div>
                                        <div class="p-4">
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                                <div>
                                                    <label class="block text-xs text-gray-600">Основное фото</label>
                                                    <div class="mt-2 space-y-2">
                                                        @if($animal->photo)
                                                            <img src="{{ asset('storage/'.$animal->photo) }}" 
                                                                 class="w-full aspect-square object-cover rounded border cursor-pointer hover:opacity-75 transition"
                                                                 onclick="openPhotoModal('{{ asset('storage/'.$animal->photo) }}', '')">
                                                        @endif
                                                        <input type="file" name="photo" accept="image/*" class="w-full text-sm">
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-xs text-gray-600">Фото морды</label>
                                                    <div class="mt-2 space-y-2">
                                                        @if($animal->registrationCard && $animal->registrationCard->photo_face)
                                                            <img src="{{ asset('storage/'.$animal->registrationCard->photo_face) }}" 
                                                                 class="w-full aspect-square object-cover rounded border cursor-pointer hover:opacity-75 transition"
                                                                 onclick="openPhotoModal('{{ asset('storage/'.$animal->registrationCard->photo_face) }}', '{{ $animal->registrationCard->photo_profile ? asset('storage/'.$animal->registrationCard->photo_profile) : '' }}')">
                                                        @endif
                                                        <input type="file" name="photo_face" accept="image/*" class="w-full text-sm">
                                                    </div>
                            </div>
                                                <div>
                                                    <label class="block text-xs text-gray-600">Фото профиля (с линейкой)</label>
                                                    <div class="mt-2 space-y-2">
                                                        @if($animal->registrationCard && $animal->registrationCard->photo_profile)
                                                            <img src="{{ asset('storage/'.$animal->registrationCard->photo_profile) }}" 
                                                                 class="w-full aspect-square object-cover rounded border cursor-pointer hover:opacity-75 transition"
                                                                 onclick="openPhotoModal('{{ asset('storage/'.$animal->registrationCard->photo_profile) }}', '{{ $animal->registrationCard->photo_face ? asset('storage/'.$animal->registrationCard->photo_face) : '' }}')">
                            @endif
                                                        <input type="file" name="photo_profile" accept="image/*" class="w-full text-sm">
                                                    </div>
                                                </div>
                                            </div>
                                            <p class="mt-3 text-xs text-gray-500">Обязательные: морда и профиль; основное — для превью.</p>
                                        </div>
                                    </div>
                        </div>
                        
                                <!-- Данные о владельце -->
                                <div class="pt-6 border-t border-gray-200">
                                    <h4 class="text-base font-semibold text-gray-900 mb-3">Данные о владельце</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs text-gray-600">Тип владельца</label>
                                            <select name="new_owner_type" class="mt-1 w-full rounded border-gray-300">
                                                <option value="">—</option>
                                                <option value="legal" {{ old('new_owner_type', $animal->registrationCard->new_owner_type) === 'legal' ? 'selected' : '' }}>Юр. лицо</option>
                                                <option value="individual" {{ old('new_owner_type', $animal->registrationCard->new_owner_type) === 'individual' ? 'selected' : '' }}>Физ. лицо</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-600">Наименование/ФИО</label>
                                            <input name="new_owner_name" value="{{ old('new_owner_name', $animal->registrationCard->new_owner_name) }}" class="mt-1 w-full rounded border-gray-300">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-xs text-gray-600">Адрес</label>
                                            <textarea name="new_owner_address" class="mt-1 w-full rounded border-gray-300">{{ old('new_owner_address', $animal->registrationCard->new_owner_address) }}</textarea>
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-600">Телефон</label>
                                            <input name="new_owner_phone" value="{{ old('new_owner_phone', $animal->registrationCard->new_owner_phone) }}" class="mt-1 w-full rounded border-gray-300">
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-6 border-t border-gray-200">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Сохранить</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>


            </div>

            <!-- Боковая панель: Печать и инфо -->
            <div class="space-y-6">
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Печать</h3>
                    <div class="space-y-3">
                        @if(\Illuminate\Support\Facades\Route::has('admin.animals.print.card'))
                            <a href="{{ route('admin.animals.print.card', $animal) }}" target="_blank"
                               class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                🖨️ Печать карточки
                            </a>
                        @else
                            <button type="button" disabled
                               class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-300 text-gray-600 rounded-md cursor-not-allowed">
                                🖨️ Печать карточки (скоро)
                            </button>
                        @endif
                        @if(\Illuminate\Support\Facades\Route::has('admin.animals.print.cage'))
                            <a href="{{ route('admin.animals.print.cage', $animal) }}" target="_blank"
                           class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                🖨️ Карточка на вольер
                            </a>
                        @else
                            <button type="button" disabled
                               class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-300 text-gray-600 rounded-md cursor-not-allowed">
                                🖨️ Карточка на вольер (скоро)
                            </button>
                        @endif
                    </div>
                </div>
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Передача владельцу</h3>
                    <a href="{{ route('admin.animal-transfer-acts.create') }}"
                       class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                        🤝 Передать новому владельцу
                    </a>
                </div>
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Дополнительная информация</h3>
                    <div class="space-y-3 text-sm">
                        @if($animal->completed_at)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Завершено:</span>
                                <span class="font-medium">{{ $animal->completed_at->format('d.m.Y') }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="mt-4">
                        <button type="button" onclick="deleteAnimal({{ $animal->id }})"
                                class="w-full inline-flex justify-center items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                            🗑️ Удалить
                        </button>
                    </div>
                </div>

                <!-- Задачи текущего этапа -->
                @if($animal->currentStage && $currentStageTasks->isNotEmpty())
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">
                            📋 Задачи этапа: {{ $animal->currentStage->name }}
                        </h3>
                        <span class="text-sm text-gray-500">
                            {{ $currentStageTasks->where('is_completed', true)->count() }} / {{ $currentStageTasks->count() }}
                        </span>
                    </div>
                    
                    <div class="space-y-3">
                        @foreach($currentStageTasks as $task)
                            <div class="border rounded-lg p-3 {{ $task->is_completed ? 'bg-green-50 border-green-200' : 'bg-gray-50' }}">
                                @php
                                    $canCompleteCheckbox = true;
                                    if(str_contains($task->name, 'Установка чипа') && empty($animal->chip_number)) {
                                        $canCompleteCheckbox = false;
                                    }
                                    if(str_contains($task->name, 'Установка бирки') && empty($animal->tag_number)) {
                                        $canCompleteCheckbox = false;
                                    }
                                    if(str_contains($task->name, 'Вакцинация') && (
                                        empty(optional($animal->registrationCard)->vaccination_act_number) ||
                                        empty(optional($animal->registrationCard)->vaccination_act_date) ||
                                        empty(optional($animal->registrationCard)->vaccination_type)
                                    )) {
                                        $canCompleteCheckbox = false;
                                    }
                                @endphp
                                
                                <div class="flex items-start space-x-3">
                                    <div class="flex-shrink-0 mt-1 {{ ($task->is_completed || $canCompleteCheckbox) ? 'cursor-pointer' : 'cursor-not-allowed opacity-50' }}" 
                                         @if($task->is_completed || $canCompleteCheckbox)
                                            onclick="{{ $task->is_completed ? "uncompleteTask({$animal->id}, {$task->id})" : "completeTask({$animal->id}, {$task->id})" }}"
                                         @else
                                            title="Сначала внесите данные в карточку"
                                         @endif>
                                        @if($task->is_completed)
                                            <svg class="w-5 h-5 text-green-600 hover:text-green-700" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                        @else
                                            <div class="w-5 h-5 border-2 border-gray-400 rounded {{ $canCompleteCheckbox ? 'hover:border-blue-500 hover:bg-blue-50' : '' }} transition-colors"></div>
                                        @endif
                                    </div>
                                    
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <h4 class="text-sm font-medium {{ $task->is_completed ? 'text-green-900' : 'text-gray-900' }}">
                                                {{ $task->name }}
                                                @if($task->is_required)
                                                    <span class="text-red-500">*</span>
                                                @endif
                                                
                                                @if(str_contains($task->name, 'Наблюдение за состоянием здоровья') && $animal->currentStage->slug === 'quarantine')
                                                    @php
                                                        $daysInQuarantine = $animal->arrived_at ? (int) $animal->arrived_at->diffInDays(now()) : 0;
                                                        $daysRemaining = max(0, 10 - $daysInQuarantine);
                                                    @endphp
                                                    <span class="ml-2 px-2 py-0.5 text-xs rounded-full {{ $daysRemaining <= 2 ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                                        осталось {{ $daysRemaining }} {{ $daysRemaining === 1 ? 'день' : 'дней' }}
                                                    </span>
                                                @endif
                                            </h4>
                                        </div>
                                        
                                        @if($task->description)
                                            <p class="text-xs text-gray-600 mt-1">{{ $task->description }}</p>
                                        @endif
                                        
                                        @if(str_contains($task->name, 'Наблюдение за состоянием здоровья') && $animal->currentStage->slug === 'quarantine' && $animal->arrived_at)
                                            @php
                                                $daysInQuarantine = (int) $animal->arrived_at->diffInDays(now());
                                                $daysRemaining = max(0, 10 - $daysInQuarantine);
                                            @endphp
                                            <div class="mt-2 p-2 bg-blue-50 rounded text-xs">
                                                <div class="flex justify-between items-center">
                                                    <span class="text-gray-600">Дата отлова:</span>
                                                    <span class="font-medium text-gray-900">{{ $animal->arrived_at->format('d.m.Y') }}</span>
                                                </div>
                                                <div class="flex justify-between items-center mt-1">
                                                    <span class="text-gray-600">Прошло дней:</span>
                                                    <span class="font-medium text-gray-900">{{ $daysInQuarantine }}</span>
                                                </div>
                                                <div class="flex justify-between items-center mt-1">
                                                    <span class="text-gray-600">Осталось дней:</span>
                                                    <span class="font-bold {{ $daysRemaining <= 2 ? 'text-green-600' : 'text-blue-600' }}">{{ $daysRemaining }}</span>
                                                </div>
                                            </div>
                                        @endif
                                        
                                        @if($task->is_completed && $task->completion)
                                            <div class="mt-2 text-xs text-gray-500">
                                                <div>Выполнено: {{ $task->completion->completed_at->format('d.m.Y H:i') }}</div>
                                                @if($task->completion->completedBy)
                                                    <div>Исполнитель: {{ $task->completion->completedBy->name }}</div>
                                                @endif
                                                @if($task->completion->notes)
                                                    <div class="mt-1 text-gray-700">{{ $task->completion->notes }}</div>
                                                @endif
                                            </div>
                                        @endif
                                        
                                        @php
                                            $canComplete = true;
                                            $blockReason = '';
                                            
                                            // Проверка для задачи "Установка чипа"
                                            if(str_contains($task->name, 'Установка чипа') && empty($animal->chip_number)) {
                                                $canComplete = false;
                                                $blockReason = 'Сначала внесите номер чипа в карточку животного';
                                            }
                                            
                                            // Проверка для задачи "Установка бирки"
                                            if(str_contains($task->name, 'Установка бирки') && empty($animal->tag_number)) {
                                                $canComplete = false;
                                                $blockReason = 'Сначала внесите номер бирки в карточку животного';
                                            }
                                            
                                            // Проверка для задачи "Вакцинация"
                                            if(str_contains($task->name, 'Вакцинация') && (
                                                empty(optional($animal->registrationCard)->vaccination_act_number) ||
                                                empty(optional($animal->registrationCard)->vaccination_act_date) ||
                                                empty(optional($animal->registrationCard)->vaccination_type)
                                            )) {
                                                $canComplete = false;
                                                $blockReason = 'Сначала внесите данные о вакцинации: акт (№ и дата) и вид прививки';
                                            }
                                        @endphp
                                        
                                        @if(!$task->is_completed)
                                            @if($canComplete)
                                                <button type="button" 
                                                        onclick="completeTask({{ $animal->id }}, {{ $task->id }})"
                                                        class="mt-2 text-xs text-blue-600 hover:text-blue-800 font-medium">
                                                    ✓ Отметить как выполненное
                                                </button>
                                            @else
                                                <div class="mt-2 p-2 bg-yellow-50 border border-yellow-200 rounded text-xs text-yellow-800">
                                                    ⚠️ {{ $blockReason }}
                                                </div>
                                            @endif
                                        @else
                                            <button type="button" 
                                                    onclick="uncompleteTask({{ $animal->id }}, {{ $task->id }})"
                                                    class="mt-2 text-xs text-gray-600 hover:text-gray-800 font-medium">
                                                ✗ Отменить выполнение
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    @php
                        $requiredTasks = $currentStageTasks->where('is_required', true);
                        $completedRequired = $requiredTasks->where('is_completed', true)->count();
                        $allRequiredCompleted = $completedRequired === $requiredTasks->count();
                    @endphp
                    
                    @if(!$allRequiredCompleted)
                        <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <p class="text-sm text-yellow-800">
                                ⚠️ Для перехода на следующий этап необходимо выполнить все обязательные задачи ({{ $completedRequired }}/{{ $requiredTasks->count() }})
                            </p>
                        </div>
                    @else
                        <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                            <p class="text-sm text-green-800">
                                ✓ Все обязательные задачи выполнены!
                            </p>
                            
                            @if($animal->currentStage && $animal->currentStage->name === 'Готов к выпуску')
                                <div class="mt-4 space-y-3">
                                    <p class="text-sm font-semibold text-gray-900">Выберите дальнейшую судьбу животного:</p>
                                    <div class="flex gap-3">
                                        <form action="{{ route('admin.animals.release-to-original-place', $animal) }}" method="POST" class="flex-1">
                                            @csrf
                                            <button type="submit" class="w-full px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                                📍 На прежнее место
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.animals.keep-in-shelter', $animal) }}" method="POST" class="flex-1">
                                            @csrf
                                            <button type="submit" class="w-full px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                                🏠 Остается в приюте
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- JavaScript для управления задачами -->
    <script>
        function completeTask(animalId, taskId) {
            const notes = prompt('Добавить примечания к выполнению задачи (необязательно):');
            
            fetch(`/admin/animals/${animalId}/complete-task`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    task_id: taskId,
                    notes: notes
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Ошибка: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Произошла ошибка при выполнении задачи');
            });
        }

        function uncompleteTask(animalId, taskId) {
            if (confirm('Отменить выполнение задачи?')) {
                fetch(`/admin/animals/${animalId}/uncomplete-task/${taskId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Ошибка: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Произошла ошибка при отмене задачи');
                });
            }
        }

        function deleteAnimal(animalId) {
            if (confirm('Вы уверены, что хотите удалить это животное? Это действие нельзя отменить.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/animals/${animalId}`;
                
                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';
                form.appendChild(methodField);
                
                const tokenField = document.createElement('input');
                tokenField.type = 'hidden';
                tokenField.name = '_token';
                tokenField.value = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                form.appendChild(tokenField);
                
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
    
    <!-- Подключаем базу кличек -->
    <script src="{{ asset('js/animal-names.js') }}"></script>
@endsection

<!-- Модальное окно для просмотра фото -->
<div id="photoModal" class="hidden fixed inset-0 bg-black bg-opacity-90 z-50 flex items-center justify-center p-4" onclick="closePhotoModal()">
    <div class="relative max-w-7xl w-full" onclick="event.stopPropagation()">
        <!-- Кнопка закрытия -->
        <button onclick="closePhotoModal()" class="absolute top-4 right-4 text-white hover:text-gray-300 text-4xl z-10">&times;</button>
        
        <!-- Контент -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Фото 1 -->
            <div class="bg-white rounded-lg p-2">
                <img id="photoModalImage1" src="" alt="Фото 1" class="w-full h-auto rounded">
            </div>
            
            <!-- Фото 2 -->
            <div id="photoModalImage2Container" class="bg-white rounded-lg p-2 hidden">
                <img id="photoModalImage2" src="" alt="Фото 2" class="w-full h-auto rounded">
            </div>
        </div>
    </div>
</div>

<script>
function openPhotoModal(photo1, photo2) {
    const modal = document.getElementById('photoModal');
    const image1 = document.getElementById('photoModalImage1');
    const image2 = document.getElementById('photoModalImage2');
    const image2Container = document.getElementById('photoModalImage2Container');
    
    image1.src = photo1;
    
    if (photo2) {
        image2.src = photo2;
        image2Container.classList.remove('hidden');
    } else {
        image2Container.classList.add('hidden');
    }
    
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

// Генерация клички для животного
function generateAnimalName(type, gender, color) {
    // Проверяем, загружена ли база кличек
    if (typeof AnimalNames === 'undefined') {
        alert('База кличек не загружена');
        return;
    }
    
    // Определяем пол
    const genderKey = gender === 'male' ? 'male' : 'female';
    
    // Определяем тип животного
    const typeKey = type === 'cat' ? 'cat' : 'dog';
    
    // Получаем список кличек из внешнего файла
    let nameList = [...AnimalNames[typeKey][genderKey]];
    
    // Добавляем клички по окрасу, если окрас указан
    if (color && AnimalNames.colorNames) {
        const colorLower = color.toLowerCase();
        for (const [colorKey, colorNameList] of Object.entries(AnimalNames.colorNames)) {
            if (colorLower.includes(colorKey)) {
                nameList = [...nameList, ...colorNameList];
                break;
            }
        }
    }
    
    // Выбираем случайную кличку
    const randomName = nameList[Math.floor(Math.random() * nameList.length)];
    
    // Вставляем в поле
    const input = document.getElementById('animal-name-input');
    if (input) {
        input.value = randomName;
        input.focus();
        
        // Анимация
        input.classList.add('ring-2', 'ring-indigo-500');
        setTimeout(() => {
            input.classList.remove('ring-2', 'ring-indigo-500');
        }, 1000);
    }
}
</script>
