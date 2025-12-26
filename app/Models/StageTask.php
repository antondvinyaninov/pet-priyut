<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StageTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'stage_id',
        'name',
        'description',
        'order',
        'is_required',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    // Связи
    public function stage(): BelongsTo
    {
        return $this->belongsTo(AnimalStage::class, 'stage_id');
    }

    public function completions(): HasMany
    {
        return $this->hasMany(AnimalTaskCompletion::class, 'task_id');
    }

    // Скоупы
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    // Методы
    public function isCompletedForAnimal(int $animalId): bool
    {
        return $this->completions()
            ->where('animal_id', $animalId)
            ->exists();
    }

    public function getCompletionForAnimal(int $animalId): ?AnimalTaskCompletion
    {
        return $this->completions()
            ->where('animal_id', $animalId)
            ->first();
    }

    public function getCompletionRate(): float
    {
        $totalAnimals = Animal::where('current_stage_id', $this->stage_id)
            ->where('status', 'active')
            ->count();

        if ($totalAnimals === 0) {
            return 0;
        }

        $completedCount = $this->completions()
            ->whereHas('animal', function($query) {
                $query->where('current_stage_id', $this->stage_id)
                      ->where('status', 'active');
            })
            ->count();

        return ($completedCount / $totalAnimals) * 100;
    }
}
