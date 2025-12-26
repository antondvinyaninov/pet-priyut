@extends('admin.layout')

@section('header', 'Карта выездов на сегодня')

@section('content')
<div class="space-y-6">
    <!-- Заголовок -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-4">
            <h3 class="text-xl font-bold text-white flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                </svg>
                Интерактивная карта выездов
            </h3>
        </div>
    </div>

    <!-- Панель управления -->
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex flex-wrap gap-4 mb-4">
            <button id="showAllZones" onclick="showAllZones()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Показать все зоны
            </button>
            <button id="clearSelection" onclick="clearRouteSelection()" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                Сбросить выбор
            </button>
            <button id="optimizeRoutes" onclick="optimizeAllRoutes()" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                Оптимизировать маршруты
            </button>
            <button onclick="testRouteBuilding()" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
                🧪 Тест маршрута
            </button>
            <button onclick="refreshCurrentRoute()" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                🔄 Обновить маршрут
            </button>
            <div class="relative">
                <button id="exportRoutes" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Экспорт маршрутов
                </button>
                <div id="exportMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10">
                    <div class="py-1">
                        <a href="#" onclick="exportToYandex()" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            📍 Яндекс.Навигатор
                        </a>
                        <a href="#" onclick="exportToGoogle()" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            🗺️ Google Maps
                        </a>
                        <a href="#" onclick="exportToPDF()" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            📄 PDF маршрутный лист
                        </a>
                        <a href="#" onclick="exportToExcel()" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            📊 Excel таблица
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Информационное сообщение -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Как работать с картой</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Кликните на маршрут в списке справа, чтобы увидеть оптимальный путь на карте</li>
                            <li>Синяя линия показывает порядок объезда точек от приюта и обратно</li>
                            <li>Синие кружки с номерами в списке и на карте показывают последовательность посещения</li>
                            <li>Адрес каждой точки указан сразу после номера заявки для удобства</li>
                            <li><strong>Кнопка "Оптимизировать маршруты"</strong> перегруппирует заявки по географической близости и создаст более эффективные маршруты</li>
                            <li>Используйте кнопки "Показать все зоны" и "Сбросить выбор" для управления</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Карта -->
        <div id="map" class="w-full h-96 rounded-lg border border-gray-300" style="min-height: 400px;"></div>
    </div>

    <!-- Боковая панель с деталями -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <!-- Список маршрутов -->
            <div id="routesList" class="space-y-4">
                <!-- Динамически заполняется JS -->
            </div>
        </div>
        
        <div class="space-y-4">
            <!-- Статистика -->
            <div class="bg-white shadow rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 mb-3">Статистика маршрутов</h4>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Всего зон:</span>
                        <span id="totalZones" class="font-medium">0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Общее время:</span>
                        <span id="totalTime" class="font-medium">0ч 0мин</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Расстояние:</span>
                        <span id="totalDistance" class="font-medium">0 км</span>
                    </div>
                </div>
            </div>

            <!-- Фильтры -->
            <div class="bg-white shadow rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 mb-3">Фильтры</h4>
                <div class="space-y-3">
                    <label class="flex items-center">
                        <input type="checkbox" id="showUrgent" checked class="rounded border-gray-300">
                        <span class="ml-2 text-sm">Срочные заявки</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" id="showToday" checked class="rounded border-gray-300">
                        <span class="ml-2 text-sm">На сегодня</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" id="showOverdue" checked class="rounded border-gray-300">
                        <span class="ml-2 text-sm">Просроченные</span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
console.log('🚀 НАЧАЛО ВЫПОЛНЕНИЯ СКРИПТА КАРТЫ');
console.log('Текущий URL:', window.location.href);
console.log('Время загрузки:', new Date().toLocaleTimeString());
</script>
<script src="https://api-maps.yandex.ru/2.1/?apikey=aba2bc56-907f-41a7-9377-d32e69eff205&lang=ru_RU" type="text/javascript" 
        onload="console.log('✅ Яндекс.Карты API загружен успешно')" 
        onerror="console.error('❌ Ошибка загрузки Яндекс.Карт API')"></script>
<script>
console.log('Скрипт карты загружен, ожидаем готовности ymaps...');

// Глобальные переменные
let map = null;
let shelterCoords = [51.6845, 39.2156]; // Балашовская 29/1, Левобережный район
let currentZones = []; // Хранение данных зон
let selectedZoneIndex = null; // Индекс выбранной зоны
let routeObjects = []; // Объекты маршрута на карте

// Глобальные функции для работы со списками (работают без карты)
function showNoRoutesMessage() {
    const routesList = document.getElementById('routesList');
    routesList.innerHTML = `
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
            <div class="text-yellow-600 mb-2">
                <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-yellow-800 mb-2">Нет маршрутов на сегодня</h3>
            <p class="text-yellow-700">
                На данный момент нет заявок, требующих выезда. 
                Проверьте позже или создайте новые заявки.
            </p>
        </div>
    `;
}

function showErrorMessage() {
    const routesList = document.getElementById('routesList');
    routesList.innerHTML = `
        <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
            <div class="text-red-600 mb-2">
                <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-red-800 mb-2">Ошибка загрузки данных</h3>
            <p class="text-red-700">Не удалось загрузить данные маршрутов. Попробуйте обновить страницу.</p>
        </div>
    `;
}

