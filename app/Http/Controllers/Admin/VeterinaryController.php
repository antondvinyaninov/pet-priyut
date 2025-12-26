<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\AnimalInspectionAct;
use App\Models\AnimalInspectionCommission;
use App\Models\AnimalReturnProcedure;
use App\Models\OsvvRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VeterinaryController extends Controller
{
    public function index()
    {
        // Статистика ветеринарной деятельности
        $stats = [
            'total_animals' => Animal::count(),
            'pending_inspections' => Animal::whereDoesntHave('inspectionActs')->count(),
            'sterilization_required' => AnimalInspectionAct::where('sterilization_required', true)
                ->where('is_sterilized', false)->count(),
            'return_procedures' => AnimalReturnProcedure::count(),
            'active_commissions' => AnimalInspectionCommission::where('is_active', true)->count(),
        ];

        // Недавние осмотры животных
        $recent_inspections = AnimalInspectionAct::with(['animal', 'commission'])
            ->orderBy('inspection_date', 'desc')
            ->limit(5)
            ->get()
            ->map(function($inspection) {
                $inspection->health_status_color = $this->getHealthStatusColor($inspection->health_status);
                $inspection->health_status_name = $this->getHealthStatusName($inspection->health_status);
                $inspection->aggression_level_color = $this->getAggressionLevelColor($inspection->aggression_level);
                $inspection->aggression_level_name = $this->getAggressionLevelName($inspection->aggression_level);
                return $inspection;
            });

        // Просроченные возвраты
        $overdue_returns = AnimalReturnProcedure::with('animal')
            ->where('planned_return_date', '<', now())
            ->where('return_status', 'planned')
            ->orderBy('planned_return_date', 'asc')
            ->limit(5)
            ->get()
            ->map(function($return) {
                $return->days_overdue = now()->diffInDays($return->planned_return_date);
                return $return;
            });

        // Животные, требующие особого внимания
        $animals_requiring_attention = AnimalInspectionAct::with('animal')
            ->where(function($query) {
                $query->where('health_status', 'critical')
                      ->orWhere('aggression_level', 'unmotivated')
                      ->orWhere(function($q) {
                          $q->where('sterilization_required', true)
                            ->where('is_sterilized', false);
                      });
            })
            ->orderBy('inspection_date', 'desc')
            ->limit(6)
            ->get()
            ->map(function($inspection) {
                $inspection->health_status_name = $this->getHealthStatusName($inspection->health_status);
                $inspection->aggression_level_color = $this->getAggressionLevelColor($inspection->aggression_level);
                $inspection->aggression_level_name = $this->getAggressionLevelName($inspection->aggression_level);
                return $inspection;
            });

        return view('admin.veterinary.index', compact(
            'stats', 
            'recent_inspections', 
            'overdue_returns', 
            'animals_requiring_attention'
        ));
    }

    public function storeCommission(Request $request)
    {
        $request->validate([
            'commission_name' => 'required|string|max:255',
            'formation_date' => 'required|date',
            'valid_until' => 'nullable|date|after:formation_date',
            'members' => 'required|array|min:1',
            'members.*' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $commission = AnimalInspectionCommission::create([
                'commission_name' => $request->commission_name,
                'formation_date' => $request->formation_date,
                'valid_until' => $request->valid_until,
                'members' => $request->members,
                'is_active' => true,
            ]);

            DB::commit();
            return response()->json(['success' => true, 'commission' => $commission]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function storeInspectionAct(Request $request)
    {
        $request->validate([
            'act_number' => 'required|string|max:50|unique:animal_inspection_acts,act_number',
            'inspection_date' => 'required|date',
            'animal_id' => 'required|exists:animals,id',
            'health_status' => 'required|in:healthy,sick,injured,critical',
            'aggression_level' => 'required|in:none,low,moderate,high,unmotivated',
            'sterilization_required' => 'boolean',
            'inspection_notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Автоматически создаем комиссию, если её нет
            $commission = AnimalInspectionCommission::where('is_active', true)->first();
            if (!$commission) {
                $commission = AnimalInspectionCommission::create([
                    'commission_name' => 'Комиссия по осмотру животных №1',
                    'formation_date' => now(),
                    'members' => ['Комиссия создана автоматически'],
                    'is_active' => true,
                ]);
            }

            $inspectionAct = AnimalInspectionAct::create([
                'act_number' => $request->act_number,
                'inspection_date' => $request->inspection_date,
                'animal_id' => $request->animal_id,
                'commission_id' => $commission->id,
                'health_status' => $request->health_status,
                'aggression_level' => $request->aggression_level,
                'sterilization_required' => $request->sterilization_required ?? false,
                'is_sterilized' => false,
                'return_to_habitat_allowed' => in_array($request->aggression_level, ['none', 'low']),
                'inspection_notes' => $request->inspection_notes,
                'commission_signatures' => [],
                'status' => 'draft',
            ]);

            DB::commit();
            return response()->json(['success' => true, 'inspection_act' => $inspectionAct]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function storeReturnProcedure(Request $request)
    {
        $request->validate([
            'animal_id' => 'required|exists:animals,id',
            'original_location' => 'required|string|max:500',
            'planned_return_date' => 'required|date|after:today',
            'responsible_persons' => 'required|array|min:1',
            'responsible_persons.*' => 'required|string|max:255',
            'return_notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $returnProcedure = AnimalReturnProcedure::create([
                'animal_id' => $request->animal_id,
                'inspection_act_id' => 1, // Временно, нужно связать с актом осмотра
                'original_location' => $request->original_location,
                'planned_return_date' => $request->planned_return_date,
                'return_status' => 'planned',
                'return_notes' => $request->return_notes,
                'responsible_persons' => $request->responsible_persons,
            ]);

            DB::commit();
            return response()->json(['success' => true, 'return_procedure' => $returnProcedure]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function getHealthStatusColor($status)
    {
        return match($status) {
            'healthy' => 'green',
            'sick' => 'yellow',
            'injured' => 'orange',
            'critical' => 'red',
            default => 'gray'
        };
    }

    private function getHealthStatusName($status)
    {
        return match($status) {
            'healthy' => 'Здоровое',
            'sick' => 'Больное',
            'injured' => 'Травмированное',
            'critical' => 'Критическое',
            default => 'Неизвестно'
        };
    }

    private function getAggressionLevelColor($level)
    {
        return match($level) {
            'none' => 'green',
            'low' => 'yellow',
            'moderate' => 'orange',
            'high' => 'red',
            'unmotivated' => 'red',
            default => 'gray'
        };
    }

    private function getAggressionLevelName($level)
    {
        return match($level) {
            'none' => 'Отсутствует',
            'low' => 'Низкая',
            'moderate' => 'Умеренная',
            'high' => 'Высокая',
            'unmotivated' => 'Немотивированная',
            default => 'Неизвестно'
        };
    }
} 