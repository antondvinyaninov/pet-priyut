@extends('admin.layout')

@section('header')
    Создание пункта меню
@endsection

@section('content')
    <div class="space-y-6">
        <!-- Хлебные крошки -->
        <div class="bg-white shadow rounded-lg">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                <div class="flex items-center space-x-2 text-white">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-200">Главная</a>
                    <span>/</span>
                    <a href="{{ route('admin.menu.index') }}" class="hover:text-blue-200">Управление меню</a>
                    <span>/</span>
                    <span class="font-semibold">Создание</span>
                </div>
            </div>
        </div>

        <!-- Форма создания -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Новый пункт меню</h3>
            </div>
            
            <form method="POST" action="{{ route('admin.menu.store') }}" class="p-6">
                @csrf
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Название -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">Название <span class="text-red-500">*</span></label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Иконка -->
                    <div>
                        <label for="icon" class="block text-sm font-medium text-gray-700">Иконка (эмодзи) <span class="text-red-500">*</span></label>
                        <input type="text" name="icon" id="icon" value="{{ old('icon') }}" required maxlength="10"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-2xl">
                        @error('icon')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Например: 🏠, 📋, 🐾, 📊</p>
                    </div>

                    <!-- Маршрут -->
                    <div>
                        <label for="route" class="block text-sm font-medium text-gray-700">Маршрут</label>
                        <input type="text" name="route" id="route" value="{{ old('route') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               placeholder="admin.dashboard">
                        @error('route')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Оставьте пустым для подменю</p>
                    </div>

                    <!-- Родительский пункт -->
                    <div>
                        <label for="parent_id" class="block text-sm font-medium text-gray-700">Родительский пункт</label>
                        <select name="parent_id" id="parent_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Нет (основной пункт)</option>
                            @foreach($parentItems as $parent)
                                <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->title }}
                                </option>
                            @endforeach
                        </select>
                        @error('parent_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- ID подменю -->
                    <div>
                        <label for="submenu_id" class="block text-sm font-medium text-gray-700">ID подменю</label>
                        <input type="text" name="submenu_id" id="submenu_id" value="{{ old('submenu_id') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               placeholder="tasksSubmenu">
                        @error('submenu_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Для JavaScript (например: tasksSubmenu)</p>
                    </div>
                </div>

                <!-- Чекбоксы -->
                <div class="mt-6 space-y-4">
                    <div class="flex items-center">
                        <input type="checkbox" name="is_submenu" id="is_submenu" value="1" {{ old('is_submenu') ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_submenu" class="ml-2 block text-sm text-gray-900">
                            Это подменю (с дочерними пунктами)
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">
                            Активен (отображается в меню)
                        </label>
                    </div>
                </div>

                <!-- Кнопки -->
                <div class="mt-8 flex justify-end space-x-3">
                    <a href="{{ route('admin.menu.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Отмена
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        💾 Создать пункт меню
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection 