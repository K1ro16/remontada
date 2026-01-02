<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\ActivityLogger;

class SaleController extends Controller
{
    public function index()
    {
        $business = auth()->user()->currentBusiness;
        $sales = $business->sales()
            ->with(['user', 'items.product'])
            ->orderBy('sale_date', 'desc')
            ->paginate(20);
        
        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $business = auth()->user()->currentBusiness;
        $products = $business->products()->where('is_active', true)->get();
        
        return view('sales.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sale_date' => 'required|date',
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

            // Generate internal reference (kept for DB uniqueness; not shown in UI)
            $invoiceNumber = 'INV-' . $business->id . '-' . now()->format('Ymd') . '-' . str_pad(Sale::where('business_id', $business->id)->whereDate('created_at', now()->toDateString())->count() + 1, 4, '0', STR_PAD_LEFT);

            // Create sale
            $sale = Sale::create([
                'business_id' => $business->id,
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
                    'notes' => 'Sale',
                ]);
            }

            DB::commit();

            ActivityLogger::log('created', 'Sale', $sale->id, null, [
                'subtotal' => $sale->subtotal,
                'tax' => $sale->tax,
                'discount' => $sale->discount,
                'total' => $sale->total,
                'sale_date' => $sale->sale_date,
            ], 'Created sale');

            return redirect()->route('sales.show', $sale)->with('success', 'Sale recorded successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to record sale: ' . $e->getMessage()])->withInput();
        }
    }

    public function show(Sale $sale)
    {
        $sale->load(['user', 'items.product']);
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
                    'notes' => 'Sale cancelled',
                ]);
            }

            $old = $sale->toArray();
            $sale->delete();
            DB::commit();

            ActivityLogger::log('deleted', 'Sale', $sale->id, $old, null, 'Deleted sale');

            return redirect()->route('sales.index')->with('success', 'Sale deleted and stock restored.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete sale: ' . $e->getMessage()]);
        }
    }

    public function export(Request $request)
    {
        $business = auth()->user()->currentBusiness;
        // Prefill with current month
        $defaultFrom = now()->startOfMonth()->format('Y-m-d');
        $defaultTo = now()->endOfMonth()->format('Y-m-d');

        $fromDate = $request->input('from_date', $defaultFrom);
        $toDate = $request->input('to_date', $defaultTo);

        $start = \Carbon\Carbon::parse($fromDate)->startOfDay();
        $end = \Carbon\Carbon::parse($toDate)->endOfDay();

        $sales = $business->sales()
            ->with(['user'])
            ->withCount('items')
            ->whereBetween('sale_date', [$start, $end])
            ->orderBy('sale_date', 'desc')
            ->paginate(25)
            ->withQueryString();

        return view('sales.export', compact('fromDate','toDate','sales'));
    }

    public function exportSalesCsv(Request $request)
    {
        $business = auth()->user()->currentBusiness;
        $fromDate = $request->input('from_date', now()->subMonths(1)->format('Y-m-d'));
        $toDate = $request->input('to_date', now()->format('Y-m-d'));
        $minTotal = $request->input('min_total');
        $maxTotal = $request->input('max_total');

        $start = \Carbon\Carbon::parse($fromDate)->startOfDay();
        $end = \Carbon\Carbon::parse($toDate)->endOfDay();

        $query = $business->sales()
            ->with('user')
            ->whereBetween('sale_date', [$start, $end]);

        if ($minTotal !== null && $minTotal !== '') { $query->where('total', '>=', (float)$minTotal); }
        if ($maxTotal !== null && $maxTotal !== '') { $query->where('total', '<=', (float)$maxTotal); }

        $rows = $query->orderBy('sale_date')->get([
            'id','invoice_number','sale_date','subtotal','tax','discount','total','status','notes','user_id'
        ]);

        $lines = [];
        $lines[] = 'Sale ID,Invoice Number,Sale Date,Subtotal,Tax,Discount,Total,Status,Notes,User';
        foreach ($rows as $s) {
            $line = [
                $s->id,
                $s->invoice_number,
                optional($s->sale_date)->format('Y-m-d'),
                number_format((float)$s->subtotal, 2, '.', ''),
                number_format((float)$s->tax, 2, '.', ''),
                number_format((float)$s->discount, 2, '.', ''),
                number_format((float)$s->total, 2, '.', ''),
                $s->status,
                str_replace(["\r","\n",',' ], ' ', (string)($s->notes ?? '')),
                optional($s->user)->name,
            ];
            $lines[] = implode(',', array_map(function($v){ return '"'.str_replace('"','""',$v).'"'; }, $line));
        }

        $csv = implode("\n", $lines) . "\n";
        $filename = 'sales_' . now()->format('Ymd_His') . '.csv';
        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }

    public function exportSaleItemsCsv(Request $request)
    {
        $business = auth()->user()->currentBusiness;
        $fromDate = $request->input('from_date', now()->subMonths(1)->format('Y-m-d'));
        $toDate = $request->input('to_date', now()->format('Y-m-d'));
        $minTotal = $request->input('min_total');
        $maxTotal = $request->input('max_total');

        $start = \Carbon\Carbon::parse($fromDate)->startOfDay();
        $end = \Carbon\Carbon::parse($toDate)->endOfDay();

        $query = \App\Models\SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.business_id', $business->id)
            ->whereBetween('sales.sale_date', [$start, $end]);

        if ($minTotal !== null && $minTotal !== '') { $query->where('sales.total', '>=', (float)$minTotal); }
        if ($maxTotal !== null && $maxTotal !== '') { $query->where('sales.total', '<=', (float)$maxTotal); }

        $rows = $query
            ->orderBy('sales.sale_date')
            ->get([
                'sales.id as sale_id',
                'sales.invoice_number',
                'sales.sale_date',
                'sales.total as sale_total',
                'products.id as product_id',
                'products.name as product_name',
                'sale_items.quantity',
                'sale_items.price',
                'sale_items.subtotal',
            ]);

        $lines = [];
        $lines[] = 'Sale ID,Invoice Number,Sale Date,Sale Total,Product ID,Product Name,Quantity,Price,Item Subtotal';
        foreach ($rows as $r) {
            $line = [
                $r->sale_id,
                $r->invoice_number,
                optional($r->sale_date)->format('Y-m-d'),
                number_format((float)$r->sale_total, 2, '.', ''),
                $r->product_id,
                $r->product_name,
                $r->quantity,
                number_format((float)$r->price, 2, '.', ''),
                number_format((float)$r->subtotal, 2, '.', ''),
            ];
            $lines[] = implode(',', array_map(function($v){ return '"'.str_replace('"','""',$v).'"'; }, $line));
        }

        $csv = implode("\n", $lines) . "\n";
        $filename = 'sale_items_' . now()->format('Ymd_His') . '.csv';
        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }

    public function exportExcel(Request $request)
    {
        $business = auth()->user()->currentBusiness;
        $fromDate = $request->input('from_date', now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', now()->endOfMonth()->format('Y-m-d'));

        $start = \Carbon\Carbon::parse($fromDate)->startOfDay();
        $end = \Carbon\Carbon::parse($toDate)->endOfDay();

        $rows = $business->sales()
            ->with('user')
            ->withCount('items')
            ->whereBetween('sale_date', [$start, $end])
            ->orderBy('sale_date')
            ->get([
                'id','invoice_number','sale_date','subtotal','tax','discount','total','status','notes','user_id'
            ]);

        // Build simple HTML table that Excel can open
        $html = '<html><head><meta charset="UTF-8"></head><body>';
        $html .= '<table border="1">';
        $html .= '<thead><tr>'
            . '<th>Sale ID</th><th>Invoice Number</th><th>Sale Date</th><th>User</th><th>Items</th>'
            . '<th>Subtotal</th><th>Tax</th><th>Discount</th><th>Total</th><th>Status</th><th>Notes</th>'
            . '</tr></thead><tbody>';
        foreach ($rows as $s) {
            $html .= '<tr>'
                . '<td>' . e($s->id) . '</td>'
                . '<td>' . e($s->invoice_number) . '</td>'
                . '<td>' . e(optional($s->sale_date)->format('Y-m-d')) . '</td>'
                . '<td>' . e(optional($s->user)->name) . '</td>'
                . '<td>' . e($s->items_count) . '</td>'
                . '<td>' . number_format((float)$s->subtotal, 2, '.', '') . '</td>'
                . '<td>' . number_format((float)$s->tax, 2, '.', '') . '</td>'
                . '<td>' . number_format((float)$s->discount, 2, '.', '') . '</td>'
                . '<td>' . number_format((float)$s->total, 2, '.', '') . '</td>'
                . '<td>' . e($s->status) . '</td>'
                . '<td>' . e((string)($s->notes ?? '')) . '</td>'
                . '</tr>';
        }
        $html .= '</tbody></table></body></html>';

        $filename = 'sales_' . now()->format('Ymd_His') . '.xls';
        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }
}
