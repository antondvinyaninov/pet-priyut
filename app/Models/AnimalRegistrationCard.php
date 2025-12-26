<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnimalRegistrationCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'animal_id',
        'registration_number',
        'registration_date',
        'category',
        'photo_face', 'photo_profile',
        'intake_source',
        'intake_location',
        'physical_description',
        'coat', 'ears', 'tail', 'size',
        'special_marks',
        'clinical_exam_date', 'clinical_exam_conclusion',
        'aggression_notes', 'behavior_correction_notes',
        'deworming_date', 'sterilization_date', 'sterilization_vet',
        'marking_date', 'tag_number_card', 'chip_number_card',
        'vet_doc_number', 'vet_doc_date', 'capture_location_address',
        'capture_act_number', 'capture_act_date',
        'aggression_act_number', 'aggression_act_date',
        'outcome_reason', 'outcome_date',
        'release_address',
        'new_owner_type', 'new_owner_name', 'new_owner_address', 'new_owner_phone',
        'reproductive_status',
        'veterinary_notes',
        'vaccination_history',
        'vaccination_act_number',
        'vaccination_act_date',
        'vaccination_type',
        'vaccination_series',
        'vaccination_manufacture_date',
        'card_status',
    ];

    protected $casts = [
        'registration_date' => 'date',
        'intake_location' => 'array',
        'special_marks' => 'array',
        'clinical_exam_date' => 'date',
        'deworming_date' => 'date',
        'sterilization_date' => 'date',
        'marking_date' => 'date',
        'vet_doc_date' => 'date',
        'capture_act_date' => 'date',
        'aggression_act_date' => 'date',
        'outcome_date' => 'date',
        'vaccination_history' => 'array',
        'vaccination_act_date' => 'date',
    ];

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }

    public static function getReproductiveStatuses(): array
    {
        return [
            'intact' => 'Не стерилизован',
            'sterilized' => 'Стерилизован',
            'castrated' => 'Кастрирован',
            'unknown' => 'Неизвестно',
        ];
    }

    public static function getCardStatuses(): array
    {
        return [
            'active' => 'Активная',
            'archived' => 'Архивная',
            'transferred' => 'Передана',
        ];
    }

    public static function getIntakeSources(): array
    {
        return [
            'ОСВВ' => 'ОСВВ',
            'Находка' => 'Находка',
            'Передача от владельца' => 'Передача от владельца',
            'Другой приют' => 'Другой приют',
            'Конфискация' => 'Конфискация',
            'Другое' => 'Другое',
        ];
    }

    public function getReproductiveStatusNameAttribute(): string
    {
        return self::getReproductiveStatuses()[$this->reproductive_status] ?? $this->reproductive_status;
    }

    public function getCardStatusNameAttribute(): string
    {
        return self::getCardStatuses()[$this->card_status] ?? ($this->card_status ?? '');
    }

    public function getCardStatusColorAttribute(): string
    {
        return match ($this->card_status) {
            'active' => 'green',
            'archived' => 'gray',
            'transferred' => 'blue',
            default => 'gray',
        };
    }

    /**
     * Генерация регистрационного номера
     */
    public static function generateRegistrationNumber(): string
    {
        $year = date('Y');
        $lastCard = self::whereYear('registration_date', $year)
            ->orderBy('registration_number', 'desc')
            ->first();

        if ($lastCard && preg_match('/РК-(\d+)\/(\d{4})$/', $lastCard->registration_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('РК-%04d/%s', $nextNumber, $year);
    }

    /**
     * Получить список особых примет в виде строки
     */
    public function getSpecialMarksListAttribute(): string
    {
        if (!$this->special_marks) {
            return '';
        }

        return collect($this->special_marks)->map(function ($mark) {
            return $mark['type'] . ': ' . $mark['description'];
        })->implode(', ');
    }

    /**
     * Получить последнюю вакцинацию
     */
    public function getLastVaccinationAttribute(): ?array
    {
        if (!$this->vaccination_history) {
            return null;
        }

        return collect($this->vaccination_history)->sortByDesc('date')->first();
    }
} 