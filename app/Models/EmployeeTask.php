<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class EmployeeTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'priority',
        'status',
        'type',
        'assigned_to',
        'created_by',
        'assigned_by',
        'due_date',
        'started_at',
        'completed_at',
        'estimated_hours',
        'actual_hours',
        'notes',
        'completion_notes',
        'attachments',
        'tags',
        'osvv_request_id',
        'animal_id',
        'employee_id',
        'departure_plan_id',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'attachments' => 'array',
        'tags' => 'array',
    ];

    // Константы для статусов
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    const STATUSES = [
        self::STATUS_PENDING => 'Ожидает',
        self::STATUS_IN_PROGRESS => 'В работе',
        self::STATUS_COMPLETED => 'Выполнено',
        self::STATUS_CANCELLED => 'Отменено',
    ];

    // Константы для приоритетов
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    const PRIORITIES = [
        self::PRIORITY_LOW => 'Низкий',
        self::PRIORITY_MEDIUM => 'Средний',
        self::PRIORITY_HIGH => 'Высокий',
        self::PRIORITY_URGENT => 'Срочный',
    ];

    // Константы для типов задач
    const TYPE_GENERAL = 'general';
    const TYPE_OSVV = 'osvv';
    const TYPE_ANIMAL_CARE = 'animal_care';
    const TYPE_ADMINISTRATIVE = 'administrative';

    const TYPES = [
        self::TYPE_GENERAL => 'Общая',
        self::TYPE_OSVV => 'ОСВВ',
        self::TYPE_ANIMAL_CARE => 'Уход за животными',
        self::TYPE_ADMINISTRATIVE => 'Административная',
    ];

    // Отношения
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function osvvRequest(): BelongsTo
    {
        return $this->belongsTo(OsvvRequest::class);
    }

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function departurePlan(): BelongsTo
    {
        return $this->belongsTo(DeparturePlan::class);
    }

    // Скоупы
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority(Builder $query, string $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('due_date', '<', now())
            ->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function scopeDueSoon(Builder $query, int $hours = 24): Builder
    {
        return $query->where('due_date', '>=', now())
            ->where('due_date', '<=', now()->addHours($hours))
            ->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    // Методы
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getPriorityLabelAttribute(): string
    {
        return self::PRIORITIES[$this->priority] ?? $this->priority;
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function isOverdue(): bool
    {
        return $this->due_date && 
               $this->due_date->isPast() && 
               !in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function isDueSoon(int $hours = 24): bool
    {
        return $this->due_date && 
               $this->due_date->isFuture() && 
               $this->due_date->diffInHours(now()) <= $hours &&
               !in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function canStart(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function canComplete(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_IN_PROGRESS]);
    }

    public function canCancel(): bool
    {
        return !in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function start(): void
    {
        if ($this->canStart()) {
            $this->update([
                'status' => self::STATUS_IN_PROGRESS,
                'started_at' => now(),
            ]);
        }
    }

    public function complete(string $completionNotes = null, int $actualHours = null): void
    {
        if ($this->canComplete()) {
            $this->update([
                'status' => self::STATUS_COMPLETED,
                'completed_at' => now(),
                'completion_notes' => $completionNotes,
                'actual_hours' => $actualHours,
            ]);
        }
    }

    public function cancel(): void
    {
        if ($this->canCancel()) {
            $this->update([
                'status' => self::STATUS_CANCELLED,
            ]);
        }
    }

    public function getDurationAttribute(): ?int
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        return $this->started_at->diffInMinutes($this->completed_at);
    }

    public function getTimeToDeadlineAttribute(): ?string
    {
        if (!$this->due_date) {
            return null;
        }

        $diff = $this->due_date->diffForHumans();
        
        if ($this->due_date->isPast()) {
            return "Просрочено на " . $this->due_date->diffForHumans(null, true);
        }

        return "Осталось " . $this->due_date->diffForHumans(null, true);
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'green',
            self::PRIORITY_MEDIUM => 'yellow',
            self::PRIORITY_HIGH => 'orange',
            self::PRIORITY_URGENT => 'red',
            default => 'gray',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'gray',
            self::STATUS_IN_PROGRESS => 'blue',
            self::STATUS_COMPLETED => 'green',
            self::STATUS_CANCELLED => 'red',
            default => 'gray',
        };
    }
}
