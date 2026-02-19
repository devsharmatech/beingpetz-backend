<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\File;
use App\Models\VaccineRecord;
use App\Models\Vaccine;

class VaccineRecordController extends Controller
{
    use PetRecordTrait;

    public function index(Request $request)
    {
        $vaccines = Vaccine::all()->keyBy('core_vaccine');
        
        $records = VaccineRecord::where('pet_id', $request->pet_id)
                    ->orderBy('date','desc')->get()->map(function ($item) use ($vaccines) {
            return [
                'id' => $item->id,
                'pet_id' => $item->pet_id,
                'vaccine_name' => $item->vaccine_name,
                'type' => $item->type,
                'next_vaccine' => $item->next_vaccine,
                'date' => $item->date,
                'reminder_date' => $item->reminder_date,
                'reminder_time' => $item->reminder_time,
                'image_path' => $item->image_path,
                'bg_color' => $item->bg_color,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'vaccine' => $vaccines->get($item->vaccine_name) ?? null,
            ];
        });
        return response()->json(['status'=>true,'data'=>$records]);
    }
    public function getVaccineByPet(Request $request)
    {
        if($request->filled('pet_type')){
        $vaccines = Vaccine::where('pet_type', $request->pet_type)->orderBy('id','asc')->get();
         return response()->json(['status'=>true,'data'=>$vaccines]);
        }else{
         return response()->json(['status'=>false,'message'=>'Pet Type should be from dog, cat!']);  
        }
    }
    public function getAll()
    {
        $vaccines = Vaccine::orderBy('id','asc')->get();
        return response()->json(['status'=>true,'data'=>$vaccines]);  
    }

    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'pet_id' => 'required|exists:pets,id',
            'date' => 'required|date',
            'vaccine_name' => 'required|string',
            'next_vaccine' => 'nullable|string',
            'type' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'reminder_date' => 'nullable|date',
            'reminder_time' => 'nullable',
        ]);
        if ($v->fails()) {
            return response()->json(['status'=>false,'message'=>$v->errors()->first(),'errors'=>$v->errors()],422);
        }

        $data = $v->validated();
        if ($request->hasFile('image')) {
            $data['image_path'] = $this->handleImageUpload($request->file('image'), 'vaccine_');
        }
        $data['bg_color'] = $this->generateBgColor();

        $record = VaccineRecord::create($data);
        return response()->json(['status'=>true,'message'=>'Record added','data'=>$record],201);
    }

    public function update(Request $request)
    {
        $record = VaccineRecord::findOrFail($request->id);
        $v = Validator::make($request->all(), [
            'date' => 'nullable|date',
            'vaccine_name' => 'nullable|string',
            'next_vaccine' => 'nullable|string',
            'type' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'reminder_date' => 'nullable|date',
            'reminder_time' => 'nullable',
        ]);
        if ($v->fails()) {
            return response()->json(['status'=>false,'message'=>$v->errors()->first(),'errors'=>$v->errors()],422);
        }

        $data = $v->validated();
        if ($request->hasFile('image')) {
            if ($record->image_path && File::exists(public_path($record->image_path))) {
                File::delete(public_path($record->image_path));
            }
            $data['image_path'] = $this->handleImageUpload($request->file('image'), 'vaccine_');
        }
        $data['bg_color'] = $record->bg_color;
        $record->update($data);
        return response()->json(['status'=>true,'message'=>'Record updated','data'=>$record]);
    }
    
    public function deleteData(Request $request)
{
    $v = Validator::make($request->all(), [
        'id' => 'required|exists:vaccine_records,id',
    ]);

    if ($v->fails()) {
        return response()->json([
            'status' => false,
            'message' => $v->errors()->first(),
            'errors' => $v->errors(),
        ], 422);
    }

    $record = VaccineRecord::find($request->id);

    if (!$record) {
        return response()->json([
            'status' => false,
            'message' => 'Vaccine record not found',
        ], 404);
    }

    // Delete image from public folder if it exists
    if (!empty($record->image_path)) {
        $imagePath = public_path($record->image_path);

        if (File::exists($imagePath)) {
            File::delete($imagePath);
        }
    }

    $record->delete();

    return response()->json([
        'status' => true,
        'message' => 'Vaccine record deleted successfully',
    ], 200);
}
}