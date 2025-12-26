<div class="bg-white shadow rounded-lg">
    <div class="px-6 pt-4">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-6" aria-label="Tabs">
                <a href="{{ route('admin.animal-registry.cards') }}" class="whitespace-nowrap py-4 px-1 border-b-2 text-sm font-medium {{ request()->routeIs('admin.animal-registry.cards') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Карточки
                </a>
                <a href="{{ route('admin.animal-registry.cages') }}" class="whitespace-nowrap py-4 px-1 border-b-2 text-sm font-medium {{ request()->routeIs('admin.animal-registry.cages') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Вольеры
                </a>
                <a href="{{ route('admin.animal-registry.documents') }}" class="whitespace-nowrap py-4 px-1 border-b-2 text-sm font-medium {{ request()->routeIs('admin.animal-registry.documents') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Документы
                </a>
            </nav>
        </div>
    </div>
</div>





