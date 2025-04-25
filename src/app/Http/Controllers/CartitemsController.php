<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Products;
use App\Models\Carts;
use Illuminate\Http\Request;

class CartitemsController extends Controller
{
    public function getItemsCart(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $cart = Carts::where('user_id', $user->id)->first();
        if (!$cart) {
            return response()->json(['message' => 'No cart found for this user'], 404);
        }

        $items = CartItem::where('cart_id', $cart->id)->get();
        $itemsArray = [];
        $totalAmount = 0;

        foreach ($items as $item) {
            $product = Products::find($item->product_id);
            

            $itemsArray[] = [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unitPrice,
                'id' => $item->id,
                'cart_id' => $item->cart_id,
                
            ];

            $totalAmount += $item->quantity * $item->unitPrice;
        }
        $response = [
            'cart_id' => $cart->id,
            'total_amount' => $totalAmount,
            'items' => $itemsArray
        ];
        return response()->json($response);
}

    public function AddItemCart(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);
        $product = Products::find($validated['product_id']);
        if ($product->stock < $validated['quantity']) {
            return response()->json(['message' => 'Insufficient stock'], 400);
        }
        $cart = Carts::firstOrCreate(['user_id' => $user->id]);
        $cartItem = CartItem::where('cart_id', $cart->id)
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
            $cartItem = CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $validated['quantity'],
                'unitPrice' => $product->price,
            ]);
        }
        return response()->json(['message' => 'Item added to cart successfully', 'cart_item' => $cartItem], 201);
    }

    public function updateItemCart(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['detail' => [['msg' => 'Unauthorized', 'type' => 'auth']]], 401);
        }
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
            ]);
        } catch (ValidationException $e) {
            $errors = [];
            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $msg) {
                    $errors[] = [
                        'loc' => [$field],
                        'msg' => $msg,
                        'type' => 'validation'
                    ];
                }
            }
            return response()->json(['detail' => $errors], 422);
        }
        $cart = Carts::where('user_id', $user->id)->first();
        if (!$cart) {
            return response()->json(['detail' => [['msg' => 'No cart found for this user', 'type' => 'not_found']]], 404);
        }
        $cartItem = Cartitem::where('cart_id', $cart->id)
            ->where('product_id', $validated['product_id'])
            ->first();
        if (!$cartItem) {
            return response()->json(['detail' => [['msg' => 'Item not found in cart', 'type' => 'not_found']]], 404);
        }
        $product = Products::find($validated['product_id']);
        if ($product->stock < $validated['quantity']) {
            return response()->json(['detail' => [['msg' => 'Insufficient stock', 'type' => 'stock']]], 422);
        }
        $cartItem->quantity = $validated['quantity'];
        $cartItem->unitPrice = $product->price;
        $cartItem->save();
        return response()->json(['message' => 'Cart item quantity updated successfully!'], 200);
    }

    public function destroyItemCart(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['detail' => [['msg' => 'Unauthorized', 'type' => 'auth']]], 401);
        }
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
            ]);
        } catch (ValidationException $e) {
            $errors = [];
            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $msg) {
                    $errors[] = [
                        'loc' => [$field],
                        'msg' => $msg,
                        'type' => 'validation'
                    ];
                }
            }
            return response()->json(['detail' => $errors], 422);
        }
        $cart = Carts::where('user_id', $user->id)->first();
        if (!$cart) {
            return response()->json(['detail' => [['msg' => 'No cart found for this user', 'type' => 'not_found']]], 404);
        }
        $cartItem = Cartitem::where('cart_id', $cart->id)
            ->where('product_id', $validated['product_id'])
            ->first();
        if (!$cartItem) {
            return response()->json(['detail' => [['msg' => 'Item not found in cart', 'type' => 'not_found']]], 404);
        }
        $cartItem->delete();
        return response()->json(['message' => 'Item removed from cart successfully'], 200);
    }

    public function clear(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['detail' => [['msg' => 'Unauthorized', 'type' => 'auth']]], 401);
        }
        $cart = Carts::where('user_id', $user->id)->first();
        if (!$cart) {
            return response()->json(['detail' => [['msg' => 'No cart found for this user', 'type' => 'not_found']]], 404);
        }
        Cartitem::where('cart_id', $cart->id)->delete();
        return response()->json(['message' => 'Cart cleared successfully'], 200);
    }
}