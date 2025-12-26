@extends('admin.layout')

@section('header')
    Редактирование: {{ $animal->cage_number ? 'Вольер №' . $animal->cage_number : ($animal->name ?? 'Животное #' . $animal->id) }}
@endsection

@section('content')
    <div class="space-y-6">
        <!-- Хлебные крошки -->
        <div class="bg-white shadow rounded-lg">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                <div class="flex items-center space-x-2 text-white">
                    <a href="{{ route('admin.animals.index') }}" class="hover:text-blue-200">Управление животными</a>
                    <span>/</span>
                    <a href="{{ route('admin.animals.show', $animal) }}" class="hover:text-blue-200">
                        {{ $animal->cage_number ? 'Вольер №' . $animal->cage_number : ($animal->name ?? 'Животное #' . $animal->id) }}
                    </a>
                    <span>/</span>
                    <span class="font-semibold">Редактирование</span>
                </div>
            </div>
        </div>

        <!-- Форма редактирования -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Информация о животном</h3>
            </div>
            
            <form method="POST" action="{{ route('admin.animals.update', $animal) }}" enctype="multipart/form-data" class="p-6">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Левая колонка: Фото -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-4">Фотография</label>
                            
                            <!-- Текущее фото -->
                            <div class="mb-4">
                                @php
                                    $displayPhoto = $animal->photo ?? ($animal->registrationCard->photo_face ?? null);
                                @endphp
                                @if($displayPhoto)
                                    <img src="{{ asset('storage/' . $displayPhoto) }}" 
                                         alt="{{ $animal->name ?? 'Животное' }}"
                                         class="w-48 h-48 rounded-lg object-cover border-2 border-gray-300 shadow-md mx-auto cursor-pointer hover:opacity-75 transition"
                                         onclick="openPhotoModal('{{ asset('storage/' . $displayPhoto) }}', '')">
                                @else
                                    <div class="w-48 h-48 rounded-lg bg-gray-100 border-2 border-gray-300 flex items-center justify-center shadow-md mx-auto">
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
                            
                            <!-- Загрузка нового фото -->
                            <input type="file" name="photo" id="photo" accept="image/*"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
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
                            <input type="text" name="name" id="name" value="{{ old('name', $animal->name) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Номер вольера -->
                        <div>
                            <label for="cage_number" class="block text-sm font-medium text-gray-700">Номер вольера</label>
                            <input type="text" name="cage_number" id="cage_number" value="{{ old('cage_number', $animal->cage_number) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('cage_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Тип -->
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700">Тип животного <span class="text-red-500">*</span></label>
                            <select name="type" id="type" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Выберите тип</option>
                                <option value="dog" {{ old('type', $animal->type) === 'dog' ? 'selected' : '' }}>🐕 Собака</option>
                                <option value="cat" {{ old('type', $animal->type) === 'cat' ? 'selected' : '' }}>🐱 Кошка</option>
                                <option value="other" {{ old('type', $animal->type) === 'other' ? 'selected' : '' }}>🐾 Другое</option>
                            </select>
                            @error('type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Пол -->
                        <div>
                            <label for="gender" class="block text-sm font-medium text-gray-700">Пол <span class="text-red-500">*</span></label>
                            <select name="gender" id="gender" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Выберите пол</option>
                                <option value="male" {{ old('gender', $animal->gender) === 'male' ? 'selected' : '' }}>♂️ Самец</option>
                                <option value="female" {{ old('gender', $animal->gender) === 'female' ? 'selected' : '' }}>♀️ Самка</option>
                                <option value="unknown" {{ old('gender', $animal->gender) === 'unknown' ? 'selected' : '' }}>❓ Неизвестно</option>
                            </select>
                            @error('gender')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Порода -->
                        <div>
                            <label for="breed" class="block text-sm font-medium text-gray-700">Порода</label>
                            <input type="text" name="breed" id="breed" value="{{ old('breed', $animal->breed) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('breed')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Окрас -->
                        <div>
                            <label for="color" class="block text-sm font-medium text-gray-700">Окрас</label>
                            <input type="text" name="color" id="color" value="{{ old('color', $animal->color) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
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
                                   value="{{ old('age_months', $animal->age_months) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('age_months')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">От 0 до 300 месяцев</p>
                        </div>

                        <!-- Вес -->
                        <div>
                            <label for="weight" class="block text-sm font-medium text-gray-700">Вес (кг)</label>
                            <input type="number" name="weight" id="weight" min="0" max="200" step="0.1"
                                   value="{{ old('weight', $animal->weight) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('weight')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Номер чипа -->
                        <div>
                            <label for="chip_number" class="block text-sm font-medium text-gray-700">Номер чипа</label>
                            <input type="text" name="chip_number" id="chip_number" value="{{ old('chip_number', $animal->chip_number) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('chip_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Номер бирки -->
                        <div>
                            <label for="tag_number" class="block text-sm font-medium text-gray-700">Номер бирки</label>
                            <input type="text" name="tag_number" id="tag_number" value="{{ old('tag_number', $animal->tag_number) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('tag_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Заявка ОСВВ -->
                        <div>
                            <label for="osvv_request_id" class="block text-sm font-medium text-gray-700">Заявка ОСВВ</label>
                            <select name="osvv_request_id" id="osvv_request_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Не связано с заявкой</option>
                                @foreach($osvvRequests as $request)
                                    <option value="{{ $request->id }}" 
                                            {{ old('osvv_request_id', $animal->osvv_request_id) == $request->id ? 'selected' : '' }}>
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

                        <!-- Статус -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Статус <span class="text-red-500">*</span></label>
                            <select name="status" id="status" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="active" {{ old('status', $animal->status) === 'active' ? 'selected' : '' }}>✅ Активен</option>
                                <option value="released" {{ old('status', $animal->status) === 'released' ? 'selected' : '' }}>🌍 Выпущен</option>
                                <option value="adopted" {{ old('status', $animal->status) === 'adopted' ? 'selected' : '' }}>❤️ Пристроен</option>
                                <option value="deceased" {{ old('status', $animal->status) === 'deceased' ? 'selected' : '' }}>💔 Умер</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Описание -->
                <div class="mt-6">
                    <label for="description" class="block text-sm font-medium text-gray-700">Описание</label>
                    <textarea name="description" id="description" rows="4"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                              placeholder="Особенности поведения, здоровья, характера...">{{ old('description', $animal->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Кнопки действий -->
                <div class="mt-8 flex justify-end space-x-3">
                    <a href="{{ route('admin.animals.show', $animal) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Отмена
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        💾 Сохранить изменения
                    </button>
                </div>
            </form>
        </div>
    </div>

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

    <script>
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
@endsection 