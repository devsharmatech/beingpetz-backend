<?php

namespace App\Http\Controllers\V2Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Company;
use App\Models\MarketplaceCategory;
use App\Models\Attribute;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function formData()
    {
        try {
            $companies = Company::select('id', 'name')
                ->orderBy('name')
                ->get();
                
            $categories = MarketplaceCategory::select('id', 'name')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'companies' => $companies,
                    'categories' => $categories
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Form data error: ' . $e->getMessage());
            \Log::error('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load form data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a listing of products with filters
     */
    public function index(Request $request)
    {
        try {
            $query = Product::with(['company', 'category', 'variants.attributeValues', 'images']);

            // Search filter
            if ($request->filled('search')) {
                $query->where(function($q) use ($request) {
                    $q->where('name', 'like', "%{$request->search}%")
                      ->orWhere('description', 'like', "%{$request->search}%");
                });
            }

            // Company filter
            if ($request->filled('company_id')) {
                $query->where('company_id', $request->company_id);
            }

            // Category filter
            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            // Status filter
            if ($request->filled('is_active')) {
                $query->where('is_active', $request->is_active);
            }

            $perPage = $request->input('per_page', 10);
            $products = $query->latest()->paginate($perPage);

            // Add image URLs to products
            $products->getCollection()->transform(function ($product) {
                $product->image_url = $product->image ? asset($product->image) : null;
                
                if ($product->variants) {
                    $product->variants->transform(function ($variant) {
                        $variant->image_url = $variant->image ? asset($variant->image) : null;
                        return $variant;
                    });
                }
                
                return $product;
            });

            return response()->json([
                'success' => true,
                'message' => 'Products retrieved successfully',
                'data' => $products
            ]);

        } catch (\Exception $e) {
            \Log::error('Product index error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve products',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    

    /**
     * Display the specified product
     */
    public function show($id)
    {
        try {
            $product = Product::with([
                'company',
                'category',
                'variants.attributeValues',
                'images'
            ])->findOrFail($id);

            // Add image URLs
            $product->image_url = $product->image ? asset($product->image) : null;
            
            if ($product->variants) {
                $product->variants->transform(function ($variant) {
                    $variant->image_url = $variant->image ? asset($variant->image) : null;
                    return $variant;
                });
            }

            return response()->json([
                'success' => true,
                'message' => 'Product retrieved successfully',
                'data' => $product
            ]);

        } catch (\Exception $e) {
            \Log::error('Product show error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'company_id' => 'required|exists:companies,id',
                'category_id' => 'required|exists:marketplace_categories,id',
                'name' => 'required|string|max:255|unique:products,name',
                'description' => 'nullable|string',
                'base_price' => 'nullable|numeric|min:0',
                'sale_price' => 'nullable|numeric|min:0',
                'stock' => 'nullable|integer|min:0',
                'primary_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
                'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
                'is_featured' => 'boolean',
                'is_trending' => 'boolean',
                'is_best_seller' => 'boolean',
                'is_new' => 'boolean',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->except(['primary_image', 'gallery_images']);
            $data['slug'] = $this->generateUniqueSlug($request->name);
            $data['is_featured'] = $request->boolean('is_featured', false);
            $data['is_trending'] = $request->boolean('is_trending', false);
            $data['is_best_seller'] = $request->boolean('is_best_seller', false);
            $data['is_new'] = $request->boolean('is_new', false);
            $data['is_active'] = $request->boolean('is_active', true);

            $product = Product::create($data);

            // Handle primary image
            if ($request->hasFile('primary_image')) {
                $imagePath = $this->uploadImage($request->file('primary_image'), 'products');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $imagePath,
                    'is_primary' => true
                ]);
            }

            // Handle gallery images
            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $image) {
                    $imagePath = $this->uploadImage($image, 'products/gallery');
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image' => $imagePath,
                        'is_primary' => false
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product->load(['company', 'category', 'images'])
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Product store error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product'
            ], 500);
        }
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'company_id' => 'required|exists:companies,id',
                'category_id' => 'required|exists:marketplace_categories,id',
                'name' => ['required', 'string', 'max:255', Rule::unique('products')->ignore($id)],
                'description' => 'nullable|string',
                'base_price' => 'nullable|numeric|min:0',
                'sale_price' => 'nullable|numeric|min:0',
                'stock' => 'nullable|integer|min:0',
                'primary_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
                'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
                'is_featured' => 'boolean',
                'is_trending' => 'boolean',
                'is_best_seller' => 'boolean',
                'is_new' => 'boolean',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->except(['primary_image', 'gallery_images', 'delete_gallery_images']);
            
            // Update slug only if name changed
            if ($request->name !== $product->name) {
                $data['slug'] = $this->generateUniqueSlug($request->name, $id);
            }
            
            $data['is_featured'] = $request->has('is_featured') ? $request->boolean('is_featured') : false;
            $data['is_trending'] = $request->has('is_trending') ? $request->boolean('is_trending') : false;
            $data['is_best_seller'] = $request->has('is_best_seller') ? $request->boolean('is_best_seller') : false;
            $data['is_new'] = $request->has('is_new') ? $request->boolean('is_new') : false;
            $data['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

            $product->update($data);

            // Handle primary image
            if ($request->hasFile('primary_image')) {
                // Delete old primary image
                $oldPrimary = $product->images()->where('is_primary', true)->first();
                if ($oldPrimary) {
                    if (file_exists(public_path($oldPrimary->image))) {
                        unlink(public_path($oldPrimary->image));
                    }
                    $oldPrimary->delete();
                }
                
                $imagePath = $this->uploadImage($request->file('primary_image'), 'products');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $imagePath,
                    'is_primary' => true
                ]);
            }

            // Handle gallery images
            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $image) {
                    $imagePath = $this->uploadImage($image, 'products/gallery');
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image' => $imagePath,
                        'is_primary' => false
                    ]);
                }
            }

            // Handle gallery image deletions
            if ($request->has('delete_gallery_images')) {
                $deleteIds = explode(',', $request->delete_gallery_images);
                $images = ProductImage::whereIn('id', $deleteIds)
                    ->where('product_id', $product->id)
                    ->where('is_primary', false)
                    ->get();
                    
                foreach ($images as $image) {
                    if (file_exists(public_path($image->image))) {
                        unlink(public_path($image->image));
                    }
                    $image->delete();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $product->fresh(['company', 'category', 'variants', 'images'])
            ]);

        } catch (\Exception $e) {
            \Log::error('Product update error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product'
            ], 500);
        }
    }

    /**
     * Remove the specified product
     */
    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            
            // Delete all images
            foreach ($product->images as $image) {
                if (file_exists(public_path($image->image))) {
                    unlink(public_path($image->image));
                }
                $image->delete();
            }
            
            // Delete variant images
            foreach ($product->variants as $variant) {
                if ($variant->image && file_exists(public_path($variant->image))) {
                    unlink(public_path($variant->image));
                }
            }
            
            // Delete variants
            $product->variants()->delete();
            
            // Delete product
            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Product delete error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product'
            ], 500);
        }
    }

    /**
     * Set image as primary
     */
    public function setPrimaryImage(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);
            $imageId = $request->image_id;
            
            // Reset all images to not primary
            $product->images()->update(['is_primary' => false]);
            
            // Set selected image as primary
            $product->images()->where('id', $imageId)->update(['is_primary' => true]);
            
            return response()->json([
                'success' => true,
                'message' => 'Primary image updated successfully'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Set primary image error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update primary image'
            ], 500);
        }
    }

    /**
     * Delete gallery image
     */
    public function deleteGalleryImage($id)
    {
        try {
            $image = ProductImage::findOrFail($id);
            
            // Don't allow deleting primary image
            if ($image->is_primary) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete primary image. Set another image as primary first.'
                ], 400);
            }
            
            if (file_exists(public_path($image->image))) {
                unlink(public_path($image->image));
            }
            
            $image->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Delete gallery image error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete image'
            ], 500);
        }
    }

    /**
     * Upload image using move()
     */
    private function uploadImage($file, $folder)
    {
        $path = public_path('uploads/' . $folder);
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $file->move($path, $filename);
        
        return 'uploads/' . $folder . '/' . $filename;
    }

    /**
     * Generate unique slug
     */
    private function generateUniqueSlug($name, $ignoreId = null)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        $query = Product::where('slug', $slug);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        while ($query->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
            
            $query = Product::where('slug', $slug);
            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }
        }

        return $slug;
    }
}