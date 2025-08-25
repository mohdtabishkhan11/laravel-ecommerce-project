<?php

namespace App\Http\Controllers\Admin;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('images')->latest()->paginate(10);
        return view('admin.products.index', compact('products'));
    }


    public function create()
    {
        return view('admin.products.create');
    }


    public function store(Request $request)
    {
        // 1. Validate the incoming data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'images' => 'required|array', // Ensure images is an array
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048' // Validate each file in the array
        ]);

        // 2. Use a database transaction
        DB::beginTransaction();

        try {
            // 3. Create the product
            $product = Product::create([
                'name' => $validated['name'],
                'price' => $validated['price'],
                'description' => $validated['description'],
            ]);

            // 4. Handle image uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $imageFile) {
                    // Store the image in 'storage/app/public/products'
                    $path = $imageFile->store('products', 'public');

                    // Create the image record in the database
                    $product->images()->create([
                        'image_path' => $path
                    ]);
                }
            }

            // If everything is successful, commit the transaction
            DB::commit();

            // 5. Redirect with a success message
            return redirect()->route('products.index')->with('success', 'Product created successfully!');

        } catch (\Exception $e) {
            // If anything goes wrong, roll back the transaction
            DB::rollBack();

            // Log the error for debugging
            // Log::error('Product creation failed: ' . $e->getMessage());

            // 6. Redirect back with an error message
            return redirect()->back()->with('error', 'Failed to create product. Please try again.')->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product) // Using route-model binding
    {
        // Eager load images to have them available in the view
        $product->load('images');
        return view('admin.products.edit', compact('product'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'images' => 'nullable|array', // New images are not required
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'delete_images' => 'nullable|array', // Array of image IDs to delete
            'delete_images.*' => 'integer|exists:product_images,id'
        ]);

        DB::beginTransaction();
        try {
            // 1. Update product details
            $product->update([
                'name' => $validated['name'],
                'price' => $validated['price'],
                'description' => $validated['description'],
            ]);

            // 2. Delete selected images
            if (!empty($validated['delete_images'])) {
                foreach ($validated['delete_images'] as $imageId) {
                    $image = \App\Models\ProductImage::find($imageId);
                    if ($image) {
                        Storage::disk('public')->delete($image->image_path);
                        $image->delete();
                    }
                }
            }

            // 3. Add new images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $imageFile) {
                    $path = $imageFile->store('products', 'public');
                    $product->images()->create(['image_path' => $path]);
                }
            }

            DB::commit();
            return redirect()->route('products.index')->with('success', 'Product updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update product. Please try again.')->withInput();
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        DB::beginTransaction();
        try {
            // Delete all associated image files from storage
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->image_path);
            }

            // Delete the product. The 'onDelete('cascade')' in the migration
            // will automatically delete the 'product_images' records.
            $product->delete();

            DB::commit();
            return redirect()->route('products.index')->with('success', 'Product deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('products.index')->with('error', 'Failed to delete product.');
        }
    }
}
