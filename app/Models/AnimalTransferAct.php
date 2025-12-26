<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnimalTransferAct extends Model
{
    use HasFactory;

    protected $fillable = [
        'act_number',
        'act_date',
        'transfer_type',
        'from_organization',
        'to_organization',
        'from_representatives',
        'to_representatives',
        'transfer_reason',
        'notes',
        'status',
    ];

    protected $casts = [
        'act_date' => 'date',
        'from_representatives' => 'array',
        'to_representatives' => 'array',
    ];

    public function animals(): BelongsToMany
    {
        return $this->belongsToMany(Animal::class, 'animal_transfer_act_animals')
            ->withPivot(['animal_condition', 'special_notes'])
            ->withTimestamps();
    }

    public function transferActAnimals(): HasMany
    {
        return $this->hasMany(AnimalTransferActAnimal::class, 'transfer_act_id');
    }

    public static function getTransferTypes(): array
    {
        return [
            'intake' => 'Поступление',
            'release' => 'Передача',
            'return' => 'Возврат',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            'draft' => 'Черновик',
            'signed' => 'Подписан',
            'completed' => 'Выполнен',
        ];
    }

    public function getTransferTypeNameAttribute(): string
    {
        return self::getTransferTypes()[$this->transfer_type] ?? $this->transfer_type;
    }

    public function getStatusNameAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'gray',
            'signed' => 'blue',
            'completed' => 'green',
            default => 'gray',
        };
    }

    /**
     * Генерация номера акта
     */
    public static function generateActNumber(): string
    {
        $year = date('Y');
        $lastAct = self::whereYear('act_date', $year)
            ->orderBy('act_number', 'desc')
            ->first();

        if ($lastAct && preg_match('/(\d+)\/(\d{4})$/', $lastAct->act_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('%03d/%s', $nextNumber, $year);
    }
} 