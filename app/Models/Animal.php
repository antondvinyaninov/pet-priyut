<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Animal extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'gender',
        'breed',
        'color',
        'age_months',
        'weight',
        'description',
        'photo',
        'chip_number',
        'tag_number',
        'cage_number',
        'osvv_request_id',
        'current_stage_id',
        'arrived_at',
        'stage_started_at',
        'completed_at',
        'status',
    ];

    protected $casts = [
        'arrived_at' => 'datetime',
        'stage_started_at' => 'datetime',
        'completed_at' => 'datetime',
        'weight' => 'decimal:2',
    ];

    // Связи
    public function osvvRequest(): BelongsTo
    {
        return $this->belongsTo(OsvvRequest::class);
    }

    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(AnimalStage::class, 'current_stage_id');
    }

    public function taskCompletions(): HasMany
    {
        return $this->hasMany(AnimalTaskCompletion::class);
    }

    // Связи с нормативными таблицами
    public function transferActs(): BelongsToMany
    {
        return $this->belongsToMany(AnimalTransferAct::class, 'animal_transfer_act_animals', 'animal_id', 'transfer_act_id')
            ->withPivot(['animal_condition', 'special_notes'])
            ->withTimestamps();
    }

    public function cageMovements(): HasMany
    {
        return $this->hasMany(AnimalCageMovement::class);
    }

    public function inspectionActs(): HasMany
    {
        return $this->hasMany(AnimalInspectionAct::class);
    }

    public function registrationCard(): HasOne
    {
        return $this->hasOne(AnimalRegistrationCard::class);
    }

    public function returnProcedure(): HasOne
    {
        return $this->hasOne(AnimalReturnProcedure::class);
    }

    public function releaseTracking(): HasMany
    {
        return $this->hasMany(DepartureRouteAnimal::class);
    }

    // Методы
    public function getTypeNameAttribute(): string
    {
        return match($this->type) {
            'dog' => 'Собака',
            'cat' => 'Кошка',
            'other' => 'Другое',
            default => 'Неизвестно'
        };
    }

    public function getGenderNameAttribute(): string
    {
        return match($this->gender) {
            'male' => 'Самец',
            'female' => 'Самка',
            'unknown' => 'Неизвестно',
            default => 'Неизвестно'
        };
    }

    public function getStatusNameAttribute(): string
    {
        return match($this->status) {
            'active' => 'Активный',
            'released' => 'Готов к выезду',
            'adopted' => 'В приюте',
            'deceased' => 'Умер',
            default => 'Неизвестно'
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'active' => 'blue',
            'released' => 'green',
            'adopted' => 'purple',
            'deceased' => 'gray',
            default => 'gray'
        };
    }

    public function getCompletedTasksForStage(int $stageId): array
    {
        return $this->taskCompletions()
            ->whereHas('task', function($query) use ($stageId) {
                $query->where('stage_id', $stageId);
            })
            ->pluck('task_id')
            ->toArray();
    }

    public function canMoveToNextStage(): bool
    {
        $currentStage = $this->currentStage;
        if (!$currentStage) {
            return false;
        }

        $requiredTasks = $currentStage->tasks()->where('is_required', true)->pluck('id');
        $completedTasks = $this->getCompletedTasksForStage($currentStage->id);

        return $requiredTasks->diff($completedTasks)->isEmpty();
    }

    public function getDaysInCurrentStage(): int
    {
        return $this->stage_started_at->diffInDays(now());
    }
}
