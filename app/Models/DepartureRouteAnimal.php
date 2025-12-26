<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartureRouteAnimal extends Model
{
    use HasFactory;

    protected $fillable = [
        'departure_route_id',
        'animal_id',
        'sequence_order',
        'estimated_time',
        'notes',
        'release_status',
        'release_result',
        'release_notes',
        'released_at',
        'release_location',
    ];

    protected $casts = [
        'sequence_order' => 'integer',
        'estimated_time' => 'integer',
        'released_at' => 'datetime',
    ];

    /**
     * Маршрут выезда
     */
    public function departureRoute(): BelongsTo
    {
        return $this->belongsTo(DepartureRoute::class);
    }

    /**
     * Животное
     */
    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }

    /**
     * Получить список статусов выпуска
     */
    public static function getReleaseStatuses(): array
    {
        return [
            'pending' => 'Ожидает выпуска',
            'released' => 'Выпущен', 
            'failed' => 'Не удалось выпустить',
            'cancelled' => 'Отменен'
        ];
    }

    /**
     * Получить список результатов выпуска
     */
    public static function getReleaseResults(): array
    {
        return [
            'success' => 'Успешно выпущен',
            'failed' => 'Не удалось выпустить',
            'cancelled' => 'Отменен'
        ];
    }

    /**
     * Получить название статуса выпуска
     */
    public function getReleaseStatusNameAttribute(): ?string
    {
        $statuses = self::getReleaseStatuses();
        return $statuses[$this->release_status] ?? null;
    }

    /**
     * Получить название результата выпуска
     */
    public function getReleaseResultNameAttribute(): ?string
    {
        if (!$this->release_result) {
            return null;
        }
        
        $results = self::getReleaseResults();
        return $results[$this->release_result] ?? null;
    }

    /**
     * Получить цвет статуса для интерфейса
     */
    public function getReleaseStatusColorAttribute(): string
    {
        return match($this->release_status) {
            'pending' => 'gray',
            'released' => 'green',
            'failed' => 'red',
            'cancelled' => 'gray',
            default => 'gray'
        };
    }
}
