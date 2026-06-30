<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;

class ProductController extends Controller
{
    // 📂 Products by category
    public function byCategory($id)
    {
        
        $products = Product::with(['images', 'variants'])
            ->where('category_id', $id)
            ->paginate(10);

        return response()->json(['success' => true,'message'=>"Fetched Successfully",'data'=>$products]);
    }

    // 🔍 Product detail
    public function show($id)
    {
        $product = Product::with([
            'images',
            'variants.attributeValues.attribute',
            'company'
        ])->findOrFail($id);

        return response()->json(['success' => true,'message'=>"Fetched detail Successfully",'data'=>$product]);
    }
}