<?php

namespace App\Http\Controllers\V2Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Attribute;

class AttributeController extends Controller
{
    // LIST
    public function index()
    {
        $data = Attribute::with('values')->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Attribute list',
            'data' => $data
        ]);
    }

    // STORE
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        $attribute = Attribute::create([
            'name' => $request->name,
            'slug' => \Str::slug($request->name),
            'is_active' => $request->is_active ?? 1
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attribute created',
            'data' => $attribute
        ]);
    }

    // SHOW
    public function show($id)
    {
        $data = Attribute::with('values')->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Attribute detail',
            'data' => $data
        ]);
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $attribute = Attribute::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        $attribute->update([
            'name' => $request->name,
            'slug' => \Str::slug($request->name),
            'is_active' => $request->is_active ?? 1
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attribute updated',
            'data' => $attribute
        ]);
    }

    // DELETE
    public function destroy($id)
    {
        Attribute::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Attribute deleted'
        ]);
    }
}