function displayRoutesList(zones) {
    const routesList = document.getElementById('routesList');
    
    if (!zones || zones.length === 0) {
        showNoRoutesMessage();
        return;
    }
    
    let html = '';
    
    zones.forEach((zone, index) => {
        const priorityColor = zone.priority_level >= 10 ? 'red' : 
                             zone.priority_level >= 5 ? 'yellow' : 'green';
        const priorityText = zone.priority_level >= 10 ? 'Критично' : 
                            zone.priority_level >= 5 ? 'Срочно' : 'Плановый';
        
        html += `
            <div id="zone-${index}" class="bg-white shadow rounded-lg p-6 border-l-4 border-${priorityColor}-500 cursor-pointer hover:shadow-lg transition-shadow route-zone" 
                 onclick="selectRoute(${index})" data-zone-index="${index}">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        🚗 Маршрут ${index + 1} - ${zone.center_request.district || 'Район не указан'} (${zone.requests.length} точек)
                    </h3>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 text-xs font-medium rounded-full bg-${priorityColor}-100 text-${priorityColor}-800">
                            ${priorityText}
                        </span>
                        <button class="px-3 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700" 
                                onclick="event.stopPropagation(); selectRoute(${index})">
                            Показать маршрут
                        </button>
                    </div>
                </div>
                
                <div class="grid grid-cols-3 gap-4 mb-4 text-sm">
                    <div>
                        <span class="text-gray-600">Точек:</span>
                        <span class="font-medium">${zone.requests.length}</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Время:</span>
                        <span class="font-medium">${Math.floor(zone.estimated_time / 60)}ч ${zone.estimated_time % 60}мин</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Приоритет:</span>
                        <span class="font-medium">${zone.priority_level}</span>
                    </div>
                </div>
                
                <div class="space-y-3">
                    ${zone.requests.map((request, reqIndex) => `
                        <div class="bg-gray-50 rounded-lg p-3 border">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="font-medium text-gray-900">
                                    <span class="inline-flex items-center justify-center w-6 h-6 bg-blue-600 text-white text-xs font-bold rounded-full mr-2">
                                        ${reqIndex + 1}
                                    </span>
                                    #${request.id} - ${request.location_address}
                                </h4>
                                <div class="flex gap-1">
                                    ${request.has_bite ? '<span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded">УКУС</span>' : ''}
                                    ${request.is_pregnant ? '<span class="px-2 py-1 text-xs bg-pink-100 text-pink-800 rounded">БЕРЕМЕННОСТЬ</span>' : ''}
                                    ${request.animals_count > 1 ? `<span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">${request.animals_count} животных</span>` : ''}
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 mb-2">
                                <strong>Контакт:</strong> ${request.contact_name} (${request.contact_phone})
                            </p>
                            <div class="flex gap-2">
                                <a href="/admin/osvv/${request.id}" 
                                   class="text-xs bg-blue-600 text-white px-2 py-1 rounded hover:bg-blue-700">
                                    Открыть заявку
                                </a>
                                ${request.latitude && request.longitude ? `
                                    <a href="https://yandex.ru/maps/?rtext=Балашовская 29/1~${encodeURIComponent(request.location_address)}" 
                                       target="_blank"
                                       class="text-xs bg-green-600 text-white px-2 py-1 rounded hover:bg-green-700">
                                        Маршрут
                                    </a>
                                ` : ''}
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    });
    
    routesList.innerHTML = html;
}

function updateStatistics(data) {
    document.getElementById('totalZones').textContent = data.zones.length;
    
    let totalTime = 0;
    data.zones.forEach(zone => {
        totalTime += zone.estimated_time;
    });
    
    const hours = Math.floor(totalTime / 60);
    const minutes = totalTime % 60;
    document.getElementById('totalTime').textContent = `${hours}ч ${minutes}мин`;
}

function loadDepartureRoutes() {
    console.log('Начинаем загрузку данных маршрутов...');
    
    fetch('/admin/osvv/departure-routes-data')
        .then(response => {
            console.log('Получен ответ от сервера:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Данные с сервера:', data);
            
            if (data.debug) {
                console.log('Отладочная информация:', data.debug);
            }
            
            if (!data.zones || data.zones.length === 0) {
                console.log('Нет зон для отображения');
                showNoRoutesMessage();
                return;
            }
            
            console.log(`Найдено ${data.zones.length} зон для отображения`);
            
            // Сохраняем данные зон
            currentZones = data.zones;
            
            // Проверяем, есть ли заявки с координатами
            let requestsWithCoords = 0;
            data.zones.forEach(zone => {
                zone.requests.forEach(request => {
                    if (request.latitude && request.longitude) {
                        requestsWithCoords++;
                    }
                });
            });
            
            console.log(`Заявок с координатами: ${requestsWithCoords}`);
            
            // Отображаем список в любом случае
            displayRoutesList(data.zones);
            updateStatistics(data);
            
            // Если карта доступна, отображаем маршруты на карте
            if (map && requestsWithCoords > 0) {
                displayRoutesOnMap(data.zones);
            }
        })
        .catch(error => {
            console.error('Ошибка загрузки данных:', error);
            showErrorMessage();
        });
}

// Функция выбора маршрута
function selectRoute(zoneIndex) {
    console.log('Выбран маршрут:', zoneIndex);
    
    if (!currentZones || !currentZones[zoneIndex]) {
        console.error('Зона не найдена:', zoneIndex);
        return;
    }
    
    // Снимаем выделение с предыдущей зоны
    if (selectedZoneIndex !== null) {
        const prevZone = document.getElementById(`zone-${selectedZoneIndex}`);
        if (prevZone) {
            prevZone.classList.remove('ring-2', 'ring-blue-500', 'bg-blue-50');
        }
    }
    
    // Выделяем новую зону
    selectedZoneIndex = zoneIndex;
    const currentZone = document.getElementById(`zone-${zoneIndex}`);
    if (currentZone) {
        currentZone.classList.add('ring-2', 'ring-blue-500', 'bg-blue-50');
    }
    
    // Если карта доступна, показываем маршрут
    if (map) {
        showRouteOnMap(zoneIndex);
    }
    
    // Обновляем статистику для выбранной зоны
    updateRouteStatistics(currentZones[zoneIndex]);
}

// Функция отображения маршрута на карте с использованием API маршрутизации
function showRouteOnMap(zoneIndex) {
    if (!map || !currentZones || !currentZones[zoneIndex]) {
        console.error('Нет карты или зоны для отображения маршрута');
        return;
    }
    
    console.log('🗺️ Отображаем маршрут на карте для зоны:', zoneIndex);
    
    // Очищаем предыдущий маршрут
    clearRouteFromMap();
    
    const zone = currentZones[zoneIndex];
    console.log('Данные зоны:', zone);
    
    // Проверяем заявки с координатами (учитываем новый формат с addresses)
    const requestsWithCoords = zone.requests.filter(req => {
        if (req.addresses && req.addresses.length > 0) {
            // Новый формат: проверяем, есть ли хотя бы один адрес с координатами
            return req.addresses.some(addr => addr.latitude && addr.longitude);
        } else {
            // Старый формат: проверяем основные координаты
            return req.latitude && req.longitude;
        }
    });
    
    console.log('Заявки с координатами:', requestsWithCoords.length, requestsWithCoords);
    
    if (requestsWithCoords.length === 0) {
        alert('У заявок в этой зоне нет координат для построения маршрута');
        return;
    }
    
    // Строим оптимальный маршрут
    console.log('🔄 Строим оптимальный маршрут...');
    const routePoints = buildOptimalRoute(requestsWithCoords);
    console.log('Точки маршрута:', routePoints);
    
    if (routePoints.length === 0) {
        alert('Не удалось построить маршрут - нет действительных координат');
        return;
    }
    
    // Добавляем приют в начало и конец
    const fullRoute = [shelterCoords, ...routePoints, shelterCoords];
    console.log('Полный маршрут (с приютом):', fullRoute);
    
    // Строим маршрут по дорогам с помощью API Яндекс.Карт
    buildRoadRoute(fullRoute, zoneIndex);
}

