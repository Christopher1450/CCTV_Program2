<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CctvNote;
use Illuminate\Support\Facades\Auth;



class CctvNoteController extends Controller
{
    public function index()
    {
        // Menampilkan semua catatan CCTV dengan relasi CCTV & user pembuat (jika ada)
        return response()->json(
            CctvNote::with(['cctv', 'creator'])->latest()->get()
        );
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'cctv_id' => 'required|exists:cctvs,id',
            'notes' => 'required|string|max:255',
            'created_by' => $user->id
        ]);

        $note = CctvNote::create([
            'cctv_id'    => $validated['cctv_id'],
            'notes'      => $validated['notes'],
            'created_by' => $user->id
        ]);

        return response()->json([
            'message' => 'Note created successfully',
            'data' => $note->load('creator') // langsung return dengan relasi creator
        ], 201);
    }

    public function getByCctv($id)
    {
        $notes = CctvNote::with('creator')
            ->where('cctv_id', $id)
            ->latest()
            ->get();

        return response()->json(['data' => $notes]);
    }
}
