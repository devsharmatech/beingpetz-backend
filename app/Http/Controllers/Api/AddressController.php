<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $addresses=UserAddress::where('user_id', $request->user_id)->get();
        return response()->json(['success' => true,'message'=>'Successfully fetched','data'=>$addresses]);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $address = UserAddress::create($data);

        return response()->json(['success' => true,'message'=>"Saved Successfully",'data'=>$address]);
    }

    public function update(Request $request, $id)
    {
        $address = UserAddress::findOrFail($id);
        $address->update($request->all());

        return response()->json(['success' => true,'message'=>"Updated Successfully",'data'=>$address]);
    }

    public function destroy($id)
    {
        $address=UserAddress::find($id);
        if($address){
            $address->delete();
        }

        return response()->json(['success' => true,'message'=>"Deleted Successfully"]);
    }
}