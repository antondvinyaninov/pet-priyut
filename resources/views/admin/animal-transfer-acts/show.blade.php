@extends('admin.layout')

@section('header')
    Акт приема-передачи {{ $animalTransferAct->act_number }}
@endsection

@section('content')
    <div class="space-y-6">
        <!-- Хлебные крошки -->
        <div class="bg-white shadow rounded-lg">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2 text-white">
                        <a href="{{ route('admin.animal-transfer-acts.index') }}" class="hover:text-blue-200">Акты приема-передачи</a>
                        <span>/</span>
                        <span class="font-semibold">{{ $animalTransferAct->act_number }}</span>
                    </div>
                    <div class="flex space-x-2">
                        @if($animalTransferAct->status === 'draft')
                            <a href="{{ route('admin.animal-transfer-acts.edit', $animalTransferAct) }}"
                               class="inline-flex items-center px-3 py-1 bg-white/20 text-white rounded-md hover:bg-white/30 text-sm">
                                ✏️ Редактировать
                            </a>
                            <form method="POST" action="{{ route('admin.animal-transfer-acts.sign', $animalTransferAct) }}" class="inline">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center px-3 py-1 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm">
                                    ✅ Подписать
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.animal-transfer-acts.unsign', $animalTransferAct) }}" class="inline">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center px-3 py-1 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 text-sm">
                                    ❌ Отменить подпись
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('admin.animal-transfer-acts.pdf', $animalTransferAct) }}"
                           class="inline-flex items-center px-3 py-1 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm" target="_blank">
                            📄 PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Основная информация -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Основная информация</h3>
                    <div class="flex items-center space-x-2">
                        @if($animalTransferAct->status === 'draft')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                ⏳ Черновик
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                ✅ Подписанный
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Номер акта</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $animalTransferAct->act_number }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Дата акта</label>
                        <p class="text-lg text-gray-900">{{ \Carbon\Carbon::parse($animalTransferAct->act_date)->format('d.m.Y') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Дата создания</label>
                        <p class="text-sm text-gray-600">{{ $animalTransferAct->created_at->format('d.m.Y H:i') }}</p>
                    </div>
                    @if($animalTransferAct->signed_at)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Дата подписания</label>
                            <p class="text-sm text-gray-600">{{ $animalTransferAct->signed_at->format('d.m.Y H:i') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Стороны передачи -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Стороны передачи</h3>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Передающая сторона -->
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-blue-900 mb-3">Передающая сторона</h4>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Организация</label>
                                <p class="text-gray-900">{{ $animalTransferAct->from_organization }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ответственное лицо</label>
                                <p class="text-gray-900">{{ $animalTransferAct->from_person }}</p>
                            </div>
                            @if($animalTransferAct->from_position)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Должность</label>
                                    <p class="text-gray-900">{{ $animalTransferAct->from_position }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Принимающая сторона -->
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-green-900 mb-3">Принимающая сторона</h4>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Организация</label>
                                <p class="text-gray-900">{{ $animalTransferAct->to_organization }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ответственное лицо</label>
                                <p class="text-gray-900">{{ $animalTransferAct->to_person }}</p>
                            </div>
                            @if($animalTransferAct->to_position)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Должность</label>
                                    <p class="text-gray-900">{{ $animalTransferAct->to_position }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Животные -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Животные ({{ $animalTransferAct->animals->count() }})</h3>
            </div>
            
            <div class="p-6">
                @if($animalTransferAct->animals->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($animalTransferAct->animals as $animal)
                            <div class="border rounded-lg p-4 hover:bg-gray-50">
                                <div class="flex items-center space-x-3">
                                    @php
                                        $displayPhoto = $animal->photo ?? ($animal->registrationCard->photo_face ?? null);
                                    @endphp
                                    @if($displayPhoto)
                                        <img src="{{ asset('storage/' . $displayPhoto) }}" 
                                             alt="{{ $animal->name ?? 'Животное' }}"
                                             class="w-12 h-12 rounded-full object-cover">
                                    @else
                                        <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center">
                                            @if($animal->type === 'dog')
                                                🐕
                                            @elseif($animal->type === 'cat')
                                                🐱
                                            @else
                                                🐾
                                            @endif
                                        </div>
                                    @endif
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900">
                                            {{ $animal->name ?? 'Без имени' }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $animal->cage_number ? 'Вольер ' . $animal->cage_number : 'ID: ' . $animal->id }}
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            {{ ucfirst($animal->type) }} • {{ ucfirst($animal->gender) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 text-sm text-gray-600">
                                    @if($animal->breed)
                                        <div><strong>Порода:</strong> {{ $animal->breed }}</div>
                                    @endif
                                    @if($animal->color)
                                        <div><strong>Окрас:</strong> {{ $animal->color }}</div>
                                    @endif
                                    @if($animal->chip_number)
                                        <div><strong>Чип:</strong> {{ $animal->chip_number }}</div>
                                    @endif
                                    @if($animal->tag_number)
                                        <div><strong>Бирка:</strong> {{ $animal->tag_number }}</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <div class="text-6xl mb-4">🐾</div>
                        <div class="text-xl font-medium mb-2">Нет животных</div>
                        <div>К акту не привязаны животные</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Дополнительная информация -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Дополнительная информация</h3>
            </div>
            
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Причина передачи</label>
                    <div class="bg-gray-50 p-3 rounded-md">
                        <p class="text-gray-900 whitespace-pre-wrap">{{ $animalTransferAct->transfer_reason }}</p>
                    </div>
                </div>

                @if($animalTransferAct->conditions)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Условия передачи</label>
                        <div class="bg-gray-50 p-3 rounded-md">
                            <p class="text-gray-900 whitespace-pre-wrap">{{ $animalTransferAct->conditions }}</p>
                        </div>
                    </div>
                @endif

                @if($animalTransferAct->notes)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Примечания</label>
                        <div class="bg-gray-50 p-3 rounded-md">
                            <p class="text-gray-900 whitespace-pre-wrap">{{ $animalTransferAct->notes }}</p>
                        </div>
                    </div>
                @endif

                <!-- Информация о создателе -->
                <div class="pt-4 border-t border-gray-200">
                    <p class="text-sm text-gray-600">
                        <strong>Создатель:</strong> {{ $animalTransferAct->creator->name ?? 'Неизвестно' }}
                        <br>
                        <strong>Дата создания:</strong> {{ $animalTransferAct->created_at->format('d.m.Y H:i') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Кнопки действий -->
        <div class="flex justify-end space-x-3">
            <a href="{{ route('admin.animal-transfer-acts.index') }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                ← Назад к списку
            </a>
            @if($animalTransferAct->status === 'draft')
                <a href="{{ route('admin.animal-transfer-acts.edit', $animalTransferAct) }}"
                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                    ✏️ Редактировать
                </a>
                <form method="POST" action="{{ route('admin.animal-transfer-acts.destroy', $animalTransferAct) }}" class="inline" 
                      onsubmit="return confirm('Вы уверены, что хотите удалить этот акт?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700">
                        🗑️ Удалить
                    </button>
                </form>
            @endif
        </div>
    </div>
@endsection 