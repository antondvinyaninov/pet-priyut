<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OsvvRequest;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    /**
     * Отображает список заданий на выезд
     */
    public function index(Request $request)
    {
        return view('admin.osvv.task.index');
    }
    
    /**
     * Отображает форму для создания нового задания
     */
    public function create(Request $request)
    {
        $osvvRequests = OsvvRequest::whereNotIn('status', ['completed', 'cancelled'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        $catchers = User::role('catcher')->get();
        $osvvRequestId = $request->osvv_request_id;
        
        return view('admin.osvv.task.create', [
            'osvvRequests' => $osvvRequests,
            'catchers' => $catchers,
            'osvvRequestId' => $osvvRequestId,
        ]);
    }
    
    /**
     * Сохраняет новое задание
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'osvv_request_id' => 'required|exists:osvv_requests,id',
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'nullable',
            'user_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'is_priority' => 'boolean',
        ]);
        
        $osvvRequest = OsvvRequest::findOrFail($validated['osvv_request_id']);
        
        $task = $osvvRequest->createTask(
            $validated['scheduled_date'],
            $validated['scheduled_time'] ?? null,
            $request->has('is_priority'),
            $validated['notes'] ?? null
        );
        
        // Если был выбран отловщик, назначаем его
        if (!empty($validated['user_id'])) {
            $user = User::findOrFail($validated['user_id']);
            $task->assignTo($user);
        }
        
        // Обновляем дату следующего выезда в заявке
        $osvvRequest->scheduleNextDeparture($validated['scheduled_date']);
        
        return redirect()
            ->route('admin.osvv.task.index')
            ->with('success', 'Задание на выезд успешно создано');
    }
    
    /**
     * Отображает детали задания
     */
    public function show(Task $task)
    {
        $task->load(['osvvRequest', 'user']);
        
        return view('admin.osvv.task.show', [
            'task' => $task
        ]);
    }
    
    /**
     * Отображает форму для редактирования задания
     */
    public function edit(Task $task)
    {
        $task->load(['osvvRequest', 'user']);
        $catchers = User::role('catcher')->get();
        
        return view('admin.osvv.task.edit', [
            'task' => $task,
            'catchers' => $catchers,
        ]);
    }
    
    /**
     * Обновляет задание
     */
    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'nullable',
            'user_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,assigned,in_progress,completed,cancelled',
            'is_priority' => 'boolean',
        ]);
        
        $task->update([
            'scheduled_date' => $validated['scheduled_date'],
            'scheduled_time' => $validated['scheduled_time'] ?? null,
            'user_id' => $validated['user_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => $validated['status'],
            'is_priority' => $request->has('is_priority'),
        ]);
        
        if ($validated['status'] === 'completed' && !$task->completed_at) {
            $task->completed_at = now();
            $task->save();
        } else if ($validated['status'] === 'cancelled' && !$task->cancelled_at) {
            $task->cancelled_at = now();
            $task->save();
        }
        
        return redirect()
            ->route('admin.osvv.task.show', $task)
            ->with('success', 'Задание на выезд успешно обновлено');
    }
    
    /**
     * Отметить задание как выполненное
     */
    public function markAsCompleted(Task $task)
    {
        $task->markAsCompleted();
        
        // Регистрируем выезд в заявке ОСВВ
        $task->osvvRequest->registerDeparture(
            $task->scheduled_date,
            "Выезд выполнен. " . ($task->notes ?? '')
        );
        
        return redirect()
            ->back()
            ->with('success', 'Задание отмечено как выполненное');
    }
    
    /**
     * Отметить задание как отмененное
     */
    public function markAsCancelled(Task $task, Request $request)
    {
        $request->validate([
            'cancel_reason' => 'required|string|max:255',
        ]);
        
        $task->markAsCancelled();
        
        // Добавляем комментарий к заявке с причиной отмены
        $task->osvvRequest->comments()->create([
            'user_id' => auth()->id(),
            'comment' => '[ОТМЕНА ВЫЕЗДА] ' . $request->cancel_reason,
        ]);
        
        return redirect()
            ->back()
            ->with('success', 'Задание отмечено как отмененное');
    }
    
    /**
     * Показывает страницу с маршрутами для выбранной даты
     */
    public function routes(Request $request)
    {
        $date = $request->has('date') ? Carbon::parse($request->date) : Carbon::today();
        
        $catchers = User::role('catcher')->get();
        $catcherId = $request->catcher_id ?? null;
        
        // Получаем задания на выбранную дату и для выбранного отловщика (если указан)
        $query = Task::with(['osvvRequest', 'user'])
            ->whereDate('scheduled_date', $date)
            ->whereIn('status', ['pending', 'assigned', 'in_progress']);
            
        if ($catcherId) {
            $query->where('user_id', $catcherId);
        }
        
        $tasks = $query->get();
        
        // Подготавливаем данные для карты
        $mapData = [];
        foreach ($tasks as $task) {
            if ($task->osvvRequest->latitude && $task->osvvRequest->longitude) {
                $mapData[] = [
                    'id' => $task->id,
                    'lat' => $task->osvvRequest->latitude,
                    'lng' => $task->osvvRequest->longitude,
                    'address' => $task->osvvRequest->location_address,
                    'priority' => $task->is_priority,
                    'status' => $task->status,
                    'time' => $task->scheduled_time ? Carbon::parse($task->scheduled_time)->format('H:i') : null,
                    'assigned_to' => $task->user ? $task->user->name : 'Не назначен'
                ];
            }
        }
        
        return view('admin.osvv.task.routes', [
            'date' => $date->format('Y-m-d'),
            'catchers' => $catchers,
            'catcherId' => $catcherId,
            'tasks' => $tasks,
            'mapData' => json_encode($mapData),
        ]);
    }
    
    /**
     * Обновляет порядок маршрута для заданий
     */
    public function updateRouteOrder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:tasks,id',
        ]);
        
        $order = $request->order;
        
        // Обновляем порядок в базе данных
        foreach ($order as $index => $taskId) {
            Task::where('id', $taskId)->update([
                'route_position' => ['order' => $index + 1]
            ]);
        }
        
        return response()->json(['success' => true]);
    }
}
