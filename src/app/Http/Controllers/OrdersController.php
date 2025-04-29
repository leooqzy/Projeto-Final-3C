<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use App\Models\Carts;
use App\Models\CartItem;
use App\Models\Products;
use App\Models\Coupons;
use App\Models\Addresses;
use App\Models\Orderitems;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    public function getOrders(Request $request)
    {
        $user = $request->user();
        $orders = Orders::where('user_id', $user->id)
            ->with(['products' => function($query) {
                $query->select(
                'products.id',
                'products.name',
                'products.price',
                'products.stock',
                'products.category_id',
                'products.description',
                'products.image',
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
                        'image' => $product->image ? asset('storage/' . $product->image) : null,
                    ];
                }),
            ];
        });

        return response()->json($formatted, 200);
    }


    public function createOrder(Request $request)
    {
        $user = $request->user();

        $cart = Carts::where('user_id', $user->id)->first();
        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }
        $cartItems = CartItem::where('cart_id', $cart->id)->get();
        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty. Add products before making the order.'], 400);
        }

        $validated = $request->validate([
            'address_id' => 'required|integer|exists:addresses,id',
            'coupon_name' => 'sometimes|string|exists:coupons,code',
        ]);

        $address = Addresses::where('id', $validated['address_id'])
            ->where('user_id', $user->id)
            ->first();
        if (!$address) {
            return response()->json(['message' => 'Address not found or does not belong to the authenticated user.'], 403);
        }

        foreach ($cartItems as $item) {
            $product = Products::find($item->product_id);
            if (!$product || $product->stock < $item->quantity) {
                return response()->json([
                    'message' => 'Insufficient stock for product: ' . ($product ? $product->name : $item->product_id)
                ], 400);
            }
        }

        $order = new Orders();
        $order->user_id = $user->id;
        $order->address_id = $validated['address_id'];
        if (!empty($validated['coupon_name'])) {
    $coupon = Coupons::where('code', $validated['coupon_name'])->first();
    if (!$coupon) {
        return response()->json(['message' => 'Coupon not found'], 404);
    }
    $order->coupon_id = $coupon->id;
} else {
    $order->coupon_id = null;
}
        $order->orderDate = now();
        $order->status = 'PENDING';
        $order->save();

        foreach ($cartItems as $item) {
            $product = Products::with('discount')->find($item->product_id);
            $discount = 0;
            if ($product->discount) {
                $discount = $product->discount->discountPercentage ?? 0;
            } elseif (isset($product->discount_percentage)) {
                $discount = $product->discount_percentage;
            }
            $discountedPrice = $item->unitPrice * (1 - $discount / 100);

            $product->stock -= $item->quantity;
            $product->save();
            $order->products()->attach($item->product_id, [
                'quantity' => $item->quantity,
                'price' => $discountedPrice
            ]);
        }
        $order->load(['products' => function($query) {
            $query->select(
                'products.id',
                'products.name',
                'products.price',
                'products.stock',
                'products.category_id',
                'products.description',
                'products.image',
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
        $discount_product = 0;
        foreach ($order->products as $product) {
            $unit_price = $product->price;
            $quantity = $product->pivot->quantity ?? 1;
            $discount = 0;
            if ($product->discount) {
                $discount = $product->discount->discountPercentage ?? 0;
            } elseif (isset($product->discount_percentage)) {
                $discount = $product->discount_percentage;
            }
            $discount_product += ($unit_price * $discount / 100) * $quantity;
            $subtotal += $unit_price * $quantity;
        }
        $value_with_discount_product = $subtotal - $discount_product;
        $discount_coupon = 0;
        if ($order->coupon_id) {
            $coupon = Coupons::find($order->coupon_id);
            if ($coupon) {
                $discountValue = $coupon->discountPercentage;
                if ($discountValue > 1) {
                    $discountValue = $discountValue / 100;
                }
                $discount_coupon = $value_with_discount_product * $discountValue;
            }
        }
        $total = $value_with_discount_product - $discount_coupon;
        $response['subtotal'] = round($subtotal, 2);
        $response['discount_product'] = round($discount_product, 2);
        $response['discount_coupon'] = round($discount_coupon, 2);
        $response['total'] = round($total, 2);

        return response()->json($response, 201);
    }

    public function showOrder(Request $request, $id)
    {
        $user = $request->user();
        $order = Orders::where('id', $id)
            ->where('user_id', $user->id)
            ->with(['products' => function($query) {
                $query->select(
                'products.id',
                'products.name',
                'products.price',
                'products.stock',
                'products.category_id',
                'products.description',
                'products.image',
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
        $value_with_discount_product = $subtotal;
        $discount_coupon = 0;
        if ($order->coupon_id) {
            $coupon = Coupons::find($order->coupon_id);
            if ($coupon) {
                $discount_coupon = $value_with_discount_product * ($coupon->discountPercentage / 100);
            }
        }
        $response['total'] = round($total, 2);
        $response['subtotal'] = round($subtotal, 2);
        return response()->json($response, 200);
    }

    public function updateOrder(Request $request, $id)
    {
        $user = $request->user();
        if (!in_array($user->role, ['admin', 'moderator'])) {
            return response()->json([
                'message' => 'You do not have permission to update an order',
            ], 403);
        }
        $order = Orders::where('id', $id)
            ->where('user_id', $user->id)
            ->with(['products' => function($query) {
                $query->select(
                'products.id',
                'products.name',
                'products.price',
                'products.stock',
                'products.category_id',
                'products.description',
                'products.image',
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
                'products.price',
                'products.stock',
                'products.category_id',
                'products.description',
                'products.image',
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

    public function destroyOrder(Request $request, $id)
    {
        $user = $request->user();
        $order = Orders::where('id', $id)->where('user_id', $user->id)->first();
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        foreach ($order->products as $product) {
            $quantity = $product->pivot->quantity ?? 1;
            $product->stock += $quantity;
            $product->save();
        }

        Orderitems::where('order_id', $order->id)->delete();
        $order->products()->detach();
        $order->delete();
        return response()->json(['message' => 'Order deleted successfully'], 200);
    }
}
