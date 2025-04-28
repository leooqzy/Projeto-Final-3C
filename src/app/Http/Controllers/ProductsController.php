<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductsController extends Controller
{

    public function getAllProducts(Request $request)
    {
        $products = Products::with('discount')->get();
        $products->transform(function ($product) {
            $discount = $product->discount;
            $product->image_url = $product->image ? asset('storage/' . $product->image) : null;
            $product->discount_percentage = $discount ? $discount->discountPercentage : 0;
            return $product;
        });
        return response()->json($products);
    }

    public function createAnProduct(Request $request)
    {
        if (!in_array($request->user()->role, ['admin', 'moderator'])) {
            return response()->json([
                'message' => 'You cannot create a product',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'stock' => 'required|integer',
            'price' => 'required|numeric',
            'image' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string|max:1000',
        ]);

        $path = $request->file('image')->store('products', 'public');
        unset($validated['image']);

        $product = Products::create($validated + [
            'user_id' => $request->user()->id,
            'image' => $path,
        ]);

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product,
            'image_url' => $product->image ? asset('storage/' . $product->image) : null,
        ], 201);
    }

    public function getProductsByUser($userId)
    {
        $products = Products::where('user_id', $userId)->get(['id', 'name', 'user_id', 'image']);
        $products->transform(function ($product) {
            $product->image_url = $product->image ? asset('storage/' . $product->image) : null;
            return $product;
        });
        return response()->json($products);
    }

    public function getProductsByCategory($categoryId)
    {
        $products = Products::with('discount')->where('category_id', $categoryId)->get();
        $products->transform(function ($product) {
            $discount = $product->discount;
            $product->image_url = $product->image ? asset('storage/' . $product->image) : null;
            $product->discount_percentage = $discount ? $discount->discountPercentage : 0;
            $product->discount = $discount;
            return $product;
        });
        return response()->json($products);
    }

    public function showProduct($id)
    {
        $product = Products::with('discount')->find($id);
        if (!$product) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }

        $discount = $product->discount;

        return response()->json([
            'id' => $product->id,
            'category_id' => $product->category_id,
            'user_id' => $product->user_id,
            'name' => $product->name,
            'stock' => $product->stock,
            'price' => $product->price,
            'discount_percentage' => $discount ? $discount->discountPercentage : 0,
            'description' => $product->description,
            'created_at' => $product->created_at,
            'updated_at' => $product->updated_at,
            'image_url' => $product->image ? asset('storage/' . $product->image) : null,
        ], 200);
    }

    public function updateProduct(Request $request, $id)
    {
        if (!in_array($request->user()->role, ['admin', 'moderator'])) {
            return response()->json([
                'message' => 'You cannot update a product',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'stock' => 'required|integer',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
        ]);

        $product = Products::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }

        $product->update($validated);

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product,
        ], 200);
    }

    public function destroyProduct(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'You cannot delete a product',
            ], 403);
        }

        $product = Products::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully',
        ], 200);
    }

    public function updateStockProduct(Request $request, $id)
    {
        if (!in_array($request->user()->role, ['admin', 'moderator'])) {
            return response()->json([
                'message' => 'You cannot update a stock',
            ], 403);
        }

        $validated = $request->validate([
            'stock' => 'required|integer',
        ]);

        $product = Products::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }

        $product->update($validated);

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product,
            'image_url' => $product->image ? asset('storage/' . $product->image) : null,
        ], 200);
    }

    public function showImage($id)
    {
        $product = Products::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'image_url' => $product->image ? asset('storage/' . $product->image) : null
        ]);

    }

    public function updateImage(Request $request, $id)
    {
        if (!in_array($request->user()->role, ['admin', 'moderator'])) {
            return response()->json([
                'message' => 'You cannot update an image',
            ], 403);
        }

        $validated = $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        $product = Products::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }

        if ($request->hasFile('image')) {
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $path = $request->file('image')->store('products', 'public');
            $validated['image'] = $path;
        }

        $product->update($validated);

        return response()->json([
            'message' => 'Product image updated successfully',
            'product' => $product,
            'image_url' => $product->image ? asset('storage/' . $product->image) : null,
        ], 200);
    }

}
