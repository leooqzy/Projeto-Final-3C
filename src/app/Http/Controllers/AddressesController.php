<?php

namespace App\Http\Controllers;

use App\Models\Addresses;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressesController extends Controller
{

    public function myAddresses(Request $request)
    {
        $user = $request->user();
        $addresses = $user->addresses;
        return response()->json($addresses, 200);
    }

    public function getUserAddresses(User $user)
    {
        $authUser = auth()->user();
        if ($authUser->role !== 'admin' && $authUser->id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $addresses = $user->addresses;
        return response()->json($addresses, 200);
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'street' => 'required|string|max:255',
            'number' => 'required|integer',
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

        if ($user->role !== 'admin' && $user->id !== $addresses->user_id) {
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
            'number' => 'required|integer',
            'zip' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'country' => 'required|string|max:255',
        ]);

        if (Auth::user()->role !== 'admin' && $addresses->user_id != Auth::user()->id) {
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

    public function show(Addresses $addresses)
    {
        $user = auth()->user();
        if ($user->role !== 'admin') {
            return response()->json([
                'message' => 'Only admin can view this address',
            ], 403);
        }
        return response()->json($addresses, 200);
    }

}