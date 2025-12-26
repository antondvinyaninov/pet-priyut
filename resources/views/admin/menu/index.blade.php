@extends('admin.layout')

@section('header')
    Управление меню
@endsection

@section('content')
    <div class="space-y-6">
        <!-- Хлебные крошки -->
        <div class="bg-white shadow rounded-lg">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2 text-white">
                        <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-200">Главная</a>
                        <span>/</span>
                        <span class="font-semibold">Управление меню</span>
                    </div>
                    <a href="{{ route('admin.menu.create') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                        ➕ Добавить пункт меню
                    </a>
                </div>
            </div>
        </div>

        <!-- Список пунктов меню -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Пункты меню</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Порядок
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Иконка
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Название
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Маршрут
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Тип
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Статус
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Действия
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($menuItems as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="flex items-center space-x-2">
                                        @if(!$item->parent_id)
                                            <form method="POST" action="{{ route('admin.menu.move-up', $item) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="text-gray-400 hover:text-gray-600" 
                                                        {{ $loop->first ? 'disabled' : '' }}>
                                                    ⬆️
                                                </button>
                                            </form>
                                            <span class="font-medium">{{ $item->order }}</span>
                                            <form method="POST" action="{{ route('admin.menu.move-down', $item) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="text-gray-400 hover:text-gray-600"
                                                        {{ $loop->last ? 'disabled' : '' }}>
                                                    ⬇️
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-gray-400 ml-4">{{ $item->order }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-2xl">
                                    {{ $item->icon }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        @if($item->parent_id)
                                            <span class="text-gray-400">└─</span>
                                        @endif
                                        {{ $item->title }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $item->route ?? 'Подменю' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($item->is_submenu)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            Подменю
                                        </span>
                                    @elseif($item->parent_id)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Подпункт
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Основной
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <form method="POST" action="{{ route('admin.menu.toggle-active', $item) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="focus:outline-none">
                                            @if($item->is_active)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    ✅ Активен
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    ❌ Неактивен
                                                </span>
                                            @endif
                                        </button>
                                    </form>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('admin.menu.edit', $item) }}" 
                                           class="text-indigo-600 hover:text-indigo-900">
                                            ✏️ Редактировать
                                        </a>
                                        <form method="POST" action="{{ route('admin.menu.destroy', $item) }}" 
                                              class="inline" onsubmit="return confirm('Удалить этот пункт меню?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">
                                                🗑️ Удалить
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Дочерние пункты -->
                            @foreach($item->children as $child)
                                <tr class="hover:bg-gray-50 bg-gray-25">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div class="flex items-center space-x-2">
                                            <form method="POST" action="{{ route('admin.menu.move-up', $child) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="text-gray-400 hover:text-gray-600" 
                                                        {{ $loop->first ? 'disabled' : '' }}>
                                                    ⬆️
                                                </button>
                                            </form>
                                            <span class="font-medium">{{ $child->order }}</span>
                                            <form method="POST" action="{{ route('admin.menu.move-down', $child) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="text-gray-400 hover:text-gray-600"
                                                        {{ $loop->last ? 'disabled' : '' }}>
                                                    ⬇️
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-2xl">
                                        {{ $child->icon }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <span class="text-gray-400">└─</span>
                                            {{ $child->title }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $child->route }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Подпункт
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <form method="POST" action="{{ route('admin.menu.toggle-active', $child) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="focus:outline-none">
                                                @if($child->is_active)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        ✅ Активен
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        ❌ Неактивен
                                                    </span>
                                                @endif
                                            </button>
                                        </form>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('admin.menu.edit', $child) }}" 
                                               class="text-indigo-600 hover:text-indigo-900">
                                                ✏️ Редактировать
                                            </a>
                                            <form method="POST" action="{{ route('admin.menu.destroy', $child) }}" 
                                                  class="inline" onsubmit="return confirm('Удалить этот пункт меню?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">
                                                    🗑️ Удалить
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection 