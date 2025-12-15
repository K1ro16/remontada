<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ActivityLogger;

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

        $category = Category::create([
            'business_id' => Auth::user()->current_business_id,
            'name' => $request->name,
            'prefix' => strtoupper($request->prefix),
            'description' => $request->description,
        ]);

        ActivityLogger::log('created', 'Category', $category->id, null, $category->toArray(), 'Created category ' . $category->name . ' [' . $category->prefix . ']');

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
            'update_skus' => 'nullable',
        ]);
        $oldPrefix = $category->prefix;
        $newPrefix = strtoupper($request->prefix);

        $old = $category->toArray();
        $category->update([
            'name' => $request->name,
            'prefix' => $newPrefix,
            'description' => $request->description,
        ]);
    ActivityLogger::log('updated', 'Category', $category->id, $old, $category->toArray(), 'Edited category ' . $category->name . ' [' . $old['prefix'] . '→' . $category->prefix . ']');

        $updatedCount = 0;
        // If user opted in and prefix actually changed
        if ($request->has('update_skus') && $oldPrefix !== $newPrefix) {
            $products = $category->products()->get();
            foreach ($products as $product) {
                $sku = $product->sku;
                // Match old prefix + numeric suffix only
                if (str_starts_with($sku, $oldPrefix)) {
                    $numeric = substr($sku, strlen($oldPrefix));
                    if ($numeric !== '' && preg_match('/^[0-9]+$/', $numeric)) {
                        $newSku = $newPrefix . $numeric;
                        // Skip if collision with existing SKU in any product
                        $exists = \App\Models\Product::where('sku', $newSku)->where('id', '!=', $product->id)->exists();
                        if ($exists) {
                            continue; // leave original sku to avoid conflict
                        }
                        $product->sku = $newSku;
                        $product->save();
                        $updatedCount++;
                    }
                }
            }
        }

        $message = 'Category updated successfully.';
        if ($updatedCount > 0) {
            $message .= " Updated $updatedCount product code" . ($updatedCount === 1 ? '' : 's') . '.';
        } elseif ($request->has('update_skus') && $oldPrefix !== $newPrefix) {
            $message .= ' No product codes required updating (possible conflicts or no matching codes).';
        }

        return redirect()->route('categories.index')
            ->with('success', $message);
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

        $old = $category->toArray();
        $category->delete();

        ActivityLogger::log('deleted', 'Category', $category->id, $old, null, 'Deleted category ' . ($old['name'] ?? 'Unknown') . ' [' . ($old['prefix'] ?? '') . ']');

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
