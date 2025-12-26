<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Services\MenuService;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Показать список пунктов меню
     */
    public function index()
    {
        $menuItems = MenuItem::getAllForAdmin();
        
        return view('admin.menu.index', compact('menuItems'));
    }

    /**
     * Показать форму создания пункта меню
     */
    public function create()
    {
        $parentItems = MenuItem::where('is_submenu', true)->get();
        
        return view('admin.menu.create', compact('parentItems'));
    }

    /**
     * Сохранить новый пункт меню
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'route' => 'nullable|string|max:255',
            'icon' => 'required|string|max:10',
            'is_active' => 'boolean',
            'is_submenu' => 'boolean',
            'parent_id' => 'nullable|exists:menu_items,id',
            'submenu_id' => 'nullable|string|max:255',
        ]);

        // Определяем порядок
        $maxOrder = MenuItem::where('parent_id', $validated['parent_id'] ?? null)
            ->max('order') ?? 0;
        $validated['order'] = $maxOrder + 1;

        MenuItem::create($validated);

        // Очищаем кэш меню
        MenuService::clearCache();

        return redirect()->route('admin.menu.index')
            ->with('success', 'Пункт меню успешно создан');
    }

    /**
     * Показать форму редактирования
     */
    public function edit(MenuItem $menuItem)
    {
        $parentItems = MenuItem::where('is_submenu', true)
            ->where('id', '!=', $menuItem->id)
            ->get();
        
        return view('admin.menu.edit', compact('menuItem', 'parentItems'));
    }

    /**
     * Обновить пункт меню
     */
    public function update(Request $request, MenuItem $menuItem)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'route' => 'nullable|string|max:255',
            'icon' => 'required|string|max:10',
            'is_active' => 'boolean',
            'is_submenu' => 'boolean',
            'parent_id' => 'nullable|exists:menu_items,id',
            'submenu_id' => 'nullable|string|max:255',
        ]);

        $menuItem->update($validated);

        // Очищаем кэш меню
        MenuService::clearCache();

        return redirect()->route('admin.menu.index')
            ->with('success', 'Пункт меню успешно обновлен');
    }

    /**
     * Удалить пункт меню
     */
    public function destroy(MenuItem $menuItem)
    {
        $menuItem->delete();

        // Очищаем кэш меню
        MenuService::clearCache();

        return redirect()->route('admin.menu.index')
            ->with('success', 'Пункт меню успешно удален');
    }

    /**
     * Переместить пункт меню вверх
     */
    public function moveUp(MenuItem $menuItem)
    {
        $menuItem->moveUp();

        // Очищаем кэш меню
        MenuService::clearCache();

        return redirect()->route('admin.menu.index')
            ->with('success', 'Порядок изменен');
    }

    /**
     * Переместить пункт меню вниз
     */
    public function moveDown(MenuItem $menuItem)
    {
        $menuItem->moveDown();

        // Очищаем кэш меню
        MenuService::clearCache();

        return redirect()->route('admin.menu.index')
            ->with('success', 'Порядок изменен');
    }

    /**
     * Переключить активность пункта меню
     */
    public function toggleActive(MenuItem $menuItem)
    {
        $menuItem->update(['is_active' => !$menuItem->is_active]);

        // Очищаем кэш меню
        MenuService::clearCache();

        return redirect()->route('admin.menu.index')
            ->with('success', 'Статус изменен');
    }
}
