<?php

namespace App\Http\Controllers\V2Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\ProductVariant;
use Illuminate\Validation\Rule;

class ProductVariantController extends Controller
{
    /**
     * Store a newly created variant
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|exists:products,id',
                'sku' => 'required|string|max:100|unique:product_variants,sku',
                'price' => 'required|numeric|min:0',
                'sale_price' => 'nullable|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'attribute_value_ids' => 'required|json',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->only(['product_id', 'sku', 'price', 'sale_price', 'stock']);
            
            $variant = ProductVariant::create($data);

            // Attach attribute values
            $attributeValueIds = json_decode($request->attribute_value_ids, true);
            if (!empty($attributeValueIds)) {
                $variant->attributeValues()->attach($attributeValueIds);
            }

            // Handle variant image
            if ($request->hasFile('image')) {
                $imagePath = $this->uploadVariantImage($request->file('image'), $variant->id);
                $variant->image = $imagePath;
                $variant->save();
            }

            $variant->load('attributeValues');
            $variant->image_url = $variant->image ? asset($variant->image) : null;

            return response()->json([
                'success' => true,
                'message' => 'Variant added successfully',
                'data' => $variant
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Variant store error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to add variant',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update the specified variant
     */
    public function update(Request $request, $id)
    {
        try {
            $variant = ProductVariant::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'sku' => ['required', 'string', 'max:100', Rule::unique('product_variants')->ignore($id)],
                'price' => 'required|numeric|min:0',
                'sale_price' => 'nullable|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'attribute_value_ids' => 'nullable|json',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->only(['sku', 'price', 'sale_price', 'stock']);
            
            // Handle variant image
            if ($request->hasFile('image')) {
                // Delete old image
                if ($variant->image && file_exists(public_path($variant->image))) {
                    unlink(public_path($variant->image));
                }
                
                $imagePath = $this->uploadVariantImage($request->file('image'), $variant->id);
                $data['image'] = $imagePath;
            }

            $variant->update($data);

            // Sync attribute values if provided
            if ($request->has('attribute_value_ids')) {
                $attributeValueIds = json_decode($request->attribute_value_ids, true);
                $variant->attributeValues()->sync($attributeValueIds);
            }

            $variant->load('attributeValues');
            $variant->image_url = $variant->image ? asset($variant->image) : null;

            return response()->json([
                'success' => true,
                'message' => 'Variant updated successfully',
                'data' => $variant
            ]);

        } catch (\Exception $e) {
            \Log::error('Variant update error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update variant'
            ], 500);
        }
    }

    /**
     * Remove the specified variant
     */
    public function destroy($id)
    {
        try {
            $variant = ProductVariant::findOrFail($id);
            
            // Delete variant image
            if ($variant->image && file_exists(public_path($variant->image))) {
                unlink(public_path($variant->image));
            }
            
            // Detach attribute values
            $variant->attributeValues()->detach();
            
            $variant->delete();

            return response()->json([
                'success' => true,
                'message' => 'Variant deleted successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Variant delete error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete variant'
            ], 500);
        }
    }

    /**
     * Upload variant image using move()
     */
    private function uploadVariantImage($file, $variantId)
    {
        $path = public_path('uploads/variants');
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        $filename = time() . '_variant_' . $variantId . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
        $file->move($path, $filename);
        
        return 'uploads/variants/' . $filename;
    }
}