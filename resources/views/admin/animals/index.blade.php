@extends('admin.layout')

@section('title', 'Управление животными')

@section('content')
<div class="space-y-6">
    <!-- Заголовок и статистика -->
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold text-gray-900">Управление животными</h1>
            <div class="flex space-x-4">
                <a href="{{ route('admin.animals.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Добавить животное
                </a>
                <a href="{{ route('admin.animals.export') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Экспорт CSV
                </a>
            </div>
        </div>
        
        <!-- Статистика -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-blue-600">{{ $totalAnimals }}</div>
                <div class="text-sm text-blue-800">Всего активных животных</div>
            </div>
            <div class="bg-red-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-red-600">{{ $overdueAnimals }}</div>
                <div class="text-sm text-red-800">Просроченных этапов</div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-green-600">{{ $stages->sum(function($stage) { return $stage->animals->count(); }) }}</div>
                <div class="text-sm text-green-800">В обработке</div>
            </div>
        </div>
    </div>

    <!-- Kanban доска -->
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Этапы обработки</h2>
        
        <div class="flex space-x-4 overflow-x-auto pb-4" id="kanban-board">
            @foreach($stages as $stage)
                <div class="flex-shrink-0 w-80 bg-gray-50 rounded-lg p-4" data-stage-id="{{ $stage->id }}">
                    <!-- Заголовок колонки -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 rounded-full" style="background-color: {{ $stage->color }}"></div>
                            <h3 class="font-semibold text-gray-900">{{ $stage->name }}</h3>
                            <span class="bg-gray-200 text-gray-700 text-xs px-2 py-1 rounded-full">
                                {{ $stage->animals->count() }}
                            </span>
                        </div>
                        @if($stage->duration_days)
                            <span class="text-xs text-gray-500">{{ $stage->duration_days }} дн.</span>
                        @endif
                    </div>
                    
                    <!-- Описание этапа -->
                    <p class="text-sm text-gray-600 mb-4">{{ $stage->description }}</p>
                    
                    <!-- Карточки животных -->
                    <div class="space-y-3 min-h-[200px]" id="stage-{{ $stage->id }}">
                        @foreach($stage->animals as $animal)
                            <div class="bg-white rounded-lg shadow-sm border p-4 cursor-move animal-card" 
                                 data-animal-id="{{ $animal->id }}"
                                 data-current-stage="{{ $animal->current_stage_id }}">
                                
                                <!-- Фото и основная информация -->
                                <div class="flex items-start space-x-3">
                                    @php
                                        $displayPhoto = $animal->photo ?? ($animal->registrationCard->photo_face ?? null);
                                    @endphp
                                    @if($displayPhoto)
                                        <img src="{{ Storage::url($displayPhoto) }}" 
                                             alt="Фото животного" 
                                             class="w-12 h-12 rounded-lg object-cover cursor-pointer hover:opacity-75 transition"
                                             onclick="openPhotoModal('{{ Storage::url($displayPhoto) }}', '')">
                                    @else
                                        <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    @endif
                                    
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <h4 class="text-sm font-medium text-gray-900 truncate">
                                                {{ $animal->name ?: 'Без клички' }}
                                            </h4>
                                            <span class="text-xs text-gray-500">#{{ $animal->id }}</span>
                                        </div>
                                        
                                        <div class="text-xs text-gray-600 space-y-1">
                                            <div>{{ $animal->type_name }} • {{ $animal->gender_name }}</div>
                                            @if($animal->breed)
                                                <div>{{ $animal->breed }}</div>
                                            @endif
                                            @if($animal->osvvRequest)
                                                <div class="text-blue-600">ОСВВ #{{ $animal->osvvRequest->id }}</div>
                                            @endif
                                        </div>
                                        
                                        <!-- Прогресс выполнения задач -->
                                        @php
                                            $totalTasks = $stage->tasks->count();
                                            $completedTasks = $animal->taskCompletions->where('task.stage_id', $stage->id)->count();
                                            $progress = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;
                                        @endphp
                                        
                                        <div class="mt-2">
                                            <div class="flex justify-between text-xs text-gray-600 mb-1">
                                                <span>Задачи</span>
                                                <span>{{ $completedTasks }}/{{ $totalTasks }}</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $progress }}%"></div>
                                            </div>
                                        </div>
                                        
                                        <!-- Время на этапе -->
                                        <div class="mt-2 text-xs text-gray-500">
                                            На этапе: {{ $animal->getDaysInCurrentStage() }} дн.
                                            @if($stage->duration_days && $animal->getDaysInCurrentStage() > $stage->duration_days)
                                                <span class="text-red-600 font-medium">(просрочено)</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Действия -->
                                <div class="mt-3 flex justify-between items-center">
                                    <a href="{{ route('admin.animals.show', $animal) }}" 
                                       class="text-blue-600 hover:text-blue-800 text-xs font-medium">
                                        Подробнее
                                    </a>
                                    
                                    @if($animal->canMoveToNextStage())
                                        <span class="text-green-600 text-xs">✓ Готов к переводу</span>
                                    @else
                                        <span class="text-orange-600 text-xs">⏳ В процессе</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                        
                        <!-- Placeholder для пустых колонок -->
                        @if($stage->animals->count() === 0)
                            <div class="text-center text-gray-400 py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                <p class="mt-2 text-sm">Нет животных</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Модальное окно для перемещения животного -->
