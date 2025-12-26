<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployeeTask;
use App\Models\User;
use App\Models\Employee;
use App\Models\OsvvRequest;
use App\Models\Animal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class EmployeeTaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = EmployeeTask::with(['assignedTo', 'createdBy', 'assignedBy', 'osvvRequest', 'animal', 'employee', 'departurePlan']);
        
        // Фильтрация по пользователю (назначенному)
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }
        
        // Фильтрация по статусу
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Фильтрация по приоритету
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        
        // Фильтрация по типу
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        // Фильтрация по дате
        if ($request->filled('due_date_from')) {
            $query->where('due_date', '>=', $request->due_date_from);
        }
        
        if ($request->filled('due_date_to')) {
            $query->where('due_date', '<=', $request->due_date_to);
        }
        
        // Поиск по названию
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        
        // Сортировка
        $sortBy = $request->get('sort', 'created_at');
        $sortDir = $request->get('direction', 'desc');
        $query->orderBy($sortBy, $sortDir);
        
        $tasks = $query->paginate(20);
        
        // Данные для фильтров
        $users = User::select('id', 'name')->get();
        $statuses = EmployeeTask::STATUSES;
        $priorities = EmployeeTask::PRIORITIES;
        $types = EmployeeTask::TYPES;
        
        // Статистика
        $stats = [
            'total' => EmployeeTask::count(),
            'pending' => EmployeeTask::where('status', EmployeeTask::STATUS_PENDING)->count(),
            'in_progress' => EmployeeTask::where('status', EmployeeTask::STATUS_IN_PROGRESS)->count(),
            'completed' => EmployeeTask::where('status', EmployeeTask::STATUS_COMPLETED)->count(),
            'overdue' => EmployeeTask::overdue()->count(),
            'due_soon' => EmployeeTask::dueSoon()->count(),
        ];
        
        return view('admin.tasks.index', compact('tasks', 'users', 'statuses', 'priorities', 'types', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::select('id', 'name')->get();
        $employees = Employee::with('user')->get();
        $osvvRequests = OsvvRequest::whereNotIn('status', ['completed', 'cancelled'])->get();
        $animals = Animal::where('status', 'active')->get();
        
        return view('admin.tasks.create', compact('users', 'employees', 'osvvRequests', 'animals'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => ['required', Rule::in(array_keys(EmployeeTask::PRIORITIES))],
            'type' => ['required', Rule::in(array_keys(EmployeeTask::TYPES))],
            'assigned_to' => 'required|exists:users,id',
            'assigned_by' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date|after:now',
            'estimated_hours' => 'nullable|integer|min:1|max:168',
            'notes' => 'nullable|string',
            'tags' => 'nullable|string',
            'osvv_request_id' => 'nullable|exists:osvv_requests,id',
            'animal_id' => 'nullable|exists:animals,id',
            'employee_id' => 'nullable|exists:employees,id',
        ]);
        
        // Обработка тегов
        if ($validated['tags']) {
            $validated['tags'] = array_map('trim', explode(',', $validated['tags']));
        }
        
        $validated['created_by'] = Auth::id();
        $validated['assigned_by'] = $validated['assigned_by'] ?? Auth::id();
        
        $task = EmployeeTask::create($validated);
        
        return redirect()->route('admin.tasks.index')
            ->with('success', 'Задача успешно создана');
    }

    /**
     * Display the specified resource.
     */
    public function show(EmployeeTask $task)
    {
        $task->load(['assignedTo', 'createdBy', 'assignedBy', 'osvvRequest', 'animal', 'employee', 'departurePlan']);
        
        return view('admin.tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmployeeTask $task)
    {
        $task->load(['assignedTo', 'createdBy', 'assignedBy', 'osvvRequest', 'animal', 'employee', 'departurePlan']);
        
        $users = User::select('id', 'name')->get();
        $employees = Employee::with('user')->get();
        $osvvRequests = OsvvRequest::whereNotIn('status', ['completed', 'cancelled'])->get();
        $animals = Animal::where('status', 'active')->get();
        
        return view('admin.tasks.edit', compact('task', 'users', 'employees', 'osvvRequests', 'animals'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmployeeTask $task)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => ['required', Rule::in(array_keys(EmployeeTask::PRIORITIES))],
            'type' => ['required', Rule::in(array_keys(EmployeeTask::TYPES))],
            'assigned_to' => 'required|exists:users,id',
            'assigned_by' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|integer|min:1|max:168',
            'notes' => 'nullable|string',
            'tags' => 'nullable|string',
            'osvv_request_id' => 'nullable|exists:osvv_requests,id',
            'animal_id' => 'nullable|exists:animals,id',
            'employee_id' => 'nullable|exists:employees,id',
        ]);
        
        // Обработка тегов
        if ($validated['tags']) {
            $validated['tags'] = array_map('trim', explode(',', $validated['tags']));
        }
        
        $task->update($validated);
        
        return redirect()->route('admin.tasks.show', $task)
            ->with('success', 'Задача успешно обновлена');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmployeeTask $task)
    {
        $task->delete();
        
        return redirect()->route('admin.tasks.index')
            ->with('success', 'Задача удалена');
    }

    /**
     * Начать выполнение задачи
     */
    public function start(EmployeeTask $task)
    {
        if (!$task->canStart()) {
            return redirect()->back()
                ->with('error', 'Задача не может быть начата');
        }
        
        $task->start();
        
        return redirect()->back()
            ->with('success', 'Задача взята в работу');
    }

    /**
     * Завершить задачу
     */
    public function complete(Request $request, EmployeeTask $task)
    {
        if (!$task->canComplete()) {
            return redirect()->back()
                ->with('error', 'Задача не может быть завершена');
        }
        
        $validated = $request->validate([
            'completion_notes' => 'nullable|string',
            'actual_hours' => 'nullable|integer|min:1|max:168',
        ]);
        
        $task->complete(
            $validated['completion_notes'] ?? null,
            $validated['actual_hours'] ?? null
        );
        
        return redirect()->back()
            ->with('success', 'Задача завершена');
    }

    /**
     * Отменить задачу
     */
    public function cancel(EmployeeTask $task)
    {
        if (!$task->canCancel()) {
            return redirect()->back()
                ->with('error', 'Задача не может быть отменена');
        }
        
        $task->cancel();
        
        return redirect()->back()
            ->with('success', 'Задача отменена');
    }

    /**
     * Мои задачи (для текущего пользователя)
     */
    public function myTasks(Request $request)
    {
        $query = EmployeeTask::forUser(Auth::id())
            ->with(['createdBy', 'assignedBy', 'osvvRequest', 'animal', 'employee', 'departurePlan']);
        
        // Фильтрация по статусу
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Фильтрация по приоритету
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        
        // Фильтрация по типу
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        // Поиск по названию
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        
        // Сортировка
        $sortBy = $request->get('sort', 'due_date');
        $sortDir = $request->get('direction', 'asc');
        $query->orderBy($sortBy, $sortDir);
        
        $tasks = $query->paginate(20);
        
        // Данные для фильтров
        $statuses = EmployeeTask::STATUSES;
        $priorities = EmployeeTask::PRIORITIES;
        $types = EmployeeTask::TYPES;
        
        // Статистика для текущего пользователя
        $stats = [
            'total' => EmployeeTask::forUser(Auth::id())->count(),
            'pending' => EmployeeTask::forUser(Auth::id())->where('status', EmployeeTask::STATUS_PENDING)->count(),
            'in_progress' => EmployeeTask::forUser(Auth::id())->where('status', EmployeeTask::STATUS_IN_PROGRESS)->count(),
            'completed' => EmployeeTask::forUser(Auth::id())->where('status', EmployeeTask::STATUS_COMPLETED)->count(),
            'overdue' => EmployeeTask::forUser(Auth::id())->overdue()->count(),
            'due_soon' => EmployeeTask::forUser(Auth::id())->dueSoon()->count(),
        ];
        
        return view('admin.tasks.my-tasks', compact('tasks', 'statuses', 'priorities', 'types', 'stats'));
    }

    /**
     * Массовые операции
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:complete,cancel,assign',
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:employee_tasks,id',
            'assigned_to' => 'nullable|exists:users,id',
        ]);
        
        $tasks = EmployeeTask::whereIn('id', $validated['task_ids'])->get();
        
        foreach ($tasks as $task) {
            switch ($validated['action']) {
                case 'complete':
                    if ($task->canComplete()) {
                        $task->complete();
                    }
                    break;
                case 'cancel':
                    if ($task->canCancel()) {
                        $task->cancel();
                    }
                    break;
                case 'assign':
                    if ($validated['assigned_to']) {
                        $task->update(['assigned_to' => $validated['assigned_to']]);
                    }
                    break;
            }
        }
        
        return redirect()->back()
            ->with('success', 'Массовая операция выполнена');
    }
}
