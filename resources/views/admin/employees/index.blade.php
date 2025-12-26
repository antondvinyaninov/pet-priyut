@extends('admin.layout')

@section('header', 'Сотрудники')

@section('content')
<div class="space-y-6">
    <!-- Верхняя панель с заголовком и кнопкой добавления -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="bg-gradient-to-r from-purple-600 to-indigo-600 px-6 py-4">
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-bold text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Управление сотрудниками
                </h3>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.employees.general-timesheet') }}" 
                       class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-white/20 active:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                        </svg>
                        Табель
                    </a>
                    <a href="{{ route('admin.employees.general-schedules') }}" 
                       class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-white/20 active:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                        </svg>
                        График работы
                    </a>
                    <a href="{{ route('admin.employees.export', request()->query()) }}" 
                       class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-white/20 active:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Экспорт
                    </a>
                    <a href="{{ route('admin.employees.create') }}" 
                       class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-white/20 active:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Добавить сотрудника
                    </a>
                </div>
            </div>
            <div class="mt-2">
                <p class="text-white/70 text-sm">Управление сотрудниками, их графиками работы и табелем учета времени</p>
            </div>
        </div>
    
        <!-- Счетчики статусов -->
        <div class="px-6 py-3 bg-white border-b border-gray-100">
            <div class="flex flex-wrap gap-2">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    Активные: {{ $allEmployees->where('is_active', true)->count() }}
                </span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    Неактивные: {{ $allEmployees->where('is_active', false)->count() }}
                </span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    Пользователи системы: {{ $allEmployees->whereNotNull('user_id')->count() }}
                </span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                    Всего: {{ $allEmployees->count() }}
                </span>
            </div>
        </div>
    </div>
    
    <!-- Фильтры в выдвижной панели -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 flex justify-between items-center border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-700 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Фильтры и поиск
            </h3>
            <button type="button" id="toggleFilters" class="text-gray-500 hover:text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
        <div id="filterPanel" class="px-6 py-4 border-b border-gray-200 {{ request()->hasAny(['search', 'status', 'department', 'employment_type', 'supervisor']) ? '' : 'hidden' }}">
            <form method="GET" action="{{ route('admin.employees.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Поиск -->
                <div class="md:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Поиск</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" 
                           placeholder="Поиск по ФИО, табельному номеру, должности..."
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
                
                <!-- Статус -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Статус</label>
                    <select name="status" id="status" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">Все статусы</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Активные</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Неактивные</option>
                    </select>
                </div>
                
                <!-- Отдел -->
                <div>
                    <label for="department" class="block text-sm font-medium text-gray-700 mb-1">Отдел</label>
                    <select name="department" id="department" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">Все отделы</option>
                        @foreach($departments as $department)
                            <option value="{{ $department }}" {{ request('department') === $department ? 'selected' : '' }}>
                                {{ $department }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Тип трудоустройства -->
                <div>
                    <label for="employment_type" class="block text-sm font-medium text-gray-700 mb-1">Тип трудоустройства</label>
                    <select name="employment_type" id="employment_type" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">Все типы</option>
                        @foreach($employmentTypes as $key => $type)
                            <option value="{{ $key }}" {{ request('employment_type') === $key ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Руководитель -->
                <div>
                    <label for="supervisor" class="block text-sm font-medium text-gray-700 mb-1">Руководитель</label>
                    <select name="supervisor" id="supervisor" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">Все</option>
                        <option value="none" {{ request('supervisor') === 'none' ? 'selected' : '' }}>Без руководителя</option>
                        @foreach($supervisors as $supervisor)
                            <option value="{{ $supervisor->id }}" {{ request('supervisor') == $supervisor->id ? 'selected' : '' }}>
                                {{ $supervisor->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-span-1 md:col-span-3 flex justify-end space-x-2">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                        Найти
                    </button>
                    <a href="{{ route('admin.employees.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 font-medium text-xs uppercase tracking-widest transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                        </svg>
                        Сбросить
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Карточки статистики -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="bg-purple-500 px-4 py-2">
                <h4 class="text-sm font-medium text-white">Общая статистика</h4>
            </div>
            <div class="px-4 py-3">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-500">Всего сотрудников</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $allEmployees->count() }}</p>
                    </div>
                    <div class="h-10 w-10 bg-purple-100 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-2">
                    <div class="bg-purple-50 rounded-md p-2">
                        <p class="text-xs text-gray-500">Активные</p>
                        <p class="text-lg font-semibold text-purple-600">{{ $allEmployees->where('is_active', true)->count() }}</p>
                    </div>
                    <div class="bg-purple-50 rounded-md p-2">
                        <p class="text-xs text-gray-500">Неактивные</p>
                        <p class="text-lg font-semibold text-purple-600">{{ $allEmployees->where('is_active', false)->count() }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="bg-blue-500 px-4 py-2">
                <h4 class="text-sm font-medium text-white">По отделам</h4>
            </div>
            <div class="px-4 py-3">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-500">Отделов</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ count($departments) }}</p>
                    </div>
                    <div class="h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 space-y-1">
                    @foreach(array_slice($departments, 0, 3) as $department)
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-500">{{ $department }}</span>
                            <span class="font-medium text-blue-600">{{ $allEmployees->where('department', $department)->count() }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="bg-green-500 px-4 py-2">
                <h4 class="text-sm font-medium text-white">Система</h4>
            </div>
            <div class="px-4 py-3">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-500">Пользователи системы</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $allEmployees->whereNotNull('user_id')->count() }}</p>
                    </div>
                    <div class="h-10 w-10 bg-green-100 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-2">
                    <div class="bg-green-50 rounded-md p-2">
                        <p class="text-xs text-gray-500">Полная занятость</p>
                        <p class="text-lg font-semibold text-green-600">{{ $allEmployees->where('employment_type', 'full_time')->count() }}</p>
                    </div>
                    <div class="bg-green-50 rounded-md p-2">
                        <p class="text-xs text-gray-500">Стажеры</p>
                        <p class="text-lg font-semibold text-green-600">{{ $allEmployees->where('employment_type', 'intern')->count() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Таблица сотрудников -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-700 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Список сотрудников
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Сотрудник
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Должность
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Отдел
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Контакты
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Статус
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Действия
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($employees as $employee)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-r from-purple-500 to-indigo-500 flex items-center justify-center text-white font-medium">
                                            {{ mb_substr($employee->first_name, 0, 1) }}{{ mb_substr($employee->last_name, 0, 1) }}
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $employee->full_name }}</div>
                                        <div class="text-sm text-gray-500">№{{ $employee->employee_number }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $employee->position }}</div>
                                <div class="text-sm text-gray-500">{{ $employmentTypes[$employee->employment_type] ?? $employee->employment_type }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $employee->department }}</div>
                                <div class="text-sm text-gray-500">Стаж: {{ $employee->work_experience }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $employee->phone ?? '—' }}</div>
                                <div class="text-sm text-gray-500">{{ $employee->email ?? '—' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col space-y-1">
                                    <button onclick="toggleEmployeeStatus({{ $employee->id }})" 
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium cursor-pointer transition-colors
                                                   {{ $employee->is_active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200' }}">
                                        {{ $employee->is_active ? 'Активен' : 'Неактивен' }}
                                    </button>
                                    @if($employee->user)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            Пользователь системы
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <a href="{{ route('admin.employees.timesheet', $employee) }}" 
                                       class="text-green-600 hover:text-green-900 transition-colors" title="Табель">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.employees.schedules', $employee) }}" 
                                       class="text-orange-600 hover:text-orange-900 transition-colors" title="График работы">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.employees.show', $employee) }}" 
                                       class="text-blue-600 hover:text-blue-900 transition-colors" title="Просмотр">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.employees.edit', $employee) }}" 
                                       class="text-indigo-600 hover:text-indigo-900 transition-colors" title="Редактировать">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    @if($employee->subordinates()->count() === 0 && $employee->timeTracking()->count() === 0)
                                        <button onclick="deleteEmployee({{ $employee->id }})" 
                                                class="text-red-600 hover:text-red-900 transition-colors" title="Удалить">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    <p class="text-lg font-medium">Сотрудники не найдены</p>
                                    <p class="text-sm">Попробуйте изменить параметры поиска</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Пагинация -->
        @if($employees->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200">
                {{ $employees->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Модальное окно подтверждения удаления -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Подтверждение удаления</h3>
            <p class="text-sm text-gray-500 mb-6">Вы уверены, что хотите удалить этого сотрудника? Это действие нельзя отменить.</p>
            <div class="flex justify-end space-x-3">
                <button onclick="closeDeleteModal()" 
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md">
                    Отмена
                </button>
                <button id="confirmDelete" 
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md">
                    Удалить
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let employeeToDelete = null;

// Переключение панели фильтров
document.getElementById('toggleFilters').addEventListener('click', function() {
    const panel = document.getElementById('filterPanel');
    const icon = this.querySelector('svg');
    
    if (panel.classList.contains('hidden')) {
        panel.classList.remove('hidden');
        icon.style.transform = 'rotate(180deg)';
    } else {
        panel.classList.add('hidden');
        icon.style.transform = 'rotate(0deg)';
    }
});

function toggleEmployeeStatus(employeeId) {
    fetch(`/admin/employees/${employeeId}/toggle-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Ошибка при изменении статуса');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ошибка при изменении статуса');
    });
}

function deleteEmployee(employeeId) {
    employeeToDelete = employeeId;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    employeeToDelete = null;
    document.getElementById('deleteModal').classList.add('hidden');
}

document.getElementById('confirmDelete').addEventListener('click', function() {
    if (employeeToDelete) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/employees/${employeeToDelete}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
});

// Закрытие модального окна при клике вне его
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});
</script>
@endpush
@endsection 