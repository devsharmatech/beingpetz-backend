<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\FriendRequestController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\LostFoundReportController;
use App\Http\Controllers\AdoptionListingController;
use App\Http\Controllers\VaccineRecordController;
use App\Http\Controllers\CommunityChatController;
use App\Http\Controllers\FriendChatController;
use App\Http\Controllers\VeterinaryDoctorController;
use App\Http\Controllers\PetShopController;
use App\Http\Controllers\PetGroomerController;
use App\Http\Controllers\PetTrainerController;
use App\Http\Controllers\PetWalkerController;
use App\Http\Controllers\PetBehaviouristController;
use App\Http\Controllers\PetResortController;
use App\Http\Controllers\PetShelterController;
use App\Http\Controllers\PetSitterController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\NotificationController;

use App\Http\Controllers\FavoriteController;

use App\Http\Controllers\PetRecordController;
use App\Http\Controllers\DewormingRecordController;
use App\Http\Controllers\GroomingRecordController;
use App\Http\Controllers\MealRecordController;
use App\Http\Controllers\WeightRecordController;
use App\Http\Controllers\GeneralRecordController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Api\CMSApiController;
use Illuminate\Support\Facades\Route;


Route::controller(AuthApiController::class)->prefix('v1/auth/')->group(function(){
    Route::post('register','register');
    Route::post('register-verify','registerVerify');
    Route::post('login','login');
    Route::post('login-verify','verifyOtpLogin');
    Route::post('login-social','socialLogin');
    Route::post('update-profile','updateProfile');
    Route::post('my-detail','myDetails');
    Route::post('update-profile-picture','updateProfilePicture');
    Route::post('delete-profile-picture','deleteProfilePicture');
    Route::post('delete-profile','deleteAccount');
    Route::post('forget-password','forgetPassword');
    Route::post('change-password','changePassword');
    
});

Route::controller(PetController::class)->prefix('v1/pet/')->group(function(){
    Route::post('add','store');
    Route::post('delete','deletePet');
    Route::post('update','updateMyPet');
    Route::post('get/my','getMyPets');
    Route::post('detail','getDetailPet');
    Route::post('phone/show-hide','togglePhoneVisibility');
    Route::post('name/show-hide','toggleNameVisibility');
});

Route::controller(ReportController::class)->prefix('v1/report/')->group(function(){
    Route::post('add','addReport');
});

Route::controller(ReportController::class)->prefix('v1/')->group(function(){
    Route::post('hide-post','hideUnhidePost');
    Route::post('unhide-post','hideUnhidePost');
});
Route::controller(BlogController::class)->prefix('v1/')->group(function(){
    // Blog Routes
    Route::get('/blogs','index');
    Route::post('/blogs', 'store');
    Route::get('/blogs/{blog}', 'show');
    Route::put('/blogs/{blog}', 'update');
    Route::delete('/blogs/{blog}', 'destroy');
});
Route::controller(EventController::class)->prefix('v1/')->group(function(){
    // Event Routes
    Route::get('/events','index');
    Route::post('/events','store');
    Route::get('/events/{event}', 'show');
    Route::put('/events/{event}','update');
    Route::delete('/events/{event}','destroy');
});
Route::controller(PostController::class)->prefix('v1/post/')->group(function(){
    Route::post('create','store');
    Route::post('re-post','repost');
    Route::post('delete','deletePost');
    Route::post('update','updatePost');
    Route::post('get/my','getMyPosts');
    Route::get('/all', 'getAllPosts');
    Route::post('/like','like');
    Route::post('/share','share');
    Route::post('/comment','comment');
    Route::post('/get-comment','getComment');
    
    Route::post('/get-post-details','getPostDetails');
});
Route::controller(PostController::class)->prefix('v1/')->group(function(){
    Route::post('search','search');
});

Route::controller(FriendRequestController::class)->prefix('v1/pet/friends/')->group(function(){
   Route::post('/send-request','sendRequest');
   Route::post('/get','getFriends');
   Route::post('/get-requests','getRequests');
   Route::post('/respond-request','respondRequest');
   Route::post('/suggestions','friendSuggestions');
   Route::post('/search-user','searchUsers');
   Route::post('/get-all-users','getAllUsers');
});

Route::controller(CommunityController::class)->prefix('v1/pet/community/')->group(function(){
   Route::post('/create','createCommunity');
   Route::get('/get','getCommunities');
   Route::post('/join','joinCommunity');
   Route::post('/left-join','leftCommunity');
   Route::post('/my','myJoinedCommunities');
   Route::post('/details','getCommunity');
   Route::post('/search','searchCommunity');
   
   Route::post('/update-profile','updateCommunityProfile');
   
   Route::post('add-role', 'addModeratorOrAdmin');
    Route::post('remove-role', 'removeModeratorOrAdmin');
    
    
   Route::post('/create2','createCommunityWithMembers');
   Route::post('/remove-member','removeCommunityMember');
   Route::post('/make-admin','makeAdminCommunity');
   Route::post('/add-member-by-admin','addCommunityMember');
   Route::post('/pending-requests','pendingCommunityMemberRequest');
   Route::post('/approve-join','approveCommunityMemberRequest');
   Route::post('/reject-join','rejectCommunityMemberRequest');
});
Route::controller(FavoriteController::class)->prefix('v1/favorite/')->group(function(){
  Route::post('/behaviourist','togglePetBehaviouristFavorite');
  Route::post('/groomer','togglePetGroomerFavorite');
  Route::post('/resort-owner','togglePetResortOwnerFavorite');
  Route::post('/shelter','togglePetShelterFavorite');
  Route::post('/shop','togglePetShopFavorite');
  Route::post('/sitter','togglePetSitterFavorite');
  Route::post('/trainer','togglePetTrainerFavorite');
  
  Route::post('/services/get','getFavoritesByUserId');
});

