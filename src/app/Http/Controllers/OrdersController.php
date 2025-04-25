<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $orders = Orders::where('user_id', $user->id)
            ->with(['products' => function($query) {
                $query->select('products.id', 'name', 'price', 'stock', 'category_id', 'image_path', 'description');
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
                        'price' => $product->price,
                        'stock' => $product->stock,
                        'category_id' => $product->category_id,
                        'image_path' => $product->image_path,
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

        $order->load(['products' => function($query) {
            $query->select('products.id', 'name', 'price', 'stock', 'category_id', 'image_path', 'description');
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
                    'price' => $product->price,
                    'stock' => $product->stock,
                    'category_id' => $product->category_id,
                    'image_path' => $product->image_path,
                    'description' => $product->description,
                ];
            }),
        ];

        return response()->json($response, 201);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $order = Orders::where('id', $id)
            ->where('user_id', $user->id)
            ->with(['products' => function($query) {
                $query->select('products.id', 'name', 'price', 'stock', 'category_id', 'image_path', 'description');
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
                    'price' => $product->price,
                    'stock' => $product->stock,
                    'category_id' => $product->category_id,
                    'image_path' => $product->image_path,
                    'description' => $product->description,
                ];
            }),
        ];
        return response()->json($response, 200);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $order = Orders::where('id', $id)
            ->where('user_id', $user->id)
            ->with(['products' => function($query) {
                $query->select('products.id', 'name', 'price', 'stock', 'category_id', 'image_path', 'description');
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
            $query->select('products.id', 'name', 'price', 'stock', 'category_id', 'image_path', 'description');
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
                    'price' => $product->price,
                    'stock' => $product->stock,
                    'category_id' => $product->category_id,
                    'image_path' => $product->image_path,
                    'description' => $product->description,
                ];
            }),
        ];
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