// Функция построения маршрута по дорогам
function buildRoadRoute(waypoints, zoneIndex) {
    console.log('🛣️ Строим маршрут по дорогам для точек:', waypoints);
    
    if (waypoints.length < 2) {
        console.error('Недостаточно точек для построения маршрута');
        return;
    }
    
    // Создаем мультимаршрут для построения пути по дорогам
    const multiRoute = new ymaps.multiRouter.MultiRoute({
        referencePoints: waypoints,
        params: {
            routingMode: 'auto', // автомобильный маршрут
            avoidTrafficJams: false
        }
    }, {
        // Настройки отображения
        boundsAutoApply: false, // ВАЖНО: отключаем автоматическое центрирование
        routeActiveStrokeWidth: 6,
        routeActiveStrokeColor: '#2563eb',
        routeStrokeWidth: 4,
        routeStrokeColor: '#2563eb',
        routeStrokeOpacity: 0.8,
        // Отключаем интерактивность маршрута
        editorDrawOver: false,
        editorMidPointsEnabled: false,
        wayPointDraggable: false,
        wayPointVisible: false,
        // Дополнительные настройки для стабильности
        routeStrokeStyle: 'solid',
        routeActiveStrokeStyle: 'solid'
    });
    
    console.log('Мультимаршрут создан:', multiRoute);
    
    // Добавляем маршрут на карту
    map.geoObjects.add(multiRoute);
    routeObjects.push(multiRoute);
    
    // Добавляем номера точек (исключая приют в начале и конце)
    const routePoints = waypoints.slice(1, -1); // убираем первую и последнюю точки (приют)
    routePoints.forEach((point, index) => {
        const numberPlacemark = new ymaps.Placemark(point, {
            iconContent: (index + 1).toString(),
            hintContent: `Точка ${index + 1}`,
            balloonContent: `
                <div style="padding: 8px;">
                    <h4 style="margin: 0 0 8px 0;">Точка ${index + 1}</h4>
                    <p style="margin: 0;">Координаты: ${point[0].toFixed(6)}, ${point[1].toFixed(6)}</p>
                </div>
            `
        }, {
            preset: 'islands#blueCircleIcon',
            iconColor: '#2563eb'
        });
        
        map.geoObjects.add(numberPlacemark);
        routeObjects.push(numberPlacemark);
    });
    
    // Обработчик готовности маршрута
    multiRoute.model.events.add('requestsuccess', function () {
        console.log('✅ Маршрут по дорогам построен успешно');
        
        // Получаем информацию о маршруте
        const routes = multiRoute.getRoutes();
        if (routes.getLength() > 0) {
            const route = routes.get(0);
            const distance = route.properties.get('distance');
            const duration = route.properties.get('duration');
            
            console.log(`📊 Расстояние: ${(distance.value / 1000).toFixed(1)} км`);
            console.log(`⏱️ Время в пути: ${Math.round(duration.value / 60)} мин`);
            
            // Обновляем статистику
            updateRouteInfo(distance.value / 1000, duration.value / 60);
        }
        
        // Центрируем карту на маршруте с небольшой задержкой
        setTimeout(() => {
            const bounds = multiRoute.getBounds();
            if (bounds) {
                map.setBounds(bounds, { 
                    checkZoomRange: true, 
                    zoomMargin: [50, 50, 50, 50],
                    duration: 500 
                });
            }
        }, 100);
    });
    
    // Обработчик ошибки построения маршрута
    multiRoute.model.events.add('requestfail', function (event) {
        console.error('❌ Ошибка построения маршрута:', event);
        alert('Не удалось построить маршрут по дорогам. Возможно, некоторые точки недоступны для автомобильного транспорта.');
        
        // Fallback: строим простую линию
        buildSimpleRoute(waypoints, zoneIndex);
    });
    
    console.log('🔢 Добавлены номера точек:', routePoints.length);
}

// Функция построения простого маршрута (fallback)
function buildSimpleRoute(waypoints, zoneIndex) {
    console.log('📍 Строим простой маршрут (fallback)');
    
    const routeLine = new ymaps.Polyline(waypoints, {
        hintContent: `Маршрут зоны ${zoneIndex + 1} (прямые линии)`
    }, {
        strokeColor: '#f59e0b',
        strokeWidth: 4,
        strokeOpacity: 0.8,
        strokeStyle: 'shortdash'
    });
    
    map.geoObjects.add(routeLine);
    routeObjects.push(routeLine);
    
    // Центрируем карту
    const bounds = routeLine.geometry.getBounds();
    if (bounds) {
        map.setBounds(bounds, { checkZoomRange: true, zoomMargin: 50 });
    }
}

// Функция обновления информации о маршруте
function updateRouteInfo(distance, duration) {
    const distanceElement = document.getElementById('totalDistance');
    const timeElement = document.getElementById('totalTime');
    
    if (distanceElement) {
        distanceElement.textContent = `${distance.toFixed(1)} км`;
    }
    
    if (timeElement) {
        const hours = Math.floor(duration / 60);
        const minutes = Math.round(duration % 60);
        timeElement.textContent = `${hours}ч ${minutes}мин`;
    }
}

// Функция вычисления расстояния между двумя точками в километрах
function calculateDistance(lat1, lon1, lat2, lon2) {
    const earthRadius = 6371; // Радиус Земли в км
    
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) + 
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
              Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    
    return earthRadius * c;
}

