<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\GeneralRecord;

class GeneralRecordController extends Controller
{
    public function index(Request $request)
    {
        $records = GeneralRecord::where('pet_id', $request->pet_id)
                    ->orderBy('date','desc')->get();
        return response()->json(['status'=>true,'data'=>$records]);
    }

    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'pet_id'=>'required|exists:pets,id',
            'date'=>'required|date',
            'time'=>'required',
            'notes'=>'required|string',
        ]);
        if ($v->fails()) {
            return response()->json(['status'=>false,'message'=>$v->errors()->first(),'errors'=>$v->errors()],422);
        }
        $data = $v->validated();
        $data['bg_color'] = sprintf('#%06X', mt_rand(0xEEEEEE, 0xFFFFFF));
        $record = GeneralRecord::create($data);
        return response()->json(['status'=>true,'message'=>'Record added','data'=>$record],201);
    }
public function deleteData(Request $request)
{
    $v = Validator::make($request->all(), [
        'id' => 'required|exists:general_records,id',
    ]);

    if ($v->fails()) {
        return response()->json([
            'status' => false,
            'message' => $v->errors()->first(),
            'errors' => $v->errors(),
        ], 422);
    }

    $record = GeneralRecord::find($request->id);

    if (!$record) {
        return response()->json([
            'status' => false,
            'message' => 'General record not found',
        ], 404);
    }

    $record->delete();

    return response()->json([
        'status' => true,
        'message' => 'General record deleted successfully',
    ], 200);
}
    public function update(Request $request)
    {
        $record = GeneralRecord::findOrFail($request->id);
        $v = Validator::make($request->all(), [
            'date'=>'nullable|date',
            'time'=>'nullable',
            'notes'=>'nullable|string',
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