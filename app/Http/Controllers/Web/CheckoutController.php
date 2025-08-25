<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // <-- FIX #1: Added Auth
use Illuminate\Support\Facades\DB;   // <-- FIX #2: Added DB

class CheckoutController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $cartItems = CartItem::with('product')->where('user_id', $userId)->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('dashboard')->with('info', 'Your cart is empty.');
        }

        $total = $cartItems->sum(function ($item) {
            return $item->quantity * $item->product->price;
        });

        return view('checkout.index', [
            'cartItems' => $cartItems,
            'total' => $total,
            'razorpayKey' => env('RAZORPAY_KEY_ID') // <-- ADD THIS LINE
        ]);
    }

    // This is the method we moved and fixed
    public function placeOrder(Request $request)
    {
        // 1. Validate the form data, including new fields
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'postal_code' => 'required|string|max:10',
            'phone' => 'required|string|max:20',
        ]);

        $userId = Auth::id();
        $cartItems = CartItem::with('product')->where('user_id', $userId)->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('dashboard')->with('info', 'Your cart is empty.');
        }

        // Use a database transaction
        DB::transaction(function () use ($userId, $cartItems, $validated) {
            $totalAmount = $cartItems->sum(fn($item) => $item->quantity * $item->product->price);

            // 2. Create the order with the new shipping details
            $order = Order::create([
                'user_id' => $userId,
                'total_amount' => $totalAmount,
                'status' => 'paid',
                'payment_gateway' => 'web_checkout',
                'customer_name' => $validated['name'],
                'customer_email' => $validated['email'],
                'shipping_address' => $validated['address'],
                'city' => $validated['city'],
                'postal_code' => $validated['postal_code'],
                'phone' => $validated['phone'],
            ]);

            // Create the order items
            foreach ($cartItems as $cartItem) {
                $order->items()->create([
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->product->price,
                ]);
            }

            // Clear the user's cart
            CartItem::where('user_id', $userId)->delete();
        });

        return redirect()->route('dashboard')->with('success', 'Your order has been placed successfully!');
    }

    // In app/Http/Controllers/Web/CheckoutController.php
    public function success()
    {
        // You can pass order details to this view if needed
        return view('checkout.success');
    }
}