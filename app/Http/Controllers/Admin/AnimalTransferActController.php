<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnimalTransferAct;
use App\Models\Animal;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AnimalTransferActController extends Controller
{
    /**
     * Список всех актов приема-передачи
     */
    public function index(): View
    {
        $query = AnimalTransferAct::with(['animals', 'creator']);

        // Фильтрация по статусу
        if (request('status')) {
            $query->where('status', request('status'));
        }

        // Фильтрация по организации-отправителю
        if (request('from_organization')) {
            $query->where('from_organization', 'like', '%' . request('from_organization') . '%');
        }

        // Фильтрация по организации-получателю
        if (request('to_organization')) {
            $query->where('to_organization', 'like', '%' . request('to_organization') . '%');
        }

        $acts = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.animal-transfer-acts.index', compact('acts'));
    }

    /**
     * Показать форму создания нового акта
     */
    public function create(): View
    {
        $animals = Animal::where('status', 'active')
            ->orderBy('cage_number')
            ->get();

        return view('admin.animal-transfer-acts.create', compact('animals'));
    }

    /**
     * Сохранить новый акт приема-передачи
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'act_number' => 'nullable|string|max:255|unique:animal_transfer_acts',
            'act_date' => 'required|date',
            'from_organization' => 'required|string|max:255',
            'to_organization' => 'required|string|max:255',
            'from_person' => 'required|string|max:255',
            'to_person' => 'required|string|max:255',
            'from_position' => 'nullable|string|max:255',
            'to_position' => 'nullable|string|max:255',
            'transfer_reason' => 'required|string',
            'animals' => 'required|array|min:1',
            'animals.*' => 'exists:animals,id',
            'conditions' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        DB::transaction(function () use ($request) {
            // Создаем акт
            $act = AnimalTransferAct::create([
                'act_number' => $request->act_number ?: $this->generateActNumber(),
                'act_date' => $request->act_date,
                'from_organization' => $request->from_organization,
                'to_organization' => $request->to_organization,
                'from_person' => $request->from_person,
                'to_person' => $request->to_person,
                'from_position' => $request->from_position,
                'to_position' => $request->to_position,
                'transfer_reason' => $request->transfer_reason,
                'conditions' => $request->conditions,
                'notes' => $request->notes,
                'status' => 'draft',
                'created_by' => Auth::id()
            ]);

            // Привязываем животных к акту
            $act->animals()->attach($request->animals);
        });

        return redirect()->route('admin.animal-transfer-acts.index')
            ->with('success', 'Акт приема-передачи создан успешно');
    }

    /**
     * Показать акт приема-передачи
     */
    public function show(AnimalTransferAct $animalTransferAct): View
    {
        $animalTransferAct->load(['animals', 'creator']);

        return view('admin.animal-transfer-acts.show', compact('animalTransferAct'));
    }

    /**
     * Показать форму редактирования акта
     */
    public function edit(AnimalTransferAct $animalTransferAct): View
    {
        $animalTransferAct->load('animals');
        
        $animals = Animal::where('status', 'active')
            ->orderBy('cage_number')
            ->get();

        return view('admin.animal-transfer-acts.edit', compact('animalTransferAct', 'animals'));
    }

    /**
     * Обновить акт приема-передачи
     */
    public function update(Request $request, AnimalTransferAct $animalTransferAct): RedirectResponse
    {
        $request->validate([
            'act_number' => 'nullable|string|max:255|unique:animal_transfer_acts,act_number,' . $animalTransferAct->id,
            'act_date' => 'required|date',
            'from_organization' => 'required|string|max:255',
            'to_organization' => 'required|string|max:255',
            'from_person' => 'required|string|max:255',
            'to_person' => 'required|string|max:255',
            'from_position' => 'nullable|string|max:255',
            'to_position' => 'nullable|string|max:255',
            'transfer_reason' => 'required|string',
            'animals' => 'required|array|min:1',
            'animals.*' => 'exists:animals,id',
            'conditions' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        DB::transaction(function () use ($request, $animalTransferAct) {
            $animalTransferAct->update([
                'act_number' => $request->act_number ?: $animalTransferAct->act_number,
                'act_date' => $request->act_date,
                'from_organization' => $request->from_organization,
                'to_organization' => $request->to_organization,
                'from_person' => $request->from_person,
                'to_person' => $request->to_person,
                'from_position' => $request->from_position,
                'to_position' => $request->to_position,
                'transfer_reason' => $request->transfer_reason,
                'conditions' => $request->conditions,
                'notes' => $request->notes
            ]);

            // Обновляем связанных животных
            $animalTransferAct->animals()->sync($request->animals);
        });

        return redirect()->route('admin.animal-transfer-acts.show', $animalTransferAct)
            ->with('success', 'Акт приема-передачи обновлен успешно');
    }

    /**
     * Удалить акт приема-передачи
     */
    public function destroy(AnimalTransferAct $animalTransferAct): RedirectResponse
    {
        // Проверяем, что акт не подписан
        if ($animalTransferAct->status === 'signed') {
            return redirect()->route('admin.animal-transfer-acts.index')
                ->with('error', 'Нельзя удалить подписанный акт');
        }

        $animalTransferAct->delete();

        return redirect()->route('admin.animal-transfer-acts.index')
            ->with('success', 'Акт приема-передачи удален');
    }

    /**
     * Подписать акт приема-передачи
     */
    public function sign(AnimalTransferAct $animalTransferAct): RedirectResponse
    {
        if ($animalTransferAct->status === 'signed') {
            return redirect()->route('admin.animal-transfer-acts.show', $animalTransferAct)
                ->with('error', 'Акт уже подписан');
        }

        $animalTransferAct->update([
            'status' => 'signed',
            'signed_at' => now(),
            'signed_by' => Auth::id()
        ]);

        return redirect()->route('admin.animal-transfer-acts.show', $animalTransferAct)
            ->with('success', 'Акт приема-передачи подписан');
    }

    /**
     * Отменить подпись акта
     */
    public function unsign(AnimalTransferAct $animalTransferAct): RedirectResponse
    {
        $animalTransferAct->update([
            'status' => 'draft',
            'signed_at' => null,
            'signed_by' => null
        ]);

        return redirect()->route('admin.animal-transfer-acts.show', $animalTransferAct)
            ->with('success', 'Подпись акта отменена');
    }

    /**
     * Экспортировать акт в PDF
     */
    public function exportPdf(AnimalTransferAct $animalTransferAct): View
    {
        $animalTransferAct->load(['animals', 'creator']);

        return view('admin.animal-transfer-acts.pdf', compact('animalTransferAct'));
    }

    /**
     * Сгенерировать номер акта
     */
    private function generateActNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        
        $lastAct = AnimalTransferAct::whereYear('act_date', $year)
            ->whereMonth('act_date', $month)
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastAct ? (int)substr($lastAct->act_number, -3) + 1 : 1;

        return sprintf('АПП-%s-%02d-%03d', $year, $month, $number);
    }
} 