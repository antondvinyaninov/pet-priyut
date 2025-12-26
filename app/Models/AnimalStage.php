<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnimalStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'order',
        'duration_days',
        'is_final',
        'is_active',
    ];

    protected $casts = [
        'is_final' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Связи
    public function animals(): HasMany
    {
        return $this->hasMany(Animal::class, 'current_stage_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(StageTask::class, 'stage_id')->orderBy('order');
    }

    public function activeTasks(): HasMany
    {
        return $this->tasks()->where('is_active', true);
    }

    public function requiredTasks(): HasMany
    {
        return $this->tasks()->where('is_required', true)->where('is_active', true);
    }

    // Скоупы
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    // Методы
    public function getNextStage(): ?AnimalStage
    {
        return static::active()
            ->where('order', '>', $this->order)
            ->ordered()
            ->first();
    }

    public function getPreviousStage(): ?AnimalStage
    {
        return static::active()
            ->where('order', '<', $this->order)
            ->orderBy('order', 'desc')
            ->first();
    }

    public function getAnimalsCount(): int
    {
        return $this->animals()->where('status', 'active')->count();
    }

    public function getOverdueAnimalsCount(): int
    {
        if (!$this->duration_days) {
            return 0;
        }

        return $this->animals()
            ->where('status', 'active')
            ->where('stage_started_at', '<', now()->subDays($this->duration_days))
            ->count();
    }
}
