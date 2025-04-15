<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getAllCategories()
    {
        $categories = Categories::all();

        return response()->json($categories, 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function createCategorie(Request $request)
    {

        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'You cannot create a category',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
        ]);

        $user = Categories::create($validated);

        return response()->json([
            'message' => 'Category created successfully',
            'category' => $user,
        ], 201);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function getCategoriesID(Request $request)
    {
        $categories = Categories::find($request->categories);

        return response()->json($categories, 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Categories $categories)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Categories $categories)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateCategorie(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'You cannot update a category',
            ], 403);
        }
    
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
        ]);
    
        $category = Categories::find($id);
    
        if (!$category) {
            return response()->json([
                'message' => 'Category not found'
            ], 404);
        }
    
        $category->update($validated);
    
        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroyCategorie(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'You cannot delete a category',
            ], 403);
        }

        $category = Categories::find($id);

        if (!$category) {
            return response()->json([
                'message' => 'Category not found'
            ], 404);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully',
        ], 200);
    }
}
