<?php

namespace App\Http\Controllers;

use App\Models\Discounts;
use Illuminate\Http\Request;

class DiscountsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getAllDiscounts(Request $request)
    {
        $discounts = Discounts::all();

        return response()->json($discounts, 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function createAnDiscount(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'You cannot create a discount',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'value' => 'required|numeric',
        ]);

        $discount = Discounts::create($validated);

        return response()->json([
            'message' => 'Discount created successfully',
            'discount' => $discount,
        ], 201);
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
    public function show(Discounts $discounts)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Discounts $discounts)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateAnDiscount(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'You cannot update a discount',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'value' => 'required|numeric',
        ]);

        $discount = Discounts::find($id);

        if (!$discount) {
            return response()->json([
                'message' => 'Discount not found'
            ], 404);
        }

        $discount->update($validated);

        return response()->json([
            'message' => 'Discount updated successfully',
            'discount' => $discount,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroyAnDiscount(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'You cannot delete a discount',
            ], 403);
        }

        $discounts->delete();

        return response()->json([
            'message' => 'Discount deleted successfully',
        ], 200);
    }
}
