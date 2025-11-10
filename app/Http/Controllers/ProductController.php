<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Product::query();

            if ($request->filled('unique_key')) {
                $query->where('unique_key', $request->input('unique_key'));
            }

            $products = $query
                ->orderBy('unique_key', 'ASC')
                ->paginate(20)
                ->withQueryString();
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database query failed in ProductController@index', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Unexpected error in ProductController@index', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
        
        return view('welcome', compact('products'));
    }
}
