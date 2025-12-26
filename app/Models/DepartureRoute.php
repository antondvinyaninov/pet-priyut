<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DepartureRoute extends Model
{
    use HasFactory;

    protected $fillable = [
        'departure_plan_id',
        'name',
        'planned_date',
        'assigned_user_id',
        'driver_user_id',
        'start_time',
        'priority',
        'notes',
        'requests_count',
        'estimated_duration',
        'status',
        'actual_start_time',
        'actual_end_time',
        'completion_status',
        'completion_notes',
        'completion_percentage'
    ];

    protected $casts = [
        'planned_date' => 'date',
        'start_time' => 'datetime:H:i',
        'actual_start_time' => 'datetime',
        'actual_end_time' => 'datetime',
        'priority' => 'integer',
        'requests_count' => 'integer',
        'estimated_duration' => 'integer',
        'completion_percentage' => 'integer',
    ];

    /**
     * План выездов, к которому относится маршрут
     */
    public function departurePlan(): BelongsTo
    {
        return $this->belongsTo(DeparturePlan::class);
    }

    /**
     * Назначенный сотрудник (отловщик)
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    /**
     * Назначенный водитель
     */
    public function driverUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_user_id');
    }

    /**
     * Заявки в маршруте
     */
    public function routeRequests(): HasMany
    {
        return $this->hasMany(DepartureRouteRequest::class)->orderBy('sequence_order');
    }

    /**
     * Заявки ОСВВ через связующую таблицу
     */
    public function osvvRequests()
    {
        return $this->belongsToMany(
            OsvvRequest::class,
            'departure_route_requests',
            'departure_route_id',
            'osvv_request_id'
        )->withPivot(['sequence_order', 'estimated_time', 'notes'])
        ->orderBy('departure_route_requests.sequence_order');
    }

    /**
     * Животные в маршруте
     */
    public function routeAnimals(): HasMany
    {
        return $this->hasMany(DepartureRouteAnimal::class)->orderBy('sequence_order');
    }

    /**
     * Животные через связующую таблицу
     */
    public function animals()
    {
        return $this->belongsToMany(
            Animal::class,
            'departure_route_animals',
            'departure_route_id',
            'animal_id'
        )->withPivot(['sequence_order', 'estimated_time', 'notes'])
        ->orderBy('departure_route_animals.sequence_order');
    }

    /**
     * Статусы маршрута
     */
    public static function getStatuses(): array
    {
        return [
            'pending' => 'Ожидает',
            'assigned' => 'Назначен',
            'in_progress' => 'Выполняется',
            'completed' => 'Завершен',
            'cancelled' => 'Отменен',
        ];
    }

    /**
     * Получить цвет статуса для UI
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'gray',
            'assigned' => 'blue',
            'in_progress' => 'yellow',
            'completed' => 'green',
            'cancelled' => 'red',
            default => 'gray'
        };
    }

    /**
     * Получить название статуса
     */
    public function getStatusNameAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    /**
     * Статусы завершения маршрута
     */
    public static function getCompletionStatuses(): array
    {
        return [
            'not_started' => 'Не начат',
            'in_progress' => 'Выполняется',
            'completed' => 'Завершен',
            'partially_completed' => 'Частично завершен',
            'failed' => 'Провален',
            'cancelled' => 'Отменен',
        ];
    }

    /**
     * Получить цвет статуса завершения для UI
     */
    public function getCompletionStatusColorAttribute(): string
    {
        return match($this->completion_status) {
            'not_started' => 'gray',
            'in_progress' => 'blue',
            'completed' => 'green',
            'partially_completed' => 'yellow',
            'failed' => 'red',
            'cancelled' => 'red',
            default => 'gray'
        };
    }

    /**
     * Получить название статуса завершения
     */
    public function getCompletionStatusNameAttribute(): string
    {
        return self::getCompletionStatuses()[$this->completion_status] ?? $this->completion_status;
    }

    /**
     * Добавить заявку в маршрут
     */
    public function addRequest(OsvvRequest $request, int $estimatedTime = 60, ?string $notes = null): DepartureRouteRequest
    {
        $nextOrder = $this->routeRequests()->max('sequence_order') + 1;
        
        $routeRequest = $this->routeRequests()->create([
            'osvv_request_id' => $request->id,
            'sequence_order' => $nextOrder,
            'estimated_time' => $estimatedTime,
            'notes' => $notes,
        ]);

        $this->recalculateStats();
        return $routeRequest;
    }

    /**
     * Удалить заявку из маршрута
     */
    public function removeRequest(OsvvRequest $request): bool
    {
        $removed = $this->routeRequests()
            ->where('osvv_request_id', $request->id)
            ->delete();

        if ($removed) {
            $this->reorderRequests();
            $this->recalculateStats();
        }

        return $removed > 0;
    }

    /**
     * Изменить порядок заявки в маршруте
     */
    public function reorderRequest(OsvvRequest $request, int $newOrder): bool
    {
        $routeRequest = $this->routeRequests()
            ->where('osvv_request_id', $request->id)
            ->first();

        if (!$routeRequest) {
            return false;
        }

        $routeRequest->sequence_order = $newOrder;
        $routeRequest->save();

        $this->reorderRequests();
        return true;
    }

    /**
     * Пересортировать заявки в маршруте (убрать пропуски в нумерации)
     */
    public function reorderRequests(): void
    {
        $requests = $this->routeRequests()->orderBy('sequence_order')->get();
        
        foreach ($requests as $index => $routeRequest) {
            $routeRequest->sequence_order = $index + 1;
            $routeRequest->save();
        }
    }

    /**
     * Добавить животное в маршрут
     */
    public function addAnimal(Animal $animal, int $estimatedTime = 30, ?string $notes = null): DepartureRouteAnimal
    {
        $nextOrder = $this->routeAnimals()->max('sequence_order') + 1;
        
        $routeAnimal = $this->routeAnimals()->create([
            'animal_id' => $animal->id,
            'sequence_order' => $nextOrder,
            'estimated_time' => $estimatedTime,
            'notes' => $notes,
        ]);

        $this->recalculateStats();
        return $routeAnimal;
    }

    /**
     * Удалить животное из маршрута
     */
    public function removeAnimal(Animal $animal): bool
    {
        $removed = $this->routeAnimals()
            ->where('animal_id', $animal->id)
            ->delete();

        if ($removed) {
            $this->reorderAnimals();
            $this->recalculateStats();
        }

        return $removed > 0;
    }

    /**
     * Пересортировать животных в маршруте
     */
    public function reorderAnimals(): void
    {
        $animals = $this->routeAnimals()->orderBy('sequence_order')->get();
        
        foreach ($animals as $index => $routeAnimal) {
            $routeAnimal->sequence_order = $index + 1;
            $routeAnimal->save();
        }
    }

    /**
     * Пересчитать статистику маршрута
     */
    public function recalculateStats(): void
    {
        $this->requests_count = $this->routeRequests()->count() + $this->routeAnimals()->count();
        $this->estimated_duration = $this->routeRequests()->sum('estimated_time') + $this->routeAnimals()->sum('estimated_time');
        $this->save();

        // Обновляем статистику плана
        $this->departurePlan->recalculateStats();
    }

    /**
     * Назначить отловщика
     */
    public function assignTo(User $user): bool
    {
        $this->assigned_user_id = $user->id;
        $this->status = 'assigned';
        return $this->save();
    }

    /**
     * Снять назначение
     */
    public function unassign(): bool
    {
        $this->assigned_user_id = null;
        $this->status = 'pending';
        return $this->save();
    }

    /**
     * Запустить выполнение маршрута
     */
    public function start(): bool
    {
        if (in_array($this->status, ['assigned', 'pending'])) {
            $this->status = 'in_progress';
            return $this->save();
        }
        return false;
    }

    /**
     * Завершить маршрут
     */
    public function complete(): bool
    {
        if ($this->status === 'in_progress') {
            $this->status = 'completed';
            return $this->save();
        }
        return false;
    }

    /**
     * Отменить маршрут
     */
    public function cancel(): bool
    {
        if (in_array($this->status, ['pending', 'assigned', 'in_progress'])) {
            $this->status = 'cancelled';
            return $this->save();
        }
        return false;
    }

    /**
     * Проверяет, можно ли редактировать маршрут
     */
    public function isEditable(): bool
    {
        return in_array($this->status, ['pending', 'assigned']) && 
               $this->departurePlan->isEditable();
    }

    /**
     * Scopes
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeAssignedTo($query, User $user)
    {
        return $query->where('assigned_user_id', $user->id);
    }

    public function scopeByPriority($query, int $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeOrderedByPriority($query)
    {
        return $query->orderBy('priority', 'desc')->orderBy('start_time');
    }
}
