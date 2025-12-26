@extends('admin.layout')

@section('header')
    Учет животных — Вольеры
@endsection

@section('main_padding', 'pl-3 pr-0 py-0 sm:pl-4')
@section('content')
    <div class="w-full" style="display:grid; grid-template-columns: 9fr 3fr; gap:5px; margin-top:10px;">
        <div class="min-w-0">
            <div class="bg-white shadow rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Рассадка по вольерам</h3>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg p-4 mt-[5px]">
                <div class="flex items-center gap-2 flex-wrap">
                    <form id="create-block-form" method="POST" action="{{ route('admin.animal-registry.cage-blocks.store') }}" class="flex items-end gap-4 flex-wrap">
                        @csrf
                        <div class="flex flex-col">
                            <label class="text-xs text-gray-600 mb-1">Название блока</label>
                            <input type="text" name="title" placeholder="Напр. Карантин" class="w-52 rounded border-gray-300" />
                        </div>
                        <div class="flex flex-col">
                            <label class="text-xs text-gray-600 mb-1">Строк (до 50)</label>
                            <input type="number" name="rows" class="w-24 rounded border-gray-300" min="1" max="50" value="1" />
                        </div>
                        <div class="flex flex-col">
                            <label class="text-xs text-gray-600 mb-1">Колонок (до 4)</label>
                            <input type="number" name="cols" class="w-28 rounded border-gray-300" min="1" max="4" value="1" />
                        </div>
                        <div class="flex flex-col">
                            <label class="text-xs text-gray-600 mb-1">Префикс номера (опц.)</label>
                            <input type="text" name="start_number" placeholder="Напр. A" class="w-56 rounded border-gray-300" />
                        </div>
                        <div>
                            <button type="submit" class="px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Добавить блок</button>
                        </div>
                    </form>
                </div>
            </div>

            @if(($blocks ?? collect())->count() > 0)
                @foreach($blocks as $block)
                    <div class="mt-[5px]" data-block-wrapper data-block-id="{{ $block->id }}">
                        <div class="flex items-center justify-between mb-1">
                            <div class="text-sm text-gray-700">Блок @if($block->title) «{{ $block->title }}» @else #{{ $block->id }} @endif — {{ $block->rows }}×{{ $block->cols }}</div>
                            <div class="flex items-center gap-3">
                                <button type="button" class="text-xs text-gray-700 hover:underline" data-toggle-block data-block-id="{{ $block->id }}" aria-expanded="true">Свернуть</button>
                                <form class="inline" data-destroy-block-form action="{{ route('admin.animal-registry.cage-blocks.destroy', $block) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 text-xs hover:underline">Удалить блок</button>
                                </form>
                            </div>
                        </div>
                        <div class="grid gap-3" data-block-content data-block-id="{{ $block->id }}" style="grid-template-columns: repeat({{ $block->cols }}, minmax(260px, 1fr));">
                            @for($r=1; $r <= $block->rows; $r++)
                                @for($c=1; $c <= $block->cols; $c++)
                                    @php
                                        $cell = $block->cages->first(function($cg) use ($r,$c){ return (int)$cg->row_index === (int)$r && (int)$cg->col_index === (int)$c; });
                                        $animalsInCage = $cell ? ($caged[$cell->number] ?? collect()) : collect();
                                        $count = $animalsInCage->count();
                                        $cap = $cell->capacity ?? $capacityPerCage;
                                        $over = $count > $cap;
                                    @endphp
                                    <div class="border-2 rounded-lg {{ $over ? 'border-red-500' : 'border-gray-400' }} shadow bg-white" data-drop-zone data-to-cage="{{ $cell?->number }}">
                                        <div class="px-4 py-2 flex items-center justify-between {{ $over ? 'bg-red-50' : 'bg-gray-50' }} rounded-t-lg">
                                            <div class="font-medium text-gray-900">@if($cell) {{ $cell->number }} @else Пустая ячейка @endif</div>
                                            <div class="text-sm {{ $over ? 'text-red-700' : 'text-gray-600' }}">{{ $count }} / {{ $cap }}</div>
                                        </div>
                                        <div class="p-4 space-y-3 min-h-36">
                                            @foreach($animalsInCage as $animal)
                                                <div class="flex items-center justify-between bg-white rounded p-2 border border-gray-300" draggable="true" data-animal-id="{{ $animal->id }}">
                                                    <div class="flex items-center gap-2">
                                                        <div class="w-10 h-10 rounded bg-white border flex items-center justify-center overflow-hidden flex-shrink-0">
                                                            @php
                                                                $displayPhoto = $animal->photo ?? ($animal->registrationCard->photo_face ?? null);
                                                            @endphp
                                                            @if($displayPhoto)
                                                                <img src="{{ asset('storage/' . $displayPhoto) }}" class="w-full h-full object-cover" alt="{{ $animal->name ?? 'Животное' }}">
                                                            @else
                                                                <span>🐾</span>
                                                            @endif
                                                        </div>
                                                        <div class="min-w-0">
                                                            <div class="text-sm font-medium text-gray-900 truncate">{{ $animal->name ?? 'Без имени' }}</div>
                                                            <div class="text-xs text-gray-600 space-y-0.5">
                                                                @if($animal->registrationCard && $animal->registrationCard->registration_number)
                                                                    <div>📋 № {{ $animal->registrationCard->registration_number }}</div>
                                                                @endif
                                                                @if($animal->tag_number)
                                                                    <div>🏷️ {{ $animal->tag_number }}</div>
                                                                @endif
                                                                @if($animal->chip_number)
                                                                    <div class="truncate">💳 {{ $animal->chip_number }}</div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <a href="{{ route('admin.animals.show', $animal) }}" class="flex-shrink-0 px-2 py-1 bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200 text-xs font-medium">
                                                        Открыть
                                                    </a>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endfor
                            @endfor
                        </div>
                    </div>
                @endforeach
            @else
                <div class="mt-[5px] bg-white shadow rounded-lg p-4 text-gray-500">Блоки вольеров не созданы. Добавьте блок выше.</div>
            @endif
        </div>

        <div>
            <div class="bg-white shadow rounded-lg p-2" data-drop-zone data-to-cage="">
                <h3 class="text-base font-semibold text-gray-900 mb-2">Без вольера</h3>
                @if($uncaged->count() > 0)
                    <div class="space-y-1 max-h-[70vh] overflow-auto pr-1">
                        @foreach($uncaged as $animal)
                            <div class="flex items-center justify-between bg-white rounded p-2 border border-gray-300" draggable="true" data-animal-id="{{ $animal->id }}">
                                <div class="flex items-center gap-2">
                                    <div class="w-10 h-10 rounded bg-white border flex items-center justify-center overflow-hidden flex-shrink-0">
                                        @php
                                            $displayPhoto = $animal->photo ?? ($animal->registrationCard->photo_face ?? null);
                                        @endphp
                                        @if($displayPhoto)
                                            <img src="{{ asset('storage/' . $displayPhoto) }}" class="w-full h-full object-cover" alt="{{ $animal->name ?? 'Животное' }}">
                                        @else
                                            <span>🐾</span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-sm font-medium text-gray-900 truncate">{{ $animal->name ?? 'Без имени' }}</div>
                                        <div class="text-xs text-gray-600 space-y-0.5">
                                            @if($animal->registrationCard && $animal->registrationCard->registration_number)
                                                <div>📋 № {{ $animal->registrationCard->registration_number }}</div>
                                            @endif
                                            @if($animal->tag_number)
                                                <div>🏷️ {{ $animal->tag_number }}</div>
                                            @endif
                                            @if($animal->chip_number)
                                                <div class="truncate">💳 {{ $animal->chip_number }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <a href="{{ route('admin.animals.show', $animal) }}" class="flex-shrink-0 px-2 py-1 bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200 text-xs font-medium">
                                    Открыть
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-gray-500">Все животные распределены по вольерам.</div>
                @endif
            </div>

            <div class="bg-white shadow rounded-lg p-2 mt-[5px]">
                <h3 class="text-base font-semibold text-gray-900 mb-2">Журнал перемещений</h3>
                @if(($movements ?? collect())->count() > 0)
                    <div class="space-y-1 text-sm">
                        @foreach($movements as $m)
                            <div class="border rounded p-1">
                                <div class="flex justify-between">
                                    <div>
                                        <span class="text-gray-600">Животное #{{ $m->animal_id }}</span>
                                        <span class="mx-2">|</span>
                                        <span>Из: {{ $m->from_cage ?? '—' }}</span>
                                        <span class="mx-2">→</span>
                                        <span>В: {{ $m->to_cage ?? '—' }}</span>
                                    </div>
                                    <div class="text-gray-500">{{ optional($m->moved_at)->format('d.m.Y H:i') ?? $m->created_at->format('d.m.Y H:i') }}</div>
                                </div>
                                @if($m->comment)
                                    <div class="text-xs text-gray-500 mt-1">{{ $m->comment }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-gray-500">Пока нет перемещений.</div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Drag start for animal cards
            document.addEventListener('dragstart', function(e) {
                const card = e.target.closest('[data-animal-id]');
                if (card && e.dataTransfer) {
                    e.dataTransfer.setData('text/plain', card.getAttribute('data-animal-id'));
                }
            });

            // Drop zones
            const zones = document.querySelectorAll('[data-drop-zone]');
            zones.forEach(function(zone) {
                zone.addEventListener('dragover', function(e) { e.preventDefault(); });
                zone.addEventListener('drop', function(e) {
                    e.preventDefault();
                    const animalId = (e.dataTransfer && e.dataTransfer.getData('text/plain')) || '';
                    if (!animalId) return;
                    const toCageAttr = zone.getAttribute('data-to-cage');
                    const toCage = toCageAttr && toCageAttr.length ? toCageAttr : null;
                    fetch('{{ url('/admin/animals/move-to-cage') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ animal_id: animalId, to_cage: toCage, comment: '' })
                    }).then(function(){ window.location.reload(); });
                });
            });

			// Create cage form AJAX
            const createForm = document.getElementById('create-cage-form');
            if (createForm) {
                createForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(createForm);
                    fetch(createForm.getAttribute('action'), {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                        body: new URLSearchParams(formData)
                    }).then(function(){ window.location.reload(); });
                });
            }

            // Create cage block form AJAX
			const createBlockForm = document.getElementById('create-block-form');
			if (createBlockForm) {
				createBlockForm.addEventListener('submit', function(e) {
					e.preventDefault();
					const formData = new FormData(createBlockForm);
					fetch(createBlockForm.getAttribute('action'), {
						method: 'POST',
						headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
						body: new URLSearchParams(formData)
					}).then(function(){ window.location.reload(); });
				});
			}

            // Destroy cage block AJAX
            document.querySelectorAll('[data-destroy-block-form]').forEach(function(form){
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const url = form.getAttribute('action');
                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-HTTP-Method-Override': 'DELETE'
                        }
                    }).then(function(){ window.location.reload(); });
                });
            });

            // Collapse/expand blocks with localStorage persistence
            const storageKey = (id) => 'cageBlockCollapsed:' + id;
            const applyInitialState = () => {
                document.querySelectorAll('[data-block-content]').forEach(function(content) {
                    const id = content.getAttribute('data-block-id');
                    const btn = document.querySelector('[data-toggle-block][data-block-id="' + id + '"]');
                    const collapsed = localStorage.getItem(storageKey(id)) === '1';
                    if (collapsed) {
                        content.classList.add('hidden');
                        if (btn) btn.setAttribute('aria-expanded', 'false');
                        if (btn) btn.textContent = 'Развернуть';
                    } else {
                        content.classList.remove('hidden');
                        if (btn) btn.setAttribute('aria-expanded', 'true');
                        if (btn) btn.textContent = 'Свернуть';
                    }
                });
            };

            applyInitialState();

            document.querySelectorAll('[data-toggle-block]').forEach(function(btn){
                btn.addEventListener('click', function(){
                    const id = btn.getAttribute('data-block-id');
                    const content = document.querySelector('[data-block-content][data-block-id="' + id + '"]');
                    if (!content) return;
                    const isHidden = content.classList.toggle('hidden');
                    localStorage.setItem(storageKey(id), isHidden ? '1' : '0');
                    btn.setAttribute('aria-expanded', isHidden ? 'false' : 'true');
                    btn.textContent = isHidden ? 'Развернуть' : 'Свернуть';
                });
            });
        });
    </script>

@endsection


