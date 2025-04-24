<?php

namespace App\Http\Controllers;

use App\Models\Discounts;
use Illuminate\Http\Request;

class DiscountsController extends Controller
{

    public function getAllDiscounts(Request $request)
    {
        $discounts = Discounts::all()->map(function($discount) {
            return [
                'description' => $discount->description,
                'discountPercentage' => $discount->discountPercentage,
                'startDate' => $discount->startDate,
                'endDate' => $discount->endDate,
                'product_id' => $discount->product_id,
                'id' => $discount->id,
            ];
        });

        return response()->json($discounts, 200);
    }

    public function createAnDiscount(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'You cannot create a discount',
            ], 403);
        }

        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'discountPercentage' => 'required|numeric',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
            'product_id' => 'required|integer|exists:products,id',
        ]);

        $discount = Discounts::create($validated);

        return response()->json([
            'description' => $discount->description,
            'discountPercentage' => $discount->discountPercentage,
            'startDate' => $discount->startDate,
            'endDate' => $discount->endDate,
            'product_id' => $discount->product_id,
            'id' => $discount->id,
        ], 201);
    }

    public function updateAnDiscount(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'You cannot update a discount',
            ], 403);
        }

        $validated = $request->validate([
            'description' => 'sometimes|required|string|max:255',
            'discountPercentage' => 'sometimes|required|numeric',
            'startDate' => 'sometimes|required|date',
            'endDate' => 'sometimes|required|date|after_or_equal:startDate',
            'product_id' => 'sometimes|required|integer|exists:products,id',
        ]);

        $discount = Discounts::find($id);

        if (!$discount) {
            return response()->json([
                'message' => 'Discount not found'
            ], 404);
        }

        $discount->update($validated);

        return response()->json([
            'description' => $discount->description,
            'discountPercentage' => $discount->discountPercentage,
            'startDate' => $discount->startDate,
            'endDate' => $discount->endDate,
            'product_id' => $discount->product_id,
            'id' => $discount->id,
        ], 200);
    }

    public function getSpecificDiscount($id)
    {
        $discount = Discounts::find($id);

        if (!$discount) {
            return response()->json(['message' => 'Discount not found'], 404);
        }

        return response()->json([
            'description' => $discount->description,
            'discountPercentage' => $discount->discountPercentage,
            'startDate' => $discount->startDate,
            'endDate' => $discount->endDate,
            'product_id' => $discount->product_id,
            'id' => $discount->id,
        ], 200);
    }

    public function destroyAnDiscount(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'You cannot delete a discount',
            ], 403);
        }

        $discount = Discounts::find($id);
        if (!$discount) {
            return response()->json([
                'message' => 'Discount not found'
            ], 404);
        }

        $discount->delete();

        return response()->json([
            'message' => 'Discount deleted successfully'
        ], 200);
    }
}
