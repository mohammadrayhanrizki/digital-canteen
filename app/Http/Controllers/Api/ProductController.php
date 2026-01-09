<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::where('stock', '>', 0)->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $products
        ]);
    }
}
