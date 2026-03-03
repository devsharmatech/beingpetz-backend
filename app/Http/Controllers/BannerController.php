<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\AdBanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;

class BannerController extends Controller
{
    public function getBanner(){
        $banners=Banner::orderBy('sort','ASC')->get();
        return response()->json([
            'status' => true,
            'message' => 'Your banners successfully fetched!',
            'banners' => $banners,
        ], 200);
    }
    public function getAdBanner(){
        $banners = AdBanner::orderBy('sort', 'ASC')->get()->map(function ($banner) {
        // Convert banner to array and replace 'image' with 'mobile_image'
        $data = $banner->toArray();
        $data['mobile_image'] = $data['image']; // rename
        unset($data['image']); // remove original 'image'
        return $data;
    });
        return response()->json([
            'status' => true,
            'message' => 'Your Ad banners successfully fetched!',
            'banners' => $banners,
        ], 200);
    }


    // admin dashboard functions will be here


   public function index()
    {
        $banners = Banner::orderBy('sort', 'asc')->get();
        return view('admin.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.banners.create');
    }

    public function store(Request $request)
    {
        try {
            Log::info('Banner store method called', ['request_data' => $request->all()]);

            $request->validate([
                'link' => 'nullable|url|max:255',
                'mobile_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'desktop_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'sort' => 'nullable|integer|min:0',
                'start_date' => 'nullable|date|after_or_equal:today',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ], [
                'mobile_image.max' => 'Mobile image must not exceed 2MB',
                'desktop_image.max' => 'Desktop image must not exceed 2MB',
                'end_date.after_or_equal' => 'End date must be after or equal to start date',
            ]);

            Log::info('Validation passed');

            $bannerData = $request->except(['mobile_image', 'desktop_image']);
            $bannerData['is_active'] = $request->has('is_active');

            // Ensure directory exists
            $uploadPath = public_path('uploads/banner');
            if (!file_exists($uploadPath)) {
                Log::info('Creating upload directory', ['path' => $uploadPath]);
                mkdir($uploadPath, 0755, true);
            }

            // Upload mobile image
            if ($request->hasFile('mobile_image')) {
                $mobileImage = $request->file('mobile_image');
                
                // Get image dimensions for validation
                $imageInfo = getimagesize($mobileImage);
                $width = $imageInfo[0];
                $height = $imageInfo[1];

                $mobileImageName = time() . '_mobile_' . uniqid() . '.' . $mobileImage->getClientOriginalExtension();
                $mobileFullPath = public_path('uploads/banner/' . $mobileImageName);
                
                Log::info('Uploading mobile image', [
                    'original_name' => $mobileImage->getClientOriginalName(),
                    'new_name' => $mobileImageName,
                    'full_path' => $mobileFullPath,
                    'dimensions' => $width . 'x' . $height
                ]);

                if ($mobileImage->move(public_path('uploads/banner'), $mobileImageName)) {
                    $bannerData['mobile_image'] = 'uploads/banner/' . $mobileImageName;
                    Log::info('Mobile image uploaded successfully', ['path' => $bannerData['mobile_image']]);
                } else {
                    throw new \Exception('Failed to upload mobile image');
                }
            }

            // Upload desktop image
            if ($request->hasFile('desktop_image')) {
                $desktopImage = $request->file('desktop_image');
                
                // Get image dimensions for validation
                $imageInfo = getimagesize($desktopImage);
                $width = $imageInfo[0];
                $height = $imageInfo[1];

                $desktopImageName = time() . '_desktop_' . uniqid() . '.' . $desktopImage->getClientOriginalExtension();
                $desktopFullPath = public_path('uploads/banner/' . $desktopImageName);
                
                Log::info('Uploading desktop image', [
                    'original_name' => $desktopImage->getClientOriginalName(),
                    'new_name' => $desktopImageName,
                    'full_path' => $desktopFullPath,
                    'dimensions' => $width . 'x' . $height
                ]);

                if ($desktopImage->move(public_path('uploads/banner'), $desktopImageName)) {
                    $bannerData['desktop_image'] = 'uploads/banner/' . $desktopImageName;
                    Log::info('Desktop image uploaded successfully', ['path' => $bannerData['desktop_image']]);
                } else {
                    throw new \Exception('Failed to upload desktop image');
                }
            }

            Log::info('Attempting to create banner', ['banner_data' => $bannerData]);

            // Create banner
            $banner = Banner::create($bannerData);

            Log::info('Banner created successfully', ['banner_id' => $banner->id]);

            return redirect()->route('admin.banner.index')->with('success', 'Banner created successfully.');

        } catch (\Exception $e) {
            Log::error('Banner creation failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create banner: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $banner = Banner::findOrFail($id);
        return view('admin.banners.edit', compact('banner'));
    }

    public function update(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);

        $request->validate([
            'link' => 'nullable|url|max:255',
            'mobile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'desktop_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'sort' => 'nullable|integer|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ], [
            'mobile_image.max' => 'Mobile image must not exceed 2MB',
            'desktop_image.max' => 'Desktop image must not exceed 2MB',
            'end_date.after_or_equal' => 'End date must be after or equal to start date',
        ]);

        $bannerData = $request->except(['mobile_image', 'desktop_image']);
        $bannerData['is_active'] = $request->has('is_active');

        // Ensure directory exists
        $uploadPath = public_path('uploads/banner');
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // Update mobile image if provided
        if ($request->hasFile('mobile_image')) {
            $mobileImage = $request->file('mobile_image');
            
            // Get image dimensions for validation
            $imageInfo = getimagesize($mobileImage);
            $width = $imageInfo[0];
            $height = $imageInfo[1];

            // Delete old mobile image
            if ($banner->mobile_image && file_exists(public_path($banner->mobile_image))) {
                unlink(public_path($banner->mobile_image));
            }

            $mobileImageName = time() . '_mobile_' . uniqid() . '.' . $mobileImage->getClientOriginalExtension();
            
            if ($mobileImage->move(public_path('uploads/banner'), $mobileImageName)) {
                $bannerData['mobile_image'] = 'uploads/banner/' . $mobileImageName;
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Failed to upload mobile image');
            }
        }

        // Update desktop image if provided
        if ($request->hasFile('desktop_image')) {
            $desktopImage = $request->file('desktop_image');
            
            // Get image dimensions for validation
            $imageInfo = getimagesize($desktopImage);
            $width = $imageInfo[0];
            $height = $imageInfo[1];

            // Delete old desktop image
            if ($banner->desktop_image && file_exists(public_path($banner->desktop_image))) {
                unlink(public_path($banner->desktop_image));
            }

            $desktopImageName = time() . '_desktop_' . uniqid() . '.' . $desktopImage->getClientOriginalExtension();
            
            if ($desktopImage->move(public_path('uploads/banner'), $desktopImageName)) {
                $bannerData['desktop_image'] = 'uploads/banner/' . $desktopImageName;
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Failed to upload desktop image');
            }
        }

        $banner->update($bannerData);

        return redirect()->route('admin.banner.index')->with('success', 'Banner updated successfully.');
    }

    public function destroy($id)
    {
        $banner = Banner::findOrFail($id);
        
        // Delete images
        if ($banner->mobile_image && file_exists(public_path($banner->mobile_image))) {
            unlink(public_path($banner->mobile_image));
        }
        
        if ($banner->desktop_image && file_exists(public_path($banner->desktop_image))) {
            unlink(public_path($banner->desktop_image));
        }

        $banner->delete();

        return redirect()->route('admin.banner.index')->with('success', 'Banner deleted successfully.');
    }


    // Admin Dashbord service banner functions will be here

    public function service_list()
    {
        $serviceBanners = AdBanner::where('section', 'services')
            ->orderBy('sort', 'asc')
            ->get();
        return view('admin.banners.service_banner', compact('serviceBanners'));
    }

    public function adoption_list()
    {
        $adoptionBanners = AdBanner::where('section', 'adoption')
            ->orderBy('sort', 'asc')
            ->get();
        return view('admin.banners.adoption_banner', compact('adoptionBanners'));
    }

    public function lost_found_list()
    {
        $lostFoundBanners = AdBanner::where('section', 'lost_found')
            ->orderBy('sort', 'asc')
            ->get();
        return view('admin.banners.lost_found_banner', compact('lostFoundBanners'));
    }

    public function service_create()
    {
        return view('admin.banners.service_banner_create');
    }

    public function adoption_create()
    {
        return view('admin.banners.adoption_banner_create');
    }

    public function lost_found_create()
    {
        return view('admin.banners.lost_found_banner_create');
    }

    public function service_store(Request $request)
    {
        return $this->storeBanner($request, 'services');
    }

    public function adoption_store(Request $request)
    {
        return $this->storeBanner($request, 'adoption');
    }

    public function lost_found_store(Request $request)
    {
        return $this->storeBanner($request, 'lost_found');
    }

    private function storeBanner(Request $request, $section)
    {
        try {
            Log::info("{$section} banner store method called", ['request_data' => $request->all()]);

            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'sort' => 'nullable|integer|min:0',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
            ]);

            Log::info('Validation passed');

            // Check image dimensions
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                list($width, $height) = getimagesize($image->getPathname());
            }

