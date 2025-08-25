<?php

namespace App\Http\View\Composers;

use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CartComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        if (Auth::check()) {
            $cartItems = CartItem::with('product')
                ->where('user_id', Auth::id())
                ->get();
            $cartTotal = $cartItems->sum(function ($item) {
                return $item->quantity * $item->product->price;
            });
        } else {
            $cartItems = collect();
            $cartTotal = 0;
        }

        $view->with('cartItems', $cartItems)->with('cartTotal', $cartTotal);
    }
}