<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\OsvvComment;
use App\Models\OsvvAnalytics;
use App\Models\OsvvRequest;

echo "Начинаем очистку данных ОСВВ...\n";

// Удаляем все комментарии к заявкам
$commentsCount = OsvvComment::count();
OsvvComment::truncate();
echo "Удалено комментариев: {$commentsCount}\n";

// Удаляем всю аналитику
$analyticsCount = OsvvAnalytics::count();
OsvvAnalytics::truncate();
echo "Удалено записей аналитики: {$analyticsCount}\n";

// Удаляем все заявки ОСВВ
$requestsCount = OsvvRequest::count();
OsvvRequest::truncate();
echo "Удалено заявок ОСВВ: {$requestsCount}\n";

echo "Все данные ОСВВ успешно очищены!\n";
echo "Теперь можно начать тестирование с заявки №1\n"; 