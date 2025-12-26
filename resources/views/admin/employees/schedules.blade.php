@extends('admin.layout')

@section('header', 'График работы сотрудника')

@section('content')
<div class="space-y-6">
    <!-- Верхняя панель с информацией о сотруднике -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="bg-gradient-to-r from-orange-600 to-red-600 px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <div class="h-12 w-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center text-white font-medium mr-4">
                        {{ mb_substr($employee->first_name, 0, 1) }}{{ mb_substr($employee->last_name, 0, 1) }}
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">{{ $employee->full_name }}</h3>
                        <p class="text-white/70 text-sm">{{ $employee->position }} • {{ $employee->department }}</p>
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
                    <a href="{{ route('admin.employees.show', $employee) }}" 
                       class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-white/20 active:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                        </svg>
                        Профиль
                    </a>
                    <a href="{{ route('admin.employees.timesheet', $employee) }}" 
                       class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-white/20 active:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                        </svg>
                        Табель
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Кнопка добавления нового графика -->
        <div class="px-6 py-3 bg-white border-b border-gray-100">
            <div class="flex justify-between items-center">
                <h4 class="text-lg font-medium text-gray-900">Графики работы</h4>
                <button onclick="openScheduleModal()" 
                        class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-orange-500 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Добавить график
                </button>
            </div>
        </div>
    </div>

    <!-- Текущий активный график -->
    @php
        $currentSchedule = $schedules->where('is_active', true)->first();
    @endphp
    
    @if($currentSchedule)
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="bg-green-500 px-6 py-3">
                <h4 class="text-lg font-medium text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Текущий график работы
                </h4>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <h5 class="text-sm font-medium text-gray-500 mb-2">Название графика</h5>
                        <p class="text-lg font-semibold text-gray-900">{{ $currentSchedule->name }}</p>
                    </div>
                    <div>
                        <h5 class="text-sm font-medium text-gray-500 mb-2">Тип графика</h5>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            {{ $scheduleTypes[$currentSchedule->type] ?? $currentSchedule->type }}
                        </span>
                    </div>
                    <div>
                        <h5 class="text-sm font-medium text-gray-500 mb-2">Период действия</h5>
                        <p class="text-sm text-gray-900">
                            с {{ \Carbon\Carbon::parse($currentSchedule->start_date)->format('d.m.Y') }}
                            @if($currentSchedule->end_date)
                                по {{ \Carbon\Carbon::parse($currentSchedule->end_date)->format('d.m.Y') }}
                            @else
                                <span class="text-green-600">(бессрочно)</span>
                            @endif
                        </p>
                    </div>
                </div>
                
                @if($currentSchedule->schedule_data)
                    <div class="mt-6">
                        <h5 class="text-sm font-medium text-gray-500 mb-3">Расписание по дням недели</h5>
                        <div class="grid grid-cols-7 gap-2">
                            @php
                                $scheduleData = json_decode($currentSchedule->schedule_data, true);
                                $days = ['monday' => 'Пн', 'tuesday' => 'Вт', 'wednesday' => 'Ср', 'thursday' => 'Чт', 'friday' => 'Пт', 'saturday' => 'Сб', 'sunday' => 'Вс'];
                            @endphp
                            @foreach($days as $day => $dayName)
                                <div class="text-center p-3 border rounded-lg {{ isset($scheduleData[$day]) && $scheduleData[$day]['is_working'] ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200' }}">
                                    <div class="text-sm font-medium text-gray-900">{{ $dayName }}</div>
                                    @if(isset($scheduleData[$day]) && $scheduleData[$day]['is_working'])
                                        <div class="text-xs text-green-600 mt-1">
                                            {{ $scheduleData[$day]['start_time'] ?? '09:00' }} - {{ $scheduleData[$day]['end_time'] ?? '18:00' }}
                                        </div>
                                        @if(isset($scheduleData[$day]['break_start']) && isset($scheduleData[$day]['break_end']))
                                            <div class="text-xs text-gray-500">
                                                Обед: {{ $scheduleData[$day]['break_start'] }} - {{ $scheduleData[$day]['break_end'] }}
                                            </div>
                                        @endif
                                    @else
                                        <div class="text-xs text-gray-400 mt-1">Выходной</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- История графиков -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-700 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Все графики работы
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            График
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Тип
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Период действия
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
                    @forelse($schedules as $schedule)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8">
                                        <div class="h-8 w-8 rounded-full {{ $schedule->is_active ? 'bg-green-500' : 'bg-gray-400' }} flex items-center justify-center">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $schedule->name }}</div>
                                        <div class="text-sm text-gray-500">Создан {{ \Carbon\Carbon::parse($schedule->created_at)->format('d.m.Y') }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                           {{ $schedule->type === 'standard' ? 'bg-blue-100 text-blue-800' : 
                                              ($schedule->type === 'flexible' ? 'bg-purple-100 text-purple-800' : 
                                               ($schedule->type === 'shift' ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-800')) }}">
                                    {{ $scheduleTypes[$schedule->type] ?? $schedule->type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    с {{ \Carbon\Carbon::parse($schedule->start_date)->format('d.m.Y') }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    @if($schedule->end_date)
                                        по {{ \Carbon\Carbon::parse($schedule->end_date)->format('d.m.Y') }}
                                    @else
                                        бессрочно
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($schedule->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        Активный
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Неактивный
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <button onclick="viewSchedule({{ $schedule->id }})" 
                                            class="text-blue-600 hover:text-blue-900 transition-colors" title="Просмотр">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                    @if(!$schedule->is_active)
                                        <button onclick="activateSchedule({{ $schedule->id }})" 
                                                class="text-green-600 hover:text-green-900 transition-colors" title="Активировать">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </button>
                                    @endif
                                    <button onclick="editSchedule({{ $schedule->id }})" 
                                            class="text-indigo-600 hover:text-indigo-900 transition-colors" title="Редактировать">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    @if(!$schedule->is_active)
                                        <button onclick="deleteSchedule({{ $schedule->id }})" 
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
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <p class="text-lg font-medium">Графики работы не найдены</p>
                                    <p class="text-sm">Создайте первый график работы для сотрудника</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Модальное окно создания/редактирования графика -->
<div id="scheduleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-4xl w-full p-6 max-h-screen overflow-y-auto">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Создание графика работы</h3>
            
            <form id="scheduleForm" method="POST" action="{{ route('admin.employees.store-schedule', $employee) }}">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Основная информация -->
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Название графика</label>
                            <input type="text" name="name" id="name" required
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm"
                                   placeholder="Например: Стандартный рабочий день">
                        </div>
                        
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Тип графика</label>
                            <select name="type" id="type" required onchange="toggleScheduleFields()"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm">
                                <option value="">Выберите тип</option>
                                @foreach($scheduleTypes as $key => $type)
                                    <option value="{{ $key }}">{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Дата начала</label>
                            <input type="date" name="start_date" id="start_date" required
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm">
                        </div>
                        
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Дата окончания</label>
                            <input type="date" name="end_date" id="end_date"
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm">
                            <p class="text-xs text-gray-500 mt-1">Оставьте пустым для бессрочного графика</p>
                        </div>
                        
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" id="is_active" value="1"
                                       class="rounded border-gray-300 text-orange-600 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                                <span class="ml-2 text-sm text-gray-700">Активировать график сразу</span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Расписание по дням недели -->
                    <div id="weeklySchedule" class="space-y-4" style="display: none;">
                        <h4 class="text-md font-medium text-gray-900">Расписание по дням недели</h4>
                        @php
                            $weekDays = [
                                'monday' => 'Понедельник',
                                'tuesday' => 'Вторник', 
                                'wednesday' => 'Среда',
                                'thursday' => 'Четверг',
                                'friday' => 'Пятница',
                                'saturday' => 'Суббота',
                                'sunday' => 'Воскресенье'
                            ];
                        @endphp
                        @foreach($weekDays as $day => $dayName)
                            <div class="border rounded-lg p-3">
                                <div class="flex items-center justify-between mb-2">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="schedule[{{ $day }}][is_working]" value="1"
                                               class="rounded border-gray-300 text-orange-600 shadow-sm focus:border-orange-500 focus:ring-orange-500"
                                               onchange="toggleDayFields('{{ $day }}')">
                                        <span class="ml-2 text-sm font-medium text-gray-700">{{ $dayName }}</span>
                                    </label>
                                </div>
                                <div id="{{ $day }}_fields" class="grid grid-cols-2 gap-2" style="display: none;">
                                    <div>
                                        <label class="block text-xs text-gray-500">Начало</label>
                                        <input type="time" name="schedule[{{ $day }}][start_time]" value="09:00"
                                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500">Окончание</label>
                                        <input type="time" name="schedule[{{ $day }}][end_time]" value="18:00"
                                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500">Обед с</label>
                                        <input type="time" name="schedule[{{ $day }}][break_start]" value="12:00"
                                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500">Обед до</label>
                                        <input type="time" name="schedule[{{ $day }}][break_end]" value="13:00"
                                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeScheduleModal()" 
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md">
                        Отмена
                    </button>
                    <button type="submit" 
                            class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-md">
                        Сохранить график
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openScheduleModal() {
    document.getElementById('scheduleModal').classList.remove('hidden');
}

function closeScheduleModal() {
    document.getElementById('scheduleModal').classList.add('hidden');
    document.getElementById('scheduleForm').reset();
    document.getElementById('weeklySchedule').style.display = 'none';
}

function toggleScheduleFields() {
    const type = document.getElementById('type').value;
    const weeklySchedule = document.getElementById('weeklySchedule');
    
    if (type === 'standard' || type === 'flexible') {
        weeklySchedule.style.display = 'block';
    } else {
        weeklySchedule.style.display = 'none';
    }
}

function toggleDayFields(day) {
    const checkbox = document.querySelector(`input[name="schedule[${day}][is_working]"]`);
    const fields = document.getElementById(`${day}_fields`);
    
    if (checkbox.checked) {
        fields.style.display = 'grid';
    } else {
        fields.style.display = 'none';
    }
}

function viewSchedule(scheduleId) {
    // Здесь можно добавить логику просмотра детального графика
    console.log('View schedule:', scheduleId);
}

function editSchedule(scheduleId) {
    // Здесь можно добавить логику редактирования графика
    console.log('Edit schedule:', scheduleId);
}

function activateSchedule(scheduleId) {
    if (confirm('Активировать этот график? Текущий активный график будет деактивирован.')) {
        fetch(`{{ route('admin.employees.schedules', $employee) }}/${scheduleId}/activate`, {
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
                alert(data.message || 'Ошибка при активации графика');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ошибка при активации графика');
        });
    }
}

function deleteSchedule(scheduleId) {
    if (confirm('Удалить этот график? Это действие нельзя отменить.')) {
        fetch(`{{ route('admin.employees.schedules', $employee) }}/${scheduleId}`, {
            method: 'DELETE',
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
                alert(data.message || 'Ошибка при удалении графика');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ошибка при удалении графика');
        });
    }
}

// Закрытие модального окна при клике вне его
document.getElementById('scheduleModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeScheduleModal();
    }
});

// Установка текущей даты по умолчанию
document.getElementById('start_date').value = new Date().toISOString().split('T')[0];
</script>
@endpush
@endsection 