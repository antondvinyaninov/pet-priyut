<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Отображение списка пользователей
     */
    public function index(Request $request)
    {
        $query = User::with(['roles', 'creator']);
        
        // Поиск
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%");
            });
        }
        
        // Фильтр по статусу
        if ($request->filled('status')) {
            $query->where('is_active', $request->get('status') === 'active');
    }

        // Фильтр по роли
        if ($request->filled('role')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('roles.id', $request->get('role'));
            });
        }
        
        $users = $query->orderBy('created_at', 'desc')->paginate(20);
        $roles = Role::active()->orderBy('display_name')->get();
        
        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Форма создания пользователя
     */
    public function create()
    {
        $roles = Role::active()->byPriority()->get();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Сохранение нового пользователя
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'position' => $request->position,
            'bio' => $request->bio,
            'is_active' => $request->boolean('is_active', true),
            'created_by' => Auth::id()
        ]);

        // Назначаем роли
        if ($request->filled('roles')) {
            foreach ($request->roles as $roleId) {
                $role = Role::find($roleId);
                if ($role) {
                    $user->assignRole($role, Auth::user());
                }
            }
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Пользователь успешно создан');
    }

    /**
     * Просмотр пользователя
     */
    public function show(User $user)
    {
        $user->load(['roles', 'creator', 'osvvRequests', 'osvvComments']);
        
        // Статистика пользователя
        $stats = [
            'total_requests' => $user->osvvRequests()->count(),
            'completed_requests' => $user->osvvRequests()->where('status', 'completed')->count(),
            'total_comments' => $user->osvvComments()->count(),
            'last_activity' => $user->osvvComments()->latest()->first()?->created_at ?? $user->last_login_at
        ];
        
        return view('admin.users.show', compact('user', 'stats'));
    }

    /**
     * Форма редактирования пользователя
     */
    public function edit(User $user)
    {
        $roles = Role::active()->byPriority()->get();
        $userRoles = $user->roles->pluck('id')->toArray();
        
        return view('admin.users.edit', compact('user', 'roles', 'userRoles'));
    }

    /**
     * Обновление пользователя
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id'
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'position' => $request->position,
            'bio' => $request->bio,
            'is_active' => $request->boolean('is_active', true)
        ];

        // Обновляем пароль только если он указан
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        // Обновляем роли
        $user->roles()->detach(); // Удаляем все текущие роли
        
        if ($request->filled('roles')) {
            foreach ($request->roles as $roleId) {
                $role = Role::find($roleId);
                if ($role) {
                    $user->assignRole($role, Auth::user());
                }
            }
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Пользователь успешно обновлен');
    }

    /**
     * Удаление пользователя
     */
    public function destroy(User $user)
    {
        // Проверяем, что пользователь не удаляет сам себя
        if ($user->id === Auth::id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Нельзя удалить самого себя');
        }

        // Проверяем, есть ли связанные заявки
        if ($user->osvvRequests()->count() > 0) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Нельзя удалить пользователя с привязанными заявками');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Пользователь успешно удален');
    }

    /**
     * Переключение статуса пользователя
     */
    public function toggleStatus(User $user)
    {
        if ($user->id === Auth::id()) {
            return response()->json(['error' => 'Нельзя изменить статус самого себя'], 400);
        }

        $user->update(['is_active' => !$user->is_active]);

        return response()->json([
            'success' => true,
            'status' => $user->is_active,
            'message' => $user->is_active ? 'Пользователь активирован' : 'Пользователь деактивирован'
        ]);
    }

    /**
     * Управление ролями пользователя
     */
    public function manageRoles(Request $request, User $user)
    {
        $request->validate([
            'action' => 'required|in:assign,remove',
            'role_id' => 'required|exists:roles,id'
        ]);

        $role = Role::find($request->role_id);

        if ($request->action === 'assign') {
            $user->assignRole($role, Auth::user());
            $message = "Роль '{$role->display_name}' назначена пользователю";
        } else {
            $user->removeRole($role);
            $message = "Роль '{$role->display_name}' удалена у пользователя";
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Экспорт пользователей в CSV
     */
    public function export(Request $request)
    {
        $query = User::with('roles');
        
        if ($request->filled('status')) {
            $query->where('is_active', $request->get('status') === 'active');
        }
        
        $users = $query->get();
        
        $filename = 'users_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            
            // Заголовки CSV
            fputcsv($file, [
                'ID',
                'Имя',
                'Email',
                'Телефон',
                'Должность',
                'Роли',
                'Статус',
                'Последний вход',
                'Дата создания'
            ]);
            
            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->phone,
                    $user->position,
                    $user->roles->pluck('display_name')->join(', '),
                    $user->is_active ? 'Активен' : 'Неактивен',
                    $user->last_login_at?->format('d.m.Y H:i'),
                    $user->created_at->format('d.m.Y H:i')
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
