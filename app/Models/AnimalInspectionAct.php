<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AnimalInspectionAct extends Model
{
    use HasFactory;

    protected $fillable = [
        'act_number',
        'inspection_date',
        'commission_id',
        'animal_id',
        'health_status',
        'aggression_level',
        'sterilization_required',
        'is_sterilized',
        'sterilization_date',
        'sterilization_mark',
        'return_to_habitat_allowed',
        'return_conditions',
        'inspection_notes',
        'commission_signatures',
        'status',
    ];

    protected $casts = [
        'inspection_date' => 'date',
        'sterilization_date' => 'date',
        'sterilization_required' => 'boolean',
        'is_sterilized' => 'boolean',
        'return_to_habitat_allowed' => 'boolean',
        'commission_signatures' => 'array',
    ];

    public function commission(): BelongsTo
    {
        return $this->belongsTo(AnimalInspectionCommission::class, 'commission_id');
    }

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }

    public function returnProcedure(): HasOne
    {
        return $this->hasOne(AnimalReturnProcedure::class, 'inspection_act_id');
    }

    public static function getHealthStatuses(): array
    {
        return [
            'healthy' => 'Здоровое',
            'sick' => 'Больное',
            'injured' => 'Травмированное',
            'critical' => 'Критическое состояние',
        ];
    }

    public static function getAggressionLevels(): array
    {
        return [
            'none' => 'Отсутствует',
            'low' => 'Низкая',
            'moderate' => 'Умеренная',
            'high' => 'Высокая',
            'unmotivated' => 'Немотивированная агрессивность',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            'draft' => 'Черновик',
            'signed' => 'Подписан',
            'completed' => 'Выполнен',
        ];
    }

    public function getHealthStatusNameAttribute(): string
    {
        return self::getHealthStatuses()[$this->health_status] ?? $this->health_status;
    }

    public function getAggressionLevelNameAttribute(): string
    {
        return self::getAggressionLevels()[$this->aggression_level] ?? $this->aggression_level;
    }

    public function getStatusNameAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    public function getHealthStatusColorAttribute(): string
    {
        return match ($this->health_status) {
            'healthy' => 'green',
            'sick' => 'yellow',
            'injured' => 'orange',
            'critical' => 'red',
            default => 'gray',
        };
    }

    public function getAggressionLevelColorAttribute(): string
    {
        return match ($this->aggression_level) {
            'none' => 'green',
            'low' => 'blue',
            'moderate' => 'yellow',
            'high' => 'orange',
            'unmotivated' => 'red',
            default => 'gray',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'gray',
            'signed' => 'blue',
            'completed' => 'green',
            default => 'gray',
        };
    }

    /**
     * Проверка, требует ли животное особого внимания
     */
    public function requiresSpecialAttention(): bool
    {
        return $this->health_status === 'critical' ||
               $this->aggression_level === 'unmotivated' ||
               ($this->sterilization_required && !$this->is_sterilized);
    }

    /**
     * Проверка, можно ли вернуть животное
     */
    public function canBeReturned(): bool
    {
        return $this->return_to_habitat_allowed &&
               $this->is_sterilized &&
               in_array($this->aggression_level, ['none', 'low']) &&
               in_array($this->health_status, ['healthy']);
    }

    /**
     * Генерация номера акта осмотра
     */
    public static function generateActNumber(): string
    {
        $year = date('Y');
        $lastAct = self::whereYear('inspection_date', $year)
            ->orderBy('act_number', 'desc')
            ->first();

        if ($lastAct && preg_match('/ОС-(\d+)\/(\d{4})$/', $lastAct->act_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('ОС-%03d/%s', $nextNumber, $year);
    }
} 