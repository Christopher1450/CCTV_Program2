<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CctvNote;

class CctvNoteController extends Controller
{
    public function index()
    {
        return response()->json(CctvNote::with('cctv')->latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cctv_id' => 'required|exists:cctvs,id',
            'notes' => 'required|string|max:255',
        ]);

        $validated['created_by'] = auth()->id();

        $note = CctvNote::create($validated);

        return response()->json([
            'message' => 'Note created',
            'data' => $note
        ], 201);
    }
}
