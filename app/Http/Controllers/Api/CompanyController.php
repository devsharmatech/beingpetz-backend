<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;

class CompanyController extends Controller
{
    public function companyProducts($id)
    {
        $company = Company::with(['products.images'])
            ->findOrFail($id);

        return response()->json(['success' => true,'message'=>"Fetched Successfully",'data'=>$company]);
    }
}