<?php

namespace App\Http\Controllers;

use App\Models\OsvvRequest;
use Illuminate\Http\Request;

class OsvvRequestController extends Controller
{
    /**
     * Отображает форму создания заявки ОСВВ.
     */
    public function create()
    {
        return view('osvv.create');
    }

    /**
     * Сохраняет новую заявку ОСВВ.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'contact_name' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'animal_type' => 'required|in:cat,dog,other',
            'animal_type_other' => 'nullable|string|max:255|required_if:animal_type,other',
            'animal_gender' => 'required|in:male,female,unknown',
            'animal_age' => 'nullable|string|max:255',
            'animal_description' => 'nullable|string',
            'location_address' => 'required|string',
            'location_landmark' => 'nullable|string',
        ]);
        
        $osvvRequest = OsvvRequest::create($validated);
        
        // Перенаправляем на страницу с информацией о созданной заявке
        return redirect()->route('osvv.thank-you', ['id' => $osvvRequest->id])
            ->with('success', 'Заявка успешно создана.');
    }

    /**
     * Отображает страницу благодарности после создания заявки.
     */
    public function thankYou(Request $request)
    {
        $id = $request->id;
        return view('osvv.thank-you', compact('id'));
    }

    /**
     * Отображает статус заявки по идентификатору.
     */
    public function status(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:osvv_requests,id',
        ]);
        
        $osvvRequest = OsvvRequest::findOrFail($validated['id']);
        
        return view('osvv.status', compact('osvvRequest'));
    }
}
