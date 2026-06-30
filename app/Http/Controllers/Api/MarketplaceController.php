<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{
    MarketplaceBanner,
    MarketplaceCategory,
    Company,
    Product
};

class MarketplaceController extends Controller
{
    public function home()
    {
        return response()->json([
            'success' => true,'message'=>"Fetched Successfully",
            'banners' => MarketplaceBanner::where('is_active', 1)->get(),

            'categories' => MarketplaceCategory::whereNull('parent_id')->get(),

            'companies' => Company::where('is_active', 1)->get(),

            'recent_products' => Product::with('images')
                ->latest()
                ->take(10)
                ->get(),

            'top_selling_products' => Product::with('images')
                ->where('is_best_seller', 1)
                ->take(10)
                ->get()
        ]);
    }
}