@extends('admin.layout')

@section('header', 'Создание заявки ОСВВ')

@section('content')
    <style>
        /* Стили для drag & drop зон */
        .medical-drop-zone, .evidence-drop-zone, .animal-photos-drop-zone {
            transition: all 0.3s ease;
        }
        
        .medical-drop-zone:hover, .evidence-drop-zone:hover, .animal-photos-drop-zone:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.15);
        }
        
        .animal-photos-drop-zone:hover {
            box-shadow: 0 4px 12px rgba(147, 51, 234, 0.15);
        }
        
        .medical-drop-zone.dragover, .evidence-drop-zone.dragover {
            border-color: #ef4444 !important;
            background-color: #fecaca !important;
            transform: scale(1.02);
        }
        
        .animal-photos-drop-zone.dragover {
            border-color: #9333ea !important;
            background-color: #e9d5ff !important;
            transform: scale(1.02);
        }
        
        /* Анимация для превью файлов */
        .file-preview-enter {
            opacity: 0;
            transform: translateY(-10px);
            animation: slideInUp 0.3s ease forwards;
        }
        
        @keyframes slideInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Стили для кнопок удаления */
        .remove-file-btn {
            transition: all 0.2s ease;
        }
        
        .remove-file-btn:hover {
            transform: scale(1.1);
        }
        
        /* Стили для уведомлений о дубликатах */
        .duplicate-warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fed7aa 100%);
            border: 1px solid #f59e0b;
            border-left: 4px solid #f59e0b;
            animation: slideDown 0.3s ease forwards;
        }
        
        .duplicate-error {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border: 1px solid #ef4444;
            border-left: 4px solid #ef4444;
            animation: slideDown 0.3s ease forwards;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .duplicate-item {
            transition: all 0.2s ease;
        }
        
        .duplicate-item:hover {
            background-color: rgba(255, 255, 255, 0.5);
            transform: translateX(4px);
        }
        
        .similarity-badge {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 12px;
            display: inline-block;
        }
    </style>

    <div class="space-y-6">
        <!-- Верхняя панель с заголовком -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Создание новой заявки ОСВВ
                    </h3>
                </div>
                <div class="mt-2">
                    <p class="text-white/70 text-sm">Заполните все необходимые поля для создания новой заявки на отлов, стерилизацию и вакцинацию животных</p>
                </div>
            </div>
        </div>

        <!-- Информационная панель с шагами заполнения -->
        <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-4">
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-indigo-700 text-sm">Заполните форму, разбитую на 3 раздела: контактная информация, информация о животном с медиа файлами, и адрес.</p>
            </div>
        </div>

        <!-- Блок для отображения ошибок валидации -->
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-4" id="validation-errors">
                <div class="flex items-start">
                    <svg class="h-5 w-5 text-red-600 mr-3 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                    <div class="flex-1">
                        <h4 class="text-sm font-semibold text-red-800 mb-2">
                            ❌ Обнаружены ошибки в заполнении формы
                        </h4>
                        <div class="text-sm text-red-700">
                            <p class="mb-2">Пожалуйста, исправьте следующие ошибки:</p>
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <button type="button" onclick="document.getElementById('validation-errors').style.display='none'" 
                                class="mt-3 text-xs text-red-600 hover:text-red-800 underline">
                            Скрыть уведомление
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Блок для отображения успешного сохранения -->
        @if (session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4" id="success-message">
                <div class="flex items-start">
                    <svg class="h-5 w-5 text-green-600 mr-3 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <div class="flex-1">
                        <h4 class="text-sm font-semibold text-green-800 mb-1">
                            ✅ Успешно!
                        </h4>
                        <p class="text-sm text-green-700">{{ session('success') }}</p>
                        <button type="button" onclick="document.getElementById('success-message').style.display='none'" 
                                class="mt-2 text-xs text-green-600 hover:text-green-800 underline">
                            Скрыть уведомление
                        </button>
                    </div>
                </div>
            </div>
        @endif
        
        <!-- Контейнер для уведомлений о дубликатах -->
        <div id="duplicates-container" style="display: none;"></div>

        <form action="{{ route('admin.osvv.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            
            <!-- Блок 1: Контактная информация -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <span class="flex items-center justify-center h-6 w-6 rounded-full bg-indigo-600 text-white text-sm font-bold mr-3">1</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Контактная информация
                    </h3>
                </div>
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Левая колонка - контактные данные -->
                        <div class="space-y-6">
                            <div class="flex items-start mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500 mr-2 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                <div>
                                    <h5 class="text-sm font-medium text-gray-700 mb-1">Данные заявителя</h5>
                                    <p class="text-xs text-gray-500">Контактная информация человека, подавшего заявку</p>
                                </div>
                            </div>

                            <div>
                                <label for="contact_name" class="block text-sm font-medium text-gray-700">ФИО заявителя</label>
                                <div class="mt-1">
                                    <input type="text" name="contact_name" id="contact_name" value="{{ old('contact_name') }}" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" required placeholder="Введите ФИО...">
                                </div>
                                @error('contact_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="contact_phone" class="block text-sm font-medium text-gray-700">Телефон</label>
                                <div class="mt-1">
                                    <input type="text" name="contact_phone" id="contact_phone" value="{{ old('contact_phone') }}" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" required placeholder="+7">
                                </div>
                                <p class="mt-1 text-xs text-gray-500">📱 Мобильные: +7 (9XX) XXX-XX-XX &nbsp;&nbsp; 📞 Городские: +7 (473) XXX-XX-XX</p>
                                @error('contact_phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="contact_email" class="block text-sm font-medium text-gray-700">Email (необязательно)</label>
                                <div class="mt-1">
                                    <input type="email" name="contact_email" id="contact_email" value="{{ old('contact_email') }}" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="example@mail.ru">
                                </div>
                                @error('contact_email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Правая колонка - описание случая -->
                        <div class="space-y-6">
                            <div class="flex items-start mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500 mr-2 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <div>
                                    <h5 class="text-sm font-medium text-gray-700 mb-1">Описание случая и источник</h5>
                                    <p class="text-xs text-gray-500">Подробная информация о ситуации и источнике заявки</p>
                                </div>
                            </div>

                            <div>
                                <label for="source_type" class="block text-sm font-medium text-gray-700">Источник заявки</label>
                                <div class="mt-1">
                                    <select name="source_type" id="source_type" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                        <option value="">Выберите источник...</option>
                                        <option value="district_office" {{ old('source_type') === 'district_office' ? 'selected' : '' }}>Управа района</option>
                                        <option value="telegram" {{ old('source_type') === 'telegram' ? 'selected' : '' }}>Телеграм</option>
                                        <option value="vkontakte" {{ old('source_type') === 'vkontakte' ? 'selected' : '' }}>ВКонтакте</option>
                                        <option value="phone" {{ old('source_type') === 'phone' ? 'selected' : '' }}>Телефон</option>
                                        <option value="media" {{ old('source_type') === 'media' ? 'selected' : '' }}>СМИ</option>
                                        <option value="other" {{ old('source_type') === 'other' ? 'selected' : '' }}>Другое</option>
                                    </select>
                                </div>
                                @error('source_type')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Поля для управы района -->
                            <div id="district_office_fields" style="{{ old('source_type') === 'district_office' ? '' : 'display: none;' }}">
                                <div class="bg-indigo-50 border border-indigo-200 rounded-md p-3">
                                    <div class="space-y-3">
                                        <div>
                                            <label for="source_district" class="block text-xs font-medium text-indigo-700">Район управы</label>
                                            <div class="mt-1">
                                                <input type="text" name="source_district" id="source_district" value="{{ old('source_district') }}" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full text-xs border-gray-300 rounded-md" placeholder="Например: Центральный район">
                                            </div>
                                            @error('source_district')
                                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        
                                        <div>
                                            <label for="aurora_number" class="block text-xs font-medium text-indigo-700">Номер из программы "Аврора"</label>
                                            <div class="mt-1">
                                                <input type="text" name="aurora_number" id="aurora_number" value="{{ old('aurora_number') }}" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full text-xs border-gray-300 rounded-md" placeholder="АВР-2025-001234">
                                            </div>
                                            @error('aurora_number')
                                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="case_description" class="block text-sm font-medium text-gray-700">Описание ситуации</label>
                                <div class="mt-1">
                                    <textarea name="case_description" id="case_description" rows="6" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Опишите подробно ситуацию с животным:&#10;- Где и когда обнаружено животное&#10;- Состояние животного&#10;- Обстоятельства случая&#10;- Дополнительная информация">{{ old('case_description') }}</textarea>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Чем подробнее описание, тем эффективнее будет оказана помощь</p>
                                @error('case_description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Блок 2: Информация о животном -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <span class="flex items-center justify-center h-6 w-6 rounded-full bg-indigo-600 text-white text-sm font-bold mr-3">2</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                        Информация о животном
                    </h3>
                </div>
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Левая колонка - основные характеристики -->
                        <div class="space-y-6">
                            <div class="flex items-start mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500 mr-2 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                                <div>
                                    <h5 class="text-sm font-medium text-gray-700 mb-1">Основные характеристики</h5>
                                    <p class="text-xs text-gray-500">Базовая информация для идентификации</p>
                                </div>
                            </div>

                            <div>
                                <label for="animal_type" class="block text-sm font-medium text-gray-700">Вид животного</label>
                                <div class="mt-1">
                                    <select name="animal_type" id="animal_type" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" required>
                                        <option value="cat" {{ old('animal_type') === 'cat' ? 'selected' : '' }}>Кошка</option>
                                        <option value="dog" {{ old('animal_type') === 'dog' ? 'selected' : '' }}>Собака</option>
                                        <option value="other" {{ old('animal_type') === 'other' ? 'selected' : '' }}>Другое</option>
                                    </select>
                                </div>
                                @error('animal_type')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div id="other_animal_type_block" style="{{ old('animal_type') === 'other' ? '' : 'display: none;' }}">
                                <label for="animal_type_other" class="block text-sm font-medium text-gray-700">Укажите вид животного</label>
                                <div class="mt-1">
                                    <input type="text" name="animal_type_other" id="animal_type_other" value="{{ old('animal_type_other') }}" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Например: хорек, енот...">
                                </div>
                                @error('animal_type_other')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="animal_gender" class="block text-sm font-medium text-gray-700">Пол животного</label>
                                <div class="mt-1">
                                    <select name="animal_gender" id="animal_gender" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" required>
                                        <option value="male" {{ old('animal_gender') === 'male' ? 'selected' : '' }}>Самец</option>
                                        <option value="female" {{ old('animal_gender') === 'female' ? 'selected' : '' }}>Самка</option>
                                        <option value="unknown" {{ old('animal_gender') === 'unknown' ? 'selected' : '' }}>Неизвестно</option>
                                    </select>
                                </div>
                                @error('animal_gender')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="animal_age" class="block text-sm font-medium text-gray-700">Примерный возраст</label>
                                <div class="mt-1">
                                    <input type="text" name="animal_age" id="animal_age" value="{{ old('animal_age') }}" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Например: молодое, взрослое...">
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Укажите приблизительный возраст</p>
                                @error('animal_age')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="animals_count" class="block text-sm font-medium text-gray-700">Количество животных</label>
                                <div class="mt-1">
                                    <input type="number" name="animals_count" id="animals_count" min="1" value="{{ old('animals_count', 1) }}" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Количество животных в ситуации</p>
                                @error('animals_count')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Средняя колонка - описание и особые условия -->
                        <div class="space-y-6">
                            <div class="flex items-start mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500 mr-2 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                    <h5 class="text-sm font-medium text-gray-700 mb-1">Описание и особенности</h5>
                                    <p class="text-xs text-gray-500">Детальная информация и особые условия</p>
                                </div>
                            </div>

                            <div>
                                <label for="animal_description" class="block text-sm font-medium text-gray-700">Описание животного</label>
                                <div class="mt-1">
                                    <textarea name="animal_description" id="animal_description" rows="4" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Опишите внешний вид:&#10;- Размер, окрас, приметы&#10;- Состояние, поведение">{{ old('animal_description') }}</textarea>
                                </div>
                                @error('animal_description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <fieldset>
                                    <legend class="block text-sm font-medium text-gray-700 mb-3">Особые условия</legend>
                                    <div class="space-y-3">
                                        <div class="relative flex items-start p-3 bg-red-50 border border-red-200 rounded-md">
                                            <div class="flex items-center h-5">
                                                <input id="has_bite" name="has_bite" type="checkbox" value="1" {{ old('has_bite') ? 'checked' : '' }} class="focus:ring-red-500 h-4 w-4 text-red-600 border-gray-300 rounded">
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <label for="has_bite" class="font-medium text-red-700">Был укус</label>
                                                <p class="text-red-600 text-xs mt-1">⚠️ Критичный случай - срок выезда 1 день</p>
                                            </div>
                                        </div>
                                        
                                        <div class="relative flex items-start p-3 bg-pink-50 border border-pink-200 rounded-md">
                                            <div class="flex items-center h-5">
                                                <input id="is_pregnant" name="is_pregnant" type="checkbox" value="1" {{ old('is_pregnant') ? 'checked' : '' }} class="focus:ring-pink-500 h-4 w-4 text-pink-600 border-gray-300 rounded">
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <label for="is_pregnant" class="font-medium text-pink-700">Беременность</label>
                                                <p class="text-pink-600 text-xs mt-1">🤱 Требует особого внимания</p>
                                            </div>
                                        </div>
                                        
                                        <div class="relative flex items-start p-3 bg-blue-50 border border-blue-200 rounded-md">
                                            <div class="flex items-center h-5">
                                                <input id="has_tags" name="has_tags" type="checkbox" value="1" {{ old('has_tags') ? 'checked' : '' }} class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <label for="has_tags" class="font-medium text-blue-700">Есть бирки</label>
                                            </div>
                                        </div>
                                        
                                        <!-- Поле описания бирок удалено -->
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                        
                        <!-- Правая колонка - медиа файлы -->
                        <div class="space-y-6">
                            <div class="flex items-start mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-500 mr-2 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <div>
                                    <h5 class="text-sm font-medium text-gray-700 mb-1">Медиа файлы</h5>
                                    <p class="text-xs text-gray-500">Фото и видео для идентификации</p>
                                </div>
                            </div>
                            
                            <!-- Фото и видео животного -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">📷 Фото/видео животного</label>
                                
                                <div class="animal-photos-drop-zone relative border-2 border-dashed border-purple-300 rounded-lg p-4 text-center hover:border-purple-400 transition-all duration-300 cursor-pointer bg-purple-50 hover:bg-purple-100">
                                    <input type="file" 
                                           name="animal_photos[]" 
                                           id="animal_photos" 
                                           multiple 
                                           accept=".jpg,.jpeg,.png,.mp4,.mov,.avi"
                                           class="hidden">
                                    
                                    <div class="animal-photos-drop-content">
                                        <div class="flex justify-center space-x-2 mb-2">
                                            <svg class="h-8 w-8 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <svg class="h-8 w-8 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <p class="text-sm font-medium text-purple-600 mb-1">Перетащите или выберите файлы</p>
                                        <p class="text-xs text-purple-500">Фото: JPG, PNG / Видео: MP4, MOV, AVI</p>
                                        <p class="text-xs text-purple-400">До 50MB</p>
                                    </div>
                                    
                                    <div class="animal-photos-loading hidden">
                                        <div class="flex items-center justify-center">
                                            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-purple-600"></div>
                                            <span class="ml-2 text-sm text-purple-600">Загрузка...</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div id="animal-photos-preview" class="mt-3 grid grid-cols-2 gap-2"></div>
                                @error('animal_photos.*')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Документы по укусу (появляются при выборе чекбокса) -->
                            <div id="bite_files_section" style="{{ old('has_bite') ? '' : 'display: none;' }}" class="border-t border-red-200 pt-4">
                                <div class="bg-red-25 border border-red-200 rounded-md p-3 mb-4">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z" />
                                        </svg>
                                        <h6 class="text-sm font-medium text-red-800">⚖️ Документы по укусу</h6>
                                    </div>
                                    <p class="text-xs text-red-700 mt-1">Приложите медицинские справки и фото/видео фиксацию</p>
                                </div>
                                
                                <!-- Медицинские справки -->
                                <div class="mb-4">
                                    <label class="block text-xs font-medium text-red-700 mb-2">📄 Медицинские справки</label>
                                    
                                    <div class="medical-drop-zone relative border-2 border-dashed border-red-300 rounded-lg p-3 text-center hover:border-red-400 transition-all duration-300 cursor-pointer bg-red-50 hover:bg-red-100">
                                        <input type="file" 
                                               name="bite_medical_files[]" 
                                               id="bite_medical_files" 
                                               multiple 
                                               accept=".pdf,.jpg,.jpeg,.png"
                                               class="hidden">
                                        
                                        <div class="medical-drop-content">
                                            <svg class="mx-auto h-6 w-6 text-red-400 mb-1" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <p class="text-xs font-medium text-red-600">PDF, JPG, PNG до 10MB</p>
                                        </div>
                                        
                                        <div class="medical-loading hidden">
                                            <div class="flex items-center justify-center">
                                                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-red-600"></div>
                                                <span class="ml-2 text-xs text-red-600">Загрузка...</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div id="medical-files-preview" class="mt-2 space-y-1"></div>
                                </div>
                                
                                <!-- Фото/видео фиксация укуса -->
                                <div>
                                    <label class="block text-xs font-medium text-red-700 mb-2">📸 Фото/видео фиксация</label>
                                    
                                    <div class="evidence-drop-zone relative border-2 border-dashed border-red-300 rounded-lg p-3 text-center hover:border-red-400 transition-all duration-300 cursor-pointer bg-red-50 hover:bg-red-100">
                                        <input type="file" 
                                               name="bite_evidence_files[]" 
                                               id="bite_evidence_files" 
                                               multiple 
                                               accept=".jpg,.jpeg,.png,.mp4,.mov,.avi"
                                               class="hidden">
                                        
                                        <div class="evidence-drop-content">
                                            <svg class="mx-auto h-6 w-6 text-red-400 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                            <p class="text-xs font-medium text-red-600">JPG, PNG, MP4, MOV, AVI до 50MB</p>
                                        </div>
                                        
                                        <div class="evidence-loading hidden">
                                            <div class="flex items-center justify-center">
                                                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-red-600"></div>
                                                <span class="ml-2 text-xs text-red-600">Загрузка...</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div id="evidence-files-preview" class="mt-2 grid grid-cols-2 gap-1"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Блок 3: Адрес/локация -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <span class="flex items-center justify-center h-6 w-6 rounded-full bg-indigo-600 text-white text-sm font-bold mr-3">3</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Адрес/локация
                    </h3>
                </div>
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Левая колонка - данные по адресу -->
                        <div class="space-y-6">
                            <div class="flex items-start mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500 mr-2 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <div>
                                    <h5 class="text-sm font-medium text-gray-700 mb-1">Информация об адресе</h5>
                                    <p class="text-xs text-gray-500">Укажите адрес и дополнительную информацию о местоположении</p>
                                </div>
                            </div>
                            
                            <div>
                                <label for="district" class="block text-sm font-medium text-gray-700">Район</label>
                                <div class="mt-1">
                                    <input type="text" name="district" id="district" value="{{ old('district') }}" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Определяется автоматически...">
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Заполняется автоматически при указании адреса или точки на карте</p>
                                @error('district')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Статус заявки <span class="text-red-500">*</span></label>
                                <div class="mt-1">
                                    <select name="status" id="status" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" required>
                                        <option value="">Выберите статус...</option>
                                        <option value="new" {{ old('status') == 'new' ? 'selected' : '' }}>Новая</option>
                                        <option value="processing" {{ old('status') == 'processing' ? 'selected' : '' }}>В обработке</option>
                                        <option value="capture_scheduled" {{ old('status') == 'capture_scheduled' ? 'selected' : '' }}>Запланирован отлов</option>
                                        <option value="captured" {{ old('status') == 'captured' ? 'selected' : '' }}>Отловлено</option>
                                        <option value="in_shelter" {{ old('status') == 'in_shelter' ? 'selected' : '' }}>В приюте</option>
                                        <option value="sterilized" {{ old('status') == 'sterilized' ? 'selected' : '' }}>Стерилизовано</option>
                                        <option value="vaccinated" {{ old('status') == 'vaccinated' ? 'selected' : '' }}>Вакцинировано</option>
                                        <option value="ready_for_return" {{ old('status') == 'ready_for_return' ? 'selected' : '' }}>Готово к возврату</option>
                                        <option value="returned" {{ old('status') == 'returned' ? 'selected' : '' }}>Возвращено</option>
                                        <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Завершено</option>
                                        <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Отменено</option>
                                    </select>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Выберите текущий статус заявки</p>
                                @error('status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700">Заметки администратора</label>
                                <div class="mt-1">
                                    <textarea name="notes" id="notes" rows="3" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Дополнительные заметки или комментарии...">{{ old('notes') }}</textarea>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Внутренние заметки, видимые только администраторам</p>
                                @error('notes')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="location_address" class="block text-sm font-medium text-gray-700">Адрес</label>
                                <div class="mt-1">
                                    <textarea name="location_address" id="location_address" rows="3" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" required placeholder="Введите полный адрес...">{{ old('location_address') }}</textarea>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">При вводе адреса на карте автоматически появится метка</p>
                                @error('location_address')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="location_landmark" class="block text-sm font-medium text-gray-700">Ориентир</label>
                                <div class="mt-1">
                                    <input type="text" name="location_landmark" id="location_landmark" value="{{ old('location_landmark') }}" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Укажите ближайшие объекты...">
                                </div>
                                @error('location_landmark')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Дополнительные адреса -->
                            <div class="pt-4 border-t border-gray-100">
                                <div class="flex items-center justify-between mb-3">
                                    <label class="block text-sm font-medium text-gray-700">Дополнительные адреса</label>
                                    <button type="button" id="add-address-btn" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        Добавить адрес
                                    </button>
                                </div>
                                <div id="additional-addresses-container" class="space-y-3">
                                    <!-- Дополнительные адреса будут добавляться здесь динамически -->
                                </div>
                                <p class="mt-2 text-xs text-gray-500">Если животные находятся в нескольких местах, добавьте дополнительные адреса. Все точки будут отображены на карте.</p>
                            </div>
                            
                            <!-- Координаты -->
                            <div class="pt-4 border-t border-gray-100">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="latitude" class="block text-xs font-medium text-gray-500">Широта</label>
                                        <input type="text" id="latitude" name="latitude" value="{{ old('latitude') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500" readonly>
                                    </div>
                                    <div>
                                        <label for="longitude" class="block text-xs font-medium text-gray-500">Долгота</label>
                                        <input type="text" id="longitude" name="longitude" value="{{ old('longitude') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Правая колонка - интерактивная карта -->
                        <div class="space-y-4">
                            <div class="flex items-start mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500 mr-2 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                </svg>
                                <div>
                                    <h5 class="text-sm font-medium text-gray-700 mb-1">Интерактивная карта</h5>
                                    <p class="text-xs text-gray-500">Кликните на карте для точного указания местоположения</p>
                                </div>
                            </div>
                            
                            <div id="map" class="w-full h-80 rounded-lg border border-gray-300 overflow-hidden shadow-sm"></div>
                            
                            <div class="text-xs text-gray-500 bg-gray-50 p-3 rounded-md">
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="font-medium">Подсказка:</span>
                                </div>
                                <ul class="mt-1 space-y-0.5">
                                    <li>• Введите адрес слева - метка появится на карте</li>
                                    <li>• Кликните по карте - адрес заполнится автоматически</li>
                                    <li>• <span class="font-medium text-indigo-600">Район определится автоматически</span> при любом из действий выше</li>
                                    <li>• Используйте поиск на карте для быстрого перехода</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Кнопки действий -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.osvv.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Отмена
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Создать заявку
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Подключение Яндекс Карт API -->
    <script src="https://api-maps.yandex.ru/2.1/?apikey=aba2bc56-907f-41a7-9377-d32e69eff205&lang=ru_RU" type="text/javascript"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const animalTypeSelect = document.getElementById('animal_type');
            const otherAnimalTypeBlock = document.getElementById('other_animal_type_block');
            const sourceTypeSelect = document.getElementById('source_type');
            const districtOfficeFields = document.getElementById('district_office_fields');
            const hasBiteCheckbox = document.getElementById('has_bite');
            const biteFilesSection = document.getElementById('bite_files_section');
            const statusSelect = document.getElementById('status');
            // Убираем обработчик для has_tags - больше не нужен
            
            // Устанавливаем статус "Новая" по умолчанию, если не выбран другой
            if (statusSelect && !statusSelect.value) {
                statusSelect.value = 'new';
            }
            
            // Обработчик для типа животного
            animalTypeSelect.addEventListener('change', function() {
                if (this.value === 'other') {
                    otherAnimalTypeBlock.style.display = '';
                } else {
                    otherAnimalTypeBlock.style.display = 'none';
                }
            });
            
            // Обработчик для типа источника
            if (sourceTypeSelect && districtOfficeFields) {
                sourceTypeSelect.addEventListener('change', function() {
                    if (this.value === 'district_office') {
                        districtOfficeFields.style.display = '';
                    } else {
                        districtOfficeFields.style.display = 'none';
                    }
                });
            }
            
            // Обработчик для чекбокса "Был укус"
            if (hasBiteCheckbox && biteFilesSection) {
                hasBiteCheckbox.addEventListener('change', function() {
                    if (this.checked) {
                        biteFilesSection.style.display = '';
                        // Добавляем плавную анимацию появления
                        biteFilesSection.style.opacity = '0';
                        biteFilesSection.style.transform = 'translateY(-10px)';
                        setTimeout(() => {
                            biteFilesSection.style.transition = 'all 0.3s ease';
                            biteFilesSection.style.opacity = '1';
                            biteFilesSection.style.transform = 'translateY(0)';
                        }, 10);
                    } else {
                        biteFilesSection.style.transition = 'all 0.3s ease';
                        biteFilesSection.style.opacity = '0';
                        biteFilesSection.style.transform = 'translateY(-10px)';
                        setTimeout(() => {
                            biteFilesSection.style.display = 'none';
                        }, 300);
                    }
                });
            }
            
            // Обработчик для чекбокса "Есть бирки"
            // Убираем обработчик для has_tags - больше не нужен
            
            // Управление дополнительными адресами
            let additionalAddressIndex = 0;
            const addAddressBtn = document.getElementById('add-address-btn');
            const additionalAddressesContainer = document.getElementById('additional-addresses-container');
            
            // Глобальные переменные для карты (будут инициализированы в ymaps.ready)
            let myMap = null;
            let myPlacemark = null;
            let additionalPlacemarks = [];
            let directGeocode = null;
            
            // Функция добавления нового адреса
            function addAdditionalAddress() {
                const addressHtml = `
                    <div class="additional-address-item border border-gray-200 rounded-lg p-4 bg-gray-50" data-index="${additionalAddressIndex}">
                        <div class="flex items-center justify-between mb-3">
                            <h6 class="text-sm font-medium text-gray-700">Адрес ${additionalAddressIndex + 2}</h6>
                            <button type="button" class="remove-address-btn text-red-600 hover:text-red-800 transition-colors" data-index="${additionalAddressIndex}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Адрес</label>
                                <textarea name="additional_addresses[${additionalAddressIndex}][address]" rows="2" class="additional-address-input w-full text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" placeholder="Введите дополнительный адрес..."></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Ориентир</label>
                                <input type="text" name="additional_addresses[${additionalAddressIndex}][landmark]" class="w-full text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" placeholder="Ориентир...">
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Широта</label>
                                    <input type="text" name="additional_addresses[${additionalAddressIndex}][latitude]" class="additional-latitude w-full text-xs border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" readonly>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Долгота</label>
                                    <input type="text" name="additional_addresses[${additionalAddressIndex}][longitude]" class="additional-longitude w-full text-xs border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                additionalAddressesContainer.insertAdjacentHTML('beforeend', addressHtml);
                additionalAddressIndex++;
                
                // Добавляем обработчики для нового адреса
                setupAddressHandlers();
            }
            
            // Функция удаления адреса
            function removeAdditionalAddress(index) {
                const addressItem = document.querySelector(`[data-index="${index}"]`);
                if (addressItem) {
                    addressItem.remove();
                    updateAddressNumbers();
                    updateMapPlacemarks();
                }
            }
            
            // Функция обновления номеров адресов
            function updateAddressNumbers() {
                const addressItems = document.querySelectorAll('.additional-address-item');
                addressItems.forEach((item, index) => {
                    const title = item.querySelector('h6');
                    if (title) {
                        title.textContent = `Адрес ${index + 2}`; // +2 потому что основной адрес - это адрес 1
                    }
                });
            }
            
            // Обработчик кнопки добавления адреса
            addAddressBtn.addEventListener('click', addAdditionalAddress);
            
            // Обработчик удаления адресов (делегирование событий)
            additionalAddressesContainer.addEventListener('click', function(e) {
                if (e.target.closest('.remove-address-btn')) {
                    const index = e.target.closest('.remove-address-btn').dataset.index;
                    removeAdditionalAddress(index);
                }
            });
            
            // Инициализация Яндекс карты
            ymaps.ready(function () {
                myMap = new ymaps.Map('map', {
                    center: [51.6720, 39.1843], // Воронеж по умолчанию
                    zoom: 10,
                    controls: ['zoomControl', 'searchControl', 'typeSelector', 'fullscreenControl']
                });
                
                // Инициализируем глобальные переменные
                myPlacemark = null;
                additionalPlacemarks = [];
                var isUpdatingFromMap = false; // Флаг для предотвращения зацикливания
                
                // Функция извлечения района из результата геокодирования
                function extractDistrict(geoObject) {
                    try {
                        console.log('Получен geoObject для анализа');
                        
                        // Получаем полный адрес
                        var fullAddress = '';
                        if (geoObject.getAddressLine) {
                            fullAddress = geoObject.getAddressLine();
                        } else if (geoObject.addressLine) {
                            fullAddress = geoObject.addressLine;
                        }
                        
                        console.log('Полный адрес:', fullAddress);
                        
                        if (!fullAddress) {
                            console.log('Не удалось получить адрес из geoObject');
                            return null;
                        }
                        
                        var foundDistrict = null;
                        
                        // Сначала попробуем найти район прямо в адресе
                        var addressParts = fullAddress.split(',');
                        for (var j = 0; j < addressParts.length; j++) {
                            var part = addressParts[j].trim().toLowerCase();
                            if (part.includes('район') || 
                                part.includes('округ') ||
                                part.includes('р-н') ||
                                part.includes('р-он')) {
                                foundDistrict = addressParts[j].trim();
                                console.log('Найден район в адресе:', foundDistrict);
                                break;
                            }
                        }
                        
                        // Если не нашли и это Воронеж - используем справочник улиц
                        if (!foundDistrict && fullAddress.toLowerCase().includes('воронеж')) {
                            console.log('Район не найден в адресе, используем справочник улиц');
                            foundDistrict = getVoronezhDistrictByStreet(fullAddress);
                            if (foundDistrict) {
                                console.log('Район определен по справочнику улиц:', foundDistrict);
                            }
                        }
                        
                        // Очищаем название района от лишних слов
                        if (foundDistrict) {
                            foundDistrict = foundDistrict
                                .replace(/район$/i, 'район')
                                .replace(/округ$/i, 'округ')
                                .replace(/\s+/g, ' ')
                                .trim();
                        }
                        
                        console.log('Итоговый район:', foundDistrict);
                        return foundDistrict;
                        
                    } catch (e) {
                        console.log('Ошибка при извлечении района:', e);
                        return null;
                    }
                }
                
                // Функция определения района Воронежа по названию улицы
                function getVoronezhDistrictByStreet(address) {
                    var streetName = '';
                    var addressLower = address.toLowerCase();
                    
                    console.log('Анализируем адрес для определения района:', address);
                    
                    // Извлекаем название улицы - улучшенные паттерны
                    var streetPatterns = [
                        /улица\s+([а-яё\s\-]+)/i,
                        /ул\.\s*([а-яё\s\-]+)/i,
                        /([а-яё\s\-]+)\s+улица/i,
                        /([а-яё\s\-]+ская)\s*[\d,]/i,
                        /([а-яё\s\-]+ский)\s*[\d,]/i,
                        /([а-яё\s\-]+)\s*[\d]/i,
                        /([а-яё\s\-]+),/i
                    ];
                    
                    for (var i = 0; i < streetPatterns.length; i++) {
                        var pattern = streetPatterns[i];
                        var match = address.match(pattern);
                        if (match && match[1]) {
                            streetName = match[1].trim().toLowerCase();
                            // Убираем лишние слова
                            streetName = streetName.replace(/\s+/g, ' ').trim();
                            if (streetName.length > 2) { // Минимум 3 символа
                                break;
                            }
                        }
                    }
                    
                    console.log('Извлеченное название улицы:', streetName);
                    
                    if (!streetName) {
                        return null;
                    }
                    
                    // Справочник улиц Воронежа по районам
                    var voronezhStreets = {
                        'Центральный район': [
                            'плехановская', 'кольцовская', 'карла маркса', 'революции 1905 года',
                            'фридриха энгельса', 'кирова', 'комиссаржевской', 'никитинская',
                            'театральная', 'пушкинская', 'орджоникидзе', 'ленина', 'мира'
                        ],
                        'Коминтерновский район': [
                            '9 января', 'московский проспект', 'кольцовская',
                            'дружинников', 'героев стратосферы', 'генерала лизюкова',
                            'владимира невского', 'донбасская', 'шишкова'
                        ],
                        'Советский район': [
                            'ленинский проспект', 'куйбышева', 'землячки', 'мордасовой',
                            'южно-моравская', 'хользунова', 'антонова-овсеенко', 'беговая',
                            'остужева', 'ломоносова', 'студенческая'
                        ],
                        'Железнодорожный район': [
                            'вокзальная', 'урицкого', 'ворошилова', 'двенадцатого апреля',
                            'железнодорожная', '20-летия влксм', 'машиностроителей',
                            'текстильщиков', 'электросигнальная'
                        ],
                        'Левобережный район': [
                            'балашовская', 'ленинградская', 'ворошилова', 'героев сибиряков', 'шишкова',
                            'космонавтов', 'переверткина', 'матросова', 'лебедева',
                            'минская', 'ростовская', 'брусилова', 'беговая'
                        ],
                        'Ленинский район': [
                            'патриотов', 'героев труда', 'краснознаменная', 'бульвар победы',
                            'новосибирская', 'заболотного', 'монтажный проезд', 'ростовская'
                        ]
                    };
                    
                    // Ищем улицу в справочнике - улучшенный поиск
                    for (var district in voronezhStreets) {
                        var streets = voronezhStreets[district];
                        for (var j = 0; j < streets.length; j++) {
                            var street = streets[j].toLowerCase();
                            
                            // Проверяем точное совпадение или вхождение
                            if (streetName === street || 
                                streetName.includes(street) || 
                                street.includes(streetName)) {
                                console.log('Найдено совпадение:', streetName, '→', street, '→', district);
                                return district;
                            }
                        }
                    }
                    
                    console.log('Улица не найдена в справочнике:', streetName);
                    return null;
                }
                
                // Функция обновления поля района
                function updateDistrictField(geoObject) {
                    var district = extractDistrict(geoObject);
                    var districtInput = document.getElementById('district');
                    
                    console.log('Найденный район для заполнения:', district);
                    
                    if (district && districtInput) {
                        // Заполняем поле района только если оно пустое или если новый район отличается
                        if (!districtInput.value.trim() || districtInput.value.trim() !== district) {
                            districtInput.value = district;
                            
                            // Добавляем визуальную подсказку
                            districtInput.style.backgroundColor = '#f0f9ff';
                            districtInput.style.borderColor = '#3b82f6';
                            districtInput.style.transition = 'all 0.3s ease';
                            
                            console.log('Район заполнен:', district);
                            
                            setTimeout(function() {
                                districtInput.style.backgroundColor = '';
                                districtInput.style.borderColor = '';
                                districtInput.style.transition = '';
                            }, 2000);
                        }
                    } else {
                        console.log('Район не найден или поле района недоступно');
                    }
                }
                
                // Функция обратного геокодирования (координаты -> адрес)
                function reverseGeocode(coords) {
                    return ymaps.geocode(coords, {
                        results: 1
                    }).then(function (res) {
                        var firstGeoObject = res.geoObjects.get(0);
                        if (firstGeoObject) {
                            var address = firstGeoObject.getAddressLine();
                            
                            // Обновляем район в отдельное поле
                            updateDistrictField(firstGeoObject);
                            
                            return address;
                        }
                        return null;
                    });
                }
                
                // Функция прямого геокодирования (адрес -> координаты)
                directGeocode = function(address) {
                    return ymaps.geocode(address, {
                        results: 1
                    }).then(function (res) {
                        var firstGeoObject = res.geoObjects.get(0);
                        if (firstGeoObject) {
                            // Обновляем район в отдельное поле
                            updateDistrictField(firstGeoObject);
                            
                            return firstGeoObject.geometry.getCoordinates();
                        }
                        return null;
                    });
                };
                
                // Функция обновления метки на карте
                function updatePlacemark(coords, hintText, balloonText, color = '#DC2626') {
                    // Удаляем предыдущую метку
                    if (myPlacemark) {
                        myMap.geoObjects.remove(myPlacemark);
                    }
                    
                    // Создаем новую метку
                    var iconHref = color === '#3463F0' 
                        ? 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTEyIDJDOC4xMzQwMSAyIDUgNS4xMzQwMSA1IDlDNSAxNC4yNSAxMiAyMiAxMiAyMkMxMiAyMiAxOSAxNC4yNSAxOSA5QzE5IDUuMTM0MDEgMTUuODY2IDIgMTIgMloiIGZpbGw9IiMzNDYzRjAiIHN0cm9rZT0iI0ZGRkZGRiIgc3Ryb2tlLXdpZHRoPSIyIi8+CjxjaXJjbGUgY3g9IjEyIiBjeT0iOSIgcj0iMyIgZmlsbD0iI0ZGRkZGRiIvPgo8L3N2Zz4K'
                        : 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTEyIDJDOC4xMzQwMSAyIDUgNS4xMzQwMSA1IDlDNSAxNC4yNSAxMiAyMiAxMiAyMkMxMiAyMiAxOSAxNC4yNSAxOSA5QzE5IDUuMTM0MDEgMTUuODY2IDIgMTIgMloiIGZpbGw9IiNEQzI2MjYiIHN0cm9rZT0iI0ZGRkZGRiIgc3Ryb2tlLXdpZHRoPSIyIi8+CjxjaXJjbGUgY3g9IjEyIiBjeT0iOSIgcj0iMyIgZmlsbD0iI0ZGRkZGRiIvPgo8L3N2Zz4K';
                    
                    myPlacemark = new ymaps.Placemark(coords, {
                        hintContent: hintText,
                        balloonContent: balloonText
                    }, {
                        iconLayout: 'default#image',
                        iconImageHref: iconHref,
                        iconImageSize: [24, 24],
                        iconImageOffset: [-12, -24]
                    });
                    
                    myMap.geoObjects.add(myPlacemark);
                    
                    // Заполняем поля координат
                    document.getElementById('latitude').value = coords[0].toPrecision(8);
                    document.getElementById('longitude').value = coords[1].toPrecision(8);
                }
                
                // Обработчик клика по карте
                myMap.events.add('click', function (e) {
                    var coords = e.get('coords');
                    isUpdatingFromMap = true;
                    
                    // Обновляем метку на карте
                    updatePlacemark(coords, 'Выбранное местоположение', 
                        'Координаты: ' + coords[0].toPrecision(6) + ', ' + coords[1].toPrecision(6));
                    
                    // Получаем адрес по координатам и заполняем поле
                    reverseGeocode(coords).then(function(address) {
                        if (address) {
                            document.getElementById('location_address').value = address;
                            
                            // Обновляем balloon с адресом
                            myPlacemark.properties.set('balloonContent', 
                                '<strong>Выбранное местоположение</strong><br>' + 
                                address + '<br><br>' +
                                '<small>Координаты: ' + coords[0].toPrecision(6) + ', ' + coords[1].toPrecision(6) + '</small>');
                        }
                        
                        setTimeout(function() {
                            isUpdatingFromMap = false;
                        }, 500);
                    });
                });
                
                // Обработчик изменения адреса (с задержкой для избежания частых запросов)
                var addressInput = document.getElementById('location_address');
                var searchTimeout;
                
                if (addressInput) {
                    // Обработчик ввода с задержкой
                    addressInput.addEventListener('input', function() {
                        if (isUpdatingFromMap) return; // Игнорируем изменения от карты
                        
                        clearTimeout(searchTimeout);
                        var address = this.value.trim();
                        
                        console.log('Введен адрес:', address);
                        
                        if (address.length > 5) { // Начинаем поиск после 5 символов
                            searchTimeout = setTimeout(function() {
                                console.log('Выполняем геокодирование для:', address);
                                directGeocode(address).then(function(coords) {
                                    if (coords) {
                                        myMap.setCenter(coords, 15);
                                        updatePlacemark(coords, 'Найденный адрес', 
                                            '<strong>Найденный адрес</strong><br>' + address, '#3463F0');
                                    }
                                }).catch(function(error) {
                                    console.log('Ошибка геокодирования:', error);
                                });
                            }, 1000); // Задержка 1 секунда для избежания частых запросов
                        }
                    });
                    
                    // Обработчик потери фокуса (для более точного поиска)
                    addressInput.addEventListener('blur', function() {
                        if (isUpdatingFromMap) return;
                        
                        var address = this.value.trim();
                        console.log('Поле адреса потеряло фокус, адрес:', address);
                        
                        if (address && address.length > 3) {
                            console.log('Выполняем финальное геокодирование для:', address);
                            directGeocode(address).then(function(coords) {
                                if (coords) {
                                    myMap.setCenter(coords, 16);
                                    updatePlacemark(coords, 'Найденный адрес', 
                                        '<strong>Найденный адрес</strong><br>' + address + '<br><br>' +
                                        '<small>Координаты: ' + coords[0].toPrecision(6) + ', ' + coords[1].toPrecision(6) + '</small>', '#3463F0');
                                }
                            }).catch(function(error) {
                                console.log('Ошибка финального геокодирования:', error);
                            });
                        }
                    });
                }
                
                // Если есть сохраненные координаты, показываем их на карте
                var savedLat = document.getElementById('latitude').value;
                var savedLng = document.getElementById('longitude').value;
                
                if (savedLat && savedLng) {
                    var coords = [parseFloat(savedLat), parseFloat(savedLng)];
                    myMap.setCenter(coords, 15);
                    
                    updatePlacemark(coords, 'Сохраненное местоположение', 
                        'Координаты: ' + savedLat + ', ' + savedLng);
                    
                    // Если нет адреса, пытаемся получить его по координатам
                    if (!addressInput.value.trim()) {
                        reverseGeocode(coords).then(function(address) {
                            if (address) {
                                addressInput.value = address;
                            }
                        });
                    }
                }
                
                // Функция настройки обработчиков для дополнительных адресов
                function setupAddressHandlers() {
                    const additionalInputs = document.querySelectorAll('.additional-address-input');
                    
                    additionalInputs.forEach(function(input) {
                        // Получаем data-index родительского элемента
                        const addressItem = input.closest('.additional-address-item');
                        const dataIndex = addressItem ? addressItem.getAttribute('data-index') : null;
                        
                        if (!dataIndex) return;
                        
                        // Удаляем предыдущие обработчики, если они есть
                        input.removeEventListener('input', input._inputHandler);
                        input.removeEventListener('blur', input._blurHandler);
                        
                        // Создаем новые обработчики
                        input._inputHandler = function() {
                            clearTimeout(input._searchTimeout);
                            var address = this.value.trim();
                            
                            if (address.length > 5) {
                                input._searchTimeout = setTimeout(function() {
                                    geocodeAdditionalAddress(address, dataIndex);
                                }, 1000);
                            }
                        };
                        
                        input._blurHandler = function() {
                            var address = this.value.trim();
                            if (address && address.length > 3) {
                                geocodeAdditionalAddress(address, dataIndex);
                            }
                        };
                        
                        // Добавляем обработчики
                        input.addEventListener('input', input._inputHandler);
                        input.addEventListener('blur', input._blurHandler);
                    });
                }
                
                // Функция геокодирования дополнительного адреса
                function geocodeAdditionalAddress(address, index) {
                    if (!directGeocode) {
                        console.log('Карта еще не инициализирована');
                        return;
                    }
                    
                    directGeocode(address).then(function(coords) {
                        if (coords) {
                            // Находим соответствующие поля координат
                            const addressItem = document.querySelector(`[data-index="${index}"]`);
                            if (addressItem) {
                                const latInput = addressItem.querySelector('.additional-latitude');
                                const lngInput = addressItem.querySelector('.additional-longitude');
                                
                                if (latInput && lngInput) {
                                    latInput.value = coords[0].toPrecision(8);
                                    lngInput.value = coords[1].toPrecision(8);
                                    
                                    console.log(`Координаты для адреса ${index}:`, coords);
                                }
                            }
                            
                            // Обновляем метки на карте
                            updateMapPlacemarks();
                        }
                    }).catch(function(error) {
                        console.log('Ошибка геокодирования дополнительного адреса:', error);
                    });
                }
                
                // Функция обновления всех меток на карте
                function updateMapPlacemarks() {
                    if (!myMap) {
                        console.log('Карта еще не инициализирована');
                        return;
                    }
                    
                    // Очищаем все дополнительные метки
                    additionalPlacemarks.forEach(function(placemark) {
                        myMap.geoObjects.remove(placemark);
                    });
                    additionalPlacemarks = [];
                    
                    // Добавляем метки для всех дополнительных адресов
                    const addressItems = document.querySelectorAll('.additional-address-item');
                    
                    console.log(`Найдено ${addressItems.length} дополнительных адресов`);
                    
                    addressItems.forEach(function(item, displayIndex) {
                        const latInput = item.querySelector('.additional-latitude');
                        const lngInput = item.querySelector('.additional-longitude');
                        const addressInput = item.querySelector('.additional-address-input');
                        
                        console.log(`Проверяем адрес ${displayIndex + 2}:`, {
                            lat: latInput ? latInput.value : 'нет',
                            lng: lngInput ? lngInput.value : 'нет',
                            address: addressInput ? addressInput.value : 'нет'
                        });
                        
                        if (latInput && lngInput && latInput.value && lngInput.value) {
                            const coords = [parseFloat(latInput.value), parseFloat(lngInput.value)];
                            const address = addressInput ? addressInput.value : `Адрес ${displayIndex + 2}`; // +2 потому что основной адрес - это адрес 1
                            
                            console.log(`Создаем метку для адреса ${displayIndex + 2}:`, coords, address);
                            
                            // Создаем метку для дополнительного адреса
                            const placemark = new ymaps.Placemark(coords, {
                                hintContent: `Дополнительный адрес ${displayIndex + 2}`,
                                balloonContent: `<strong>Дополнительный адрес ${displayIndex + 2}</strong><br>${address}<br><br><small>Координаты: ${coords[0].toPrecision(6)}, ${coords[1].toPrecision(6)}</small>`
                            }, {
                                iconLayout: 'default#image',
                                iconImageHref: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTEyIDJDOC4xMzQwMSAyIDUgNS4xMzQwMSA1IDlDNSAxNC4yNSAxMiAyMiAxMiAyMkMxMiAyMiAxOSAxNC4yNSAxOSA5QzE5IDUuMTM0MDEgMTUuODY2IDIgMTIgMloiIGZpbGw9IiM5MzMzRUEiIHN0cm9rZT0iI0ZGRkZGRiIgc3Ryb2tlLXdpZHRoPSIyIi8+CjxjaXJjbGUgY3g9IjEyIiBjeT0iOSIgcj0iMyIgZmlsbD0iI0ZGRkZGRiIvPgo8L3N2Zz4K', // Фиолетовая метка
                                iconImageSize: [24, 24],
                                iconImageOffset: [-12, -24]
                            });
                            
                            myMap.geoObjects.add(placemark);
                            additionalPlacemarks.push(placemark);
                            
                            console.log(`Метка добавлена для адреса ${displayIndex + 2}`);
                        }
                    });
                    
                    console.log(`Всего меток на карте: ${additionalPlacemarks.length}`);
                }
                
                // Делаем функции доступными глобально для использования в других частях кода
                window.setupAddressHandlers = setupAddressHandlers;
                window.updateMapPlacemarks = updateMapPlacemarks;
                window.geocodeAdditionalAddress = geocodeAdditionalAddress;
            });
            
            // Инициализация Drag & Drop для файлов укуса
            initializeFileUpload();
            
            function initializeFileUpload() {
                // Инициализация для медицинских файлов
                initializeDropZone('.medical-drop-zone', '#bite_medical_files', '#medical-files-preview', 'medical');
                
                // Инициализация для фото/видео файлов
                initializeDropZone('.evidence-drop-zone', '#bite_evidence_files', '#evidence-files-preview', 'evidence');
                
                // Инициализация для фотографий животного
                initializeDropZone('.animal-photos-drop-zone', '#animal_photos', '#animal-photos-preview', 'animal_photos');
            }
            
            function initializeDropZone(dropZoneSelector, inputSelector, previewSelector, type) {
                const dropZone = document.querySelector(dropZoneSelector);
                const fileInput = document.querySelector(inputSelector);
                const previewContainer = document.querySelector(previewSelector);
                
                if (!dropZone || !fileInput || !previewContainer) return;
                
                // Клик по зоне открывает диалог выбора файлов
                dropZone.addEventListener('click', () => {
                    fileInput.click();
                });
                
                // Обработчики drag & drop
                dropZone.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    dropZone.classList.add('dragover');
                });
                
                dropZone.addEventListener('dragleave', (e) => {
                    e.preventDefault();
                    if (!dropZone.contains(e.relatedTarget)) {
                        dropZone.classList.remove('dragover');
                    }
                });
                
                dropZone.addEventListener('drop', (e) => {
                    e.preventDefault();
                    dropZone.classList.remove('dragover');
                    
                    const files = Array.from(e.dataTransfer.files);
                    handleFiles(files, fileInput, previewContainer, type);
                });
                
                // Обработчик выбора файлов через диалог
                fileInput.addEventListener('change', (e) => {
                    const files = Array.from(e.target.files);
                    handleFiles(files, fileInput, previewContainer, type);
                });
            }
            
            function handleFiles(files, fileInput, previewContainer, type) {
                // Валидация файлов
                const validFiles = validateFiles(files, type);
                
                if (validFiles.length === 0) return;
                
                // Создаем новый DataTransfer для обновления input
                const dt = new DataTransfer();
                
                // Добавляем существующие файлы
                if (fileInput.files) {
                    Array.from(fileInput.files).forEach(file => dt.items.add(file));
                }
                
                // Добавляем новые файлы
                validFiles.forEach(file => dt.items.add(file));
                fileInput.files = dt.files;
                
                // Обновляем превью
                updatePreview(validFiles, previewContainer, type);
            }
            
            function validateFiles(files, type) {
                const validFiles = [];
                let maxSize, allowedTypes;
                
                if (type === 'medical') {
                    maxSize = 10 * 1024 * 1024; // 10MB
                    allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
                } else if (type === 'evidence') {
                    maxSize = 50 * 1024 * 1024; // 50MB
                    allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'video/mp4', 'video/quicktime', 'video/x-msvideo'];
                } else if (type === 'animal_photos') {
                    maxSize = 50 * 1024 * 1024; // 50MB
                    allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'video/mp4', 'video/quicktime', 'video/x-msvideo'];
                }
                
                files.forEach(file => {
                    if (file.size > maxSize) {
                        alert(`Файл "${file.name}" слишком большой. Максимальный размер: ${maxSize / 1024 / 1024}MB`);
                        return;
                    }
                    
                    if (!allowedTypes.includes(file.type)) {
                        alert(`Файл "${file.name}" имеет неподдерживаемый формат.`);
                        return;
                    }
                    
                    validFiles.push(file);
                });
                
                return validFiles;
            }
            
            function updatePreview(files, previewContainer, type) {
                files.forEach(file => {
                    const previewElement = createPreviewElement(file, type);
                    previewElement.classList.add('file-preview-enter');
                    previewContainer.appendChild(previewElement);
                });
            }
            
            function createPreviewElement(file, type) {
                const div = document.createElement('div');
                
                if (type === 'medical') {
                    div.className = 'flex items-center p-3 bg-white border border-red-200 rounded-lg shadow-sm';
                } else if (type === 'evidence' || type === 'animal_photos') {
                    div.className = 'relative group bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm';
                    if (type === 'animal_photos') {
                        div.className = 'relative group bg-white border border-indigo-200 rounded-lg overflow-hidden shadow-sm';
                    }
                }
                
                if (type === 'medical') {
                    div.innerHTML = `
                        <div class="flex-shrink-0">
                            ${getFileIcon(file)}
                        </div>
                        <div class="ml-3 flex-1 min-w-0">
                            <p class="text-xs font-medium text-gray-900 truncate">${file.name}</p>
                            <p class="text-xs text-gray-500">${formatFileSize(file.size)}</p>
                        </div>
                        <button type="button" class="ml-3 flex-shrink-0 text-red-400 hover:text-red-600" onclick="removeFile(this, '${file.name}')">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    `;
                } else {
                    // Для фото/видео и фотографий животного создаем превью с миниатюрой
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            const borderColor = type === 'animal_photos' ? 'purple' : 'red';
                            div.innerHTML = `
                                <img src="${e.target.result}" class="w-full h-20 object-cover">
                                <div class="absolute inset-0 bg-black bg-opacity-40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <button type="button" class="text-white hover:text-${borderColor}-300" onclick="removeFile(this, '${file.name}')">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-60 text-white p-1">
                                    <p class="text-xs truncate">${file.name}</p>
                                </div>
                            `;
                        };
                        reader.readAsDataURL(file);
                    } else {
                        // Для видео показываем иконку
                        const borderColor = type === 'animal_photos' ? 'purple' : 'red';
                        div.innerHTML = `
                            <div class="w-full h-20 bg-gray-100 flex items-center justify-center">
                                <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="absolute inset-0 bg-black bg-opacity-40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <button type="button" class="text-white hover:text-${borderColor}-300" onclick="removeFile(this, '${file.name}')">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-60 text-white p-1">
                                <p class="text-xs truncate">${file.name}</p>
                                <p class="text-xs opacity-75">${formatFileSize(file.size)}</p>
                            </div>
                        `;
                    }
                }
                
                return div;
            }
            
            function getFileIcon(file) {
                if (file.type === 'application/pdf') {
                    return `<svg class="h-8 w-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>`;
                } else {
                    return `<svg class="h-8 w-8 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>`;
                }
            }
            
            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }
            
            // Глобальная функция для удаления файлов
            window.removeFile = function(button, fileName) {
                // Находим родительский элемент превью
                const previewElement = button.closest('.flex, .relative');
                if (previewElement) {
                    previewElement.remove();
                }
                
                // Обновляем input файлы (удаляем файл из списка)
                // Это упрощенная версия - в реальности нужно более сложная логика
                console.log('Удаление файла:', fileName);
            };
            
            // Функция проверки обязательных полей
            function validateRequiredFields() {
                const requiredFields = [
                    { id: 'contact_name', name: 'ФИО заявителя' },
                    { id: 'contact_phone', name: 'Телефон' },
                    { id: 'animal_type', name: 'Тип животного' },
                    { id: 'animal_gender', name: 'Пол животного' },
                    { id: 'location_address', name: 'Адрес' },
                    { id: 'status', name: 'Статус заявки' }
                ];
                
                let hasErrors = false;
                const errors = [];
                
                requiredFields.forEach(field => {
                    const element = document.getElementById(field.id);
                    if (element && !element.value.trim()) {
                        hasErrors = true;
                        errors.push(field.name);
                        element.classList.add('border-red-500', 'ring-red-500');
                        element.classList.remove('border-gray-300');
                    } else if (element) {
                        element.classList.remove('border-red-500', 'ring-red-500');
                        element.classList.add('border-gray-300');
                    }
                });
                
                return { hasErrors, errors };
            }
            
            // Обработчик отправки формы
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const validation = validateRequiredFields();
                    
                    if (validation.hasErrors) {
                        e.preventDefault();
                        
                        // Показываем уведомление об ошибках
                        const errorMessage = `Пожалуйста, заполните обязательные поля: ${validation.errors.join(', ')}`;
                        
                        // Создаем временное уведомление
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'fixed top-4 right-4 bg-red-50 border border-red-200 rounded-lg p-4 shadow-lg z-50';
                        alertDiv.innerHTML = `
                            <div class="flex items-start">
                                <svg class="h-5 w-5 text-red-600 mr-3 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                                <div class="flex-1">
                                    <h4 class="text-sm font-semibold text-red-800 mb-1">Ошибка валидации</h4>
                                    <p class="text-sm text-red-700">${errorMessage}</p>
                                </div>
                                <button type="button" onclick="this.parentElement.parentElement.remove()" class="ml-3 text-red-600 hover:text-red-800">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        `;
                        
                        document.body.appendChild(alertDiv);
                        
                        // Автоматически скрываем через 5 секунд
                        setTimeout(() => {
                            if (alertDiv.parentElement) {
                                alertDiv.remove();
                            }
                        }, 5000);
                        
                        // Прокручиваем к первому полю с ошибкой
                        const firstErrorField = document.querySelector('.border-red-500');
                        if (firstErrorField) {
                            firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            firstErrorField.focus();
                        }
                    }
                });
            }
        });
        
        // Умная маска для телефона с поддержкой мобильных и городских номеров
        const contactPhoneInput = document.getElementById('contact_phone');
        if (contactPhoneInput) {
            
            // Функция для определения типа номера
            function getPhoneType(digits) {
                if (digits.length < 4) return 'unknown';
                if (digits.startsWith('7473')) return 'city';
                if (digits.startsWith('79')) return 'mobile';
                return 'invalid';
            }
            
            // Функция форматирования номера
            function formatPhone(digits) {
                if (!digits) return '+7';
                
                // Убираем все нецифровые символы и ограничиваем до 11 цифр
                digits = digits.replace(/\D/g, '');
                
                // Если номер начинается с 8, заменяем на 7
                if (digits.startsWith('8')) {
                    digits = '7' + digits.substring(1);
                }
                // Если номер не начинается с 7, добавляем 7
                else if (!digits.startsWith('7') && digits.length > 0) {
                    digits = '7' + digits;
                }
                
                // Ограничиваем до 11 цифр
                if (digits.length > 11) {
                    digits = digits.substring(0, 11);
                }
                
                const phoneType = getPhoneType(digits);
                let formatted = '+7';
                
                if (digits.length > 1) {
                    const areaCode = digits.substring(1, 4);
                    const number = digits.substring(4);
                    
                    if (phoneType === 'city' && digits.startsWith('7473')) {
                        // Городской номер Воронежа: +7 (473) XXX-XX-XX
                        formatted += ' (473';
                        if (digits.length > 4) {
                            formatted += ') ' + number.substring(0, 3);
                            if (number.length > 3) {
                                formatted += '-' + number.substring(3, 5);
                                if (number.length > 5) {
                                    formatted += '-' + number.substring(5, 7);
                                }
                            }
                        } else {
                            formatted += ')';
                        }
                    } else if (phoneType === 'mobile' || (digits.length > 1 && digits[1] === '9')) {
                        // Мобильный номер: +7 (9XX) XXX-XX-XX
                        formatted += ' (' + areaCode;
                        if (digits.length > 4) {
                            formatted += ') ' + number.substring(0, 3);
                            if (number.length > 3) {
                                formatted += '-' + number.substring(3, 5);
                                if (number.length > 5) {
                                    formatted += '-' + number.substring(5, 7);
                                }
                            }
                        } else if (digits.length === 4) {
                            formatted += ')';
                        }
                    } else {
                        // Неопределенный формат - просто добавляем цифры
                        formatted += ' (' + areaCode;
                        if (digits.length === 4) {
                            formatted += ')';
                        } else if (digits.length > 4) {
                            formatted += ') ' + number;
                        }
                    }
                }
                
                return formatted;
            }
            
            // Функция валидации номера
            function validatePhone(value) {
                const digits = value.replace(/\D/g, '');
                
                if (digits.length < 11) {
                    return { valid: false, message: 'Номер телефона неполный' };
                }
                
                if (digits.length > 11) {
                    return { valid: false, message: 'Номер телефона слишком длинный' };
                }
                
                if (!digits.startsWith('7')) {
                    return { valid: false, message: 'Номер должен начинаться с +7' };
                }
                
                const phoneType = getPhoneType(digits);
                
                if (phoneType === 'city' && digits.startsWith('7473')) {
                    return { valid: true, message: 'Городской номер Воронежа', type: 'city' };
                } else if (phoneType === 'mobile' && digits[1] === '9') {
                    // Проверяем, что это валидный мобильный код
                    const mobileCode = digits.substring(1, 4);
                    const validMobileCodes = ['900', '901', '902', '903', '904', '905', '906', '908', '909', 
                                            '910', '911', '912', '913', '914', '915', '916', '917', '918', '919',
                                            '920', '921', '922', '923', '924', '925', '926', '927', '928', '929',
                                            '930', '931', '932', '933', '934', '936', '937', '938', '939',
                                            '941', '950', '951', '952', '953', '954', '955', '956', '958', '960',
                                            '961', '962', '963', '964', '965', '966', '967', '968', '969',
                                            '970', '971', '977', '978', '980', '981', '982', '983', '984', '985',
                                            '986', '987', '988', '989', '991', '992', '993', '994', '995', '996', '997', '999'];
                    
                    if (validMobileCodes.includes(mobileCode)) {
                        return { valid: true, message: 'Мобильный номер', type: 'mobile' };
                    } else {
                        return { valid: false, message: 'Неверный код мобильного оператора' };
                    }
                } else {
                    return { valid: false, message: 'Поддерживаются только мобильные номера и городские номера Воронежа (473)' };
                }
            }
            
            // Обновление внешнего вида поля в зависимости от валидации
            function updateFieldAppearance(validation) {
                // Ищем родительский div - сначала пробуем .relative, потом просто родительский div
                let parentDiv = contactPhoneInput.closest('.relative');
                if (!parentDiv) {
                    parentDiv = contactPhoneInput.parentElement;
                }
                
                let messageElement = parentDiv ? parentDiv.querySelector('.phone-validation-message') : null;
                
                // Удаляем предыдущее сообщение
                if (messageElement) {
                    messageElement.remove();
                }
                
                // Убираем предыдущие классы
                contactPhoneInput.classList.remove('border-green-500', 'border-red-500', 'border-yellow-500');
                
                if (contactPhoneInput.value.length > 3) { // Показываем статус только если что-то введено
                    if (validation.valid) {
                        contactPhoneInput.classList.add('border-green-500');
                        // Создаем сообщение об успехе
                        if (parentDiv) {
                            messageElement = document.createElement('p');
                            messageElement.className = 'phone-validation-message mt-1 text-xs text-green-600 flex items-center';
                            messageElement.innerHTML = `
                                <svg class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                ${validation.type === 'mobile' ? '📱' : '📞'} ${validation.message}
                            `;
                            parentDiv.appendChild(messageElement);
                        }
                    } else {
                        contactPhoneInput.classList.add('border-red-500');
                        // Создаем сообщение об ошибке
                        if (parentDiv) {
                            messageElement = document.createElement('p');
                            messageElement.className = 'phone-validation-message mt-1 text-xs text-red-600 flex items-center';
                            messageElement.innerHTML = `
                                <svg class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                ❌ ${validation.message}
                            `;
                            parentDiv.appendChild(messageElement);
                        }
                    }
                }
            }
            
            // Обработчик ввода с фильтрацией нецифровых символов
            contactPhoneInput.addEventListener('input', function(e) {
                console.log('Input event triggered, value:', e.target.value);
                
                const cursorPosition = e.target.selectionStart;
                const oldValue = e.target.value;
                const oldLength = oldValue.length;
                
                // Форматируем номер (это также удалит нецифровые символы)
                const formatted = formatPhone(e.target.value);
                console.log('Formatted value:', formatted);
                
                e.target.value = formatted;
                
                // Корректируем позицию курсора
                const newLength = formatted.length;
                const lengthDiff = newLength - oldLength;
                let newCursorPosition = cursorPosition + lengthDiff;
                
                // Убеждаемся, что курсор не попадает на служебные символы
                if (newCursorPosition <= 2) newCursorPosition = formatted.length;
                
                e.target.setSelectionRange(newCursorPosition, newCursorPosition);
                
                // Валидируем и обновляем внешний вид
                const validation = validatePhone(formatted);
                updateFieldAppearance(validation);
                
                // Вызываем проверку дубликатов
                checkDuplicatesWithDelay();
            });
            
            // Обработчик вставки
            contactPhoneInput.addEventListener('paste', function(e) {
                console.log('Paste event triggered');
                setTimeout(() => {
                    const formatted = formatPhone(contactPhoneInput.value);
                    contactPhoneInput.value = formatted;
                    const validation = validatePhone(formatted);
                    updateFieldAppearance(validation);
                    checkDuplicatesWithDelay();
                }, 0);
            });
            
            // Обработчик нажатия клавиш (разрешаем только цифры и служебные клавиши)
            contactPhoneInput.addEventListener('keydown', function(e) {
                console.log('Keydown event:', e.key, e.keyCode);
                
                // Разрешаем: backspace, delete, tab, escape, enter, home, end, стрелки
                if ([8, 9, 27, 13, 46, 35, 36, 37, 38, 39, 40].indexOf(e.keyCode) !== -1 ||
                    // Разрешаем: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                    (e.ctrlKey === true && [65, 67, 86, 88].indexOf(e.keyCode) !== -1)) {
                    return;
                }
                
                // Запрещаем все, кроме цифр
                if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                    console.log('Preventing key:', e.key);
                    e.preventDefault();
                }
            });
            
            // Обработчик потери фокуса (финальная валидация)
            contactPhoneInput.addEventListener('blur', function() {
                const validation = validatePhone(contactPhoneInput.value);
                updateFieldAppearance(validation);
                
                // Если номер неполный, добавляем класс ошибки
                if (!validation.valid && contactPhoneInput.value.length > 3) {
                    contactPhoneInput.classList.add('border-red-500');
                }
                
                // Вызываем проверку дубликатов
                checkDuplicates();
            });
            
            // Инициализация - если поле уже содержит значение
            if (contactPhoneInput.value) {
                const formatted = formatPhone(contactPhoneInput.value);
                contactPhoneInput.value = formatted;
                const validation = validatePhone(formatted);
                updateFieldAppearance(validation);
            }
        }
        
        // Функционал проверки дубликатов
        let duplicateCheckTimeout;
        const duplicatesContainer = document.getElementById('duplicates-container');
        const addressInput = document.getElementById('location_address');
        
        // Проверка дубликатов с задержкой
        function checkDuplicatesWithDelay() {
            clearTimeout(duplicateCheckTimeout);
            duplicateCheckTimeout = setTimeout(checkDuplicates, 1000); // Задержка 1 секунда
        }
        
        // Основная функция проверки дубликатов
        function checkDuplicates() {
            const phone = contactPhoneInput.value.trim();
            const address = addressInput.value.trim();
            
            // Минимальная длина для проверки
            if (phone.length < 10 && address.length < 10) {
                hideDuplicates();
                return;
            }
            
            // Подготавливаем данные для запроса
            const formData = new FormData();
            if (phone.length >= 10) {
                formData.append('phone', phone);
            }
            if (address.length >= 10) {
                formData.append('address', address);
            }
            
            // Добавляем CSRF токен
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            
            // Отправляем запрос
            fetch('{{ route("admin.osvv.check-duplicates") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.has_duplicates) {
                    showDuplicates(data.duplicates);
                } else {
                    hideDuplicates();
                }
            })
            .catch(error => {
                console.error('Ошибка проверки дубликатов:', error);
                hideDuplicates();
            });
        }
        
        // Показать уведомления о дубликатах
        function showDuplicates(duplicates) {
            let html = '';
            
            Object.values(duplicates).forEach(duplicate => {
                const isPhoneDuplicate = duplicate.type === 'phone';
                const alertClass = isPhoneDuplicate ? 'duplicate-error' : 'duplicate-warning';
                const iconColor = isPhoneDuplicate ? 'text-red-600' : 'text-yellow-600';
                const titleColor = isPhoneDuplicate ? 'text-red-800' : 'text-yellow-800';
                const textColor = isPhoneDuplicate ? 'text-red-700' : 'text-yellow-700';
                
                html += `
                    <div class="${alertClass} rounded-lg p-4 mb-4">
                        <div class="flex items-start">
                            <svg class="h-5 w-5 ${iconColor} mr-3 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                            <div class="flex-1">
                                <h4 class="text-sm font-semibold ${titleColor} mb-2">
                                    ${isPhoneDuplicate ? '📞' : '📍'} ${duplicate.message} (${duplicate.count})
                                </h4>
                                <div class="space-y-2">
                `;
                
                duplicate.requests.forEach(request => {
                    const statusName = getStatusName(request.status);
                    html += `
                        <div class="duplicate-item bg-white bg-opacity-60 rounded-md p-3 border border-gray-200">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-sm font-medium text-gray-900">
                                            №${request.id} - ${request.contact_name}
                                        </span>
                                        ${duplicate.type === 'address' ? `<span class="similarity-badge">${request.similarity}% схожесть</span>` : ''}
                                    </div>
                                    <p class="text-xs ${textColor} mb-1">📞 ${request.contact_phone}</p>
                                    <p class="text-xs ${textColor} mb-1">📍 ${request.location_address}</p>
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-500">📅 ${request.created_at}</span>
                                        <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600">${statusName}</span>
                                    </div>
                                </div>
                                <a href="${request.url}" target="_blank" class="ml-3 inline-flex items-center px-2 py-1 text-xs text-indigo-600 hover:text-indigo-800 transition-colors">
                                    <svg class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                    Открыть
                                </a>
                            </div>
                        </div>
                    `;
                });
                
                html += `
                                </div>
                                <p class="text-xs ${textColor} mt-3 opacity-80">
                                    ${isPhoneDuplicate 
                                        ? '⚠️ Заявки с одинаковым номером телефона могут быть дубликатами' 
                                        : 'ℹ️ Заявки с похожим адресом не всегда являются дубликатами - разные люди могут подавать заявки на один адрес'
                                    }
                                </p>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            duplicatesContainer.innerHTML = html;
            duplicatesContainer.style.display = '';
        }
        
        // Скрыть уведомления о дубликатах
        function hideDuplicates() {
            duplicatesContainer.style.display = 'none';
            duplicatesContainer.innerHTML = '';
        }
        
        // Получить название статуса
        function getStatusName(status) {
            const statusNames = {
                'new': 'Новая',
                'processing': 'В обработке',
                'capture_scheduled': 'Запланирован отлов',
                'captured': 'Отловлено',
                'in_shelter': 'В приюте',
                'sterilized': 'Стерилизовано',
                'vaccinated': 'Вакцинировано',
                'ready_for_return': 'Готово к возврату',
                'returned': 'Возвращено',
                'completed': 'Завершено',
                'cancelled': 'Отменено'
            };
            return statusNames[status] || status;
        }
        
        // Привязываем обработчики событий только для адреса
        // (обработчики для телефона уже добавлены в блоке валидации выше)
        if (addressInput) {
            addressInput.addEventListener('input', checkDuplicatesWithDelay);
            addressInput.addEventListener('blur', checkDuplicates);
        }
        
        // Добавляем мета-тег для CSRF токена если его нет
        if (!document.querySelector('meta[name="csrf-token"]')) {
            const meta = document.createElement('meta');
            meta.name = 'csrf-token';
            meta.content = '{{ csrf_token() }}';
            document.head.appendChild(meta);
        }
        
        // Инициализация обработчиков для дополнительных адресов
        setupAddressHandlers();
    </script>
@endsection 