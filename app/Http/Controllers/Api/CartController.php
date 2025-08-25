<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartItemResource;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validate the request
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        // 2. Hardcode the user ID as per requirements
        $userId = 1;

        // 3. Check if the product is already in the user's cart
        $cartItem = CartItem::where('user_id', $userId)
            ->where('product_id', $validated['product_id'])
            ->first();

        $statusCode = 200; // Default to 200 OK (for updates)

        if ($cartItem) {
            // If it exists, increment the quantity
            $cartItem->increment('quantity', $validated['quantity']);
            $message = 'Product quantity updated in cart.';
        } else {
            // If it doesn't exist, create a new cart item
            $cartItem = CartItem::create([
                'user_id' => $userId,
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
            ]);
            $message = 'Product added to cart.';
            $statusCode = 201; // 201 Created (for new resources)
        }

        // Eager load the product details for the response
        $cartItem->load('product.images');

        // 4. Return a successful response
        return response()->json([
            'message' => $message,
            'data' => new CartItemResource($cartItem),
        ], $statusCode);
    }

    public function index()
    {
        // Hardcode the user ID
        $userId = 1;

        // Get all cart items for the user, including product details
        $cartItems = CartItem::with('product.images')
            ->where('user_id', $userId)
            ->get();

        // Calculate totals
        $subtotal = $cartItems->sum(function ($item) {
            return $item->quantity * $item->product->price;
        });

        // You can add more calculations like tax, shipping, etc. here if needed
        // For now, let's keep it simple.
        $total = $subtotal;

        return response()->json([
            'data' => [
                'items' => CartItemResource::collection($cartItems),
                'totals' => [
                    'subtotal' => number_format($subtotal, 2, '.', ''),
                    'total' => number_format($total, 2, '.', ''),
                ]
            ]
        ]);
    }

    public function update(Request $request, CartItem $cartItem)
    {
        // Authorization check (ensure the item belongs to the hardcoded user)
        if ($cartItem->user_id != 1) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem->update(['quantity' => $validated['quantity']]);

        $cartItem->load('product.images');

        return response()->json([
            'message' => 'Cart item updated successfully.',
            'data' => new CartItemResource($cartItem),
        ]);
    }
    public function destroy(CartItem $cartItem)
    {
        // Authorization check
        if ($cartItem->user_id != 1) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $cartItem->delete();

        // Return a 204 No Content response, which is standard for successful deletions
        return response()->json(null, 204);
    }
}