Route::controller(LostFoundReportController::class)->prefix('v1/pet/lost-found/')->group(function(){
    Route::post('/store','store');
    Route::get('/all','getAllReports');
    Route::post('/my-reports','getMyReports');
    Route::post('/update','update');
    Route::post('/delete','destroy');
});

Route::controller(AdoptionListingController::class)->prefix('v1/pet/')->group(function(){
   Route::post('/create-adoption','create');
   Route::post('/edit-adoption','editAdoption');
   Route::post('/my-adoptions', 'myListings');
   Route::post('/all-adoptions','allListings');
   Route::post('/delete-adoption','delete');
});
Route::controller(BannerController::class)->prefix('v1/banner/')->group(function(){
   Route::get('/all','getBanner');
   Route::get('/all-ads','getAdBanner');
});

Route::controller(VaccineRecordController::class)->prefix('v1/vaccine/')->group(function(){
    Route::post('all-records','index');
    Route::post('get','getVaccineByPet');
    Route::get('get-all','getAll');
    Route::post('save-records','store');
    Route::post('update-records','update');
    Route::post('delete-record','deleteData');
});
Route::controller(VeterinaryDoctorController::class)->prefix('v1/veterinary/')->group(function(){
    Route::get('doctors','index');
});
Route::controller(PetShopController::class)->prefix('v1/pet/')->group(function(){
    Route::get('shops','index');
});
Route::controller(PetGroomerController::class)->prefix('v1/pet/')->group(function(){
    Route::get('groomers','index');
});
Route::controller(PetTrainerController::class)->prefix('v1/pet/')->group(function(){
    Route::get('trainers','index');
});
Route::controller(PetWalkerController::class)->prefix('v1/pet/')->group(function(){
    Route::get('walkers','index');
});
Route::controller(PetBehaviouristController::class)->prefix('v1/pet/')->group(function(){
    Route::get('behaviourists','index');
});
Route::controller(PetResortController::class)->prefix('v1/pet/')->group(function(){
    Route::get('resorts','index');
});

Route::controller(PetShelterController::class)->prefix('v1/pet/')->group(function(){
    Route::get('shelters','index');
});
Route::controller(PetSitterController::class)->prefix('v1/pet/')->group(function(){
    Route::get('sitters','index');
});

Route::controller(DewormingRecordController::class)->prefix('v1/deworming/')->group(function(){
    Route::post('all-records','index');
    Route::post('save-records','store');
    Route::post('update-records','update');
    Route::post('delete-record','deleteData');
});

Route::controller(GroomingRecordController::class)->prefix('v1/grooming/')->group(function(){
    Route::post('all-records','index');
    Route::post('save-records','store');
    Route::post('update-records','update');
    Route::post('get/type','getType');
    Route::post('delete-record','deleteData');
});

Route::controller(MealRecordController::class)->prefix('v1/meal/')->group(function(){
    Route::post('all-records','index');
    Route::post('save-records','store');
    Route::post('update-records','update');
    Route::post('delete-record','deleteData');
});

Route::controller(WeightRecordController::class)->prefix('v1/weight/')->group(function(){
    Route::post('all-records','index');
    Route::post('save-records','store');
    Route::post('update-records','update');
    Route::post('delete-record','deleteData');
});
Route::controller(GeneralRecordController::class)->prefix('v1/general/')->group(function(){
    Route::post('all-records','index');
    Route::post('save-records','store');
    Route::post('update-records','update');
    Route::post('delete-record','deleteData');
});


Route::controller(CommunityChatController::class)->prefix('v1/community')->group(function () {
    Route::post('send-message','sendMessage');
    Route::post('get-messages','getMessages');
    Route::post('delete-message-for-me','deleteForMe');
    Route::post('delete-message-for-all','deleteForAll');
    Route::post('vote-poll','votePoll');
    Route::post('message/like-unlike','toggleLike');
});

Route::controller(FriendChatController::class)->prefix('v1/friend')->group(function () {
    Route::post('send-message','sendMessage');
    Route::post('get-messages','getMessages');
});

Route::controller(PetRecordController::class)->prefix('v1/pet')->group(function () {
    Route::post('records','getAllRecords');
});
Route::controller(NotificationController::class)->prefix('v1/notification')->group(function () {
    Route::post('list', 'getNotifications');           
    Route::post('read', 'markNotificationRead');       
    Route::post('delete', 'deleteNotification');       
    Route::post('clear', 'clearAllNotifications');     
    Route::post('update-device/token', 'updateToken');     
});

Route::controller(CMSApiController::class)->prefix('v1/cms')->group(function () {
    Route::get('terms', 'getTerms');
    Route::get('privacy', 'getPrivacyPolicy');
    Route::get('legal', 'getLegal');
    Route::get('settings', 'getSettings');
});

