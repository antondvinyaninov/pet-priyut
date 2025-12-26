<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cage extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'title',
        'capacity',
        'layout',
        'cage_block_id',
        'row_index',
        'col_index',
    ];

    protected $casts = [
        'layout' => 'array',
    ];

    public function animals()
    {
        return $this->hasMany(Animal::class, 'cage_number', 'number');
    }

    public function block()
    {
        return $this->belongsTo(CageBlock::class, 'cage_block_id');
    }
}


