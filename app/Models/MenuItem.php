<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'route',
        'icon',
        'order',
        'is_active',
        'is_submenu',
        'parent_id',
        'submenu_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_submenu' => 'boolean',
    ];

    /**
     * Родительский пункт меню
     */
    public function parent()
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * Дочерние пункты меню
     */
    public function children()
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('order');
    }

    /**
     * Получить все активные пункты меню верхнего уровня
     */
    public static function getMainMenu()
    {
        return self::where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('order')
            ->with('children')
            ->get();
    }

    /**
     * Получить все пункты меню для админки
     */
    public static function getAllForAdmin()
    {
        return self::orderBy('order')
            ->with('children')
            ->get();
    }

    /**
     * Переместить пункт меню вверх
     */
    public function moveUp()
    {
        $previousItem = self::where('order', '<', $this->order)
            ->where('parent_id', $this->parent_id)
            ->orderBy('order', 'desc')
            ->first();

        if ($previousItem) {
            $tempOrder = $this->order;
            $this->order = $previousItem->order;
            $previousItem->order = $tempOrder;
            
            $this->save();
            $previousItem->save();
        }
    }

    /**
     * Переместить пункт меню вниз
     */
    public function moveDown()
    {
        $nextItem = self::where('order', '>', $this->order)
            ->where('parent_id', $this->parent_id)
            ->orderBy('order')
            ->first();

        if ($nextItem) {
            $tempOrder = $this->order;
            $this->order = $nextItem->order;
            $nextItem->order = $tempOrder;
            
            $this->save();
            $nextItem->save();
        }
    }
}
