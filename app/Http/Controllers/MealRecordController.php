<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\MealRecord;

class MealRecordController extends Controller
{
    public function index(Request $request)
    {
        $records = MealRecord::where('pet_id', $request->pet_id)
                    ->orderBy('created_at','desc')->get();
        return response()->json(['status'=>true,'data'=>$records]);
    }

    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'pet_id'=>'required|exists:pets,id',
            'meal_time'=>'required|in:morning,afternoon,evening,night,Morning,Afternoon,Evening,Night',
            'reminder_date'=>'nullable|date',
            'reminder_time'=>'nullable',
        ]);
        if ($v->fails()) {
            return response()->json(['status'=>false,'message'=>$v->errors()->first(),'errors'=>$v->errors()],422);
        }
        $data = $v->validated();
        $data['bg_color'] = sprintf('#%06X', mt_rand(0xEEEEEE, 0xFFFFFF));
        $record = MealRecord::create($data);
        return response()->json(['status'=>true,'message'=>'Record added','data'=>$record],201);
    }
    
    public function deleteData(Request $request)
    {
    $v = Validator::make($request->all(), [
        'id' => 'required|exists:meal_records,id',
    ]);

    if ($v->fails()) {
        return response()->json([
            'status' => false,
            'message' => $v->errors()->first(),
            'errors' => $v->errors(),
        ], 422);
    }

    $record = MealRecord::find($request->id);

    if (!$record) {
        return response()->json([
            'status' => false,
            'message' => 'Meal record not found',
        ], 404);
    }

    $record->delete();

    return response()->json([
        'status' => true,
        'message' => 'Meal record deleted successfully',
    ], 200);
}

    public function update(Request $request)
    {
        $record = MealRecord::findOrFail($request->id);
        $v = Validator::make($request->all(), [
            'meal_time'=>'nullable|in:morning,afternoon,evening,night,Morning,Afternoon,Evening,Night',
            'reminder_date'=>'nullable|date',
            'reminder_time'=>'nullable',
        ]);
        if ($v->fails()) {
            return response()->json(['status'=>false,'message'=>$v->errors()->first(),'errors'=>$v->errors()],422);
        }
        $data = $v->validated();
        $data['bg_color'] = $record->bg_color;
        $record->update($data);
        return response()->json(['status'=>true,'message'=>'Record updated','data'=>$record]);
    }
}