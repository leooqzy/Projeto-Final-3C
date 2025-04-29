<?php

namespace App\Http\Controllers;

use App\Models\Coupons;
use Illuminate\Http\Request;

class CouponsController extends Controller
{

    public function createAnCoupon(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'You do not have permission to create a coupon'], 403);
        }
        $validated = $request->validate([
            'code' => 'required|string|unique:coupons,code',
            'discount_percentage' => 'required|numeric|min:0|max:30',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $coupon = Coupons::create([
            'code' => $validated['code'],
            'discountPercentage' => $validated['discount_percentage'],
            'startDate' => $validated['start_date'],
            'endDate' => $validated['end_date'],
        ]);

        return response()->json($coupon, 201);
    }

    public function getAllCoupons()
    {
        $coupons = Coupons::all();
        return response()->json($coupons);
    }

    public function getSpecificCoupon($id)
    {
        $coupon = Coupons::find($id);
        if (!$coupon) {
            return response()->json(['message' => 'Coupon not found'], 404);
        }
        return response()->json($coupon);
    }

    public function updateCoupon(Request $request, $id)
    {
        $coupon = Coupons::find($id);
        if (!$coupon) {
            return response()->json(['message' => 'Coupon not found'], 404);
        }
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'You do not have permission to update a coupon'], 403);
        }

        $validated = $request->validate([
            'code' => 'sometimes|required|string|unique:coupons,code,' . $id,
            'discount_percentage' => 'sometimes|required|numeric|min:0|max:30',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after:start_date',
        ]);

        if (isset($validated['code'])) {
            $coupon->code = $validated['code'];
        }
        if (isset($validated['discount_percentage'])) {
            $coupon->discountPercentage = $validated['discount_percentage'];
        }
        if (isset($validated['start_date'])) {
            $coupon->startDate = $validated['start_date'];
        }
        if (isset($validated['end_date'])) {
            $coupon->endDate = $validated['end_date'];
        }

        $coupon->save();
        return response()->json($coupon);
    }

    public function deleteCoupon(Request $request, $id)
    {
        $coupon = Coupons::find($id);
        if (!$coupon) {
            return response()->json(['message' => 'Coupon not found'], 404);
        }
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'You do not have permission to delete a coupon'], 403);
        }
        $coupon->delete();
        return response()->json(['message' => 'Coupon deleted successfully'], 200);
    }




}
