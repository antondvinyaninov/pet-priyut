@extends('admin.layout')

@section('title', 'Соответствие нормативным требованиям')

@section('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Соответствие нормативным требованиям</h1>
        <div class="flex space-x-3">
            <a href="{{ route('admin.legal-compliance.export') }}" 
               class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                📥 Экспорт данных
            </a>
        </div>
    </div>

    <!-- Табы для разделов -->
    <div class="mb-8">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <button onclick="showSection('veterinary')" 
                        class="tab-button py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200 border-indigo-500 text-indigo-600" 
                        id="veterinary-tab">
                    🏥 Ветеринария
                </button>
                <button onclick="showSection('warehouse')" 
                        class="tab-button py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" 
                        id="warehouse-tab">
                    📦 Склад
                </button>
                <button onclick="showSection('reports')" 
                        class="tab-button py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" 
                        id="reports-tab">
                    📊 Отчетность
                </button>
            </nav>
        </div>
    </div>

    <!-- Раздел Ветеринария -->
    <div id="veterinary-section" class="section-content">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-900">Ветеринарная деятельность</h2>
            <div class="flex space-x-3">
                <div class="relative inline-block text-left">
                    <button type="button" id="veterinary-create-button" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center">
                        ➕ Создать
                        <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="veterinary-create-menu" class="hidden absolute right-0 mt-2 w-64 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10">
                        <div class="py-1">
                            <a href="#" onclick="createInspectionCommission()" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">👥 Комиссия по осмотру</a>
                            <a href="#" onclick="createInspectionAct()" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">📋 Акт осмотра животного</a>
                            <a href="#" onclick="createReturnProcedure()" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">🌍 Процедура возврата</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Статистика ветеринарии -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 rounded-lg bg-blue-50">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Всего животных</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total_animals'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 rounded-lg bg-yellow-50">
                        <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Ожидают осмотра</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['pending_inspections'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 rounded-lg bg-red-50">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Требуют стерилизации</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['sterilization_required'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 rounded-lg bg-green-50">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Процедуры возврата</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['return_procedures'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Недавние осмотры -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Недавние осмотры животных</h3>
                </div>
                <div class="p-6">
                    @if($recent_inspections->count() > 0)
                        <div class="space-y-4">
                            @foreach($recent_inspections as $inspection)
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="font-medium text-gray-900">
                                            {{ $inspection->animal->name ?? 'Животное #' . $inspection->animal->id }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            Акт № {{ $inspection->act_number }} от {{ $inspection->inspection_date->format('d.m.Y') }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            Комиссия: {{ $inspection->commission->commission_name }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-{{ $inspection->health_status_color }}-100 text-{{ $inspection->health_status_color }}-800">
                                            {{ $inspection->health_status_name }}
                                        </span>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ $inspection->aggression_level_name }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-4">Нет недавних осмотров</p>
                    @endif
                </div>
            </div>

            <!-- Просроченные возвраты -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Просроченные возвраты</h3>
                </div>
                <div class="p-6">
                    @if($overdue_returns->count() > 0)
                        <div class="space-y-4">
                            @foreach($overdue_returns as $return)
                                <div class="flex items-center justify-between p-4 bg-red-50 rounded-lg">
                                    <div>
                                        <p class="font-medium text-gray-900">
                                            {{ $return->animal->name ?? 'Животное #' . $return->animal->id }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            Планировалось: {{ $return->planned_return_date->format('d.m.Y') }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            Место: {{ $return->original_location }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                                            Просрочено на {{ abs($return->getDaysUntilReturn()) }} дн.
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-4">Нет просроченных возвратов</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Раздел Склад -->
    <div id="warehouse-section" class="section-content hidden">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-900">Управление складом</h2>
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
                        <p class="text-2xl font-bold text-gray-900">0</p>
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
                        <p class="text-2xl font-bold text-gray-900">0</p>
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
                        <p class="text-2xl font-bold text-gray-900">0</p>
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
                        <p class="text-2xl font-bold text-gray-900">0</p>
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
                    <p class="text-gray-500 text-center py-4">Нет товаров с критическими остатками</p>
                </div>
            </div>

            <!-- Ближайшие поставки -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Ближайшие поставки</h3>
                </div>
                <div class="p-6">
                    <p class="text-gray-500 text-center py-4">Нет запланированных поставок</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Раздел Отчетность -->
    <div id="reports-section" class="section-content hidden">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-900">Отчеты и аналитика</h2>
            <div class="flex space-x-3">
                <div class="relative inline-block text-left">
                    <button type="button" id="reports-create-button" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center">
                        📊 Создать отчет
                        <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="reports-create-menu" class="hidden absolute right-0 mt-2 w-64 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10">
                        <div class="py-1">
                            <a href="#" onclick="createComplianceReport()" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">📋 Отчет соответствия</a>
                            <a href="#" onclick="createVeterinaryReport()" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">🏥 Ветеринарный отчет</a>
                            <a href="#" onclick="createWarehouseReport()" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">📦 Складской отчет</a>
                            <a href="#" onclick="createRegulatoryDocument()" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">📄 Нормативный документ</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Статистика отчетности -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 rounded-lg bg-indigo-50">
                        <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Действующих документов</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['effective_documents'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 rounded-lg bg-purple-50">
                        <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Активных комиссий</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['active_commissions'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 rounded-lg bg-green-50">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Отчетов создано</p>
                        <p class="text-2xl font-bold text-gray-900">0</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 rounded-lg bg-blue-50">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Процент соответствия</p>
                        <p class="text-2xl font-bold text-gray-900">85%</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Нормативная база -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Нормативная база</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Приказ №5 от 13.01.2020</p>
                                <p class="text-sm text-gray-600">Об утверждении порядка осмотра животных</p>
                            </div>
                            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                Действует
                            </span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Приказ №6 от 13.01.2020</p>
                                <p class="text-sm text-gray-600">Об утверждении актов приема-передачи</p>
                            </div>
                            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                Действует
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Последние отчеты -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Последние отчеты</h3>
                </div>
                <div class="p-6">
                    <p class="text-gray-500 text-center py-4">Нет созданных отчетов</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Животные, требующие внимания (показывается только в разделе Ветеринария) -->
    @if($animals_requiring_attention->count() > 0)
        <div id="attention-animals" class="mt-8 bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Животные, требующие особого внимания</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($animals_requiring_attention as $inspection)
                        <div class="p-4 border border-yellow-200 rounded-lg bg-yellow-50">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-medium text-gray-900">
                                    {{ $inspection->animal->name ?? 'Животное #' . $inspection->animal->id }}
                                </h4>
                                <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-{{ $inspection->aggression_level_color }}-100 text-{{ $inspection->aggression_level_color }}-800">
                                    {{ $inspection->aggression_level_name }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 mb-1">
                                Здоровье: {{ $inspection->health_status_name }}
                            </p>
                            @if($inspection->sterilization_required && !$inspection->is_sterilized)
                                <p class="text-sm text-red-600">⚠️ Требует стерилизации</p>
                            @endif
                            @if($inspection->health_status === 'critical')
                                <p class="text-sm text-red-600">🚨 Критическое состояние</p>
                            @endif
                            @if($inspection->aggression_level === 'unmotivated')
                                <p class="text-sm text-red-600">⚠️ Немотивированная агрессия</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Модальные окна -->
<div id="modal-overlay" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 id="modal-title" class="text-lg font-medium text-gray-900"></h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="modal-content">
                <!-- Содержимое модальных окон будет загружаться здесь -->
            </div>
        </div>
    </div>
</div>

<script>
// Управление табами
function showSection(sectionName) {
    // Скрыть все разделы
    const sections = ['veterinary-section', 'warehouse-section', 'reports-section'];
    sections.forEach(section => {
        document.getElementById(section).classList.add('hidden');
    });
    
    // Сбросить все табы
    const tabs = ['veterinary-tab', 'warehouse-tab', 'reports-tab'];
    tabs.forEach(tab => {
        const tabElement = document.getElementById(tab);
        tabElement.classList.remove('border-indigo-500', 'text-indigo-600');
        tabElement.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Показать выбранный раздел
    document.getElementById(sectionName + '-section').classList.remove('hidden');
    
    // Активировать выбранный таб
    const activeTab = document.getElementById(sectionName + '-tab');
    activeTab.classList.remove('border-transparent', 'text-gray-500');
    activeTab.classList.add('border-indigo-500', 'text-indigo-600');
    
    // Показать/скрыть блок животных для ветеринарии
    const attentionAnimals = document.getElementById('attention-animals');
    if (attentionAnimals) {
        if (sectionName === 'veterinary') {
            attentionAnimals.classList.remove('hidden');
        } else {
            attentionAnimals.classList.add('hidden');
        }
    }
}

// Управление выпадающими меню
function setupDropdownMenu(buttonId, menuId) {
    const button = document.getElementById(buttonId);
    const menu = document.getElementById(menuId);
    
    if (button && menu) {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            // Закрыть все другие меню
            document.querySelectorAll('[id$="-create-menu"]').forEach(m => {
                if (m !== menu) m.classList.add('hidden');
            });
            menu.classList.toggle('hidden');
        });
    }
}

// Инициализация выпадающих меню
document.addEventListener('DOMContentLoaded', function() {
    setupDropdownMenu('veterinary-create-button', 'veterinary-create-menu');
    setupDropdownMenu('warehouse-create-button', 'warehouse-create-menu');
    setupDropdownMenu('reports-create-button', 'reports-create-menu');
});

// Закрытие меню при клике вне их
document.addEventListener('click', function(event) {
    document.querySelectorAll('[id$="-create-menu"]').forEach(menu => {
        const button = document.getElementById(menu.id.replace('-create-menu', '-create-button'));
        if (button && !button.contains(event.target) && !menu.contains(event.target)) {
            menu.classList.add('hidden');
        }
    });
});

// Функции для создания записей - Ветеринария
function createInspectionCommission() {
    document.getElementById('veterinary-create-menu').classList.add('hidden');
    document.getElementById('modal-title').textContent = 'Создать комиссию по осмотру';
    document.getElementById('modal-content').innerHTML = `
        <form onsubmit="submitInspectionCommission(event)">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Название комиссии</label>
                    <input type="text" name="commission_name" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           placeholder="Комиссия по осмотру животных №1">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Дата формирования</label>
                    <input type="date" name="formation_date" required value="${new Date().toISOString().substr(0,10)}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Действительна до (необязательно)</label>
                    <input type="date" name="valid_until"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Члены комиссии (по одному в строке)</label>
                    <textarea name="members" rows="4" required
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                              placeholder="Иванов И.И. - Ветеринарный врач&#10;Петров П.П. - Зоотехник&#10;Сидоров С.С. - Специалист по работе с животными"></textarea>
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Отмена</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Создать</button>
            </div>
        </form>
    `;
    document.getElementById('modal-overlay').classList.remove('hidden');
}

function createInspectionAct() {
    document.getElementById('veterinary-create-menu').classList.add('hidden');
    document.getElementById('modal-title').textContent = 'Создать акт осмотра животного';
    document.getElementById('modal-content').innerHTML = `
        <form onsubmit="submitInspectionAct(event)">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Номер акта</label>
                    <input type="text" name="act_number" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           placeholder="ОС-001/${new Date().getFullYear()}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Дата осмотра</label>
                    <input type="date" name="inspection_date" required value="${new Date().toISOString().substr(0,10)}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Животное (ID)</label>
                    <input type="number" name="animal_id" required min="1"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           placeholder="Введите ID животного">
                    <p class="text-xs text-gray-500 mt-1">Найти ID можно в разделе "Управление животными"</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Состояние здоровья</label>
                    <select name="health_status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Выберите состояние</option>
                        <option value="healthy">Здоровое</option>
                        <option value="sick">Больное</option>
                        <option value="injured">Травмированное</option>
                        <option value="critical">Критическое состояние</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Уровень агрессии</label>
                    <select name="aggression_level" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Выберите уровень</option>
                        <option value="none">Отсутствует</option>
                        <option value="low">Низкая</option>
                        <option value="moderate">Умеренная</option>
                        <option value="high">Высокая</option>
                        <option value="unmotivated">Немотивированная агрессивность</option>
                    </select>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="sterilization_required" value="1" 
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label class="ml-2 text-sm text-gray-700">Требуется стерилизация</label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Заключение комиссии</label>
                    <textarea name="inspection_notes" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                              placeholder="Заключение о состоянии животного..."></textarea>
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Отмена</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Создать</button>
            </div>
        </form>
    `;
    document.getElementById('modal-overlay').classList.remove('hidden');
}

function createReturnProcedure() {
    document.getElementById('veterinary-create-menu').classList.add('hidden');
    document.getElementById('modal-title').textContent = 'Создать процедуру возврата';
    document.getElementById('modal-content').innerHTML = `
        <form onsubmit="submitReturnProcedure(event)">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Животное (ID)</label>
                    <input type="number" name="animal_id" required min="1"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           placeholder="Введите ID животного">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Место обитания</label>
                    <input type="text" name="original_location" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           placeholder="Описание места, где было найдено животное">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Планируемая дата возврата</label>
                    <input type="date" name="planned_return_date" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Ответственные лица (по одному в строке)</label>
                    <textarea name="responsible_persons" rows="3" required
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                              placeholder="Иванов И.И. - Ветеринарный врач&#10;Петров П.П. - Водитель"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Примечания</label>
                    <textarea name="return_notes" rows="2"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                              placeholder="Дополнительные условия возврата..."></textarea>
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Отмена</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Создать</button>
            </div>
        </form>
    `;
    document.getElementById('modal-overlay').classList.remove('hidden');
}

// Функции для создания записей - Склад
function createVeterinarySupply() {
    document.getElementById('warehouse-create-menu').classList.add('hidden');
    // Добавить логику создания ветеринарного препарата
    alert('Функция создания ветеринарного препарата будет добавлена');
}

function createFeed() {
    document.getElementById('warehouse-create-menu').classList.add('hidden');
    // Добавить логику создания корма
    alert('Функция создания корма будет добавлена');
}

function createEquipment() {
    document.getElementById('warehouse-create-menu').classList.add('hidden');
    // Добавить логику создания инвентаря
    alert('Функция создания инвентаря будет добавлена');
}

function createSupplyRequest() {
    document.getElementById('warehouse-create-menu').classList.add('hidden');
    // Добавить логику создания заявки на поставку
    alert('Функция создания заявки на поставку будет добавлена');
}

// Функции для создания записей - Отчетность
function createComplianceReport() {
    document.getElementById('reports-create-menu').classList.add('hidden');
    // Добавить логику создания отчета соответствия
    alert('Функция создания отчета соответствия будет добавлена');
}

function createVeterinaryReport() {
    document.getElementById('reports-create-menu').classList.add('hidden');
    // Добавить логику создания ветеринарного отчета
    alert('Функция создания ветеринарного отчета будет добавлена');
}

function createWarehouseReport() {
    document.getElementById('reports-create-menu').classList.add('hidden');
    // Добавить логику создания складского отчета
    alert('Функция создания складского отчета будет добавлена');
}

function createRegulatoryDocument() {
    document.getElementById('reports-create-menu').classList.add('hidden');
    document.getElementById('modal-title').textContent = 'Добавить нормативный документ';
    document.getElementById('modal-content').innerHTML = `
        <form onsubmit="submitRegulatoryDocument(event)">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Тип документа</label>
                    <select name="document_type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Выберите тип</option>
                        <option value="order">Приказ</option>
                        <option value="regulation">Постановление</option>
                        <option value="instruction">Инструкция</option>
                        <option value="methodical">Методические рекомендации</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Номер документа</label>
                    <input type="text" name="document_number" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           placeholder="№5, №6 и т.д.">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Дата документа</label>
                    <input type="date" name="document_date" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Издавший орган</label>
                    <input type="text" name="issuing_authority" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           placeholder="Управление ветеринарии Воронежской области">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Название/предмет</label>
                    <input type="text" name="title" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           placeholder="Об утверждении порядка...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Действует с</label>
                    <input type="date" name="effective_from" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Отмена</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Создать</button>
            </div>
        </form>
    `;
    document.getElementById('modal-overlay').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('modal-overlay').classList.add('hidden');
}

// Функции отправки форм (существующие)
function submitInspectionCommission(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    const data = {
        commission_name: formData.get('commission_name'),
        formation_date: formData.get('formation_date'),
        valid_until: formData.get('valid_until') || null,
        members: formData.get('members').split('\n').filter(m => m.trim()),
        is_active: true
    };
    
    fetch('/admin/legal-compliance/commissions', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        closeModal();
        alert('Комиссия создана успешно!');
        location.reload();
    })
    .catch(error => {
        alert('Ошибка при создании комиссии: ' + error.message);
    });
}

function submitInspectionAct(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    const data = {
        act_number: formData.get('act_number'),
        inspection_date: formData.get('inspection_date'),
        animal_id: parseInt(formData.get('animal_id')),
        commission_id: 1,
        health_status: formData.get('health_status'),
        aggression_level: formData.get('aggression_level'),
        sterilization_required: formData.get('sterilization_required') ? true : false,
        is_sterilized: false,
        return_to_habitat_allowed: false,
        inspection_notes: formData.get('inspection_notes') || '',
        commission_signatures: [],
        status: 'draft'
    };
    
    fetch('/admin/legal-compliance/inspection-acts', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        closeModal();
        alert('Акт осмотра создан успешно!');
        location.reload();
    })
    .catch(error => {
        alert('Ошибка при создании акта: ' + error.message);
    });
}

function submitReturnProcedure(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    const data = {
        animal_id: parseInt(formData.get('animal_id')),
        inspection_act_id: 1,
        original_location: formData.get('original_location'),
        planned_return_date: formData.get('planned_return_date'),
        return_status: 'planned',
        return_notes: formData.get('return_notes') || '',
        responsible_persons: formData.get('responsible_persons').split('\n').filter(p => p.trim())
    };
    
    fetch('/admin/legal-compliance/return-procedures', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        closeModal();
        alert('Процедура возврата создана успешно!');
        location.reload();
    })
    .catch(error => {
        alert('Ошибка при создании процедуры: ' + error.message);
    });
}

function submitRegulatoryDocument(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    const data = {
        document_type: formData.get('document_type'),
        document_number: formData.get('document_number'),
        document_date: formData.get('document_date'),
        issuing_authority: formData.get('issuing_authority'),
        title: formData.get('title'),
        effective_from: formData.get('effective_from'),
        is_active: true
    };
    
    fetch('/admin/legal-compliance/regulatory-documents', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        closeModal();
        alert('Нормативный документ добавлен успешно!');
        location.reload();
    })
    .catch(error => {
        alert('Ошибка при добавлении документа: ' + error.message);
    });
}
</script>
@endsection 