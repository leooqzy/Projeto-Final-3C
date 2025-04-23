<?php

namespace App\Http\Controllers;

use App\Models\Carts;
use Illuminate\Http\Request;

class CartsController extends Controller
{

    public function getCart(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['detail' => [['msg' => 'Unauthorized', 'type' => 'auth']]], 401);
        }
        $cart = Carts::where('user_id', $user->id)->first();
        if (!$cart) {
            return response()->json(['detail' => [['msg' => 'No cart found for this user', 'type' => 'not_found']]], 404);
        }
        return response()->json($cart);
    }

    public function createCart(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $cart = Carts::where('user_id', $user->id)->first();
        if ($cart) {
            return response()->json(['message' => 'You already have a cart'], 400);
        }
        $cart = Carts::create([
            'user_id' => $user->id,
            'createdAt' => now(),
        ]);
        return response()->json($cart, 201);
    }

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
