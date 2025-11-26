<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::where('business_id', Auth::user()->current_business_id)
            ->withCount('products')
            ->orderBy('name')
            ->get();

        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'prefix' => 'required|string|max:10',
            'description' => 'nullable|string',
        ]);

        Category::create([
            'business_id' => Auth::user()->current_business_id,
            'name' => $request->name,
            'prefix' => strtoupper($request->prefix),
            'description' => $request->description,
        ]);

        return redirect()->route('categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        if ($category->business_id !== Auth::user()->current_business_id) {
            abort(403, 'Unauthorized action.');
        }

        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        if ($category->business_id !== Auth::user()->current_business_id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'prefix' => 'required|string|max:10',
            'description' => 'nullable|string',
        ]);

        $category->update([
            'name' => $request->name,
            'prefix' => strtoupper($request->prefix),
            'description' => $request->description,
        ]);

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        if ($category->business_id !== Auth::user()->current_business_id) {
            abort(403, 'Unauthorized action.');
        }

        $productsCount = $category->products()->count();
        
        if ($productsCount > 0) {
            return redirect()->route('categories.index')
                ->with('error', 'Cannot delete category with existing products.');
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
