<?php

namespace App\Http\Controllers;

use App\Models\Cartitems;
use App\Models\Products;
use App\Models\Carts;
use Illuminate\Http\Request;

class CartitemsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $cart = Carts::where('user_id', $user->id)->first();
        if (!$cart) {
            return response()->json(['message' => 'No cart found for this user'], 404);
        }
        $items = Cartitems::where('cart_id', $cart->id)->get();
        return response()->json($items);
    }

    // Add item to cart
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);
        $product = \App\Models\Products::find($validated['product_id']);
        if ($product->stock < $validated['quantity']) {
            return response()->json(['message' => 'Insufficient stock'], 400);
        }
        $cart = \App\Models\Carts::firstOrCreate(['user_id' => $user->id]);
        $cartItem = \App\Models\Cartitems::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();
        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $validated['quantity'];
            if ($product->stock < $newQuantity) {
                return response()->json(['message' => 'Insufficient stock for requested quantity'], 400);
            }
            $cartItem->quantity = $newQuantity;
            $cartItem->unitPrice = $product->price;
            $cartItem->save();
        } else {
            $cartItem = \App\Models\Cartitems::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $validated['quantity'],
                'unitPrice' => $product->price,
            ]);
        }
        return response()->json(['message' => 'Item added to cart successfully', 'cart_item' => $cartItem], 201);
    }

    // Update item quantity in cart
    public function update(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);
        $cart = \App\Models\Carts::where('user_id', $user->id)->first();
        if (!$cart) {
            return response()->json(['message' => 'No cart found for this user'], 404);
        }
        $cartItem = \App\Models\Cartitems::where('cart_id', $cart->id)
            ->where('product_id', $validated['product_id'])
            ->first();
        if (!$cartItem) {
            return response()->json(['message' => 'Item not found in cart'], 404);
        }
        $product = \App\Models\Products::find($validated['product_id']);
        if ($product->stock < $validated['quantity']) {
            return response()->json(['message' => 'Insufficient stock'], 400);
        }
        $cartItem->quantity = $validated['quantity'];
        $cartItem->unitPrice = $product->price;
        $cartItem->save();
        return response()->json([
            'message' => 'Cart item quantity updated successfully!',
            'cart_item' => $cartItem
        ], 200);
    }

    // Remove item from cart
    public function destroy(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);
        $cart = \App\Models\Carts::where('user_id', $user->id)->first();
        if (!$cart) {
            return response()->json(['message' => 'No cart found for this user'], 404);
        }
        $cartItem = \App\Models\Cartitems::where('cart_id', $cart->id)
            ->where('product_id', $validated['product_id'])
            ->first();
        if (!$cartItem) {
            return response()->json(['message' => 'Item not found in cart'], 404);
        }
        $cartItem->delete();
        return response()->json(['message' => 'Item removed from cart successfully']);
    }
}
