<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegulatoryDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_type',
        'document_number',
        'document_date',
        'issuing_authority',
        'title',
        'description',
        'file_path',
        'is_active',
        'effective_from',
        'effective_until',
    ];

    protected $casts = [
        'document_date' => 'date',
        'effective_from' => 'date',
        'effective_until' => 'date',
        'is_active' => 'boolean',
    ];

    public static function getDocumentTypes(): array
    {
        return [
            'Приказ' => 'Приказ',
            'Постановление' => 'Постановление',
            'Распоряжение' => 'Распоряжение',
            'Указ' => 'Указ',
            'Федеральный закон' => 'Федеральный закон',
            'Региональный закон' => 'Региональный закон',
            'СанПиН' => 'СанПиН',
            'ГОСТ' => 'ГОСТ',
            'Методические рекомендации' => 'Методические рекомендации',
            'Другое' => 'Другое',
        ];
    }

    public function getStatusColorAttribute(): string
    {
        if (!$this->is_active) {
            return 'gray';
        }

        $today = now()->toDateString();
        
        if ($this->effective_from > $today) {
            return 'blue'; // Еще не вступил в силу
        }

        if ($this->effective_until && $this->effective_until < $today) {
            return 'red'; // Срок действия истек
        }

        return 'green'; // Действующий
    }

    public function getStatusNameAttribute(): string
    {
        if (!$this->is_active) {
            return 'Неактивный';
        }

        $today = now()->toDateString();
        
        if ($this->effective_from > $today) {
            return 'Ожидает вступления в силу';
        }

        if ($this->effective_until && $this->effective_until < $today) {
            return 'Срок действия истек';
        }

        return 'Действующий';
    }

    /**
     * Проверка, действует ли документ на указанную дату
     */
    public function isEffectiveOn(\DateTime|string $date): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $checkDate = is_string($date) ? new \DateTime($date) : $date;
        $effectiveFrom = new \DateTime($this->effective_from->format('Y-m-d'));
        
        if ($checkDate < $effectiveFrom) {
            return false;
        }

        if ($this->effective_until) {
            $effectiveUntil = new \DateTime($this->effective_until->format('Y-m-d'));
            return $checkDate <= $effectiveUntil;
        }

        return true;
    }

    /**
     * Получить действующие документы на текущую дату
     */
    public static function getEffectiveDocuments(): \Illuminate\Database\Eloquent\Collection
    {
        $today = now()->format('Y-m-d');
        
        return self::where('is_active', true)
            ->where('effective_from', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', $today);
            })
            ->orderBy('document_date', 'desc')
            ->get();
    }

    /**
     * Поиск документов по типу
     */
    public static function getByType(string $type): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('document_type', $type)
            ->where('is_active', true)
            ->orderBy('document_date', 'desc')
            ->get();
    }

    /**
     * Получить полное наименование документа
     */
    public function getFullNameAttribute(): string
    {
        return $this->document_type . ' ' . $this->document_number . ' от ' . $this->document_date->format('d.m.Y');
    }
} 