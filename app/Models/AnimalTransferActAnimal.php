<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnimalTransferActAnimal extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_act_id',
        'animal_id',
        'animal_condition',
        'special_notes',
    ];

    public function transferAct(): BelongsTo
    {
        return $this->belongsTo(AnimalTransferAct::class, 'transfer_act_id');
    }

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }
} 