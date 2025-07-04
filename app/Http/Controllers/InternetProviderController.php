<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InternetProvider;
use Faker\Provider\ar_EG\Internet;
use App\Models\IspMaster;
use Illuminate\Support\Facades\Auth;

class InternetProviderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);

        $query = InternetProvider::with(['createdBy:id,name', 'updatedBy:id,name']);

        // Filter berdasarkan search query
        if ($request->has('q') && !empty($request->q)) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        $providers = $query->paginate($limit);

        return response()->json([
            'data' => $providers->items(),
            'total' => $providers->total(),
            'current_page' => $providers->currentPage(),
            'last_page' => $providers->lastPage(),
        ]);
    }

    public function list()
    {
        return response()->json(
            InternetProvider::select('id', 'provider_name')->get()
        );
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Auto assign created_by dan updated_by dari user login
        $validated['created_by'] = $user->id;
        $validated['updated_by'] = $user->id;

        $model = InternetProvider::create($validated);

        return response()->json($model, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $provider = InternetProvider::findOrFail($id);
        return response()->json($provider);
    }

    public function update(Request $request, string $id)
    {
        $user = Auth::user();
        $provider = InternetProvider::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $validated['updated_by'] = $user->id;

        $provider->update($validated);

        return response()->json([
            'message' => 'Provider updated successfully',
            'data' => $provider
        ]);
    }

    public function destroy(string $id)
    {
        $provider = InternetProvider::findOrFail($id);
        $provider->delete();

        return response()->json(['message' => 'Internet Provider deleted successfully']);
    }
}
