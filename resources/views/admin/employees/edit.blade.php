@extends('admin.layout')

@section('header')
    <div class="bg-gradient-to-r from-purple-600 to-blue-600 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <div>
                        <h1 class="text-2xl font-bold">Редактирование сотрудника</h1>
                        <p class="text-white/70">{{ $employee->full_name }}</p>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.employees.show', $employee) }}" 
                       class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-white/20 active:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                        </svg>
                        Просмотр
                    </a>
                    <a href="{{ route('admin.employees.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-white/20 active:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                        </svg>
                        К списку
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <form action="{{ route('admin.employees.update', $employee) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        
        <!-- Основная информация -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="bg-gradient-to-r from-gray-600 to-gray-700 px-6 py-4">
                <h3 class="text-lg font-bold text-white">Основная информация</h3>
            </div>
            <div class="px-6 py-4 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="employee_number" class="block text-sm font-medium text-gray-700 mb-1">Табельный номер</label>
                        <input type="text" name="employee_number" id="employee_number" 
                               value="{{ old('employee_number', $employee->employee_number) }}"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('employee_number') border-red-300 @enderror">
                        @error('employee_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="is_active" class="block text-sm font-medium text-gray-700 mb-1">Статус</label>
                        <select name="is_active" id="is_active" 
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="1" {{ old('is_active', $employee->is_active) ? 'selected' : '' }}>Активен</option>
                            <option value="0" {{ !old('is_active', $employee->is_active) ? 'selected' : '' }}>Неактивен</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">Имя *</label>
                        <input type="text" name="first_name" id="first_name" 
                               value="{{ old('first_name', $employee->first_name) }}" required
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('first_name') border-red-300 @enderror">
                        @error('first_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Фамилия *</label>
                        <input type="text" name="last_name" id="last_name" 
                               value="{{ old('last_name', $employee->last_name) }}" required
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('last_name') border-red-300 @enderror">
                        @error('last_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="middle_name" class="block text-sm font-medium text-gray-700 mb-1">Отчество</label>
                    <input type="text" name="middle_name" id="middle_name" 
                           value="{{ old('middle_name', $employee->middle_name) }}"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('middle_name') border-red-300 @enderror">
                    @error('middle_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="birth_date" class="block text-sm font-medium text-gray-700 mb-1">Дата рождения</label>
                        <input type="date" name="birth_date" id="birth_date" 
                               value="{{ old('birth_date', $employee->birth_date?->format('Y-m-d')) }}"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('birth_date') border-red-300 @enderror">
                        @error('birth_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="hire_date" class="block text-sm font-medium text-gray-700 mb-1">Дата приема *</label>
                        <input type="date" name="hire_date" id="hire_date" 
                               value="{{ old('hire_date', $employee->hire_date?->format('Y-m-d')) }}" required
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('hire_date') border-red-300 @enderror">
                        @error('hire_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Рабочая информация -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                <h3 class="text-lg font-bold text-white">Рабочая информация</h3>
            </div>
            <div class="px-6 py-4 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="position" class="block text-sm font-medium text-gray-700 mb-1">Должность *</label>
                        <input type="text" name="position" id="position" 
                               value="{{ old('position', $employee->position) }}" required
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('position') border-red-300 @enderror">
                        @error('position')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="department" class="block text-sm font-medium text-gray-700 mb-1">Отдел *</label>
                        <input type="text" name="department" id="department" 
                               value="{{ old('department', $employee->department) }}" required
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('department') border-red-300 @enderror">
                        @error('department')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="employment_type" class="block text-sm font-medium text-gray-700 mb-1">Тип трудоустройства</label>
                        <select name="employment_type" id="employment_type" 
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach(\App\Models\Employee::getEmploymentTypes() as $key => $type)
                                <option value="{{ $key }}" {{ old('employment_type', $employee->employment_type) === $key ? 'selected' : '' }}>
                                    {{ $type }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label for="supervisor_id" class="block text-sm font-medium text-gray-700 mb-1">Руководитель</label>
                        <select name="supervisor_id" id="supervisor_id" 
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Без руководителя</option>
                            @foreach($supervisors as $supervisor)
                                <option value="{{ $supervisor->id }}" 
                                        {{ old('supervisor_id', $employee->supervisor_id) == $supervisor->id ? 'selected' : '' }}>
                                    {{ $supervisor->full_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('supervisor_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="salary" class="block text-sm font-medium text-gray-700 mb-1">Оклад</label>
                    <input type="number" name="salary" id="salary" step="0.01" min="0"
                           value="{{ old('salary', $employee->salary) }}"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('salary') border-red-300 @enderror">
                    @error('salary')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Контактная информация -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4">
                <h3 class="text-lg font-bold text-white">Контактная информация</h3>
            </div>
            <div class="px-6 py-4 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Телефон</label>
                        <input type="tel" name="phone" id="phone" 
                               value="{{ old('phone', $employee->phone) }}"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('phone') border-red-300 @enderror">
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" id="email" 
                               value="{{ old('email', $employee->email) }}"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('email') border-red-300 @enderror">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Адрес</label>
                    <textarea name="address" id="address" rows="3"
                              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('address') border-red-300 @enderror">{{ old('address', $employee->address) }}</textarea>
                    @error('address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Связь с пользователем -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-4">
                <h3 class="text-lg font-bold text-white">Пользователь системы</h3>
            </div>
            <div class="px-6 py-4">
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">Связанный пользователь</label>
                    <select name="user_id" id="user_id" 
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Не связан с пользователем</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" 
                                    {{ old('user_id', $employee->user_id) == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-500">Выберите пользователя, если сотрудник должен иметь доступ к системе</p>
                    @error('user_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Кнопки действий -->
        <div class="flex justify-end space-x-3">
            <a href="{{ route('admin.employees.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-medium text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 transition">
                Отмена
            </a>
            <button type="submit" 
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V6h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2h5v5.586l-1.293-1.293zM9 4a1 1 0 012 0v2H9V4z" />
                </svg>
                Сохранить изменения
            </button>
        </div>
    </form>
</div>
@endsection 