<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CaptureAct;
use App\Models\OsvvRequest;
use App\Models\User;
use App\Models\Animal;
use App\Models\AnimalStage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class CaptureActController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $acts = CaptureAct::with(['osvvRequest', 'user'])
            ->latest()
            ->paginate(15);
            
        return view('admin.osvv.acts.index', compact('acts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $osvvRequestId = $request->input('osvv_request_id');
        $osvvRequest = null;
        
        if ($osvvRequestId) {
            $osvvRequest = OsvvRequest::findOrFail($osvvRequestId);
        }
        
        // Список отловщиков
        $catchers = User::all(); // Заменить на User::role('catcher') после настройки системы ролей
        
        // Генерируем номер акта
        $actNumber = CaptureAct::generateActNumber();
        
        return view('admin.osvv.acts.create', compact('osvvRequest', 'catchers', 'actNumber'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'osvv_request_id' => 'required|exists:osvv_requests,id',
            'user_id' => 'nullable|exists:users,id',
            'act_number' => 'required|string|unique:capture_acts,act_number',
            'capture_date' => 'required|date',
            'capture_time' => 'nullable|date_format:H:i',
            'capture_location' => 'required|string|max:255',
            'animal_type' => 'nullable|string|max:100',
            'animal_gender' => 'nullable|string|max:20',
            'animal_breed' => 'nullable|string|max:100',
            'animal_color' => 'nullable|string|max:100',
            'animal_size' => 'nullable|string|max:50',
            'animal_features' => 'nullable|string',
            'animal_behavior' => 'nullable|string|max:255',
            'capturing_method' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|string|in:created,completed,cancelled',
            'animals_count' => 'required|integer|min:1|max:20',
        ]);
        
        // Создаем акт отлова
        $act = CaptureAct::create($validated);
        
        // Автоматическое создание животных в системе управления при статусе "выполнен"
        if ($validated['status'] === 'completed' && $validated['animals_count'] > 0) {
            $this->createAnimalsFromAct($act, $validated['animals_count']);
        }
        
        // Добавляем комментарий к заявке
        $osvvRequest = OsvvRequest::find($validated['osvv_request_id']);
        if ($osvvRequest) {
            $commentText = 'Создан акт отлова #' . $act->act_number;
            
            if ($validated['animals_count'] > 1) {
                $commentText .= " (отловлено животных: {$validated['animals_count']})";
            }
            
            if ($validated['user_id']) {
                $catcher = User::find($validated['user_id']);
                $commentText .= ', отловщик: ' . $catcher->name;
            }
            
            $osvvRequest->comments()->create([
                'user_id' => Auth::id() ?? 1,
                'comment' => $commentText
            ]);
            
            // Если статус акта "выполнен", обновляем статус заявки
            if ($validated['status'] === 'completed') {
                $osvvRequest->status = 'completed';
                $osvvRequest->capture_result = 'Животное отловлено согласно акту №' . $act->act_number;
                $osvvRequest->save();
                
                // Добавляем еще один комментарий о завершении заявки и передаче в систему управления
                $animalWord = $validated['animals_count'] > 1 ? 'животных' : 'животного';
                $osvvRequest->comments()->create([
                    'user_id' => Auth::id() ?? 1,
                    'comment' => "🎯 Заявка завершена. Отлов выполнен согласно акту №{$act->act_number}. {$validated['animals_count']} {$animalWord} автоматически добавлено в систему управления животными для дальнейшей обработки."
                ]);
            }
        }
        
        return redirect()->route('admin.osvv.acts.show', $act->id)
            ->with('success', 'Акт отлова успешно создан');
    }

    /**
     * Display the specified resource.
     */
    public function show(CaptureAct $act)
    {
        $act->load(['osvvRequest', 'user']);
        return view('admin.osvv.acts.show', compact('act'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CaptureAct $act)
    {
        $act->load('osvvRequest');
        
        // Список отловщиков
        $catchers = User::all(); // Заменить на User::role('catcher') после настройки системы ролей
        
        return view('admin.osvv.acts.edit', compact('act', 'catchers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CaptureAct $act)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'capture_date' => 'required|date',
            'capture_time' => 'nullable|date_format:H:i',
            'capture_location' => 'required|string|max:255',
            'animal_type' => 'nullable|string|max:100',
            'animal_gender' => 'nullable|string|max:20',
            'animal_breed' => 'nullable|string|max:100',
            'animal_color' => 'nullable|string|max:100',
            'animal_size' => 'nullable|string|max:50',
            'animal_features' => 'nullable|string',
            'animal_behavior' => 'nullable|string|max:255',
            'capturing_method' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|string|in:created,completed,cancelled',
            'animals_count' => 'required|integer|min:1|max:20',
        ]);
        
        // Проверяем, изменился ли статус акта
        $statusChanged = $act->status !== $validated['status'];
        $oldStatus = $act->status;
        
        // Обновляем акт
        $act->update($validated);
        
        // Обрабатываем изменение статуса
        if ($statusChanged && $act->osvvRequest) {
            // Добавляем комментарий о смене статуса акта
            $statusList = CaptureAct::getStatusList();
            $commentText = 'Статус акта отлова #' . $act->act_number . ' изменен: ' . 
                $statusList[$oldStatus] . ' → ' . $statusList[$validated['status']];
            
            $act->osvvRequest->comments()->create([
                'user_id' => Auth::id() ?? 1,
                'comment' => $commentText
            ]);
            
            // Если акт отмечен как выполненный, обновляем статус заявки
            if ($validated['status'] === 'completed' && $oldStatus !== 'completed') {
                // Автоматическое создание животных в системе управления
                if ($validated['animals_count'] > 0) {
                    $this->createAnimalsFromAct($act, $validated['animals_count']);
                }
                
                $act->osvvRequest->status = 'completed';
                $act->osvvRequest->capture_result = 'Животное отловлено согласно акту №' . $act->act_number;
                $act->osvvRequest->save();
                
                // Добавляем комментарий о завершении заявки и передаче в систему управления
                $animalWord = $validated['animals_count'] > 1 ? 'животных' : 'животного';
                $act->osvvRequest->comments()->create([
                    'user_id' => Auth::id() ?? 1,
                    'comment' => "🎯 Заявка завершена. Отлов выполнен согласно акту №{$act->act_number}. {$validated['animals_count']} {$animalWord} автоматически добавлено в систему управления животными для дальнейшей обработки."
                ]);
            }
            // Если акт отменен, но заявка была завершена этим актом, возвращаем статус в работу
            elseif ($validated['status'] === 'cancelled' && $oldStatus === 'completed') {
                $act->osvvRequest->status = 'in_progress';
                $act->osvvRequest->capture_result = null;
                $act->osvvRequest->save();
                
                // Добавляем комментарий о возврате заявки в работу
                $act->osvvRequest->comments()->create([
                    'user_id' => Auth::id() ?? 1,
                    'comment' => 'Заявка возвращена в работу в связи с отменой акта №' . $act->act_number
                ]);
            }
        }
        
        return redirect()->route('admin.osvv.acts.show', $act->id)
            ->with('success', 'Акт отлова успешно обновлен');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CaptureAct $act)
    {
        // Проверяем, чтобы акт не был в статусе "выполнен"
        if ($act->status === 'completed') {
            return back()->with('error', 'Нельзя удалить выполненный акт отлова');
        }
        
        // Добавляем комментарий к заявке об удалении акта
        if ($act->osvvRequest) {
            $act->osvvRequest->comments()->create([
                'user_id' => Auth::id() ?? 1,
                'comment' => 'Удален акт отлова #' . $act->act_number
            ]);
        }
        
        // Удаляем акт
        $act->delete();
        
        return redirect()->route('admin.osvv.acts.index')
            ->with('success', 'Акт отлова успешно удален');
    }
    
    /**
     * Генерировать PDF-версию акта отлова.
     */
    public function generatePdf(CaptureAct $act)
    {
        $act->load(['osvvRequest', 'user']);
        
        $pdf = PDF::loadView('admin.osvv.acts.pdf', compact('act'));
        
        return $pdf->download('act_' . $act->act_number . '.pdf');
    }
    
    /**
     * Автоматическое создание животных в системе управления на основе акта отлова
     */
    private function createAnimalsFromAct(CaptureAct $act, int $animalsCount): void
    {
        // Получаем первый активный этап (карантин)
        $firstStage = AnimalStage::active()->ordered()->first();
        
        if (!$firstStage) {
            // Если этапы не настроены, создаем базовый этап
            $firstStage = AnimalStage::create([
                'name' => 'Карантин',
                'slug' => 'quarantine',
                'description' => 'Карантин для отловленных животных',
                'color' => '#EF4444',
                'order' => 1,
                'duration_days' => 10,
                'is_final' => false,
                'is_active' => true,
            ]);
        }
        
        // Маппинг типов животных между актом и системой управления
        $animalTypeMapping = [
            'кошка' => 'cat',
            'кот' => 'cat',
            'собака' => 'dog',
            'пес' => 'dog',
            'dog' => 'dog',
            'cat' => 'cat',
        ];
        
        // Маппинг пола животных
        $genderMapping = [
            'самец' => 'male',
            'самка' => 'female',
            'мужской' => 'male',
            'женский' => 'female',
            'м' => 'male',
            'ж' => 'female',
            'male' => 'male',
            'female' => 'female',
        ];
        
        // Определяем тип животного
        $animalType = 'dog'; // по умолчанию собака
        if ($act->animal_type) {
            $lowerType = mb_strtolower(trim($act->animal_type));
            $animalType = $animalTypeMapping[$lowerType] ?? 'other';
        }
        
        // Определяем пол животного
        $animalGender = 'unknown';
        if ($act->animal_gender) {
            $lowerGender = mb_strtolower(trim($act->animal_gender));
            $animalGender = $genderMapping[$lowerGender] ?? 'unknown';
        }
        
        // Создаем животных
        for ($i = 1; $i <= $animalsCount; $i++) {
            $animalName = null;
            if ($animalsCount > 1) {
                $animalName = "Из акта {$act->act_number} №{$i}";
            } else {
                $animalName = "Из акта {$act->act_number}";
            }
            
            Animal::create([
                'name' => $animalName,
                'type' => $animalType,
                'gender' => $animalGender,
                'breed' => $act->animal_breed,
                'color' => $act->animal_color,
                'description' => $this->buildAnimalDescription($act, $i),
                'osvv_request_id' => $act->osvv_request_id,
                'current_stage_id' => $firstStage->id,
                'arrived_at' => $act->capture_date ? Carbon::parse($act->capture_date) : now(),
                'stage_started_at' => now(),
                'status' => 'active',
            ]);
        }
    }
    
    /**
     * Формирование описания животного на основе данных акта
     */
    private function buildAnimalDescription(CaptureAct $act, int $animalNumber): string
    {
        $description = [];
        
        $description[] = "Животное #{$animalNumber} из акта отлова #{$act->act_number}";
        $description[] = "Дата отлова: " . ($act->capture_date ? $act->capture_date->format('d.m.Y') : 'Не указана');
        $description[] = "Место отлова: " . ($act->capture_location ?? 'Не указано');
        
        if ($act->animal_size) {
            $description[] = "Размер: {$act->animal_size}";
        }
        
        if ($act->animal_features) {
            $description[] = "Особые приметы: {$act->animal_features}";
        }
        
        if ($act->animal_behavior) {
            $description[] = "Поведение: {$act->animal_behavior}";
        }
        
        if ($act->capturing_method) {
            $description[] = "Способ отлова: {$act->capturing_method}";
        }
        
        if ($act->notes) {
            $description[] = "Примечания: {$act->notes}";
        }
        
        return implode("\n", $description);
    }
}