            // Ensure directory exists
            $uploadPath = public_path('uploads/ads');
            if (!file_exists($uploadPath)) {
                Log::info('Creating upload directory', ['path' => $uploadPath]);
                mkdir($uploadPath, 0755, true);
            }

            $bannerData = [
                'sort' => $request->sort ?? 0,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'section' => $section
            ];

            // Upload image
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $fullPath = public_path('uploads/ads/' . $imageName);
                
                Log::info('Uploading banner image', [
                    'original_name' => $image->getClientOriginalName(),
                    'new_name' => $imageName,
                    'full_path' => $fullPath
                ]);

                // Simple move without resizing
                if ($image->move(public_path('uploads/ads'), $imageName)) {
                    $bannerData['image'] = 'uploads/ads/' . $imageName;
                    Log::info('Banner image uploaded successfully', ['path' => $bannerData['image']]);
                } else {
                    throw new \Exception('Failed to upload banner image');
                }
            }

            Log::info('Attempting to create banner', ['banner_data' => $bannerData]);

            // Create banner
            $banner = AdBanner::create($bannerData);

            Log::info('Banner created successfully', ['banner_id' => $banner->id]);

            $route = match($section) {
                'adoption' => 'admin.adoption-banner.index',
                'lost_found' => 'admin.lost-found-banner.index',
                default => 'admin.service-banner.index'
            };

            return redirect()->route($route)->with('success', ucfirst(str_replace('_', ' ', $section)) . ' banner created successfully.');

        } catch (\Exception $e) {
            Log::error('Banner creation failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create banner: ' . $e->getMessage());
        }
    }

    public function service_edit($id)
    {
        $serviceBanner = AdBanner::where('section', 'services')->findOrFail($id);
        return view('admin.banners.service_banner_edit', compact('serviceBanner'));
    }

    public function adoption_edit($id)
    {
        $banner = AdBanner::where('section', 'adoption')->findOrFail($id);
        return view('admin.banners.adoption_banner_edit', compact('banner'));
    }

    public function lost_found_edit($id)
    {
        $banner = AdBanner::where('section', 'lost_found')->findOrFail($id);
        return view('admin.banners.lost_found_banner_edit', compact('banner'));
    }

    public function service_update(Request $request, $id)
    {
        return $this->updateBanner($request, $id, 'services');
    }

    public function adoption_update(Request $request, $id)
    {
        return $this->updateBanner($request, $id, 'adoption');
    }

    public function lost_found_update(Request $request, $id)
    {
        return $this->updateBanner($request, $id, 'lost_found');
    }

    private function updateBanner(Request $request, $id, $section)
    {
        try {
            $banner = AdBanner::where('section', $section)->findOrFail($id);

            Log::info("{$section} banner update method called", [
                'banner_id' => $id,
                'request_data' => $request->all()
            ]);

            $request->validate([
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'sort' => 'nullable|integer|min:0',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
            ]);

            // Check image dimensions if new image is uploaded
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                list($width, $height) = getimagesize($image->getPathname());
            }

            $bannerData = $request->except('image');
            $bannerData['section'] = $section;

            // Update image if provided
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($banner->image && file_exists(public_path($banner->image))) {
                    Log::info('Deleting old banner image', ['old_image' => $banner->image]);
                    unlink(public_path($banner->image));
                }

                $image = $request->file('image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $fullPath = public_path('uploads/ads/' . $imageName);
                
                Log::info('Uploading new banner image', [
                    'original_name' => $image->getClientOriginalName(),
                    'new_name' => $imageName,
                    'full_path' => $fullPath
                ]);

                // Simple move without resizing
                if ($image->move(public_path('uploads/ads'), $imageName)) {
                    $bannerData['image'] = 'uploads/ads/' . $imageName;
                    Log::info('New banner image uploaded successfully', ['path' => $bannerData['image']]);
                } else {
                    throw new \Exception('Failed to upload new banner image');
                }
            }

            Log::info('Updating banner', ['banner_data' => $bannerData]);

            $banner->update($bannerData);

            Log::info('Banner updated successfully', ['banner_id' => $banner->id]);

            $route = match($section) {
                'adoption' => 'admin.adoption-banner.index',
                'lost_found' => 'admin.lost-found-banner.index',
                default => 'admin.service-banner.index'
            };

            return redirect()->route($route)->with('success', ucfirst(str_replace('_', ' ', $section)) . ' banner updated successfully.');

        } catch (\Exception $e) {
            Log::error('Banner update failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update banner: ' . $e->getMessage());
        }
    }

    public function service_destroy($id)
    {
        return $this->destroyBanner($id, 'services');
    }

    public function adoption_destroy($id)
    {
        return $this->destroyBanner($id, 'adoption');
    }

    public function lost_found_destroy($id)
    {
        return $this->destroyBanner($id, 'lost_found');
    }

    private function destroyBanner($id, $section)
    {
        try {
            $banner = AdBanner::where('section', $section)->findOrFail($id);

            Log::info('Deleting banner', ['banner_id' => $id, 'section' => $section]);

            // Delete image if exists
            if ($banner->image && file_exists(public_path($banner->image))) {
                Log::info('Deleting banner image', ['image_path' => $banner->image]);
                unlink(public_path($banner->image));
            }

            $banner->delete();

            Log::info('Banner deleted successfully', ['banner_id' => $id]);

            $route = match($section) {
                'adoption' => 'admin.adoption-banner.index',
                'lost_found' => 'admin.lost-found-banner.index',
                default => 'admin.service-banner.index'
            };

            return redirect()->route($route)->with('success', ucfirst(str_replace('_', ' ', $section)) . ' banner deleted successfully.');

        } catch (\Exception $e) {
            Log::error('Banner deletion failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return redirect()->back()->with('error', 'Failed to delete banner: ' . $e->getMessage());
        }
    }

    
}
