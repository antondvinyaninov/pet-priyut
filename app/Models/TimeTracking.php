<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class TimeTracking extends Model
{
    use HasFactory;

    protected $table = 'time_tracking';

    protected $fillable = [
        'employee_id',
        'work_date',
        'clock_in',
        'clock_out',
        'lunch_start',
        'lunch_end',
        'total_minutes',
        'work_minutes',
        'lunch_minutes',
        'overtime_minutes',
        'late_minutes',
        'early_leave_minutes',
        'status',
        'absence_reason',
        'notes',
        'breaks',
        'is_approved',
        'approved_by',
        'approved_at',
        'clock_in_lat',
        'clock_in_lng',
        'clock_out_lat',
        'clock_out_lng'
    ];

    protected $casts = [
        'work_date' => 'date',
        'breaks' => 'array',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime'
    ];

    /**
     * Связь с сотрудником
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Связь с пользователем, который подтвердил запись
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Автоматический расчет времени при сохранении
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($timeTracking) {
            $timeTracking->calculateTime();
        });
    }

    /**
     * Расчет всех временных показателей
     */
    public function calculateTime()
    {
        if ($this->status !== 'present' || !$this->clock_in) {
            $this->resetTimeFields();
            return;
        }

        // Получаем график работы сотрудника
        $schedule = $this->employee->activeWorkSchedule();
        $expectedWorkTime = $schedule ? $schedule->getWorkTimeForDate($this->work_date) : null;

        // Рассчитываем общее время
        if ($this->clock_out) {
            $workDate = Carbon::parse($this->work_date)->format('Y-m-d');
            $clockIn = Carbon::parse($workDate . ' ' . $this->clock_in);
            $clockOut = Carbon::parse($workDate . ' ' . $this->clock_out);
            
            // Если время ухода меньше времени прихода, значит работа продолжилась на следующий день
            if ($clockOut->lt($clockIn)) {
                $clockOut->addDay();
            }
            
            $this->total_minutes = $clockOut->diffInMinutes($clockIn);
        } else {
            $this->total_minutes = 0;
        }

        // Рассчитываем время обеда
        $this->lunch_minutes = 0;
        if ($this->lunch_start && $this->lunch_end) {
            $workDate = Carbon::parse($this->work_date)->format('Y-m-d');
            $lunchStart = Carbon::parse($workDate . ' ' . $this->lunch_start);
            $lunchEnd = Carbon::parse($workDate . ' ' . $this->lunch_end);
            $this->lunch_minutes = $lunchEnd->diffInMinutes($lunchStart);
        }

        // Рассчитываем дополнительные перерывы
        $breakMinutes = 0;
        if ($this->breaks) {
            $workDate = Carbon::parse($this->work_date)->format('Y-m-d');
            foreach ($this->breaks as $break) {
                if (isset($break['start']) && isset($break['end'])) {
                    $breakStart = Carbon::parse($workDate . ' ' . $break['start']);
                    $breakEnd = Carbon::parse($workDate . ' ' . $break['end']);
                    $breakMinutes += $breakEnd->diffInMinutes($breakStart);
                }
            }
        }

        // Рабочее время = общее время - обед - перерывы
        $this->work_minutes = max(0, $this->total_minutes - $this->lunch_minutes - $breakMinutes);

        // Рассчитываем опоздания и ранний уход
        $this->calculateLatenessAndEarlyLeave($expectedWorkTime);

        // Рассчитываем сверхурочные
        $this->calculateOvertime($expectedWorkTime);
    }

    /**
     * Расчет опозданий и раннего ухода
     */
    private function calculateLatenessAndEarlyLeave($expectedWorkTime)
    {
        $this->late_minutes = 0;
        $this->early_leave_minutes = 0;

        if (!$expectedWorkTime || !$expectedWorkTime['is_working_day']) {
            return;
        }

        // Опоздание
        if ($this->clock_in && $expectedWorkTime['start']) {
            $workDate = Carbon::parse($this->work_date)->format('Y-m-d');
            $expectedStart = Carbon::parse($workDate . ' ' . $expectedWorkTime['start']);
            $actualStart = Carbon::parse($workDate . ' ' . $this->clock_in);
            
            if ($actualStart->gt($expectedStart)) {
                $this->late_minutes = $actualStart->diffInMinutes($expectedStart);
            }
        }

        // Ранний уход
        if ($this->clock_out && $expectedWorkTime['end']) {
            $workDate = Carbon::parse($this->work_date)->format('Y-m-d');
            $expectedEnd = Carbon::parse($workDate . ' ' . $expectedWorkTime['end']);
            $actualEnd = Carbon::parse($workDate . ' ' . $this->clock_out);
            
            if ($actualEnd->lt($expectedEnd)) {
                $this->early_leave_minutes = $expectedEnd->diffInMinutes($actualEnd);
            }
        }
    }

    /**
     * Расчет сверхурочных
     */
    private function calculateOvertime($expectedWorkTime)
    {
        $this->overtime_minutes = 0;

        if (!$expectedWorkTime || !$expectedWorkTime['is_working_day']) {
            return;
        }

        $schedule = $this->employee->activeWorkSchedule();
        if (!$schedule) {
            return;
        }

        $expectedHours = $schedule->getWorkingHoursForDate($this->work_date);
        $expectedMinutes = $expectedHours * 60;
        
        if ($this->work_minutes > $expectedMinutes) {
            $this->overtime_minutes = $this->work_minutes - $expectedMinutes;
        }
    }

    /**
     * Сброс временных полей
     */
    private function resetTimeFields()
    {
        $this->total_minutes = 0;
        $this->work_minutes = 0;
        $this->lunch_minutes = 0;
        $this->overtime_minutes = 0;
        $this->late_minutes = 0;
        $this->early_leave_minutes = 0;
    }

    /**
     * Получить рабочее время в часах
     */
    public function getWorkHoursAttribute(): float
    {
        return round($this->work_minutes / 60, 2);
    }

    /**
     * Получить сверхурочные в часах
     */
    public function getOvertimeHoursAttribute(): float
    {
        return round($this->overtime_minutes / 60, 2);
    }

    /**
     * Получить время опоздания в часах
     */
    public function getLateHoursAttribute(): float
    {
        return round($this->late_minutes / 60, 2);
    }

    /**
     * Получить общее время в часах
     */
    public function getTotalHoursAttribute(): float
    {
        return round($this->total_minutes / 60, 2);
    }

    /**
     * Проверить, было ли опоздание
     */
    public function getIsLateAttribute(): bool
    {
        return $this->late_minutes > 0;
    }

    /**
     * Проверить, был ли день полностью отработан
     */
    public function isFullWorkDay(): bool
    {
        $schedule = $this->employee->activeWorkSchedule();
        if (!$schedule) {
            return false;
        }

        $expectedHours = $schedule->getWorkingHoursForDate($this->work_date);
        $actualHours = $this->work_hours;
        
        // Считаем день полностью отработанным, если отклонение не более 15 минут
        return abs($expectedHours - $actualHours) <= 0.25;
    }

    /**
     * Scopes
     */
    public function scopeForDate($query, $date)
    {
        return $query->where('work_date', $date);
    }

    public function scopeForMonth($query, $year, $month)
    {
        return $query->whereYear('work_date', $year)
                    ->whereMonth('work_date', $month);
    }

    public function scopePresent($query)
    {
        return $query->where('status', 'present');
    }

    public function scopeAbsent($query)
    {
        return $query->where('status', '!=', 'present');
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_approved', false);
    }

    /**
     * Статусы дня
     */
    public static function getStatuses(): array
    {
        return [
            'present' => 'Присутствовал',
            'absent' => 'Отсутствовал',
            'sick' => 'Больничный',
            'vacation' => 'Отпуск',
            'holiday' => 'Праздничный день',
            'business_trip' => 'Командировка',
            'remote' => 'Удаленная работа'
        ];
    }

    /**
     * Создать запись о приходе на работу
     */
    public static function clockIn($employeeId, $time = null, $lat = null, $lng = null)
    {
        $time = $time ?? now()->format('H:i:s');
        $date = now()->format('Y-m-d');

        return self::updateOrCreate(
            [
                'employee_id' => $employeeId,
                'work_date' => $date
            ],
            [
                'clock_in' => $time,
                'clock_in_lat' => $lat,
                'clock_in_lng' => $lng,
                'status' => 'present'
            ]
        );
    }

    /**
     * Создать запись об уходе с работы
     */
    public function clockOut($time = null, $lat = null, $lng = null)
    {
        $time = $time ?? now()->format('H:i:s');
        
        $this->update([
            'clock_out' => $time,
            'clock_out_lat' => $lat,
            'clock_out_lng' => $lng
        ]);

        return $this;
    }
}
