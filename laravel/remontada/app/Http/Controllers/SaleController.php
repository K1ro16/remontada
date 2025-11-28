<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index()
    {
        $business = auth()->user()->currentBusiness;
        $sales = $business->sales()
            ->with(['customer', 'user', 'items.product'])
            ->orderBy('sale_date', 'desc')
            ->paginate(20);
        
        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $business = auth()->user()->currentBusiness;
        $products = $business->products()->where('is_active', true)->get();
        $customers = $business->customers()->orderBy('name')->get();
        
        return view('sales.create', compact('products', 'customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sale_date' => 'required|date',
            'customer_id' => 'nullable|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $business = auth()->user()->currentBusiness;

        DB::beginTransaction();
        try {
            // Calculate totals
            $subtotal = 0;
            $totalTax = 0;
            $saleItems = [];

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                // Check stock
                if ($product->stock < $item['quantity']) {
                    return back()->withErrors(['items' => "Insufficient stock for {$product->name}. Available: {$product->stock}"])->withInput();
                }

                $itemSubtotal = $product->price * $item['quantity'];
                $itemTax = $itemSubtotal * ($product->tax_percentage / 100);
                
                $subtotal += $itemSubtotal;
                $totalTax += $itemTax;

                $saleItems[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $itemSubtotal,
                    'tax' => $itemTax,
                ];
            }

            $discount = $request->discount ?? 0;
            $total = $subtotal + $totalTax - $discount;

            // Generate invoice number
            $invoiceNumber = 'INV-' . $business->id . '-' . now()->format('Ymd') . '-' . str_pad(Sale::where('business_id', $business->id)->whereDate('created_at', now()->toDateString())->count() + 1, 4, '0', STR_PAD_LEFT);

            // Create sale
            $sale = Sale::create([
                'business_id' => $business->id,
                'customer_id' => $request->customer_id,
                'user_id' => auth()->id(),
                'invoice_number' => $invoiceNumber,
                'subtotal' => $subtotal,
                'tax' => $totalTax,
                'discount' => $discount,
                'total' => $total,
                'status' => 'completed',
                'notes' => $request->notes,
                'sale_date' => $request->sale_date,
            ]);

            // Create sale items and update stock
            foreach ($saleItems as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product']->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                ]);

                // Update product stock
                $stockBefore = $item['product']->stock;
                $item['product']->decrement('stock', $item['quantity']);

                // Log inventory
                Inventory::create([
                    'business_id' => $business->id,
                    'product_id' => $item['product']->id,
                    'user_id' => auth()->id(),
                    'type' => 'out',
                    'quantity' => $item['quantity'],
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockBefore - $item['quantity'],
                    'notes' => "Sale: {$invoiceNumber}",
                ]);
            }

            DB::commit();

            return redirect()->route('sales.show', $sale)->with('success', 'Sale recorded successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to record sale: ' . $e->getMessage()])->withInput();
        }
    }

    public function show(Sale $sale)
    {
        $sale->load(['customer', 'user', 'items.product']);
        return view('sales.show', compact('sale'));
    }

    public function destroy(Sale $sale)
    {
        if ($sale->business_id !== auth()->user()->current_business_id) {
            abort(403, 'Unauthorized action.');
        }

        DB::beginTransaction();
        try {
            // Restore stock for each item
            foreach ($sale->items as $item) {
                $product = $item->product;
                $stockBefore = $product->stock;
                $product->increment('stock', $item->quantity);

                // Log inventory restoration
                Inventory::create([
                    'business_id' => $sale->business_id,
                    'product_id' => $product->id,
                    'user_id' => auth()->id(),
                    'type' => 'in',
                    'quantity' => $item->quantity,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockBefore + $item->quantity,
                    'notes' => "Sale cancelled: {$sale->invoice_number}",
                ]);
            }

            $sale->delete();
            DB::commit();

            return redirect()->route('sales.index')->with('success', 'Sale deleted and stock restored.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete sale: ' . $e->getMessage()]);
        }
    }
}
