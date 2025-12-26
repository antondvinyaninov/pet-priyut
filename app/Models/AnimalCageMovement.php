<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnimalCageMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'animal_id',
        'from_cage',
        'to_cage',
        'comment',
        'moved_at',
        'moved_by',
    ];

    protected $casts = [
        'moved_at' => 'datetime',
    ];
}






