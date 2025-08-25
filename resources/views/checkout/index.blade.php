<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-weight-bold mb-0">
            {{ __('Checkout') }}
        </h2>
    </x-slot>

    <div class="container py-4">
        <div class="row">
            <!-- Shipping Details Form -->
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header">
                        <h4>Shipping Details</h4>
                    </div>
                    <div class="card-body">
                        {{-- We use a form tag to group inputs, but handle submission with JS --}}
                        <form id="shipping-form">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        value="{{ old('name', Auth::user()->name) }}" required>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        value="{{ old('email', Auth::user()->email) }}" required>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label for="address" class="form-label">Shipping Address</label>
                                    <input type="text" class="form-control" id="address" name="address"
                                        value="{{ old('address') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" class="form-control" id="city" name="city"
                                        value="{{ old('city') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="postal_code" class="form-label">Postal Code</label>
                                    <input type="text" class="form-control" id="postal_code" name="postal_code"
                                        value="{{ old('postal_code') }}" required>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone"
                                        value="{{ old('phone') }}" required>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header">
                        <h4>Order Summary</h4>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            @foreach ($cartItems as $item)
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <h6 class="my-0">{{ $item->product->name }}</h6>
                                        <small class="text-muted">Quantity: {{ $item->quantity }}</small>
                                    </div>
                                    <span
                                        class="text-muted">₹{{ number_format($item->product->price * $item->quantity, 2) }}</span>
                                </li>
                            @endforeach
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <strong>Total</strong>
                                <strong>₹{{ number_format($total, 2) }}</strong>
                            </li>
                        </ul>
                        <button id="pay-button" class="btn btn-primary w-100 btn-lg mt-3">
                            Place Order & Pay
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        document.getElementById('pay-button').onclick = async function (e) {
            e.preventDefault();

            // 1. Get form data
            const shippingForm = document.getElementById('shipping-form');
            const formData = new FormData(shippingForm);
            const shippingData = Object.fromEntries(formData.entries());

            // Simple client-side validation
            for (const key in shippingData) {
                if (!shippingData[key]) {
                    alert(`Please fill out the ${key.replace('_', ' ')} field.`);
                    return;
                }
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // 2. Create Razorpay Order, sending shipping data in the body
            const orderResponse = await fetch("{{ route('api.checkout.order') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(shippingData)
            });

            if (!orderResponse.ok) {
                const errorData = await orderResponse.json();
                if (errorData.errors) {
                    let errorMsg = 'Please correct the following errors:\n';
                    for (const field in errorData.errors) {
                        errorMsg += `- ${errorData.errors[field][0]}\n`;
                    }
                    alert(errorMsg);
                } else {
                    alert(errorData.message || 'Failed to create order.');
                }
                return;
            }

            const orderData = await orderResponse.json();

            // 3. Configure and Open Razorpay Checkout
            const options = {
                "key": "{{ $razorpayKey }}",
                "amount": orderData.amount,
                "currency": orderData.currency,
                "name": "{{ config('app.name', 'Laravel') }}",
                "description": "E-commerce Transaction",
                "order_id": orderData.razorpay_order_id,
                "handler": async function (response) {
                    // 4. Verify payment
                    const verifyResponse = await fetch("{{ route('api.checkout.verify') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            razorpay_order_id: response.razorpay_order_id,
                            razorpay_payment_id: response.razorpay_payment_id,
                            razorpay_signature: response.razorpay_signature
                        })
                    });

                    if (verifyResponse.ok) {
                        // 5. Redirect to success page
                        window.location.href = "{{ route('checkout.success') }}";
                    } else {
                        alert('Payment verification failed. Please contact support.');
                    }
                },
                "prefill": {
                    "name": shippingData.name,
                    "email": shippingData.email,
                    "contact": shippingData.phone
                },
                "theme": {
                    "color": "#0d6efd"
                }
            };

            const rzp = new Razorpay(options);
            rzp.open();
        }
    </script>
</x-app-layout>