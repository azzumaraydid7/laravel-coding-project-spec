<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('unique_key')) {
            $query->where('unique_key', $request->input('unique_key'));
        }

        $products = $query->orderBy('unique_key', 'ASC')->paginate(20)->withQueryString();
        return view('welcome', compact('products'));
    }
}
