<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\admin\auth\AuthController;
use App\Http\Controllers\admin\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\CategoryController;
use App\Http\Controllers\admin\BlogController;
use App\Http\Controllers\admin\ParentsController;
use App\Http\Controllers\admin\ServiceController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\FriendChatController;
use App\Http\Controllers\admin\ProfileController;
use App\Http\Controllers\admin\SettingController;
use App\Http\Controllers\admin\ProviderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\admin\UserVendorController;

use App\Http\Controllers\admin\RoleController;
use App\Http\Controllers\admin\PermissionController;


Route::name('admin.')->group(function () {

    Route::controller(AuthController::class)->group(function () {
        Route::get('/login', 'login')->name('login');
        Route::post('/login', 'loginSubmit')->name('loginSubmit');
        Route::get('/logout', 'logout')->name('logout')->middleware('admin');
    });
    
    Route::middleware(['admin'])->group(function () {
        // Dashboard - always accessible
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // Profile routes - always accessible
        Route::get('/profile', [ProfileController::class, 'profile'])->name('profile');
        Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/update-profile', [ProfileController::class, 'updateAvatar'])->name('profile.update-avatar');
        Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password');

        // Categories - requires permission
        Route::middleware(['check.permission:categories'])->group(function () {
            Route::resource('categories', CategoryController::class)->except(['create', 'edit', 'show']);
        });

        // Blogs - requires permission
        Route::middleware(['check.permission:blogs'])->controller(BlogController::class)->prefix('blogs')->name('blogs.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/{blog}/edit', 'edit')->name('edit');
            Route::put('/{blog}', 'update')->name('update');
            Route::delete('/{blog}', 'destroy')->name('destroy');
        });

        // Events - requires permission
        Route::middleware(['check.permission:events'])->controller(EventController::class)->prefix('events')->name('events.')->group(function () {
            Route::get('/', 'event_list')->name('list');
            Route::get('/create', 'event_create')->name('create');
            Route::post('/store', 'event_store')->name('store');
            Route::get('/{event}/edit', 'event_edit')->name('edit');
            Route::put('/{event}', 'event_update')->name('update');
            Route::delete('/{event}', 'event_delete')->name('delete');
        });

        // Pets - requires permission
        Route::middleware(['check.permission:pets'])->controller(PetController::class)->prefix('pets')->name('pets.')->group(function () {
            Route::get('/', 'pet_list')->name('list');
            Route::post('/store', 'pet_save')->name('save');
            Route::put('/{pet}', 'pet_update')->name('update');
            Route::delete('/{pet}', 'pet_delete')->name('delete');
            Route::get('/export', 'export')->name('export');
        });

        // Parents - requires permission
        Route::middleware(['check.permission:parents'])->controller(ParentsController::class)->prefix('parents')->name('parents.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/{parent}/edit', 'edit')->name('edit');
            Route::put('/{parent}', 'update')->name('update');
            Route::delete('/{parent}', 'destroy')->name('destroy');
            Route::get('/export/csv', 'exportCSV')->name('export.csv');
        });

        // Settings - requires permission
        Route::middleware(['check.permission:settings'])->prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingController::class, 'index'])->name('index');
            Route::get('/{group}', [SettingController::class, 'manage'])->name('manage');
            Route::put('/{group}', [SettingController::class, 'update'])->name('update');
        });

        // Services - requires permission
        Route::middleware(['check.permission:services'])->controller(ServiceController::class)->prefix('services')->name('services.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{service}/edit', 'edit')->name('edit');
            Route::put('/{service}', 'update')->name('update');
            Route::delete('/{service}', 'destroy')->name('destroy');
        });
        
        Route::middleware(['check.permission:services'])->group(function () {
            Route::post('/services/upload-providers', [ServiceController::class, 'uploadProviders'])->name('services.upload-providers');
            Route::get('/services/download-template', [ServiceController::class, 'downloadTemplate'])->name('services.download-template');
            Route::get('services/{service}/providers', [ServiceController::class, 'getProviders'])->name('services.providers');
        });

        // Providers - requires permission
        Route::middleware(['check.permission:services'])->resource('providers', ProviderController::class);

        // Banner - requires permission
        Route::middleware(['check.permission:banner'])->controller(BannerController::class)->prefix('banner')->name('banner.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{banner}/edit', 'edit')->name('edit');
            Route::put('/{banner}', 'update')->name('update');
            Route::delete('/{banner}', 'destroy')->name('destroy');
        });

        // Service Banner - requires permission
        Route::middleware(['check.permission:service-banner'])->controller(BannerController::class)->prefix('service-banner')->name('service-banner.')->group(function () {
            Route::get('/', 'service_list')->name('index');
            Route::get('/create', 'service_create')->name('create');
            Route::post('/', 'service_store')->name('store');
            Route::get('/{banner}/edit', 'service_edit')->name('edit');
            Route::put('/{banner}', 'service_update')->name('update');
            Route::delete('/{banner}', 'service_destroy')->name('destroy');
        });

        // Adoption Banner - requires permission
        Route::middleware(['check.permission:banner'])->controller(BannerController::class)->prefix('adoption-banner')->name('adoption-banner.')->group(function () {
            Route::get('/', 'adoption_list')->name('index');
            Route::get('/create', 'adoption_create')->name('create');
            Route::post('/', 'adoption_store')->name('store');
            Route::get('/{banner}/edit', 'adoption_edit')->name('edit');
            Route::put('/{banner}', 'adoption_update')->name('update');
            Route::delete('/{banner}', 'adoption_destroy')->name('destroy');
        });

        // Lost & Found Banner - requires permission
        Route::middleware(['check.permission:banner'])->controller(BannerController::class)->prefix('lost-found-banner')->name('lost-found-banner.')->group(function () {
            Route::get('/', 'lost_found_list')->name('index');
            Route::get('/create', 'lost_found_create')->name('create');
            Route::post('/', 'lost_found_store')->name('store');
            Route::get('/{banner}/edit', 'lost_found_edit')->name('edit');
            Route::put('/{banner}', 'lost_found_update')->name('update');
            Route::delete('/{banner}', 'lost_found_destroy')->name('destroy');
        });

        // Reports - requires permission
        Route::middleware(['check.permission:reports'])->group(function () {
            Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
            Route::put('/reports/{id}/status', [ReportController::class, 'updateStatus'])->name('reports.updateStatus');
            Route::delete('/reports/{id}', [ReportController::class, 'destroy'])->name('reports.destroy');
            Route::post('/reports/{id}/delete-content', [ReportController::class, 'deleteContent'])->name('reports.deleteContent');
            Route::get('/reports/{id}/preview', [ReportController::class, 'getPreview'])->name('reports.preview');
            Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
        });

        // Messages - requires permission
        Route::middleware(['check.permission:messages'])->prefix('messages')->name('messages.')->group(function () {
            Route::get('/', [FriendChatController::class, 'index'])->name('index');
            Route::post('/{id}/mark-as-read', [FriendChatController::class, 'markAsRead'])->name('markAsRead');
            Route::get('/conversation/{senderId}/{receiverId}', [FriendChatController::class, 'conversationHistory'])->name('conversation');
            Route::delete('/delete/{id}', [FriendChatController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-delete', [FriendChatController::class, 'bulkDelete'])->name('bulkDelete');
        });

        // Posts - requires permission
        Route::middleware(['check.permission:post'])->controller(PostController::class)->prefix('post')->name('post.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::get('/{post}', 'show')->name('show');
            Route::post('/', 'save_post')->name('save_post');
            Route::get('/{post}/edit', 'edit')->name('edit');
            Route::put('/{post}', 'update')->name('update');
            Route::delete('/{post}', 'delete')->name('delete');
        });
        
        Route::middleware(['check.permission:post'])->group(function () {
            Route::get('post-logs', [PostController::class, 'post_logs'])->name('post.history-log');
            Route::put('/{post}/restore', [PostController::class, 'restore_post'])->name('post.restore');
            Route::delete('/{post}/force-delete', [PostController::class, 'forceDelete'])->name('post.force-delete');
            Route::post('posts/toggle-status/{id}', [PostController::class, 'toggleStatus'])->name('posts.toggleStatus');
            Route::get('posts/{id}/gallery', [PostController::class, 'gallery'])->name('post.gallery');
            Route::delete('media/image/{id}', [PostController::class, 'deleteImage'])->name('media.image.delete');
            Route::delete('media/video/{id}', [PostController::class, 'deleteVideo'])->name('media.video.delete');
        });

        // Community - requires permission
        Route::middleware(['check.permission:community'])->controller(CommunityController::class)->prefix('community')->name('community.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{community}/edit', 'edit')->name('edit');
            Route::put('/{community}', 'update')->name('update');
            Route::delete('/{community}', 'destroy')->name('destroy');
        });
        
        Route::middleware(['check.permission:community'])->group(function () {
            Route::get('/community/export', [CommunityController::class, 'export'])->name('community.export');
            Route::get('/community/{id}/transfer-ownership', [CommunityController::class, 'showTransferForm'])->name('community.transfer.show');
            Route::post('/community/{id}/transfer-ownership', [CommunityController::class, 'transferOwnership'])->name('community.transfer');
        });

        // Notifications - requires permission
        Route::middleware(['check.permission:notifications'])->group(function () {
            Route::resource('notifications', NotificationController::class);
            Route::post('/notifications/{notification}/toggle-status', [NotificationController::class, 'toggleStatus'])->name('notifications.toggle-status');
            Route::post('/notifications/{notification}/schedule', [NotificationController::class, 'schedule'])->name('notifications.schedule');
            Route::post('/notifications/bulk-upload', [NotificationController::class, 'bulkUpload'])->name('notifications.bulk-upload');
        });

        // Users & Vendors - requires permission
        Route::middleware(['check.permission:uservendors'])->group(function () {
            Route::resource('uservendors', UserVendorController::class);
            Route::post('uservendors/{id}/reset-password', [UserVendorController::class, 'resetPassword'])
                ->name('uservendors.reset-password');
        });

        // Additional routes
        Route::get('get-pets-by-user/{user_id}', [PostController::class, 'getPetsByUser']);
        Route::get('/deleted-users', [AdminController::class, 'deletedUsers'])->name('users.deleted');
        Route::get('/user/{id}', [AdminController::class, 'userDetails'])->name('users.details');
        Route::get('/export-chart-data', [AdminController::class, 'exportChartData'])->name('export.chart');
        Route::get('/export-chart-image', [AdminController::class, 'exportChartImage'])->name('export.chart.image');
        Route::get('/get-chart-data', [AdminController::class, 'getChartData'])->name('chart.data');
    });



    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::get('/create', [RoleController::class, 'create'])->name('create');
        Route::post('/', [RoleController::class, 'store'])->name('store');
        Route::get('/{id}', [RoleController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [RoleController::class, 'edit'])->name('edit');
        Route::put('/{id}', [RoleController::class, 'update'])->name('update');
        Route::delete('/{id}', [RoleController::class, 'destroy'])->name('destroy');
        Route::get('/api/list', [RoleController::class, 'getRoles'])->name('api.list');
    });
    
    Route::prefix('permissions')->name('permissions.')->group(function () {
        Route::get('/', [PermissionController::class, 'index'])->name('index');
        Route::get('/create', [PermissionController::class, 'create'])->name('create');
        Route::post('/', [PermissionController::class, 'store'])->name('store');
        Route::get('/{permission}/edit', [PermissionController::class, 'edit'])->name('edit');
        Route::put('/{permission}', [PermissionController::class, 'update'])->name('update');
        Route::delete('/{permission}', [PermissionController::class, 'destroy'])->name('destroy');
        Route::post('/{permission}/toggle-status', [PermissionController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/by-module', [PermissionController::class, 'getByModule'])->name('by-module');
    });



});