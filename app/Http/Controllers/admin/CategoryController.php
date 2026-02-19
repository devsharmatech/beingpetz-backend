<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::latest()->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'type' => 'required|in:blog,event',
        ]);

        Category::create($request->all());

        return redirect()->route('admin.categories.index')
                        ->with('success', 'Category created successfully.');
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'type' => 'required|in:blog,event',
        ]);

        $category->update($request->all());

        return redirect()->route('admin.categories.index')
                        ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('admin.categories.index')
                        ->with('success', 'Category deleted successfully.');
    }
}