<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Resources\ProductResource; // <-- Add this
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Eager load the images relationship
        $products = Product::with('images')->latest()->get();

        // Return a collection of products transformed by the resource
        return ProductResource::collection($products);
    }
}