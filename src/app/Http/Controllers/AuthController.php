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

        $user = User::create($validated + [
            'role' => 'client'
        ]);

        return response()->json([
            'name' => $user->name,
            'email' => $user->email,
            'id' => $user->id,
            'role' => $user->role
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);
    
        if (!Auth::attempt($validated)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;
    
        return response()->json([
            'name' => $user->name,
            'email' => $user->email,
            'id' => $user->id,
            'role' => $user->role,
            'token' => $token,
        ], 200);
    }
    

    public function index()
    {
        $user = User::all();

        return response()->json($user, 200);
    }

    public function showMe()
    {
        $user = Auth::user();
        return response()->json([
            'name' => $user->name,
            'email' => $user->email,
            'id' => $user->id,
            'role' => $user->role,
            
        ], 200);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string',
            'email' => 'sometimes|required|string'
        ]);

        $user = Auth::user();
        $user->update($validated);

        return response()->json([
            'name' => $user->name,
            'email' => $user->email,
            'id' => $user->id,
            'role' => $user->role,
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
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'You cannot create a moderator',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::createAsModerator($validated);

        return response()->json([
            'name' => $user->name,
            'email' => $user->email,
            'id' => $user->id,
            'role' => $user->role,
        ], 201);
    }

    public function updateImage(Request $request)
    {
        $validated = $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        $user = Auth::user();
        $imagePath = $request->file('image')->store('users', 'public');
        $user->update(['image_path' => $imagePath]);

        return response()->json([
            'name' => $user->name,
            'email' => $user->email,
            'id' => $user->id,
            'role' => $user->role,
            'image_path' => $user->image_path,
        ], 200);
    }


}
