<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InternetProvider;


class InternetProviderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(InternetProvider::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:internet_providers,name',
        ]);

        $provider = InternetProvider::create($request->only('name'));

        return response()->json($provider, 201);
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

        $request->validate([
            'name' => 'required|string|unique:internet_providers,name,' . $id,
        ]);

        $provider->update($request->only('name'));

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
