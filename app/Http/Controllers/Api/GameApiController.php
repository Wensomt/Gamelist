<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GameApiController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('search');
        $page = $request->input('page', 1);

        // Używamy config() zamiast env(), bo tak masz w api.php
        $response = Http::get(config('rawg.base_url') . '/games', [
            'key' => config('rawg.key'),
            'search' => $query,
            'page_size' => 9,
            'page' => $page,
        ]);

        if ($response->successful()) {
            return response()->json($response->json());
        }

        return response()->json(['error' => 'Błąd API RAWG'], 500);
    }
}