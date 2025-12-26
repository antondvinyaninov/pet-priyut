<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'position',
        'bio',
        'is_active',
        'created_by'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime'
        ];
    }
    
    /**
     * Получить заявки ОСВВ, закрепленные за пользователем
     */
    public function osvvRequests(): HasMany
    {
        return $this->hasMany(OsvvRequest::class);
    }
    
    /**
     * Получить комментарии пользователя к заявкам ОСВВ
     */
    public function osvvComments(): HasMany
    {
        return $this->hasMany(OsvvComment::class);
    }
    
    /**
     * Роли пользователя
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withPivot(['assigned_at', 'assigned_by'])
            ->withTimestamps();
    }
    
    /**
     * Кто создал пользователя
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    /**
     * Пользователи, созданные этим пользователем
     */
    public function createdUsers(): HasMany
    {
        return $this->hasMany(User::class, 'created_by');
    }
    
    /**
     * Связанный сотрудник
     */
    public function employee()
    {
        return $this->hasOne(Employee::class);
    }
    
    /**
     * Проверить, есть ли у пользователя определенная роль
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->where('is_active', true)->exists();
    }
    
    /**
     * Проверить, есть ли у пользователя определенное разрешение
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->is_active) {
            return false;
        }
        
        return $this->roles()->active()->get()->some(function ($role) use ($permission) {
            return $role->hasPermission($permission);
        });
    }
    
    /**
     * Назначить роль пользователю
     */
    public function assignRole(Role $role, ?User $assignedBy = null): void
    {
        if (!$this->hasRole($role->name)) {
            $this->roles()->attach($role->id, [
                'assigned_at' => now(),
                'assigned_by' => $assignedBy?->id
            ]);
        }
    }
    
    /**
     * Удалить роль у пользователя
     */
    public function removeRole(Role $role): void
    {
        $this->roles()->detach($role->id);
    }
    
    /**
     * Получить все разрешения пользователя
     */
    public function getAllPermissions(): array
    {
        $permissions = [];
        
        foreach ($this->roles()->active()->get() as $role) {
            $permissions = array_merge($permissions, $role->permissions ?? []);
        }
        
        return array_unique($permissions);
    }
    
    /**
     * Scope для активных пользователей
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    /**
     * Обновить время последнего входа
     */
    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }
}
