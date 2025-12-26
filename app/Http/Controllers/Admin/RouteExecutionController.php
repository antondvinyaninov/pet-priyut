<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DepartureRoute;
use App\Models\DepartureRouteRequest;
use App\Models\DepartureRouteAnimal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RouteExecutionController extends Controller
{


    /**
     * Отметить выполнение заявки в маршруте
     */
    public function markRequestExecution(Request $request, DepartureRouteRequest $routeRequest)
    {
        $request->validate([
            'execution_status' => 'required|in:visited,completed,failed,cancelled,no_animals_found',
            'execution_result' => 'nullable|in:success,partial_success,no_result,failed,cancelled',
            'execution_notes' => 'nullable|string',
            'animals_captured' => 'nullable|integer|min:0',
            'execution_photos' => 'nullable|array',
            'execution_photos.*' => 'string' // base64 или пути к файлам
        ]);

        try {
            DB::beginTransaction();

            $routeRequest->update([
                'execution_status' => $request->execution_status,
                'execution_result' => $request->execution_result,
                'execution_notes' => $request->execution_notes,
                'executed_at' => now(),
                'animals_captured' => $request->animals_captured ?? 0,
                'execution_photos' => $request->execution_photos
            ]);

            // Обновляем процент выполнения маршрута
            $this->updateRouteCompletionPercentage($routeRequest->departureRoute);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Выполнение заявки отмечено'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ошибка при отметке выполнения заявки: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при отметке выполнения'
            ], 500);
        }
    }

    /**
     * Получить форму для отметки выполнения заявки
     */
    public function showRequestExecutionForm(DepartureRouteRequest $routeRequest)
    {
        $routeRequest->load(['departureRoute', 'osvvRequest']);
        
        return view('admin.route-execution.request-form', compact('routeRequest'));
    }

    /**
     * Получить интерфейс выполнения маршрута
     */
    public function showRouteExecution(DepartureRoute $route)
    {
        $route->load([
            'departurePlan',
            'assignedUser',
            'driverUser',
            'routeRequests.osvvRequest',
            'routeAnimals.animal'
        ]);

        return view('admin.route-execution.index', compact('route'));
    }

    /**
     * Автоматически рассчитать процент выполнения маршрута
     */
    private function calculateCompletionPercentage(DepartureRoute $route): int
    {
        $totalRequests = $route->routeRequests()->count();
        $totalAnimals = $route->routeAnimals()->count();
        $totalTasks = $totalRequests + $totalAnimals;
        
        if ($totalTasks === 0) {
            return 100;
        }

        $completedRequests = $route->routeRequests()
            ->whereIn('execution_status', ['completed', 'visited'])
            ->count();
            
        $releasedAnimals = $route->routeAnimals()
            ->where('release_status', 'released')
            ->count();

        $completedTasks = $completedRequests + $releasedAnimals;

        return round(($completedTasks / $totalTasks) * 100);
    }

    /**
     * Обновить процент выполнения маршрута на основе заявок
     */
    private function updateRouteCompletionPercentage(DepartureRoute $route): void
    {
        $percentage = $this->calculateCompletionPercentage($route);
        
        // Если маршрут еще не начат, но есть выполненные заявки, начинаем его
        if ($percentage > 0 && $route->completion_status === 'not_started') {
            $route->update([
                'completion_status' => 'in_progress',
                'actual_start_time' => now(),
                'completion_percentage' => $percentage
            ]);
        } else {
            $route->update(['completion_percentage' => $percentage]);
        }

        // Если все заявки выполнены, автоматически завершаем маршрут
        if ($percentage === 100 && $route->completion_status === 'in_progress') {
            $route->update([
                'completion_status' => 'completed',
                'actual_end_time' => now()
            ]);
            
            // Обновляем статус плана
            $this->updatePlanStatus($route->departurePlan);
        }
    }

    /**
     * Обновить статус плана на основе выполнения маршрутов
     */
    private function updatePlanStatus($plan): void
    {
        $totalRoutes = $plan->routes()->count();
        $completedRoutes = $plan->routes()
            ->where('completion_status', 'completed')
            ->count();

        // Если все маршруты завершены, завершаем план
        if ($totalRoutes > 0 && $completedRoutes === $totalRoutes) {
            $plan->update(['status' => 'completed']);
        }
    }

    /**
     * Получить статистику выполнения маршрута
     */
    public function getRouteStats(DepartureRoute $route)
    {
        $stats = [
            'total_requests' => $route->routeRequests()->count(),
            'completed_requests' => $route->routeRequests()->whereIn('execution_status', ['completed', 'visited'])->count(),
            'failed_requests' => $route->routeRequests()->where('execution_status', 'failed')->count(),
            'pending_requests' => $route->routeRequests()->where('execution_status', 'pending')->count(),
            'total_animals_captured' => $route->routeRequests()->sum('animals_captured'),
            'total_animals_for_release' => $route->routeAnimals()->count(),
            'released_animals' => $route->routeAnimals()->where('release_status', 'released')->count(),
            'pending_animals' => $route->routeAnimals()->where('release_status', 'pending')->count(),
            'failed_releases' => $route->routeAnimals()->where('release_status', 'failed')->count(),
            'completion_percentage' => $this->calculateCompletionPercentage($route)
        ];

        return response()->json($stats);
    }

    /**
     * Отметить выпуск животного
     */
    public function markAnimalRelease(Request $request, DepartureRouteAnimal $routeAnimal)
    {
        $request->validate([
            'release_status' => 'required|in:pending,released,failed,cancelled',
            'release_result' => 'nullable|in:success,failed,cancelled',
            'release_notes' => 'nullable|string',
            'release_location' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $routeAnimal->update([
                'release_status' => $request->release_status,
                'release_result' => $request->release_result,
                'release_notes' => $request->release_notes,
                'release_location' => $request->release_location,
                'released_at' => $request->release_status === 'released' ? now() : null,
            ]);

            // Обновляем процент выполнения маршрута
            $this->updateRouteCompletionPercentage($routeAnimal->departureRoute);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Статус выпуска животного обновлен'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ошибка при отметке выпуска животного: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при отметке выпуска'
            ], 500);
        }
    }
}
