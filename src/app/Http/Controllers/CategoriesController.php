<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{

    public function getAllCategories()
    {
        $categories = Categories::all();

        return response()->json($categories, 200);
    }

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

    public function getCategoriesID(Request $request)
    {
        $categories = Categories::find($request->categories);

        return response()->json($categories, 200);
    }

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
