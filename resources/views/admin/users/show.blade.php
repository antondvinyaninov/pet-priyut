@extends('admin.layout')

@section('title', 'Просмотр пользователя')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Заголовок с навигацией -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg shadow-lg p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white mb-2">{{ $user->name }}</h1>
                <nav class="text-indigo-100">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-white">Главная</a>
                    <span class="mx-2">/</span>
                    <a href="{{ route('admin.users.index') }}" class="hover:text-white">Пользователи</a>
                    <span class="mx-2">/</span>
                    <span class="text-white">Просмотр</span>
                </nav>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('admin.users.edit', $user) }}" 
                   class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-edit mr-2"></i>Редактировать
                </a>
                <a href="{{ route('admin.users.index') }}" 
                   class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Назад к списку
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Основная информация -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Основная информация</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Имя пользователя</label>
                        <p class="text-lg text-gray-900">{{ $user->name }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Email</label>
                        <p class="text-lg text-gray-900">{{ $user->email }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Статус</label>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ ($user->is_active ?? true) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ ($user->is_active ?? true) ? 'Активен' : 'Неактивен' }}
                        </span>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Email подтвержден</label>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $user->email_verified_at ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ $user->email_verified_at ? 'Подтвержден' : 'Не подтвержден' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Роли пользователя -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Роли пользователя</h2>
                
                @if($user->roles->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($user->roles as $role)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="font-medium text-gray-900">{{ $role->name }}</h3>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                        {{ $role->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $role->is_active ? 'Активна' : 'Неактивна' }}
                                    </span>
                                </div>
                                @if($role->description)
                                    <p class="text-sm text-gray-600">{{ $role->description }}</p>
                                @endif
                                <div class="mt-2 text-xs text-gray-500">
                                    Приоритет: {{ $role->priority }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-user-slash text-4xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500">У пользователя нет назначенных ролей</p>
                        <a href="{{ route('admin.users.edit', $user) }}" 
                           class="mt-2 inline-flex items-center text-indigo-600 hover:text-indigo-500">
                            <i class="fas fa-plus mr-1"></i>Назначить роли
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Боковая панель -->
        <div class="space-y-6">
            <!-- Статистика -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Статистика</h3>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Ролей назначено</span>
                        <span class="font-semibold text-gray-900">{{ $user->roles->count() }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Активных ролей</span>
                        <span class="font-semibold text-gray-900">{{ $user->roles->where('is_active', true)->count() }}</span>
                    </div>
                </div>
            </div>

            <!-- Временные метки -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Временные метки</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Создан</label>
                        <p class="text-sm text-gray-900">{{ $user->created_at->format('d.m.Y H:i') }}</p>
                        <p class="text-xs text-gray-500">{{ $user->created_at->diffForHumans() }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Обновлен</label>
                        <p class="text-sm text-gray-900">{{ $user->updated_at->format('d.m.Y H:i') }}</p>
                        <p class="text-xs text-gray-500">{{ $user->updated_at->diffForHumans() }}</p>
                    </div>
                    
                    @if($user->email_verified_at)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Email подтвержден</label>
                            <p class="text-sm text-gray-900">{{ $user->email_verified_at->format('d.m.Y H:i') }}</p>
                            <p class="text-xs text-gray-500">{{ $user->email_verified_at->diffForHumans() }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Действия -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Действия</h3>
                
                <div class="space-y-3">
                    <a href="{{ route('admin.users.edit', $user) }}" 
                       class="w-full flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors">
                        <i class="fas fa-edit mr-2"></i>Редактировать
                    </a>
                    
                    @if(!$user->email_verified_at)
                        <button type="button" 
                                class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                            <i class="fas fa-envelope mr-2"></i>Отправить подтверждение
                        </button>
                    @endif
                    
                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" 
                          onsubmit="return confirm('Вы уверены, что хотите удалить этого пользователя?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="w-full flex items-center justify-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 transition-colors">
                            <i class="fas fa-trash mr-2"></i>Удалить
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 