<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create($validated);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user,
        ], 201);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['token' => $token, 'user'  => $user], 201);
    }

    public function index()
    {
        $user = User::all();

        return response()->json($user, 200);
    }

    public function showMe()
    {
        return Auth::user();
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string',
        ]);

        $user = Auth::user();

        $user->update($validated);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user,
        ], 200);
    }

    public function destroy()
    {
        $user = Auth::user();
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
            'user' => $user,
        ], 200);
    }

    public function createModerator(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::createAsModerator($validated);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user,
        ], 201);
    }

}
