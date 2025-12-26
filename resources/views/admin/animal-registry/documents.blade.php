@extends('admin.layout')

@section('header')
    Учет животных — Документы
@endsection

@section('content')
    <div class="space-y-6">
        

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Акты приема-передачи -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Акты приема-передачи</h3>
                </div>
                <div class="p-6 space-y-3">
                    @forelse($transferActs as $act)
                        <div class="flex items-center justify-between bg-gray-50 p-3 rounded">
                            <div>
                                <div class="text-sm font-medium text-gray-900">Акт №{{ $act->id }}</div>
                                <div class="text-xs text-gray-500">{{ $act->created_at->format('d.m.Y H:i') }} • Животных: {{ $act->animals_count ?? $act->animals_count }}</div>
                            </div>
                            <div class="space-x-2 text-sm">
                                <a href="{{ route('admin.animal-transfer-acts.show', $act) }}" class="text-indigo-600 hover:underline">Открыть</a>
                                <a href="{{ route('admin.animal-transfer-acts.pdf', $act) }}" class="text-gray-600 hover:underline">PDF</a>
                            </div>
                        </div>
                    @empty
                        <div class="text-gray-500">Нет актов</div>
                    @endforelse
                    <div class="pt-2">{{ $transferActs->onEachSide(0)->links() }}</div>
                </div>
            </div>

            <!-- Регистрационные карточки -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Регистрационные карточки</h3>
                </div>
                <div class="p-6 space-y-3">
                    @forelse($registrationCards as $card)
                        <div class="flex items-center justify-between bg-gray-50 p-3 rounded">
                            <div>
                                <div class="text-sm font-medium text-gray-900">Карточка №{{ $card->id }}</div>
                                <div class="text-xs text-gray-500">{{ $card->created_at->format('d.m.Y H:i') }}</div>
                            </div>
                            <div class="space-x-2 text-sm">
                                <a href="#" class="text-indigo-600 hover:underline">Открыть</a>
                            </div>
                        </div>
                    @empty
                        <div class="text-gray-500">Нет карточек</div>
                    @endforelse
                    <div class="pt-2">{{ $registrationCards->onEachSide(0)->links() }}</div>
                </div>
            </div>

            <!-- Регламентные документы / Договоры -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Договоры и документы</h3>
                </div>
                <div class="p-6 space-y-3">
                    @forelse($regulatoryDocuments as $doc)
                        <div class="flex items-center justify-between bg-gray-50 p-3 rounded">
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $doc->title ?? ('Документ №'.$doc->id) }}</div>
                                <div class="text-xs text-gray-500">{{ $doc->created_at->format('d.m.Y H:i') }}</div>
                            </div>
                            <div class="space-x-2 text-sm">
                                @if($doc->file_path)
                                    <a href="{{ asset('storage/'.$doc->file_path) }}" target="_blank" class="text-indigo-600 hover:underline">Открыть</a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-gray-500">Нет документов</div>
                    @endforelse
                    <div class="pt-2">{{ $regulatoryDocuments->onEachSide(0)->links() }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection


