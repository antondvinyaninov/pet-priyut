@extends('admin.layout')

@section('title', 'Создание пользователя')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Заголовок с навигацией -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg shadow-lg p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white mb-2">Создание пользователя</h1>
                <nav class="text-blue-100">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-white">Главная</a>
                    <span class="mx-2">/</span>
                    <a href="{{ route('admin.users.index') }}" class="hover:text-white">Пользователи</a>
                    <span class="mx-2">/</span>
                    <span class="text-white">Создание</span>
                </nav>
            </div>
            <a href="{{ route('admin.users.index') }}" 
               class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Назад к списку
            </a>
        </div>
    </div>

    <!-- Форма создания -->
    <div class="bg-white rounded-lg shadow-lg">
        <div class="p-6">
            <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-6">
                @csrf

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
                               value="{{ old('name') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
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
                               value="{{ old('email') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror"
                               placeholder="user@example.com"
                               required>
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Пароль -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Пароль -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Пароль <span class="text-red-500">*</span>
                        </label>
                        <input type="password" 
                               id="password" 
                               name="password"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror"
                               placeholder="Минимум 8 символов"
                               required>
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Подтверждение пароля -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                            Подтверждение пароля <span class="text-red-500">*</span>
                        </label>
                        <input type="password" 
                               id="password_confirmation" 
                               name="password_confirmation"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Повторите пароль"
                               required>
                    </div>
                </div>

                <!-- Роли -->
                <div>
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
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                       {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}>
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
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label for="is_active" class="ml-3 text-sm text-gray-700">
                                Активный пользователь
                            </label>
                        </div>

                        <!-- Отправить уведомление -->
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="send_notification" 
                                   name="send_notification" 
                                   value="1"
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                   {{ old('send_notification', true) ? 'checked' : '' }}>
                            <label for="send_notification" class="ml-3 text-sm text-gray-700">
                                Отправить уведомление о создании аккаунта
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Кнопки действий -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t">
                    <a href="{{ route('admin.users.index') }}" 
                       class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        Отмена
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-save mr-2"></i>Создать пользователя
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
        }
    }
    
    password.addEventListener('input', checkPasswords);
    passwordConfirmation.addEventListener('input', checkPasswords);
});
</script>
@endsection 