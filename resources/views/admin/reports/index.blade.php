@extends('admin.layout')

@section('title', 'Отчетность')

@section('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">📊 Отчеты и аналитика</h1>
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
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['reports_created'] }}</p>
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
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['compliance_percentage'] }}%</p>
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
                @if($regulatory_documents->count() > 0)
                    <div class="space-y-3">
                        @foreach($regulatory_documents as $doc)
                            <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-900">
                                        {{ ucfirst($doc->document_type) }} №{{ $doc->document_number }} от {{ \Carbon\Carbon::parse($doc->document_date)->format('d.m.Y') }}
                                    </p>
                                    <p class="text-sm text-gray-600">{{ $doc->title }}</p>
                                    <p class="text-xs text-gray-500">{{ $doc->issuing_authority }}</p>
                                </div>
                                <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-{{ $doc->status_color }}-100 text-{{ $doc->status_color }}-800">
                                    {{ $doc->status_name }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Приказ №5 от 13.01.2020</p>
                                <p class="text-sm text-gray-600">Об утверждении порядка осмотра животных</p>
                                <p class="text-xs text-gray-500">Управление ветеринарии Воронежской области</p>
                            </div>
                            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                Действует
                            </span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Приказ №6 от 13.01.2020</p>
                                <p class="text-sm text-gray-600">Об утверждении актов приема-передачи</p>
                                <p class="text-xs text-gray-500">Управление ветеринарии Воронежской области</p>
                            </div>
                            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                Действует
                            </span>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Последние отчеты -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Последние отчеты</h3>
            </div>
            <div class="p-6">
                @if($recent_reports->count() > 0)
                    <div class="space-y-4">
                        @foreach($recent_reports as $report)
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $report->title }}</p>
                                    <p class="text-sm text-gray-600">Создан: {{ $report->created_at->format('d.m.Y H:i') }}</p>
                                    <p class="text-sm text-gray-600">Автор: {{ $report->creator->name }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-{{ $report->status_color }}-100 text-{{ $report->status_color }}-800">
                                        {{ $report->status_name }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">Нет созданных отчетов</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Дополнительная информация -->
    <div class="mt-8 bg-green-50 border border-green-200 rounded-lg p-6">
        <div class="flex items-center">
            <svg class="h-8 w-8 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <h3 class="text-lg font-medium text-green-900">Система отчетности</h3>
                <p class="text-green-700 mt-1">
                    Здесь вы можете создавать отчеты по различным направлениям деятельности, 
                    управлять нормативными документами и отслеживать соответствие требованиям законодательства.
                </p>
            </div>
        </div>
    </div>
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
// Управление выпадающим меню
document.getElementById('reports-create-button').addEventListener('click', function(e) {
    e.stopPropagation();
    const menu = document.getElementById('reports-create-menu');
    menu.classList.toggle('hidden');
});

// Закрытие меню при клике вне его
document.addEventListener('click', function(event) {
    const button = document.getElementById('reports-create-button');
    const menu = document.getElementById('reports-create-menu');
    
    if (!button.contains(event.target) && !menu.contains(event.target)) {
        menu.classList.add('hidden');
    }
});

// Функции для создания записей
function createComplianceReport() {
    document.getElementById('reports-create-menu').classList.add('hidden');
    alert('Функция создания отчета соответствия будет добавлена в ближайших обновлениях');
}

function createVeterinaryReport() {
    document.getElementById('reports-create-menu').classList.add('hidden');
    alert('Функция создания ветеринарного отчета будет добавлена в ближайших обновлениях');
}

function createWarehouseReport() {
    document.getElementById('reports-create-menu').classList.add('hidden');
    alert('Функция создания складского отчета будет добавлена в ближайших обновлениях');
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
    
    fetch('/admin/reports/regulatory-documents', {
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