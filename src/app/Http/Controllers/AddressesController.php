<?php

namespace App\Http\Controllers;

use App\Models\Addresses;
use Illuminate\Http\Request;

class AddressesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $addresses = Addresses::all();

        return response()->json($addresses, 200);
    }

    /**
     * Show the form for creating a new resource.
     */
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
    public function show(Addresses $addresses)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Addresses $addresses)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Addresses $addresses)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
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
}
