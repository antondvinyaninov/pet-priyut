<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class DeparturePlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'planned_date',
        'status',
        'created_by',
        'notes',
        'total_requests',
        'total_routes',
        'estimated_duration'
    ];

    protected $casts = [
        'planned_date' => 'date',
        'total_requests' => 'integer',
        'total_routes' => 'integer',
        'estimated_duration' => 'integer',
    ];

    /**
     * Создатель плана
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Маршруты в плане
     */
    public function routes(): HasMany
    {
        return $this->hasMany(DepartureRoute::class);
    }

    /**
     * Все заявки в плане через маршруты
     */
    public function requests()
    {
        return $this->hasManyThrough(
            DepartureRouteRequest::class,
            DepartureRoute::class,
            'departure_plan_id',
            'departure_route_id'
        );
    }

    /**
     * Задачи, связанные с планом выезда
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(EmployeeTask::class);
    }

    /**
     * Статусы плана
     */
    public static function getStatuses(): array
    {
        return [
            'draft' => 'Черновик',
            'approved' => 'Утвержден',
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
            'draft' => 'gray',
            'approved' => 'blue',
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
     * Автоматически генерирует название плана
     */
    public static function generateDefaultName(Carbon $date): string
    {
        return 'План выездов на ' . $date->format('d.m.Y');
    }

    /**
     * Пересчитывает статистику плана
     */
    public function recalculateStats(): void
    {
        $this->total_routes = $this->routes()->count();
        $this->total_requests = $this->requests()->count();
        $this->estimated_duration = $this->routes()->sum('estimated_duration');
        $this->save();
    }

    /**
     * Проверяет, можно ли редактировать план
     */
    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'approved']);
    }

    /**
     * Проверяет, можно ли утвердить план
     */
    public function canBeApproved(): bool
    {
        return $this->status === 'draft' && $this->total_routes > 0;
    }

    /**
     * Утверждает план
     */
    public function approve(): bool
    {
        if ($this->canBeApproved()) {
            $this->status = 'approved';
            return $this->save();
        }
        return false;
    }

    /**
     * Запускает выполнение плана
     */
    public function start(): bool
    {
        if ($this->status === 'approved') {
            $this->status = 'in_progress';
            return $this->save();
        }
        return false;
    }

    /**
     * Завершает план
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
     * Отменяет план
     */
    public function cancel(): bool
    {
        if (in_array($this->status, ['draft', 'approved', 'in_progress'])) {
            $this->status = 'cancelled';
            return $this->save();
        }
        return false;
    }

    /**
     * Scopes
     */
    public function scopeForDate($query, Carbon $date)
    {
        return $query->where('planned_date', $date->format('Y-m-d'));
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['draft', 'approved', 'in_progress']);
    }
}
