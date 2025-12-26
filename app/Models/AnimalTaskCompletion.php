<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnimalTaskCompletion extends Model
{
    use HasFactory;

    protected $fillable = [
        'animal_id',
        'task_id',
        'completed_by',
        'completed_at',
        'notes',
        'data',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'data' => 'array',
    ];

    // Связи
    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(StageTask::class, 'task_id');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    // Скоупы
    public function scopeForAnimal($query, int $animalId)
    {
        return $query->where('animal_id', $animalId);
    }

    public function scopeForTask($query, int $taskId)
    {
        return $query->where('task_id', $taskId);
    }

    public function scopeCompletedBy($query, int $userId)
    {
        return $query->where('completed_by', $userId);
    }

    public function scopeCompletedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('completed_at', [$startDate, $endDate]);
    }

    // Методы
    public function getFormattedDataAttribute(): string
    {
        if (!$this->data) {
            return '';
        }

        $formatted = [];
        foreach ($this->data as $key => $value) {
            $formatted[] = "{$key}: {$value}";
        }

        return implode(', ', $formatted);
    }

    public function getDaysToComplete(): int
    {
        return $this->animal->stage_started_at->diffInDays($this->completed_at);
    }
}
