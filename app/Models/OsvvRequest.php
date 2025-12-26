<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OsvvRequest extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Поля, доступные для массового присваивания
     */
    protected $fillable = [
        'contact_name',
        'contact_phone',
        'contact_email',
        'case_description',
        'source_type',
        'source_district',
        'aurora_number',
        'animal_type',
        'animal_type_other',
        'animal_gender',
        'animal_age',
        'animal_description',
        'animal_photos',
        'location_address',
        'location_landmark',
        'locations',
        'status',
        'notes',
        'user_id',
        'created_at',
        'animals_count',
        'district',
        'has_bite',
        'bite_medical_files',
        'bite_evidence_files',
        'is_pregnant',
        'has_tags',
        'tags_description',
        'departure_date',
        'deadline_date',
        'capture_result',
        'latitude',
        'longitude',
        'departures_count',
        'next_departure_date',
        'max_departures',
        'departure_notes',
        'requires_video',
        'departure_videos',
    ];

    /**
     * Атрибуты, которые должны быть преобразованы
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'departure_date' => 'date',
        'deadline_date' => 'date',
        'has_bite' => 'boolean',
        'bite_medical_files' => 'array',
        'bite_evidence_files' => 'array',
        'animal_photos' => 'array',
        'locations' => 'array',
        'is_pregnant' => 'boolean',
        'has_tags' => 'boolean',
        'next_departure_date' => 'datetime',
        'requires_video' => 'boolean',
        'departure_videos' => 'array',
    ];

    /**
     * Получить пользователя, ответственного за заявку
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Получить комментарии к заявке
     */
    public function comments(): HasMany
    {
        return $this->hasMany(OsvvComment::class);
    }
    
    /**
     * Получить задания на выезд по данной заявке
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Получить акты отлова для заявки
     */
    public function captureActs(): HasMany
    {
        return $this->hasMany(CaptureAct::class);
    }

    /**
     * Получить животное, связанное с заявкой
     */
    public function animal()
    {
        return $this->hasOne(Animal::class);
    }

    /**
     * Получить связи с маршрутами
     */
    public function routeRequests(): HasMany
    {
        return $this->hasMany(DepartureRouteRequest::class, 'osvv_request_id');
    }

    /**
     * Получить все адреса заявки (основной + дополнительные)
     */
    public function getAllAddresses(): array
    {
        $addresses = [];
        
        // Добавляем основной адрес
        if ($this->location_address) {
            $addresses[] = [
                'address' => $this->location_address,
                'landmark' => $this->location_landmark,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'is_primary' => true
            ];
        }
        
        // Добавляем дополнительные адреса
        if ($this->locations && is_array($this->locations)) {
            foreach ($this->locations as $location) {
                $addresses[] = array_merge($location, ['is_primary' => false]);
            }
        }
        
        return $addresses;
    }

    /**
     * Добавить дополнительный адрес к заявке
     */
    public function addLocation(string $address, ?string $landmark = null, ?float $latitude = null, ?float $longitude = null): void
    {
        $locations = $this->locations ?? [];
        
        $locations[] = [
            'address' => $address,
            'landmark' => $landmark,
            'latitude' => $latitude,
            'longitude' => $longitude
        ];
        
        $this->locations = $locations;
    }

    /**
     * Удалить дополнительный адрес по индексу
     */
    public function removeLocation(int $index): bool
    {
        $locations = $this->locations ?? [];
        
        if (isset($locations[$index])) {
            unset($locations[$index]);
            $this->locations = array_values($locations); // Переиндексируем массив
            return true;
        }
        
        return false;
    }

    /**
     * Получить количество всех адресов (основной + дополнительные)
     */
    public function getTotalAddressesCount(): int
    {
        $count = $this->location_address ? 1 : 0; // Основной адрес
        $count += count($this->locations ?? []); // Дополнительные адреса
        
        return $count;
    }

    /**
     * Проверить, может ли заявка быть переведена в указанный статус
     */
    public function canTransitionTo(string $status): bool
    {
        // Логика проверки возможности перехода в статус
        // Добавьте здесь правила для различных переходов статуса
        return true;
    }
    
    /**
     * Автоматически рассчитывает крайний срок выезда на основе даты создания и наличия укуса
     */
    public function calculateDeadlineDate()
    {
        if ($this->created_at) {
            if ($this->has_bite) {
                // Если был укус - срок 1 день
                $this->deadline_date = $this->created_at->copy()->addDay();
            } else {
                // Если не было укуса - срок 6 дней
                $this->deadline_date = $this->created_at->copy()->addDays(6);
            }
        }
        
        return $this;
    }
    
    /**
     * Автоматически устанавливает крайний срок выезда при изменении флага укуса
     */
    public function setHasBiteAttribute($value)
    {
        $this->attributes['has_bite'] = $value;
        
        // Если заявка уже существует, пересчитываем срок выезда
        if ($this->exists) {
            $this->calculateDeadlineDate();
        }
        
        // Установка максимального количества выездов для заявок с укусами
        if ($value) {
            $this->max_departures = 10; // Максимум 10 выездов для заявок с укусами
            $this->requires_video = true; // Требуется видеофиксация для заявок с укусами
        }
    }
    
    /**
     * Регистрирует новый выезд по заявке
     *
     * @param \DateTime|string|null $departureDate Дата выезда
     * @param string|null $notes Примечания к выезду
     * @return $this
     */
    public function registerDeparture($departureDate = null, $notes = null, $videoPath = null)
    {
        // Увеличиваем счетчик выездов
        $this->departures_count = ($this->departures_count ?? 0) + 1;
        
        // Устанавливаем дату последнего выезда
        if ($departureDate) {
            $this->departure_date = $departureDate;
        } else {
            $this->departure_date = now();
        }
        
        // Добавляем примечания к выезду
        if ($notes) {
            $existingNotes = $this->departure_notes ? $this->departure_notes . "\n\n" : '';
            $this->departure_notes = $existingNotes . 'Выезд #' . $this->departures_count . ' (' . now()->format('d.m.Y H:i') . '): ' . $notes;
        }
        
        // Сохраняем путь к видео, если загружено
        if ($videoPath) {
            $existingVideos = $this->departure_videos ?? [];
            $existingVideos[] = [
                'path' => $videoPath,
                'departure_number' => $this->departures_count,
                'uploaded_at' => now()->toDateTimeString()
            ];
            $this->departure_videos = $existingVideos;
        }
        
        // Проверяем, нужно ли автоматически завершить заявку после 3 выездов
        if ($this->departures_count >= 3 && !in_array($this->status, ['completed', 'cancelled'])) {
            // Автоматически изменяем статус на "завершено"
            $this->status = 'completed';
            
            // Добавляем комментарий об автоматическом завершении
            $this->comments()->create([
                'user_id' => auth()->id() ?? 1,
                'comment' => '✅ Заявка автоматически завершена после ' . $this->departures_count . ' выездов. ' .
                           'Согласно регламенту, после трех выездов заявка считается исполненной.',
            ]);
        }
        
        $this->save();
        
        return $this;
    }
    
    /**
     * Планирует следующий выезд по заявке
     *
     * @param \DateTime|string $nextDepartureDate Дата следующего выезда
     * @return $this
     */
    public function scheduleNextDeparture($nextDepartureDate)
    {
        $this->next_departure_date = $nextDepartureDate;
        $this->save();
        
        return $this;
    }
    
    /**
     * Проверяет, требуется ли еще выезд по заявке
     *
     * @return bool
     */
    public function needsMoreDepartures()
    {
        // Если заявка с укусом и не достигнуто максимальное количество выездов
        if ($this->has_bite && $this->max_departures && $this->departures_count < $this->max_departures) {
            return true;
        }
        
        // Если заявка не завершена и не отменена
        return !in_array($this->status, ['completed', 'cancelled']);
    }
    
    /**
     * Создает новое задание на выезд
     *
     * @param \DateTime|string $scheduledDate Запланированная дата выезда
     * @param string|null $scheduledTime Запланированное время выезда (опционально)
     * @param bool $isPriority Является ли выезд приоритетным
     * @param string|null $notes Примечания к заданию
     * @return Task
     */
    public function createTask($scheduledDate, $scheduledTime = null, $isPriority = false, $notes = null)
    {
        return $this->tasks()->create([
            'scheduled_date' => $scheduledDate,
            'scheduled_time' => $scheduledTime,
            'is_priority' => $isPriority,
            'notes' => $notes,
            'requires_video' => $this->requires_video,
            'status' => 'pending'
        ]);
    }
    
    /**
     * Boot метод модели - выполняется при создании/обновлении модели
     */
    protected static function boot()
    {
        parent::boot();
        
        // Устанавливаем дедлайн при создании заявки
        static::creating(function ($request) {
            $request->calculateDeadlineDate();
            
            // Устанавливаем максимальное количество выездов для заявок с укусами
            if ($request->has_bite) {
                $request->max_departures = 10; // Максимум 10 выездов для заявок с укусами
                $request->requires_video = true; // Требуется видеофиксация для заявок с укусами
            }
        });
        
        // Логируем создание заявки
        static::created(function ($request) {
            if (class_exists('App\Models\OsvvAnalytics')) {
                OsvvAnalytics::logEvent($request->id, 'request_created', [
                    'animal_type' => $request->animal_type,
                    'district' => $request->district,
                    'has_bite' => $request->has_bite,
                    'is_pregnant' => $request->is_pregnant,
                    'animals_count' => $request->animals_count,
                    'source_type' => $request->source_type,
                    'deadline_date' => $request->deadline_date?->format('Y-m-d'),
                ], [
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                ]);
            }
        });
        
        // Логируем изменения статуса
        static::updating(function ($request) {
            if ($request->isDirty('status') && class_exists('App\Models\OsvvAnalytics')) {
                $oldStatus = $request->getOriginal('status');
                $newStatus = $request->status;
                
                OsvvAnalytics::logEvent($request->id, 'status_changed', [
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'district' => $request->district,
                ]);
                
                // Если заявка завершена, логируем время обработки
                if ($newStatus === 'completed') {
                    $processingTime = $request->created_at->diffInMinutes(now());
                    OsvvAnalytics::logEvent($request->id, 'request_completed', [
                        'processing_time_minutes' => $processingTime,
                        'district' => $request->district,
                        'departures_count' => $request->departures_count ?? 0,
                        'had_bite' => $request->has_bite,
                        'was_pregnant' => $request->is_pregnant,
                    ], [
                        'duration_minutes' => $processingTime,
                        'latitude' => $request->latitude,
                        'longitude' => $request->longitude,
                    ]);
                }
            }
        });
    }
}