// Функция построения оптимального маршрута (простой алгоритм ближайшего соседа)
function buildOptimalRoute(requests) {
    if (requests.length === 0) {
        return [];
    }
    
    // Собираем все точки из всех заявок (основные и дополнительные адреса)
    const allPoints = [];
    
    requests.forEach(request => {
        if (request.addresses && request.addresses.length > 0) {
            // Новый формат: используем все адреса заявки
            request.addresses.forEach(address => {
                if (address.latitude && address.longitude) {
                    allPoints.push({
                        coords: [address.latitude, address.longitude],
                        request: request,
                        address: address,
                        isMainAddress: address.is_primary
                    });
                }
            });
        } else if (request.latitude && request.longitude) {
            // Старый формат: только основной адрес
            allPoints.push({
                coords: [request.latitude, request.longitude],
                request: request,
                address: {
                    address: request.location_address,
                    is_primary: true
                },
                isMainAddress: true
            });
        }
    });
    
    if (allPoints.length === 0) {
        return [];
    }
    
    if (allPoints.length === 1) {
        return [allPoints[0].coords];
    }
    
    // Алгоритм ближайшего соседа для оптимизации маршрута
    const route = [];
    let currentPoint = shelterCoords;
    const remainingPoints = [...allPoints];
    
    while (remainingPoints.length > 0) {
        // Находим ближайшую точку
        let nearestIndex = 0;
        let minDistance = calculateDistance(
            currentPoint[0], currentPoint[1],
            remainingPoints[0].coords[0], remainingPoints[0].coords[1]
        );
        
        for (let i = 1; i < remainingPoints.length; i++) {
            const distance = calculateDistance(
                currentPoint[0], currentPoint[1],
                remainingPoints[i].coords[0], remainingPoints[i].coords[1]
            );
            
            if (distance < minDistance) {
                minDistance = distance;
                nearestIndex = i;
            }
        }
        
        // Добавляем ближайшую точку в маршрут
        const nearestPoint = remainingPoints.splice(nearestIndex, 1)[0];
        route.push(nearestPoint.coords);
        currentPoint = nearestPoint.coords;
    }
    
    return route;
}

// Функция очистки маршрута с карты
function clearRouteFromMap() {
    if (!map) {
        console.log('🚫 Нет карты для очистки маршрута');
        return;
    }
    
    console.log('🧹 Очищаем предыдущий маршрут, объектов:', routeObjects.length);
    
    routeObjects.forEach((obj, index) => {
        try {
            console.log(`Удаляем объект ${index}:`, obj);
            
            // Проверяем тип объекта и удаляем соответствующим образом
            if (obj && typeof obj.setMap === 'function') {
                // Для мультимаршрутов
                obj.setMap(null);
            } else if (obj && map.geoObjects) {
                // Для обычных геообъектов
                map.geoObjects.remove(obj);
            }
        } catch (error) {
            console.warn(`Ошибка при удалении объекта ${index}:`, error);
        }
    });
    
    routeObjects = [];
    console.log('✅ Маршрут очищен');
}

// Функция обновления статистики для выбранного маршрута
function updateRouteStatistics(zone) {
    if (!zone) return;
    
    const requestsWithCoords = zone.requests.filter(req => req.latitude && req.longitude);
    const totalDistance = calculateRouteDistance(requestsWithCoords);
    
    // Обновляем статистику в боковой панели
    document.getElementById('totalZones').textContent = `1 (выбрана)`;
    document.getElementById('totalTime').textContent = `${Math.floor(zone.estimated_time / 60)}ч ${zone.estimated_time % 60}мин`;
    document.getElementById('totalDistance').textContent = `${totalDistance.toFixed(1)} км`;
}

// Функция расчета общего расстояния маршрута
function calculateRouteDistance(requests) {
    if (requests.length === 0) return 0;
    
    const routePoints = buildOptimalRoute(requests);
    const fullRoute = [shelterCoords, ...routePoints, shelterCoords];
    
    let totalDistance = 0;
    for (let i = 0; i < fullRoute.length - 1; i++) {
        totalDistance += calculateDistance(
            fullRoute[i][0], fullRoute[i][1],
            fullRoute[i + 1][0], fullRoute[i + 1][1]
        );
    }
    
    return totalDistance;
}

// Функция показа всех зон
function showAllZones() {
    console.log('Показываем все зоны');
    
    // Сбрасываем выбор
    clearRouteSelection();
    
    // Если карта доступна, отображаем все маршруты
    if (map && currentZones) {
        displayRoutesOnMap(currentZones);
        
        // Центрируем карту на всех точках
        const bounds = map.geoObjects.getBounds();
        if (bounds) {
            map.setBounds(bounds, { checkZoomRange: true, zoomMargin: 50 });
        }
    }
    
    // Восстанавливаем общую статистику
    updateStatistics({ zones: currentZones });
}

// Функция сброса выбора маршрута
function clearRouteSelection() {
    console.log('Сбрасываем выбор маршрута');
    
    // Снимаем выделение с зоны
    if (selectedZoneIndex !== null) {
        const prevZone = document.getElementById(`zone-${selectedZoneIndex}`);
        if (prevZone) {
            prevZone.classList.remove('ring-2', 'ring-blue-500', 'bg-blue-50');
        }
    }
    
    selectedZoneIndex = null;
    
    // Очищаем маршрут с карты
    clearRouteFromMap();
    
    // Восстанавливаем общую статистику
    if (currentZones) {
        updateStatistics({ zones: currentZones });
    }
}

function showMapFallback() {
    console.log('Показываем fallback для карты');
    const mapContainer = document.getElementById('map');
    mapContainer.innerHTML = `
        <div class="flex items-center justify-center h-full bg-gray-100 rounded-lg">
            <div class="text-center p-8">
                <div class="text-gray-500 mb-4">
                    <svg class="mx-auto h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Карта временно недоступна</h3>
                <p class="text-gray-600 mb-4">Используйте список маршрутов ниже для планирования выездов</p>
                <button onclick="location.reload()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Обновить страницу
                </button>
            </div>
        </div>
    `;
    
    // Все равно загружаем данные для списка
    loadDepartureRoutes();
}

