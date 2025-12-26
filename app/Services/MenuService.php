<?php

namespace App\Services;

use App\Models\MenuItem;
use Illuminate\Support\Facades\Cache;

class MenuService
{
    /**
     * Получить меню для отображения
     */
    public static function getMenu()
    {
        return Cache::remember('admin_menu', 300, function () {
            return MenuItem::getMainMenu();
        });
    }

    /**
     * Очистить кэш меню
     */
    public static function clearCache()
    {
        Cache::forget('admin_menu');
    }

    /**
     * Получить активный пункт меню
     */
    public static function getActiveMenuItem($currentRoute)
    {
        $menuItems = self::getMenu();
        
        foreach ($menuItems as $item) {
            if ($item->route === $currentRoute) {
                return $item;
            }
            
            foreach ($item->children as $child) {
                if ($child->route === $currentRoute) {
                    return $child;
                }
            }
        }
        
        return null;
    }
} 