<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\User;
use App\Models\OsvvRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Отображает главную страницу админ-панели
     */
    public function index()
    {
        // Статистика пользователей
        $totalUsers = User::count();
        
        // Статистика животных
        $totalAnimals = Animal::where('status', 'active')->count();
        $animalsWithOSVV = Animal::where('status', 'active')
            ->whereNotNull('osvv_request_id')
            ->count();
        $animalsInShelter = Animal::where('status', 'active')
            ->whereNull('osvv_request_id')
            ->count();
        $releasedAnimals = Animal::where('status', 'released')->count();
        $adoptedAnimals = Animal::where('status', 'adopted')->count();

        // Статистика по типам животных
        $animalsByType = Animal::where('status', 'active')
            ->select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get()
            ->keyBy('type');

        // Последние поступления (последние 8 для дашборда)
        $recentAnimals = Animal::where('status', 'active')
            ->with(['osvvRequest', 'currentStage'])
            ->orderBy('arrived_at', 'desc')
            ->limit(8)
            ->get();

        // Животные готовые к выпуску
        $readyForRelease = Animal::where('status', 'active')
            ->whereNotNull('current_stage_id')
            ->with(['currentStage'])
            ->whereHas('currentStage', function($query) {
                $query->where('is_final', true);
            })
            ->count();

        // Статистика заявок ОСВВ
        $totalOsvvRequests = OsvvRequest::count();
        $pendingOsvvRequests = OsvvRequest::where('status', 'pending')->count();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalAnimals',
            'animalsWithOSVV', 
            'animalsInShelter',
            'releasedAnimals',
            'adoptedAnimals',
            'animalsByType',
            'recentAnimals',
            'readyForRelease',
            'totalOsvvRequests',
            'pendingOsvvRequests'
        ));
    }
}
