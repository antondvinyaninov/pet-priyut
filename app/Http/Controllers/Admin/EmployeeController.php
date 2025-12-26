<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use App\Models\WorkSchedule;
use App\Models\TimeTracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class EmployeeController extends Controller
{
    /**
     * Отображение списка сотрудников
     */
    public function index(Request $request)
    {
        $query = Employee::with(['user', 'supervisor', 'subordinates']);
        
        // Поиск
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%")
                  ->orWhere('employee_number', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // Фильтр по статусу
        if ($request->filled('status')) {
            $query->where('is_active', $request->get('status') === 'active');
        }
        
        // Фильтр по отделу
        if ($request->filled('department')) {
            $query->where('department', $request->get('department'));
        }
        
        // Фильтр по типу трудоустройства
        if ($request->filled('employment_type')) {
            $query->where('employment_type', $request->get('employment_type'));
        }
        
        // Фильтр по руководителю
        if ($request->filled('supervisor')) {
            if ($request->get('supervisor') === 'none') {
                $query->whereNull('supervisor_id');
            } else {
                $query->where('supervisor_id', $request->get('supervisor'));
            }
        }
        
        $employees = $query->orderBy('last_name')->orderBy('first_name')->paginate(20);
        
        // Данные для фильтров
        $departments = Employee::getDepartments();
        $employmentTypes = Employee::getEmploymentTypes();
        $supervisors = Employee::supervisors()->active()->get();
        
        // Получаем все сотрудники для статистики (без пагинации)
        $allEmployees = Employee::all();
        
        return view('admin.employees.index', compact(
            'employees', 
            'departments', 
            'employmentTypes', 
            'supervisors',
            'allEmployees'
        ));
    }

    /**
     * Форма создания сотрудника
     */
    public function create()
    {
        $users = User::active()->whereDoesntHave('employee')->get();
        $supervisors = Employee::active()->get();
        $departments = Employee::getDepartments();
        $positions = Employee::getPositions();
        $employmentTypes = Employee::getEmploymentTypes();
        
        return view('admin.employees.create', compact(
            'users', 
            'supervisors', 
            'departments', 
            'positions', 
            'employmentTypes'
        ));
    }

    /**
     * Сохранение нового сотрудника
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_number' => 'required|string|max:50|unique:employees',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'position' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'supervisor_id' => 'nullable|exists:employees,id',
            'hire_date' => 'required|date',
            'termination_date' => 'nullable|date|after:hire_date',
            'employment_type' => 'required|in:' . implode(',', array_keys(Employee::getEmploymentTypes())),
            'salary' => 'nullable|numeric|min:0',
            'user_id' => 'nullable|exists:users,id|unique:employees',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:1000',
            'birth_date' => 'nullable|date|before:today',
            'passport_series' => 'nullable|string|max:10',
            'passport_number' => 'nullable|string|max:20',
            'inn' => 'nullable|string|max:12',
            'snils' => 'nullable|string|max:14',
            'notes' => 'nullable|string|max:2000',
            'is_active' => 'boolean'
        ]);

        $employee = Employee::create($request->all());

        // Создаем стандартный график работы
        if ($request->boolean('create_schedule', true)) {
            WorkSchedule::createStandardSchedule($employee->id);
        }

        return redirect()->route('admin.employees.index')
            ->with('success', 'Сотрудник успешно добавлен');
    }

    /**
     * Просмотр сотрудника
     */
    public function show(Request $request, Employee $employee)
    {
        $employee->load(['user', 'supervisor', 'subordinates', 'workSchedules', 'timeTracking']);
        
        // Получаем год и месяц из запроса или используем текущие
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);
        
        // Статистика за выбранный месяц
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        
        $stats = $employee->getTimeStats(
            $startDate->toDateString(),
            $endDate->toDateString()
        );
        
        // Активный график работы
        $activeSchedule = $employee->activeWorkSchedule();
        
        // Все графики работы
        $workSchedules = $employee->workSchedules()
            ->orderBy('start_date', 'desc')
            ->get();
        
        // Последние записи времени
        $recentTimeRecords = $employee->timeTracking()
            ->orderBy('work_date', 'desc')
            ->limit(10)
            ->get();
        
        // Данные для календаря табеля
        $calendar = $this->buildCalendar($employee, $year, $month);
        
        return view('admin.employees.show', compact(
            'employee', 
            'stats', 
            'activeSchedule', 
            'workSchedules',
            'recentTimeRecords',
            'calendar',
            'year',
            'month'
        ));
    }

    /**
     * Форма редактирования сотрудника
     */
    public function edit(Employee $employee)
    {
        $users = User::active()
            ->where(function($query) use ($employee) {
                $query->whereDoesntHave('employee')
                      ->orWhere('id', $employee->user_id);
            })
            ->get();
            
        $supervisors = Employee::active()
            ->where('id', '!=', $employee->id)
            ->get();
            
        $departments = Employee::getDepartments();
        $positions = Employee::getPositions();
        $employmentTypes = Employee::getEmploymentTypes();
        
        return view('admin.employees.edit', compact(
            'employee',
            'users', 
            'supervisors', 
            'departments', 
            'positions', 
            'employmentTypes'
        ));
    }

    /**
     * Обновление сотрудника
     */
    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'employee_number' => ['required', 'string', 'max:50', Rule::unique('employees')->ignore($employee->id)],
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'position' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'supervisor_id' => [
                'nullable',
                'exists:employees,id',
                function ($attribute, $value, $fail) use ($employee) {
                    if ($value == $employee->id) {
                        $fail('Сотрудник не может быть руководителем самого себя.');
                    }
                    
                    // Проверяем циклические зависимости
                    if ($value && $this->wouldCreateCycle($employee, $value)) {
                        $fail('Назначение этого руководителя создаст циклическую зависимость.');
                    }
                }
            ],
            'hire_date' => 'required|date',
            'termination_date' => 'nullable|date|after:hire_date',
            'employment_type' => 'required|in:' . implode(',', array_keys(Employee::getEmploymentTypes())),
            'salary' => 'nullable|numeric|min:0',
            'user_id' => [
                'nullable',
                'exists:users,id',
                Rule::unique('employees')->ignore($employee->id)
            ],
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:1000',
            'birth_date' => 'nullable|date|before:today',
            'passport_series' => 'nullable|string|max:10',
            'passport_number' => 'nullable|string|max:20',
            'inn' => 'nullable|string|max:12',
            'snils' => 'nullable|string|max:14',
            'notes' => 'nullable|string|max:2000',
            'is_active' => 'boolean'
        ]);

        $employee->update($request->all());

        return redirect()->route('admin.employees.index')
            ->with('success', 'Сотрудник успешно обновлен');
    }

    /**
     * Удаление сотрудника
     */
    public function destroy(Employee $employee)
    {
        // Проверяем, есть ли подчиненные
        if ($employee->subordinates()->count() > 0) {
            return redirect()->route('admin.employees.index')
                ->with('error', 'Нельзя удалить сотрудника, у которого есть подчиненные');
        }

        // Проверяем, есть ли записи времени
        if ($employee->timeTracking()->count() > 0) {
            return redirect()->route('admin.employees.index')
                ->with('error', 'Нельзя удалить сотрудника с записями учета времени');
        }

        $employee->delete();

        return redirect()->route('admin.employees.index')
            ->with('success', 'Сотрудник успешно удален');
    }

    /**
     * Переключение статуса сотрудника
     */
    public function toggleStatus(Employee $employee)
    {
        $employee->update(['is_active' => !$employee->is_active]);

        return response()->json([
            'success' => true,
            'status' => $employee->is_active,
            'message' => $employee->is_active ? 'Сотрудник активирован' : 'Сотрудник деактивирован'
        ]);
    }

    /**
     * Управление графиками работы
     */
    public function schedules(Employee $employee)
    {
        $schedules = $employee->workSchedules()->orderBy('start_date', 'desc')->get();
        $scheduleTypes = WorkSchedule::getScheduleTypes();
        
        return view('admin.employees.schedules', compact('employee', 'schedules', 'scheduleTypes'));
    }

    /**
     * Создание графика работы
     */
    public function storeSchedule(Request $request, Employee $employee)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', array_keys(WorkSchedule::getScheduleTypes())),
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'hours_per_week' => 'required|integer|min:1|max:168',
            'hours_per_day' => 'required|integer|min:1|max:24',
            'lunch_start' => 'nullable|date_format:H:i',
            'lunch_end' => 'nullable|date_format:H:i|after:lunch_start',
            'notes' => 'nullable|string|max:1000'
        ]);

        $scheduleData = $request->all();
        $scheduleData['employee_id'] = $employee->id;

        // Добавляем время работы для каждого дня недели
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        foreach ($days as $day) {
            $scheduleData[$day . '_start'] = $request->input($day . '_start');
            $scheduleData[$day . '_end'] = $request->input($day . '_end');
        }

        WorkSchedule::create($scheduleData);

        return redirect()->route('admin.employees.schedules', $employee)
            ->with('success', 'График работы успешно создан');
    }

    /**
     * Табель учета рабочего времени
     */
    public function timesheet(Request $request, Employee $employee)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);
        
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        
        // Получаем записи времени за месяц
        $timeRecords = $employee->timeTracking()
            ->forMonth($year, $month)
            ->orderBy('work_date')
            ->get()
            ->keyBy('work_date');
        
        // Создаем календарь месяца
        $calendar = [];
        $current = $startDate->copy();
        
        while ($current <= $endDate) {
            $dateString = $current->format('Y-m-d');
            $record = $timeRecords->get($dateString);
            
            $calendar[] = [
                'date' => $current->copy(),
                'record' => $record,
                'is_weekend' => $current->isWeekend(),
                'is_today' => $current->isToday()
            ];
            
            $current->addDay();
        }
        
        // Статистика за месяц
        $stats = $employee->getTimeStats($startDate->toDateString(), $endDate->toDateString());
        
        return view('admin.employees.timesheet', compact(
            'employee', 
            'calendar', 
            'stats', 
            'year', 
            'month'
        ));
    }

    /**
     * Отметка прихода/ухода
     */
    public function clockInOut(Request $request, Employee $employee)
    {
        $request->validate([
            'action' => 'required|in:clock_in,clock_out',
            'time' => 'nullable|date_format:H:i',
            'date' => 'nullable|date',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric'
        ]);

        $date = $request->get('date', now()->format('Y-m-d'));
        $time = $request->get('time', now()->format('H:i'));
        
        if ($request->action === 'clock_in') {
            $timeRecord = TimeTracking::clockIn(
                $employee->id,
                $time,
                $request->get('lat'),
                $request->get('lng')
            );
            $message = 'Приход отмечен';
        } else {
            $timeRecord = TimeTracking::where('employee_id', $employee->id)
                ->where('work_date', $date)
                ->first();
                
            if (!$timeRecord) {
                return response()->json(['error' => 'Запись о приходе не найдена'], 400);
            }
            
            $timeRecord->clockOut(
                $time,
                $request->get('lat'),
                $request->get('lng')
            );
            $message = 'Уход отмечен';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'record' => $timeRecord
        ]);
    }

    /**
     * Экспорт сотрудников в CSV
     */
    public function export(Request $request)
    {
        $query = Employee::with(['user', 'supervisor']);
        
        // Применяем фильтры
        if ($request->filled('status')) {
            $query->where('is_active', $request->get('status') === 'active');
        }
        
        if ($request->filled('department')) {
            $query->where('department', $request->get('department'));
        }
        
        $employees = $query->get();
        
        $filename = 'employees_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($employees) {
            $file = fopen('php://output', 'w');
            
            // Заголовки CSV
            fputcsv($file, [
                'Табельный номер',
                'ФИО',
                'Должность',
                'Отдел',
                'Руководитель',
                'Тип трудоустройства',
                'Дата приема',
                'Телефон',
                'Email',
                'Статус'
            ]);
            
            foreach ($employees as $employee) {
                fputcsv($file, [
                    $employee->employee_number,
                    $employee->full_name,
                    $employee->position,
                    $employee->department,
                    $employee->supervisor?->full_name ?? '',
                    Employee::getEmploymentTypes()[$employee->employment_type] ?? $employee->employment_type,
                    $employee->hire_date->format('d.m.Y'),
                    $employee->phone,
                    $employee->email,
                    $employee->is_active ? 'Активен' : 'Неактивен'
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Проверка на циклические зависимости в иерархии
     */
    private function wouldCreateCycle(Employee $employee, $supervisorId): bool
    {
        $supervisor = Employee::find($supervisorId);
        if (!$supervisor) {
            return false;
        }
        
        // Проверяем, не является ли текущий сотрудник руководителем предполагаемого руководителя
        $chain = $supervisor->getSupervisorChain();
        return $chain->contains('id', $employee->id);
    }

    /**
     * Построение календаря для табеля
     */
    private function buildCalendar(Employee $employee, int $year, int $month): array
    {
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        
        // Получаем записи времени за месяц
        $timeRecords = $employee->timeTracking()
            ->forMonth($year, $month)
            ->get()
            ->keyBy('work_date');
        
        $calendar = [];
        
        // Добавляем пустые дни в начале месяца
        $startOfWeek = $startDate->copy()->startOfWeek(Carbon::MONDAY);
        while ($startOfWeek->lt($startDate)) {
            $calendar[] = ['date' => null, 'record' => null];
            $startOfWeek->addDay();
        }
        
        // Добавляем дни месяца
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $dateString = $currentDate->format('Y-m-d');
            $calendar[] = [
                'date' => $currentDate->copy(),
                'record' => $timeRecords->get($dateString)
            ];
            $currentDate->addDay();
        }
        
        // Добавляем пустые дни в конце месяца до конца недели
        while (count($calendar) % 7 !== 0) {
            $calendar[] = ['date' => null, 'record' => null];
        }
        
        return $calendar;
    }

    /**
     * Общий табель учета времени всех сотрудников
     */
    public function generalTimesheet(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);
        
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        
        // Получаем всех активных сотрудников
        $employees = Employee::where('is_active', true)
            ->with(['timeTracking' => function($query) use ($year, $month) {
                $query->forMonth($year, $month);
            }])
            ->orderBy('department')
            ->orderBy('last_name')
            ->get();
        
        // Создаем календарь дней месяца
        $days = [];
        $current = $startDate->copy();
        while ($current <= $endDate) {
            $days[] = $current->copy();
            $current->addDay();
        }
        
        // Группируем сотрудников по отделам
        $employeesByDepartment = $employees->groupBy('department');
        
        // Общая статистика
        $totalStats = [
            'total_employees' => $employees->count(),
            'total_work_hours' => 0,
            'total_overtime' => 0,
            'total_late_minutes' => 0,
            'departments_count' => $employeesByDepartment->count()
        ];
        
        foreach ($employees as $employee) {
            $stats = $employee->getTimeStats($startDate->toDateString(), $endDate->toDateString());
            $totalStats['total_work_hours'] += $stats['total_work_hours'];
            $totalStats['total_overtime'] += $stats['total_overtime'];
            $totalStats['total_late_minutes'] += $stats['total_late_minutes'];
        }
        
        return view('admin.employees.general-timesheet', compact(
            'employees',
            'employeesByDepartment', 
            'days',
            'totalStats',
            'year', 
            'month',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Общие графики работы всех сотрудников
     */
    public function generalSchedules(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);
        
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        
        // Получаем всех активных сотрудников с их графиками
        $employees = Employee::where('is_active', true)
            ->with(['workSchedules' => function($query) use ($startDate, $endDate) {
                $query->where(function($q) use ($startDate, $endDate) {
                    $q->where('start_date', '<=', $endDate)
                      ->where(function($subQ) use ($startDate) {
                          $subQ->whereNull('end_date')
                               ->orWhere('end_date', '>=', $startDate);
                      });
                });
            }])
            ->orderBy('department')
            ->orderBy('last_name')
            ->get();
        
        // Создаем календарь дней месяца
        $days = [];
        $current = $startDate->copy();
        while ($current <= $endDate) {
            $days[] = $current->copy();
            $current->addDay();
        }
        
        // Группируем сотрудников по отделам
        $employeesByDepartment = $employees->groupBy('department');
        
        // Статистика по типам графиков
        $scheduleStats = [
            'total_employees' => $employees->count(),
            'with_schedules' => $employees->filter(function($emp) {
                return $emp->workSchedules->isNotEmpty();
            })->count(),
            'without_schedules' => 0,
            'schedule_types' => []
        ];
        
        $scheduleStats['without_schedules'] = $scheduleStats['total_employees'] - $scheduleStats['with_schedules'];
        
        // Подсчитываем типы графиков
        $scheduleTypes = [];
        foreach ($employees as $employee) {
            foreach ($employee->workSchedules as $schedule) {
                $type = $schedule->schedule_type;
                if (!isset($scheduleTypes[$type])) {
                    $scheduleTypes[$type] = 0;
                }
                $scheduleTypes[$type]++;
            }
        }
        $scheduleStats['schedule_types'] = $scheduleTypes;
        
        return view('admin.employees.general-schedules', compact(
            'employees',
            'employeesByDepartment', 
            'days',
            'scheduleStats',
            'year', 
            'month',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Экспорт общего табеля в CSV
     */
    public function exportTimesheet(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);
        
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        
        $employees = Employee::where('is_active', true)
            ->with(['timeTracking' => function($query) use ($year, $month) {
                $query->forMonth($year, $month);
            }])
            ->orderBy('department')
            ->orderBy('last_name')
            ->get();
        
        $filename = 'timesheet_' . $year . '_' . str_pad($month, 2, '0', STR_PAD_LEFT) . '_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($employees, $startDate, $endDate, $year, $month) {
            $file = fopen('php://output', 'w');
            
            // BOM для корректного отображения UTF-8 в Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Заголовки
            $headers = ['Отдел', 'ФИО', 'Должность'];
            
            // Добавляем дни месяца
            $current = $startDate->copy();
            while ($current <= $endDate) {
                $headers[] = $current->format('d.m');
                $current->addDay();
            }
            
            $headers = array_merge($headers, ['Всего часов', 'Сверхурочные', 'Опоздания (мин)']);
            fputcsv($file, $headers);
            
            foreach ($employees as $employee) {
                $row = [
                    $employee->department,
                    $employee->full_name,
                    $employee->position
                ];
                
                // Добавляем данные по дням
                $timeRecords = $employee->timeTracking->keyBy('work_date');
                $current = $startDate->copy();
                while ($current <= $endDate) {
                    $dateString = $current->format('Y-m-d');
                    $record = $timeRecords->get($dateString);
                    
                    if ($record && $record->clock_in_time && $record->clock_out_time) {
                        $row[] = $record->work_hours . 'ч';
                    } elseif ($record && $record->clock_in_time) {
                        $row[] = 'Не ушел';
                    } else {
                        $row[] = '-';
                    }
                    
                    $current->addDay();
                }
                
                // Статистика
                $stats = $employee->getTimeStats($startDate->toDateString(), $endDate->toDateString());
                $row[] = $stats['total_work_hours'] . 'ч';
                $row[] = $stats['total_overtime'] . 'ч';
                $row[] = $stats['total_late_minutes'] . 'мин';
                
                fputcsv($file, $row);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Экспорт общих графиков в CSV
     */
    public function exportSchedules(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);
        
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        
        $employees = Employee::where('is_active', true)
            ->with(['workSchedules' => function($query) use ($startDate, $endDate) {
                $query->where(function($q) use ($startDate, $endDate) {
                    $q->where('start_date', '<=', $endDate)
                      ->where(function($subQ) use ($startDate) {
                          $subQ->whereNull('end_date')
                               ->orWhere('end_date', '>=', $startDate);
                      });
                });
            }])
            ->orderBy('department')
            ->orderBy('last_name')
            ->get();
        
        $filename = 'schedules_' . $year . '_' . str_pad($month, 2, '0', STR_PAD_LEFT) . '_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($employees, $startDate, $endDate) {
            $file = fopen('php://output', 'w');
            
            // BOM для корректного отображения UTF-8 в Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Заголовки
            $headers = ['Отдел', 'ФИО', 'Должность'];
            
            // Добавляем дни месяца
            $current = $startDate->copy();
            while ($current <= $endDate) {
                $headers[] = $current->format('d.m') . ' (' . $current->locale('ru')->dayName . ')';
                $current->addDay();
            }
            
            $headers[] = 'Тип графика';
            fputcsv($file, $headers);
            
            foreach ($employees as $employee) {
                $row = [
                    $employee->department,
                    $employee->full_name,
                    $employee->position
                ];
                
                // Получаем активный график
                $activeSchedule = $employee->workSchedules->first();
                
                // Добавляем данные по дням
                $current = $startDate->copy();
                while ($current <= $endDate) {
                    if ($activeSchedule) {
                        $workTime = $activeSchedule->getWorkTimeForDate($current);
                        if ($workTime) {
                            $row[] = $workTime['start'] . '-' . $workTime['end'];
                        } else {
                            $row[] = 'Выходной';
                        }
                    } else {
                        $row[] = 'Не назначен';
                    }
                    
                    $current->addDay();
                }
                
                // Тип графика
                $row[] = $activeSchedule ? WorkSchedule::getScheduleTypes()[$activeSchedule->schedule_type] : 'Не назначен';
                
                fputcsv($file, $row);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
