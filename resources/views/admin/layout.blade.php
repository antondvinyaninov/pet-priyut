<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - @yield('title', 'Админ-панель')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        /* Фиксированная ширина сайдбара */
        #sidebar {
            width: 256px !important;
            min-width: 256px !important;
            max-width: 256px !important;
        }
        
        /* Только мобильная адаптация */
        @media (max-width: 767px) {
            .mobile-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease-in-out;
                z-index: 50;
                width: 256px !important;
                min-width: 256px !important;
                max-width: 256px !important;
            }
            
            .mobile-sidebar.open {
                transform: translateX(0);
            }
            
            .mobile-overlay {
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.3s ease-in-out, visibility 0.3s ease-in-out;
                z-index: 40;
            }
            
            .mobile-overlay.open {
                opacity: 1;
                visibility: visible;
            }
        }
        
        /* Планшеты - боковое меню скрыто, контент на всю ширину */
        @media (min-width: 768px) and (max-width: 1023px) {
            .tablet-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease-in-out;
                z-index: 50;
                width: 256px !important;
                min-width: 256px !important;
                max-width: 256px !important;
            }
            
            .tablet-sidebar.open {
                transform: translateX(0);
            }
            
            .tablet-overlay {
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.3s ease-in-out, visibility 0.3s ease-in-out;
                z-index: 40;
            }
            
            .tablet-overlay.open {
                opacity: 1;
                visibility: visible;
            }
        }
        
        /* Анимация гамбургер-меню */
        .hamburger-line {
            transition: all 0.3s ease;
        }
        
        .hamburger.open .hamburger-line:nth-child(1) {
            transform: rotate(45deg) translate(5px, 5px);
        }
        
        .hamburger.open .hamburger-line:nth-child(2) {
            opacity: 0;
        }
        
        .hamburger.open .hamburger-line:nth-child(3) {
            transform: rotate(-45deg) translate(7px, -6px);
        }
    </style>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        <!-- Мобильный overlay -->
        <div id="mobile-overlay" class="fixed inset-0 bg-black bg-opacity-50 lg:hidden mobile-overlay tablet-overlay" onclick="closeMobileMenu()"></div>
        
        <!-- Боковая навигация -->
        <div class="flex">
            <!-- Сайдбар -->
            <div id="sidebar" class="w-64 bg-white border-r border-gray-200 min-h-screen p-4 fixed lg:static mobile-sidebar tablet-sidebar shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3 px-3 py-2">
                        <img src="{{ asset('favicon.svg') }}" alt="АСУП" class="h-6 w-6">
                        <span class="text-gray-800 font-semibold text-lg">АСУП</span>
                    </div>
                    <!-- Кнопка закрытия меню (только на мобильных и планшетах) -->
                    <button id="close-menu-btn" class="text-gray-600 lg:hidden hover:text-gray-800" onclick="closeMobileMenu()">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <nav>
                    <ul class="space-y-1">
                        <li>
                            <a href="{{ route('admin.dashboard') }}" class="text-gray-700 hover:bg-blue-50 hover:text-blue-600 group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
                                <span class="mr-3 text-xl">🏠</span>
                                Главная
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.osvv.index') }}" class="text-gray-700 hover:bg-blue-50 hover:text-blue-600 group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
                                <span class="mr-3 text-xl">📋</span>
                                Журнал заявок
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.animals.index') }}" class="text-gray-700 hover:bg-blue-50 hover:text-blue-600 group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
                                <span class="mr-3 text-xl">🐾</span>
                                ОСВВ
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.analytics.index') }}" class="text-gray-700 hover:bg-blue-50 hover:text-blue-600 group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
                                <span class="mr-3 text-xl">📊</span>
                                Аналитика
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.departure-planner.index') }}" class="text-gray-700 hover:bg-blue-50 hover:text-blue-600 group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
                                <span class="mr-3 text-xl">📅</span>
                                Планировщик выездов
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.employees.index') }}" class="text-gray-700 hover:bg-blue-50 hover:text-blue-600 group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
                                <span class="mr-3 text-xl">👨‍💼</span>
                                Сотрудники
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.veterinary.index') }}" class="text-gray-700 hover:bg-blue-50 hover:text-blue-600 group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
                                <span class="mr-3 text-xl">🏥</span>
                                Ветеринария
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.warehouse.index') }}" class="text-gray-700 hover:bg-blue-50 hover:text-blue-600 group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
                                <span class="mr-3 text-xl">📦</span>
                                Склад
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.reports.index') }}" class="text-gray-700 hover:bg-blue-50 hover:text-blue-600 group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
                                <span class="mr-3 text-xl">📊</span>
                                Отчетность
                            </a>
                        </li>
                        <li class="space-y-1">
                            <button type="button" class="text-gray-700 hover:bg-blue-50 hover:text-blue-600 group flex items-center px-3 py-2 text-sm font-medium rounded-lg w-full flex justify-between transition-colors" 
                                    onclick="toggleSubmenu('tasksSubmenu')" aria-expanded="false" aria-controls="tasksSubmenu">
                                <div class="flex items-center">
                                    <span class="mr-3 text-xl">📋</span>
                                    Задачи
                                </div>
                                <svg class="text-gray-400 ml-3 h-4 w-4 transform transition-transform duration-150 submenu-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div id="tasksSubmenu" class="hidden pl-8 space-y-1">
                                <a href="{{ route('admin.tasks.index') }}" class="text-gray-600 hover:bg-blue-50 hover:text-blue-600 group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
                                    <span class="mr-3 text-lg">📝</span>
                                    Все задачи
                                </a>
                                <a href="{{ route('admin.tasks.create') }}" class="text-gray-600 hover:bg-blue-50 hover:text-blue-600 group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
                                    <span class="mr-3 text-lg">➕</span>
                                    Создать задачу
                                </a>
                            </div>
                        </li>
                        <li class="space-y-1">
                            <button type="button" class="text-gray-700 hover:bg-blue-50 hover:text-blue-600 group flex items-center px-3 py-2 text-sm font-medium rounded-lg w-full flex justify-between transition-colors" 
                                    onclick="toggleSubmenu('animalRegistrySubmenu')" aria-expanded="false" aria-controls="animalRegistrySubmenu">
                                <div class="flex items-center">
                                    <span class="mr-3 text-xl">🐕</span>
                                    Учет животных
                                </div>
                                <svg class="text-gray-400 ml-3 h-4 w-4 transform transition-transform duration-150 submenu-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div id="animalRegistrySubmenu" class="hidden pl-8 space-y-1">
                                <a href="{{ route('admin.animal-registry.cards') }}" class="text-gray-600 hover:bg-blue-50 hover:text-blue-600 group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
                                    <span class="mr-3 text-lg">📇</span>
                                    Карточки
                                </a>
                                <a href="{{ route('admin.animal-registry.cages') }}" class="text-gray-600 hover:bg-blue-50 hover:text-blue-600 group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
                                    <span class="mr-3 text-lg">🏚️</span>
                                    Вольеры
                                </a>
                                <a href="{{ route('admin.animal-registry.documents') }}" class="text-gray-600 hover:bg-blue-50 hover:text-blue-600 group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
                                    <span class="mr-3 text-lg">📄</span>
                                    Документы
                                </a>
                            </div>
                        </li>
                        <li class="space-y-1">
                            <button type="button" class="text-gray-700 hover:bg-blue-50 hover:text-blue-600 group flex items-center px-3 py-2 text-sm font-medium rounded-lg w-full flex justify-between transition-colors" 
                                    onclick="toggleSubmenu('usersSubmenu')" aria-expanded="false" aria-controls="usersSubmenu">
                                <div class="flex items-center">
                                    <span class="mr-3 text-xl">⚙️</span>
                                    Управление
                                </div>
                                <svg class="text-gray-400 ml-3 h-4 w-4 transform transition-transform duration-150 submenu-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div id="usersSubmenu" class="hidden pl-8 space-y-1">
                                <a href="{{ route('admin.users.index') }}" class="text-gray-600 hover:bg-blue-50 hover:text-blue-600 group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
                                    <span class="mr-3 text-lg">👥</span>
                                    Пользователи
                                </a>
                                <a href="{{ route('admin.roles.index') }}" class="text-gray-600 hover:bg-blue-50 hover:text-blue-600 group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
                                    <span class="mr-3 text-lg">🔐</span>
                                    Роли и права
                                </a>
                                <a href="{{ route('admin.menu.index') }}" class="text-gray-600 hover:bg-blue-50 hover:text-blue-600 group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
                                    <span class="mr-3 text-lg">📋</span>
                                    Управление меню
                                </a>
                            </div>
                        </li>
                        <li class="pt-4 mt-4 border-t border-gray-200">
                            <a href="{{ route('profile.edit') }}" class="text-gray-700 hover:bg-blue-50 hover:text-blue-600 group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
                                <span class="mr-3 text-xl">👤</span>
                                Профиль
                            </a>
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="flex items-center">
                                @csrf
                                <button type="submit" class="text-gray-700 hover:bg-red-50 hover:text-red-600 group flex items-center px-3 py-2 text-sm font-medium rounded-lg w-full transition-colors">
                                    <span class="mr-3 text-xl">🚪</span>
                                    Выйти
                                </button>
                            </form>
                        </li>
                    </ul>
                </nav>
            </div>

            <!-- Основное содержимое -->
            <div class="flex-1 w-full lg:w-auto lg:ml-0">
                <!-- Верхняя панель -->
                <div class="bg-white shadow">
                    <div class="px-4 py-6 sm:px-6 lg:px-8">
                        <div class="flex items-center justify-between">
                            <!-- Гамбургер-меню (только на мобильных и планшетах) -->
                            <button id="mobile-menu-btn" class="lg:hidden text-gray-800 hover:text-gray-600" onclick="openMobileMenu()">
                                <div class="hamburger">
                                    <div class="w-6 h-0.5 bg-current hamburger-line"></div>
                                    <div class="w-6 h-0.5 bg-current mt-1 hamburger-line"></div>
                                    <div class="w-6 h-0.5 bg-current mt-1 hamburger-line"></div>
                                </div>
                            </button>
                            
                            <h1 class="text-2xl font-semibold text-gray-900 lg:text-left text-center flex-1 lg:flex-none">
                                @hasSection('header')
                                    @yield('header')
                                @elseif(View::hasSection('title'))
                                    @yield('title')
                                @else
                                    Админ-панель
                                @endif
                            </h1>
                            
                            <!-- Пустой div для выравнивания на мобильных и планшетах -->
                            <div class="lg:hidden w-6"></div>
                        </div>
                    </div>
                </div>

                <!-- Контент страницы -->
                <main class="@yield('main_padding', 'px-4 py-6 sm:px-6') w-full">
                    @yield('content')
                </main>
            </div>
        </div>
    </div>
    
    <script>
        // Функции для мобильного меню
        function openMobileMenu() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobile-overlay');
            const hamburger = document.querySelector('.hamburger');
            
            sidebar.classList.add('open');
            overlay.classList.add('open');
            hamburger.classList.add('open');
            
            // Блокируем скролл body
            document.body.style.overflow = 'hidden';
        }
        
        function closeMobileMenu() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobile-overlay');
            const hamburger = document.querySelector('.hamburger');
            
            sidebar.classList.remove('open');
            overlay.classList.remove('open');
            hamburger.classList.remove('open');
            
            // Разблокируем скролл body
            document.body.style.overflow = '';
        }
        
        // Закрытие меню при изменении размера экрана
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                closeMobileMenu();
            }
        });
        
        function toggleSubmenu(id) {
            const submenu = document.getElementById(id);
            const chevron = event.currentTarget.querySelector('.submenu-chevron');
            
            if (submenu.classList.contains('hidden')) {
                submenu.classList.remove('hidden');
                chevron.classList.add('rotate-180');
            } else {
                submenu.classList.add('hidden');
                chevron.classList.remove('rotate-180');
            }
        }
        
        // Автоматически открываем активное подменю при загрузке страницы
        document.addEventListener('DOMContentLoaded', function() {
            const currentUrl = window.location.pathname;
            
            // Проверяем, находимся ли мы в разделе ОСВВ
            if (currentUrl.includes('/admin/osvv')) {
                const osvvSubmenu = document.getElementById('osvvSubmenu');
                const chevron = osvvSubmenu.previousElementSibling.querySelector('.submenu-chevron');
                
                if (osvvSubmenu) {
                    osvvSubmenu.classList.remove('hidden');
                    chevron.classList.add('rotate-180');
                }
            }

            // Проверяем, находимся ли мы в разделе задач
            if (currentUrl.includes('/admin/tasks')) {
                const tasksSubmenu = document.getElementById('tasksSubmenu');
                const chevron = tasksSubmenu.previousElementSibling.querySelector('.submenu-chevron');
                
                if (tasksSubmenu) {
                    tasksSubmenu.classList.remove('hidden');
                    chevron.classList.add('rotate-180');
                }
            }
            
            // Проверяем, находимся ли мы в разделе управления пользователями/ролями
            if (currentUrl.includes('/admin/users') || currentUrl.includes('/admin/roles')) {
                const usersSubmenu = document.getElementById('usersSubmenu');
                const chevron = usersSubmenu.previousElementSibling.querySelector('.submenu-chevron');
                
                if (usersSubmenu) {
                    usersSubmenu.classList.remove('hidden');
                    chevron.classList.add('rotate-180');
                }
            }
            
            // Проверяем, находимся ли мы в разделе учета животных или актов приема-передачи
            if (currentUrl.includes('/admin/animal-registry') || currentUrl.includes('/admin/animal-transfer-acts')) {
                const animalRegistrySubmenu = document.getElementById('animalRegistrySubmenu');
                const chevron = animalRegistrySubmenu.previousElementSibling.querySelector('.submenu-chevron');
                
                if (animalRegistrySubmenu) {
                    animalRegistrySubmenu.classList.remove('hidden');
                    chevron.classList.add('rotate-180');
                }
            }
        });
    </script>
    
    <!-- Дополнительные скрипты страниц -->
    @stack('scripts')
</body>
</html> 