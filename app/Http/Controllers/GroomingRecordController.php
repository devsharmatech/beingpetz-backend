<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\File;
use App\Models\GroomingRecord;
use App\Models\Grooming;

class GroomingRecordController extends Controller
{
    use PetRecordTrait;

    public function index(Request $request)
    {
        $records = GroomingRecord::where('pet_id', $request->pet_id)
                    ->orderBy('date','desc')->get()->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'pet_id' => $item->pet_id,
                        'type' => $item->grooming_type, // renamed here
                        'next_grooming' => $item->next_grooming, // renamed here
                        'date' => $item->date,
                        'reminder_date' => $item->reminder_date,
                        'reminder_time' => $item->reminder_time,
                        'image_path' => $item->image_path,
                        'bg_color' => $item->bg_color,
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at,
                    ];
                });
        return response()->json(['status'=>true,'data'=>$records]);
    }
    
    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'pet_id'=>'required|exists:pets,id',
            'date'=>'required|date',
            'grooming_type'=>'required|string',
            'next_grooming'=>'nullable|string',
            'image'=>'nullable|image|max:2048',
            'reminder_date'=>'nullable|date',
            'reminder_time'=>'nullable',
        ]);
        if ($v->fails()) {
            return response()->json(['status'=>false,'message'=>$v->errors()->first(),'errors'=>$v->errors()],422);
        }
        $data = $v->validated();
        if ($request->hasFile('image')) {
            $data['image_path'] = $this->handleImageUpload($request->file('image'), 'groom_');
        }
        $data['bg_color'] = $this->generateBgColor();

        $record = GroomingRecord::create($data);
        return response()->json(['status'=>true,'message'=>'Record added','data'=>$record],201);
    }
    public function getType(Request $request)
    {
        $v = Validator::make($request->all(), [
            'pet_type'=>'required'
        ]);
        if ($v->fails()) {
            return response()->json(['status'=>false,'message'=>$v->errors()->first(),'errors'=>$v->errors()],422);
        }
        $records = Grooming::latest()->where('type',$request->pet_type)->get();
        return response()->json(['status'=>true,'data'=>$records]);
    }


    public function update(Request $request)
    {
        $record = GroomingRecord::findOrFail($request->id);
        $v = Validator::make($request->all(), [
            'date'=>'nullable|date',
            'grooming_type'=>'nullable|string',
            'next_grooming'=>'nullable|string',
            'image'=>'nullable|image|max:2048',
            'reminder_date'=>'nullable|date',
            'reminder_time'=>'nullable',
        ]);
        if ($v->fails()) {
            return response()->json(['status'=>false,'message'=>$v->errors()->first(),'errors'=>$v->errors()],422);
        }
        $data = $v->validated();
        if ($request->hasFile('image')) {
            if ($record->image_path && File::exists(public_path($record->image_path))) {
                File::delete(public_path($record->image_path));
            }
            $data['image_path'] = $this->handleImageUpload($request->file('image'), 'groom_');
        }
        $data['bg_color'] = $record->bg_color;
        $record->update($data);
        return response()->json(['status'=>true,'message'=>'Record updated','data'=>$record]);
    }
    
    public function deleteData(Request $request)
{
    $v = Validator::make($request->all(), [
        'id' => 'required|exists:grooming_records,id',
    ]);

    if ($v->fails()) {
        return response()->json([
            'status' => false,
            'message' => $v->errors()->first(),
            'errors' => $v->errors(),
        ], 422);
    }

    $record = GroomingRecord::find($request->id);

    if (!$record) {
        return response()->json([
            'status' => false,
            'message' => 'Record not found',
        ], 404);
    }

    // Delete image if it exists
    if (!empty($record->image_path)) {
        $imagePath = public_path($record->image_path);

        if (File::exists($imagePath)) {
            File::delete($imagePath);
        }
    }

    $record->delete();

    return response()->json([
        'status' => true,
        'message' => 'Grooming record deleted successfully',
    ], 200);
}
}