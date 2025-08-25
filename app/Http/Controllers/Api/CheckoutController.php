<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

class CheckoutController extends Controller
{
    /**
     * Step 1: Validates shipping info, creates a Razorpay order with notes.
     */
    public function createRazorpayOrder(Request $request)
    {
        // 1. Validate the incoming shipping details from the form
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'postal_code' => 'required|string|max:10',
            'phone' => 'required|string|max:20',
        ]);

        $userId = 1; // Hardcoded user ID
        $cartItems = CartItem::with('product')->where('user_id', $userId)->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty.'], 400);
        }

        $totalAmount = $cartItems->sum(fn($item) => $item->quantity * $item->product->price);

        $api = new Api(env('RAZORPAY_KEY_ID'), env('RAZORPAY_KEY_SECRET'));

        // 2. Create the Razorpay order, attaching shipping details in 'notes'
        $razorpayOrder = $api->order->create([
            'receipt' => 'rcpt_' . time(),
            'amount' => $totalAmount * 100,
            'currency' => 'INR',
            'payment_capture' => 1,
            'notes' => $validated // This is where we pass the shipping data to Razorpay
        ]);

        return response()->json([
            'razorpay_order_id' => $razorpayOrder['id'],
            'amount' => $razorpayOrder['amount'],
            'currency' => $razorpayOrder['currency'],
        ]);
    }

    /**
     * Step 2: Verify payment and create the order in our database using notes.
     */
    public function verifyPayment(Request $request)
    {
        $validated = $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $api = new Api(env('RAZORPAY_KEY_ID'), env('RAZORPAY_KEY_SECRET'));

        try {
            $attributes = [
                'razorpay_order_id' => $validated['razorpay_order_id'],
                'razorpay_payment_id' => $validated['razorpay_payment_id'],
                'razorpay_signature' => $validated['razorpay_signature']
            ];
            $api->utility->verifyPaymentSignature($attributes);

            // 3. If signature is verified, fetch the order from Razorpay to get the notes
            $razorpayOrder = $api->order->fetch($validated['razorpay_order_id']);

            // 4. Pass the full Razorpay order object to create the order in our DB
            return $this->createOrderInDatabase($razorpayOrder);

        } catch (SignatureVerificationError $e) {
            return response()->json(['message' => 'Payment verification failed. Invalid signature.'], 400);
        }
    }

    /**
     * Helper method to create the order using data from the Razorpay order object.
     */
    private function createOrderInDatabase($razorpayOrder)
    {
        return DB::transaction(function () use ($razorpayOrder) {
            $userId = 1;
            $shippingDetails = $razorpayOrder['notes']; // Get the shipping data back from notes

            $order = Order::create([
                'user_id' => $userId,
                'total_amount' => $razorpayOrder['amount'] / 100, // Convert from paise
                'status' => 'paid',
                'payment_gateway' => 'razorpay',
                'customer_name' => $shippingDetails['name'],
                'customer_email' => $shippingDetails['email'],
                'shipping_address' => $shippingDetails['address'],
                'city' => $shippingDetails['city'],
                'postal_code' => $shippingDetails['postal_code'],
                'phone' => $shippingDetails['phone'],
            ]);

            $cartItems = CartItem::with('product')->where('user_id', $userId)->get();
            foreach ($cartItems as $cartItem) {
                $order->items()->create([
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->product->price,
                ]);
            }

            CartItem::where('user_id', $userId)->delete();

            return response()->json([
                'message' => 'Checkout successful! Your order has been placed.',
                'order_id' => $order->id,
            ], 201);
        });
    }
}