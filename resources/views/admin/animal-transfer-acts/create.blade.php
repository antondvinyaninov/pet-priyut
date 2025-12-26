@extends('admin.layout')

@section('header')
    Создание акта приема-передачи
@endsection

@section('content')
    <div class="space-y-6">
        <!-- Хлебные крошки -->
        <div class="bg-white shadow rounded-lg">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                <div class="flex items-center space-x-2 text-white">
                    <a href="{{ route('admin.animal-transfer-acts.index') }}" class="hover:text-blue-200">Акты приема-передачи</a>
                    <span>/</span>
                    <span class="font-semibold">Создание акта</span>
                </div>
            </div>
        </div>

        <!-- Форма создания -->
        <form method="POST" action="{{ route('admin.animal-transfer-acts.store') }}" class="space-y-6">
            @csrf
            
            <!-- Основная информация -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Основная информация</h3>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Номер акта -->
                        <div>
                            <label for="act_number" class="block text-sm font-medium text-gray-700 mb-2">
                                Номер акта
                            </label>
                            <input type="text" name="act_number" id="act_number" 
                                   value="{{ old('act_number') }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   placeholder="Оставьте пустым для автоматической генерации">
                            @error('act_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Если не указан, будет создан автоматически</p>
                        </div>

                        <!-- Дата акта -->
                        <div>
                            <label for="act_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Дата акта <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="act_date" id="act_date" 
                                   value="{{ old('act_date', date('Y-m-d')) }}" required
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('act_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Организации -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Передающая и принимающая стороны</h3>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Передающая сторона -->
                        <div class="space-y-4">
                            <h4 class="font-medium text-gray-900">Передающая сторона</h4>
                            
                            <div>
                                <label for="from_organization" class="block text-sm font-medium text-gray-700 mb-2">
                                    Организация <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="from_organization" id="from_organization" 
                                       value="{{ old('from_organization') }}" required
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="Название организации">
                                @error('from_organization')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="from_person" class="block text-sm font-medium text-gray-700 mb-2">
                                    Ответственное лицо <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="from_person" id="from_person" 
                                       value="{{ old('from_person') }}" required
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="ФИО ответственного лица">
                                @error('from_person')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="from_position" class="block text-sm font-medium text-gray-700 mb-2">
                                    Должность
                                </label>
                                <input type="text" name="from_position" id="from_position" 
                                       value="{{ old('from_position') }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="Должность">
                                @error('from_position')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Принимающая сторона -->
                        <div class="space-y-4">
                            <h4 class="font-medium text-gray-900">Принимающая сторона</h4>
                            
                            <div>
                                <label for="to_organization" class="block text-sm font-medium text-gray-700 mb-2">
                                    Организация <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="to_organization" id="to_organization" 
                                       value="{{ old('to_organization') }}" required
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="Название организации">
                                @error('to_organization')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="to_person" class="block text-sm font-medium text-gray-700 mb-2">
                                    Ответственное лицо <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="to_person" id="to_person" 
                                       value="{{ old('to_person') }}" required
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="ФИО ответственного лица">
                                @error('to_person')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="to_position" class="block text-sm font-medium text-gray-700 mb-2">
                                    Должность
                                </label>
                                <input type="text" name="to_position" id="to_position" 
                                       value="{{ old('to_position') }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="Должность">
                                @error('to_position')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Животные -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Животные для передачи</h3>
                </div>
                
                <div class="p-6">
                    @if($animals->count() > 0)
                        <!-- Поиск животных -->
                        <div class="mb-6">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input type="text" id="animal-search" 
                                       class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="🔍 Поиск по имени, бирке или чипу...">
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Введите имя животного, номер бирки или чипа для поиска</p>
                        </div>

                        <!-- Счетчик выбранных -->
                        <div id="stats-panel" class="mb-4 hidden">
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-600">
                                    Найдено: <span id="visible-animals">0</span>
                                    • Выбрано: <span id="selected-animals">0</span>
                                </div>
                                <div class="flex space-x-2">
                                    <button type="button" id="select-all" 
                                            class="text-sm text-blue-600 hover:text-blue-800 focus:outline-none">
                                        Выбрать все
                                    </button>
                                    <span class="text-gray-400">|</span>
                                    <button type="button" id="deselect-all" 
                                            class="text-sm text-blue-600 hover:text-blue-800 focus:outline-none">
                                        Снять выбор
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Начальное сообщение -->
                        <div id="initial-message" class="text-center py-6 text-gray-500">
                            <div class="text-sm">
                                🔍 Введите минимум 2 символа для поиска животного
                                <span class="text-gray-400">(доступно: {{ $animals->count() }})</span>
                            </div>
                        </div>

                        <!-- Результаты поиска -->
                        <div id="animals-grid" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($animals as $animal)
                                <div class="animal-card border rounded-lg p-4 hover:bg-gray-50" 
                                     data-name="{{ strtolower($animal->name ?? '') }}"
                                     data-tag="{{ strtolower($animal->tag_number ?? '') }}"
                                     data-chip="{{ strtolower($animal->chip_number ?? '') }}"
                                     data-cage="{{ strtolower($animal->cage_number ?? '') }}">
                                    <label class="flex items-start space-x-3 cursor-pointer">
                                        <input type="checkbox" name="animals[]" value="{{ $animal->id }}"
                                               class="animal-checkbox mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                               {{ in_array($animal->id, old('animals', [])) ? 'checked' : '' }}>
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-2">
                                                @php
                                                    $displayPhoto = $animal->photo ?? ($animal->registrationCard->photo_face ?? null);
                                                @endphp
                                                @if($displayPhoto)
                                                    <img src="{{ asset('storage/' . $displayPhoto) }}" 
                                                         alt="{{ $animal->name ?? 'Животное' }}"
                                                         class="w-10 h-10 rounded-full object-cover">
                                                @else
                                                    <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                        @if($animal->type === 'dog')
                                                            🐕
                                                        @elseif($animal->type === 'cat')
                                                            🐱
                                                        @else
                                                            🐾
                                                        @endif
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="font-medium text-gray-900">
                                                        {{ $animal->name ?? 'Без имени' }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ $animal->cage_number ? 'Вольер ' . $animal->cage_number : 'ID: ' . $animal->id }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-2 text-sm text-gray-600">
                                                <div>{{ ucfirst($animal->type) }} • {{ ucfirst($animal->gender) }}</div>
                                                @if($animal->breed)
                                                    <div>{{ $animal->breed }}</div>
                                                @endif
                                                @if($animal->color)
                                                    <div>{{ $animal->color }}</div>
                                                @endif
                                                @if($animal->tag_number)
                                                    <div class="text-blue-600">🏷️ Бирка: {{ $animal->tag_number }}</div>
                                                @endif
                                                @if($animal->chip_number)
                                                    <div class="text-green-600">💾 Чип: {{ $animal->chip_number }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <!-- Сообщение о отсутствии результатов поиска -->
                        <div id="no-results" class="hidden text-center py-8 text-gray-500">
                            <div class="text-6xl mb-4">🔍</div>
                            <div class="text-xl font-medium mb-2">Животные не найдены</div>
                            <div>Попробуйте изменить поисковый запрос</div>
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <div class="text-6xl mb-4">🐾</div>
                            <div class="text-xl font-medium mb-2">Нет доступных животных</div>
                            <div>Все животные уже переданы или не имеют статуса "активен"</div>
                        </div>
                    @endif
                    
                    @error('animals')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Дополнительная информация -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Дополнительная информация</h3>
                </div>
                
                <div class="p-6 space-y-4">
                    <!-- Причина передачи -->
                    <div>
                        <label for="transfer_reason" class="block text-sm font-medium text-gray-700 mb-2">
                            Причина передачи <span class="text-red-500">*</span>
                        </label>
                        <textarea name="transfer_reason" id="transfer_reason" rows="3" required
                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                  placeholder="Укажите причину передачи животных...">{{ old('transfer_reason') }}</textarea>
                        @error('transfer_reason')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Условия передачи -->
                    <div>
                        <label for="conditions" class="block text-sm font-medium text-gray-700 mb-2">
                            Условия передачи
                        </label>
                        <textarea name="conditions" id="conditions" rows="3"
                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                  placeholder="Особые условия или требования к передаче...">{{ old('conditions') }}</textarea>
                        @error('conditions')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Примечания -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Примечания
                        </label>
                        <textarea name="notes" id="notes" rows="3"
                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                  placeholder="Дополнительные примечания...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Кнопки действий -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.animal-transfer-acts.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Отмена
                </a>
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    📋 Создать акт
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('animal-search');
            const animalCards = document.querySelectorAll('.animal-card');
            const animalsGrid = document.getElementById('animals-grid');
            const noResultsDiv = document.getElementById('no-results');
            const initialMessage = document.getElementById('initial-message');
            const statsPanel = document.getElementById('stats-panel');
            const visibleAnimalsSpan = document.getElementById('visible-animals');
            const selectedAnimalsSpan = document.getElementById('selected-animals');
            const selectAllBtn = document.getElementById('select-all');
            const deselectAllBtn = document.getElementById('deselect-all');
            
            // Функция поиска
            function performSearch() {
                const searchTerm = searchInput.value.toLowerCase().trim();
                let visibleCount = 0;
                
                // Если поисковый запрос пустой или слишком короткий
                if (searchTerm.length < 2) {
                    // Показываем начальное сообщение
                    initialMessage.classList.remove('hidden');
                    animalsGrid.classList.add('hidden');
                    noResultsDiv.classList.add('hidden');
                    statsPanel.classList.add('hidden');
                    return;
                }
                
                // Скрываем начальное сообщение
                initialMessage.classList.add('hidden');
                
                // Поиск по животным
                animalCards.forEach(card => {
                    const name = card.dataset.name || '';
                    const tag = card.dataset.tag || '';
                    const chip = card.dataset.chip || '';
                    const cage = card.dataset.cage || '';
                    
                    const matches = name.includes(searchTerm) || 
                                   tag.includes(searchTerm) || 
                                   chip.includes(searchTerm) ||
                                   cage.includes(searchTerm);
                    
                    if (matches) {
                        card.style.display = 'block';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });
                
                // Обновляем счетчик отображаемых
                visibleAnimalsSpan.textContent = visibleCount;
                
                // Показываем/скрываем соответствующие блоки
                if (visibleCount === 0) {
                    noResultsDiv.classList.remove('hidden');
                    animalsGrid.classList.add('hidden');
                    statsPanel.classList.add('hidden');
                } else {
                    noResultsDiv.classList.add('hidden');
                    animalsGrid.classList.remove('hidden');
                    statsPanel.classList.remove('hidden');
                }
            }
            
            // Функция подсчета выбранных
            function updateSelectedCount() {
                const checkboxes = document.querySelectorAll('.animal-checkbox');
                const selectedCount = Array.from(checkboxes).filter(cb => cb.checked && cb.closest('.animal-card').style.display !== 'none').length;
                selectedAnimalsSpan.textContent = selectedCount;
            }
            
            // Функция выбора всех видимых
            function selectAllVisible() {
                const visibleCards = Array.from(animalCards).filter(card => card.style.display !== 'none');
                visibleCards.forEach(card => {
                    const checkbox = card.querySelector('.animal-checkbox');
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });
                updateSelectedCount();
            }
            
            // Функция снятия выбора со всех
            function deselectAll() {
                const checkboxes = document.querySelectorAll('.animal-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
                updateSelectedCount();
            }
            
            // Обработчики событий
            searchInput.addEventListener('input', performSearch);
            
            // Обработчики для чекбоксов
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('animal-checkbox')) {
                    updateSelectedCount();
                }
            });
            
            // Обработчики для кнопок
            selectAllBtn.addEventListener('click', selectAllVisible);
            deselectAllBtn.addEventListener('click', deselectAll);
            
            // Инициализация счетчиков
            updateSelectedCount();
            
            // Фокус на поле поиска при загрузке
            searchInput.focus();
            
            // Очистка поиска при нажатии Escape
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    searchInput.value = '';
                    performSearch();
                }
            });
        });
    </script>
@endsection 