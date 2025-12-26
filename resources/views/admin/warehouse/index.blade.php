@extends('admin.layout')

@section('title', 'Склад')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">📦 Управление складом</h1>
        <div class="flex space-x-3">
            <div class="relative inline-block text-left">
                <button type="button" id="warehouse-create-button" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center">
                    ➕ Добавить
                    <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="warehouse-create-menu" class="hidden absolute right-0 mt-2 w-64 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10">
                    <div class="py-1">
                        <a href="#" onclick="createVeterinarySupply()" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">💉 Ветеринарный препарат</a>
                        <a href="#" onclick="createFeed()" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">🥘 Корм</a>
                        <a href="#" onclick="createEquipment()" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">🔧 Инвентарь</a>
                        <a href="#" onclick="createSupplyRequest()" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">📋 Заявка на поставку</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Статистика склада -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 rounded-lg bg-purple-50">
                    <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Позиций на складе</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_items'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 rounded-lg bg-orange-50">
                    <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Заканчивается</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['low_stock'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 rounded-lg bg-red-50">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Просрочено</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['expired'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 rounded-lg bg-green-50">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">В наличии</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['in_stock'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Критические остатки -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Критические остатки</h3>
            </div>
            <div class="p-6">
                @if($critical_items->count() > 0)
                    <div class="space-y-4">
                        @foreach($critical_items as $item)
                            <div class="flex items-center justify-between p-4 bg-red-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $item->name }}</p>
                                    <p class="text-sm text-gray-600">Категория: {{ $item->category }}</p>
                                    <p class="text-sm text-gray-600">Срок годности: {{ $item->expiry_date ?? 'Не указан' }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                                        Осталось: {{ $item->quantity }} {{ $item->unit }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">Нет товаров с критическими остатками</p>
                @endif
            </div>
        </div>

        <!-- Ближайшие поставки -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Ближайшие поставки</h3>
            </div>
            <div class="p-6">
                @if($upcoming_deliveries->count() > 0)
                    <div class="space-y-4">
                        @foreach($upcoming_deliveries as $delivery)
                            <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $delivery->supplier_name }}</p>
                                    <p class="text-sm text-gray-600">Товаров: {{ $delivery->items_count }}</p>
                                    <p class="text-sm text-gray-600">Дата: {{ $delivery->delivery_date->format('d.m.Y') }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                        {{ $delivery->status_name }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">Нет запланированных поставок</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Дополнительная информация -->
    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
        <div class="flex items-center">
            <svg class="h-8 w-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <h3 class="text-lg font-medium text-blue-900">Функционал в разработке</h3>
                <p class="text-blue-700 mt-1">
                    Полный функционал управления складом будет добавлен в ближайших обновлениях. 
                    Здесь будет доступно управление запасами, учет поставок, контроль сроков годности и автоматические уведомления.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
// Управление выпадающим меню
document.getElementById('warehouse-create-button').addEventListener('click', function(e) {
    e.stopPropagation();
    const menu = document.getElementById('warehouse-create-menu');
    menu.classList.toggle('hidden');
});

// Закрытие меню при клике вне его
document.addEventListener('click', function(event) {
    const button = document.getElementById('warehouse-create-button');
    const menu = document.getElementById('warehouse-create-menu');
    
    if (!button.contains(event.target) && !menu.contains(event.target)) {
        menu.classList.add('hidden');
    }
});

// Функции для создания записей
function createVeterinarySupply() {
    document.getElementById('warehouse-create-menu').classList.add('hidden');
    alert('Функция создания ветеринарного препарата будет добавлена в ближайших обновлениях');
}

function createFeed() {
    document.getElementById('warehouse-create-menu').classList.add('hidden');
    alert('Функция создания корма будет добавлена в ближайших обновлениях');
}

function createEquipment() {
    document.getElementById('warehouse-create-menu').classList.add('hidden');
    alert('Функция создания инвентаря будет добавлена в ближайших обновлениях');
}

function createSupplyRequest() {
    document.getElementById('warehouse-create-menu').classList.add('hidden');
    alert('Функция создания заявки на поставку будет добавлена в ближайших обновлениях');
}
</script>
@endsection 