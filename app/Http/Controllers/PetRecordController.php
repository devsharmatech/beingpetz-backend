<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\VaccineRecord;
use App\Models\DewormingRecord;
use App\Models\GroomingRecord;
use App\Models\MealRecord;
use App\Models\WeightRecord;
use App\Models\GeneralRecord;

class PetRecordController extends Controller
{
   
    public function getAllRecords(Request $request)
    {
        
        $v = Validator::make($request->all(), [
            'pet_id' => 'required|exists:pets,id',
        ]);

        if ($v->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $v->errors()->first(),
                'errors'  => $v->errors(),
            ], 422);
        }

        $petId = $request->pet_id;

       
        $vaccines    = VaccineRecord::where('pet_id', $petId)->orderBy('date','desc')->get();
        $dewormings  = DewormingRecord::where('pet_id', $petId)->orderBy('date','desc')->get();
        $groomings   = GroomingRecord::where('pet_id', $petId)->orderBy('date','desc')->get();
        $meals       = MealRecord::where('pet_id', $petId)->orderBy('reminder_date','desc')->get();
        $weights     = WeightRecord::where('pet_id', $petId)->orderBy('date','desc')->get();
        $generals    = GeneralRecord::where('pet_id', $petId)->orderBy('date','desc')->get();

       
        return response()->json([
            'status'   => true,
            'message'  => 'All records fetched successfully',
            'data'     => [
                'vaccines'   => $vaccines,
                'dewormings' => $dewormings,
                'groomings'  => $groomings,
                'meals'      => $meals,
                'weights'    => $weights,
                'general'    => $generals,
            ],
        ], 200);
    }
}
