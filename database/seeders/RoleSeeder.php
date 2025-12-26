<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'Суперадминистратор',
                'description' => 'Полный доступ ко всем функциям системы',
                'priority' => 100,
                'permissions' => ['*']
            ],
            [
                'name' => 'admin',
                'display_name' => 'Администратор',
                'description' => 'Администратор системы с расширенными правами',
                'priority' => 90,
                'permissions' => [
                    'admin.access',
                    'users.view', 'users.create', 'users.edit', 'users.manage_roles',
                    'roles.view', 'roles.create', 'roles.edit',
                    'osvv.view', 'osvv.create', 'osvv.edit', 'osvv.delete', 'osvv.change_status', 'osvv.assign',
                    'analytics.view', 'analytics.export', 'analytics.ai',
                    'acts.view', 'acts.create', 'acts.edit', 'acts.delete'
                ]
            ],
            [
                'name' => 'manager',
                'display_name' => 'Менеджер',
                'description' => 'Управление заявками и координация работы',
                'priority' => 70,
                'permissions' => [
                    'admin.access',
                    'users.view',
                    'osvv.view', 'osvv.create', 'osvv.edit', 'osvv.change_status', 'osvv.assign',
                    'analytics.view', 'analytics.export',
                    'acts.view', 'acts.create', 'acts.edit'
                ]
            ],
            [
                'name' => 'operator',
                'display_name' => 'Оператор',
                'description' => 'Обработка заявок и ведение документации',
                'priority' => 50,
                'permissions' => [
                    'admin.access',
                    'osvv.view', 'osvv.create', 'osvv.edit', 'osvv.change_status',
                    'analytics.view',
                    'acts.view', 'acts.create', 'acts.edit'
                ]
            ],
            [
                'name' => 'specialist',
                'display_name' => 'Специалист',
                'description' => 'Выполнение работ по отлову и стерилизации',
                'priority' => 30,
                'permissions' => [
                    'admin.access',
                    'osvv.view', 'osvv.edit', 'osvv.change_status',
                    'acts.view', 'acts.create'
                ]
            ],
            [
                'name' => 'viewer',
                'display_name' => 'Наблюдатель',
                'description' => 'Только просмотр информации',
                'priority' => 10,
                'permissions' => [
                    'admin.access',
                    'osvv.view',
                    'analytics.view',
                    'acts.view'
                ]
            ]
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
        }
    }
}