<div id="move-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Перевести животное</h3>
                <p class="text-sm text-gray-600 mb-4">Выберите этап для перевода животного:</p>
                
                <div class="space-y-2" id="stage-options">
                    <!-- Опции этапов будут добавлены через JavaScript -->
                </div>
                
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeMoveModal()" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md">
                        Отмена
                    </button>
                    <button type="button" onclick="confirmMove()" 
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md">
                        Перевести
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для выбора дальнейшей судьбы -->
<div id="release-choice-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Выберите дальнейшую судьбу животного</h3>
                <p class="text-sm text-gray-600 mb-6">Животное готово к выпуску. Выберите один из вариантов:</p>
                
                <div class="space-y-3">
                    <button type="button" onclick="releaseToOriginalPlace()" 
                            class="w-full px-4 py-3 text-left bg-blue-50 hover:bg-blue-100 border-2 border-blue-200 rounded-lg transition-colors">
                        <div class="flex items-center space-x-3">
                            <span class="text-2xl">📍</span>
                            <div>
                                <div class="font-medium text-gray-900">На прежнее место</div>
                                <div class="text-sm text-gray-600">Животное будет добавлено в планировщик выезда</div>
                            </div>
                        </div>
                    </button>
                    
                    <button type="button" onclick="keepInShelter()" 
                            class="w-full px-4 py-3 text-left bg-green-50 hover:bg-green-100 border-2 border-green-200 rounded-lg transition-colors">
                        <div class="flex items-center space-x-3">
                            <span class="text-2xl">🏠</span>
                            <div>
                                <div class="font-medium text-gray-900">Остается в приюте</div>
                                <div class="text-sm text-gray-600">Животное присоединится к постоянным жителям приюта</div>
                            </div>
                        </div>
                    </button>
                </div>
                
                <div class="mt-6 flex justify-end">
                    <button type="button" onclick="closeReleaseChoiceModal()" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md">
                        Отмена
                    </button>
                </div>
            </div>
        </div>
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

@push('scripts')
<script>
let currentAnimal = null;
let selectedStage = null;

// Инициализация drag & drop
document.addEventListener('DOMContentLoaded', function() {
    initializeDragAndDrop();
});

