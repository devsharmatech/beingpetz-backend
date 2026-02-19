<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\FavoritePetBehaviourist;
use App\Models\FavoritePetGroomer;
use App\Models\FavoritePetResortOwner;
use App\Models\FavoritePetShelter;
use App\Models\FavoritePetShop;
use App\Models\FavoritePetSitter;
use App\Models\FavoritePetTrainer;


class FavoriteController extends Controller
{
  public function togglePetBehaviouristFavorite(Request $request)
  {
    $request->validate([
        'user_id' => 'required|integer',
        'service_id' => 'required|integer',
    ]);

    $favorite =FavoritePetBehaviourist::where('user_id', $request->user_id)
        ->where('pet_behaviourist_id', $request->service_id)
        ->first();

    if ($favorite) {
        $favorite->delete();
        return response()->json(['status'=>true,'code' => 'removed']);
    } else {
         FavoritePetBehaviourist::create([
            'user_id' => $request->user_id,
            'pet_behaviourist_id' => $request->service_id,
        ]);
        return response()->json(['status'=>true,'code' => 'added']);
    }
}
  
  public function togglePetGroomerFavorite(Request $request)
  {
    $request->validate([
        'user_id' => 'required|integer',
        'service_id' => 'required|integer',
    ]);

    $favorite = FavoritePetGroomer::where('user_id', $request->user_id)
        ->where('pet_groomer_id', $request->service_id)
        ->first();

    if ($favorite) {
        $favorite->delete();
        return response()->json(['status'=>true,'code' => 'removed']);
    } else {
          FavoritePetGroomer::create([
            'user_id' => $request->user_id,
            'pet_groomer_id' => $request->service_id,
        ]);
        return response()->json(['status'=>true,'code' => 'added']);
    }
}
  
  public function togglePetResortOwnerFavorite(Request $request)
  {
    $request->validate([
        'user_id' => 'required|integer',
        'service_id' => 'required|integer',
    ]);

    $favorite =FavoritePetResortOwner::where('user_id', $request->user_id)
        ->where('pet_resort_owner_id', $request->service_id)
        ->first();

    if ($favorite) {
        $favorite->delete();
        return response()->json(['status'=>true,'code' => 'removed']);
    } else {
         FavoritePetResortOwner::create([
            'user_id' => $request->user_id,
            'pet_resort_owner_id' => $request->service_id,
        ]);
        return response()->json(['status'=>true,'code' => 'added']);
    }
}
 
  public function togglePetShelterFavorite(Request $request)
  {
    $request->validate([
        'user_id' => 'required|integer',
        'service_id' => 'required|integer',
    ]);

    $favorite =FavoritePetShelter::where('user_id', $request->user_id)
        ->where('pet_shelter_id', $request->service_id)
        ->first();

    if ($favorite) {
        $favorite->delete();
        return response()->json(['status'=>true,'code' => 'removed']);
    } else {
        FavoritePetShelter::create([
            'user_id' => $request->user_id,
            'pet_shelter_id' => $request->service_id,
        ]);
        return response()->json(['status'=>true,'code' => 'added']);
    }
}


   public function togglePetShopFavorite(Request $request)
{
    $request->validate([
        'user_id' => 'required|integer',
        'service_id' => 'required|integer',
    ]);

    $favorite = FavoritePetShop::where('user_id', $request->user_id)
        ->where('pet_shop_id', $request->service_id)
        ->first();

    if ($favorite) {
        $favorite->delete();
        return response()->json(['status'=>true,'code' => 'removed']);
    } else {
        FavoritePetShop::create([
            'user_id' => $request->user_id,
            'pet_shop_id' => $request->service_id,
        ]);
        return response()->json(['status'=>true,'code' => 'added']);
    }
}


  public function togglePetSitterFavorite(Request $request)
{
    $request->validate([
        'user_id' => 'required|integer',
        'service_id' => 'required|integer',
    ]);

    $favorite =FavoritePetSitter::where('user_id', $request->user_id)
        ->where('pet_sitter_id', $request->service_id)
        ->first();

    if ($favorite) {
        $favorite->delete();
        return response()->json(['status'=>true,'code' => 'removed']);
    } else {
        FavoritePetSitter::create([
            'user_id' => $request->user_id,
            'pet_sitter_id' => $request->service_id,
        ]);
        return response()->json(['status'=>true,'code' => 'added']);
    }
}

public function togglePetTrainerFavorite(Request $request)
{
    $request->validate([
        'user_id' => 'required|integer',
        'service_id' => 'required|integer',
    ]);

    $favorite =FavoritePetTrainer::where('user_id', $request->user_id)
        ->where('pet_trainer_id', $request->service_id)
        ->first();

    if ($favorite) {
        $favorite->delete();
        return response()->json(['status'=>true,'code' => 'removed']);
    } else {
        FavoritePetTrainer::create([
            'user_id' => $request->user_id,
            'pet_trainer_id' => $request->service_id,
        ]);
        return response()->json(['status'=>true,'code' => 'added']);
    }
}

public function getFavoritesByUserId(Request $request)
{
    $user_id=$request->user_id;
    $favorites = [
        'pet_groomers' => FavoritePetGroomer::with('petGroomer')->where('user_id', $user_id)->get(),
        'pet_trainers' => FavoritePetTrainer::with('petTrainer')->where('user_id', $user_id)->get(),
        'pet_sitters' => FavoritePetSitter::with('petSitter')->where('user_id', $user_id)->get(),
        'pet_shops' => FavoritePetShop::with('petShop')->where('user_id', $user_id)->get(),
        'pet_shelters' => FavoritePetShelter::with('petShelter')->where('user_id', $user_id)->get(),
        'pet_resort_owners' => FavoritePetResortOwner::with('petResortOwner')->where('user_id', $user_id)->get(),
        'pet_behaviourists' => FavoritePetBehaviourist::with('petBehaviourist')->where('user_id', $user_id)->get(),
    ];

    return response()->json([
        'status' => true,
        'message' => 'Favorite services fetched successfully',
        'data' => $favorites,
    ]);
}


}