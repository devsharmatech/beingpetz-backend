<?php

namespace App\Http\Controllers\V2Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\MarketplaceBanner;

class MarketplaceBannerController extends Controller
{
    // LIST
    public function index(Request $request)
    {
        $query = MarketplaceBanner::query();

        if ($request->search) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        $data = $query->orderBy('position', 'asc')->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Banner list',
            'data' => $data
        ]);
    }

    // STORE
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'image' => 'required|image',
            'link' => 'nullable|url'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        // IMAGE UPLOAD
        $imagePath = null;

        if ($request->hasFile('image')) {
            $folder = public_path('uploads/banners');

            if (!file_exists($folder)) {
                mkdir($folder, 0777, true);
            }

            $name = time().'_'.$request->file('image')->getClientOriginalName();
            $request->file('image')->move($folder, $name);

            $imagePath = 'uploads/banners/'.$name;
        }

        $banner = MarketplaceBanner::create([
            'title' => $request->title,
            'image' => $imagePath,
            'link' => $request->link,
            'position' => $request->position ?? 0,
            'is_active' => $request->is_active ?? 1
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Banner created',
            'data' => $banner
        ]);
    }

    // SHOW
    public function show($id)
    {
        $data = MarketplaceBanner::findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Banner detail',
            'data' => $data
        ]);
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $banner = MarketplaceBanner::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'image' => 'nullable|image',
            'link' => 'nullable|url'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        $data = [
            'title' => $request->title,
            'link' => $request->link,
            'position' => $request->position ?? 0,
            'is_active' => $request->is_active ?? 1
        ];

        // IMAGE UPDATE
        if ($request->hasFile('image')) {

            $folder = public_path('uploads/banners');

            if (!file_exists($folder)) {
                mkdir($folder, 0777, true);
            }

            $name = time().'_'.$request->file('image')->getClientOriginalName();
            $request->file('image')->move($folder, $name);

            $data['image'] = 'uploads/banners/'.$name;
        }

        $banner->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Banner updated',
            'data' => $banner
        ]);
    }

    // DELETE
    public function destroy($id)
    {
        MarketplaceBanner::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Banner deleted'
        ]);
    }
}