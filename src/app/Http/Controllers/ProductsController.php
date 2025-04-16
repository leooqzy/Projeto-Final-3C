<?php

namespace App\Http\Controllers;

use App\Models\Categories;
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
            'price' => 'required|:numeric',
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
    public function getProductsByCategory($categoryId)
    {
        $products = Products::where('category_id', $categoryId)->get(['id', 'name', 'user_id']);
        return response()->json($products);
    }

    /**
     * Display the specified resource.
     */
    public function updateProduct(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'You cannot update a product',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'stock' => 'required|integer',
            'price' => 'required|:numeric',
            'category_id' => 'required|exists:categories,id',
        ]);

        $product = Products::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }

        $product->update($validated);

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product,
        ], 200);
    }

    public function destroyProduct(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'You cannot delete a product',
            ], 403);
        }

        $product = Products::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully',
        ], 200);
    }


    /**
     * Update the specified resource in storage.
     */
    public function updateStock(Request $request, Products $products)
    {
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Products $products)
    {
        //
    }
}
