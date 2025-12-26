@extends('admin.layout')

@section('header')
    Акты приема-передачи животных
@endsection

@section('content')
    <div class="space-y-6">
        <!-- Заголовок и кнопка создания -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Акты приема-передачи животных</h1>
                <p class="text-gray-600">Управление документооборотом передачи животных между организациями</p>
            </div>
            <a href="{{ route('admin.animal-transfer-acts.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                📋 Создать акт
            </a>
        </div>

        <!-- Статистика -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <span class="text-blue-600 font-bold">📋</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Всего актов</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $acts->total() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                            <span class="text-yellow-600 font-bold">⏳</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Черновики</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $acts->where('status', 'draft')->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <span class="text-green-600 font-bold">✅</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Подписанные</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $acts->where('status', 'signed')->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                            <span class="text-purple-600 font-bold">🐾</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Животных передано</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $acts->sum(function($act) { return $act->animals->count(); }) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Фильтры -->
        <div class="bg-white rounded-lg shadow p-6">
            <form method="GET" action="{{ route('admin.animal-transfer-acts.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Статус</label>
                    <select name="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Все статусы</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Черновик</option>
                        <option value="signed" {{ request('status') === 'signed' ? 'selected' : '' }}>Подписанный</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Организация-отправитель</label>
                    <input type="text" name="from_organization" value="{{ request('from_organization') }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Название организации">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Организация-получатель</label>
                    <input type="text" name="to_organization" value="{{ request('to_organization') }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Название организации">
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full bg-blue-600 text-white rounded-md px-4 py-2 hover:bg-blue-700">
                        🔍 Фильтровать
                    </button>
                </div>
            </form>
        </div>

        <!-- Таблица актов -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Номер акта
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Дата акта
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Организации
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Животные
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Статус
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Действия
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($acts as $act)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $act->act_number }}</div>
                                    <div class="text-sm text-gray-500">{{ $act->created_at->format('d.m.Y H:i') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($act->act_date)->format('d.m.Y') }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        <div><strong>От:</strong> {{ $act->from_organization }}</div>
                                        <div><strong>К:</strong> {{ $act->to_organization }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $act->animals->count() }} животных
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($act->status === 'draft')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            ⏳ Черновик
                                        </span>
                                    @elseif($act->status === 'signed')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            ✅ Подписанный
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <a href="{{ route('admin.animal-transfer-acts.show', $act) }}" 
                                       class="text-blue-600 hover:text-blue-900">👁️ Просмотр</a>
                                    
                                    @if($act->status === 'draft')
                                        <a href="{{ route('admin.animal-transfer-acts.edit', $act) }}" 
                                           class="text-indigo-600 hover:text-indigo-900">✏️ Изменить</a>
                                    @endif
                                    
                                    <a href="{{ route('admin.animal-transfer-acts.pdf', $act) }}" 
                                       class="text-red-600 hover:text-red-900" target="_blank">📄 PDF</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    <div class="py-8">
                                        <div class="text-6xl mb-4">📋</div>
                                        <div class="text-xl font-medium mb-2">Актов не найдено</div>
                                        <div class="text-gray-500">Создайте первый акт приема-передачи</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Пагинация -->
        @if($acts->hasPages())
            <div class="bg-white rounded-lg shadow p-6">
                {{ $acts->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
@endsection 