function initializeDragAndDrop() {
    const animalCards = document.querySelectorAll('.animal-card');
    const stageColumns = document.querySelectorAll('[data-stage-id]');
    
    // Делаем карточки перетаскиваемыми
    animalCards.forEach(card => {
        card.draggable = true;
        
        card.addEventListener('dragstart', function(e) {
            currentAnimal = {
                id: this.dataset.animalId,
                currentStage: this.dataset.currentStage,
                element: this
            };
            this.classList.add('opacity-50');
        });
        
        card.addEventListener('dragend', function(e) {
            this.classList.remove('opacity-50');
        });
    });
    
    // Настраиваем зоны для сброса
    stageColumns.forEach(column => {
        column.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('bg-blue-50');
        });
        
        column.addEventListener('dragleave', function(e) {
            this.classList.remove('bg-blue-50');
        });
        
        column.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('bg-blue-50');
            
            const targetStageId = this.dataset.stageId;
            if (currentAnimal && targetStageId !== currentAnimal.currentStage) {
                showMoveModal(currentAnimal.id, targetStageId);
            }
        });
    });
}

function showMoveModal(animalId, targetStageId) {
    currentAnimal.targetStage = targetStageId;
    selectedStage = targetStageId;
    
    // Получаем информацию об этапах
    const stages = @json($stages->map(function($stage) {
        return [
            'id' => $stage->id,
            'name' => $stage->name,
            'color' => $stage->color
        ];
    }));
    
    const currentStageId = parseInt(currentAnimal.currentStage);
    const currentStage = stages.find(s => s.id == currentStageId);
    
    // Проверяем, перетаскивается ли животное с этапа "Готов к выпуску"
    if (currentStage && currentStage.name === 'Готов к выпуску') {
        showReleaseChoiceModal(animalId);
        return;
    }
    
    const targetStage = stages.find(s => s.id == targetStageId);
    
    // Заполняем опции
    const stageOptions = document.getElementById('stage-options');
    stageOptions.innerHTML = `
        <div class="flex items-center space-x-3 p-3 border rounded-lg bg-blue-50">
            <div class="w-4 h-4 rounded-full" style="background-color: ${targetStage.color}"></div>
            <span class="font-medium">${targetStage.name}</span>
        </div>
    `;
    
    document.getElementById('move-modal').classList.remove('hidden');
}

function closeMoveModal() {
    document.getElementById('move-modal').classList.add('hidden');
    currentAnimal = null;
    selectedStage = null;
}

function confirmMove() {
    if (!currentAnimal || !selectedStage) return;
    
    fetch(`/admin/animals/${currentAnimal.id}/move-stage`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            stage_id: selectedStage
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Перезагружаем страницу для обновления
            location.reload();
        } else {
            alert(data.message || 'Ошибка при перемещении животного');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Произошла ошибка при перемещении животного');
    })
    .finally(() => {
        closeMoveModal();
    });
}

// Функции для модального окна выбора дальнейшей судьбы
function showReleaseChoiceModal(animalId) {
    document.getElementById('release-choice-modal').classList.remove('hidden');
}

function closeReleaseChoiceModal() {
    document.getElementById('release-choice-modal').classList.add('hidden');
    currentAnimal = null;
}

function releaseToOriginalPlace() {
    if (!currentAnimal) return;
    
    fetch(`/admin/animals/${currentAnimal.id}/release-to-original-place`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Животное добавлено в планировщик выезда');
            location.reload();
        } else {
            alert(data.message || 'Ошибка при обработке');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Произошла ошибка');
    })
    .finally(() => {
        closeReleaseChoiceModal();
    });
}

function keepInShelter() {
    if (!currentAnimal) return;
    
    fetch(`/admin/animals/${currentAnimal.id}/keep-in-shelter`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Животное присоединилось к постоянным жителям приюта');
            location.reload();
        } else {
            alert(data.message || 'Ошибка при обработке');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Произошла ошибка');
    })
    .finally(() => {
        closeReleaseChoiceModal();
    });
}

// Обновление страницы каждые 30 секунд
setInterval(() => {
    location.reload();
}, 30000);
</script>
@endpush 