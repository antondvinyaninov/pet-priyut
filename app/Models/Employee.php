<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employee_number',
        'first_name',
        'last_name',
        'middle_name',
        'position',
        'department',
        'supervisor_id',
        'hire_date',
        'termination_date',
        'employment_type',
        'salary',
        'phone',
        'email',
        'address',
        'birth_date',
        'passport_series',
        'passport_number',
        'inn',
        'snils',
        'notes',
        'is_active'
    ];

    protected $casts = [
        'hire_date' => 'date',
        'termination_date' => 'date',
        'birth_date' => 'date',
        'salary' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    /**
     * Связь с пользователем системы
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Связь с руководителем
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'supervisor_id');
    }

    /**
     * Связь с подчиненными
     */
    public function subordinates(): HasMany
    {
        return $this->hasMany(Employee::class, 'supervisor_id');
    }

    /**
     * Связь с графиками работы
     */
    public function workSchedules(): HasMany
    {
        return $this->hasMany(WorkSchedule::class);
    }

    /**
     * Активный график работы
     */
    public function activeWorkSchedule()
    {
        return $this->workSchedules()
            ->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where(function($query) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', now());
            })
            ->latest('start_date')
            ->first();
    }

    /**
     * Связь с записями учета времени
     */
    public function timeTracking(): HasMany
    {
        return $this->hasMany(TimeTracking::class);
    }

    /**
     * Полное имя сотрудника
     */
    public function getFullNameAttribute(): string
    {
        $parts = array_filter([$this->last_name, $this->first_name, $this->middle_name]);
        return implode(' ', $parts);
    }

    /**
     * Краткое имя (Фамилия И.О.)
     */
    public function getShortNameAttribute(): string
    {
        $result = $this->last_name;
        
        if ($this->first_name) {
            $result .= ' ' . mb_substr($this->first_name, 0, 1) . '.';
        }
        
        if ($this->middle_name) {
            $result .= mb_substr($this->middle_name, 0, 1) . '.';
        }
        
        return $result;
    }

    /**
     * Возраст сотрудника
     */
    public function getAgeAttribute(): ?int
    {
        return $this->birth_date ? $this->birth_date->age : null;
    }

    /**
     * Стаж работы
     */
    public function getWorkExperienceAttribute(): string
    {
        $endDate = $this->termination_date ?? now();
        $diff = $this->hire_date->diff($endDate);
        
        $years = $diff->y;
        $months = $diff->m;
        
        $result = [];
        if ($years > 0) {
            $result[] = $years . ' ' . trans_choice('год|года|лет', $years);
        }
        if ($months > 0) {
            $result[] = $months . ' ' . trans_choice('месяц|месяца|месяцев', $months);
        }
        
        return empty($result) ? 'Менее месяца' : implode(' ', $result);
    }

    /**
     * Получить всех подчиненных (включая подчиненных подчиненных)
     */
    public function getAllSubordinates()
    {
        $subordinates = collect();
        
        foreach ($this->subordinates as $subordinate) {
            $subordinates->push($subordinate);
            $subordinates = $subordinates->merge($subordinate->getAllSubordinates());
        }
        
        return $subordinates;
    }

    /**
     * Получить всех руководителей (цепочку до самого верха)
     */
    public function getSupervisorChain()
    {
        $chain = collect();
        $current = $this->supervisor;
        
        while ($current) {
            $chain->push($current);
            $current = $current->supervisor;
        }
        
        return $chain;
    }

    /**
     * Проверить, является ли сотрудник руководителем
     */
    public function isSupervisor(): bool
    {
        return $this->subordinates()->count() > 0;
    }

    /**
     * Получить статистику по времени за период
     */
    public function getTimeStats($startDate, $endDate)
    {
        $timeRecords = $this->timeTracking()
            ->whereBetween('work_date', [$startDate, $endDate])
            ->get();

        return [
            'total_days' => $timeRecords->count(),
            'present_days' => $timeRecords->where('status', 'present')->count(),
            'absent_days' => $timeRecords->where('status', 'absent')->count(),
            'sick_days' => $timeRecords->where('status', 'sick')->count(),
            'vacation_days' => $timeRecords->where('status', 'vacation')->count(),
            'total_work_hours' => round($timeRecords->sum('work_minutes') / 60, 2),
            'total_overtime_hours' => round($timeRecords->sum('overtime_minutes') / 60, 2),
            'average_work_hours' => $timeRecords->count() > 0 ? 
                round($timeRecords->avg('work_minutes') / 60, 2) : 0,
            'late_count' => $timeRecords->where('late_minutes', '>', 0)->count(),
            'total_late_minutes' => $timeRecords->sum('late_minutes')
        ];
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    public function scopeByEmploymentType($query, $type)
    {
        return $query->where('employment_type', $type);
    }

    public function scopeSupervisors($query)
    {
        return $query->whereHas('subordinates');
    }

    /**
     * Константы для типов трудоустройства
     */
    public static function getEmploymentTypes(): array
    {
        return [
            'full_time' => 'Полная занятость',
            'part_time' => 'Частичная занятость',
            'contract' => 'Договор подряда',
            'intern' => 'Стажер',
            'temporary' => 'Временный'
        ];
    }

    /**
     * Получить список отделов
     */
    public static function getDepartments(): array
    {
        return self::distinct('department')
            ->whereNotNull('department')
            ->pluck('department')
            ->sort()
            ->values()
            ->toArray();
    }

    /**
     * Получить список должностей
     */
    public static function getPositions(): array
    {
        return self::distinct('position')
            ->whereNotNull('position')
            ->pluck('position')
            ->sort()
            ->values()
            ->toArray();
    }
}
