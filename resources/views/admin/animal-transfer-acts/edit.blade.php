@extends('admin.layout')

@section('header')
    Редактирование акта {{ $animalTransferAct->act_number }}
@endsection

@section('content')
    <div class="space-y-6">
        <!-- Хлебные крошки -->
        <div class="bg-white shadow rounded-lg">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                <div class="flex items-center space-x-2 text-white">
                    <a href="{{ route('admin.animal-transfer-acts.index') }}" class="hover:text-blue-200">Акты приема-передачи</a>
                    <span>/</span>
                    <a href="{{ route('admin.animal-transfer-acts.show', $animalTransferAct) }}" class="hover:text-blue-200">{{ $animalTransferAct->act_number }}</a>
                    <span>/</span>
                    <span class="font-semibold">Редактирование</span>
                </div>
            </div>
        </div>

        <!-- Форма редактирования -->
        <form method="POST" action="{{ route('admin.animal-transfer-acts.update', $animalTransferAct) }}" class="space-y-6">
            @csrf
            @method('PUT')
            
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
                                   value="{{ old('act_number', $animalTransferAct->act_number) }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('act_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Дата акта -->
                        <div>
                            <label for="act_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Дата акта <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="act_date" id="act_date" 
                                   value="{{ old('act_date', $animalTransferAct->act_date) }}" required
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
                                       value="{{ old('from_organization', $animalTransferAct->from_organization) }}" required
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('from_organization')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="from_person" class="block text-sm font-medium text-gray-700 mb-2">
                                    Ответственное лицо <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="from_person" id="from_person" 
                                       value="{{ old('from_person', $animalTransferAct->from_person) }}" required
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('from_person')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="from_position" class="block text-sm font-medium text-gray-700 mb-2">
                                    Должность
                                </label>
                                <input type="text" name="from_position" id="from_position" 
                                       value="{{ old('from_position', $animalTransferAct->from_position) }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
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
                                       value="{{ old('to_organization', $animalTransferAct->to_organization) }}" required
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('to_organization')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="to_person" class="block text-sm font-medium text-gray-700 mb-2">
                                    Ответственное лицо <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="to_person" id="to_person" 
                                       value="{{ old('to_person', $animalTransferAct->to_person) }}" required
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('to_person')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="to_position" class="block text-sm font-medium text-gray-700 mb-2">
                                    Должность
                                </label>
                                <input type="text" name="to_position" id="to_position" 
                                       value="{{ old('to_position', $animalTransferAct->to_position) }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
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
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($animals as $animal)
                                <div class="border rounded-lg p-4 hover:bg-gray-50">
                                    <label class="flex items-start space-x-3 cursor-pointer">
                                        <input type="checkbox" name="animals[]" value="{{ $animal->id }}"
                                               class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                               {{ in_array($animal->id, old('animals', $animalTransferAct->animals->pluck('id')->toArray())) ? 'checked' : '' }}>
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
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            @endforeach
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
                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('transfer_reason', $animalTransferAct->transfer_reason) }}</textarea>
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
                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('conditions', $animalTransferAct->conditions) }}</textarea>
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
                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('notes', $animalTransferAct->notes) }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Кнопки действий -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.animal-transfer-acts.show', $animalTransferAct) }}" 
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
@endsection 