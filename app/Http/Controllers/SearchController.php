<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SearchService;

class SearchController extends Controller
{
    public function index(Request $request, SearchService $service)
    {
        $q = trim($request->query('q', ''));

        if (mb_strlen($q) < 3) {
            return response()->json([]);
        }

        return response()->json(
            $service->search($q)
        );
    }
}
