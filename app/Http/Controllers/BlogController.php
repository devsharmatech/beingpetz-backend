<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Validator;


class BlogController extends Controller
{
   public function index()
    {
        $blogs = Blog::with('admin')->latest()->paginate(3);
        return response()->json(['status'=>true,'blogs'=>$blogs]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'admin_id'             => 'required|exists:users,id',
            'title'             => 'required|string|max:255',
            'short_description' => 'required|string|max:500',
            'content'           => 'required',
            'image'             => 'nullable|image',
        ]);

        if ($validator->fails()) {
            return response()->json(['status'=>false,'message'=>$validator->errors()->first(),'errors' => $validator->errors()], 422);
        }

        $slug = $this->generateUniqueSlug($request->title);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $uploadPath = public_path('uploads/blog');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $fileName = time() . '-' . Str::slug(pathinfo($request->image->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $request->image->getClientOriginalExtension();
            $request->image->move($uploadPath, $fileName);

            $imagePath = 'uploads/blog/' . $fileName;
        }

        $blog = Blog::create([
            'admin_id'          => $request->admin_id ?? null,
            'title'             => $request->title,
            'slug'              => $slug,
            'short_description' => $request->short_description,
            'content'           => $request->content,
            'image'             => $imagePath,
            'author_name'       => $request->author_name ?? null,
            'author_link'       => $request->author_link ?? null,
        ]);

        return response()->json(['status'=>true,'blog'=>$blog], 201);
    }

    public function show(Blog $blog)
    {
        $blog->load('admin');
        return response()->json(['status'=>true,'blog'=>$blog]);
    }

    public function update(Request $request, Blog $blog)
    {
        $validator = Validator::make($request->all(), [
            'title'             => 'sometimes|string|max:255',
            'short_description' => 'sometimes|string|max:500',
            'content'           => 'sometimes',
            'image'             => 'nullable|image',
        ]);

        if ($validator->fails()) {
            return response()->json(['status'=>false,'message'=>$validator->errors()->first(),'errors' => $validator->errors()], 422);
        }

        $slug = $request->title 
            ? $this->generateUniqueSlug($request->title, $blog->id) 
            : $blog->slug;

        $imagePath = $blog->image;
        if ($request->hasFile('image')) {
            $uploadPath = public_path('uploads/blog');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $fileName = time() . '-' . Str::slug(pathinfo($request->image->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $request->image->getClientOriginalExtension();
            $request->image->move($uploadPath, $fileName);

            $imagePath = 'uploads/blog/' . $fileName;
        }

        $blog->update([
            'title'             => $request->title ?? $blog->title,
            'slug'              => $slug,
            'short_description' => $request->short_description ?? $blog->short_description,
            'content'           => $request->content ?? $blog->content,
            'image'             => $imagePath,
            'author_name'       => $request->author_name ?? $blog->author_name,
            'author_link'       => $request->author_link ?? $blog->author_link,
        ]);

        return response()->json(['status'=>true,'blog'=>$blog]);
    }

    public function destroy(Blog $blog)
    {
        if ($blog->image && file_exists(public_path($blog->image))) {
            unlink(public_path($blog->image));
        }
        $blog->delete();
        return response()->json(['status'=>true,'message' => 'Blog deleted successfully']);
    }

    private function generateUniqueSlug($title, $ignoreId = null)
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $count = 1;

        while (Blog::where('slug', $slug)
                ->when($ignoreId, fn($query) => $query->where('id', '!=', $ignoreId))
                ->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        return $slug;
    }
    
}
