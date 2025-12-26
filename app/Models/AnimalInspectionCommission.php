<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnimalInspectionCommission extends Model
{
    use HasFactory;

    protected $fillable = [
        'commission_name',
        'formation_date',
        'valid_until',
        'members',
        'is_active',
    ];

    protected $casts = [
        'formation_date' => 'date',
        'valid_until' => 'date',
        'members' => 'array',
        'is_active' => 'boolean',
    ];

    public function inspectionActs(): HasMany
    {
        return $this->hasMany(AnimalInspectionAct::class, 'commission_id');
    }

    /**
     * Проверка, действует ли комиссия на указанную дату
     */
    public function isValidOn(\DateTime|string $date): bool
    {
        $checkDate = is_string($date) ? new \DateTime($date) : $date;
        $formationDate = new \DateTime($this->formation_date->format('Y-m-d'));
        
        if ($checkDate < $formationDate) {
            return false;
        }

        if ($this->valid_until) {
            $validUntil = new \DateTime($this->valid_until->format('Y-m-d'));
            return $checkDate <= $validUntil;
        }

        return $this->is_active;
    }

    /**
     * Получить активные комиссии на текущую дату
     */
    public static function getActiveCommissions(): \Illuminate\Database\Eloquent\Collection
    {
        $today = now()->format('Y-m-d');
        
        return self::where('is_active', true)
            ->where('formation_date', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', $today);
            })
            ->get();
    }

    /**
     * Получить список членов комиссии в виде строки
     */
    public function getMembersListAttribute(): string
    {
        if (!$this->members) {
            return '';
        }

        return collect($this->members)->map(function ($member) {
            return $member['name'] . ' (' . $member['position'] . ')';
        })->implode(', ');
    }

    /**
     * Получить председателя комиссии
     */
    public function getChairmanAttribute(): ?array
    {
        if (!$this->members) {
            return null;
        }

        return collect($this->members)->firstWhere('is_chairman', true);
    }
} 