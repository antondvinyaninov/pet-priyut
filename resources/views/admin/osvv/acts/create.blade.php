@extends('admin.layout')

@section('title', 'Создание акта отлова')

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
                    Создание акта отлова
                </h3>
                <div class="flex space-x-2">
                    <a href="{{ route('admin.osvv.index') }}" class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-white/20 active:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                        </svg>
                        К списку заявок
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Форма создания акта отлова -->
    <div class="bg-white shadow rounded-lg">
        <form action="{{ route('admin.osvv.acts.store') }}" method="POST" class="p-6">
            @csrf
            
            <!-- Основная информация об акте -->
            <div class="mb-8">
                <h4 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Основная информация
                </h4>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Заявка ОСВВ -->
                    <div class="col-span-1">
                        <label for="osvv_request_id" class="block text-sm font-medium text-gray-700 mb-1">Заявка ОСВВ *</label>
                        <select name="osvv_request_id" id="osvv_request_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required {{ $osvvRequest ? 'disabled' : '' }}>
                            @if($osvvRequest)
                                <option value="{{ $osvvRequest->id }}" selected>№{{ $osvvRequest->id }} - {{ $osvvRequest->location_address }}</option>
                                <input type="hidden" name="osvv_request_id" value="{{ $osvvRequest->id }}">
                            @else
                                <option value="">Выберите заявку</option>
                                @foreach(App\Models\OsvvRequest::orderBy('id', 'desc')->limit(100)->get() as $request)
                                    <option value="{{ $request->id }}">№{{ $request->id }} - {{ $request->location_address }}</option>
                                @endforeach
                            @endif
                        </select>
                        @error('osvv_request_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Номер акта -->
                    <div class="col-span-1">
                        <label for="act_number" class="block text-sm font-medium text-gray-700 mb-1">Номер акта *</label>
                        <input type="text" name="act_number" id="act_number" value="{{ $actNumber }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required readonly>
                        @error('act_number')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Статус акта -->
                    <div class="col-span-1">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Статус акта *</label>
                        <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                            @foreach(App\Models\CaptureAct::getStatusList() as $value => $label)
                                <option value="{{ $value }}" {{ old('status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Отловщик -->
                    <div class="col-span-1">
                        <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">Отловщик</label>
                        <select name="user_id" id="user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Не выбран</option>
                            @foreach($catchers as $catcher)
                                <option value="{{ $catcher->id }}" {{ old('user_id') == $catcher->id ? 'selected' : '' }}>{{ $catcher->name }}</option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Дата отлова -->
                    <div class="col-span-1">
                        <label for="capture_date" class="block text-sm font-medium text-gray-700 mb-1">Дата отлова *</label>
                        <input type="date" name="capture_date" id="capture_date" value="{{ old('capture_date', now()->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                        @error('capture_date')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Время отлова -->
                    <div class="col-span-1">
                        <label for="capture_time" class="block text-sm font-medium text-gray-700 mb-1">Время отлова</label>
                        <input type="time" name="capture_time" id="capture_time" value="{{ old('capture_time', now()->format('H:i')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        @error('capture_time')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Количество отловленных животных -->
                    <div class="col-span-1">
                        <label for="animals_count" class="block text-sm font-medium text-gray-700 mb-1">Количество отловленных животных *</label>
                        <input type="number" name="animals_count" id="animals_count" value="{{ old('animals_count', $osvvRequest ? $osvvRequest->animals_count : 1) }}" min="1" max="20" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                        @error('animals_count')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">При статусе "Выполнен" животные автоматически добавятся в систему управления</p>
                    </div>
                </div>
            </div>
            
            <!-- Информация о месте отлова -->
            <div class="mb-8">
                <h4 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Место отлова
                </h4>
                
                <div class="grid grid-cols-1 gap-6">
                    <!-- Адрес/место отлова -->
                    <div>
                        <label for="capture_location" class="block text-sm font-medium text-gray-700 mb-1">Адрес/место отлова *</label>
                        <input type="text" name="capture_location" id="capture_location" value="{{ old('capture_location', $osvvRequest ? $osvvRequest->location_address : '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                        @error('capture_location')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            
            <!-- Информация о животном -->
            <div class="mb-8">
                <h4 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Информация о животном
                </h4>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Вид животного -->
                    <div>
                        <label for="animal_type" class="block text-sm font-medium text-gray-700 mb-1">Вид животного</label>
                        <select name="animal_type" id="animal_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Не указано</option>
                            <option value="cat" {{ old('animal_type', $osvvRequest && $osvvRequest->animal_type == 'cat' ? 'cat' : '') == 'cat' ? 'selected' : '' }}>Кошка</option>
                            <option value="dog" {{ old('animal_type', $osvvRequest && $osvvRequest->animal_type == 'dog' ? 'dog' : '') == 'dog' ? 'selected' : '' }}>Собака</option>
                            <option value="other" {{ old('animal_type') == 'other' ? 'selected' : '' }}>Другое</option>
                        </select>
                        @error('animal_type')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Пол животного -->
                    <div>
                        <label for="animal_gender" class="block text-sm font-medium text-gray-700 mb-1">Пол животного</label>
                        <select name="animal_gender" id="animal_gender" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Не указано</option>
                            <option value="male" {{ old('animal_gender', $osvvRequest && $osvvRequest->animal_gender == 'male' ? 'male' : '') == 'male' ? 'selected' : '' }}>Самец</option>
                            <option value="female" {{ old('animal_gender', $osvvRequest && $osvvRequest->animal_gender == 'female' ? 'female' : '') == 'female' ? 'selected' : '' }}>Самка</option>
                        </select>
                        @error('animal_gender')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Порода -->
                    <div>
                        <label for="animal_breed" class="block text-sm font-medium text-gray-700 mb-1">Порода</label>
                        <input type="text" name="animal_breed" id="animal_breed" value="{{ old('animal_breed') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        @error('animal_breed')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Окрас -->
                    <div>
                        <label for="animal_color" class="block text-sm font-medium text-gray-700 mb-1">Окрас</label>
                        <input type="text" name="animal_color" id="animal_color" value="{{ old('animal_color') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        @error('animal_color')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Размер -->
                    <div>
                        <label for="animal_size" class="block text-sm font-medium text-gray-700 mb-1">Размер</label>
                        <select name="animal_size" id="animal_size" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Не указано</option>
                            <option value="small" {{ old('animal_size') == 'small' ? 'selected' : '' }}>Маленький</option>
                            <option value="medium" {{ old('animal_size') == 'medium' ? 'selected' : '' }}>Средний</option>
                            <option value="large" {{ old('animal_size') == 'large' ? 'selected' : '' }}>Крупный</option>
                        </select>
                        @error('animal_size')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Способ отлова -->
                    <div>
                        <label for="capturing_method" class="block text-sm font-medium text-gray-700 mb-1">Способ отлова</label>
                        <select name="capturing_method" id="capturing_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Не указано</option>
                            <option value="net" {{ old('capturing_method') == 'net' ? 'selected' : '' }}>Сеть</option>
                            <option value="cage" {{ old('capturing_method') == 'cage' ? 'selected' : '' }}>Клетка-ловушка</option>
                            <option value="pole" {{ old('capturing_method') == 'pole' ? 'selected' : '' }}>Сачок</option>
                            <option value="hand" {{ old('capturing_method') == 'hand' ? 'selected' : '' }}>Руками</option>
                            <option value="other" {{ old('capturing_method') == 'other' ? 'selected' : '' }}>Другое</option>
                        </select>
                        @error('capturing_method')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Особые приметы -->
                    <div class="col-span-full">
                        <label for="animal_features" class="block text-sm font-medium text-gray-700 mb-1">Особые приметы</label>
                        <textarea name="animal_features" id="animal_features" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('animal_features') }}</textarea>
                        @error('animal_features')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Особенности поведения -->
                    <div class="col-span-full">
                        <label for="animal_behavior" class="block text-sm font-medium text-gray-700 mb-1">Особенности поведения</label>
                        <input type="text" name="animal_behavior" id="animal_behavior" value="{{ old('animal_behavior') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        @error('animal_behavior')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            
            <!-- Дополнительная информация -->
            <div class="mb-8">
                <h4 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                    </svg>
                    Дополнительная информация
                </h4>
                
                <div class="grid grid-cols-1 gap-6">
                    <!-- Примечания -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Примечания</label>
                        <textarea name="notes" id="notes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            
            <!-- Кнопки формы -->
            <div class="flex items-center justify-end gap-x-4">
                <a href="{{ $osvvRequest ? route('admin.osvv.show', $osvvRequest->id) : route('admin.osvv.acts.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Отмена
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Создать акт отлова
                </button>
            </div>
        </form>
    </div>
</div>
@endsection 