// Функции для работы с картой (только если ymaps доступен)
function displayRoutesOnMap(zones) {
    if (!map) return;
    
    // Очищаем предыдущие объекты (кроме приюта)
    map.geoObjects.each(function(geoObject) {
        if (geoObject.properties && geoObject.properties.get('type') !== 'shelter') {
            map.geoObjects.remove(geoObject);
        }
    });
    
    zones.forEach((zone, index) => {
        const color = zone.priority_level >= 10 ? '#ef4444' : 
                     zone.priority_level >= 5 ? '#f59e0b' : '#10b981';
        
        zone.requests.forEach((request, reqIndex) => {
            // Добавляем метки для всех адресов заявки
            if (request.addresses && request.addresses.length > 0) {
                request.addresses.forEach((address, addrIndex) => {
                    if (address.latitude && address.longitude) {
                        const addressCoords = [address.latitude, address.longitude];
                        
                        // Определяем цвет и иконку для адреса
                        const isMainAddress = address.is_primary;
                        const addressColor = isMainAddress ? color : '#6b7280'; // серый для дополнительных
                        const iconPreset = isMainAddress ? 'islands#circleIcon' : 'islands#dotIcon';
                        
                        const placemark = new ymaps.Placemark(
                            addressCoords,
                            {
                                balloonContent: `
                                    <div style="padding: 10px; min-width: 250px;">
                                        <h4 style="margin: 0 0 8px 0; color: #1f2937;">
                                            <span style="display: inline-flex; align-items: center; justify-content: center; width: 20px; height: 20px; background: ${isMainAddress ? '#2563eb' : '#6b7280'}; color: white; font-size: 11px; font-weight: bold; border-radius: 50%; margin-right: 8px;">
                                                ${reqIndex + 1}${isMainAddress ? '' : '.' + (addrIndex + 1)}
                                            </span>
                                            #${request.id} - ${isMainAddress ? 'Основной адрес' : 'Дополнительный адрес ' + addrIndex}
                                        </h4>
                                        <p style="margin: 4px 0;"><strong>Адрес:</strong> ${address.address}</p>
                                        ${address.landmark ? `<p style="margin: 4px 0;"><strong>Ориентир:</strong> ${address.landmark}</p>` : ''}
                                        <p style="margin: 4px 0;"><strong>Контакт:</strong> ${request.contact_name}</p>
                                        <p style="margin: 4px 0;"><strong>Телефон:</strong> ${request.contact_phone}</p>
                                        ${request.has_bite ? '<p style="margin: 4px 0; color: #dc2626;"><strong>⚠️ ЕСТЬ УКУС!</strong></p>' : ''}
                                        ${request.is_pregnant ? '<p style="margin: 4px 0; color: #f59e0b;"><strong>🤰 Беременная</strong></p>' : ''}
                                        ${request.animals_count > 1 ? `<p style="margin: 4px 0;"><strong>Животных:</strong> ${request.animals_count}</p>` : ''}
                                        <p style="margin: 4px 0; font-size: 12px; color: #6b7280;">
                                            Всего адресов в заявке: ${request.total_addresses_count}
                                        </p>
                                        <div style="margin-top: 12px;">
                                            <a href="/admin/osvv/${request.id}" style="color: #2563eb; text-decoration: none;">
                                                📋 Открыть заявку
                                            </a>
                                            <br><br>
                                            <button onclick="selectRoute(${index})" style="background: #2563eb; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer;">
                                                Выбрать маршрут зоны ${index + 1}
                                            </button>
                                        </div>
                                    </div>
                                `,
                                hintContent: `${isMainAddress ? 'Основной' : 'Доп.'} адрес #${request.id}: ${address.address}`,
                                type: 'request'
                            },
                            {
                                preset: iconPreset,
                                iconColor: addressColor
                            }
                        );
                        
                        map.geoObjects.add(placemark);
                    }
                });
            } else {
                // Fallback для старого формата данных (только основной адрес)
                if (request.latitude && request.longitude) {
                    const requestCoords = [request.latitude, request.longitude];
                    
                    const placemark = new ymaps.Placemark(
                        requestCoords,
                        {
                            balloonContent: `
                                <div style="padding: 10px; min-width: 250px;">
                                    <h4 style="margin: 0 0 8px 0; color: #1f2937;">
                                        <span style="display: inline-flex; align-items: center; justify-content: center; width: 20px; height: 20px; background: #2563eb; color: white; font-size: 11px; font-weight: bold; border-radius: 50%; margin-right: 8px;">
                                            ${reqIndex + 1}
                                        </span>
                                        #${request.id} - ${request.location_address}
                                    </h4>
                                    <p style="margin: 4px 0;"><strong>Контакт:</strong> ${request.contact_name}</p>
                                    <p style="margin: 4px 0;"><strong>Телефон:</strong> ${request.contact_phone}</p>
                                    <div style="margin-top: 12px;">
                                        <a href="/admin/osvv/${request.id}" style="color: #2563eb; text-decoration: none;">
                                            📋 Открыть заявку
                                        </a>
                                        <br><br>
                                        <button onclick="selectRoute(${index})" style="background: #2563eb; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer;">
                                            Выбрать маршрут зоны ${index + 1}
                                        </button>
                                    </div>
                                </div>
                            `,
                            hintContent: `Точка ${reqIndex + 1}: #${request.id} - ${request.location_address}`,
                            type: 'request'
                        },
                        {
                            preset: 'islands#circleIcon',
                            iconColor: color
                        }
                    );
                    
                    map.geoObjects.add(placemark);
                }
            }
        });
    });
}

// Инициализация
console.log('Проверяем доступность ymaps...');
console.log('DOM готов:', document.readyState);
console.log('Контейнер карты существует:', !!document.getElementById('map'));

// Fallback если ymaps не загрузился
setTimeout(function() {
    if (typeof ymaps === 'undefined') {
        console.error('Яндекс.Карты не загрузились за 5 секунд, показываем fallback');
        showMapFallback();
    }
}, 5000);

// Проверяем, загружен ли ymaps
if (typeof ymaps !== 'undefined') {
    console.log('ymaps уже доступен');
    ymaps.ready(initializeMap);
} else {
    console.log('Ожидаем загрузки ymaps...');
    // Если ymaps не загрузился сразу, показываем данные без карты
    setTimeout(function() {
        if (typeof ymaps === 'undefined') {
            console.log('ymaps не загрузился, показываем только список');
            loadDepartureRoutes();
        }
    }, 2000);
}

