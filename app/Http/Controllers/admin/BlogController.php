<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Blog;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class BlogController extends Controller
{
     public function index()
    { 
         $blogs = Blog::with('category')->latest()->get();
        return view('admin.blog.index', compact('blogs'));
    }

     public function create()
    {
        $categories = Category::latest()->where('type','blog')->get();
        return view('admin.blog.create',compact('categories'));
    }



    public function store(Request $request)
    {
        try {
            $request->validate([
                'category_id'       => 'nullable|exists:categories,id', 
                'title'             => 'required|string|max:255',
                'short_description' => 'nullable|string|max:1000',
                'content'           => 'required|string',
                'image'             => 'required|image|mimes:jpeg,png,jpg,webp,avif|max:2048|dimensions:min_width=500,min_height=500',
                'slug'              => 'required|string|unique:blogs,slug',
                'author_name'       => 'nullable|string|max:255',
                'author_link'       => 'nullable|url|max:500',
                'published_at'      => 'nullable|date',
            ]);

            $data = $request->only([
                'category_id',
                'title', 
                'short_description', 
                'content', 
                'slug',
                'author_name',
                'author_link',
                'published_at'
            ]);

            $data['admin_id'] = Auth::id();

            // Set published_at to current time if not provided
            if (empty($data['published_at'])) {
                $data['published_at'] = now();
            }

            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads/blog'), $imageName);
                $data['image'] = 'uploads/blog/' . $imageName;
            }

            // Save blog
            Blog::create($data);

            return redirect()->route('admin.blogs.index')
                ->with('success', 'Blog created successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // If validation fails, automatically redirected back with errors
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            // Log unexpected errors
            Log::error('Blog Store Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Something went wrong while creating the blog.')
                ->withInput();
        }
    }


     public function edit(Blog $blog)
    {
          $categories = Category::all();
        return view('admin.blog.edit', compact('blog','categories'));
    }

    

   
    public function destroy(Blog $blog)
    {
        if ($blog->image && file_exists(public_path($blog->image))) {
            unlink(public_path($blog->image));
        }
        
        $blog->delete();

        return redirect()->route('admin.blogs.index')->with('success', 'Blog deleted successfully.');
    }

 
    public function update(Request $request, Blog $blog)
    {
        $request->validate([
            'category_id'       => 'nullable|exists:categories,id', 
            'title'             => 'required|string|max:255',
            'short_description' => 'required|string|max:1000',
            'content'           => 'required|string',
            'image'             => 'nullable|image|mimes:jpeg,png,jpg,webp,avif|max:2048|dimensions:min_width=500,min_height=500',
            'slug'              => 'required|string|unique:blogs,slug,' . $blog->id,
            'remove_image'      => 'nullable|boolean',
            'author_name'       => 'nullable|string|max:255',
            'author_link'       => 'nullable|url|max:500',
            'published_at'      => 'nullable|date',
        ]);

        $data = $request->only([
            'category_id',
            'title', 
            'short_description', 
            'content', 
            'status', 
            'slug',
            'author_name',
            'author_link',
            'published_at'
        ]);

        if (empty($data['published_at'])) {
            $data['published_at'] = now();
        }

       
        if ($request->has('remove_image') && $request->remove_image == 1) {
            if ($blog->image && file_exists(public_path($blog->image))) {
                unlink(public_path($blog->image));
            }
            $data['image'] = null;
        } 
    
        else if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($blog->image && file_exists(public_path($blog->image))) {
                unlink(public_path($blog->image));
            }

            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/blog'), $imageName);
            $data['image'] = 'uploads/blog/' . $imageName;
        } 
        else {
            $data['image'] = $blog->image;
        }

        $blog->update($data);

        return redirect()->route('admin.blogs.index')
            ->with('success', 'Blog updated successfully.');
    }
}