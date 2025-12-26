<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CaptureAct extends Model
{
    use HasFactory;

    /**
     * Атрибуты, для которых разрешено массовое назначение.
     *
     * @var array
     */
    protected $fillable = [
        'osvv_request_id',
        'user_id',
        'act_number',
        'capture_date',
        'capture_time',
        'capture_location',
        'animal_type',
        'animal_gender',
        'animal_breed',
        'animal_color',
        'animal_size',
        'animal_features',
        'animal_behavior',
        'capturing_method',
        'notes',
        'status',
        'animals_count',
    ];

    /**
     * Атрибуты, которые должны быть приведены к определенным типам.
     *
     * @var array
     */
    protected $casts = [
        'capture_date' => 'date',
        'capture_time' => 'datetime:H:i',
    ];

    /**
     * Получить заявку ОСВВ, связанную с актом.
     */
    public function osvvRequest()
    {
        return $this->belongsTo(OsvvRequest::class);
    }

    /**
     * Получить пользователя (отловщика), ответственного за акт.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Сгенерировать номер акта.
     * 
     * @return string
     */
    public static function generateActNumber(): string
    {
        $today = Carbon::now();
        $prefix = 'ACT-' . $today->format('Ym');
        
        // Находим последний номер акта с этим префиксом
        $lastAct = self::where('act_number', 'like', $prefix . '%')
            ->orderBy('act_number', 'desc')
            ->first();
        
        if ($lastAct) {
            // Извлекаем последний номер и увеличиваем на 1
            $lastNumber = intval(substr($lastAct->act_number, strlen($prefix) + 1));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        // Форматируем номер с ведущими нулями
        return $prefix . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Получить список статусов акта.
     * 
     * @return array
     */
    public static function getStatusList(): array
    {
        return [
            'created' => 'Создан',
            'completed' => 'Выполнен',
            'cancelled' => 'Отменен',
        ];
    }

    /**
     * Получить форматированную дату и время отлова.
     * 
     * @return string
     */
    public function getFormattedCaptureDateTime(): string
    {
        $result = $this->capture_date->format('d.m.Y');
        
        if ($this->capture_time) {
            $result .= ' в ' . Carbon::parse($this->capture_time)->format('H:i');
        }
        
        return $result;
    }
}
