<?php

namespace App\Http\Controllers;

use App\Models\Carts;
use Illuminate\Http\Request;

class CartsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        // Exemplo: Retorna todos os carrinhos do usuário autenticado
        $carts = Carts::where('user_id', $user->id)->get();
        return response()->json($carts);
    }


    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        \Log::info('DEBUG usuario autenticado', ['user' => $request->user()]);
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $cart = \App\Models\Carts::where('user_id', $user->id)->first();
        if ($cart) {
            return response()->json([
                'message' => 'Carrinho já existe para o usuário',
                'cart' => $cart
            ], 200);
        }

        $cart = new \App\Models\Carts();
        $cart->user_id = $user->id;
        $cart->createdAt = now();
        $cart->save();

        return response()->json([
            'message' => 'Carrinho criado com sucesso',
            'cart' => $cart
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Carts $carts)
    {
        $user = $request->user();
        if (!$user || $carts->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        return response()->json($carts);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Carts $carts)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Carts $carts)
    {

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Carts $carts)
    {
        $user = $request->user();
        if (!$user || $carts->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $carts->delete();
        return response()->json(['message' => 'Cart deleted successfully']);
    }
}
