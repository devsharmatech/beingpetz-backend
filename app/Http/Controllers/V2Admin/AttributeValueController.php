<?php

namespace App\Http\Controllers\V2Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\AttributeValue;

class AttributeValueController extends Controller
{
    // STORE
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'attribute_id' => 'required|exists:attributes,id',
            'value' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        $value = AttributeValue::create([
            'attribute_id' => $request->attribute_id,
            'value' => $request->value,
            'is_active' => 1
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Value added',
            'data' => $value
        ]);
    }

    // DELETE VALUE
    public function destroy($id)
    {
        AttributeValue::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Value deleted'
        ]);
    }
}