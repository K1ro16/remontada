<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Inventory;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $business = auth()->user()->currentBusiness;
        $products = $business->products()
            ->with('category')
            ->orderBy('category_id', 'asc')
            ->orderBy('name', 'asc')
            ->get();
        
        return view('products.index', compact('products'));
    }

    public function create()
    {
        $business = auth()->user()->currentBusiness;
        $categories = $business->categories;
        
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products',
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'cost' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'is_inactive' => 'nullable|boolean',
            'inactive_reason' => 'required_if:is_inactive,1|nullable|string',
        ]);

        $business = auth()->user()->currentBusiness;

        $product = Product::create([
            'business_id' => $business->id,
            'name' => $request->name,
            'sku' => strtoupper($request->sku),
            'category_id' => $request->category_id,
            'description' => $request->description,
            'price' => $request->price,
            'tax_percentage' => $request->tax_percentage ?? 0,
            'cost' => $request->cost,
            'stock' => $request->stock,
            'min_stock' => $request->min_stock,
            'is_active' => !$request->has('is_inactive'),
            'inactive_reason' => $request->is_inactive ? $request->inactive_reason : null,
        ]);

        // Log initial stock
        Inventory::create([
            'business_id' => $business->id,
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'type' => 'in',
            'quantity' => $request->stock,
            'stock_before' => 0,
            'stock_after' => $request->stock,
            'notes' => 'Initial stock',
        ]);

        return redirect()->route('products.index')->with('success', 'Product created successfully!');
    }

    public function edit(Product $product)
    {
        $business = auth()->user()->currentBusiness;
        $categories = $business->categories;
        
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'category_id' => 'exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'cost' => 'nullable|numeric|min:0',
            'min_stock' => 'required|integer|min:0',
            'is_inactive' => 'nullable|boolean',
            'inactive_reason' => 'required_if:is_inactive,1|nullable|string',
        ]);

        $product->update([
            'name' => $request->name,
            'sku' => $request->sku,
            'category_id' => $request->category_id,
            'description' => $request->description,
            'price' => $request->price,
            'tax_percentage' => $request->tax_percentage ?? 0,
            'cost' => $request->cost,
            'min_stock' => $request->min_stock,
            'is_active' => !$request->has('is_inactive'),
            'inactive_reason' => $request->is_inactive ? $request->inactive_reason : null,
        ]);

        return redirect()->route('products.index')->with('success', 'Product updated successfully!');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted successfully!');
    }

    public function adjustStock(Request $request, Product $product)
    {
        $request->validate([
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $business = auth()->user()->currentBusiness;
        $stockBefore = $product->stock;

        if ($request->type === 'in') {
            $product->stock += $request->quantity;
        } elseif ($request->type === 'out') {
            $product->stock -= $request->quantity;
        } else {
            $product->stock = $request->quantity;
        }

        $product->save();

        Inventory::create([
            'business_id' => $business->id,
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'type' => $request->type,
            'quantity' => $request->quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $product->stock,
            'notes' => $request->notes,
        ]);

        return redirect()->route('products.index')->with('success', 'Stock adjusted successfully!');
    }

    public function getNextSKU($prefix)
    {
        $business = auth()->user()->currentBusiness;
        
        // Find the highest number for this prefix in this business
        $lastProduct = Product::where('business_id', $business->id)
            ->where('sku', 'LIKE', $prefix . '%')
            ->orderByRaw('CAST(SUBSTRING(sku, ' . (strlen($prefix) + 1) . ') AS UNSIGNED) DESC')
            ->first();
        
        if ($lastProduct) {
            // Extract the number part and increment
            $number = (int) substr($lastProduct->sku, strlen($prefix));
            $nextNumber = $number + 1;
        } else {
            $nextNumber = 1;
        }
        
        return response()->json([
            'sku' => $prefix . $nextNumber
        ]);
    }
}
