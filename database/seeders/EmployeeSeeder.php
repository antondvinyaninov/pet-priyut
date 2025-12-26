<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\User;
use App\Models\WorkSchedule;
use App\Models\TimeTracking;
use Carbon\Carbon;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем директора
        $director = Employee::create([
            'employee_number' => 'DIR001',
            'first_name' => 'Александр',
            'last_name' => 'Петров',
            'middle_name' => 'Иванович',
            'position' => 'Директор',
            'department' => 'Руководство',
            'hire_date' => '2020-01-15',
            'employment_type' => 'full_time',
            'salary' => 150000,
            'phone' => '+7 (999) 123-45-67',
            'email' => 'director@osvv.ru',
            'is_active' => true
        ]);

        // Создаем заместителя директора
        $deputyDirector = Employee::create([
            'employee_number' => 'DEP001',
            'first_name' => 'Мария',
            'last_name' => 'Сидорова',
            'middle_name' => 'Петровна',
            'position' => 'Заместитель директора',
            'department' => 'Руководство',
            'supervisor_id' => $director->id,
            'hire_date' => '2020-03-01',
            'employment_type' => 'full_time',
            'salary' => 120000,
            'phone' => '+7 (999) 234-56-78',
            'email' => 'deputy@osvv.ru',
            'is_active' => true
        ]);

        // Создаем начальника отдела ОСВВ
        $osvvHead = Employee::create([
            'employee_number' => 'OSV001',
            'first_name' => 'Сергей',
            'last_name' => 'Иванов',
            'middle_name' => 'Александрович',
            'position' => 'Начальник отдела ОСВВ',
            'department' => 'Отдел ОСВВ',
            'supervisor_id' => $deputyDirector->id,
            'hire_date' => '2020-06-15',
            'employment_type' => 'full_time',
            'salary' => 80000,
            'phone' => '+7 (999) 345-67-89',
            'email' => 'osvv.head@osvv.ru',
            'is_active' => true
        ]);

        // Создаем специалистов отдела ОСВВ
        $specialists = [
            [
                'employee_number' => 'OSV002',
                'first_name' => 'Анна',
                'last_name' => 'Козлова',
                'middle_name' => 'Сергеевна',
                'position' => 'Ветеринарный врач',
                'phone' => '+7 (999) 456-78-90',
                'email' => 'vet1@osvv.ru',
            ],
            [
                'employee_number' => 'OSV003',
                'first_name' => 'Дмитрий',
                'last_name' => 'Морозов',
                'middle_name' => 'Владимирович',
                'position' => 'Специалист по отлову',
                'phone' => '+7 (999) 567-89-01',
                'email' => 'catch1@osvv.ru',
            ],
            [
                'employee_number' => 'OSV004',
                'first_name' => 'Елена',
                'last_name' => 'Волкова',
                'middle_name' => 'Николаевна',
                'position' => 'Ветеринарный врач',
                'phone' => '+7 (999) 678-90-12',
                'email' => 'vet2@osvv.ru',
            ],
            [
                'employee_number' => 'OSV005',
                'first_name' => 'Михаил',
                'last_name' => 'Лебедев',
                'middle_name' => 'Андреевич',
                'position' => 'Водитель-специалист',
                'phone' => '+7 (999) 789-01-23',
                'email' => 'driver1@osvv.ru',
            ]
        ];

        foreach ($specialists as $specialistData) {
            $specialist = Employee::create(array_merge($specialistData, [
                'department' => 'Отдел ОСВВ',
                'supervisor_id' => $osvvHead->id,
                'hire_date' => Carbon::now()->subMonths(rand(6, 24)),
                'employment_type' => 'full_time',
                'salary' => rand(45000, 65000),
                'is_active' => true
            ]));

            // Создаем стандартный график работы для каждого специалиста
            WorkSchedule::createStandardSchedule($specialist->id);
        }

        // Создаем административный персонал
        $adminHead = Employee::create([
            'employee_number' => 'ADM001',
            'first_name' => 'Ольга',
            'last_name' => 'Смирнова',
            'middle_name' => 'Викторовна',
            'position' => 'Начальник административного отдела',
            'department' => 'Административный отдел',
            'supervisor_id' => $deputyDirector->id,
            'hire_date' => '2020-09-01',
            'employment_type' => 'full_time',
            'salary' => 70000,
            'phone' => '+7 (999) 890-12-34',
            'email' => 'admin.head@osvv.ru',
            'is_active' => true
        ]);

        $adminStaff = [
            [
                'employee_number' => 'ADM002',
                'first_name' => 'Татьяна',
                'last_name' => 'Новикова',
                'middle_name' => 'Алексеевна',
                'position' => 'Секретарь',
                'phone' => '+7 (999) 901-23-45',
                'email' => 'secretary@osvv.ru',
            ],
            [
                'employee_number' => 'ADM003',
                'first_name' => 'Игорь',
                'last_name' => 'Федоров',
                'middle_name' => 'Сергеевич',
                'position' => 'Бухгалтер',
                'phone' => '+7 (999) 012-34-56',
                'email' => 'accountant@osvv.ru',
            ],
            [
                'employee_number' => 'ADM004',
                'first_name' => 'Наталья',
                'last_name' => 'Орлова',
                'middle_name' => 'Дмитриевна',
                'position' => 'Специалист по кадрам',
                'phone' => '+7 (999) 123-45-67',
                'email' => 'hr@osvv.ru',
            ]
        ];

        foreach ($adminStaff as $staffData) {
            $staff = Employee::create(array_merge($staffData, [
                'department' => 'Административный отдел',
                'supervisor_id' => $adminHead->id,
                'hire_date' => Carbon::now()->subMonths(rand(12, 36)),
                'employment_type' => 'full_time',
                'salary' => rand(35000, 55000),
                'is_active' => true
            ]));

            // Создаем стандартный график работы
            WorkSchedule::createStandardSchedule($staff->id);
        }

        // Создаем стажеров и временных сотрудников
        $temporaryStaff = [
            [
                'employee_number' => 'TMP001',
                'first_name' => 'Алексей',
                'last_name' => 'Зайцев',
                'middle_name' => 'Романович',
                'position' => 'Стажер ветеринарного врача',
                'department' => 'Отдел ОСВВ',
                'supervisor_id' => $osvvHead->id,
                'employment_type' => 'intern',
                'salary' => 25000,
            ],
            [
                'employee_number' => 'TMP002',
                'first_name' => 'Юлия',
                'last_name' => 'Белова',
                'middle_name' => 'Игоревна',
                'position' => 'Временный специалист',
                'department' => 'Административный отдел',
                'supervisor_id' => $adminHead->id,
                'employment_type' => 'temporary',
                'salary' => 30000,
            ]
        ];

        foreach ($temporaryStaff as $tempData) {
            $temp = Employee::create(array_merge($tempData, [
                'hire_date' => Carbon::now()->subMonths(rand(1, 6)),
                'phone' => '+7 (999) ' . rand(100, 999) . '-' . rand(10, 99) . '-' . rand(10, 99),
                'is_active' => true
            ]));

            // Создаем неполный график работы для стажеров
            if ($temp->employment_type === 'intern') {
                WorkSchedule::create([
                    'employee_id' => $temp->id,
                    'name' => 'График стажера (неполный день)',
                    'type' => 'part_time',
                    'start_date' => $temp->hire_date,
                    'monday_start' => '09:00',
                    'monday_end' => '14:00',
                    'tuesday_start' => '09:00',
                    'tuesday_end' => '14:00',
                    'wednesday_start' => '09:00',
                    'wednesday_end' => '14:00',
                    'thursday_start' => '09:00',
                    'thursday_end' => '14:00',
                    'friday_start' => '09:00',
                    'friday_end' => '14:00',
                    'lunch_start' => '12:00',
                    'lunch_end' => '13:00',
                    'hours_per_week' => 20,
                    'hours_per_day' => 4,
                    'is_active' => true
                ]);
            } else {
                WorkSchedule::createStandardSchedule($temp->id);
            }
        }

        // Создаем записи учета времени за последний месяц для некоторых сотрудников
        $activeEmployees = Employee::where('is_active', true)->get();
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now();

        foreach ($activeEmployees->take(5) as $employee) {
            $current = $startDate->copy();
            
            while ($current <= $endDate) {
                // Пропускаем выходные для стандартного графика
                if (!$current->isWeekend()) {
                    $schedule = $employee->activeWorkSchedule();
                    
                    if ($schedule) {
                        $workTime = $schedule->getWorkTimeForDate($current);
                        
                        if ($workTime && ($workTime['is_working_day'] ?? false)) {
                            // Случайно определяем, был ли сотрудник на работе
                            $wasPresent = rand(1, 100) <= 90; // 90% вероятность присутствия
                            
                            if ($wasPresent) {
                                $clockIn = Carbon::parse($current->format('Y-m-d') . ' ' . $workTime['start'])
                                    ->addMinutes(rand(-10, 30)); // Может прийти на 10 минут раньше или на 30 минут позже
                                
                                $clockOut = Carbon::parse($current->format('Y-m-d') . ' ' . $workTime['end'])
                                    ->addMinutes(rand(-30, 60)); // Может уйти на 30 минут раньше или на час позже
                                
                                TimeTracking::create([
                                    'employee_id' => $employee->id,
                                    'work_date' => $current->format('Y-m-d'),
                                    'clock_in' => $clockIn->format('H:i:s'),
                                    'clock_out' => $clockOut->format('H:i:s'),
                                    'lunch_start' => $schedule->lunch_start,
                                    'lunch_end' => $schedule->lunch_end,
                                    'status' => 'present',
                                    'is_approved' => rand(1, 100) <= 80 // 80% записей подтверждены
                                ]);
                            } else {
                                // Случайно выбираем причину отсутствия
                                $absenceReasons = ['sick', 'vacation', 'business_trip'];
                                $reason = $absenceReasons[array_rand($absenceReasons)];
                                
                                TimeTracking::create([
                                    'employee_id' => $employee->id,
                                    'work_date' => $current->format('Y-m-d'),
                                    'status' => $reason,
                                    'absence_reason' => $reason === 'sick' ? 'Больничный лист' : 
                                                      ($reason === 'vacation' ? 'Отпуск' : 'Командировка'),
                                    'is_approved' => true
                                ]);
                            }
                        }
                    }
                }
                
                $current->addDay();
            }
        }

        // Создаем графики работы для руководителей
        foreach ([$director, $deputyDirector, $osvvHead, $adminHead] as $manager) {
            WorkSchedule::create([
                'employee_id' => $manager->id,
                'name' => 'Руководящий график',
                'type' => 'flexible',
                'start_date' => $manager->hire_date,
                'monday_start' => '08:00',
                'monday_end' => '19:00',
                'tuesday_start' => '08:00',
                'tuesday_end' => '19:00',
                'wednesday_start' => '08:00',
                'wednesday_end' => '19:00',
                'thursday_start' => '08:00',
                'thursday_end' => '19:00',
                'friday_start' => '08:00',
                'friday_end' => '18:00',
                'lunch_start' => '13:00',
                'lunch_end' => '14:00',
                'hours_per_week' => 45,
                'hours_per_day' => 9,
                'is_active' => true
            ]);
        }

        $this->command->info('Создано ' . Employee::count() . ' сотрудников');
        $this->command->info('Создано ' . WorkSchedule::count() . ' графиков работы');
        $this->command->info('Создано ' . TimeTracking::count() . ' записей учета времени');
    }
}