// Принудительная загрузка данных через 3 секунды в любом случае
setTimeout(function() {
    console.log('⏰ Принудительная загрузка данных через 3 секунды');
    if (!document.querySelector('#routesList .bg-white')) {
        console.log('Данные еще не загружены, загружаем принудительно');
        loadDepartureRoutes();
    } else {
        console.log('Данные уже загружены');
    }
}, 3000);

function initializeMap() {
    console.log('Яндекс.Карты готовы к работе!');
    
    // Проверяем, что контейнер карты существует
    const mapContainer = document.getElementById('map');
    console.log('Контейнер карты найден:', mapContainer);
    
    if (!mapContainer) {
        console.error('Контейнер с id="map" не найден!');
        loadDepartureRoutes();
        return;
    }
    
    try {
        map = new ymaps.Map('map', {
            center: [51.661535, 39.200287], // Воронеж
            zoom: 11,
            controls: ['zoomControl', 'fullscreenControl']
        });
        
        console.log('Карта создана успешно:', map);
        
        // Отключаем поведения карты, которые могут мешать маршруту
        map.behaviors.disable('scrollZoom'); // отключаем зум колесиком
        map.behaviors.enable('scrollZoom'); // включаем обратно, но теперь он не будет сбрасывать маршрут
        
        // Добавляем обработчики событий карты для отладки
        map.events.add('boundschange', function(e) {
            console.log('🔄 Границы карты изменились, но маршрут должен остаться');
        });
        
        // Добавляем метку приюта
        const shelterPlacemark = new ymaps.Placemark(
            shelterCoords,
            {
                balloonContent: `
                    <div style="padding: 10px;">
                        <h4 style="margin: 0 0 8px 0; color: #10b981;">🏠 Приют ОСВВ</h4>
                        <p><strong>Адрес:</strong> Балашовская 29/1</p>
                        <p><strong>Статус:</strong> Стартовая точка маршрутов</p>
                    </div>
                `,
                hintContent: 'Приют ОСВВ - стартовая точка',
                type: 'shelter'
            },
            {
                preset: 'islands#homeIcon',
                iconColor: '#10b981'
            }
        );
        
        map.geoObjects.add(shelterPlacemark);
        
        // Ждем полной загрузки карты
        map.events.add('ready', function() {
            console.log('Карта полностью загружена и готова к использованию');
            // Загружаем данные после готовности карты
            loadDepartureRoutes();
        });
        
    } catch (error) {
        console.error('Ошибка создания карты:', error);
        loadDepartureRoutes();
    }
}

// Обработчики экспорта
document.addEventListener('DOMContentLoaded', function() {
    const exportButton = document.getElementById('exportRoutes');
    const exportMenu = document.getElementById('exportMenu');
    
    if (exportButton && exportMenu) {
        exportButton.addEventListener('click', function() {
            exportMenu.classList.toggle('hidden');
        });
        
        // Закрытие меню при клике вне его
        document.addEventListener('click', function(event) {
            if (!exportButton.contains(event.target) && !exportMenu.contains(event.target)) {
                exportMenu.classList.add('hidden');
            }
        });
    }
});

// Функции экспорта
function exportToYandex() {
    fetch('/admin/osvv/departure-routes-data')
        .then(response => response.json())
        .then(data => {
            let waypoints = ['Балашовская 29/1, Воронеж'];
            data.zones.forEach(zone => {
                zone.requests.forEach(request => {
                    waypoints.push(request.location_address);
                });
            });
            waypoints.push('Балашовская 29/1, Воронеж');
            
            const url = `https://yandex.ru/maps/?rtext=${waypoints.map(encodeURIComponent).join('~')}`;
            window.open(url, '_blank');
        });
}

function exportToGoogle() {
    fetch('/admin/osvv/departure-routes-data')
        .then(response => response.json())
        .then(data => {
            let waypoints = [];
            data.zones.forEach(zone => {
                zone.requests.forEach(request => {
                    waypoints.push(request.location_address);
                });
            });
            
            const origin = 'Балашовская 29/1, Воронеж';
            const destination = 'Балашовская 29/1, Воронеж';
            const waypointsStr = waypoints.map(encodeURIComponent).join('|');
            
            const url = `https://www.google.com/maps/dir/${encodeURIComponent(origin)}/${waypointsStr}/${encodeURIComponent(destination)}`;
            window.open(url, '_blank');
        });
}

function exportToPDF() {
    window.open('/admin/osvv/departure-routes-pdf', '_blank');
}

function exportToExcel() {
    window.open('/admin/osvv/departure-routes-excel', '_blank');
}

// Функция тестирования построения маршрута
function testRouteBuilding() {
    console.log('🧪 ТЕСТ: Начинаем тестирование построения маршрута');
    
    if (!map) {
        console.error('❌ ТЕСТ: Карта не инициализирована');
        alert('Карта не инициализирована');
        return;
    }
    
    if (!currentZones || currentZones.length === 0) {
        console.error('❌ ТЕСТ: Нет данных зон');
        alert('Нет данных зон для тестирования');
        return;
    }
    
    console.log('✅ ТЕСТ: Карта и данные доступны');
    console.log('📊 ТЕСТ: Количество зон:', currentZones.length);
    
    // Найдем зону с координатами
    let testZoneIndex = -1;
    for (let i = 0; i < currentZones.length; i++) {
        const requestsWithCoords = currentZones[i].requests.filter(req => req.latitude && req.longitude);
        if (requestsWithCoords.length > 0) {
            testZoneIndex = i;
            break;
        }
    }
    
    if (testZoneIndex === -1) {
        console.error('❌ ТЕСТ: Нет зон с координатами');
        alert('Нет зон с координатами для тестирования');
        return;
    }
    
    console.log('🎯 ТЕСТ: Выбрана зона для тестирования:', testZoneIndex);
    
    // Создаем тестовую линию
    const testCoords = [
        [51.6845, 39.2156], // Приют
        [51.660781, 39.200296], // Тестовая точка 1
        [51.702147, 39.156891], // Тестовая точка 2
        [51.6845, 39.2156]  // Обратно в приют
    ];
    
    console.log('📍 ТЕСТ: Создаем тестовую линию с координатами:', testCoords);
    
    // Очищаем предыдущие маршруты
    clearRouteFromMap();
    
    // Создаем тестовую линию
    const testLine = new ymaps.Polyline(testCoords, {
        hintContent: 'Тестовый маршрут'
    }, {
        strokeColor: '#ff0000',
        strokeWidth: 6,
        strokeOpacity: 1
    });
    
    console.log('🔴 ТЕСТ: Тестовая линия создана:', testLine);
    
    map.geoObjects.add(testLine);
    routeObjects.push(testLine);
    
    console.log('✅ ТЕСТ: Тестовая линия добавлена на карту');
    
    // Центрируем карту
    map.setBounds(testLine.geometry.getBounds(), { checkZoomRange: true, zoomMargin: 50 });
    
    alert('Тестовая красная линия должна быть видна на карте!');
}

