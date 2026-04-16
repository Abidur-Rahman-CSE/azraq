<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('parent')->orderBy('sort_order')->orderBy('name')->paginate(12);

        return view('admin.catalog.categories.index', compact('categories'));
    }

    public function create()
    {
        $category = new Category(['is_active' => true]);
        $parents = Category::orderBy('name')->get();

        return view('admin.catalog.categories.create', compact('category', 'parents'));
    }

    public function store(CategoryRequest $request)
    {
        Category::create($request->validated() + [
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $request->integer('sort_order'),
        ]);

        return redirect()->route('admin.catalog.categories.index')->with('status', 'Category created.');
    }

    public function edit(Category $category)
    {
        $parents = Category::whereKeyNot($category->id)->orderBy('name')->get();

        return view('admin.catalog.categories.edit', compact('category', 'parents'));
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $category->update($request->validated() + [
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $request->integer('sort_order'),
        ]);

        return redirect()->route('admin.catalog.categories.index')->with('status', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('admin.catalog.categories.index')->with('status', 'Category deleted.');
    }
}
