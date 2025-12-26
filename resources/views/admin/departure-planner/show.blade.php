@extends('admin.layout')

@section('header', 'Просмотр плана выезда')

@section('content')
    <div class="space-y-6">
        <!-- Верхняя панель с заголовком и действиями -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        {{ $plan->name }}
                    </h3>
                    <div class="flex space-x-2">
                        <a href="{{ route('admin.departure-planner.index') }}" class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-white/20 active:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                            </svg>
                            Назад к планам
                        </a>
                        @if($plan->status === 'draft')
                            <a href="{{ route('admin.departure-planner.edit', $plan) }}" class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-white/20 active:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                </svg>
                                Редактировать
                            </a>
                        @endif
                    </div>
                </div>
                <div class="mt-2">
                    <p class="text-white/70 text-sm">Подробная информация о плане выезда и маршрутах</p>
                </div>
            </div>
        
            <!-- Статус и информация -->
            <div class="px-6 py-3 bg-white border-b border-gray-100">
                <div class="flex flex-wrap items-center gap-4">
                    @switch($plan->status)
                        @case('draft')
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                Черновик
                            </span>
                            @break
                        @case('approved')
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                Утвержден
                            </span>
                            @break
                        @case('in_progress')
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                В процессе
                            </span>
                            @break
                        @case('completed')
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                Завершен
                            </span>
                            @break
                    @endswitch
                    
                    <span class="text-sm text-gray-600">
                        Создан: {{ $plan->created_at->format('d.m.Y H:i') }}
                    </span>
                    
                    @if($plan->planned_date)
                        <span class="text-sm text-gray-600">
                            Планируется: {{ $plan->planned_date->format('d.m.Y') }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Основная информация -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-700 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Основная информация
                </h3>
            </div>
            <div class="px-6 py-4">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-700">Название плана</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $plan->name }}</dd>
                    </div>
                    
                    @if($plan->description)
                        <div>
                            <dt class="text-sm font-medium text-gray-700">Описание</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $plan->description }}</dd>
                        </div>
                    @endif
                    
                    @if($plan->planned_date)
                        <div>
                            <dt class="text-sm font-medium text-gray-700">Дата планирования</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $plan->planned_date->format('d.m.Y') }}</dd>
                        </div>
                    @endif
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-700">Создатель</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $plan->creator->name }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Статистика -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="bg-indigo-500 px-4 py-2">
                    <h4 class="text-sm font-medium text-white">Маршруты</h4>
                </div>
                <div class="px-4 py-3">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500">Всего маршрутов</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $plan->routes->count() }}</p>
                        </div>
                        <div class="h-10 w-10 bg-indigo-100 rounded-full flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m0 0L9 7" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="bg-green-500 px-4 py-2">
                    <h4 class="text-sm font-medium text-white">Заявки</h4>
                </div>
                <div class="px-4 py-3">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500">Всего заявок</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $totalRequests }}</p>
                        </div>
                        <div class="h-10 w-10 bg-green-100 rounded-full flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="bg-purple-500 px-4 py-2">
                    <h4 class="text-sm font-medium text-white">Время</h4>
                </div>
                <div class="px-4 py-3">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500">Общее время</p>
                            @if($estimatedTime > 0)
                                <p class="text-2xl font-semibold text-gray-900">{{ floor($estimatedTime / 60) }}ч {{ $estimatedTime % 60 }}м</p>
                            @else
                                <p class="text-2xl font-semibold text-gray-400">—</p>
                            @endif
                        </div>
                        <div class="h-10 w-10 bg-purple-100 rounded-full flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Маршруты -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-700 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                    Маршруты ({{ $plan->routes->count() }})
                </h3>
            </div>

            @if($plan->routes->count() > 0)
                <div class="px-6 py-4">
                    <div class="space-y-6">
                        @foreach($plan->routes as $route)
                            <div class="border border-gray-200 rounded-lg">
                                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-md font-medium text-gray-900">
                                            Маршрут {{ $loop->iteration }}
                                        </h4>
                                        <div class="flex items-center space-x-4 text-sm text-gray-600">
                                            @if($route->catcher)
                                                <span class="flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                    </svg>
                                                    {{ $route->catcher->name }}
                                                </span>
                                            @endif
                                            
                                            @if($route->start_time)
                                                <span class="flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    {{ $route->start_time }}
                                                </span>
                                            @endif
                                            
                                            <span class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                </svg>
                                                {{ $route->requests->count() }} заявок
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                @if($route->requests->count() > 0)
                                    <div class="p-4">
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                            @foreach($route->requests as $routeRequest)
                                                @php $request = $routeRequest->osvvRequest @endphp
                                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                                                    <div class="flex items-start justify-between">
                                                        <div class="flex-1">
                                                            <div class="text-sm font-medium text-gray-900">
                                                                #{{ $request->id }} - {{ $request->applicant_name }}
                                                            </div>
                                                            <div class="text-xs text-gray-600 mt-1">
                                                                {{ $request->address }}
                                                            </div>
                                                            <div class="text-xs text-gray-500 mt-2">
                                                                {{ $request->created_at->format('d.m.Y') }}
                                                                @if($request->is_urgent)
                                                                    <span class="ml-2 inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                                        Срочно
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="ml-2 text-xs text-gray-500">
                                                            #{{ $loop->iteration }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <div class="p-4 text-center text-gray-500 text-sm">
                                        В маршруте нет заявок
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M34 40h10v-4a6 6 0 00-10.712-3.714M34 40H14m20 0v-4a9.971 9.971 0 00-.712-3.714M14 40H4v-4a6 6 0 0110.713-3.714M14 40v-4c0-1.313.253-2.566.713-3.714m0 0A10.003 10.003 0 0124 26c4.21 0 7.813 2.602 9.288 6.286M30 14a6 6 0 11-12 0 6 6 0 0112 0zm12 6a4 4 0 11-8 0 4 4 0 018 0zm-28 0a4 4 0 11-8 0 4 4 0 018 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Нет маршрутов</h3>
                    <p class="mt-1 text-sm text-gray-500">В этом плане пока нет созданных маршрутов.</p>
                </div>
            @endif
        </div>

        <!-- Действия -->
        @if($plan->status === 'draft')
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-700 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4" />
                        </svg>
                        Действия
                    </h3>
                </div>
                <div class="px-6 py-4">
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('admin.departure-planner.edit', $plan) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-medium text-sm text-white hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                            </svg>
                            Редактировать план
                        </a>
                        
                        <form method="POST" action="{{ route('admin.departure-planner.approve', $plan) }}" class="inline">
                            @csrf
                            <button type="submit" onclick="return confirm('Утвердить план? После утверждения редактирование будет невозможно.')" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-medium text-sm text-white hover:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Утвердить план
                            </button>
                        </form>
                        
                        <form method="POST" action="{{ route('admin.departure-planner.destroy', $plan) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Удалить план? Это действие нельзя отменить.')" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-medium text-sm text-white hover:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd" />
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                                Удалить план
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection 