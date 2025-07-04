<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class BranchController extends Controller
{

    public function index(Request $request)
    {
        $limit = $request->input('limit', 10); // default 10 per halaman

        $query = Branch::with(['provider', 'ipCamAccount', 'cctvs'])
            ->orderBy('id', 'asc');

        // Tambahkan filter jika ada query search
    if ($request->has('q') && !empty($request->q)) {
        $keyword = strtolower($request->q); // biar gak case sensitive

        $query->where(function ($q) use ($keyword) {
            $q->whereRaw('LOWER(name) LIKE ?', ["%{$keyword}%"])
            ->orWhereHas('provider', function ($sub) use ($keyword) {
                // FIX: kolom relasi yang benar adalah 'name', bukan 'provider_name'
                $sub->whereRaw('LOWER(name) LIKE ?', ["%{$keyword}%"]);
            })
            ->orWhereRaw('LOWER(address) LIKE ?', ["%{$keyword}%"])
            ->orWhereRaw('LOWER(phone_number) LIKE ?', ["%{$keyword}%"]);
        });
    }

        $branches = $query->paginate($limit);

        return response()->json([
            'data' => $branches->items(),
            'total' => $branches->total(),
            'current_page' => $branches->currentPage(),
            'last_page' => $branches->lastPage(),
        ]);
    }


   public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'internet_provider_id'  => 'required|exists:internet_providers,id',
            'internet_customer_id'  => 'required|string|max:60',
            'cctv_type'             => 'required|in:1,2', // 1: IP Cam, 2: Analog
            'ip_cam_account_id'     => 'nullable|exists:ip_cam_accounts,id',
        ]);

        // Tambahkan created_by otomatis
        $validated['created_by'] = $user->id;

        $branch = Branch::create($validated);

        return response()->json([
            'message' => 'Branch created successfully',
            'data' => $branch
        ], 201);
    }


    public function show($id)
    {
        $branch = Branch::with(['provider', 'ipCamAccount'])->findOrFail($id);
        return response()->json($branch);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();

        $branch = Branch::findOrFail($id);

        $validated = $request->validate([
            'internet_provider_id'  => 'nullable|exists:internet_providers,id',
            'internet_customer_id'  => 'nullable|string|max:60',
            'cctv_type'             => 'nullable|in:1,2',
            'ip_cam_account_id'     => 'nullable|exists:ip_cam_accounts,id',
            'updated_by'            => $user->id
        ]);

        $branch->update($validated);

        return response()->json([
            'message' => 'Branch updated successfully',
            'branch' => $branch
        ]);
    }

    public function destroy($id)
    {
        $branch = Branch::findOrFail($id);
        $branch->delete();

        return response()->json(['message' => 'Branch deleted successfully']);
    }

    public function searchByName(Request $request)
    {
        $name = $request->query('name');

        if (!$name) {
            return response()->json(['error' => 'Parameter name diperlukan'], 400);
        }

        $branch = Branch::where('name', $name)->first();

        if (!$branch) {
            return response()->json(['error' => 'Cabang tidak ditemukan'], 404);
        }

        return response()->json(['id' => $branch->id]);
    }

    // public function syncFromHRIS($id)
    // {
    //     $token = config('services.hris.token'); // dari config/services.php

    //     $response = Http::withToken($token)->get("https://external-hris.com/api/branches/{$id}");

    //     if ($response->failed() || !$response->json()) {
    //         return response()->json(['message' => 'Gagal sinkronisasi data dari HRIS'], 500);
    //     }

    //     return response()->json($response->json());
    // }
}
