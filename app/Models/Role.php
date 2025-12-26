<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'permissions',
        'is_active',
        'priority'
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean'
    ];

    /**
     * Пользователи с этой ролью
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles')
            ->withPivot(['assigned_at', 'assigned_by'])
            ->withTimestamps();
    }

    /**
     * Проверить, есть ли у роли определенное разрешение
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $permissions = $this->permissions ?? [];
        return in_array($permission, $permissions) || in_array('*', $permissions);
    }

    /**
     * Добавить разрешение к роли
     */
    public function addPermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->permissions = $permissions;
            $this->save();
        }
    }

    /**
     * Удалить разрешение из роли
     */
    public function removePermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        $permissions = array_filter($permissions, fn($p) => $p !== $permission);
        $this->permissions = array_values($permissions);
        $this->save();
    }

    /**
     * Получить все доступные разрешения в системе
     */
    public static function getAvailablePermissions(): array
    {
        return [
            // Управление пользователями
            'users.view' => 'Просмотр пользователей',
            'users.create' => 'Создание пользователей',
            'users.edit' => 'Редактирование пользователей',
            'users.delete' => 'Удаление пользователей',
            'users.manage_roles' => 'Управление ролями пользователей',
            
            // Управление ролями
            'roles.view' => 'Просмотр ролей',
            'roles.create' => 'Создание ролей',
            'roles.edit' => 'Редактирование ролей',
            'roles.delete' => 'Удаление ролей',
            
            // Заявки ОСВВ
            'osvv.view' => 'Просмотр заявок ОСВВ',
            'osvv.create' => 'Создание заявок ОСВВ',
            'osvv.edit' => 'Редактирование заявок ОСВВ',
            'osvv.delete' => 'Удаление заявок ОСВВ',
            'osvv.change_status' => 'Изменение статуса заявок',
            'osvv.assign' => 'Назначение заявок',
            
            // Аналитика
            'analytics.view' => 'Просмотр аналитики',
            'analytics.export' => 'Экспорт аналитики',
            'analytics.ai' => 'Доступ к AI-аналитике',
            
            // Акты отлова
            'acts.view' => 'Просмотр актов отлова',
            'acts.create' => 'Создание актов отлова',
            'acts.edit' => 'Редактирование актов отлова',
            'acts.delete' => 'Удаление актов отлова',
            
            // Системные разрешения
            'admin.access' => 'Доступ к админ-панели',
            'system.settings' => 'Настройки системы',
            '*' => 'Все разрешения (суперадмин)'
        ];
    }

    /**
     * Scope для активных ролей
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope для сортировки по приоритету
     */
    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'desc');
    }
}
