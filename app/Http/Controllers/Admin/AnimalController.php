<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\AnimalStage;
use App\Models\StageTask;
use App\Models\AnimalTaskCompletion;
use App\Models\OsvvRequest;
use App\Models\AnimalRegistrationCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use App\Models\AnimalCageMovement;

class AnimalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Получаем все этапы с животными
        $stages = AnimalStage::active()
            ->ordered()
            ->with(['animals' => function($query) {
                $query->where('status', 'active')
                      ->with(['osvvRequest', 'currentStage'])
                      ->orderBy('arrived_at', 'desc');
            }])
            ->get();

        // Статистика
        $totalAnimals = Animal::where('status', 'active')->count();
        $overdueAnimals = 0;
        
        foreach ($stages as $stage) {
            $overdueAnimals += $stage->getOverdueAnimalsCount();
        }

        return view('admin.animals.index', compact('stages', 'totalAnimals', 'overdueAnimals'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $stages = AnimalStage::active()->ordered()->get();
        $osvvRequests = OsvvRequest::whereDoesntHave('animal')->get();
        
        return view('admin.animals.create', compact('stages', 'osvvRequests'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'cage_number' => 'nullable|string|max:255',
            'type' => 'required|in:dog,cat,other',
            'gender' => 'required|in:male,female,unknown',
            'breed' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'age_months' => 'nullable|integer|min:0|max:300',
            'weight' => 'nullable|numeric|min:0|max:200',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'chip_number' => 'nullable|string|max:255|unique:animals',
            'tag_number' => 'nullable|string|max:255|unique:animals',
            'osvv_request_id' => 'nullable|exists:osvv_requests,id',
            'current_stage_id' => 'required|exists:animal_stages,id',
        ]);

        // Обработка загрузки фото
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('animals', 'public');
            $validated['photo'] = $photoPath;
        }

        // Устанавливаем даты
        $validated['arrived_at'] = now();
        $validated['stage_started_at'] = now();

        $animal = Animal::create($validated);

        return redirect()->route('admin.animals.index')
            ->with('success', 'Животное успешно добавлено в систему');
    }

    /**
     * Display the specified resource.
     */
    public function show(Animal $animal)
    {
        $animal->load(['osvvRequest', 'currentStage.tasks', 'taskCompletions.task', 'taskCompletions.completedBy', 'registrationCard']);

        // Если регистрационной карточки ещё нет, подставляем пустую модель для рендера формы
        if (!$animal->registrationCard) {
            $animal->setRelation('registrationCard', new AnimalRegistrationCard(['animal_id' => $animal->id]));
        }
        
        // Получаем задачи текущего этапа с отметками о выполнении
        $currentStageTasks = collect();
        if ($animal->currentStage) {
            $currentStageTasks = $animal->currentStage->tasks->map(function($task) use ($animal) {
                $completion = $animal->taskCompletions->where('task_id', $task->id)->first();
                $task->is_completed = $completion !== null;
                $task->completion = $completion;
                return $task;
            });
        }

        return view('admin.animals.show', compact('animal', 'currentStageTasks'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Animal $animal)
    {
        $stages = AnimalStage::active()->ordered()->get();
        $osvvRequests = OsvvRequest::whereDoesntHave('animal')
            ->orWhere('id', $animal->osvv_request_id)
            ->get();
        
        return view('admin.animals.edit', compact('animal', 'stages', 'osvvRequests'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Animal $animal)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'cage_number' => 'nullable|string|max:255',
            'type' => 'required|in:dog,cat,other',
            'gender' => 'required|in:male,female,unknown',
            'breed' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'age_months' => 'nullable|integer|min:0|max:300',
            'weight' => 'nullable|numeric|min:0|max:200',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'chip_number' => 'nullable|string|max:255|unique:animals,chip_number,' . $animal->id,
            'tag_number' => 'nullable|string|max:255|unique:animals,tag_number,' . $animal->id,
            'osvv_request_id' => 'nullable|exists:osvv_requests,id',
            'status' => 'required|in:active,released,adopted,deceased',
        ]);

        // Обработка загрузки фото
        if ($request->hasFile('photo')) {
            // Удаляем старое фото если есть
            if ($animal->photo) {
                \Storage::disk('public')->delete($animal->photo);
            }
            $photoPath = $request->file('photo')->store('animals', 'public');
            $validated['photo'] = $photoPath;
        }

        $animal->update($validated);

        return redirect()->route('admin.animals.show', $animal)
            ->with('success', 'Информация о животном обновлена');
    }

    /**
     * Обновление регистрационной карточки с формы на странице просмотра
     */
    public function updateRegistrationCard(Request $request, Animal $animal)
    {
        $validated = $request->validate([
            'registration_number' => 'nullable|string|max:255',
            'registration_date' => 'nullable|date',
            'category' => 'nullable|string|max:100',
            'photo_face' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'photo_profile' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'intake_source' => 'nullable|string|max:255',
            'intake_location' => 'nullable',
            'capture_location_address' => 'nullable|string',
            'physical_description' => 'nullable|string',
            'coat' => 'nullable|string|max:255',
            'ears' => 'nullable|string|max:255',
            'tail' => 'nullable|string|max:255',
            'size' => 'nullable|string|max:255',
            'clinical_exam_date' => 'nullable|date',
            'clinical_exam_conclusion' => 'nullable|string',
            'aggression_notes' => 'nullable|string',
            'behavior_correction_notes' => 'nullable|string',
            'deworming_date' => 'nullable|date',
            'sterilization_date' => 'nullable|date',
            'sterilization_vet' => 'nullable|string|max:255',
            'marking_date' => 'nullable|date',
            'tag_number_card' => 'nullable|string|max:255',
            'chip_number_card' => 'nullable|string|max:255',
            'aggression_act_number' => 'nullable|string|max:255',
            'aggression_act_date' => 'nullable|date',
            'capture_act_number' => 'nullable|string|max:255',
            'capture_act_date' => 'nullable|date',
            'vet_doc_number' => 'nullable|string|max:255',
            'vet_doc_date' => 'nullable|date',
            'outcome_reason' => 'nullable|string',
            'outcome_date' => 'nullable|date',
            'release_address' => 'nullable|string',
            'new_owner_type' => 'nullable|in:legal,individual',
            'new_owner_name' => 'nullable|string|max:255',
            'new_owner_address' => 'nullable|string',
            'new_owner_phone' => 'nullable|string|max:255',
        ]);

        // Найти или создать карточку
        $card = $animal->registrationCard ?: new AnimalRegistrationCard(['animal_id' => $animal->id]);

        // Загрузка фото
        if ($request->hasFile('photo_face')) {
            if ($card->photo_face) { \Storage::disk('public')->delete($card->photo_face); }
            $card->photo_face = $request->file('photo_face')->store('animal_cards', 'public');
        }
        if ($request->hasFile('photo_profile')) {
            if ($card->photo_profile) { \Storage::disk('public')->delete($card->photo_profile); }
            $card->photo_profile = $request->file('photo_profile')->store('animal_cards', 'public');
        }

        // Присвоение простых полей
        foreach ([
            'registration_number','registration_date','category','intake_source','capture_location_address',
            'physical_description','coat','ears','tail','size','clinical_exam_date','clinical_exam_conclusion',
            'aggression_notes','behavior_correction_notes','deworming_date','sterilization_date','sterilization_vet',
            'marking_date','tag_number_card','chip_number_card','aggression_act_number','aggression_act_date',
            'capture_act_number','capture_act_date','vet_doc_number','vet_doc_date','outcome_reason','outcome_date',
            'release_address','new_owner_type','new_owner_name','new_owner_address','new_owner_phone'
        ] as $field) {
            if (array_key_exists($field, $validated)) {
                $card->$field = $validated[$field];
            }
        }

        // intake_location может быть строкой или JSON
        if ($request->filled('intake_location')) {
            $raw = $request->input('intake_location');
            $card->intake_location = is_array($raw) ? $raw : ['address' => $raw];
        }

        $card->animal_id = $animal->id;
        $card->save();

        return redirect()->route('admin.animals.show', $animal)
            ->with('success', 'Регистрационная карточка обновлена');
    }

    /**
     * Обновление одной формой: основные данные Animal + регистрационная карточка
     */
    public function updateFullCard(Request $request, Animal $animal)
    {
        // Динамические правила для фото: делаем необязательными
        $requireMainPhoto = false;
        $existingCard = $animal->registrationCard;
        $requireFacePhoto = false;
        $requireProfilePhoto = false;

        $animalValidated = $request->validate([
            'name' => 'nullable|string|max:255',
            'cage_number' => 'nullable|string|max:255',
            'type' => 'nullable|in:dog,cat,other',
            'gender' => 'nullable|in:male,female,unknown',
            'breed' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'age_months' => 'nullable|integer|min:0|max:300',
            'weight' => 'nullable|numeric|min:0|max:200',
            'chip_number' => 'nullable|string|max:255|unique:animals,chip_number,' . $animal->id,
            'tag_number' => 'nullable|string|max:255|unique:animals,tag_number,' . $animal->id,
            'photo' => [($requireMainPhoto ? 'required' : 'nullable'), 'image', 'mimes:jpeg,png,jpg,gif', 'max:4096'],
        ]);

        $cardValidated = $request->validate([
            'registration_number' => 'nullable|string|max:255',
            'registration_date' => 'nullable|date',
            'category' => 'nullable|string|max:100',
            'intake_source' => 'nullable|string|max:255',
            'capture_location_address' => 'nullable|string',
            'physical_description' => 'nullable|string',
            'coat' => 'nullable|string|max:255',
            'ears' => 'nullable|string|max:255',
            'tail' => 'nullable|string|max:255',
            'size' => 'nullable|string|max:255',
            'clinical_exam_date' => 'nullable|date',
            'clinical_exam_conclusion' => 'nullable|string',
            'aggression_notes' => 'nullable|string',
            'behavior_correction_notes' => 'nullable|string',
            'deworming_date' => 'nullable|date',
            'sterilization_date' => 'nullable|date',
            'sterilization_vet' => 'nullable|string|max:255',
            'marking_date' => 'nullable|date',
            'aggression_act_number' => 'nullable|string|max:255',
            'aggression_act_date' => 'nullable|date',
            'capture_act_number' => 'nullable|string|max:255',
            'capture_act_date' => 'nullable|date',
            'vet_doc_number' => 'nullable|string|max:255',
            'vet_doc_date' => 'nullable|date',
            'vaccination_act_number' => 'nullable|string|max:255',
            'vaccination_act_date' => 'nullable|date',
            'vaccination_type' => 'nullable|string|max:255',
            'vaccination_series' => 'nullable|string|max:255',
            'vaccination_manufacture_date' => 'nullable|string|max:255',
            'outcome_reason' => 'nullable|string',
            'outcome_date' => 'nullable|date',
            'release_address' => 'nullable|string',
            'new_owner_type' => 'nullable|in:legal,individual',
            'new_owner_name' => 'nullable|string|max:255',
            'new_owner_address' => 'nullable|string',
            'new_owner_phone' => 'nullable|string|max:255',
            'photo_face' => [($requireFacePhoto ? 'required' : 'nullable'), 'image', 'mimes:jpeg,png,jpg,gif', 'max:4096'],
            'photo_profile' => [($requireProfilePhoto ? 'required' : 'nullable'), 'image', 'mimes:jpeg,png,jpg,gif', 'max:4096'],
        ]);

        DB::transaction(function () use ($animal, $animalValidated, $cardValidated, $request) {
            $animal->update($animalValidated);

            $card = $animal->registrationCard;
            if (!$card) {
                $card = new AnimalRegistrationCard(['animal_id' => $animal->id]);
                // Генерируем регистрационный номер, если не указан
                if (empty($cardValidated['registration_number'])) {
                    $card->registration_number = AnimalRegistrationCard::generateRegistrationNumber();
                }
            }
            
            foreach ($cardValidated as $key => $value) {
                $card->$key = $value;
            }
            if ($request->filled('intake_location')) {
                $raw = $request->input('intake_location');
                $card->intake_location = is_array($raw) ? $raw : ['address' => $raw];
            }
            // Синхронизация чип/бирка на случай заполнения только Animal
            if (!empty($animalValidated['chip_number'])) {
                $card->chip_number_card = $animalValidated['chip_number'];
            }
            if (!empty($animalValidated['tag_number'])) {
                $card->tag_number_card = $animalValidated['tag_number'];
            }

            // Загрузка фото: основное фото Animal
            if ($request->hasFile('photo')) {
                if ($animal->photo) {
                    \Storage::disk('public')->delete($animal->photo);
                }
                $path = $request->file('photo')->store('animals', 'public');
                $animal->photo = $path;
                $animal->save();
            }
            // Фото морды / профиль для карточки
            if ($request->hasFile('photo_face')) {
                if ($card->photo_face) { \Storage::disk('public')->delete($card->photo_face); }
                $card->photo_face = $request->file('photo_face')->store('animal_cards', 'public');
            }
            if ($request->hasFile('photo_profile')) {
                if ($card->photo_profile) { \Storage::disk('public')->delete($card->photo_profile); }
                $card->photo_profile = $request->file('photo_profile')->store('animal_cards', 'public');
            }
            $card->animal_id = $animal->id;
            $card->save();
        });

        return redirect()->route('admin.animals.show', $animal)
            ->with('success', 'Карточка животного сохранена');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Animal $animal)
    {
        // Удаляем фото если есть
        if ($animal->photo) {
            \Storage::disk('public')->delete($animal->photo);
        }

        $animal->delete();

        return redirect()->route('admin.animals.index')
            ->with('success', 'Животное удалено из системы');
    }

    /**
     * Перемещение животного на следующий этап
     */
    public function moveToStage(Request $request, Animal $animal)
    {
        $validated = $request->validate([
            'stage_id' => 'required|exists:animal_stages,id',
        ]);

        $newStage = AnimalStage::find($validated['stage_id']);
        
        // Проверяем, можно ли перейти на этот этап
        if (!$animal->canMoveToNextStage() && $newStage->order > $animal->currentStage->order) {
            return response()->json([
                'success' => false,
                'message' => 'Не все обязательные задачи текущего этапа выполнены'
            ], 400);
        }

        DB::transaction(function() use ($animal, $newStage) {
            // Статус остается 'active' для всех этапов, кроме случаев когда явно выбрана судьба
            // Статус меняется только через методы releaseToOriginalPlace или keepInShelter
            $animal->update([
                'current_stage_id' => $newStage->id,
                'stage_started_at' => now(),
                'completed_at' => null, // Не завершаем автоматически
                'status' => 'active' // Всегда active при перемещении между этапами
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => "Животное переведено на этап: {$newStage->name}"
        ]);
    }

    /**
     * Перемещение животного в/из вольера (drag&drop)
     */
    public function moveToCage(Request $request)
    {
        $validated = $request->validate([
            'animal_id' => 'required|exists:animals,id',
            'to_cage' => 'nullable|string|max:255',
            'comment' => 'nullable|string',
        ]);

        $animal = Animal::findOrFail($validated['animal_id']);
        $from = $animal->cage_number;
        $to = $validated['to_cage'] ?? null;

        DB::transaction(function () use ($animal, $from, $to, $validated) {
            $animal->update(['cage_number' => $to]);

            AnimalCageMovement::create([
                'animal_id' => $animal->id,
                'from_cage' => $from,
                'to_cage' => $to,
                'comment' => $validated['comment'] ?? null,
                'moved_at' => now(),
                'moved_by' => FacadesAuth::id(),
            ]);
        });

        return response()->json(['success' => true]);
    }

    /**
     * Отметить задачу как выполненную
     */
    public function completeTask(Request $request, Animal $animal)
    {
        $validated = $request->validate([
            'task_id' => 'required|exists:stage_tasks,id',
            'notes' => 'nullable|string',
            'data' => 'nullable|array',
        ]);

        $task = StageTask::find($validated['task_id']);
        
        // Проверяем, что задача относится к текущему этапу животного
        if ($task->stage_id !== $animal->current_stage_id) {
            return response()->json([
                'success' => false,
                'message' => 'Задача не относится к текущему этапу животного'
            ], 400);
        }

        // Проверяем, не выполнена ли уже задача
        $existingCompletion = AnimalTaskCompletion::where('animal_id', $animal->id)
            ->where('task_id', $task->id)
            ->first();

        if ($existingCompletion) {
            return response()->json([
                'success' => false,
                'message' => 'Задача уже выполнена'
            ], 400);
        }

        AnimalTaskCompletion::create([
            'animal_id' => $animal->id,
            'task_id' => $task->id,
            'completed_by' => Auth::id(),
            'completed_at' => now(),
            'notes' => $validated['notes'] ?? null,
            'data' => $validated['data'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Задача '{$task->name}' отмечена как выполненная"
        ]);
    }

    /**
     * Отменить выполнение задачи
     */
    public function uncompleteTask(Animal $animal, StageTask $task)
    {
        $completion = AnimalTaskCompletion::where('animal_id', $animal->id)
            ->where('task_id', $task->id)
            ->first();

        if (!$completion) {
            return response()->json([
                'success' => false,
                'message' => 'Задача не была выполнена'
            ], 400);
        }

        $completion->delete();

        return response()->json([
            'success' => true,
            'message' => "Выполнение задачи '{$task->name}' отменено"
        ]);
    }

    /**
     * Экспорт данных в CSV
     */
    public function export()
    {
        $animals = Animal::with(['osvvRequest', 'currentStage'])->get();
        
        $filename = 'animals_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($animals) {
            $file = fopen('php://output', 'w');
            
            // Заголовки CSV
            fputcsv($file, [
                'ID',
                'Кличка',
                'Тип',
                'Пол',
                'Порода',
                'Окрас',
                'Возраст (мес)',
                'Вес (кг)',
                'Номер чипа',
                'Номер бирки',
                'Текущий этап',
                'Статус',
                'Дата поступления',
                'Дней на текущем этапе',
                'Заявка ОСВВ'
            ]);

            foreach ($animals as $animal) {
                fputcsv($file, [
                    $animal->id,
                    $animal->name,
                    $animal->type_name,
                    $animal->gender_name,
                    $animal->breed,
                    $animal->color,
                    $animal->age_months,
                    $animal->weight,
                    $animal->chip_number,
                    $animal->tag_number,
                    $animal->currentStage->name,
                    $animal->status_name,
                    $animal->arrived_at->format('d.m.Y'),
                    $animal->getDaysInCurrentStage(),
                    $animal->osvvRequest ? "#{$animal->osvvRequest->id}" : ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Печать карточки животного
     */
    public function printCard(Animal $animal)
    {
        $animal->load(['registrationCard', 'osvvRequest', 'currentStage']);
        
        return view('admin.animals.print', compact('animal'));
    }

    /**
     * Быстрое обновление отдельного поля
     */
    public function quickUpdate(Request $request, Animal $animal)
    {
        $field = $request->input('field');
        $value = $request->input('value');

        // Список разрешенных полей для быстрого обновления
        $allowedFields = [
            'chip_number', 'tag_number', 'name', 'cage_number', 
            'breed', 'color', 'age_months', 'weight'
        ];

        if (!in_array($field, $allowedFields)) {
            return response()->json(['success' => false, 'message' => 'Недопустимое поле'], 400);
        }

        // Валидация в зависимости от поля
        $rules = [
            'chip_number' => 'nullable|string|max:255|unique:animals,chip_number,' . $animal->id,
            'tag_number' => 'nullable|string|max:255|unique:animals,tag_number,' . $animal->id,
            'name' => 'nullable|string|max:255',
            'cage_number' => 'nullable|string|max:255',
            'breed' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'age_months' => 'nullable|integer|min:0|max:300',
            'weight' => 'nullable|numeric|min:0|max:200',
        ];

        $validator = \Validator::make(['value' => $value], ['value' => $rules[$field]]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'message' => $validator->errors()->first('value')
            ], 422);
        }

        $animal->$field = $value;
        $animal->save();

        return response()->json([
            'success' => true, 
            'message' => 'Поле обновлено',
            'value' => $animal->$field
        ]);
    }

    /**
     * Выпуск животного на прежнее место
     */
    public function releaseToOriginalPlace(Request $request, Animal $animal)
    {
        // Изменяем статус на "released" (выпущен)
        $animal->status = 'released';
        $animal->completed_at = now();
        $animal->save();

        // TODO: Добавить животное в планировщик выезда (departure_planner)
        // Это будет реализовано позже

        // Если запрос AJAX - возвращаем JSON
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Животное добавлено в планировщик выезда'
            ]);
        }

        // Иначе редирект
        return redirect()->route('admin.animals.index')
            ->with('success', 'Животное добавлено в планировщик выезда');
    }

    /**
     * Оставить животное в приюте
     */
    public function keepInShelter(Request $request, Animal $animal)
    {
        // Изменяем статус на "adopted" (пристроен/остается в приюте)
        $animal->status = 'adopted';
        $animal->completed_at = now();
        $animal->save();

        // TODO: Добавить животное в список постоянных жителей приюта
        // Это будет реализовано позже

        // Если запрос AJAX - возвращаем JSON
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Животное присоединилось к постоянным жителям приюта'
            ]);
        }

        // Иначе редирект
        return redirect()->route('admin.animals.index')
            ->with('success', 'Животное присоединилось к постоянным жителям приюта');
    }
}
