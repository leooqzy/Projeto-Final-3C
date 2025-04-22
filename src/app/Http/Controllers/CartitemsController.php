<?php

namespace App\Http\Controllers;

use App\Models\Cartitems;
use App\Models\Products;
use App\Models\Carts;
use Illuminate\Http\Request;

class CartitemsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
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

        $product = Products::find($validated['product_id']);
        if (!$product) {
            return response()->json(['message' => 'Produto não encontrado'], 404);
        }
        if ($product->stock < $validated['quantity']) {
            return response()->json(['message' => 'Estoque insuficiente'], 400);
        }

        $cart = Carts::where('user_id', $user->id)->first();
        if (!$cart) {
            $cart = new Carts();
            $cart->user_id = $user->id;
            $cart->save();
        }

        $cartItem = Cartitems::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();
        if ($cartItem) {
            $novaQuantidade = $cartItem->quantity + $validated['quantity'];
            if ($product->stock < $novaQuantidade) {
                return response()->json(['message' => 'Estoque insuficiente para a quantidade solicitada'], 400);
            }
            $cartItem->quantity = $novaQuantidade;
            $cartItem->unitPrice = $product->price;
            $cartItem->save();
        } else {
            Cartitems::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $validated['quantity'],
                'unitPrice' => $product->price,
            ]);
        }

        return response()->json(['message' => 'Item adicionado ao carrinho com sucesso'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Cartitems $cartitems)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cartitems $cartitems)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cartitems $cartitems)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cartitems $cartitems)
    {
        //
    }
}
