<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OsvvComment;
use App\Models\OsvvRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OsvvRequestController extends Controller
{
    /**
     * Отображает список заявок ОСВВ.
     */
    public function index(Request $request)
    {
        $requestsQuery = OsvvRequest::query();
        
        // Применяем фильтры
        if ($request->filled('status')) {
            $requestsQuery->where('status', $request->status);
        }
        
        if ($request->filled('date_from')) {
            $requestsQuery->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $requestsQuery->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Фильтр по району
        if ($request->filled('district')) {
            $requestsQuery->where('district', 'like', '%' . $request->district . '%');
        }
        
        // Фильтр по укусу
        if ($request->has('has_bite')) {
            $requestsQuery->where('has_bite', true);
        }
        
        // Фильтр по беременности
        if ($request->has('is_pregnant')) {
            $requestsQuery->where('is_pregnant', true);
        }
        
        // Фильтр по просроченному дедлайну
        if ($request->has('deadline_overdue')) {
            $requestsQuery->where('deadline_date', '<', now())
                          ->whereNull('departure_date');
        }
        
        // Применяем сортировку
        if ($request->filled('sort')) {
            $direction = $request->input('direction', 'asc');
            $requestsQuery->orderBy($request->sort, $direction);
        } else {
            // По умолчанию сортируем по дате создания (последние сверху)
            $requestsQuery->latest();
        }
        
        // Получаем список заявок с пагинацией
        $requests = $requestsQuery->paginate(15)->withQueryString();
        
        // Получаем счетчики по статусам
        $statusCounts = [
            'all' => OsvvRequest::count(),
            'new' => OsvvRequest::where('status', 'new')->count(),
            'processing' => OsvvRequest::where('status', 'processing')->count(),
            'capture_scheduled' => OsvvRequest::where('status', 'capture_scheduled')->count(),
            'captured' => OsvvRequest::where('status', 'captured')->count(),
            'in_shelter' => OsvvRequest::where('status', 'in_shelter')->count(),
            'sterilized' => OsvvRequest::where('status', 'sterilized')->count(),
            'vaccinated' => OsvvRequest::where('status', 'vaccinated')->count(),
            'ready_for_return' => OsvvRequest::where('status', 'ready_for_return')->count(),
            'returned' => OsvvRequest::where('status', 'returned')->count(),
            'completed' => OsvvRequest::where('status', 'completed')->count(),
            'cancelled' => OsvvRequest::where('status', 'cancelled')->count(),
            'deadline_overdue' => OsvvRequest::where('deadline_date', '<', now())
                                              ->whereNull('departure_date')
                                              ->count(),
            'has_bite' => OsvvRequest::where('has_bite', true)->count(),
        ];
        
        // Формируем список на выезд на сегодня
        $todayDepartureList = $this->generateTodayDepartureList();
        
        return view('admin.osvv.index', compact('requests', 'statusCounts', 'todayDepartureList'));
    }

    /**
     * Отображает форму создания заявки ОСВВ.
     */
    public function create()
    {
        return view('admin.osvv.create');
    }

    /**
     * Сохраняет новую заявку ОСВВ, созданную администратором.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'contact_name' => 'required|string|max:255',
            'contact_phone' => ['required', 'string', function ($attribute, $value, $fail) {
                if (!$this->validatePhoneNumber($value)) {
                    $fail('Неверный формат номера телефона. Поддерживаются мобильные номера и городские номера Воронежа (473).');
                }
            }],
            'contact_email' => 'nullable|email|max:255',
            'case_description' => 'nullable|string|max:2000',
            'source_type' => 'nullable|in:district_office,telegram,vkontakte,phone,media,other',
            'source_district' => 'nullable|string|max:100|required_if:source_type,district_office',
            'aurora_number' => 'nullable|string|max:50',
            'animal_type' => 'required|in:cat,dog,other',
            'animal_type_other' => 'nullable|string|max:255|required_if:animal_type,other',
            'animal_gender' => 'required|in:male,female,unknown',
            'animal_age' => 'nullable|string|max:255',
            'animal_description' => 'nullable|string',
            'animal_photos.*' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov,avi|max:51200',
            'location_address' => 'required|string',
            'location_landmark' => 'nullable|string',
            'additional_addresses' => 'nullable|array',
            'additional_addresses.*.address' => 'required|string',
            'additional_addresses.*.landmark' => 'nullable|string',
            'additional_addresses.*.latitude' => 'nullable|numeric|between:-90,90',
            'additional_addresses.*.longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'required|in:new,processing,capture_scheduled,captured,in_shelter,sterilized,vaccinated,ready_for_return,returned,completed,cancelled',
            'notes' => 'nullable|string',
            'animals_count' => 'nullable|integer|min:1',
            'district' => 'nullable|string|max:255',
            'has_bite' => 'nullable|boolean',
            'bite_medical_files.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'bite_evidence_files.*' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov,avi|max:51200',
            'is_pregnant' => 'nullable|boolean',
            'has_tags' => 'nullable|boolean',
            'departure_date' => 'nullable|date',
            'capture_result' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);
        
        // Если количество животных не указано, устанавливаем значение по умолчанию
        if (!isset($validated['animals_count'])) {
            $validated['animals_count'] = 1;
        }
        
        // Обрабатываем загрузку файлов медицинских справок
        $medicalFiles = [];
        if ($request->hasFile('bite_medical_files')) {
            foreach ($request->file('bite_medical_files') as $file) {
                $path = $file->store('osvv/bite_medical', 'public');
                $medicalFiles[] = $path;
            }
        }
        $validated['bite_medical_files'] = $medicalFiles;
        
        // Обрабатываем загрузку файлов фото/видео фиксации
        $evidenceFiles = [];
        if ($request->hasFile('bite_evidence_files')) {
            foreach ($request->file('bite_evidence_files') as $file) {
                $path = $file->store('osvv/bite_evidence', 'public');
                $evidenceFiles[] = $path;
            }
        }
        $validated['bite_evidence_files'] = $evidenceFiles;
        
        // Обрабатываем загрузку фотографий животного
        $animalPhotos = [];
        if ($request->hasFile('animal_photos')) {
            foreach ($request->file('animal_photos') as $file) {
                $path = $file->store('osvv/animal_photos', 'public');
                $animalPhotos[] = $path;
            }
        }
        $validated['animal_photos'] = $animalPhotos;
        
        // Обрабатываем дополнительные адреса
        if (isset($validated['additional_addresses']) && is_array($validated['additional_addresses'])) {
            $additionalLocations = [];
            foreach ($validated['additional_addresses'] as $additionalAddress) {
                if (!empty($additionalAddress['address'])) {
                    $additionalLocations[] = [
                        'address' => $additionalAddress['address'],
                        'landmark' => $additionalAddress['landmark'] ?? null,
                        'latitude' => $additionalAddress['latitude'] ?? null,
                        'longitude' => $additionalAddress['longitude'] ?? null,
                    ];
                }
            }
            $validated['locations'] = $additionalLocations;
        }
        
        // Удаляем additional_addresses из validated, так как мы сохраняем их в locations
        unset($validated['additional_addresses']);
        
        $osvvRequest = OsvvRequest::create(array_merge($validated, [
            'user_id' => Auth::id(),
        ]));
        
        // Автоматически рассчитываем крайний срок выезда
        $osvvRequest->calculateDeadlineDate()->save();
        
        // Добавляем комментарий о создании заявки
        $osvvRequest->comments()->create([
            'user_id' => Auth::id() ?? 1,
            'comment' => 'Заявка создана через административную панель.',
        ]);
        
        return redirect()->route('admin.osvv.show', $osvvRequest)
            ->with('success', 'Заявка успешно создана.');
    }

    /**
     * Отображает детали заявки ОСВВ.
     */
    public function show(OsvvRequest $osvvRequest)
    {
        $osvvRequest->load(['comments.user', 'user', 'captureActs']);
        
        return view('admin.osvv.show', compact('osvvRequest'));
    }

    /**
     * Отображает форму редактирования заявки ОСВВ.
     */
    public function edit(OsvvRequest $osvvRequest)
    {
        return view('admin.osvv.edit', compact('osvvRequest'));
    }

    /**
     * Обновляет заявку ОСВВ.
     */
    public function update(Request $request, OsvvRequest $osvvRequest)
    {
        $validated = $request->validate([
            'contact_name' => 'required|string|max:255',
            'contact_phone' => ['required', 'string', function ($attribute, $value, $fail) {
                if (!$this->validatePhoneNumber($value)) {
                    $fail('Неверный формат номера телефона. Поддерживаются мобильные номера и городские номера Воронежа (473).');
                }
            }],
            'contact_email' => 'nullable|email|max:255',
            'case_description' => 'nullable|string|max:2000',
            'source_type' => 'nullable|in:district_office,telegram,vkontakte,phone,media,other',
            'source_district' => 'nullable|string|max:100|required_if:source_type,district_office',
            'aurora_number' => 'nullable|string|max:50',
            'animal_type' => 'required|in:cat,dog,other',
            'animal_type_other' => 'nullable|string|max:255|required_if:animal_type,other',
            'animal_gender' => 'required|in:male,female,unknown',
            'animal_age' => 'nullable|string|max:255',
            'animal_description' => 'nullable|string',
            'animal_photos.*' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov,avi|max:51200',
            'location_address' => 'required|string',
            'location_landmark' => 'nullable|string',
            'additional_addresses' => 'nullable|array',
            'additional_addresses.*.address' => 'required|string',
            'additional_addresses.*.landmark' => 'nullable|string',
            'additional_addresses.*.latitude' => 'nullable|numeric|between:-90,90',
            'additional_addresses.*.longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'required|in:new,processing,capture_scheduled,captured,in_shelter,sterilized,vaccinated,ready_for_return,returned,completed,cancelled',
            'notes' => 'nullable|string',
            'created_at' => 'nullable|date',
            'animals_count' => 'nullable|integer|min:1',
            'district' => 'nullable|string|max:255',
            'has_bite' => 'nullable|boolean',
            'bite_medical_files.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'bite_evidence_files.*' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov,avi|max:51200',
            'is_pregnant' => 'nullable|boolean',
            'has_tags' => 'nullable|boolean',
            'departure_date' => 'nullable|date',
            'capture_result' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);
        
        // Если количество животных не указано, устанавливаем значение по умолчанию
        if (!isset($validated['animals_count'])) {
            $validated['animals_count'] = 1;
        }
        
        // Обрабатываем загрузку новых файлов медицинских справок
        if ($request->hasFile('bite_medical_files')) {
            $medicalFiles = $osvvRequest->bite_medical_files ?? [];
            foreach ($request->file('bite_medical_files') as $file) {
                $path = $file->store('osvv/bite_medical', 'public');
                $medicalFiles[] = $path;
            }
            $validated['bite_medical_files'] = $medicalFiles;
        }
        
        // Обрабатываем загрузку новых файлов фото/видео фиксации
        if ($request->hasFile('bite_evidence_files')) {
            $evidenceFiles = $osvvRequest->bite_evidence_files ?? [];
            foreach ($request->file('bite_evidence_files') as $file) {
                $path = $file->store('osvv/bite_evidence', 'public');
                $evidenceFiles[] = $path;
            }
            $validated['bite_evidence_files'] = $evidenceFiles;
        }
        
        // Обрабатываем загрузку новых фотографий животного
        if ($request->hasFile('animal_photos')) {
            $animalPhotos = $osvvRequest->animal_photos ?? [];
            foreach ($request->file('animal_photos') as $file) {
                $path = $file->store('osvv/animal_photos', 'public');
                $animalPhotos[] = $path;
            }
            $validated['animal_photos'] = $animalPhotos;
        }
        
        // Обрабатываем дополнительные адреса
        if (isset($validated['additional_addresses']) && is_array($validated['additional_addresses'])) {
            $additionalLocations = [];
            foreach ($validated['additional_addresses'] as $additionalAddress) {
                if (!empty($additionalAddress['address'])) {
                    $additionalLocations[] = [
                        'address' => $additionalAddress['address'],
                        'landmark' => $additionalAddress['landmark'] ?? null,
                        'latitude' => $additionalAddress['latitude'] ?? null,
                        'longitude' => $additionalAddress['longitude'] ?? null,
                    ];
                }
            }
            $validated['locations'] = $additionalLocations;
        }
        
        // Удаляем additional_addresses из validated, так как мы сохраняем их в locations
        unset($validated['additional_addresses']);
        
        // Обрабатываем чекбоксы, устанавливая значения по умолчанию
        $validated['has_bite'] = isset($validated['has_bite']);
        $validated['is_pregnant'] = isset($validated['is_pregnant']);
        $validated['has_tags'] = isset($validated['has_tags']);
        
        // Получаем старое значение флага укуса для проверки изменения
        $oldHasBite = $osvvRequest->has_bite;
        $oldDeadline = $osvvRequest->deadline_date ? $osvvRequest->deadline_date->format('d.m.Y') : 'не установлен';
        
        $osvvRequest->update($validated);
        
        // Если изменился флаг укуса, пересчитываем срок выезда
        if ($oldHasBite !== $osvvRequest->has_bite) {
            $osvvRequest->calculateDeadlineDate()->save();
            
            $newDeadline = $osvvRequest->deadline_date ? $osvvRequest->deadline_date->format('d.m.Y') : 'не установлен';
            
            // Добавляем информативный комментарий об изменении срока выезда
            $biteStatus = $osvvRequest->has_bite ? 'с укусом (срок 1 день)' : 'без укуса (срок 6 дней)';
            
            $comment = '🚨 Изменен статус укуса животного:';
            $comment .= "\n• Было: " . ($oldHasBite ? 'с укусом' : 'без укуса');
            $comment .= "\n• Стало: " . ($osvvRequest->has_bite ? 'с укусом' : 'без укуса');
            $comment .= "\n\n🕒 Крайний срок выезда автоматически пересчитан:";
            $comment .= "\n• Был: " . $oldDeadline;
            $comment .= "\n• Стал: " . $newDeadline;
            $comment .= "\n• Причина: заявка " . $biteStatus;
            
            $osvvRequest->comments()->create([
                'user_id' => Auth::id() ?? 1,
                'comment' => $comment,
            ]);
        }
        
        // Если передана дата создания, обновляем её отдельно
        if ($request->filled('created_at')) {
            $osvvRequest->created_at = $request->created_at;
            $osvvRequest->save();
            
            // Пересчитываем крайний срок выезда после изменения даты создания
            $osvvRequest->calculateDeadlineDate()->save();
            
            // Добавляем комментарий об изменении даты
            $osvvRequest->comments()->create([
                'user_id' => Auth::id() ?? 1,
                'comment' => 'Дата создания заявки изменена на: ' . \Carbon\Carbon::parse($request->created_at)->format('d.m.Y H:i:s'),
            ]);
        }
        
        // Добавляем комментарий об обновлении заявки
        $osvvRequest->comments()->create([
            'user_id' => Auth::id() ?? 1,
            'comment' => 'Заявка была отредактирована.',
        ]);
        
        return redirect()->route('admin.osvv.show', $osvvRequest)
            ->with('success', 'Заявка успешно обновлена.');
    }

    /**
     * Добавляет комментарий к заявке ОСВВ.
     */
    public function addComment(Request $request, OsvvRequest $osvvRequest)
    {
        $validated = $request->validate([
            'comment' => 'required|string',
        ]);
        
        $osvvRequest->comments()->create([
            'user_id' => Auth::id() ?? 1,
            'comment' => $validated['comment'],
        ]);
        
        return redirect()->route('admin.osvv.show', $osvvRequest)
            ->with('success', 'Комментарий успешно добавлен.');
    }

    /**
     * Изменяет статус заявки ОСВВ.
     */
    public function changeStatus(Request $request, OsvvRequest $osvvRequest)
    {
        $validated = $request->validate([
            'status' => 'required|in:new,processing,capture_scheduled,captured,in_shelter,sterilized,vaccinated,ready_for_return,returned,completed,cancelled',
        ]);
        
        // Проверка возможности перехода в новый статус
        if (!$osvvRequest->canTransitionTo($validated['status'])) {
            return redirect()->route('admin.osvv.show', $osvvRequest)
                ->with('error', 'Невозможно изменить статус на указанный.');
        }
        
        $osvvRequest->update([
            'status' => $validated['status'],
        ]);
        
        // Добавляем комментарий об изменении статуса
        $osvvRequest->comments()->create([
            'user_id' => Auth::id() ?? 1,
            'comment' => 'Статус изменен на: ' . $this->getStatusName($validated['status']),
        ]);
        
        return redirect()->route('admin.osvv.show', $osvvRequest)
            ->with('success', 'Статус успешно изменен.');
    }

    /**
     * Изменяет дату создания заявки ОСВВ.
     */
    public function updateCreatedAt(Request $request, OsvvRequest $osvvRequest)
    {
        $validated = $request->validate([
            'created_at' => 'required|date',
        ]);
        
        $oldDate = $osvvRequest->created_at->format('d.m.Y H:i:s');
        $oldDeadline = $osvvRequest->deadline_date ? $osvvRequest->deadline_date->format('d.m.Y') : 'не установлен';
        
        // Обновляем дату создания
        $osvvRequest->created_at = $validated['created_at'];
        
        // Пересчитываем крайний срок выезда
        $osvvRequest->calculateDeadlineDate();
        
        // Сохраняем изменения
        $osvvRequest->save();
        
        $newDeadline = $osvvRequest->deadline_date ? $osvvRequest->deadline_date->format('d.m.Y') : 'не установлен';
        
        // Добавляем комментарий об изменении даты и крайнего срока
        $comment = 'Дата создания заявки изменена с ' . $oldDate . ' на ' . \Carbon\Carbon::parse($validated['created_at'])->format('d.m.Y H:i:s');
        
        if ($oldDeadline !== $newDeadline) {
            $comment .= "\n\n🕒 Крайний срок выезда автоматически пересчитан:";
            $comment .= "\n• Был: " . $oldDeadline;
            $comment .= "\n• Стал: " . $newDeadline;
            
            if ($osvvRequest->has_bite) {
                $comment .= "\n• Причина: заявка с укусом (срок 1 день)";
            } else {
                $comment .= "\n• Причина: заявка без укуса (срок 6 дней)";
            }
        }
        
        $osvvRequest->comments()->create([
            'user_id' => Auth::id() ?? 1,
            'comment' => $comment,
        ]);
        
        return redirect()->route('admin.osvv.show', $osvvRequest)
            ->with('success', 'Дата создания заявки успешно изменена. Крайний срок выезда автоматически пересчитан.');
    }
    
    /**
     * Возвращает название статуса на русском языке.
     */
    private function getStatusName($status)
    {
        $statusNames = [
            'new' => 'Новая заявка',
            'processing' => 'В обработке',
            'capture_scheduled' => 'Запланирован отлов',
            'captured' => 'Животное отловлено',
            'in_shelter' => 'В приюте',
            'sterilized' => 'Стерилизовано',
            'vaccinated' => 'Вакцинировано',
            'ready_for_return' => 'Готово к возврату',
            'returned' => 'Возвращено',
            'completed' => 'Завершено',
            'cancelled' => 'Отменено',
        ];
        
        return $statusNames[$status] ?? $status;
    }
    
    /**
     * Обновляет координаты заявки ОСВВ.
     */
    public function updateCoordinates(Request $request, OsvvRequest $osvvRequest)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);
        
        $osvvRequest->update($validated);
        
        // Добавляем комментарий об обновлении координат
        $osvvRequest->comments()->create([
            'user_id' => Auth::id() ?? 1,
            'comment' => 'Обновлены координаты местоположения: ' . 
                'Широта: ' . $validated['latitude'] . ', Долгота: ' . $validated['longitude'],
        ]);
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Регистрирует выезд по заявке ОСВВ.
     */
    public function registerDeparture(Request $request, OsvvRequest $osvvRequest)
    {
        $validated = $request->validate([
            'departure_date' => 'required|date',
            'departure_notes' => 'nullable|string',
            'requires_video' => 'nullable',
            'captured' => 'nullable',
            'animals_count' => 'nullable|integer|min:1|max:20',
            'video_file' => 'nullable|file|mimes:mp4,mov,avi,wmv|max:102400', // 100MB
        ]);
        
        // Обрабатываем чекбоксы (они приходят как "on" или отсутствуют)
        $requiresVideo = $request->has('requires_video');
        $captured = $request->has('captured');
        
        // Обрабатываем загрузку видео
        $videoPath = null;
        if ($request->hasFile('video_file')) {
            $videoPath = $request->file('video_file')->store('osvv/departure_videos', 'public');
        }
        
        // Регистрируем выезд
        $osvvRequest->registerDeparture(
            $validated['departure_date'],
            $validated['departure_notes'],
            $videoPath
        );
        
        // Обрабатываем отлов животных, если отмечен
        if ($captured && isset($validated['animals_count'])) {
            // Обновляем информацию о заявке
            $osvvRequest->update([
                'animals_count' => $validated['animals_count'],
                'status' => 'captured',
                'capture_result' => "Отловлено {$validated['animals_count']} " . 
                                 ($validated['animals_count'] == 1 ? 'животное' : 'животных') . 
                                 ' (при регистрации выезда)'
            ]);
            
            // Создаем животных в системе управления
            $this->createAnimalsFromDeparture($osvvRequest, $validated['animals_count']);
            
            // Добавляем специальный комментарий об отлове
            $animalWord = $validated['animals_count'] > 1 ? 'животных' : 'животное';
            $osvvRequest->comments()->create([
                'user_id' => Auth::id() ?? 1,
                'comment' => "🎯 При выезде отловлено {$validated['animals_count']} {$animalWord}. Статус заявки изменен на 'Животное отловлено'. Животные автоматически добавлены в систему управления."
            ]);
        }
        
        // Обновляем флаг необходимости видеофиксации
        $osvvRequest->requires_video = $requiresVideo;
        $osvvRequest->save();
        
        // Получаем актуальное количество выездов после регистрации
        $departuresCount = $osvvRequest->departures_count;
        
        // Добавляем комментарий о выезде
        $comment = 'Зарегистрирован выезд №' . $departuresCount . ' от ' . 
            \Carbon\Carbon::parse($validated['departure_date'])->format('d.m.Y H:i');
            
        if (!empty($validated['departure_notes'])) {
            $comment .= "\n\nПримечания к выезду: " . $validated['departure_notes'];
        }
        
        if ($videoPath) {
            $comment .= "\n\n📹 Видеофиксация загружена";
        }
        
        $osvvRequest->comments()->create([
            'user_id' => Auth::id() ?? 1,
            'comment' => $comment,
        ]);
        
        // Проверяем, нужно ли автоматически завершить заявку после 3 выездов
        if ($departuresCount >= 3 && !in_array($osvvRequest->status, ['completed', 'cancelled'])) {
            // Автоматически изменяем статус на "завершено"
            $osvvRequest->update(['status' => 'completed']);
            
            // Добавляем комментарий об автоматическом завершении
            $osvvRequest->comments()->create([
                'user_id' => Auth::id() ?? 1,
                'comment' => '✅ Заявка автоматически завершена после ' . $departuresCount . ' выездов. ' .
                           'Согласно регламенту, после трех выездов заявка считается исполненной.',
            ]);
            
            $successMessage = 'Выезд успешно зарегистрирован. Заявка автоматически завершена после ' . $departuresCount . ' выездов.';
        } else {
            $successMessage = 'Выезд успешно зарегистрирован.';
        }
        
        // Если был отлов, добавляем информацию в сообщение
        if ($captured) {
            $successMessage .= " Отмечено отловленных животных: {$validated['animals_count']}.";
        }
        
        return redirect()->route('admin.osvv.show', $osvvRequest)
            ->with('success', $successMessage);
    }
    
    /**
     * Планирует следующий выезд по заявке ОСВВ.
     */
    public function scheduleDeparture(Request $request, OsvvRequest $osvvRequest)
    {
        $validated = $request->validate([
            'next_departure_date' => 'required|date|after:now',
        ]);
        
        // Планируем выезд
        $osvvRequest->scheduleNextDeparture($validated['next_departure_date']);
        
        // Добавляем комментарий о запланированном выезде
        $osvvRequest->comments()->create([
            'user_id' => Auth::id() ?? 1,
            'comment' => 'Запланирован выезд на ' . 
                \Carbon\Carbon::parse($validated['next_departure_date'])->format('d.m.Y H:i'),
        ]);
        
        return redirect()->route('admin.osvv.show', $osvvRequest)
            ->with('success', 'Выезд успешно запланирован.');
    }
    
    /**
     * Быстрое отметка об отлове животного
     */
    public function quickCapture(Request $request, OsvvRequest $osvvRequest)
    {
        $validated = $request->validate([
            'animals_count' => 'required|integer|min:1|max:20',
        ]);
        
        // Обновляем заявку
        $osvvRequest->update([
            'animals_count' => $validated['animals_count'],
            'status' => 'captured',
            'capture_result' => "Отловлено {$validated['animals_count']} " . 
                             ($validated['animals_count'] == 1 ? 'животное' : 'животных') . 
                             ' (быстрая отметка)'
        ]);
        
        // Добавляем комментарий
        $animalWord = $validated['animals_count'] > 1 ? 'животных' : 'животное';
        $osvvRequest->comments()->create([
            'user_id' => Auth::id() ?? 1,
            'comment' => "🎯 Быстрая отметка: отловлено {$validated['animals_count']} {$animalWord}. Статус заявки изменен на 'Животное отловлено'."
        ]);
        
        return response()->json([
            'success' => true,
            'message' => "Успешно отмечено отловленных животных: {$validated['animals_count']}",
            'new_status' => 'captured',
            'capture_result' => $osvvRequest->capture_result
        ]);
    }

    /**
     * Отображает карту выездов.
     */
    public function departureMap()
    {
        return view('admin.osvv.departure-map');
    }
    
    /**
     * Возвращает данные маршрутов для карты в формате JSON
     */
    public function departureRoutesData()
    {
        $todayDepartureList = $this->generateTodayDepartureList();
        
        // Если нет заявок на выезд, добавляем тестовые данные для демонстрации
        if (empty($todayDepartureList['zones'])) {
            \Log::info('Нет заявок на выезд, добавляем тестовые данные');
            $todayDepartureList = $this->generateTestRoutesData();
        }
        
        // Добавляем координаты приюта
        $shelterCoords = [
            'latitude' => 51.6845,
            'longitude' => 39.2156,
            'address' => 'Балашовская 29/1, Левобережный район'
        ];
        
        // Добавляем отладочную информацию
        $debug = [
            'total_new_requests' => \App\Models\OsvvRequest::where('status', 'new')->count(),
            'total_processing_requests' => \App\Models\OsvvRequest::where('status', 'processing')->count(),
            'requests_without_departure' => \App\Models\OsvvRequest::whereNull('departure_date')->count(),
            'today' => now()->format('Y-m-d'),
            'all_requests_with_coords' => \App\Models\OsvvRequest::whereNotNull('latitude')->count(),
            'test_mode' => empty($todayDepartureList['zones']) ? false : (isset($todayDepartureList['test_mode']) ? true : false)
        ];
        
        return response()->json([
            'zones' => $todayDepartureList['zones'],
            'total_requests' => $todayDepartureList['total_requests'],
            'urgent_count' => $todayDepartureList['urgent_count'],
            'today_count' => $todayDepartureList['today_count'],
            'shelter' => $shelterCoords,
            'debug' => $debug
        ]);
    }
    
    /**
     * Генерирует тестовые данные маршрутов для демонстрации карты
     */
    private function generateTestRoutesData()
    {
        \Log::info('Генерируем тестовые данные маршрутов');
        
        // Получаем все заявки с координатами для тестирования
        $allRequestsWithCoords = OsvvRequest::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('created_at', 'desc')
            ->take(10) // Ограничиваем количество для тестирования
            ->get();
            
        \Log::info('Найдено заявок с координатами для тестирования: ' . $allRequestsWithCoords->count());
        
        if ($allRequestsWithCoords->isEmpty()) {
            return [
                'zones' => [],
                'total_requests' => 0,
                'urgent_count' => 0,
                'today_count' => 0,
                'test_mode' => true
            ];
        }
        
        // Группируем заявки по географическим зонам
        $zones = $this->groupRequestsByGeographicZones($allRequestsWithCoords);
        
        return [
            'zones' => $zones,
            'total_requests' => $allRequestsWithCoords->count(),
            'urgent_count' => $allRequestsWithCoords->where('has_bite', true)->count(),
            'today_count' => $allRequestsWithCoords->where('deadline_date', now()->format('Y-m-d'))->count(),
            'test_mode' => true
        ];
    }
    
    /**
     * Удаляет файл из заявки ОСВВ.
     */
    public function deleteFile(Request $request, OsvvRequest $osvvRequest)
    {
        $validated = $request->validate([
            'field_name' => 'required|in:bite_medical_files,bite_evidence_files,animal_photos',
            'file_index' => 'required|integer|min:0',
        ]);
        
        $fieldName = $validated['field_name'];
        $fileIndex = $validated['file_index'];
        
        // Получаем текущий массив файлов
        $currentFiles = $osvvRequest->$fieldName ?? [];
        
        // Проверяем, что индекс существует
        if (!isset($currentFiles[$fileIndex])) {
            return response()->json([
                'success' => false,
                'message' => 'Файл не найден'
            ], 404);
        }
        
        $filePath = $currentFiles[$fileIndex];
        
        // Удаляем файл из массива
        unset($currentFiles[$fileIndex]);
        $updatedFiles = array_values($currentFiles); // Переиндексируем массив
        
        // Удаляем физический файл
        if (\Storage::disk('public')->exists($filePath)) {
            \Storage::disk('public')->delete($filePath);
        }
        
        // Обновляем заявку
        $osvvRequest->update([$fieldName => $updatedFiles]);
        
        // Добавляем комментарий об удалении файла
        $fieldNameRu = [
            'bite_medical_files' => 'медицинская справка',
            'bite_evidence_files' => 'фото/видео фиксация укуса',
            'animal_photos' => 'фотография животного'
        ][$fieldName] ?? 'файл';
        
        $osvvRequest->comments()->create([
            'user_id' => Auth::id() ?? 1,
            'comment' => 'Удален файл (' . $fieldNameRu . '): ' . basename($filePath),
        ]);
        
        return response()->json(['success' => true]);
    }

    /**
     * Проверяет наличие потенциальных дублирующих заявок.
     */
    public function checkDuplicates(Request $request)
    {
        $phone = $request->input('phone');
        $address = $request->input('address');
        $excludeId = $request->input('exclude_id'); // для исключения текущей заявки при редактировании
        
        $duplicates = [];
        
        // Проверка по номеру телефона (точное совпадение)
        if ($phone) {
            $phoneQuery = OsvvRequest::where('contact_phone', $phone)
                ->where('created_at', '>=', now()->subDays(60)) // за последние 60 дней
                ->whereNotIn('status', ['completed', 'cancelled']); // исключаем завершенные
                
            if ($excludeId) {
                $phoneQuery->where('id', '!=', $excludeId);
            }
            
            $phoneDuplicates = $phoneQuery->get();
            
            if ($phoneDuplicates->count() > 0) {
                $duplicates['phone'] = [
                    'type' => 'phone',
                    'message' => 'Найдены заявки с таким же номером телефона',
                    'count' => $phoneDuplicates->count(),
                    'requests' => $phoneDuplicates->map(function($req) {
                        return [
                            'id' => $req->id,
                            'contact_name' => $req->contact_name,
                            'contact_phone' => $req->contact_phone,
                            'location_address' => $req->location_address,
                            'status' => $req->status,
                            'created_at' => $req->created_at->format('d.m.Y'),
                            'url' => route('admin.osvv.show', $req->id)
                        ];
                    })
                ];
            }
        }
        
        // Проверка по адресу (похожие адреса)
        if ($address) {
            // Очищаем адрес для сравнения
            $cleanAddress = $this->cleanAddressForComparison($address);
            
            $addressQuery = OsvvRequest::where('created_at', '>=', now()->subDays(60))
                ->whereNotIn('status', ['completed', 'cancelled']);
                
            if ($excludeId) {
                $addressQuery->where('id', '!=', $excludeId);
            }
            
            $allRequests = $addressQuery->get();
            $addressDuplicates = $allRequests->filter(function($req) use ($cleanAddress) {
                $reqCleanAddress = $this->cleanAddressForComparison($req->location_address);
                // Используем расстояние Левенштейна для нечеткого поиска
                $similarity = $this->calculateAddressSimilarity($cleanAddress, $reqCleanAddress);
                return $similarity > 0.7; // порог схожести 70%
            });
            
            if ($addressDuplicates->count() > 0) {
                $duplicates['address'] = [
                    'type' => 'address',
                    'message' => 'Найдены заявки с похожим адресом',
                    'count' => $addressDuplicates->count(),
                    'requests' => $addressDuplicates->map(function($req) use ($cleanAddress) {
                        $reqCleanAddress = $this->cleanAddressForComparison($req->location_address);
                        $similarity = $this->calculateAddressSimilarity($cleanAddress, $reqCleanAddress);
                        
                        return [
                            'id' => $req->id,
                            'contact_name' => $req->contact_name,
                            'contact_phone' => $req->contact_phone,
                            'location_address' => $req->location_address,
                            'status' => $req->status,
                            'created_at' => $req->created_at->format('d.m.Y'),
                            'similarity' => round($similarity * 100, 1),
                            'url' => route('admin.osvv.show', $req->id)
                        ];
                    })->sortByDesc('similarity')->values()
                ];
            }
        }
        
        return response()->json([
            'has_duplicates' => count($duplicates) > 0,
            'duplicates' => $duplicates
        ]);
    }
    
    /**
     * Очищает адрес для сравнения.
     */
    private function cleanAddressForComparison($address)
    {
        if (!$address) return '';
        
        // Приводим к нижнему регистру
        $clean = mb_strtolower(trim($address));
        
        // Убираем общие сокращения и слова
        $clean = preg_replace('/\b(россия|российская федерация|рф)\b/u', '', $clean);
        $clean = preg_replace('/\b(г\.|город|с\.|село|д\.|деревня|пос\.|поселок)\b/u', '', $clean);
        $clean = preg_replace('/\b(ул\.|улица|пр\.|проспект|пер\.|переулок|б-р|бульвар)\b/u', '', $clean);
        $clean = preg_replace('/\b(д\.|дом|кв\.|квартира|стр\.|строение|корп\.|корпус)\b/u', '', $clean);
        
        // Убираем лишние пробелы и знаки препинания
        $clean = preg_replace('/[,.\-;:()\/\\\\]/u', ' ', $clean);
        $clean = preg_replace('/\s+/u', ' ', $clean);
        
        return trim($clean);
    }
    
    /**
     * Вычисляет схожесть адресов.
     */
    private function calculateAddressSimilarity($address1, $address2)
    {
        if (empty($address1) || empty($address2)) return 0;
        
        // Разбиваем на слова
        $words1 = array_filter(explode(' ', $address1));
        $words2 = array_filter(explode(' ', $address2));
        
        if (empty($words1) || empty($words2)) return 0;
        
        // Считаем пересечение слов
        $intersection = array_intersect($words1, $words2);
        $union = array_unique(array_merge($words1, $words2));
        
        // Коэффициент Жаккара
        $jaccard = count($intersection) / count($union);
        
        // Дополнительная проверка на расстояние Левенштейна для коротких адресов
        if (count($words1) <= 3 || count($words2) <= 3) {
            $levenshtein = 1 - (levenshtein($address1, $address2) / max(strlen($address1), strlen($address2)));
            $jaccard = max($jaccard, $levenshtein);
        }
        
        return $jaccard;
    }
    
    /**
     * Валидирует номер телефона (мобильный или городской Воронежа).
     */
    private function validatePhoneNumber($phone)
    {
        // Убираем все нецифровые символы
        $digits = preg_replace('/\D/', '', $phone);
        
        // Проверяем длину
        if (strlen($digits) !== 11) {
            return false;
        }
        
        // Проверяем, что начинается с 7
        if (!str_starts_with($digits, '7')) {
            return false;
        }
        
        // Проверяем городской номер Воронежа
        if (str_starts_with($digits, '7473')) {
            return true;
        }
        
        // Проверяем мобильный номер
        if ($digits[1] === '9') {
            $mobileCode = substr($digits, 1, 3);
            $validMobileCodes = [
                '900', '901', '902', '903', '904', '905', '906', '908', '909',
                '910', '911', '912', '913', '914', '915', '916', '917', '918', '919',
                '920', '921', '922', '923', '924', '925', '926', '927', '928', '929',
                '930', '931', '932', '933', '934', '936', '937', '938', '939',
                '941', '950', '951', '952', '953', '954', '955', '956', '958', '960',
                '961', '962', '963', '964', '965', '966', '967', '968', '969',
                '970', '971', '977', '978', '980', '981', '982', '983', '984', '985',
                '986', '987', '988', '989', '991', '992', '993', '994', '995', '996', '997', '999'
            ];
            
            return in_array($mobileCode, $validMobileCodes);
        }
        
        return false;
    }
    
    /**
     * Формирует список заявок на выезд на сегодня с группировкой по географическим зонам
     */
    private function generateTodayDepartureList()
    {
        // Критерии для включения в список на выезд:
        // 1. Просроченные заявки (deadline_date < сегодня)
        // 2. Заявки с укусами (приоритет)
        // 3. Заявки со сроком выезда на сегодня
        // 4. Новые заявки без назначенного срока выезда
        
        $today = now()->format('Y-m-d');
        
        // Отладка: проверяем каждый критерий отдельно
        $overdue = OsvvRequest::where('deadline_date', '<', $today)
            ->whereNull('departure_date')
            ->whereNotIn('status', ['captured', 'in_shelter', 'sterilized', 'vaccinated', 'ready_for_return', 'returned', 'completed', 'cancelled'])
            ->count();
            
        $withBites = OsvvRequest::where('has_bite', true)
            ->whereNull('departure_date')
            ->whereNotIn('status', ['captured', 'in_shelter', 'sterilized', 'vaccinated', 'ready_for_return', 'returned', 'completed', 'cancelled'])
            ->count();
            
        $todayDeadline = OsvvRequest::where('deadline_date', $today)
            ->whereNull('departure_date')
            ->whereNotIn('status', ['captured', 'in_shelter', 'sterilized', 'vaccinated', 'ready_for_return', 'returned', 'completed', 'cancelled'])
            ->count();
            
        $newWithoutDeadline = OsvvRequest::whereIn('status', ['new', 'processing'])
            ->whereNull('deadline_date')
            ->whereNull('departure_date')
            ->whereNotIn('status', ['captured', 'in_shelter', 'sterilized', 'vaccinated', 'ready_for_return', 'returned', 'completed', 'cancelled'])
            ->count();
        
        \Log::info('Отладка списка на выезд', [
            'today' => $today,
            'overdue_count' => $overdue,
            'with_bites_count' => $withBites,
            'today_deadline_count' => $todayDeadline,
            'new_without_deadline_count' => $newWithoutDeadline
        ]);
        
        $priorityRequests = OsvvRequest::where(function($query) use ($today) {
            $query->where('deadline_date', '<', $today) // Просроченные
                  ->orWhere('has_bite', true) // С укусами
                  ->orWhere('deadline_date', $today) // На сегодня
                  ->orWhere(function($subQuery) { // Новые без срока
                      $subQuery->whereIn('status', ['new', 'processing'])
                               ->whereNull('deadline_date');
                  });
        })
        ->whereNull('departure_date') // Еще не было выезда
        ->whereNotIn('status', ['captured', 'in_shelter', 'sterilized', 'vaccinated', 'ready_for_return', 'returned', 'completed', 'cancelled'])
        ->orderByRaw('has_bite DESC') // Сначала с укусами
        ->orderByRaw('deadline_date ASC NULLS LAST') // Потом по срокам
        ->orderBy('created_at', 'asc') // Потом по дате создания
        ->get();
        
        \Log::info('Найдено заявок для выезда', [
            'total_found' => $priorityRequests->count(),
            'request_ids' => $priorityRequests->pluck('id')->toArray()
        ]);
        
        // Группируем заявки по географическим зонам
        $zones = $this->groupRequestsByGeographicZones($priorityRequests);
        
        \Log::info('Создано зон', [
            'zones_count' => count($zones),
            'zones_data' => collect($zones)->map(function($zone) {
                return [
                    'center_id' => $zone['center_request']['id'],
                    'requests_count' => count($zone['requests']),
                    'priority' => $zone['priority_level']
                ];
            })->toArray()
        ]);
        
        return [
            'zones' => $zones,
            'total_requests' => $priorityRequests->count(),
            'urgent_count' => $priorityRequests->where('has_bite', true)->count() + 
                             $priorityRequests->where('deadline_date', '<', $today)->count(),
            'today_count' => $priorityRequests->where('deadline_date', $today)->count(),
        ];
    }
    
    /**
     * Группирует заявки по географическим зонам для оптимизации маршрутов
     */
    private function groupRequestsByGeographicZones($requests)
    {
        $zones = [];
        $processed = [];
        
        foreach ($requests as $request) {
            if (in_array($request->id, $processed)) {
                continue;
            }
            
            // Создаем новую зону с текущей заявкой
            $zone = [
                'center_request' => $this->formatRequestForMap($request),
                'requests' => [$this->formatRequestForMap($request)],
                'priority_level' => $this->calculatePriorityLevel($request),
                'estimated_time' => $this->estimateTaskTime($request),
            ];
            
            $processed[] = $request->id;
            
            // Ищем близлежащие заявки для группировки
            foreach ($requests as $otherRequest) {
                if (in_array($otherRequest->id, $processed)) {
                    continue;
                }
                
                // Проверяем близость по району или адресу
                if ($this->areRequestsNearby($request, $otherRequest)) {
                    $zone['requests'][] = $this->formatRequestForMap($otherRequest);
                    $zone['estimated_time'] += $this->estimateTaskTime($otherRequest);
                    $zone['priority_level'] = max($zone['priority_level'], $this->calculatePriorityLevel($otherRequest));
                    $processed[] = $otherRequest->id;
                }
            }
            
            $zones[] = $zone;
        }
        
        // Сортируем зоны по приоритету
        usort($zones, function($a, $b) {
            if ($a['priority_level'] == $b['priority_level']) {
                return count($b['requests']) - count($a['requests']); // Больше заявок = выше
            }
            return $b['priority_level'] - $a['priority_level']; // Выше приоритет = выше
        });
        
        return $zones;
    }
    
    /**
     * Форматирует заявку для отправки на карту, включая все адреса
     */
    private function formatRequestForMap($request)
    {
        // Получаем все адреса заявки
        $allAddresses = $request->getAllAddresses();
        
        // Базовые данные заявки
        $formattedRequest = [
            'id' => $request->id,
            'contact_name' => $request->contact_name,
            'contact_phone' => $request->contact_phone,
            'location_address' => $request->location_address,
            'district' => $request->district,
            'has_bite' => $request->has_bite,
            'is_pregnant' => $request->is_pregnant,
            'status' => $request->status,
            'created_at' => $request->created_at->format('d.m.Y'),
            'deadline_date' => $request->deadline_date ? $request->deadline_date->format('d.m.Y') : null,
            'animals_count' => $request->animals_count ?? 1,
            // Основные координаты (для совместимости с существующим кодом)
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            // Все адреса заявки для карты
            'addresses' => $allAddresses,
            'total_addresses_count' => count($allAddresses)
        ];
        
        return $formattedRequest;
    }
    
    /**
     * Проверяет, находятся ли заявки рядом друг с другом
     */
    private function areRequestsNearby($request1, $request2)
    {
        // Сначала проверяем по району
        if ($request1->district && $request2->district) {
            if (strtolower(trim($request1->district)) === strtolower(trim($request2->district))) {
                return true;
            }
        }
        
        // Проверяем по адресу (ищем общие ключевые слова)
        $address1 = strtolower($request1->location_address);
        $address2 = strtolower($request2->location_address);
        
        // Извлекаем ключевые слова (улицы, районы)
        $keywords1 = $this->extractAddressKeywords($address1);
        $keywords2 = $this->extractAddressKeywords($address2);
        
        // Если есть общие ключевые слова
        $commonKeywords = array_intersect($keywords1, $keywords2);
        if (count($commonKeywords) > 0) {
            return true;
        }
        
        // Если есть координаты, проверяем расстояние (в радиусе 3 км)
        if ($request1->latitude && $request1->longitude && 
            $request2->latitude && $request2->longitude) {
            $distance = $this->calculateDistance(
                $request1->latitude, $request1->longitude,
                $request2->latitude, $request2->longitude
            );
            return $distance <= 3; // 3 км
        }
        
        return false;
    }
    
    /**
     * Извлекает ключевые слова из адреса
     */
    private function extractAddressKeywords($address)
    {
        // Удаляем номера домов и квартир
        $address = preg_replace('/\b\d+[а-я]?\b/u', '', $address);
        
        // Разбиваем на слова
        $words = preg_split('/[\s,\.]+/u', $address);
        
        // Фильтруем значимые слова (длиннее 2 символов)
        $keywords = array_filter($words, function($word) {
            return mb_strlen(trim($word)) > 2;
        });
        
        return array_map('trim', $keywords);
    }
    
    /**
     * Вычисляет расстояние между двумя точками в километрах
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Радиус Земли в км
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }
    
    /**
     * Рассчитывает уровень приоритета заявки
     */
    private function calculatePriorityLevel($request)
    {
        $priority = 1; // Базовый приоритет
        
        if ($request->has_bite) {
            $priority += 10; // Укусы - максимальный приоритет
        }
        
        if ($request->deadline_date && $request->deadline_date < now()->format('Y-m-d')) {
            $priority += 5; // Просроченные
        }
        
        if ($request->deadline_date === now()->format('Y-m-d')) {
            $priority += 3; // На сегодня
        }
        
        if ($request->is_pregnant) {
            $priority += 2; // Беременные
        }
        
        if ($request->animals_count > 1) {
            $priority += 1; // Несколько животных
        }
        
        return $priority;
    }
    
    /**
     * Оценивает время выполнения задания
     */
    private function estimateTaskTime($request)
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
     * Создает животных в системе управления при отлове во время выезда
     */
    private function createAnimalsFromDeparture(OsvvRequest $osvvRequest, int $animalsCount)
    {
        // Подключаем модели
        $animalModel = \App\Models\Animal::class;
        $stageModel = \App\Models\AnimalStage::class;
        
        // Проверяем, существуют ли модели
        if (!class_exists($animalModel) || !class_exists($stageModel)) {
            \Log::warning('Модели Animal или AnimalStage не найдены, пропускаем создание животных');
            return;
        }
        
        // Получаем первый этап (карантин) или создаем базовый этап
        $firstStage = $stageModel::orderBy('order')->first();
        
        if (!$firstStage) {
            // Создаем базовый этап "Карантин" если этапы не настроены
            $firstStage = $stageModel::create([
                'name' => 'Карантин',
                'description' => 'Первичный карантин после отлова',
                'order' => 1,
                'duration_days' => 10,
                'is_required' => true,
                'color' => '#3B82F6'
            ]);
        }
        
        // Создаем животных
        for ($i = 1; $i <= $animalsCount; $i++) {
            $animal = $animalModel::create([
                'name' => "Из выезда {$osvvRequest->id} №{$i}",
                'species' => $this->mapAnimalType($osvvRequest->animal_type),
                'gender' => $this->mapAnimalGender($osvvRequest->animal_gender),
                'description' => $this->buildAnimalDescriptionFromDeparture($osvvRequest, $i),
                'current_stage_id' => $firstStage->id,
                'stage_started_at' => now(),
                'status' => 'active',
                'osvv_request_id' => $osvvRequest->id,
                'admission_date' => now(),
                'source' => 'Отлов при выезде',
                'microchip_number' => null,
                'notes' => "Создано автоматически при регистрации выезда по заявке #{$osvvRequest->id}"
            ]);
            
            \Log::info("Создано животное из выезда: {$animal->name} (ID: {$animal->id})");
        }
        
        \Log::info("Создано {$animalsCount} животных в системе управления из выезда по заявке #{$osvvRequest->id}");
    }
    
    /**
     * Формирует описание животного на основе данных выезда
     */
    private function buildAnimalDescriptionFromDeparture(OsvvRequest $osvvRequest, int $animalNumber): string
    {
        $description = "🚗 Отловлено при выезде #{$osvvRequest->departures_count} по заявке #{$osvvRequest->id}\n\n";
        
        $description .= "📍 Место отлова: {$osvvRequest->location_address}\n";
        
        if ($osvvRequest->location_landmark) {
            $description .= "🗺️ Ориентир: {$osvvRequest->location_landmark}\n";
        }
        
        if ($osvvRequest->district) {
            $description .= "🏘️ Район: {$osvvRequest->district}\n";
        }
        
        $description .= "📅 Дата отлова: " . now()->format('d.m.Y H:i') . "\n";
        
        if ($osvvRequest->animal_description) {
            $description .= "\n📝 Описание от заявителя:\n{$osvvRequest->animal_description}\n";
        }
        
        if ($osvvRequest->case_description) {
            $description .= "\n📋 Описание ситуации:\n{$osvvRequest->case_description}\n";
        }
        
        if ($osvvRequest->has_bite) {
            $description .= "\n⚠️ ВНИМАНИЕ: Заявка с укусом!\n";
        }
        
        if ($osvvRequest->is_pregnant) {
            $description .= "\n🤰 Возможная беременность\n";
        }
        
        if ($osvvRequest->has_tags) {
            $description .= "\n🏷️ Имеет бирки/метки\n";
        }
        
        $description .= "\n👤 Заявитель: {$osvvRequest->contact_name}";
        $description .= "\n📞 Телефон: {$osvvRequest->contact_phone}";
        
        if ($osvvRequest->contact_email) {
            $description .= "\n✉️ Email: {$osvvRequest->contact_email}";
        }
        
        $description .= "\n\n🔢 Животное #{$animalNumber} из {$osvvRequest->animals_count}";
        
        return $description;
    }
    
    /**
     * Маппинг типа животного для системы управления
     */
    private function mapAnimalType($animalType)
    {
        $animalTypes = [
            'cat' => 'Кошка',
            'dog' => 'Собака',
            'other' => 'Другое'
        ];
        
        return $animalTypes[$animalType] ?? 'Неизвестное животное';
    }
    
    /**
     * Маппинг пола животного для системы управления
     */
    private function mapAnimalGender($animalGender)
    {
        $genders = [
            'male' => 'Самец',
            'female' => 'Самка',
            'unknown' => 'Неизвестный'
        ];
        
        return $genders[$animalGender] ?? 'Неизвестный';
    }

    /**
     * Получить доступные заявки для добавления в план
     */
    public function getAvailableRequests()
    {
        $requests = OsvvRequest::where('status', 'new')
            ->orWhere('status', 'in_progress')
            ->whereDoesntHave('routeRequests')
            ->select('id', 'applicant_name', 'location_address as location')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'requests' => $requests
        ]);
    }
}
