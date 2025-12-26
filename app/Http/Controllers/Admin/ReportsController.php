<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnimalInspectionCommission;
use App\Models\RegulatoryDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function index()
    {
        // Статистика отчетности
        $stats = [
            'effective_documents' => RegulatoryDocument::where('is_active', true)->count(),
            'active_commissions' => AnimalInspectionCommission::where('is_active', true)->count(),
            'reports_created' => 0, // Будет реализовано позже
            'compliance_percentage' => 85, // Пример процента соответствия
        ];

        // Нормативная база
        $regulatory_documents = RegulatoryDocument::where('is_active', true)
            ->orderBy('document_date', 'desc')
            ->limit(10)
            ->get()
            ->map(function($doc) {
                $doc->status_color = 'green';
                $doc->status_name = 'Действует';
                return $doc;
            });

        // Последние отчеты (пока пустые данные)
        $recent_reports = collect();

        return view('admin.reports.index', compact(
            'stats', 
            'regulatory_documents', 
            'recent_reports'
        ));
    }

    public function storeRegulatoryDocument(Request $request)
    {
        $request->validate([
            'document_type' => 'required|in:order,regulation,instruction,methodical',
            'document_number' => 'required|string|max:50',
            'document_date' => 'required|date',
            'issuing_authority' => 'required|string|max:255',
            'title' => 'required|string|max:500',
            'effective_from' => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            $document = RegulatoryDocument::create([
                'document_type' => $request->document_type,
                'document_number' => $request->document_number,
                'document_date' => $request->document_date,
                'issuing_authority' => $request->issuing_authority,
                'title' => $request->title,
                'effective_from' => $request->effective_from,
                'is_active' => true,
            ]);

            DB::commit();
            return response()->json(['success' => true, 'document' => $document]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function createComplianceReport()
    {
        // Будет реализовано позже
        return response()->json(['message' => 'Функция создания отчета соответствия будет добавлена']);
    }

    public function createVeterinaryReport()
    {
        // Будет реализовано позже
        return response()->json(['message' => 'Функция создания ветеринарного отчета будет добавлена']);
    }

    public function createWarehouseReport()
    {
        // Будет реализовано позже
        return response()->json(['message' => 'Функция создания складского отчета будет добавлена']);
    }
} 