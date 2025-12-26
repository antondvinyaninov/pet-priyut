<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AnimalStage;
use App\Models\StageTask;

class AnimalManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем этапы
        $stages = [
            [
                'name' => 'Карантин',
                'slug' => 'quarantine',
                'description' => '10-дневный карантин для новых животных',
                'color' => '#EF4444', // красный
                'order' => 1,
                'duration_days' => 10,
                'is_final' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Медицинская обработка',
                'slug' => 'medical_treatment',
                'description' => 'Стерилизация, биркование, чипирование',
                'color' => '#F59E0B', // оранжевый
                'order' => 2,
                'duration_days' => 7,
                'is_final' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Вакцинация',
                'slug' => 'vaccination',
                'description' => 'Прививки и медицинский осмотр',
                'color' => '#3B82F6', // синий
                'order' => 3,
                'duration_days' => 5,
                'is_final' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Готов к выпуску',
                'slug' => 'ready_for_release',
                'description' => 'Животное готово к принятию решения о дальнейшей судьбе',
                'color' => '#10B981', // зеленый
                'order' => 4,
                'duration_days' => null,
                'is_final' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Выпущен в среду',
                'slug' => 'released',
                'description' => 'Животное возвращено в естественную среду обитания',
                'color' => '#059669', // темно-зеленый
                'order' => 5,
                'duration_days' => null,
                'is_final' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Остается в приюте',
                'slug' => 'shelter',
                'description' => 'Животное остается на попечении приюта',
                'color' => '#8B5CF6', // фиолетовый
                'order' => 6,
                'duration_days' => null,
                'is_final' => true,
                'is_active' => true,
            ],
        ];

        foreach ($stages as $stageData) {
            $stage = AnimalStage::create($stageData);

            // Создаем задачи для каждого этапа
            $this->createTasksForStage($stage);
        }
    }

    private function createTasksForStage(AnimalStage $stage): void
    {
        $tasks = [];

        switch ($stage->slug) {
            case 'quarantine':
                $tasks = [
                    [
                        'name' => 'Первичный ветеринарный осмотр',
                        'description' => 'Общий осмотр состояния здоровья животного',
                        'order' => 1,
                        'is_required' => true,
                    ],
                    [
                        'name' => 'Анализ крови',
                        'description' => 'Общий и биохимический анализ крови',
                        'order' => 2,
                        'is_required' => true,
                    ],
                    [
                        'name' => 'Анализ кала на паразитов',
                        'description' => 'Проверка на наличие гельминтов и простейших',
                        'order' => 3,
                        'is_required' => true,
                    ],
                    [
                        'name' => 'Обработка от внешних паразитов',
                        'description' => 'Обработка от блох, клещей и других паразитов',
                        'order' => 4,
                        'is_required' => true,
                    ],
                    [
                        'name' => 'Дегельминтизация',
                        'description' => 'Обработка от внутренних паразитов',
                        'order' => 5,
                        'is_required' => true,
                    ],
                    [
                        'name' => '10 дней наблюдения',
                        'description' => 'Наблюдение за состоянием здоровья в течение 10 дней',
                        'order' => 6,
                        'is_required' => true,
                    ],
                ];
                break;

            case 'medical_treatment':
                $tasks = [
                    [
                        'name' => 'Стерилизация/кастрация',
                        'description' => 'Хирургическая стерилизация животного',
                        'order' => 1,
                        'is_required' => true,
                    ],
                    [
                        'name' => 'Установка ушной бирки',
                        'description' => 'Установка идентификационной бирки',
                        'order' => 2,
                        'is_required' => true,
                    ],
                    [
                        'name' => 'Чипирование',
                        'description' => 'Имплантация микрочипа для идентификации',
                        'order' => 3,
                        'is_required' => true,
                    ],
                    [
                        'name' => 'Послеоперационный уход',
                        'description' => 'Наблюдение и уход после операции',
                        'order' => 4,
                        'is_required' => true,
                    ],
                    [
                        'name' => 'Снятие швов',
                        'description' => 'Снятие послеоперационных швов',
                        'order' => 5,
                        'is_required' => false,
                    ],
                ];
                break;

            case 'vaccination':
                $tasks = [
                    [
                        'name' => 'Первичная вакцинация',
                        'description' => 'Комплексная вакцинация против основных заболеваний',
                        'order' => 1,
                        'is_required' => true,
                    ],
                    [
                        'name' => 'Вакцинация от бешенства',
                        'description' => 'Обязательная прививка от бешенства',
                        'order' => 2,
                        'is_required' => true,
                    ],
                    [
                        'name' => 'Ревакцинация (при необходимости)',
                        'description' => 'Повторная вакцинация через 2-4 недели',
                        'order' => 3,
                        'is_required' => false,
                    ],
                    [
                        'name' => 'Оформление ветеринарного паспорта',
                        'description' => 'Создание документа с отметками о прививках',
                        'order' => 4,
                        'is_required' => true,
                    ],
                    [
                        'name' => 'Финальный медосмотр',
                        'description' => 'Итоговый осмотр перед переводом на следующий этап',
                        'order' => 5,
                        'is_required' => true,
                    ],
                ];
                break;

            case 'ready_for_release':
                $tasks = [
                    [
                        'name' => 'Оценка поведения',
                        'description' => 'Оценка агрессивности и социализации животного',
                        'order' => 1,
                        'is_required' => true,
                    ],
                    [
                        'name' => 'Проверка здоровья',
                        'description' => 'Финальная проверка состояния здоровья',
                        'order' => 2,
                        'is_required' => true,
                    ],
                    [
                        'name' => 'Принятие решения',
                        'description' => 'Решение о выпуске или оставлении в приюте',
                        'order' => 3,
                        'is_required' => true,
                    ],
                ];
                break;

            case 'released':
                $tasks = [
                    [
                        'name' => 'Подготовка к выпуску',
                        'description' => 'Финальная подготовка животного к выпуску',
                        'order' => 1,
                        'is_required' => true,
                    ],
                    [
                        'name' => 'Выпуск в среду обитания',
                        'description' => 'Возвращение животного в естественную среду',
                        'order' => 2,
                        'is_required' => true,
                    ],
                    [
                        'name' => 'Оформление документов',
                        'description' => 'Заполнение отчетности о выпуске',
                        'order' => 3,
                        'is_required' => true,
                    ],
                ];
                break;

            case 'shelter':
                $tasks = [
                    [
                        'name' => 'Размещение в приюте',
                        'description' => 'Определение постоянного места содержания',
                        'order' => 1,
                        'is_required' => true,
                    ],
                    [
                        'name' => 'Создание карточки приюта',
                        'description' => 'Оформление документов для постоянного содержания',
                        'order' => 2,
                        'is_required' => true,
                    ],
                    [
                        'name' => 'Поиск новых хозяев',
                        'description' => 'Размещение информации для поиска новой семьи',
                        'order' => 3,
                        'is_required' => false,
                    ],
                ];
                break;
        }

        foreach ($tasks as $index => $taskData) {
            StageTask::create([
                'stage_id' => $stage->id,
                'name' => $taskData['name'],
                'description' => $taskData['description'],
                'order' => $taskData['order'],
                'is_required' => $taskData['is_required'],
                'is_active' => true,
            ]);
        }
    }
}
