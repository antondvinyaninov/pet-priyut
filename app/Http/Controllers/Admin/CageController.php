<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cage;
use Illuminate\Http\Request;

class CageController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|string|max:255|unique:cages,number',
            'title' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1|max:50',
        ]);

        Cage::create([
            'number' => $validated['number'],
            'title' => $validated['title'] ?? null,
            'capacity' => $validated['capacity'] ?? 2,
        ]);

        return response()->json(['success' => true]);
    }
}






