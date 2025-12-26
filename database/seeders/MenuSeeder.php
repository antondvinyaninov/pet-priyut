<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Очищаем таблицу
        MenuItem::truncate();

        // Создаем основные пункты меню
        $menuItems = [
            [
                'title' => 'Главная',
                'route' => 'admin.dashboard',
                'icon' => '🏠',
                'order' => 1,
                'is_active' => true,
                'is_submenu' => false,
            ],
            [
                'title' => 'Журнал заявок',
                'route' => 'admin.osvv.index',
                'icon' => '📋',
                'order' => 2,
                'is_active' => true,
                'is_submenu' => false,
            ],
            [
                'title' => 'ОСВВ',
                'route' => 'admin.animals.index',
                'icon' => '🐾',
                'order' => 3,
                'is_active' => true,
                'is_submenu' => false,
            ],
            [
                'title' => 'Аналитика',
                'route' => 'admin.analytics.index',
                'icon' => '📊',
                'order' => 4,
                'is_active' => true,
                'is_submenu' => false,
            ],
            [
                'title' => 'Планировщик выездов',
                'route' => 'admin.departure-planner.index',
                'icon' => '📅',
                'order' => 5,
                'is_active' => true,
                'is_submenu' => false,
            ],
            [
                'title' => 'Сотрудники',
                'route' => 'admin.employees.index',
                'icon' => '👨‍💼',
                'order' => 6,
                'is_active' => true,
                'is_submenu' => false,
            ],
            [
                'title' => 'Ветеринария',
                'route' => 'admin.veterinary.index',
                'icon' => '🏥',
                'order' => 7,
                'is_active' => true,
                'is_submenu' => false,
            ],
            [
                'title' => 'Склад',
                'route' => 'admin.warehouse.index',
                'icon' => '📦',
                'order' => 8,
                'is_active' => true,
                'is_submenu' => false,
            ],
            [
                'title' => 'Отчетность',
                'route' => 'admin.reports.index',
                'icon' => '📊',
                'order' => 9,
                'is_active' => true,
                'is_submenu' => false,
            ],
        ];

        // Создаем основные пункты
        foreach ($menuItems as $item) {
            MenuItem::create($item);
        }

        // Создаем подменю "Задачи"
        $tasksMenu = MenuItem::create([
            'title' => 'Задачи',
            'route' => null,
            'icon' => '📋',
            'order' => 10,
            'is_active' => true,
            'is_submenu' => true,
            'submenu_id' => 'tasksSubmenu',
        ]);

        // Дочерние пункты для "Задачи"
        $tasksChildren = [
            [
                'title' => 'Все задачи',
                'route' => 'admin.tasks.index',
                'icon' => '📝',
                'order' => 1,
            ],
            [
                'title' => 'Мои задачи',
                'route' => 'admin.tasks.my-tasks',
                'icon' => '👤',
                'order' => 2,
            ],
            [
                'title' => 'Создать задачу',
                'route' => 'admin.tasks.create',
                'icon' => '➕',
                'order' => 3,
            ],
        ];

        foreach ($tasksChildren as $child) {
            MenuItem::create(array_merge($child, [
                'parent_id' => $tasksMenu->id,
                'is_active' => true,
                'is_submenu' => false,
            ]));
        }

        // Создаем подменю "Учет животных"
        $animalRegistryMenu = MenuItem::create([
            'title' => 'Учет животных',
            'route' => null,
            'icon' => '🐕',
            'order' => 11,
            'is_active' => true,
            'is_submenu' => true,
            'submenu_id' => 'animalRegistrySubmenu',
        ]);

        // Дочерние пункты для "Учет животных"
        $animalRegistryChildren = [
            [
                'title' => 'По ОСВВ',
                'route' => 'admin.animal-registry.osvv',
                'icon' => '🏞️',
                'order' => 1,
            ],
            [
                'title' => 'В приюте',
                'route' => 'admin.animal-registry.shelter',
                'icon' => '🏠',
                'order' => 2,
            ],
            [
                'title' => 'Акты приема-передачи',
                'route' => 'admin.animal-transfer-acts.index',
                'icon' => '📋',
                'order' => 3,
            ],
        ];

        foreach ($animalRegistryChildren as $child) {
            MenuItem::create(array_merge($child, [
                'parent_id' => $animalRegistryMenu->id,
                'is_active' => true,
                'is_submenu' => false,
            ]));
        }

        // Создаем подменю "Управление"
        $managementMenu = MenuItem::create([
            'title' => 'Управление',
            'route' => null,
            'icon' => '⚙️',
            'order' => 12,
            'is_active' => true,
            'is_submenu' => true,
            'submenu_id' => 'usersSubmenu',
        ]);

        // Дочерние пункты для "Управление"
        $managementChildren = [
            [
                'title' => 'Пользователи',
                'route' => 'admin.users.index',
                'icon' => '👥',
                'order' => 1,
            ],
            [
                'title' => 'Роли и права',
                'route' => 'admin.roles.index',
                'icon' => '🔐',
                'order' => 2,
            ],
        ];

        foreach ($managementChildren as $child) {
            MenuItem::create(array_merge($child, [
                'parent_id' => $managementMenu->id,
                'is_active' => true,
                'is_submenu' => false,
            ]));
        }

        // Создаем пункты профиля и выхода
        MenuItem::create([
            'title' => 'Профиль',
            'route' => 'profile.edit',
            'icon' => '👤',
            'order' => 13,
            'is_active' => true,
            'is_submenu' => false,
        ]);

        MenuItem::create([
            'title' => 'Выйти',
            'route' => 'logout',
            'icon' => '🚪',
            'order' => 14,
            'is_active' => true,
            'is_submenu' => false,
        ]);
    }
}
