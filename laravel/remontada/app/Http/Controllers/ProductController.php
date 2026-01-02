<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Inventory;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'image' => 'nullable|image|max:2048',
            'price' => 'required|numeric|min:0',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'cost' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'is_inactive' => 'nullable|boolean',
            'inactive_reason' => 'required_if:is_inactive,1|nullable|string',
        ]);

        $business = auth()->user()->currentBusiness;

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product = Product::create([
            'business_id' => $business->id,
            'name' => $request->name,
            'sku' => strtoupper($request->sku),
            'category_id' => $request->category_id,
            'description' => $request->description,
            'image_path' => $imagePath,
            'price' => $request->price,
            'tax_percentage' => $request->tax_percentage ?? 0,
            'cost' => $request->cost,
            'stock' => $request->stock,
            'min_stock' => $request->min_stock,
            'is_active' => !$request->has('is_inactive'),
            'inactive_reason' => $request->is_inactive ? $request->inactive_reason : null,
        ]);

        ActivityLogger::log(
            'created',
            'Product',
            $product->id,
            null,
            $product->toArray(),
            'Created product ' . $product->name . ' #' . $product->sku
        );

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
            'image' => 'nullable|image|max:2048',
            'price' => 'required|numeric|min:0',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'cost' => 'nullable|numeric|min:0',
            'min_stock' => 'required|integer|min:0',
            'is_inactive' => 'nullable|boolean',
            'inactive_reason' => 'required_if:is_inactive,1|nullable|string',
        ]);

        $old = $product->toArray();
        $data = [
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
        ];

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        ActivityLogger::log(
            'updated',
            'Product',
            $product->id,
            $old,
            $product->toArray(),
            'Edited product ' . $product->name . ' #' . $product->sku
        );

        return redirect()->route('products.index')->with('success', 'Product updated successfully!');
    }

    public function destroy(Product $product)
    {
        $old = $product->toArray();
        $product->delete();
        ActivityLogger::log(
            'deleted',
            'Product',
            $product->id,
            $old,
            null,
            'Deleted product ' . ($old['name'] ?? 'Unknown') . ' #' . ($old['sku'] ?? '')
        );
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

        $inventory = Inventory::create([
            'business_id' => $business->id,
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'type' => $request->type,
            'quantity' => $request->quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $product->stock,
            'notes' => $request->notes,
        ]);

        // Activity log for inventory changes
        $verb = $request->type === 'in' ? 'Added' : ($request->type === 'out' ? 'Removed' : 'Adjusted');
        $delta = $request->type === 'in' ? '+' . $request->quantity : ($request->type === 'out' ? '-' . $request->quantity : (string)$request->quantity);
        $message = $verb . ' stock ' . $product->name . ' #' . $product->sku . ' ' . $delta . ' (stock: ' . $stockBefore . ' → ' . $product->stock . ')';
        ActivityLogger::log(
            'inventory-' . $request->type,
            'Inventory',
            $inventory->id,
            ['stock_before' => $stockBefore],
            [
                'stock_after' => $product->stock,
                'quantity' => (int)$request->quantity,
                'type' => $request->type,
                'product_id' => $product->id,
                'product_sku' => $product->sku,
            ],
            $message
        );

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