// Функция обновления текущего маршрута
function refreshCurrentRoute() {
    console.log('🔄 Обновляем текущий маршрут');
    
    if (selectedZoneIndex !== null && currentZones && currentZones[selectedZoneIndex]) {
        console.log('Перестраиваем маршрут для зоны:', selectedZoneIndex);
        showRouteOnMap(selectedZoneIndex);
    } else {
        console.log('Нет выбранного маршрута для обновления');
        alert('Сначала выберите маршрут для обновления');
    }
}

// Функция оптимизации всех маршрутов
function optimizeAllRoutes() {
    console.log('🚀 Начинаем оптимизацию всех маршрутов');
    
    if (!currentZones || currentZones.length === 0) {
        alert('Нет данных для оптимизации. Сначала загрузите маршруты.');
        return;
    }
    
    // Показываем индикатор загрузки
    const optimizeButton = document.getElementById('optimizeRoutes');
    const originalText = optimizeButton.innerHTML;
    optimizeButton.innerHTML = '⏳ Оптимизируем...';
    optimizeButton.disabled = true;
    
    try {
        console.log('📊 Исходные данные:', currentZones.length, 'зон');
        
        // Собираем все заявки из всех зон
        let allRequests = [];
        currentZones.forEach(zone => {
            allRequests = allRequests.concat(zone.requests);
        });
        
        console.log('📋 Всего заявок для оптимизации:', allRequests.length);
        
        // Фильтруем заявки с координатами
        const requestsWithCoords = allRequests.filter(req => req.latitude && req.longitude);
        console.log('📍 Заявок с координатами:', requestsWithCoords.length);
        
        if (requestsWithCoords.length === 0) {
            alert('Нет заявок с координатами для оптимизации маршрутов');
            return;
        }
        
        // Выполняем оптимизацию
        const optimizedZones = performRouteOptimization(allRequests);
        
        console.log('✅ Оптимизация завершена:', optimizedZones.length, 'зон');
        
        // Обновляем текущие данные
        currentZones = optimizedZones;
        
        // Обновляем отображение
        displayRoutesList(optimizedZones);
        updateStatistics({ zones: optimizedZones });
        
        // Если карта доступна, обновляем маршруты на карте
        if (map) {
            displayRoutesOnMap(optimizedZones);
        }
        
        // Показываем результат
        showOptimizationResults(optimizedZones);
        
    } catch (error) {
        console.error('❌ Ошибка оптимизации:', error);
        alert('Произошла ошибка при оптимизации маршрутов: ' + error.message);
    } finally {
        // Восстанавливаем кнопку
        optimizeButton.innerHTML = originalText;
        optimizeButton.disabled = false;
    }
}

// Основная функция оптимизации маршрутов
function performRouteOptimization(allRequests) {
    console.log('🧠 Выполняем алгоритм оптимизации маршрутов');
    
    // Фильтруем заявки с координатами
    const requestsWithCoords = allRequests.filter(req => req.latitude && req.longitude);
    const requestsWithoutCoords = allRequests.filter(req => !req.latitude || !req.longitude);
    
    if (requestsWithCoords.length === 0) {
        return [];
    }
    
    // Определяем оптимальное количество зон (3-5 зон в зависимости от количества заявок)
    const optimalZoneCount = Math.min(Math.max(Math.ceil(requestsWithCoords.length / 8), 2), 5);
    console.log('🎯 Оптимальное количество зон:', optimalZoneCount);
    
    // Используем алгоритм k-means для кластеризации по координатам
    const clusters = performKMeansClustering(requestsWithCoords, optimalZoneCount);
    
    // Преобразуем кластеры в зоны
    const optimizedZones = clusters.map((cluster, index) => {
        // Находим центральную заявку (с наивысшим приоритетом)
        const centerRequest = findCenterRequest(cluster);
        
        // Оптимизируем порядок заявок в зоне
        const optimizedRequests = optimizeZoneOrder(cluster, centerRequest);
        
        // Рассчитываем общий приоритет зоны
        const totalPriority = cluster.reduce((sum, req) => sum + calculateRequestPriority(req), 0);
        const avgPriority = Math.round(totalPriority / cluster.length);
        
        // Рассчитываем время выполнения
        const estimatedTime = cluster.reduce((sum, req) => sum + estimateRequestTime(req), 0);
        
        return {
            center_request: centerRequest,
            requests: optimizedRequests,
            priority_level: avgPriority,
            estimated_time: estimatedTime,
            zone_type: 'optimized'
        };
    });
    
    // Добавляем заявки без координат к ближайшим зонам
    if (requestsWithoutCoords.length > 0) {
        distributeRequestsWithoutCoords(optimizedZones, requestsWithoutCoords);
    }
    
    // Сортируем зоны по приоритету (сначала самые важные)
    optimizedZones.sort((a, b) => b.priority_level - a.priority_level);
    
    console.log('📈 Результат оптимизации:', optimizedZones.map(zone => ({
        requests: zone.requests.length,
        priority: zone.priority_level,
        time: zone.estimated_time
    })));
    
    return optimizedZones;
}

