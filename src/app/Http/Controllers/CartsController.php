<?php

namespace App\Http\Controllers;

use App\Models\Carts;
use Illuminate\Http\Request;

class CartsController extends Controller
{

    // Get the user's cart
    public function getCart(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $cart = Carts::where('user_id', $user->id)->first();
        if (!$cart) {
            return response()->json(['message' => 'No cart found for this user'], 404);
        }
        return response()->json($cart);
    }

    // Create a cart for the user (only one allowed)
    public function createCart(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $cart = Carts::firstOrCreate(['user_id' => $user->id], [
            'createdAt' => now(),
        ]);
        return response()->json($cart, 201);
    }

    // Clear the cart for the user
    public function clearCart(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $cart = Carts::where('user_id', $user->id)->first();
        if (!$cart) {
            return response()->json(['message' => 'No cart found for this user'], 404);
        }
        $cart->cartitems()->delete();
        return response()->json(['message' => 'Cart cleared successfully']);
    }

}
