<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnimalReturnProcedure extends Model
{
    use HasFactory;

    protected $fillable = [
        'animal_id',
        'inspection_act_id',
        'original_location',
        'location_coordinates',
        'planned_return_date',
        'actual_return_date',
        'return_status',
        'return_conditions_met',
        'return_notes',
        'responsible_persons',
    ];

    protected $casts = [
        'planned_return_date' => 'date',
        'actual_return_date' => 'date',
        'location_coordinates' => 'array',
        'responsible_persons' => 'array',
    ];

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }

    public function inspectionAct(): BelongsTo
    {
        return $this->belongsTo(AnimalInspectionAct::class, 'inspection_act_id');
    }

    public static function getReturnStatuses(): array
    {
        return [
            'planned' => 'Запланирован',
            'approved' => 'Одобрен',
            'in_progress' => 'В процессе',
            'completed' => 'Выполнен',
            'cancelled' => 'Отменен',
        ];
    }

    public function getReturnStatusNameAttribute(): string
    {
        return self::getReturnStatuses()[$this->return_status] ?? $this->return_status;
    }

    public function getReturnStatusColorAttribute(): string
    {
        return match ($this->return_status) {
            'planned' => 'gray',
            'approved' => 'blue',
            'in_progress' => 'yellow',
            'completed' => 'green',
            'cancelled' => 'red',
            default => 'gray',
        };
    }

    /**
     * Проверка, просрочен ли возврат
     */
    public function isOverdue(): bool
    {
        if ($this->return_status === 'completed' || $this->return_status === 'cancelled') {
            return false;
        }

        return $this->planned_return_date < now()->toDateString();
    }

    /**
     * Получить количество дней до планируемого возврата
     */
    public function getDaysUntilReturn(): int
    {
        return now()->diffInDays($this->planned_return_date, false);
    }

    /**
     * Получить список ответственных лиц в виде строки
     */
    public function getResponsiblePersonsListAttribute(): string
    {
        if (!$this->responsible_persons) {
            return '';
        }

        return collect($this->responsible_persons)->map(function ($person) {
            return $person['name'] . ' (' . $person['position'] . ')';
        })->implode(', ');
    }
} 