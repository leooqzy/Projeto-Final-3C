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
    public function getCategoriesID($id)
    {
        $categories = Categories::all();
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
    public function update(Request $request, Categories $categories)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Categories $categories)
    {
        //
    }
}
