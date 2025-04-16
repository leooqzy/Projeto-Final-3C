<?php

namespace App\Http\Controllers;

use App\Models\Products;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getAllProducts(Request $request)
    {
        return Products::all();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function createAnProduct(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'You cannot create a product',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'stock' => 'required|integer',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
        ]);

        $product = Products::create($validated + [
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product,
        ], 201);
    }

    public function getProductsByUser($userId)
    {
        $products = Products::where('user_id', $userId)->get(['id', 'name', 'user_id']);
        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Products $products)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Products $products)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Products $products)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Products $products)
    {
        //
    }
}
