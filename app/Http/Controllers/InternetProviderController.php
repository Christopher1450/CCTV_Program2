<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InternetProvider;
use Faker\Provider\ar_EG\Internet;
use App\Models\IspMaster;

class InternetProviderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);

        $providers = InternetProvider::with(['createdBy:id,name', 'updatedBy:id,name'])->paginate($limit);

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
        $validated = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
]);

        $validated['created_by'] = $request->input('created_by');

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

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $provider = InternetProvider::findOrFail($id);
        // $provider = InternetProvider

       $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $validated['created_by'] = $request->input('updated_by');


        $provider->update($validated);


        $provider->update($request->only('name', 'created_by', 'updated_by'));

        return response()->json($provider);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $provider = InternetProvider::findOrFail($id);
        $provider->delete();

        return response()->json(['message' => 'Internet Provider deleted successfully']);
    }
}
