<?php

namespace App\Http\Controllers\V2Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\MarketplaceCategory;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = MarketplaceCategory::query();

        // 🔍 Search
        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        // 🎯 Filter
        if (!is_null($request->is_active)) {
            $query->where('is_active', $request->is_active);
        }

        $data = $query->latest()->paginate(10);

        return response()->json(['success'=>true,'message'=>'Category list','data'=>$data]);
    }

    public function show($id)
    {
        $data = MarketplaceCategory::findOrFail($id);
        return response()->json(['success'=>true,'message'=>'Category detail','data'=>$data]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['success'=>false,'message'=>$validator->errors()->first()]);
        }

        $data = $request->all();
        $data['slug'] = \Str::slug($request->name);

        if ($request->hasFile('image')) {
            $path = public_path('uploads/categories');
            if (!file_exists($path)) mkdir($path, 0755, true);

            $file = $request->file('image');
            $name = time().'_cat.'.$file->getClientOriginalExtension();
            $file->move($path, $name);

            $data['image'] = 'uploads/categories/'.$name;
        }

        $category = MarketplaceCategory::create($data);

        return response()->json(['success'=>true,'message'=>'Category created','data'=>$category]);
    }

    public function update(Request $request, $id)
    {
        $category = MarketplaceCategory::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['success'=>false,'message'=>$validator->errors()->first()]);
        }

        $data = $request->all();
        $data['slug'] = \Str::slug($request->name);

        if ($request->hasFile('image')) {
            $path = public_path('uploads/categories');
            if (!file_exists($path)) mkdir($path, 0755, true);

            $file = $request->file('image');
            $name = time().'_cat.'.$file->getClientOriginalExtension();
            $file->move($path, $name);

            $data['image'] = 'uploads/categories/'.$name;
        }

        $category->update($data);

        return response()->json(['success'=>true,'message'=>'Category updated','data'=>$category]);
    }

    public function destroy($id)
    {
        MarketplaceCategory::findOrFail($id)->delete();

        return response()->json(['success'=>true,'message'=>'Category deleted']);
    }
}