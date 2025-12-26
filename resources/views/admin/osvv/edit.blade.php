@extends('admin.layout')

@section('header', 'Редактирование заявки ОСВВ №' . $osvvRequest->id)

@section('content')
    <div class="space-y-6">
        <!-- Верхняя панель с заголовком и кнопками -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828l-3.168 3.168a2 2 0 01-2.828 0l-.5-.5a2 2 0 010-2.828l3.668-3.668z" />
                        </svg>
                        Редактирование заявки #{{ $osvvRequest->id }}
                    </h3>
                    <a href="{{ route('admin.osvv.show', $osvvRequest) }}" class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-white/20 active:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                        </svg>
                        Вернуться к просмотру
                    </a>
                </div>
                <div class="mt-2">
                    <p class="text-white/70 text-sm">Внесите необходимые изменения в данные заявки и нажмите "Сохранить"</p>
                </div>
            </div>
        </div>

        <!-- Форма редактирования -->
        <form action="{{ route('admin.osvv.update', $osvvRequest) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <!-- Контактная информация -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="border-b border-gray-200">
                    <div class="px-6 py-4 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <h4 class="text-base font-semibold text-gray-800">Контактная информация</h4>
                    </div>
                </div>
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label for="contact_name" class="block text-sm font-medium text-gray-700">ФИО</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <input type="text" name="contact_name" id="contact_name" value="{{ old('contact_name', $osvvRequest->contact_name) }}" class="pl-10 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" required>
                            </div>
                            @error('contact_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="sm:col-span-3">
                            <label for="contact_phone" class="block text-sm font-medium text-gray-700">Телефон</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                </div>
                                <input type="text" name="contact_phone" id="contact_phone" value="{{ old('contact_phone', $osvvRequest->contact_phone) }}" class="pl-10 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" required>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">📱 Мобильные: +7 (9XX) XXX-XX-XX &nbsp;&nbsp; 📞 Городские: +7 (473) XXX-XX-XX</p>
                            @error('contact_phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="sm:col-span-6">
                            <label for="contact_email" class="block text-sm font-medium text-gray-700">Email</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <input type="email" name="contact_email" id="contact_email" value="{{ old('contact_email', $osvvRequest->contact_email) }}" class="pl-10 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                            @error('contact_email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Информация о животном -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="border-b border-gray-200">
                    <div class="px-6 py-4 flex items-center">
                        @if(old('animal_type', $osvvRequest->animal_type) === 'cat')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 12.5l6-6.5 2 1L12 7l4 4-1.5 1.5M20 12v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5c0-1.1.9-2 2-2h1V9a1 1 0 011-1h1a1 1 0 011 1v1h4V9a1 1 0 011-1h1a1 1 0 011 1v1h1a2 2 0 012 2z" />
                            </svg>
                        @elseif(old('animal_type', $osvvRequest->animal_type) === 'dog')
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
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <div class="sm:col-span-2">
                            <label for="animal_type" class="block text-sm font-medium text-gray-700">Вид животного</label>
                            <div class="mt-1">
                                <select name="animal_type" id="animal_type" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" required>
                                    <option value="cat" {{ old('animal_type', $osvvRequest->animal_type) === 'cat' ? 'selected' : '' }}>Кошка</option>
                                    <option value="dog" {{ old('animal_type', $osvvRequest->animal_type) === 'dog' ? 'selected' : '' }}>Собака</option>
                                    <option value="other" {{ old('animal_type', $osvvRequest->animal_type) === 'other' ? 'selected' : '' }}>Другое</option>
                                </select>
                            </div>
                            @error('animal_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="sm:col-span-2" id="other_animal_type_block" style="{{ old('animal_type', $osvvRequest->animal_type) === 'other' ? '' : 'display: none;' }}">
                            <label for="animal_type_other" class="block text-sm font-medium text-gray-700">Укажите вид животного</label>
                            <div class="mt-1">
                                <input type="text" name="animal_type_other" id="animal_type_other" value="{{ old('animal_type_other', $osvvRequest->animal_type_other) }}" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                            @error('animal_type_other')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="sm:col-span-2">
                            <label for="animal_gender" class="block text-sm font-medium text-gray-700">Пол</label>
                            <div class="mt-1">
                                <select name="animal_gender" id="animal_gender" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" required>
                                    <option value="male" {{ old('animal_gender', $osvvRequest->animal_gender) === 'male' ? 'selected' : '' }}>Самец</option>
                                    <option value="female" {{ old('animal_gender', $osvvRequest->animal_gender) === 'female' ? 'selected' : '' }}>Самка</option>
                                    <option value="unknown" {{ old('animal_gender', $osvvRequest->animal_gender) === 'unknown' ? 'selected' : '' }}>Неизвестно</option>
                                </select>
                            </div>
                            @error('animal_gender')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="sm:col-span-2">
                            <label for="animal_age" class="block text-sm font-medium text-gray-700">Примерный возраст</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <input type="text" name="animal_age" id="animal_age" value="{{ old('animal_age', $osvvRequest->animal_age) }}" class="pl-10 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                            @error('animal_age')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="sm:col-span-2">
                            <label for="animals_count" class="block text-sm font-medium text-gray-700">Количество животных</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                    </svg>
                                </div>
                                <input type="number" min="1" name="animals_count" id="animals_count" value="{{ old('animals_count', $osvvRequest->animals_count ?? 1) }}" class="pl-10 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                            @error('animals_count')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="sm:col-span-2">
                            <fieldset class="mt-4">
                                <div class="space-y-4">
                                    <div class="relative flex items-start">
                                        <div class="flex items-center h-5">
                                            <input id="has_bite" name="has_bite" type="checkbox" value="1" {{ old('has_bite', $osvvRequest->has_bite) ? 'checked' : '' }} class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="has_bite" class="font-medium text-gray-700">Был укус</label>
                                            <p class="text-gray-500 text-xs">Срок выезда — 1 день</p>
                                        </div>
                                    </div>
                                    <div class="relative flex items-start">
                                        <div class="flex items-center h-5">
                                            <input id="is_pregnant" name="is_pregnant" type="checkbox" value="1" {{ old('is_pregnant', $osvvRequest->is_pregnant) ? 'checked' : '' }} class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="is_pregnant" class="font-medium text-gray-700">Беременность</label>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                        
                        <div class="sm:col-span-6">
                            <label for="animal_description" class="block text-sm font-medium text-gray-700">Описание животного</label>
                            <div class="mt-1">
                                <textarea name="animal_description" id="animal_description" rows="3" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">{{ old('animal_description', $osvvRequest->animal_description) }}</textarea>
                            </div>
                            @error('animal_description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Адрес/локация -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="border-b border-gray-200">
                    <div class="px-6 py-4 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <h4 class="text-base font-semibold text-gray-800">Адрес/локация</h4>
                    </div>
                </div>
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <div class="sm:col-span-6">
                            <label for="location_address" class="block text-sm font-medium text-gray-700">Адрес</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    </svg>
                                </div>
                                <textarea name="location_address" id="location_address" rows="2" class="pl-10 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" required>{{ old('location_address', $osvvRequest->location_address) }}</textarea>
                            </div>
                            @error('location_address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="sm:col-span-3">
                            <label for="district" class="block text-sm font-medium text-gray-700">Район</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                    </svg>
                                </div>
                                <input type="text" name="district" id="district" value="{{ old('district', $osvvRequest->district) }}" class="pl-10 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                            @error('district')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="sm:col-span-3">
                            <label for="location_landmark" class="block text-sm font-medium text-gray-700">Ориентир</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m-6 12V10a2 2 0 012-2h2a2 2 0 012 2v8m-3-4v4" />
                                    </svg>
                                </div>
                                <input type="text" name="location_landmark" id="location_landmark" value="{{ old('location_landmark', $osvvRequest->location_landmark) }}" class="pl-10 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                            @error('location_landmark')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Информация о мероприятиях -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="border-b border-gray-200">
                    <div class="px-6 py-4 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <h4 class="text-base font-semibold text-gray-800">Информация о мероприятиях</h4>
                    </div>
                </div>
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label for="departure_date" class="block text-sm font-medium text-gray-700">Дата выезда</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <input type="date" name="departure_date" id="departure_date" value="{{ old('departure_date', $osvvRequest->departure_date ? $osvvRequest->departure_date->format('Y-m-d') : '') }}" class="pl-10 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                            @error('departure_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">Крайний срок выезда</label>
                            <div class="mt-1">
                                <div class="px-3 py-2 bg-gray-50 rounded-md border border-gray-200">
                                    @if($osvvRequest->deadline_date)
                                        <span class="text-sm font-medium text-gray-700">{{ $osvvRequest->deadline_date->format('d.m.Y') }}</span>
                                        <p class="text-xs text-gray-500 mt-1">
                                            Рассчитывается автоматически: {{ $osvvRequest->has_bite ? '1 день' : '6 дней' }} от даты создания заявки
                                        </p>
                                    @else
                                        <span class="text-sm text-gray-500">Будет рассчитан автоматически после сохранения</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="sm:col-span-6">
                            <label for="capture_result" class="block text-sm font-medium text-gray-700">Результат проведенных мероприятий</label>
                            <div class="mt-1">
                                <textarea name="capture_result" id="capture_result" rows="3" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">{{ old('capture_result', $osvvRequest->capture_result) }}</textarea>
                            </div>
                            @error('capture_result')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Дополнительная информация -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="border-b border-gray-200">
                    <div class="px-6 py-4 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h4 class="text-base font-semibold text-gray-800">Дополнительная информация</h4>
                    </div>
                </div>
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label for="status" class="block text-sm font-medium text-gray-700">Статус</label>
                            <div class="mt-1">
                                <select name="status" id="status" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" required>
                                    <option value="new" {{ old('status', $osvvRequest->status) === 'new' ? 'selected' : '' }}>Новая заявка</option>
                                    <option value="processing" {{ old('status', $osvvRequest->status) === 'processing' ? 'selected' : '' }}>В обработке</option>
                                    <option value="capture_scheduled" {{ old('status', $osvvRequest->status) === 'capture_scheduled' ? 'selected' : '' }}>Запланирован отлов</option>
                                    <option value="captured" {{ old('status', $osvvRequest->status) === 'captured' ? 'selected' : '' }}>Животное отловлено</option>
                                    <option value="in_shelter" {{ old('status', $osvvRequest->status) === 'in_shelter' ? 'selected' : '' }}>В приюте</option>
                                    <option value="sterilized" {{ old('status', $osvvRequest->status) === 'sterilized' ? 'selected' : '' }}>Стерилизовано</option>
                                    <option value="vaccinated" {{ old('status', $osvvRequest->status) === 'vaccinated' ? 'selected' : '' }}>Вакцинировано</option>
                                    <option value="ready_for_return" {{ old('status', $osvvRequest->status) === 'ready_for_return' ? 'selected' : '' }}>Готово к возврату</option>
                                    <option value="returned" {{ old('status', $osvvRequest->status) === 'returned' ? 'selected' : '' }}>Возвращено</option>
                                    <option value="completed" {{ old('status', $osvvRequest->status) === 'completed' ? 'selected' : '' }}>Завершено</option>
                                    <option value="cancelled" {{ old('status', $osvvRequest->status) === 'cancelled' ? 'selected' : '' }}>Отменено</option>
                                </select>
                            </div>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="sm:col-span-3">
                            <label for="created_at" class="block text-sm font-medium text-gray-700">Дата создания заявки</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <input type="datetime-local" name="created_at" id="created_at" value="{{ old('created_at', $osvvRequest->created_at->format('Y-m-d\TH:i')) }}" class="pl-10 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                <p class="mt-1 text-xs text-gray-500">Текущее значение: {{ $osvvRequest->created_at->format('d.m.Y H:i:s') }}</p>
                            </div>
                            @error('created_at')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="sm:col-span-6">
                            <label for="notes" class="block text-sm font-medium text-gray-700">Примечания</label>
                            <div class="mt-1">
                                <textarea name="notes" id="notes" rows="3" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">{{ old('notes', $osvvRequest->notes) }}</textarea>
                            </div>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Кнопки управления -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 flex justify-end space-x-3">
                    <a href="{{ route('admin.osvv.show', $osvvRequest) }}" class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Отмена
                    </a>
                    <button type="submit" class="inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Сохранить изменения
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const animalTypeSelect = document.getElementById('animal_type');
            const otherAnimalTypeBlock = document.getElementById('other_animal_type_block');
            
            animalTypeSelect.addEventListener('change', function() {
                if (this.value === 'other') {
                    otherAnimalTypeBlock.style.display = '';
                } else {
                    otherAnimalTypeBlock.style.display = 'none';
                }
            });
        });
    </script>
@endsection 