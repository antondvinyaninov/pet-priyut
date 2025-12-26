<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cage;
use App\Models\CageBlock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CageBlockController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'rows' => 'required|integer|min:1|max:50',
            'cols' => 'required|integer|min:1|max:4',
            'start_number' => 'nullable|string|max:255',
        ]);

        $prefix = trim((string) $request->input('start_number'));

        // Если префикс уже используется, начнём заново с 1 и будем инкрементировать до свободного

        DB::transaction(function () use ($validated, $prefix) {
            $block = CageBlock::create([
                'title' => $validated['title'] ?? null,
                'rows' => $validated['rows'],
                'cols' => $validated['cols'],
            ]);

            // Всегда начинаем нумерацию с 1 внутри нового блока
            $seq = 0;

            // Автосоздание клеток по сетке — нумерация по столбцам сверху вниз
            for ($c = 1; $c <= $block->cols; $c++) {
                for ($r = 1; $r <= $block->rows; $r++) {
                    $seq++;
                    $number = ($prefix !== '' ? $prefix : '') . (string) $seq;
                    // Гарантируем уникальность номера в пределах всей таблицы
                    while (Cage::where('number', $number)->exists()) {
                        $seq++;
                        $number = ($prefix !== '' ? $prefix : '') . (string) $seq;
                    }
                    Cage::create([
                        'number' => $number,
                        'title' => null,
                        'capacity' => 2,
                        'layout' => null,
                        'cage_block_id' => $block->id,
                        'row_index' => $r,
                        'col_index' => $c,
                    ]);
                }
            }
        });

        return response()->json(['success' => true]);
    }

    public function destroy(CageBlock $cageBlock)
    {
        DB::transaction(function () use ($cageBlock) {
            // Переносим животных из клеток блока в «Без вольера»
            $cages = Cage::where('cage_block_id', $cageBlock->id)->get();
            foreach ($cages as $c) {
                \App\Models\Animal::where('cage_number', $c->number)->update(['cage_number' => null]);
            }

            // Удаляем клетки блока
            Cage::where('cage_block_id', $cageBlock->id)->delete();

            // Удаляем сам блок
            $cageBlock->delete();
        });

        return response()->json(['success' => true]);
    }
}