// Алгоритм k-means кластеризации
function performKMeansClustering(requests, k) {
    console.log('🔬 Выполняем k-means кластеризацию для', requests.length, 'заявок в', k, 'кластеров');
    
    if (requests.length <= k) {
        // Если заявок меньше чем кластеров, каждая заявка - отдельный кластер
        return requests.map(req => [req]);
    }
    
    // Инициализируем центроиды случайным образом
    let centroids = [];
    for (let i = 0; i < k; i++) {
        const randomIndex = Math.floor(Math.random() * requests.length);
        centroids.push({
            lat: requests[randomIndex].latitude,
            lng: requests[randomIndex].longitude
        });
    }
    
    let clusters = [];
    let iterations = 0;
    const maxIterations = 50;
    
    while (iterations < maxIterations) {
        // Инициализируем пустые кластеры
        clusters = Array(k).fill().map(() => []);
        
        // Назначаем каждую заявку к ближайшему центроиду
        requests.forEach(request => {
            let minDistance = Infinity;
            let closestCluster = 0;
            
            centroids.forEach((centroid, index) => {
                const distance = calculateDistance(
                    request.latitude, request.longitude,
                    centroid.lat, centroid.lng
                );
                
                if (distance < minDistance) {
                    minDistance = distance;
                    closestCluster = index;
                }
            });
            
            clusters[closestCluster].push(request);
        });
        
        // Пересчитываем центроиды
        let centroidsChanged = false;
        centroids.forEach((centroid, index) => {
            if (clusters[index].length > 0) {
                const newLat = clusters[index].reduce((sum, req) => sum + req.latitude, 0) / clusters[index].length;
                const newLng = clusters[index].reduce((sum, req) => sum + req.longitude, 0) / clusters[index].length;
                
                if (Math.abs(centroid.lat - newLat) > 0.001 || Math.abs(centroid.lng - newLng) > 0.001) {
                    centroidsChanged = true;
                }
                
                centroid.lat = newLat;
                centroid.lng = newLng;
            }
        });
        
        iterations++;
        
        // Если центроиды не изменились, алгоритм сошелся
        if (!centroidsChanged) {
            console.log('✅ K-means сошелся за', iterations, 'итераций');
            break;
        }
    }
    
    // Удаляем пустые кластеры
    clusters = clusters.filter(cluster => cluster.length > 0);
    
    console.log('📊 Результат кластеризации:', clusters.map(cluster => cluster.length));
    
    return clusters;
}

// Функция поиска центральной заявки в кластере
function findCenterRequest(cluster) {
    if (cluster.length === 1) {
        return cluster[0];
    }
    
    // Ищем заявку с наивысшим приоритетом
    let centerRequest = cluster[0];
    let maxPriority = calculateRequestPriority(cluster[0]);
    
    cluster.forEach(request => {
        const priority = calculateRequestPriority(request);
        if (priority > maxPriority) {
            maxPriority = priority;
            centerRequest = request;
        }
    });
    
    return centerRequest;
}

// Функция оптимизации порядка заявок в зоне
function optimizeZoneOrder(cluster, centerRequest) {
    if (cluster.length <= 2) {
        return cluster;
    }
    
    // Используем алгоритм ближайшего соседа, начиная с центральной заявки
    const optimizedOrder = [centerRequest];
    const remaining = cluster.filter(req => req.id !== centerRequest.id);
    
    let currentRequest = centerRequest;
    
    while (remaining.length > 0) {
        let nearestIndex = 0;
        let minDistance = calculateDistance(
            currentRequest.latitude, currentRequest.longitude,
            remaining[0].latitude, remaining[0].longitude
        );
        
        for (let i = 1; i < remaining.length; i++) {
            const distance = calculateDistance(
                currentRequest.latitude, currentRequest.longitude,
                remaining[i].latitude, remaining[i].longitude
            );
            
            if (distance < minDistance) {
                minDistance = distance;
                nearestIndex = i;
            }
        }
        
        const nearestRequest = remaining.splice(nearestIndex, 1)[0];
        optimizedOrder.push(nearestRequest);
        currentRequest = nearestRequest;
    }
    
    return optimizedOrder;
}

// Функция расчета приоритета заявки
function calculateRequestPriority(request) {
    let priority = 1; // базовый приоритет
    
    if (request.has_bite) priority += 10;
    if (request.is_pregnant) priority += 2;
    if (request.animals_count > 1) priority += request.animals_count - 1;
    
    // Проверяем просроченность
    if (request.deadline_date) {
        const deadline = new Date(request.deadline_date);
        const today = new Date();
        const diffDays = Math.ceil((today - deadline) / (1000 * 60 * 60 * 24));
        
        if (diffDays > 0) priority += 5; // просроченные
        else if (diffDays === 0) priority += 3; // на сегодня
    }
    
    return priority;
}

// Функция оценки времени выполнения заявки
function estimateRequestTime(request) {
    let baseTime = 60; // базовое время 60 минут
    
    if (request.has_bite) baseTime += 30; // укусы требуют больше времени
    if (request.is_pregnant) baseTime += 20; // беременные животные
    if (request.animals_count > 1) baseTime += (request.animals_count - 1) * 15; // дополнительные животные
    
    return baseTime;
}

// Функция распределения заявок без координат
function distributeRequestsWithoutCoords(zones, requestsWithoutCoords) {
    requestsWithoutCoords.forEach(request => {
        // Ищем зону с тем же районом
        let targetZone = zones.find(zone => 
            zone.center_request.district === request.district
        );
        
        // Если не найдена, добавляем к зоне с наименьшим количеством заявок
        if (!targetZone) {
            targetZone = zones.reduce((min, zone) => 
                zone.requests.length < min.requests.length ? zone : min
            );
        }
        
        if (targetZone) {
            targetZone.requests.push(request);
            targetZone.estimated_time += estimateRequestTime(request);
        }
    });
}

// Функция показа результатов оптимизации
function showOptimizationResults(optimizedZones) {
    const totalRequests = optimizedZones.reduce((sum, zone) => sum + zone.requests.length, 0);
    const totalTime = optimizedZones.reduce((sum, zone) => sum + zone.estimated_time, 0);
    const avgZoneSize = Math.round(totalRequests / optimizedZones.length);
    
    const message = `
🎯 Оптимизация маршрутов завершена!

📊 Результаты:
• Создано зон: ${optimizedZones.length}
• Всего заявок: ${totalRequests}
• Среднее количество заявок на зону: ${avgZoneSize}
• Общее время выполнения: ${Math.floor(totalTime / 60)}ч ${totalTime % 60}мин

🚀 Улучшения:
• Заявки сгруппированы по географической близости
• Оптимизирован порядок посещения в каждой зоне
• Учтены приоритеты (укусы, беременность, сроки)
• Сбалансирована нагрузка между зонами

Теперь вы можете выбрать любую зону для просмотра оптимального маршрута на карте!
    `;
    
    alert(message);
}
</script>
@endpush
@endsection 