@extends('admin.layout')

@section('header')
    <div class="bg-gradient-to-r from-purple-600 to-blue-600 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <div class="h-12 w-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center text-white font-medium mr-4">
                        {{ mb_substr($employee->first_name, 0, 1) }}{{ mb_substr($employee->last_name, 0, 1) }}
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold">{{ $employee->full_name }}</h1>
                        <p class="text-white/70">{{ $employee->position }} • {{ $employee->department }}</p>
                        <p class="text-white/70 text-sm">Табельный номер: {{ $employee->employee_number }}</p>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.employees.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-white/20 active:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                        </svg>
                        Назад к списку
                    </a>
                    <a href="{{ route('admin.employees.edit', $employee) }}" 
                       class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-white/20 active:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" clip-rule="evenodd" />
                        </svg>
                        Редактировать
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Навигация по месяцам -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-3 bg-white border-b border-gray-100">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.employees.show', ['employee' => $employee, 'year' => $year, 'month' => $month - 1 < 1 ? 12 : $month - 1, 'year' => $month - 1 < 1 ? $year - 1 : $year]) }}" 
                       class="text-gray-500 hover:text-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <h4 class="text-lg font-medium text-gray-900">
                        {{ \Carbon\Carbon::create($year, $month)->locale('ru')->isoFormat('MMMM YYYY') }}
                    </h4>
                    <a href="{{ route('admin.employees.show', ['employee' => $employee, 'year' => $year, 'month' => $month + 1 > 12 ? 1 : $month + 1, 'year' => $month + 1 > 12 ? $year + 1 : $year]) }}" 
                       class="text-gray-500 hover:text-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
                <div class="flex items-center space-x-2">
                    <button onclick="showTimesheet()" 
                            id="timesheetBtn"
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                        </svg>
                        Табель
                    </button>
                    <button onclick="showSchedules()" 
                            id="schedulesBtn"
                            class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-medium text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" clip-rule="evenodd" />
                        </svg>
                        Графики
                    </button>
                    <button onclick="clockInOut()" 
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                        </svg>
                        Отметить время
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Статистика за месяц -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="bg-blue-500 px-4 py-2">
                <h4 class="text-sm font-medium text-white">Отработано часов</h4>
            </div>
            <div class="px-4 py-3">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_hours'] ?? 0 }}</p>
                        <p class="text-sm text-gray-500">из {{ $stats['expected_hours'] ?? 0 }} ожидаемых</p>
                    </div>
                    <div class="h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="bg-green-500 px-4 py-2">
                <h4 class="text-sm font-medium text-white">Рабочих дней</h4>
            </div>
            <div class="px-4 py-3">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['work_days'] ?? 0 }}</p>
                        <p class="text-sm text-gray-500">из {{ $stats['expected_days'] ?? 0 }} ожидаемых</p>
                    </div>
                    <div class="h-10 w-10 bg-green-100 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="bg-red-500 px-4 py-2">
                <h4 class="text-sm font-medium text-white">Опоздания</h4>
            </div>
            <div class="px-4 py-3">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['late_days'] ?? 0 }}</p>
                        <p class="text-sm text-gray-500">дней</p>
                    </div>
                    <div class="h-10 w-10 bg-red-100 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="bg-purple-500 px-4 py-2">
                <h4 class="text-sm font-medium text-white">Сверхурочные</h4>
            </div>
            <div class="px-4 py-3">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['overtime_hours'] ?? 0 }}</p>
                        <p class="text-sm text-gray-500">часов</p>
                    </div>
                    <div class="h-10 w-10 bg-purple-100 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Табель учета времени -->
    <div id="timesheetSection" class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-700 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Календарь рабочего времени
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-7 gap-1 mb-4">
                <!-- Заголовки дней недели -->
                @foreach(['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'] as $day)
                    <div class="p-2 text-center text-sm font-medium text-gray-500 bg-gray-50 rounded">
                        {{ $day }}
                    </div>
                @endforeach
            </div>
            
            <div class="grid grid-cols-7 gap-1">
                @foreach($calendar as $day)
                    @if($day['date'])
                        @php
                            $timeRecord = $day['record'];
                            $isToday = $day['date']->isToday();
                            $isWeekend = $day['date']->isWeekend();
                            $hasRecord = !is_null($timeRecord);
                        @endphp
                        
                        <div class="relative p-2 min-h-[80px] border rounded-lg transition-colors
                                    {{ $isToday ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }}
                                    {{ $isWeekend ? 'bg-gray-50' : 'bg-white' }}
                                    {{ $hasRecord ? 'hover:bg-gray-50' : '' }}">
                            
                            <!-- Число -->
                            <div class="text-sm font-medium {{ $isToday ? 'text-blue-600' : ($isWeekend ? 'text-gray-400' : 'text-gray-900') }}">
                                {{ $day['date']->day }}
                            </div>
                            
                            @if($hasRecord)
                                <!-- Время прихода -->
                                @if($timeRecord->clock_in)
                                    <div class="text-xs text-green-600 font-medium">
                                        {{ \Carbon\Carbon::parse($timeRecord->clock_in)->format('H:i') }}
                                    </div>
                                @endif
                                
                                <!-- Время ухода -->
                                @if($timeRecord->clock_out)
                                    <div class="text-xs text-red-600 font-medium">
                                        {{ \Carbon\Carbon::parse($timeRecord->clock_out)->format('H:i') }}
                                    </div>
                                @endif
                                
                                <!-- Общее время -->
                                @if($timeRecord->total_hours)
                                    <div class="text-xs text-gray-600 mt-1">
                                        {{ number_format($timeRecord->total_hours, 1) }}ч
                                    </div>
                                @endif
                                
                                <!-- Индикаторы -->
                                <div class="absolute top-1 right-1 flex space-x-1">
                                    @if($timeRecord->is_late)
                                        <div class="w-2 h-2 bg-red-500 rounded-full" title="Опоздание"></div>
                                    @endif
                                    @if($timeRecord->overtime_hours > 0)
                                        <div class="w-2 h-2 bg-purple-500 rounded-full" title="Сверхурочные"></div>
                                    @endif
                                </div>
                            @elseif(!$isWeekend && $day['date']->isPast())
                                <!-- Пропуск рабочего дня -->
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="w-6 h-6 bg-red-100 rounded-full flex items-center justify-center">
                                        <svg class="w-3 h-3 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <!-- Пустая ячейка для дней предыдущего/следующего месяца -->
                        <div class="p-2 min-h-[80px]"></div>
                    @endif
                @endforeach
            </div>
        </div>
        
        <!-- Легенда -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <div class="flex flex-wrap gap-4 text-xs">
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                    <span class="text-gray-600">Время прихода</span>
                </div>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                    <span class="text-gray-600">Время ухода / Опоздание</span>
                </div>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-purple-500 rounded-full mr-2"></div>
                    <span class="text-gray-600">Сверхурочные</span>
                </div>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                    <span class="text-gray-600">Сегодня</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Графики работы -->
    <div id="schedulesSection" class="bg-white shadow rounded-lg overflow-hidden hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-700 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Графики работы
                </h3>
                <button onclick="showScheduleForm()" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                    Добавить график
                </button>
            </div>
        </div>
        <div class="p-6">
            @if($workSchedules->count() > 0)
                <div class="space-y-4">
                    @foreach($workSchedules as $schedule)
                        <div class="border border-gray-200 rounded-lg p-4 {{ $schedule->is_active ? 'bg-green-50 border-green-200' : '' }}">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2">
                                        <h4 class="text-lg font-medium text-gray-900">{{ $schedule->name }}</h4>
                                        @if($schedule->is_active)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Активный
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1">
                                        {{ $schedule->hours_per_week }} ч/неделя • {{ $schedule->hours_per_day }} ч/день
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{ $schedule->start_date->format('d.m.Y') }} 
                                        @if($schedule->end_date)
                                            - {{ $schedule->end_date->format('d.m.Y') }}
                                        @else
                                            - по настоящее время
                                        @endif
                                    </p>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-gray-500 space-y-1">
                                        @php
                                            $days = ['monday' => 'Пн', 'tuesday' => 'Вт', 'wednesday' => 'Ср', 'thursday' => 'Чт', 'friday' => 'Пт', 'saturday' => 'Сб', 'sunday' => 'Вс'];
                                        @endphp
                                        @foreach($days as $day => $label)
                                            @if($schedule->{$day . '_start'} && $schedule->{$day . '_end'})
                                                <div class="flex justify-between space-x-2">
                                                    <span>{{ $label }}</span>
                                                    <span>{{ $schedule->{$day . '_start'} }} - {{ $schedule->{$day . '_end'} }}</span>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @if($schedule->notes)
                                <p class="text-sm text-gray-600 mt-2">{{ $schedule->notes }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Нет графиков работы</h3>
                    <p class="mt-1 text-sm text-gray-500">Создайте первый график работы для сотрудника.</p>
                    <div class="mt-6">
                        <button onclick="showScheduleForm()" 
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Добавить график
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Модальное окно отметки времени -->
<div id="clockModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Отметка времени</h3>
            <p class="text-sm text-gray-500 mb-6">Выберите действие для отметки рабочего времени:</p>
            <div class="flex justify-end space-x-3">
                <button onclick="closeClockModal()" 
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md">
                    Отмена
                </button>
                <button onclick="submitClockInOut('in')" 
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md">
                    Приход
                </button>
                <button onclick="submitClockInOut('out')" 
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md">
                    Уход
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Переключение между табелем и графиками
function showTimesheet() {
    document.getElementById('timesheetSection').classList.remove('hidden');
    document.getElementById('schedulesSection').classList.add('hidden');
    
    document.getElementById('timesheetBtn').classList.remove('bg-gray-300', 'text-gray-700');
    document.getElementById('timesheetBtn').classList.add('bg-green-600', 'text-white');
    
    document.getElementById('schedulesBtn').classList.remove('bg-orange-600', 'text-white');
    document.getElementById('schedulesBtn').classList.add('bg-gray-300', 'text-gray-700');
}

function showSchedules() {
    document.getElementById('timesheetSection').classList.add('hidden');
    document.getElementById('schedulesSection').classList.remove('hidden');
    
    document.getElementById('timesheetBtn').classList.remove('bg-green-600', 'text-white');
    document.getElementById('timesheetBtn').classList.add('bg-gray-300', 'text-gray-700');
    
    document.getElementById('schedulesBtn').classList.remove('bg-gray-300', 'text-gray-700');
    document.getElementById('schedulesBtn').classList.add('bg-orange-600', 'text-white');
}

// Отметка времени
function clockInOut() {
    document.getElementById('clockModal').classList.remove('hidden');
}

function closeClockModal() {
    document.getElementById('clockModal').classList.add('hidden');
}

function submitClockInOut(type) {
    fetch(`{{ route('admin.employees.clock-in-out', $employee) }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            action: type === 'in' ? 'clock_in' : 'clock_out'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Ошибка при отметке времени');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ошибка при отметке времени');
    })
    .finally(() => {
        closeClockModal();
    });
}

// Добавление графика работы
function showScheduleForm() {
    window.location.href = `{{ route('admin.employees.schedules', $employee) }}`;
}

// Закрытие модального окна при клике вне его
document.getElementById('clockModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeClockModal();
    }
});
</script>
@endpush
@endsection 