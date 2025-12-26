<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class WorkSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'name',
        'type',
        'start_date',
        'end_date',
        'monday_start',
        'monday_end',
        'tuesday_start',
        'tuesday_end',
        'wednesday_start',
        'wednesday_end',
        'thursday_start',
        'thursday_end',
        'friday_start',
        'friday_end',
        'saturday_start',
        'saturday_end',
        'sunday_start',
        'sunday_end',
        'lunch_start',
        'lunch_end',
        'hours_per_week',
        'hours_per_day',
        'special_dates',
        'notes',
        'is_active'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'special_dates' => 'array',
        'is_active' => 'boolean'
    ];

    /**
     * Связь с сотрудником
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Получить время работы для конкретного дня недели
     */
    public function getWorkTimeForDay($dayOfWeek)
    {
        $days = [
            0 => 'sunday',
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday'
        ];

        $day = $days[$dayOfWeek] ?? null;
        
        if (!$day) {
            return null;
        }

        $startField = $day . '_start';
        $endField = $day . '_end';

        return [
            'start' => $this->$startField,
            'end' => $this->$endField,
            'is_working_day' => !is_null($this->$startField) && !is_null($this->$endField)
        ];
    }

    /**
     * Получить время работы для конкретной даты
     */
    public function getWorkTimeForDate($date)
    {
        $carbon = Carbon::parse($date);
        $dayOfWeek = $carbon->dayOfWeek;
        
        // Проверяем особые даты
        $specialDates = $this->special_dates ?? [];
        $dateString = $carbon->format('Y-m-d');
        
        if (isset($specialDates[$dateString])) {
            return $specialDates[$dateString];
        }
        
        return $this->getWorkTimeForDay($dayOfWeek);
    }

    /**
     * Проверить, является ли дата рабочим днем
     */
    public function isWorkingDay($date): bool
    {
        $workTime = $this->getWorkTimeForDate($date);
        return $workTime && $workTime['is_working_day'] ?? false;
    }

    /**
     * Получить количество рабочих часов в день
     */
    public function getWorkingHoursForDate($date): float
    {
        $workTime = $this->getWorkTimeForDate($date);
        
        if (!$workTime || !($workTime['is_working_day'] ?? false)) {
            return 0;
        }

        $start = Carbon::parse($workTime['start']);
        $end = Carbon::parse($workTime['end']);
        
        $totalMinutes = $end->diffInMinutes($start);
        
        // Вычитаем время обеда
        if ($this->lunch_start && $this->lunch_end) {
            $lunchStart = Carbon::parse($this->lunch_start);
            $lunchEnd = Carbon::parse($this->lunch_end);
            $lunchMinutes = $lunchEnd->diffInMinutes($lunchStart);
            $totalMinutes -= $lunchMinutes;
        }
        
        return round($totalMinutes / 60, 2);
    }

    /**
     * Получить расписание на неделю
     */
    public function getWeeklySchedule(): array
    {
        $schedule = [];
        $days = ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье'];
        
        for ($i = 1; $i <= 7; $i++) {
            $dayIndex = $i === 7 ? 0 : $i; // Воскресенье = 0
            $workTime = $this->getWorkTimeForDay($dayIndex);
            
            $schedule[] = [
                'day' => $days[$i - 1],
                'day_index' => $dayIndex,
                'start' => $workTime['start'] ?? null,
                'end' => $workTime['end'] ?? null,
                'is_working' => $workTime['is_working_day'] ?? false,
                'hours' => $workTime['is_working_day'] ?? false ? 
                    $this->getWorkingHoursForDate(now()->startOfWeek()->addDays($i - 1)) : 0
            ];
        }
        
        return $schedule;
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCurrent($query)
    {
        return $query->where('start_date', '<=', now())
                    ->where(function($q) {
                        $q->whereNull('end_date')
                          ->orWhere('end_date', '>=', now());
                    });
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Типы графиков работы
     */
    public static function getScheduleTypes(): array
    {
        return [
            'standard' => 'Стандартный (5/2)',
            'shift' => 'Сменный',
            'flexible' => 'Гибкий',
            'individual' => 'Индивидуальный',
            '24_7' => 'Круглосуточный',
            'part_time' => 'Неполный день'
        ];
    }

    /**
     * Создать стандартный график 5/2
     */
    public static function createStandardSchedule($employeeId, $startTime = '09:00', $endTime = '18:00')
    {
        return self::create([
            'employee_id' => $employeeId,
            'name' => 'Стандартный график 5/2',
            'type' => 'standard',
            'start_date' => now(),
            'monday_start' => $startTime,
            'monday_end' => $endTime,
            'tuesday_start' => $startTime,
            'tuesday_end' => $endTime,
            'wednesday_start' => $startTime,
            'wednesday_end' => $endTime,
            'thursday_start' => $startTime,
            'thursday_end' => $endTime,
            'friday_start' => $startTime,
            'friday_end' => $endTime,
            'lunch_start' => '13:00',
            'lunch_end' => '14:00',
            'hours_per_week' => 40,
            'hours_per_day' => 8,
            'is_active' => true
        ]);
    }
}
