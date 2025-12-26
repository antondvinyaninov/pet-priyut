@extends('admin.layout')

@section('title', 'Редактирование пользователя')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Заголовок с навигацией -->
    <div class="bg-gradient-to-r from-green-600 to-blue-600 rounded-lg shadow-lg p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white mb-2">Редактирование пользователя</h1>
                <nav class="text-green-100">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-white">Главная</a>
                    <span class="mx-2">/</span>
                    <a href="{{ route('admin.users.index') }}" class="hover:text-white">Пользователи</a>
                    <span class="mx-2">/</span>
                    <span class="text-white">{{ $user->name }}</span>
                </nav>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('admin.users.show', $user) }}" 
                   class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-eye mr-2"></i>Просмотр
                </a>
                <a href="{{ route('admin.users.index') }}" 
                   class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Назад к списку
                </a>
            </div>
        </div>
    </div>

    <!-- Форма редактирования -->
    <div class="bg-white rounded-lg shadow-lg">
        <div class="p-6">
            <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Основная информация -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Имя -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Имя пользователя <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $user->name) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('name') border-red-500 @enderror"
                               placeholder="Введите имя пользователя"
                               required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email', $user->email) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('email') border-red-500 @enderror"
                               placeholder="user@example.com"
                               required>
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Пароль (опционально) -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Изменение пароля</h3>
                    <p class="text-sm text-gray-600 mb-4">Оставьте поля пустыми, если не хотите менять пароль</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Новый пароль -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                Новый пароль
                            </label>
                            <input type="password" 
                                   id="password" 
                                   name="password"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('password') border-red-500 @enderror"
                                   placeholder="Минимум 8 символов">
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Подтверждение пароля -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                Подтверждение пароля
                            </label>
                            <input type="password" 
                                   id="password_confirmation" 
                                   name="password_confirmation"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                   placeholder="Повторите новый пароль">
                        </div>
                    </div>
                </div>

                <!-- Роли -->
                <div class="border-t pt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Роли пользователя
                    </label>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($roles as $role)
                            <div class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                                <input type="checkbox" 
                                       id="role_{{ $role->id }}" 
                                       name="roles[]" 
                                       value="{{ $role->id }}"
                                       class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                                       {{ in_array($role->id, old('roles', $user->roles->pluck('id')->toArray())) ? 'checked' : '' }}>
                                <label for="role_{{ $role->id }}" class="ml-3 flex-1">
                                    <div class="text-sm font-medium text-gray-900">{{ $role->name }}</div>
                                    @if($role->description)
                                        <div class="text-xs text-gray-500">{{ $role->description }}</div>
                                    @endif
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @error('roles')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Дополнительные настройки -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Дополнительные настройки</h3>
                    
                    <div class="space-y-4">
                        <!-- Статус активности -->
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="is_active" 
                                   name="is_active" 
                                   value="1"
                                   class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                                   {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }}>
                            <label for="is_active" class="ml-3 text-sm text-gray-700">
                                Активный пользователь
                            </label>
                        </div>

                        <!-- Email верифицирован -->
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="email_verified" 
                                   name="email_verified" 
                                   value="1"
                                   class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                                   {{ old('email_verified', $user->email_verified_at ? true : false) ? 'checked' : '' }}>
                            <label for="email_verified" class="ml-3 text-sm text-gray-700">
                                Email подтвержден
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Информация о пользователе -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Информация</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                        <div>
                            <span class="font-medium">Создан:</span> 
                            {{ $user->created_at->format('d.m.Y H:i') }}
                        </div>
                        <div>
                            <span class="font-medium">Обновлен:</span> 
                            {{ $user->updated_at->format('d.m.Y H:i') }}
                        </div>
                        @if($user->email_verified_at)
                            <div>
                                <span class="font-medium">Email подтвержден:</span> 
                                {{ $user->email_verified_at->format('d.m.Y H:i') }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Кнопки действий -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t">
                    <a href="{{ route('admin.users.index') }}" 
                       class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        Отмена
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-save mr-2"></i>Сохранить изменения
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Проверка совпадения паролей
document.addEventListener('DOMContentLoaded', function() {
    const password = document.getElementById('password');
    const passwordConfirmation = document.getElementById('password_confirmation');
    
    function checkPasswords() {
        if (password.value && passwordConfirmation.value) {
            if (password.value !== passwordConfirmation.value) {
                passwordConfirmation.setCustomValidity('Пароли не совпадают');
            } else {
                passwordConfirmation.setCustomValidity('');
            }
        } else {
            passwordConfirmation.setCustomValidity('');
        }
    }
    
    password.addEventListener('input', checkPasswords);
    passwordConfirmation.addEventListener('input', checkPasswords);
});
</script>
@endsection 