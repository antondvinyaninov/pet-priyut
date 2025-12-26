@extends('admin.layout')

@section('header', 'Создать задачу')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-3xl mx-auto">
        <!-- Кнопка назад -->
        <div class="mb-6">
            <a href="{{ route('admin.tasks.index') }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Назад к списку задач
            </a>
        </div>

        <!-- Форма -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <form action="{{ route('admin.tasks.store') }}" method="POST">
                @csrf

                <!-- Название -->
                <div class="mb-4">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Название задачи <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="title" name="title" value="{{ old('title') }}" required
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('title') border-red-500 @enderror">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Описание -->
                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Описание
                    </label>
                    <textarea id="description" name="description" rows="4"
                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
                </div>

                <!-- Тип, Приоритет, Статус -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                            Тип задачи <span class="text-red-500">*</span>
                        </label>
                        <select id="type" name="type" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="general" {{ old('type') == 'general' ? 'selected' : '' }}>Общая</option>
                            <option value="osvv" {{ old('type') == 'osvv' ? 'selected' : '' }}>ОСВВ</option>
                            <option value="animal_care" {{ old('type') == 'animal_care' ? 'selected' : '' }}>Уход за животными</option>
                            <option value="administrative" {{ old('type') == 'administrative' ? 'selected' : '' }}>Административная</option>
                        </select>
                    </div>

                    <div>
                        <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                            Приоритет <span class="text-red-500">*</span>
                        </label>
                        <select id="priority" name="priority" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Низкий</option>
                            <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Средний</option>
                            <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>Высокий</option>
                            <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Срочный</option>
                        </select>
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                            Статус
                        </label>
                        <select id="status" name="status"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>Ожидает</option>
                            <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>В работе</option>
                            <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Выполнено</option>
                        </select>
                    </div>
                </div>

                <!-- Назначить сотруднику -->
                <div class="mb-4">
                    <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-2">
                        Назначить сотруднику
                    </label>
                    <select id="assigned_to" name="assigned_to"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Не назначено</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Срок выполнения -->
                <div class="mb-4">
                    <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Срок выполнения
                    </label>
                    <input type="datetime-local" id="due_date" name="due_date" value="{{ old('due_date') }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <!-- Связанные объекты -->
                <div class="border-t pt-4 mb-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Связанные объекты</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Заявка ОСВВ -->
                        <div>
                            <label for="osvv_request_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Заявка ОСВВ
                            </label>
                            <select id="osvv_request_id" name="osvv_request_id"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Не связано</option>
                                @foreach($osvvRequests as $request)
                                    <option value="{{ $request->id }}" {{ old('osvv_request_id') == $request->id ? 'selected' : '' }}>
                                        #{{ $request->id }} - {{ $request->contact_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Животное -->
                        <div>
                            <label for="animal_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Животное
                            </label>
                            <select id="animal_id" name="animal_id"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Не связано</option>
                                @foreach($animals as $animal)
                                    <option value="{{ $animal->id }}" {{ old('animal_id') == $animal->id ? 'selected' : '' }}>
                                        #{{ $animal->id }} - {{ $animal->name ?: 'Без имени' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Примечания -->
                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Примечания
                    </label>
                    <textarea id="notes" name="notes" rows="3"
                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                </div>

                <!-- Кнопки -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.tasks.index') }}"
                       class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Отмена
                    </a>
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Создать задачу
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
