<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartureRouteRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'departure_route_id',
        'osvv_request_id',
        'sequence_order',
        'estimated_time',
        'notes',
        'execution_status',
        'execution_result',
        'execution_notes',
        'executed_at',
        'animals_captured',
        'execution_photos'
    ];

    protected $casts = [
        'sequence_order' => 'integer',
        'estimated_time' => 'integer',
        'executed_at' => 'datetime',
        'animals_captured' => 'integer',
        'execution_photos' => 'array',
    ];

    /**
     * Маршрут, к которому относится заявка
     */
    public function departureRoute(): BelongsTo
    {
        return $this->belongsTo(DepartureRoute::class);
    }

    /**
     * Заявка ОСВВ
     */
    public function osvvRequest(): BelongsTo
    {
        return $this->belongsTo(OsvvRequest::class);
    }

    /**
     * Получить план выездов через маршрут
     */
    public function departurePlan()
    {
        return $this->hasOneThrough(
            DeparturePlan::class,
            DepartureRoute::class,
            'id',
            'id',
            'departure_route_id',
            'departure_plan_id'
        );
    }

    /**
     * Получить отформатированное время
     */
    public function getFormattedTimeAttribute(): string
    {
        $hours = intval($this->estimated_time / 60);
        $minutes = $this->estimated_time % 60;
        
        if ($hours > 0) {
            return $hours . 'ч ' . $minutes . 'мин';
        }
        
        return $minutes . 'мин';
    }

    /**
     * Переместить заявку вверх в порядке
     */
    public function moveUp(): bool
    {
        if ($this->sequence_order <= 1) {
            return false;
        }

        $previousRequest = self::where('departure_route_id', $this->departure_route_id)
            ->where('sequence_order', $this->sequence_order - 1)
            ->first();

        if ($previousRequest) {
            $previousRequest->sequence_order = $this->sequence_order;
            $this->sequence_order = $this->sequence_order - 1;
            
            $previousRequest->save();
            return $this->save();
        }

        return false;
    }

    /**
     * Переместить заявку вниз в порядке
     */
    public function moveDown(): bool
    {
        $maxOrder = self::where('departure_route_id', $this->departure_route_id)
            ->max('sequence_order');

        if ($this->sequence_order >= $maxOrder) {
            return false;
        }

        $nextRequest = self::where('departure_route_id', $this->departure_route_id)
            ->where('sequence_order', $this->sequence_order + 1)
            ->first();

        if ($nextRequest) {
            $nextRequest->sequence_order = $this->sequence_order;
            $this->sequence_order = $this->sequence_order + 1;
            
            $nextRequest->save();
            return $this->save();
        }

        return false;
    }

    /**
     * Scopes
     */
    public function scopeInOrder($query)
    {
        return $query->orderBy('sequence_order');
    }

    public function scopeForRoute($query, int $routeId)
    {
        return $query->where('departure_route_id', $routeId);
    }

    /**
     * Статусы выполнения заявки
     */
    public static function getExecutionStatuses(): array
    {
        return [
            'pending' => 'Ожидает',
            'visited' => 'Посещено',
            'completed' => 'Выполнено',
            'failed' => 'Не выполнено',
            'cancelled' => 'Отменено',
            'no_animals_found' => 'Животные не найдены',
        ];
    }

    /**
     * Результаты выполнения заявки
     */
    public static function getExecutionResults(): array
    {
        return [
            'success' => 'Успешно',
            'partial_success' => 'Частично выполнено',
            'no_result' => 'Без результата',
            'failed' => 'Не удалось',
            'cancelled' => 'Отменено',
        ];
    }

    /**
     * Получить цвет статуса выполнения для UI
     */
    public function getExecutionStatusColorAttribute(): string
    {
        return match($this->execution_status) {
            'pending' => 'gray',
            'visited' => 'blue',
            'completed' => 'green',
            'failed' => 'red',
            'cancelled' => 'red',
            'no_animals_found' => 'yellow',
            default => 'gray'
        };
    }

    /**
     * Получить название статуса выполнения
     */
    public function getExecutionStatusNameAttribute(): string
    {
        return self::getExecutionStatuses()[$this->execution_status] ?? $this->execution_status;
    }

    /**
     * Получить название результата выполнения
     */
    public function getExecutionResultNameAttribute(): string
    {
        return self::getExecutionResults()[$this->execution_result] ?? $this->execution_result;
    }

    /**
     * Boot method для автоматического обновления статистики
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($routeRequest) {
            $routeRequest->departureRoute->recalculateStats();
        });

        static::updated(function ($routeRequest) {
            $routeRequest->departureRoute->recalculateStats();
        });

        static::deleted(function ($routeRequest) {
            $routeRequest->departureRoute->recalculateStats();
        });
    }
}
