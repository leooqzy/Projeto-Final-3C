<?php

namespace App\Http\Controllers;

use App\Models\Addresses;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressesController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function getUserAddresses(User $user)
    {
        $addresses = $user->addresses;
        return response()->json($addresses, 200);
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'street' => 'required|string|max:255',
            'number' => 'required|integer|max:255',
            'zip' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'country' => 'required|string|max:255',
        ]);

        $validated['user_id'] = auth()->user()->id;

        $addresses = Addresses::create($validated);

        return response()->json([
            'message' => 'Address created successfully',
            'addresses' => $addresses,
        ], 201);
    }

    public function destroy(Addresses $addresses)
    {
        $user = auth()->user();

        if ($user->id !== $addresses->user_id) {
            return response()->json([
                'message' => 'You are not authorized to delete this address',
            ], 401);
        }
        $addresses->delete();

        return response()->json([
            'message' => 'Address deleted successfully',
            'addresses' => $addresses,
        ], 200);
    }

    public function update(Request $request, Addresses $addresses)
    {


        $validated = $request->validate([
            'street' => 'required|string|max:255',
            'number' => 'required|integer|max:255',
            'zip' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'country' => 'required|string|max:255',
        ]);

        if ($addresses->user_id != Auth::user()->id) {
            return response()->json([
                'message' => 'You are not authorized to update this address',
            ], 401);
        }

        $addresses->update($validated);

        return response()->json([
            'message' => 'Address updated successfully',
            'addresses' => $addresses,
        ], 200);
    }

}