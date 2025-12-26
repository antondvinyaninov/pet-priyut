@extends('admin.layout')

@section('header')
    Добавление нового животного
@endsection

@section('content')
    <div class="space-y-6">
        <!-- Хлебные крошки -->
        <div class="bg-white shadow rounded-lg">
            <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-4">
                <div class="flex items-center space-x-2 text-white">
                    <a href="{{ route('admin.animals.index') }}" class="hover:text-green-200">Управление животными</a>
                    <span>/</span>
                    <span class="font-semibold">Новое животное</span>
                </div>
            </div>
        </div>

        <!-- Форма создания -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Информация о новом животном</h3>
            </div>
            
            <form method="POST" action="{{ route('admin.animals.store') }}" enctype="multipart/form-data" class="p-6">
                @csrf
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Левая колонка: Фото -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-4">Фотография</label>
                            
                            <!-- Заглушка для фото -->
                            <div class="mb-4">
                                <div class="w-48 h-48 rounded-lg bg-gray-100 border-2 border-gray-300 flex items-center justify-center shadow-md mx-auto">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.375a2.25 2.25 0 01-2.25-2.25V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184" />
                                    </svg>
                                </div>
                            </div>
                            
                            <!-- Загрузка фото -->
                            <input type="file" name="photo" id="photo" accept="image/*"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                            @error('photo')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Загрузите изображение (JPG, PNG, GIF, до 2MB)</p>
                        </div>
                    </div>

                    <!-- Средняя колонка: Основная информация -->
                    <div class="space-y-4">
                        <!-- Имя/Кличка -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Кличка</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Номер вольера -->
                        <div>
                            <label for="cage_number" class="block text-sm font-medium text-gray-700">Номер вольера</label>
                            <input type="text" name="cage_number" id="cage_number" value="{{ old('cage_number') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                            @error('cage_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Тип -->
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700">Тип животного <span class="text-red-500">*</span></label>
                            <select name="type" id="type" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                <option value="">Выберите тип</option>
                                <option value="dog" {{ old('type') === 'dog' ? 'selected' : '' }}>🐕 Собака</option>
                                <option value="cat" {{ old('type') === 'cat' ? 'selected' : '' }}>🐱 Кошка</option>
                                <option value="other" {{ old('type') === 'other' ? 'selected' : '' }}>🐾 Другое</option>
                            </select>
                            @error('type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Пол -->
                        <div>
                            <label for="gender" class="block text-sm font-medium text-gray-700">Пол <span class="text-red-500">*</span></label>
                            <select name="gender" id="gender" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                <option value="">Выберите пол</option>
                                <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>♂️ Самец</option>
                                <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>♀️ Самка</option>
                                <option value="unknown" {{ old('gender') === 'unknown' ? 'selected' : '' }}>❓ Неизвестно</option>
                            </select>
                            @error('gender')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Порода -->
                        <div>
                            <label for="breed" class="block text-sm font-medium text-gray-700">Порода</label>
                            <input type="text" name="breed" id="breed" value="{{ old('breed') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                            @error('breed')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Окрас -->
                        <div>
                            <label for="color" class="block text-sm font-medium text-gray-700">Окрас</label>
                            <input type="text" name="color" id="color" value="{{ old('color') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                            @error('color')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Правая колонка: Дополнительная информация -->
                    <div class="space-y-4">
                        <!-- Возраст -->
                        <div>
                            <label for="age_months" class="block text-sm font-medium text-gray-700">Возраст (в месяцах)</label>
                            <input type="number" name="age_months" id="age_months" min="0" max="300" 
                                   value="{{ old('age_months') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                            @error('age_months')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">От 0 до 300 месяцев</p>
                        </div>

                        <!-- Вес -->
                        <div>
                            <label for="weight" class="block text-sm font-medium text-gray-700">Вес (кг)</label>
                            <input type="number" name="weight" id="weight" min="0" max="200" step="0.1"
                                   value="{{ old('weight') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                            @error('weight')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Номер чипа -->
                        <div>
                            <label for="chip_number" class="block text-sm font-medium text-gray-700">Номер чипа</label>
                            <input type="text" name="chip_number" id="chip_number" value="{{ old('chip_number') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                            @error('chip_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Номер бирки -->
                        <div>
                            <label for="tag_number" class="block text-sm font-medium text-gray-700">Номер бирки</label>
                            <input type="text" name="tag_number" id="tag_number" value="{{ old('tag_number') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                            @error('tag_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Заявка ОСВВ -->
                        <div>
                            <label for="osvv_request_id" class="block text-sm font-medium text-gray-700">Заявка ОСВВ</label>
                            <select name="osvv_request_id" id="osvv_request_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                <option value="">Не связано с заявкой</option>
                                @foreach($osvvRequests as $request)
                                    <option value="{{ $request->id }}" 
                                            {{ old('osvv_request_id') == $request->id ? 'selected' : '' }}>
                                        ОСВВ #{{ $request->id }}
                                        @if($request->applicant_name) - {{ $request->applicant_name }} @endif
                                        @if($request->location_address) ({{ Str::limit($request->location_address, 30) }}) @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('osvv_request_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Начальный этап -->
                        <div>
                            <label for="current_stage_id" class="block text-sm font-medium text-gray-700">Начальный этап</label>
                            <select name="current_stage_id" id="current_stage_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                <option value="">Выберите этап</option>
                                @foreach($stages as $stage)
                                    <option value="{{ $stage->id }}" 
                                            {{ old('current_stage_id') == $stage->id ? 'selected' : '' }}>
                                        {{ $stage->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('current_stage_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Описание -->
                <div class="mt-6">
                    <label for="description" class="block text-sm font-medium text-gray-700">Описание</label>
                    <textarea name="description" id="description" rows="4"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                              placeholder="Особенности поведения, здоровья, характера...">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Кнопки действий -->
                <div class="mt-8 flex justify-end space-x-3">
                    <a href="{{ route('admin.animals.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Отмена
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        ✨ Создать животное
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection 