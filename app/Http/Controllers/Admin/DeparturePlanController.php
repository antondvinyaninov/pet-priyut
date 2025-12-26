<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeparturePlan;
use App\Models\DepartureRoute;
use App\Models\OsvvRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\DepartureRouteRequest;
use App\Models\Animal;
use App\Models\EmployeeTask;

class DeparturePlanController extends Controller
{
    /**
     * Список планов выездов
     */
    public function index(Request $request)
    {
        $query = DeparturePlan::with(['creator', 'routes.routeRequests'])
            ->orderBy('planned_date', 'desc');

        // Фильтр по дате
        if ($request->filled('date')) {
            $query->whereDate('planned_date', $request->date);
        }

        // Фильтр по статусу
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $plans = $query->paginate(15)->withQueryString();

        $statuses = DeparturePlan::getStatuses();

        return view('admin.departure-planner.index', compact('plans', 'statuses'));
    }

    /**
     * Форма создания нового плана
     */
    public function create(Request $request)
    {
        // Получаем дату из параметра или используем сегодняшнюю
        $selectedDate = $request->get('date') ? 
            Carbon::createFromFormat('Y-m-d', $request->get('date')) : 
            now();
        
        // Получаем доступные заявки для выбранной даты
        $availableRequests = OsvvRequest::with(['comments'])
            ->where('status', 'processing')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('departure_route_requests')
                      ->whereColumn('departure_route_requests.osvv_request_id', 'osvv_requests.id')
                      ->whereExists(function ($subQuery) {
                          $subQuery->select(DB::raw(1))
                                   ->from('departure_routes')
                                   ->whereColumn('departure_routes.id', 'departure_route_requests.departure_route_id')
                                   ->whereExists(function ($planQuery) {
                                       $planQuery->select(DB::raw(1))
                                                ->from('departure_plans')
                                                ->whereColumn('departure_plans.id', 'departure_routes.departure_plan_id')
                                                ->where('departure_plans.status', '!=', 'cancelled');
                                   });
                      });
            })
            ->orderBy('is_urgent', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();

        // Получаем всех активных сотрудников для выбора отловщиков и водителей
        $employees = User::where('is_active', true)
            ->whereHas('roles', function($query) {
                $query->whereIn('name', ['catcher', 'driver', 'specialist', 'manager']);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'position']);

        // Разделяем сотрудников по ролям для удобства
        $catchers = User::where('is_active', true)
            ->whereHas('roles', function($query) {
                $query->whereIn('name', ['catcher', 'specialist']);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'position']);

        $drivers = User::where('is_active', true)
            ->whereHas('roles', function($query) {
                $query->whereIn('name', ['driver', 'specialist']);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'position']);

        // Получаем животных готовых к выпуску (статус "released" = "Готов к выезду")
        $animalsForRelease = Animal::where('status', 'released')
            ->with(['currentStage', 'osvvRequest', 'registrationCard'])
            ->orderBy('arrived_at')
            ->get();

        return view('admin.departure-planner.create', compact('availableRequests', 'catchers', 'drivers', 'selectedDate', 'animalsForRelease'));
    }

    /**
     * Сохранение нового плана
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'planned_date' => 'required|date',
            'notes' => 'nullable|string',
            'routes_data' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            // Декодируем данные о маршрутах
            $routesData = json_decode($request->routes_data, true);
            
            if (!$routesData || !is_array($routesData) || empty($routesData)) {
                throw new \Exception('Не переданы данные о маршрутах');
            }

            // Создаем план выезда
            $plan = DeparturePlan::create([
                'name' => $request->name,
                'description' => $request->notes ?? '',
                'planned_date' => $request->planned_date,
                'status' => 'planned',
                'created_by' => auth()->id(),
            ]);

            // Создаем маршруты и добавляем заявки/животных
            foreach ($routesData as $routeData) {
                $hasRequests = !empty($routeData['requests']);
                $hasAnimals = !empty($routeData['animals']);
                
                if (!$hasRequests && !$hasAnimals) {
                    continue;
                }

                $route = DepartureRoute::create([
                    'departure_plan_id' => $plan->id,
                    'name' => $routeData['name'] ?? 'Без названия',
                    'assigned_user_id' => $routeData['assigned_user_id'] ?? null,
                    'driver_user_id' => $routeData['driver_user_id'] ?? null,
                    'start_time' => $routeData['start_time'] ?? null,
                    'priority' => $routeData['priority'] ?? 5,
                    'notes' => $routeData['notes'] ?? '',
                    'status' => 'planned',
                ]);

                // Добавляем заявки в маршрут
                if ($hasRequests) {
                    foreach ($routeData['requests'] as $requestId) {
                        DepartureRouteRequest::create([
                            'departure_route_id' => $route->id,
                            'osvv_request_id' => $requestId,
                            'sequence_order' => 1, // Исправил название поля
                        ]);
                    }
                }

                // Добавляем животных в маршрут
                if ($hasAnimals) {
                    foreach ($routeData['animals'] as $animalId) {
                        \App\Models\DepartureRouteAnimal::create([
                            'departure_route_id' => $route->id,
                            'animal_id' => $animalId,
                            'sequence_order' => 1,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('admin.departure-planner.view', $plan->id)
                ->with('success', 'План выезда успешно создан');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ошибка при создании плана выезда: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'user_id' => auth()->id()
            ]);
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Произошла ошибка при создании плана: ' . $e->getMessage()]);
        }
    }

    /**
     * Объединенная страница просмотра и редактирования плана
     */
    public function view(DeparturePlan $departurePlan)
    {
        // Загружаем все необходимые связи
        $departurePlan->load([
            'creator',
            'routes.assignedUser',
            'routes.driverUser',
            'routes.routeRequests.osvvRequest',
            'routes.routeAnimals.animal',
            'tasks.assignedTo'
        ]);

        return view('admin.departure-planner.view', compact('departurePlan'));
    }

    /**
     * Просмотр плана выездов (редирект на объединенную страницу)
     */
    public function show(DeparturePlan $departurePlan)
    {
        return redirect()->route('admin.departure-planner.view', $departurePlan);
    }

    /**
     * Форма редактирования плана (редирект на объединенную страницу)
     */
    public function edit(DeparturePlan $departurePlan)
    {
        if (!$departurePlan->isEditable()) {
            return redirect()->route('admin.departure-planner.view', $departurePlan)
                ->with('error', 'Этот план нельзя редактировать в текущем статусе.');
        }

        return redirect()->route('admin.departure-planner.view', ['departurePlan' => $departurePlan, 'edit' => 1]);
    }

    /**
     * Обновление плана (упрощенная версия)
     */
    public function update(Request $request, DeparturePlan $departurePlan)
    {
        if (!$departurePlan->isEditable()) {
            return redirect()->route('admin.departure-planner.view', $departurePlan)
                ->with('error', 'Этот план нельзя редактировать в текущем статусе.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Обновляем только основную информацию плана
        $departurePlan->update([
            'name' => $validated['name'],
            'notes' => $validated['notes'],
        ]);

        return redirect()->route('admin.departure-planner.view', $departurePlan)
            ->with('success', 'План выездов успешно обновлен.');
    }

    /**
     * Удаление плана
     */
    public function destroy(DeparturePlan $departurePlan)
    {
        if (!$departurePlan->isEditable()) {
            return redirect()->route('admin.departure-planner.index')
                ->with('error', 'Этот план нельзя удалить в текущем статусе.');
        }

        $departurePlan->delete();

        return redirect()->route('admin.departure-planner.index')
            ->with('success', 'План выездов удален.');
    }

    /**
     * Утверждение плана
     */
    public function approve(DeparturePlan $departurePlan)
    {
        try {
            DB::beginTransaction();
            
            if ($departurePlan->approve()) {
                // Создаем задачи для сотрудников при утверждении плана
                $this->createTasksForPlan($departurePlan);
                
                DB::commit();
                
                return redirect()->route('admin.departure-planner.show', $departurePlan)
                    ->with('success', 'План выездов утвержден. Созданы задачи для сотрудников.');
            }

            DB::rollBack();
            return redirect()->route('admin.departure-planner.show', $departurePlan)
                ->with('error', 'Не удалось утвердить план.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ошибка при утверждении плана: ' . $e->getMessage());
            
            return redirect()->route('admin.departure-planner.show', $departurePlan)
                ->with('error', 'Произошла ошибка при утверждении плана.');
        }
    }

    /**
     * Запуск выполнения плана
     */
    public function start(DeparturePlan $departurePlan)
    {
        if ($departurePlan->start()) {
            return redirect()->route('admin.departure-planner.show', $departurePlan)
                ->with('success', 'Выполнение плана запущено.');
        }

        return redirect()->route('admin.departure-planner.show', $departurePlan)
            ->with('error', 'Не удалось запустить план.');
    }

    /**
     * Завершение плана
     */
    public function complete(DeparturePlan $departurePlan)
    {
        if ($departurePlan->complete()) {
            return redirect()->route('admin.departure-planner.show', $departurePlan)
                ->with('success', 'План выездов завершен.');
        }

        return redirect()->route('admin.departure-planner.show', $departurePlan)
            ->with('error', 'Не удалось завершить план.');
    }

    /**
     * Отмена плана
     */
    public function cancel(DeparturePlan $departurePlan)
    {
        if ($departurePlan->cancel()) {
            return redirect()->route('admin.departure-planner.show', $departurePlan)
                ->with('success', 'План выездов отменен.');
        }

        return redirect()->route('admin.departure-planner.show', $departurePlan)
            ->with('error', 'Не удалось отменить план.');
    }

    /**
     * Получить доступные заявки для планирования
     */
    private function getAvailableRequests(Carbon $date, array $excludeIds = []): \Illuminate\Database\Eloquent\Collection
    {
        return OsvvRequest::whereNotIn('status', [
                'captured', 'in_shelter', 'sterilized', 'vaccinated', 
                'ready_for_return', 'returned', 'completed', 'cancelled'
            ])
            ->whereNotIn('id', $excludeIds)
            ->whereDoesntHave('departureRouteRequests') // Не включены в другие планы
            ->orderByRaw('has_bite DESC') // Сначала с укусами
            ->orderByRaw('deadline_date ASC NULLS LAST') // Потом по срокам
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Оценка времени выполнения заявки
     */
    private function estimateTaskTime(OsvvRequest $request): int
    {
        $baseTime = 60; // Базовое время 60 минут
        
        if ($request->has_bite) {
            $baseTime += 30; // Дополнительное время на укусы
        }
        
        if ($request->is_pregnant) {
            $baseTime += 20; // Дополнительное время на беременных
        }
        
        if ($request->animals_count > 1) {
            $baseTime += ($request->animals_count - 1) * 15; // По 15 мин за каждое дополнительное животное
        }
        
        return $baseTime;
    }

    /**
     * Автосоздание плана на основе автоматического формирования списка
     */
    public function autoGenerate(Request $request)
    {
        $date = $request->date ? Carbon::parse($request->date) : Carbon::today();
        
        // Проверяем, нет ли уже плана на эту дату
        $existingPlan = DeparturePlan::forDate($date)->first();
        if ($existingPlan) {
            return redirect()->route('admin.departure-planner.edit', $existingPlan)
                ->with('info', 'План на эту дату уже существует.');
        }

        // Используем существующий метод автоматического формирования списка
        $controller = new \App\Http\Controllers\Admin\OsvvRequestController();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('generateTodayDepartureList');
        $method->setAccessible(true);
        $autoList = $method->invoke($controller);

        if (empty($autoList['zones'])) {
            return redirect()->route('admin.departure-planner.create', ['date' => $date->format('Y-m-d')])
                ->with('warning', 'Нет заявок для автоматического создания плана. Создайте план вручную.');
        }

        // Создаем план на основе автоматических зон
        $plan = DeparturePlan::create([
            'name' => DeparturePlan::generateDefaultName($date) . ' (автоматический)',
            'planned_date' => $date,
            'status' => 'draft',
            'created_by' => Auth::id(),
            'notes' => 'План создан автоматически на основе приоритетных заявок.',
        ]);

        // Создаем маршруты из зон
        foreach ($autoList['zones'] as $index => $zone) {
            $route = $plan->routes()->create([
                'name' => 'Зона ' . ($index + 1) . ' - ' . ($zone['center_request']['district'] ?? 'Без района'),
                'assigned_user_id' => null,
                'start_time' => null,
                'priority' => $zone['priority_level'],
                'notes' => 'Автоматически созданный маршрут',
                'status' => 'pending'
            ]);

            // Добавляем заявки в маршрут
            foreach ($zone['requests'] as $requestData) {
                $osvvRequest = OsvvRequest::find($requestData['id']);
                if ($osvvRequest) {
                    $route->addRequest($osvvRequest, $this->estimateTaskTime($osvvRequest));
                }
            }
        }

        // Пересчитываем статистику
        $plan->recalculateStats();

        return redirect()->route('admin.departure-planner.edit', $plan)
            ->with('success', 'Автоматический план создан! Проверьте и отредактируйте при необходимости.');
    }

    /**
     * Создает задачи для сотрудников при утверждении плана выездов
     */
    private function createTasksForPlan(DeparturePlan $plan): void
    {
        $routes = $plan->routes()->with(['assignedUser', 'driverUser', 'routeRequests.osvvRequest'])->get();
        
        foreach ($routes as $route) {
            // Создаем задачу для отловщика
            if ($route->assigned_user_id) {
                $this->createTaskForRoute($route, $route->assigned_user_id, 'Отловщик');
            }
            
            // Создаем задачу для водителя
            if ($route->driver_user_id && $route->driver_user_id !== $route->assigned_user_id) {
                $this->createTaskForRoute($route, $route->driver_user_id, 'Водитель');
            }
        }
    }

    /**
     * Создает задачу для конкретного маршрута и сотрудника
     */
    private function createTaskForRoute(DepartureRoute $route, int $userId, string $role): void
    {
        $plan = $route->departurePlan;
        $requestsCount = $route->routeRequests()->count();
        
        // Формируем описание задачи
        $description = "Выезд по плану \"{$plan->name}\" на {$plan->planned_date->format('d.m.Y')}.\n";
        $description .= "Роль: {$role}\n";
        $description .= "Маршрут: {$route->name}\n";
        $description .= "Количество заявок: {$requestsCount}\n";
        
        if ($route->start_time) {
            $description .= "Планируемое время начала: {$route->start_time->format('H:i')}\n";
        }
        
        if ($route->notes) {
            $description .= "Примечания: {$route->notes}\n";
        }
        
        // Определяем приоритет задачи на основе приоритета маршрута
        $taskPriority = match($route->priority) {
            1, 2 => 'low',
            3, 4, 5 => 'medium', 
            6, 7, 8 => 'high',
            9, 10 => 'urgent',
            default => 'medium'
        };
        
        // Создаем задачу
        EmployeeTask::create([
            'title' => "Выезд: {$route->name} ({$role})",
            'description' => $description,
            'priority' => $taskPriority,
            'status' => 'pending',
            'type' => 'osvv',
            'assigned_to' => $userId,
            'created_by' => auth()->id(),
            'assigned_by' => auth()->id(),
            'due_date' => $plan->planned_date->startOfDay()->addHours(8), // 8:00 утра в день выезда
            'estimated_hours' => $route->estimated_duration ? ceil($route->estimated_duration / 60) : 4,
            'departure_plan_id' => $plan->id,
            'tags' => json_encode(['выезд', 'план', $role])
        ]);
    }

    /**
     * Добавить заявку в план
     */
    public function addRequest(Request $request, DeparturePlan $departurePlan)
    {
        $request->validate([
            'osvv_request_id' => 'required|exists:osvv_requests,id',
            'route_id' => 'required|exists:departure_routes,id',
            'estimated_time' => 'nullable|integer|min:1|max:300'
        ]);

        // Проверяем, что заявка не занята
        $osvvRequest = OsvvRequest::find($request->osvv_request_id);
        if ($osvvRequest->routeRequests()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Заявка уже добавлена в другой план'
            ], 400);
        }

        // Проверяем, что маршрут принадлежит данному плану
        $route = $departurePlan->routes()->find($request->route_id);
        if (!$route) {
            return response()->json([
                'success' => false,
                'message' => 'Маршрут не найден в данном плане'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Определяем порядок заявки в маршруте
            $order = $route->routeRequests()->max('order') + 1;

            // Создаем запись в таблице связи
            DepartureRouteRequest::create([
                'departure_route_id' => $route->id,
                'osvv_request_id' => $request->osvv_request_id,
                'order' => $order,
                'estimated_time' => $request->estimated_time ?? 60,
                'execution_status' => 'pending'
            ]);

            // Обновляем статус заявки
            $osvvRequest->update(['status' => 'in_progress']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Заявка добавлена в план'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ошибка при добавлении заявки в план: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при добавлении заявки'
            ], 500);
        }
    }
}
