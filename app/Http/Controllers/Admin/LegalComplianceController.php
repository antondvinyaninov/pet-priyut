<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\AnimalInspectionAct;
use App\Models\AnimalInspectionCommission;
use App\Models\AnimalRegistrationCard;
use App\Models\AnimalReturnProcedure;
use App\Models\AnimalTransferAct;
use App\Models\RegulatoryDocument;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LegalComplianceController extends Controller
{
    /**
     * Показать дашборд соответствия нормативным требованиям
     */
    public function index(): View
    {
        $stats = [
            'total_animals' => Animal::count(),
            'pending_inspections' => AnimalInspectionAct::where('status', 'draft')->count(),
            'sterilization_required' => AnimalInspectionAct::where('sterilization_required', true)
                ->where('is_sterilized', false)->count(),
            'return_procedures' => AnimalReturnProcedure::where('return_status', 'planned')->count(),
            'active_commissions' => AnimalInspectionCommission::getActiveCommissions()->count(),
            'effective_documents' => RegulatoryDocument::getEffectiveDocuments()->count(),
        ];

        $recent_inspections = AnimalInspectionAct::with(['animal', 'commission'])
            ->orderBy('inspection_date', 'desc')
            ->limit(5)
            ->get();

        $overdue_returns = AnimalReturnProcedure::with('animal')
            ->where('return_status', '!=', 'completed')
            ->where('return_status', '!=', 'cancelled')
            ->where('planned_return_date', '<', now()->toDateString())
            ->limit(10)
            ->get();

        $animals_requiring_attention = AnimalInspectionAct::with('animal')
            ->where(function ($query) {
                $query->where('health_status', 'critical')
                    ->orWhere('aggression_level', 'unmotivated')
                    ->orWhere(function ($q) {
                        $q->where('sterilization_required', true)
                          ->where('is_sterilized', false);
                    });
            })
            ->limit(10)
            ->get();

        return view('admin.legal-compliance.index', compact(
            'stats',
            'recent_inspections', 
            'overdue_returns',
            'animals_requiring_attention'
        ));
    }

    /**
     * Показать отчет соответствия нормативным требованиям
     */
    public function complianceReport(): View
    {
        // Статистика по актам приема-передачи
        $transfer_stats = [
            'total_acts' => AnimalTransferAct::count(),
            'pending_acts' => AnimalTransferAct::where('status', 'draft')->count(),
            'completed_acts' => AnimalTransferAct::where('status', 'completed')->count(),
        ];

        // Статистика по осмотрам
        $inspection_stats = [
            'total_inspections' => AnimalInspectionAct::count(),
            'healthy_animals' => AnimalInspectionAct::where('health_status', 'healthy')->count(),
            'aggressive_animals' => AnimalInspectionAct::where('aggression_level', 'unmotivated')->count(),
            'sterilized_animals' => AnimalInspectionAct::where('is_sterilized', true)->count(),
        ];

        // Статистика по возвратам
        $return_stats = [
            'total_procedures' => AnimalReturnProcedure::count(),
            'completed_returns' => AnimalReturnProcedure::where('return_status', 'completed')->count(),
            'overdue_returns' => AnimalReturnProcedure::where('return_status', '!=', 'completed')
                ->where('return_status', '!=', 'cancelled')
                ->where('planned_return_date', '<', now()->toDateString())
                ->count(),
        ];

        // Статистика по регистрационным карточкам
        $registration_stats = [
            'total_cards' => AnimalRegistrationCard::count(),
            'active_cards' => AnimalRegistrationCard::where('card_status', 'active')->count(),
            'osvv_intakes' => AnimalRegistrationCard::where('intake_source', 'ОСВВ')->count(),
        ];

        $effective_documents = RegulatoryDocument::getEffectiveDocuments();

        return view('admin.legal-compliance.report', compact(
            'transfer_stats',
            'inspection_stats', 
            'return_stats',
            'registration_stats',
            'effective_documents'
        ));
    }

    /**
     * Экспорт отчета о соответствии
     */
    public function exportComplianceReport()
    {
        // TODO: Реализовать экспорт отчета в PDF/Excel
        return response()->json(['message' => 'Функция экспорта будет реализована позже']);
    }

    /**
     * Получить статистику для API
     */
    public function getStats()
    {
        return response()->json([
            'animals_requiring_inspection' => Animal::whereDoesntHave('inspectionActs')->count(),
            'overdue_sterilizations' => AnimalInspectionAct::where('sterilization_required', true)
                ->where('is_sterilized', false)
                ->whereDate('inspection_date', '<', now()->subDays(30))
                ->count(),
            'pending_returns' => AnimalReturnProcedure::whereIn('return_status', ['planned', 'approved'])
                ->count(),
            'compliance_percentage' => $this->calculateCompliancePercentage(),
        ]);
    }

    /**
     * Рассчитать процент соответствия нормативным требованиям
     */
    private function calculateCompliancePercentage(): float
    {
        $total_animals = Animal::count();
        
        if ($total_animals === 0) {
            return 100;
        }

        // Животные с регистрационными карточками
        $registered_animals = Animal::whereHas('registrationCard')->count();
        
        // Животные с актами осмотра
        $inspected_animals = Animal::whereHas('inspectionActs')->count();
        
        // Стерилизованные животные (если требовалась стерилизация)
        $sterilized_count = AnimalInspectionAct::where('sterilization_required', true)
            ->where('is_sterilized', true)
            ->count();
        $sterilization_required_count = AnimalInspectionAct::where('sterilization_required', true)->count();
        
        $compliance_score = 0;
        $max_score = 3;

        // Процент животных с регистрационными карточками (вес 1)
        $compliance_score += ($registered_animals / $total_animals);

        // Процент животных с актами осмотра (вес 1)  
        $compliance_score += ($inspected_animals / $total_animals);

        // Процент выполненных стерилизаций (вес 1)
        if ($sterilization_required_count > 0) {
            $compliance_score += ($sterilized_count / $sterilization_required_count);
        } else {
            $compliance_score += 1; // Если нет требований к стерилизации, считаем 100%
        }

        return round(($compliance_score / $max_score) * 100, 2);
    }

    /**
     * Создать комиссию по осмотру
     */
    public function storeCommission(Request $request)
    {
        $request->validate([
            'commission_name' => 'required|string|max:255',
            'formation_date' => 'required|date',
            'valid_until' => 'nullable|date|after:formation_date',
            'members' => 'required|array|min:1',
            'is_active' => 'boolean'
        ]);

        $commission = AnimalInspectionCommission::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Комиссия создана успешно',
            'data' => $commission
        ]);
    }

    /**
     * Создать акт осмотра животного
     */
    public function storeInspectionAct(Request $request)
    {
        $request->validate([
            'act_number' => 'required|string|unique:animal_inspection_acts',
            'inspection_date' => 'required|date',
            'animal_id' => 'required|exists:animals,id',
            'commission_id' => 'nullable|exists:animal_inspection_commissions,id',
            'health_status' => 'required|in:healthy,sick,injured,critical',
            'aggression_level' => 'required|in:none,low,moderate,high,unmotivated',
            'sterilization_required' => 'boolean',
            'is_sterilized' => 'boolean',
            'return_to_habitat_allowed' => 'boolean',
            'inspection_notes' => 'nullable|string',
            'commission_signatures' => 'array',
            'status' => 'required|in:draft,signed,completed'
        ]);

        // Если комиссия не указана, создаем временную
        if (!$request->commission_id) {
            $commission = AnimalInspectionCommission::first();
            if (!$commission) {
                $commission = AnimalInspectionCommission::create([
                    'commission_name' => 'Временная комиссия по осмотру',
                    'formation_date' => now(),
                    'members' => ['Система - Автоматическое создание'],
                    'is_active' => true
                ]);
            }
            $data = $request->all();
            $data['commission_id'] = $commission->id;
        } else {
            $data = $request->all();
        }

        $inspectionAct = AnimalInspectionAct::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Акт осмотра создан успешно',
            'data' => $inspectionAct
        ]);
    }

    /**
     * Создать процедуру возврата
     */
    public function storeReturnProcedure(Request $request)
    {
        $request->validate([
            'animal_id' => 'required|exists:animals,id',
            'inspection_act_id' => 'nullable|exists:animal_inspection_acts,id',
            'original_location' => 'required|string',
            'planned_return_date' => 'required|date|after_or_equal:today',
            'return_status' => 'required|in:planned,approved,in_progress,completed,cancelled',
            'return_notes' => 'nullable|string',
            'responsible_persons' => 'required|array|min:1'
        ]);

        // Если акт осмотра не указан, попробуем найти подходящий
        if (!$request->inspection_act_id) {
            $inspectionAct = AnimalInspectionAct::where('animal_id', $request->animal_id)->first();
            if (!$inspectionAct) {
                // Создаем базовый акт осмотра
                $commission = AnimalInspectionCommission::first();
                if (!$commission) {
                    $commission = AnimalInspectionCommission::create([
                        'commission_name' => 'Временная комиссия по осмотру',
                        'formation_date' => now(),
                        'members' => ['Система - Автоматическое создание'],
                        'is_active' => true
                    ]);
                }
                
                $inspectionAct = AnimalInspectionAct::create([
                    'act_number' => AnimalInspectionAct::generateActNumber(),
                    'inspection_date' => now(),
                    'commission_id' => $commission->id,
                    'animal_id' => $request->animal_id,
                    'health_status' => 'healthy',
                    'aggression_level' => 'none',
                    'sterilization_required' => false,
                    'is_sterilized' => false,
                    'return_to_habitat_allowed' => true,
                    'inspection_notes' => 'Автоматически создан для процедуры возврата',
                    'commission_signatures' => [],
                    'status' => 'draft'
                ]);
            }
            
            $data = $request->all();
            $data['inspection_act_id'] = $inspectionAct->id;
        } else {
            $data = $request->all();
        }

        $returnProcedure = AnimalReturnProcedure::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Процедура возврата создана успешно',
            'data' => $returnProcedure
        ]);
    }

    /**
     * Создать нормативный документ
     */
    public function storeRegulatoryDocument(Request $request)
    {
        $request->validate([
            'document_type' => 'required|string',
            'document_number' => 'required|string',
            'document_date' => 'required|date',
            'issuing_authority' => 'required|string',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'effective_from' => 'required|date',
            'effective_until' => 'nullable|date|after:effective_from',
            'is_active' => 'boolean'
        ]);

        $document = RegulatoryDocument::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Нормативный документ добавлен успешно',
            'data' => $document
        ]);
    }
} 