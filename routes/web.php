<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OsvvRequestController as AdminOsvvRequestController;
use App\Http\Controllers\Admin\CaptureActController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\EmployeeTaskController;
use App\Http\Controllers\Admin\AnimalController;
use App\Http\Controllers\Admin\DeparturePlanController;
use App\Http\Controllers\Admin\RouteExecutionController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\OsvvRequestController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AnimalRegistryController;
use App\Http\Controllers\Admin\AnimalController as AdminAnimalController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Главная страница
Route::get('/', function () {
    return view('home');
});

// Dashboard - перенаправляем на админ-панель
Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware('auth')->name('dashboard');

// Маршруты аутентификации (добавлены автоматически Laravel Breeze)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Маршруты для заявок ОСВВ (публичная часть)
Route::prefix('osvv')->name('osvv.')->group(function () {
    Route::get('/create', [OsvvRequestController::class, 'create'])->name('create');
    Route::post('/store', [OsvvRequestController::class, 'store'])->name('store');
    Route::get('/thank-you', [OsvvRequestController::class, 'thankYou'])->name('thank-you');
    Route::get('/status', [OsvvRequestController::class, 'status'])->name('status');
});

// Маршруты админ-панели
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    // Дашборд
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Управление пользователями
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
        Route::post('/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{user}/manage-roles', [UserController::class, 'manageRoles'])->name('manage-roles');
        Route::get('/export/csv', [UserController::class, 'export'])->name('export');
    });
    
    // Управление ролями
    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::get('/create', [RoleController::class, 'create'])->name('create');
        Route::post('/', [RoleController::class, 'store'])->name('store');
        Route::get('/{role}', [RoleController::class, 'show'])->name('show');
        Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
        Route::put('/{role}', [RoleController::class, 'update'])->name('update');
        Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
        Route::post('/{role}/toggle-status', [RoleController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{role}/manage-permissions', [RoleController::class, 'managePermissions'])->name('manage-permissions');
        Route::post('/{role}/duplicate', [RoleController::class, 'duplicate'])->name('duplicate');
        Route::post('/{role}/assign-to-users', [RoleController::class, 'assignToUsers'])->name('assign-to-users');
        Route::get('/export/csv', [RoleController::class, 'export'])->name('export');
    });
    
    // Управление сотрудниками
    Route::prefix('employees')->name('employees.')->group(function () {
        Route::get('/', [EmployeeController::class, 'index'])->name('index');
        Route::get('/create', [EmployeeController::class, 'create'])->name('create');
        Route::post('/', [EmployeeController::class, 'store'])->name('store');
        Route::get('/{employee}', [EmployeeController::class, 'show'])->name('show');
        Route::get('/{employee}/edit', [EmployeeController::class, 'edit'])->name('edit');
        Route::put('/{employee}', [EmployeeController::class, 'update'])->name('update');
        Route::delete('/{employee}', [EmployeeController::class, 'destroy'])->name('destroy');
        Route::post('/{employee}/toggle-status', [EmployeeController::class, 'toggleStatus'])->name('toggle-status');
        
        // Графики работы
        Route::get('/{employee}/schedules', [EmployeeController::class, 'schedules'])->name('schedules');
        Route::post('/{employee}/schedules', [EmployeeController::class, 'storeSchedule'])->name('store-schedule');
        
        // Табель учета времени
        Route::get('/{employee}/timesheet', [EmployeeController::class, 'timesheet'])->name('timesheet');
        Route::post('/{employee}/clock-in-out', [EmployeeController::class, 'clockInOut'])->name('clock-in-out');
        
        // Общий табель и графики
        Route::get('/general-timesheet', [EmployeeController::class, 'generalTimesheet'])->name('general-timesheet');
        Route::get('/general-schedules', [EmployeeController::class, 'generalSchedules'])->name('general-schedules');
        Route::get('/export-timesheet', [EmployeeController::class, 'exportTimesheet'])->name('export-timesheet');
        Route::get('/export-schedules', [EmployeeController::class, 'exportSchedules'])->name('export-schedules');
        
        // Экспорт
        Route::get('/export/csv', [EmployeeController::class, 'export'])->name('export');
    });

    // Маршруты для задач сотрудников
    Route::prefix('tasks')->name('tasks.')->group(function () {
        Route::get('/', [EmployeeTaskController::class, 'index'])->name('index');
        Route::get('/create', [EmployeeTaskController::class, 'create'])->name('create');
        Route::post('/', [EmployeeTaskController::class, 'store'])->name('store');
        Route::get('/{task}', [EmployeeTaskController::class, 'show'])->name('show');
        Route::get('/{task}/edit', [EmployeeTaskController::class, 'edit'])->name('edit');
        Route::put('/{task}', [EmployeeTaskController::class, 'update'])->name('update');
        Route::delete('/{task}', [EmployeeTaskController::class, 'destroy'])->name('destroy');
        
        // Действия с задачами
        Route::post('/{task}/start', [EmployeeTaskController::class, 'start'])->name('start');
        Route::post('/{task}/complete', [EmployeeTaskController::class, 'complete'])->name('complete');
        Route::post('/{task}/cancel', [EmployeeTaskController::class, 'cancel'])->name('cancel');
        
        // Массовые операции
        Route::post('/bulk-action', [EmployeeTaskController::class, 'bulkAction'])->name('bulk-action');
    });

    // Выполнение маршрутов
    Route::prefix('route-execution')->name('route-execution.')->group(function () {
        Route::get('/route/{route}', [RouteExecutionController::class, 'showRouteExecution'])->name('route');
        Route::get('/route/{route}/stats', [RouteExecutionController::class, 'getRouteStats'])->name('route-stats');
        
        // Выполнение заявок в маршруте
        Route::get('/request/{routeRequest}/form', [RouteExecutionController::class, 'showRequestExecutionForm'])->name('request-form');
        Route::post('/request/{routeRequest}/mark', [RouteExecutionController::class, 'markRequestExecution'])->name('mark-request');
        
        // Отметка выпуска животных
        Route::post('/animal/{routeAnimal}/mark-release', [RouteExecutionController::class, 'markAnimalRelease'])->name('mark-animal-release');
    });
    
    // Аналитика
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', [AnalyticsController::class, 'index'])->name('index');
        Route::get('/export', [AnalyticsController::class, 'exportAnalytics'])->name('export');
        Route::get('/efficiency-trends', [AnalyticsController::class, 'getEfficiencyTrends'])->name('efficiency-trends');
        Route::get('/route-optimization-data', [AnalyticsController::class, 'getRouteOptimizationData'])->name('route-optimization-data');
        Route::post('/log-departure-start', [AnalyticsController::class, 'logDepartureStart'])->name('log-departure-start');
        Route::post('/log-departure-complete', [AnalyticsController::class, 'logDepartureComplete'])->name('log-departure-complete');
        Route::post('/log-status-change', [AnalyticsController::class, 'logStatusChange'])->name('log-status-change');
        
        // AI-маршруты
        Route::get('/ai-predictions', [AnalyticsController::class, 'aiPredictions'])->name('ai-predictions');
        Route::get('/ai-route-optimization', [AnalyticsController::class, 'aiRouteOptimization'])->name('ai-route-optimization');
        Route::get('/ai-predict-completion/{requestId}', [AnalyticsController::class, 'aiPredictCompletion'])->name('ai-predict-completion');
        Route::get('/ai-anomaly-detection', [AnalyticsController::class, 'aiAnomalyDetection'])->name('ai-anomaly-detection');
    });
    
    // Ветеринария
    Route::prefix('veterinary')->name('veterinary.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\VeterinaryController::class, 'index'])->name('index');
        
        // API маршруты для создания записей
        Route::post('/commissions', [\App\Http\Controllers\Admin\VeterinaryController::class, 'storeCommission'])->name('commissions.store');
        Route::post('/inspection-acts', [\App\Http\Controllers\Admin\VeterinaryController::class, 'storeInspectionAct'])->name('inspection-acts.store');
        Route::post('/return-procedures', [\App\Http\Controllers\Admin\VeterinaryController::class, 'storeReturnProcedure'])->name('return-procedures.store');
    });

    // Склад
    Route::prefix('warehouse')->name('warehouse.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\WarehouseController::class, 'index'])->name('index');
        
        // API маршруты для создания записей
        Route::post('/veterinary-supplies', [\App\Http\Controllers\Admin\WarehouseController::class, 'createVeterinarySupply'])->name('veterinary-supplies.store');
        Route::post('/feed', [\App\Http\Controllers\Admin\WarehouseController::class, 'createFeed'])->name('feed.store');
        Route::post('/equipment', [\App\Http\Controllers\Admin\WarehouseController::class, 'createEquipment'])->name('equipment.store');
        Route::post('/supply-requests', [\App\Http\Controllers\Admin\WarehouseController::class, 'createSupplyRequest'])->name('supply-requests.store');
    });

    // Отчетность
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ReportsController::class, 'index'])->name('index');
        
        // API маршруты для создания записей
        Route::post('/compliance', [\App\Http\Controllers\Admin\ReportsController::class, 'createComplianceReport'])->name('compliance.store');
        Route::post('/veterinary', [\App\Http\Controllers\Admin\ReportsController::class, 'createVeterinaryReport'])->name('veterinary.store');
        Route::post('/warehouse', [\App\Http\Controllers\Admin\ReportsController::class, 'createWarehouseReport'])->name('warehouse.store');
        Route::post('/regulatory-documents', [\App\Http\Controllers\Admin\ReportsController::class, 'storeRegulatoryDocument'])->name('regulatory-documents.store');
    });

    // Планировщик выездов
    Route::prefix('departure-planner')->name('departure-planner.')->group(function () {
        // Основные CRUD операции
        Route::get('/', [DeparturePlanController::class, 'index'])->name('index');
        Route::get('/create', [DeparturePlanController::class, 'create'])->name('create');
        Route::post('/', [DeparturePlanController::class, 'store'])->name('store');
        Route::get('/{departurePlan}/view', [DeparturePlanController::class, 'view'])->name('view');
        Route::get('/{departurePlan}', [DeparturePlanController::class, 'show'])->name('show');
        Route::get('/{departurePlan}/edit', [DeparturePlanController::class, 'edit'])->name('edit');
        Route::put('/{departurePlan}', [DeparturePlanController::class, 'update'])->name('update');
        Route::delete('/{departurePlan}', [DeparturePlanController::class, 'destroy'])->name('destroy');
        
        // Управление состоянием плана
        Route::post('/{departurePlan}/approve', [DeparturePlanController::class, 'approve'])->name('approve');
        Route::post('/{departurePlan}/start', [DeparturePlanController::class, 'start'])->name('start');
        Route::post('/{departurePlan}/complete', [DeparturePlanController::class, 'complete'])->name('complete');
        Route::post('/{departurePlan}/cancel', [DeparturePlanController::class, 'cancel'])->name('cancel');
        
        // Автогенерация плана
        Route::post('/auto-generate', [DeparturePlanController::class, 'autoGenerate'])->name('auto-generate');
        
        // Добавление заявки в план
        Route::post('/{departurePlan}/add-request', [DeparturePlanController::class, 'addRequest'])->name('add-request');
    });
    
    // Учет животных
    Route::prefix('animal-registry')->name('animal-registry.')->group(function () {
        Route::get('/', [AnimalRegistryController::class, 'index'])->name('index');
        Route::get('/osvv', [AnimalRegistryController::class, 'osvv'])->name('osvv');
        Route::get('/shelter', [AnimalRegistryController::class, 'shelter'])->name('shelter');
        // Новые вкладки
        Route::get('/cards', [AnimalRegistryController::class, 'cards'])->name('cards');
        Route::get('/cages', [AnimalRegistryController::class, 'cages'])->name('cages');
        Route::post('/cages', [\App\Http\Controllers\Admin\CageController::class, 'store'])->name('cages.store');
        Route::post('/cage-blocks', [\App\Http\Controllers\Admin\CageBlockController::class, 'store'])->name('cage-blocks.store');
        Route::delete('/cage-blocks/{cageBlock}', [\App\Http\Controllers\Admin\CageBlockController::class, 'destroy'])->name('cage-blocks.destroy');
        Route::get('/documents', [AnimalRegistryController::class, 'documents'])->name('documents');
    });
    
    // Управление животными
    Route::prefix('animals')->name('animals.')->group(function () {
        Route::get('/', [AnimalController::class, 'index'])->name('index');
        Route::get('/create', [AnimalController::class, 'create'])->name('create');
        Route::post('/', [AnimalController::class, 'store'])->name('store');
        Route::get('/{animal}', [AnimalController::class, 'show'])->name('show');
        Route::get('/{animal}/edit', [AnimalController::class, 'edit'])->name('edit');
        Route::put('/{animal}', [AnimalController::class, 'update'])->name('update');
        Route::delete('/{animal}', [AnimalController::class, 'destroy'])->name('destroy');
        
        // API для Kanban-доски и вольеров
        Route::post('/{animal}/move-stage', [AdminAnimalController::class, 'moveToStage'])->name('move-stage');
        Route::post('/{animal}/complete-task', [AdminAnimalController::class, 'completeTask'])->name('complete-task');
        Route::delete('/{animal}/uncomplete-task/{task}', [AdminAnimalController::class, 'uncompleteTask'])->name('uncomplete-task');
        Route::post('/{animal}/uncomplete-task/{task}', [AdminAnimalController::class, 'uncompleteTask'])->name('uncomplete-task-post');
        Route::post('/move-to-cage', [AdminAnimalController::class, 'moveToCage'])->name('move-to-cage');
        
        // Экспорт
        Route::get('/export/csv', [AnimalController::class, 'export'])->name('export');

        // Печать карточки
        Route::get('/{animal}/print', [AnimalController::class, 'printCard'])->name('print.card');

        // Обновление регистрационной карточки в самой карточке животного
        Route::patch('/{animal}/registration-card', [AnimalController::class, 'updateRegistrationCard'])->name('update-registration-card');

        // Единое обновление карточки животного (основные + регистрационная карточка)
        Route::patch('/{animal}/full-card', [AnimalController::class, 'updateFullCard'])->name('update-full-card');
        
        // Быстрое обновление отдельного поля
        Route::patch('/{animal}/quick-update', [AnimalController::class, 'quickUpdate'])->name('quick-update');
        
        // Выбор дальнейшей судьбы животного
        Route::post('/{animal}/release-to-original-place', [AnimalController::class, 'releaseToOriginalPlace'])->name('release-to-original-place');
        Route::post('/{animal}/keep-in-shelter', [AnimalController::class, 'keepInShelter'])->name('keep-in-shelter');
    });
    
    // Акты приема-передачи животных
    Route::prefix('animal-transfer-acts')->name('animal-transfer-acts.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AnimalTransferActController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\AnimalTransferActController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\AnimalTransferActController::class, 'store'])->name('store');
        Route::get('/{animalTransferAct}', [\App\Http\Controllers\Admin\AnimalTransferActController::class, 'show'])->name('show');
        Route::get('/{animalTransferAct}/edit', [\App\Http\Controllers\Admin\AnimalTransferActController::class, 'edit'])->name('edit');
        Route::put('/{animalTransferAct}', [\App\Http\Controllers\Admin\AnimalTransferActController::class, 'update'])->name('update');
        Route::delete('/{animalTransferAct}', [\App\Http\Controllers\Admin\AnimalTransferActController::class, 'destroy'])->name('destroy');
        Route::post('/{animalTransferAct}/sign', [\App\Http\Controllers\Admin\AnimalTransferActController::class, 'sign'])->name('sign');
        Route::post('/{animalTransferAct}/unsign', [\App\Http\Controllers\Admin\AnimalTransferActController::class, 'unsign'])->name('unsign');
        Route::get('/{animalTransferAct}/pdf', [\App\Http\Controllers\Admin\AnimalTransferActController::class, 'exportPdf'])->name('pdf');
    });
    
    // Заявки ОСВВ (административная часть)
    Route::prefix('osvv')->name('osvv.')->group(function () {
        Route::get('/', [AdminOsvvRequestController::class, 'index'])->name('index');
        Route::get('/create', [AdminOsvvRequestController::class, 'create'])->name('create');
        Route::post('/', [AdminOsvvRequestController::class, 'store'])->name('store');
        
        // Маршруты для карты выездов (ДОЛЖНЫ БЫТЬ ВЫШЕ /{osvvRequest})
        Route::get('/departure-map', [AdminOsvvRequestController::class, 'departureMap'])->name('departure-map');
        Route::get('/departure-routes-data', [AdminOsvvRequestController::class, 'departureRoutesData'])->name('departure-routes-data');
        
        // Маршрут для проверки дубликатов
        Route::post('/check-duplicates', [AdminOsvvRequestController::class, 'checkDuplicates'])->name('check-duplicates');
        
        // Получение доступных заявок для добавления в план
        Route::get('/available', [AdminOsvvRequestController::class, 'getAvailableRequests'])->name('available');
        
        // Маршруты для управления актами отлова
        Route::resource('acts', CaptureActController::class);
        Route::get('/acts/{act}/pdf', [CaptureActController::class, 'generatePdf'])->name('acts.pdf');
        
        // Быстрый отлов
        Route::post('/{osvvRequest}/quick-capture', [AdminOsvvRequestController::class, 'quickCapture'])->name('quick-capture');
        
        // Маршруты с параметрами (ДОЛЖНЫ БЫТЬ В КОНЦЕ)
        Route::get('/{osvvRequest}', [AdminOsvvRequestController::class, 'show'])->name('show');
        Route::get('/{osvvRequest}/edit', [AdminOsvvRequestController::class, 'edit'])->name('edit');
        Route::put('/{osvvRequest}', [AdminOsvvRequestController::class, 'update'])->name('update');
        Route::post('/{osvvRequest}/comment', [AdminOsvvRequestController::class, 'addComment'])->name('comment');
        Route::put('/{osvvRequest}/status', [AdminOsvvRequestController::class, 'changeStatus'])->name('change-status');
        Route::put('/{osvvRequest}/created-at', [AdminOsvvRequestController::class, 'updateCreatedAt'])->name('update-created-at');
        Route::post('/{osvvRequest}/coordinates', [AdminOsvvRequestController::class, 'updateCoordinates'])->name('update-coordinates');
        
        // Маршруты для управления выездами
        Route::post('/{osvvRequest}/register-departure', [AdminOsvvRequestController::class, 'registerDeparture'])->name('register-departure');
        Route::post('/{osvvRequest}/schedule-departure', [AdminOsvvRequestController::class, 'scheduleDeparture'])->name('schedule-departure');
        
        // Маршрут для удаления файлов
        Route::post('/{osvvRequest}/delete-file', [AdminOsvvRequestController::class, 'deleteFile'])->name('delete-file');

    });
    
    // Управление меню
    Route::prefix('menu')->name('menu.')->group(function () {
        Route::get('/', [MenuController::class, 'index'])->name('index');
        Route::get('/create', [MenuController::class, 'create'])->name('create');
        Route::post('/', [MenuController::class, 'store'])->name('store');
        Route::get('/{menuItem}/edit', [MenuController::class, 'edit'])->name('edit');
        Route::put('/{menuItem}', [MenuController::class, 'update'])->name('update');
        Route::delete('/{menuItem}', [MenuController::class, 'destroy'])->name('destroy');
        
        // Действия с порядком
        Route::post('/{menuItem}/move-up', [MenuController::class, 'moveUp'])->name('move-up');
        Route::post('/{menuItem}/move-down', [MenuController::class, 'moveDown'])->name('move-down');
        Route::post('/{menuItem}/toggle-active', [MenuController::class, 'toggleActive'])->name('toggle-active');
    });
});

require __DIR__.'/auth.php';
