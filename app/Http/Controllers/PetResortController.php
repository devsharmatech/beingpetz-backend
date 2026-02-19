<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\File;
use App\Models\PetResort;

class PetResortController extends Controller
{
   public function index()
    {
        $resorts = PetResort::whereIn('status', ['active', 'approved'])->get();

        return response()->json([
            'success' => true,
            'data' => $resorts
        ]);
    }
}