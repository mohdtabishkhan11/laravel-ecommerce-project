<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-weight-bold mb-0">
            {{ __('Products') }}
        </h2>
    </x-slot>

    <div class="container py-4">
        {{-- Display Success Message --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            @forelse ($products as $product)
                <div class="col">
                    <div class="card h-100 shadow-sm">

                        @if($product->images->count() > 1)
                            <div id="carousel-{{ $product->id }}" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-indicators">
                                    @foreach($product->images as $key => $image)
                                        <button type="button" data-bs-target="#carousel-{{ $product->id }}"
                                            data-bs-slide-to="{{ $key }}" class="{{ $key == 0 ? 'active' : '' }}"
                                            aria-current="{{ $key == 0 ? 'true' : 'false' }}"></button>
                                    @endforeach
                                </div>
                                <div class="carousel-inner">
                                    @foreach($product->images as $key => $image)
                                        <div class="carousel-item {{ $key == 0 ? 'active' : '' }}">
                                            {{-- STYLE UPDATED HERE --}}
                                            <img src="{{ asset('storage/' . $image->image_path) }}" class="d-block w-100"
                                                alt="{{ $product->name }}" style="height: 220px; object-fit: contain;">
                                        </div>
                                    @endforeach
                                </div>
                                <button class="carousel-control-prev" type="button"
                                    data-bs-target="#carousel-{{ $product->id }}" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button"
                                    data-bs-target="#carousel-{{ $product->id }}" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            </div>
                        @else
                            @php
                                $imageUrl = $product->images->first()
                                    ? asset('storage/' . $product->images->first()->image_path)
                                    : 'https://placehold.co/600x400/e2e8f0/e2e8f0?text=No+Image';
                            @endphp
                            {{-- STYLE UPDATED HERE --}}
                            <img src="{{ $imageUrl }}" class="card-img-top" alt="{{ $product->name }}"
                                style="height: 220px; object-fit: contain;">
                        @endif

                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">{{ $product->name }}</h5>
                            <p class="card-text text-muted">₹{{ number_format($product->price, 2) }}</p>

                            <form action="{{ route('web.cart.add') }}" method="POST" class="mt-auto">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn btn-primary w-100">Add to Cart</button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        No products found. Please check back later.
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>