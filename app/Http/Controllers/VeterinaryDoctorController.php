<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\File;
use App\Models\VaccineRecord;
use App\Models\Vaccine;
use App\Models\VeterinaryDoctor;

class VeterinaryDoctorController extends Controller
{
   public function index()
    {
        $doctors = VeterinaryDoctor::whereIn('status', ['active', 'verified'])->get();

        return response()->json([
            'success' => true,
            'data' => $doctors
        ]);
    }
}