<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-weight-bold mb-0">
            {{ __('Edit Product: ') . $product->name }}
        </h2>
    </x-slot>

    <div class="card">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT') {{-- Important for updates --}}

                {{-- Product Name, Price, Description fields (pre-filled) --}}
                <div class="mb-3">
                    <label for="name" class="form-label">Product Name</label>
                    <input type="text" class="form-control" id="name" name="name"
                        value="{{ old('name', $product->name) }}" required>
                </div>

                <div class="mb-3">
                    <label for="price" class="form-label">Price</label>
                    <input type="number" class="form-control" id="price" name="price"
                        value="{{ old('price', $product->price) }}" step="0.01" required>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description"
                        rows="4">{{ old('description', $product->description) }}</textarea>
                </div>

                {{-- Existing Images --}}
                <div class="mb-3">
                    <label class="form-label">Existing Images</label>
                    <div class="d-flex flex-wrap">
                        @forelse($product->images as $image)
                            <div class="me-2 mb-2 position-relative">
                                <img src="{{ asset('storage/' . $image->image_path) }}" width="100" class="rounded">
                                <div class="position-absolute top-0 start-100 translate-middle">
                                    <input class="form-check-input bg-danger border-danger" type="checkbox"
                                        name="delete_images[]" value="{{ $image->id }}" id="delete_image_{{ $image->id }}">
                                    <label class="visually-hidden" for="delete_image_{{ $image->id }}">Delete</label>
                                </div>
                            </div>
                        @empty
                            <p>No existing images.</p>
                        @endforelse
                    </div>
                    <div class="form-text text-danger">Select an image's checkbox to delete it on update.</div>
                </div>

                {{-- Add New Images --}}
                <div class="mb-3">
                    <label for="images" class="form-label">Add New Images</label>
                    <input type="file" class="form-control" id="images" name="images[]" multiple>
                    <div class="form-text">You can upload more images.</div>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('products.index') }}" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Product</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>