#!/bin/bash

# Импорт всех недостающих файлов
php artisan animals:import-cards 2>&1 | grep -E "(Сохранено|Ошибок)" | tail -5

echo ""
echo "Проверка итогового количества:"
php artisan tinker --execute="echo 'Всего животных: ' . \App\Models\Animal::count() . PHP_EOL;"
