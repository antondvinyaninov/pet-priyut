@extends('admin.layout')

@section('header')
    <div class="bg-gradient-to-r from-purple-600 to-blue-600 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    <div>
                        <h1 class="text-2xl font-bold">Общий табель учета времени</h1>
                        <p class="text-white/70">{{ $startDate->locale('ru')->monthName }} {{ $year }} года</p>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.employees.export-timesheet', ['year' => $year, 'month' => $month]) }}" 
                       class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-white/20 active:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Экспорт CSV
                    </a>
                    <a href="{{ route('admin.employees.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-white/20 active:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                        </svg>
                        Назад к списку
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
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-700">Выбор периода</h3>
                <div class="flex space-x-4">
                    <form method="GET" class="flex items-center space-x-2">
                        <select name="month" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create(null, $m, 1)->locale('ru')->monthName }}
                                </option>
                            @endfor
                        </select>
                        <select name="year" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                        <button type="submit" class="inline-flex items-center px-3 py-2 bg-indigo-600 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                            Показать
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Общая статистика -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-8 w-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Всего сотрудников</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $totalStats['total_employees'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Всего часов</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($totalStats['total_work_hours'], 1) }}ч</p>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-8 w-8 bg-orange-100 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Сверхурочные</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($totalStats['total_overtime'], 1) }}ч</p>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-8 w-8 bg-red-100 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Опоздания</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $totalStats['total_late_minutes'] }}мин</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Табель по отделам -->
    @foreach($employeesByDepartment as $department => $departmentEmployees)
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="bg-gradient-to-r from-gray-600 to-gray-700 px-6 py-4">
                <h3 class="text-lg font-bold text-white">{{ $department }}</h3>
                <p class="text-white/70 text-sm">{{ $departmentEmployees->count() }} сотрудников</p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="sticky left-0 bg-gray-50 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">
                                Сотрудник
                            </th>
                            @foreach($days as $day)
                                <th class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[60px] {{ $day->isWeekend() ? 'bg-red-50' : '' }}">
                                    <div>{{ $day->format('d') }}</div>
                                    <div class="text-xs text-gray-400">{{ $day->locale('ru')->shortDayName }}</div>
                                </th>
                            @endforeach
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider bg-blue-50">
                                Итого
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($departmentEmployees as $employee)
                            @php
                                $timeRecords = $employee->timeTracking->keyBy('work_date');
                                $stats = $employee->getTimeStats($startDate->toDateString(), $endDate->toDateString());
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="sticky left-0 bg-white px-6 py-4 whitespace-nowrap border-r border-gray-200">
                                    <div class="flex items-center">
                                        <div class="h-8 w-8 rounded-full bg-gradient-to-r from-purple-400 to-pink-400 flex items-center justify-center text-white font-medium text-sm mr-3">
                                            {{ mb_substr($employee->first_name, 0, 1) }}{{ mb_substr($employee->last_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $employee->full_name }}</div>
                                            <div class="text-sm text-gray-500">{{ $employee->position }}</div>
                                        </div>
                                    </div>
                                </td>
                                @foreach($days as $day)
                                    @php
                                        $dateString = $day->format('Y-m-d');
                                        $record = $timeRecords->get($dateString);
                                    @endphp
                                    <td class="px-2 py-4 text-center text-xs {{ $day->isWeekend() ? 'bg-red-50' : '' }}">
                                        @if($record)
                                            @if($record->clock_in_time && $record->clock_out_time)
                                                <div class="bg-green-100 text-green-800 rounded px-1 py-1">
                                                    <div>{{ $record->work_hours }}ч</div>
                                                    @if($record->late_minutes > 0)
                                                        <div class="text-red-600">+{{ $record->late_minutes }}мин</div>
                                                    @endif
                                                </div>
                                            @elseif($record->clock_in_time)
                                                <div class="bg-yellow-100 text-yellow-800 rounded px-1 py-1">
                                                    <div>{{ $record->clock_in_time }}</div>
                                                    <div>Не ушел</div>
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="px-6 py-4 text-center bg-blue-50">
                                    <div class="text-sm font-medium text-blue-900">{{ number_format($stats['total_work_hours'], 1) }}ч</div>
                                    @if($stats['total_overtime'] > 0)
                                        <div class="text-xs text-orange-600">+{{ number_format($stats['total_overtime'], 1) }}ч</div>
                                    @endif
                                    @if($stats['total_late_minutes'] > 0)
                                        <div class="text-xs text-red-600">{{ $stats['total_late_minutes'] }}мин</div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

    @if($employees->isEmpty())
        <div class="bg-white shadow rounded-lg p-8 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Нет активных сотрудников</h3>
            <p class="text-gray-500">В системе нет активных сотрудников для отображения табеля.</p>
        </div>
    @endif
</div>
@endsection 