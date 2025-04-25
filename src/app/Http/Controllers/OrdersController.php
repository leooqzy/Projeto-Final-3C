<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use App\Models\Carts;
use App\Models\CartItem;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $orders = Orders::where('user_id', $user->id)
            ->with(['products' => function($query) {
                $query->select(
    'products.id',
    'products.name',
    'products.price as product_price',
    'products.stock',
    'products.category_id',
    'products.description',
    'orders_items.price as item_price',
'orders_items.quantity as quantity'
);
            }])
            ->get();

        $formatted = $orders->map(function($order) {
            return [
                'address_id' => $order->address_id,
                'coupon_id' => $order->coupon_id,
                'id' => $order->id,
                'order_date' => $order->orderDate,
                'status' => $order->status,
                'products' => $order->products->map(function($product) {
                    return [
                        'name' => $product->name,
                        'stock' => $product->stock,
                        'category_id' => $product->category_id,
                        'description' => $product->description,
                    ];
                }),
            ];
        });

        return response()->json($formatted, 200);
    }


    public function store(Request $request)
    {
        $user = $request->user();

        $cart = Carts::where('user_id', $user->id)->first();
        if (!$cart) {
            return response()->json(['message' => 'Carrinho não encontrado para este usuário.'], 404);
        }
        $cartItems = CartItem::where('cart_id', $cart->id)->get();
        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'O carrinho está vazio. Adicione produtos antes de fazer o pedido.'], 400);
        }

        $validated = $request->validate([
            'address_id' => 'required|integer|exists:addresses,id',
            'coupon_id' => 'nullable|integer|exists:coupons,id',
        ]);

        $order = new Orders();
        $order->user_id = $user->id;
        $order->address_id = $validated['address_id'];
        $order->coupon_id = $validated['coupon_id'] ?? null;
        $order->orderDate = now();
        $order->status = 'PENDING';
        $order->save();

        foreach ($cartItems as $item) {
            $order->products()->attach($item->product_id, [
                'quantity' => $item->quantity,
                'price' => $item->unitPrice
            ]);
        }
        $order->load(['products' => function($query) {
            $query->select(
    'products.id',
    'products.name',
    'products.price as product_price',
    'products.stock',
    'products.category_id',
    'products.description',
    'orders_items.price as item_price',
'orders_items.quantity as quantity'
);
        }]);

        $response = [
            'address_id' => $order->address_id,
            'coupon_id' => $order->coupon_id,
            'id' => $order->id,
            'order_date' => $order->orderDate,
            'status' => $order->status,
            'products' => $order->products->map(function($product) {
                return [
                    'name' => $product->name,
                    'stock' => $product->stock,
                    'category_id' => $product->category_id,
                    'description' => $product->description,
                ];
            }),
        ];

        $subtotal = 0;
        foreach ($order->products as $product) {
            $discount = isset($product->discount_percentage) ? $product->discount_percentage : 0;
            $subtotal += $product->price * (1 - $discount / 100);
        }
        $total = $subtotal;
        if ($order->coupon_id) {
            $coupon = Coupons::find($order->coupon_id);
            if ($coupon) {
                $total = $total * (1 - $coupon->discountPercentage / 100);
            }
        }
        $response['total'] = round($total, 2);
        $response['subtotal'] = round($subtotal, 2);

        return response()->json($response, 201);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $order = Orders::where('id', $id)
            ->where('user_id', $user->id)
            ->with(['products' => function($query) {
                $query->select(
    'products.id',
    'products.name',
    'products.price as product_price',
    'products.stock',
    'products.category_id',
    'products.description',
    'orders_items.price as item_price',
'orders_items.quantity as quantity'
);
            }])
            ->first();
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
        $response = [
            'address_id' => $order->address_id,
            'coupon_id' => $order->coupon_id,
            'id' => $order->id,
            'order_date' => $order->orderDate,
            'status' => $order->status,
            'products' => $order->products->map(function($product) {
                return [
                    'name' => $product->name,
                    'stock' => $product->stock,
                    'category_id' => $product->category_id,
                    'description' => $product->description,
                ];
            }),
        ];

        $subtotal = 0;
        foreach ($order->products as $product) {
            $discount = isset($product->discount_percentage) ? $product->discount_percentage : 0;
            $subtotal += $product->price * (1 - $discount / 100);
        }
        $total = $subtotal;
        if ($order->coupon_id) {
            $coupon = Coupons::find($order->coupon_id);
            if ($coupon) {
                $total = $total * (1 - $coupon->discountPercentage / 100);
            }
        }
        $response['total'] = round($total, 2);
        $response['subtotal'] = round($subtotal, 2);
        return response()->json($response, 200);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $order = Orders::where('id', $id)
            ->where('user_id', $user->id)
            ->with(['products' => function($query) {
                $query->select(
    'products.id',
    'products.name',
    'products.price as product_price',
    'products.stock',
    'products.category_id',
    'products.description',
    'orders_items.price as item_price',
'orders_items.quantity as quantity'
);
            }])
            ->first();
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
        $validated = $request->validate([
            'status' => 'required|string',
        ]);
        $order->status = $validated['status'];
        $order->save();
        $order->refresh();
        $order->load(['products' => function($query) {
            $query->select(
    'products.id',
    'products.name',
    'products.price as product_price',
    'products.stock',
    'products.category_id',
    'products.description',
    'orders_items.price as item_price',
'orders_items.quantity as quantity'
);
        }]);
        $response = [
            'address_id' => $order->address_id,
            'coupon_id' => $order->coupon_id,
            'id' => $order->id,
            'order_date' => $order->orderDate,
            'status' => $order->status,
            'products' => $order->products->map(function($product) {
                return [
                    'name' => $product->name,
                    'stock' => $product->stock,
                    'category_id' => $product->category_id,
                    
                    'description' => $product->description,
                    'discount_percentage' => $product->discount_percentage ?? 0,
'quantity' => $product->quantity,
                ];
            }),
        ];

        $subtotal = 0;
        foreach ($order->products as $product) {
            $discount = isset($product->discount_percentage) ? $product->discount_percentage : 0;
            $subtotal += $product->price * (1 - $discount / 100);
        }
        $total = $subtotal;
        if ($order->coupon_id) {
            $coupon = Coupons::find($order->coupon_id);
            if ($coupon) {
                $total = $total * (1 - $coupon->discountPercentage / 100);
            }
        }
        $response['total'] = round($total, 2);
        $response['subtotal'] = round($subtotal, 2);
        return response()->json($response, 200);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $order = Orders::where('id', $id)->where('user_id', $user->id)->first();
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
        $order->delete();
        return response()->json(['message' => 'Order deleted successfully'], 204);
    }
}
