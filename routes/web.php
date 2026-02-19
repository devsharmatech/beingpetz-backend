<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CommunityController;



Route::controller(PostController::class)->group(function(){
    Route::get('birthday-wish','createPetBirthdayPosts');
});
Route::controller(PetController::class)->group(function(){
    Route::get('/{unid}/show-parent-detail','showParentInfo');
});
Route::controller(CommunityController::class)->group(function(){
    Route::get('/clean-community','autoDeleteEmptyCommunities');
});
Route::controller(NotificationController::class)->group(function(){
    Route::get('/send-reminder','checkReminders');
    Route::get('/cleanup','cleanup');
});

