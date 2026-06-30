<?php

namespace App\Http\Controllers\V2Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Company;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::query();

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if (!is_null($request->is_active)) {
            $query->where('is_active', $request->is_active);
        }

        $data = $query->latest()->paginate(10);

        return response()->json(['success'=>true,'message'=>'Company list','data'=>$data]);
    }

    public function show($id)
    {
        return response()->json([
            'success'=>true,
            'message'=>'Company detail',
            'data'=>Company::findOrFail($id)
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'=>'required',
            'logo'=>'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'banner'=>'nullable|file|mimes:jpg,jpeg,png|max:4096'
        ]);

        if ($validator->fails()) {
            return response()->json(['success'=>false,'message'=>$validator->errors()->first()]);
        }

        $data = $request->all();
        $data['slug'] = \Str::slug($request->name);

        $path = public_path('uploads/companies');
        if (!file_exists($path)) mkdir($path,0755,true);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $name = time().'_logo.'.$file->getClientOriginalExtension();
            $file->move($path,$name);
            $data['logo'] = 'uploads/companies/'.$name;
        }

        if ($request->hasFile('banner')) {
            $file = $request->file('banner');
            $name = time().'_banner.'.$file->getClientOriginalExtension();
            $file->move($path,$name);
            $data['banner'] = 'uploads/companies/'.$name;
        }

        $company = Company::create($data);

        return response()->json(['success'=>true,'message'=>'Company created','data'=>$company]);
    }

    public function update(Request $request,$id)
    {
        $company = Company::findOrFail($id);

        $data = $request->all();
        $data['slug'] = \Str::slug($request->name);

        $path = public_path('uploads/companies');
        if (!file_exists($path)) mkdir($path,0755,true);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $name = time().'_logo.'.$file->getClientOriginalExtension();
            $file->move($path,$name);
            $data['logo'] = 'uploads/companies/'.$name;
        }

        if ($request->hasFile('banner')) {
            $file = $request->file('banner');
            $name = time().'_banner.'.$file->getClientOriginalExtension();
            $file->move($path,$name);
            $data['banner'] = 'uploads/companies/'.$name;
        }

        $company->update($data);

        return response()->json(['success'=>true,'message'=>'Company updated','data'=>$company]);
    }

    public function destroy($id)
    {
        Company::findOrFail($id)->delete();
        return response()->json(['success'=>true,'message'=>'Company deleted']);
    }
}