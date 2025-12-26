<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\AnimalRegistrationCard;
use App\Models\AnimalTransferAct;
use App\Models\AnimalCageMovement;
use App\Models\Cage;
use App\Models\OsvvRequest;
use App\Models\RegulatoryDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnimalRegistryController extends Controller
{
    /**
     * Главная страница учета животных
     */
    public function index()
    {
        // Статистика общая
        $totalAnimals = Animal::where('status', 'active')->count();
        $animalsWithOSVV = Animal::where('status', 'active')
            ->whereNotNull('osvv_request_id')
            ->count();
        $animalsInShelter = Animal::where('status', 'active')
            ->whereNull('osvv_request_id')
            ->count();
        $releasedAnimals = Animal::where('status', 'released')->count();
        $adoptedAnimals = Animal::where('status', 'adopted')->count();

        // Статистика по типам
        $animalsByType = Animal::where('status', 'active')
            ->select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get()
            ->keyBy('type');

        // Последние поступления (последние 10)
        $recentAnimals = Animal::where('status', 'active')
            ->with(['osvvRequest', 'currentStage'])
            ->orderBy('arrived_at', 'desc')
            ->limit(10)
            ->get();

        // Животные готовые к выпуску
        $readyForRelease = Animal::where('status', 'active')
            ->whereNotNull('current_stage_id')
            ->with(['currentStage'])
            ->whereHas('currentStage', function($query) {
                $query->where('is_final', true);
            })
            ->count();

        return view('admin.animal-registry.index', compact(
            'totalAnimals',
            'animalsWithOSVV', 
            'animalsInShelter',
            'releasedAnimals',
            'adoptedAnimals',
            'animalsByType',
            'recentAnimals',
            'readyForRelease'
        ));
    }

    /**
     * Животные по ОСВВ (связанные с заявками)
     */
    public function osvv(Request $request)
    {
        $query = Animal::with(['osvvRequest', 'currentStage'])
            ->whereNotNull('osvv_request_id')
            ->where('status', 'active');

        // Фильтры
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        if ($request->filled('stage')) {
            $query->where('current_stage_id', $request->stage);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('chip_number', 'like', "%{$search}%")
                  ->orWhere('tag_number', 'like', "%{$search}%")
                  ->orWhere('cage_number', 'like', "%{$search}%")
                  ->orWhereHas('osvvRequest', function($subq) use ($search) {
                      $subq->where('id', 'like', "%{$search}%")
                           ->orWhere('applicant_name', 'like', "%{$search}%")
                           ->orWhere('location_address', 'like', "%{$search}%");
                  });
            });
        }

        // Сортировка
        $sortBy = $request->get('sort', 'arrived_at');
        $sortDirection = $request->get('direction', 'desc');
        
        if ($sortBy === 'osvv_request_id') {
            $query->join('osvv_requests', 'animals.osvv_request_id', '=', 'osvv_requests.id')
                  ->orderBy('osvv_requests.id', $sortDirection)
                  ->select('animals.*');
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }

        $animals = $query->paginate(20)->withQueryString();

        // Данные для фильтров
        $stages = \App\Models\AnimalStage::active()->ordered()->get();
        $types = ['dog' => 'Собака', 'cat' => 'Кошка', 'other' => 'Другое'];
        $genders = ['male' => 'Самец', 'female' => 'Самка', 'unknown' => 'Неизвестно'];

        return view('admin.animal-registry.osvv', compact(
            'animals', 
            'stages', 
            'types', 
            'genders'
        ));
    }

    /**
     * Животные в приюте (не связанные с заявками ОСВВ)
     */
    public function shelter(Request $request)
    {
        $query = Animal::with(['currentStage'])
            ->whereNull('osvv_request_id')
            ->where('status', 'active');

        // Фильтры
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        if ($request->filled('stage')) {
            $query->where('current_stage_id', $request->stage);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('chip_number', 'like', "%{$search}%")
                  ->orWhere('tag_number', 'like', "%{$search}%")
                  ->orWhere('cage_number', 'like', "%{$search}%")
                  ->orWhere('breed', 'like', "%{$search}%");
            });
        }

        // Сортировка
        $sortBy = $request->get('sort', 'arrived_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $animals = $query->paginate(20)->withQueryString();

        // Данные для фильтров
        $stages = \App\Models\AnimalStage::active()->ordered()->get();
        $types = ['dog' => 'Собака', 'cat' => 'Кошка', 'other' => 'Другое'];
        $genders = ['male' => 'Самец', 'female' => 'Самка', 'unknown' => 'Неизвестно'];

        return view('admin.animal-registry.shelter', compact(
            'animals', 
            'stages', 
            'types', 
            'genders'
        ));
    }

    /**
     * Карточки всех животных (ОСВВ + приют)
     */
    public function cards(Request $request)
    {
        $query = Animal::with(['osvvRequest', 'currentStage', 'registrationCard', 'transferActs'])
            ->whereIn('status', ['active', 'released', 'adopted']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        if ($request->filled('stage')) {
            $query->where('current_stage_id', $request->stage);
        }

        if ($request->filled('source')) {
            if ($request->source === 'osvv') {
                $query->whereNotNull('osvv_request_id');
            } elseif ($request->source === 'shelter') {
                $query->whereNull('osvv_request_id');
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('chip_number', 'like', "%{$search}%")
                    ->orWhere('tag_number', 'like', "%{$search}%")
                    ->orWhere('cage_number', 'like', "%{$search}%")
                    ->orWhere('breed', 'like', "%{$search}%")
                    ->orWhereHas('osvvRequest', function ($subq) use ($search) {
                        $subq->where('id', 'like', "%{$search}%")
                            ->orWhere('applicant_name', 'like', "%{$search}%")
                            ->orWhere('location_address', 'like', "%{$search}%");
                    });
            });
        }

        $sortBy = $request->get('sort', 'registration_number'); // По умолчанию сортировка по номеру карточки
        $sortDirection = $request->get('direction', 'asc'); // По возрастанию
        
        // Если сортировка по номеру карточки
        if ($sortBy === 'registration_number' || $sortBy === 'card_number') {
            // Сортировка с учетом ведущих нулей: сначала 0001, 0002, потом 1, 2, 3
            $query->leftJoin('animal_registration_cards', 'animals.id', '=', 'animal_registration_cards.animal_id')
                  ->orderByRaw("
                      CASE 
                          WHEN substr(animal_registration_cards.registration_number, 1, 1) = '0' THEN 0
                          ELSE 1
                      END " . ($sortDirection === 'asc' ? 'ASC' : 'DESC') . ",
                      CAST(animal_registration_cards.registration_number AS INTEGER) " . ($sortDirection === 'asc' ? 'ASC' : 'DESC'))
                  ->select('animals.*');
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }

        $animals = $query->paginate(20)->withQueryString();

        $stages = \App\Models\AnimalStage::active()->ordered()->get();
        $types = ['dog' => 'Собака', 'cat' => 'Кошка', 'other' => 'Другое'];
        $genders = ['male' => 'Самец', 'female' => 'Самка', 'unknown' => 'Неизвестно'];

        return view('admin.animal-registry.cards', compact(
            'animals',
            'stages',
            'types',
            'genders'
        ));
    }

    /**
     * Вольеры: рассадка и индикатор переполнения (>2)
     */
    public function cages(Request $request)
    {
        $animals = Animal::where('status', 'active')
            ->orderBy('cage_number')
            ->orderBy('arrived_at', 'asc')
            ->get();

        $cages = Cage::with('block')->whereNotNull('cage_block_id')->orderBy('number')->get();
        $blocks = \App\Models\CageBlock::with(['cages' => function($q){
            $q->orderBy('row_index')->orderBy('col_index');
        }])->orderBy('id', 'asc')->get();
        $caged = $animals->filter(fn ($a) => !empty($a->cage_number))->groupBy('cage_number');
        $uncaged = $animals->filter(fn ($a) => empty($a->cage_number));
        $capacityPerCage = 2;

        $movements = AnimalCageMovement::latest('moved_at')->limit(50)->get();

        return view('admin.animal-registry.cages', [
            'cages' => $cages,
            'blocks' => $blocks,
            'caged' => $caged,
            'uncaged' => $uncaged,
            'capacityPerCage' => $capacityPerCage,
            'movements' => $movements,
        ]);
    }

    /**
     * Документы: акты, регистрационные карточки, рег. документы
     */
    public function documents(Request $request)
    {
        $transferActs = AnimalTransferAct::query()
            ->latest('created_at')
            ->withCount('animals')
            ->paginate(10, ['*'], 'acts_page');

        $registrationCards = AnimalRegistrationCard::query()
            ->latest('created_at')
            ->paginate(10, ['*'], 'cards_page');

        $regulatoryDocuments = RegulatoryDocument::query()
            ->latest('created_at')
            ->paginate(10, ['*'], 'docs_page');

        return view('admin.animal-registry.documents', compact(
            'transferActs',
            'registrationCards',
            'regulatoryDocuments'
        ));
    }
}
