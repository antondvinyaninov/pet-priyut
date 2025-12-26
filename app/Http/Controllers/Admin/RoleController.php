<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    /**
     * Отображение списка ролей
     */
    public function index(Request $request)
    {
        $query = Role::withCount('users');
        
        // Поиск
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('display_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Фильтр по статусу
        if ($request->filled('status')) {
            $query->where('is_active', $request->get('status') === 'active');
        }
        
        $roles = $query->byPriority()->paginate(20);
        
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Форма создания роли
     */
    public function create()
    {
        $availablePermissions = Role::getAvailablePermissions();
        return view('admin.roles.create', compact('availablePermissions'));
    }

    /**
     * Сохранение новой роли
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles|regex:/^[a-z_]+$/',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'priority' => 'required|integer|min:0|max:100',
            'is_active' => 'boolean',
            'permissions' => 'array',
            'permissions.*' => 'string'
        ], [
            'name.regex' => 'Название роли должно содержать только строчные буквы и подчеркивания'
        ]);

        // Проверяем, что разрешения существуют
        $availablePermissions = array_keys(Role::getAvailablePermissions());
        $permissions = $request->get('permissions', []);
        $validPermissions = array_intersect($permissions, $availablePermissions);

        Role::create([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
            'priority' => $request->priority,
            'is_active' => $request->boolean('is_active', true),
            'permissions' => $validPermissions
        ]);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Роль успешно создана');
    }

    /**
     * Просмотр роли
     */
    public function show(Role $role)
    {
        $role->load('users');
        $availablePermissions = Role::getAvailablePermissions();
        
        // Статистика роли
        $stats = [
            'total_users' => $role->users()->count(),
            'active_users' => $role->users()->where('is_active', true)->count(),
            'permissions_count' => count($role->permissions ?? [])
        ];
        
        return view('admin.roles.show', compact('role', 'availablePermissions', 'stats'));
    }

    /**
     * Форма редактирования роли
     */
    public function edit(Role $role)
    {
        $availablePermissions = Role::getAvailablePermissions();
        return view('admin.roles.edit', compact('role', 'availablePermissions'));
    }

    /**
     * Обновление роли
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-z_]+$/', Rule::unique('roles')->ignore($role->id)],
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'priority' => 'required|integer|min:0|max:100',
            'is_active' => 'boolean',
            'permissions' => 'array',
            'permissions.*' => 'string'
        ], [
            'name.regex' => 'Название роли должно содержать только строчные буквы и подчеркивания'
        ]);

        // Проверяем, что разрешения существуют
        $availablePermissions = array_keys(Role::getAvailablePermissions());
        $permissions = $request->get('permissions', []);
        $validPermissions = array_intersect($permissions, $availablePermissions);

        $role->update([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
            'priority' => $request->priority,
            'is_active' => $request->boolean('is_active', true),
            'permissions' => $validPermissions
        ]);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Роль успешно обновлена');
    }

    /**
     * Удаление роли
     */
    public function destroy(Role $role)
    {
        // Проверяем, есть ли пользователи с этой ролью
        if ($role->users()->count() > 0) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Нельзя удалить роль, назначенную пользователям');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Роль успешно удалена');
    }

    /**
     * Переключение статуса роли
     */
    public function toggleStatus(Role $role)
    {
        $role->update(['is_active' => !$role->is_active]);

        return response()->json([
            'success' => true,
            'status' => $role->is_active,
            'message' => $role->is_active ? 'Роль активирована' : 'Роль деактивирована'
        ]);
    }

    /**
     * Управление разрешениями роли
     */
    public function managePermissions(Request $request, Role $role)
    {
        $request->validate([
            'action' => 'required|in:add,remove',
            'permission' => 'required|string'
        ]);

        $availablePermissions = array_keys(Role::getAvailablePermissions());
        
        if (!in_array($request->permission, $availablePermissions)) {
            return response()->json(['error' => 'Недопустимое разрешение'], 400);
        }

        if ($request->action === 'add') {
            $role->addPermission($request->permission);
            $message = "Разрешение '{$request->permission}' добавлено к роли";
        } else {
            $role->removePermission($request->permission);
            $message = "Разрешение '{$request->permission}' удалено из роли";
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Копирование роли
     */
    public function duplicate(Role $role)
    {
        $newRole = Role::create([
            'name' => $role->name . '_copy_' . time(),
            'display_name' => $role->display_name . ' (Копия)',
            'description' => $role->description,
            'priority' => $role->priority,
            'is_active' => false, // Новая роль неактивна по умолчанию
            'permissions' => $role->permissions
        ]);

        return redirect()->route('admin.roles.edit', $newRole)
            ->with('success', 'Роль успешно скопирована. Отредактируйте её перед активацией.');
    }

    /**
     * Назначение роли пользователям
     */
    public function assignToUsers(Request $request, Role $role)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        $assignedCount = 0;
        
        foreach ($request->user_ids as $userId) {
            $user = User::find($userId);
            if ($user && !$user->hasRole($role->name)) {
                $user->assignRole($role);
                $assignedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Роль назначена {$assignedCount} пользователям"
        ]);
    }

    /**
     * Экспорт ролей в CSV
     */
    public function export(Request $request)
    {
        $query = Role::withCount('users');
        
        if ($request->filled('status')) {
            $query->where('is_active', $request->get('status') === 'active');
        }
        
        $roles = $query->byPriority()->get();
        
        $filename = 'roles_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($roles) {
            $file = fopen('php://output', 'w');
            
            // Заголовки CSV
            fputcsv($file, [
                'ID',
                'Название',
                'Отображаемое название',
                'Описание',
                'Приоритет',
                'Количество пользователей',
                'Количество разрешений',
                'Статус',
                'Дата создания'
            ]);
            
            foreach ($roles as $role) {
                fputcsv($file, [
                    $role->id,
                    $role->name,
                    $role->display_name,
                    $role->description,
                    $role->priority,
                    $role->users_count,
                    count($role->permissions ?? []),
                    $role->is_active ? 'Активна' : 'Неактивна',
                    $role->created_at->format('d.m.Y H:i')
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
