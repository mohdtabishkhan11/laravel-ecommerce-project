<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-weight-bold mb-0">
            {{ __('Order Details') }} #{{ $order->id }}
        </h2>
    </x-slot>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Items in Order</div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                <tr>
                                    <td>{{ $item->product->name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>${{ number_format($item->price, 2) }}</td>
                                    <td>${{ number_format($item->quantity * $item->price, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Order Summary</div>
                <div class="card-body">
                    <p><strong>Order Status:</strong> <span class="badge bg-success">{{ $order->status }}</span></p>
                    <p><strong>Order Date:</strong> {{ $order->created_at->format('M d, Y') }}</p>
                    <p><strong>Payment Gateway:</strong> <span
                            class="text-capitalize">{{ $order->payment_gateway }}</span></p>
                    <h4 class="mt-3">Total: ${{ number_format($order->total_amount, 2) }}</h4>

                    <hr>

                    {{-- MODIFICATION START: Added Shipping Details --}}
                    <h5 class="mt-4">Shipping Details</h5>
                    <p>
                        <strong>Customer:</strong> {{ $order->customer_name }}<br>
                        <strong>Email:</strong> {{ $order->customer_email }}<br>
                        <strong>Phone:</strong> {{ $order->phone }}<br>
                        <strong>Address:</strong> {{ $order->shipping_address }}<br>
                        {{ $order->city }}, {{ $order->postal_code }}
                    </p>
                    {{-- MODIFICATION END --}}

                </div>
            </div>
        </div>
    </div>
</x-app-layout>