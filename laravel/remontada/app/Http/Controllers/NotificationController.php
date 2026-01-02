<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Notification;
use App\Models\Product;
use Carbon\Carbon;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $business = $user->currentBusiness;

        // Real-time: always evaluate for current month
        $baseMonth = now()->format('Y-m');
        $compareMonth = Carbon::parse($baseMonth . '-01')->subMonth()->format('Y-m');

        // Read thresholds from Business (fallback to session/defaults); allow overrides via request
        $session = $request->session();
        $growthWarn = (float) ($business->kpi_growth_warn ?? 10.0);
        $growthCrit = (float) ($business->kpi_growth_crit ?? -10.0);
        $marginWarn = (float) ($business->kpi_margin_warn ?? 30.0);
        $marginCrit = (float) ($business->kpi_margin_crit ?? 10.0);

        $overrideMap = [
            'growth_warn' => 'kpi_growth_warn',
            'growth_crit' => 'kpi_growth_crit',
            'margin_warn' => 'kpi_margin_warn',
            'margin_crit' => 'kpi_margin_crit',
        ];
        $updated = false;
        foreach ($overrideMap as $reqKey => $col) {
            if ($request->has($reqKey)) {
                $val = (float) $request->input($reqKey);
                $business->{$col} = $val;
                $session->put('kpi.' . $reqKey, $val);
                switch ($reqKey) {
                    case 'growth_warn': $growthWarn = $val; break;
                    case 'growth_crit': $growthCrit = $val; break;
                    case 'margin_warn': $marginWarn = $val; break;
                    case 'margin_crit': $marginCrit = $val; break;
                }
                $updated = true;
            }
        }
        if ($updated) { $business->save(); }

        $start = Carbon::parse($baseMonth . '-01')->startOfMonth();
        $end   = Carbon::parse($baseMonth . '-01')->endOfMonth();
        $cmpStart = Carbon::parse($compareMonth . '-01')->startOfMonth();
        $cmpEnd   = Carbon::parse($compareMonth . '-01')->endOfMonth();

        $currSales = (float) Sale::where('business_id', $business->id)
            ->whereBetween('sale_date', [$start, $end])
            ->sum('total');
        $prevSales = (float) Sale::where('business_id', $business->id)
            ->whereBetween('sale_date', [$cmpStart, $cmpEnd])
            ->sum('total');

        $salesGrowth = $prevSales > 0 ? round((($currSales - $prevSales) / $prevSales) * 100, 2) : 0.0;
        $salesGrowthStatus = $salesGrowth >= $growthWarn ? 'green' : ($salesGrowth <= $growthCrit ? 'red' : 'yellow');

        $marginAgg = SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.business_id', $business->id)
            ->whereBetween('sales.sale_date', [$start, $end])
            ->selectRaw('SUM(sale_items.subtotal) as revenue, SUM(sale_items.quantity * COALESCE(products.cost,0)) as cogs')
            ->first();
        $rev = (float) ($marginAgg->revenue ?? 0);
        $cogs = (float) ($marginAgg->cogs ?? 0);
        $avgMarginRate = $rev > 0 ? round((($rev - $cogs) / $rev) * 100, 2) : 0.0;
        $marginStatus = $avgMarginRate >= $marginWarn ? 'green' : ($avgMarginRate < $marginCrit ? 'red' : 'yellow');

        // Product low stock (real-time based on current stock and minimum)
        $lowStockProducts = Product::where('business_id', $business->id)
            ->whereNotNull('min_stock')
            ->where('min_stock', '>', 0)
            ->whereColumn('stock', '<=', 'min_stock')
            ->orderBy('name')
            ->get(['id','name','stock','min_stock']);

        // Build current alerts and persist unique records per month/type/severity
        $currentAlerts = [];
        if (in_array($salesGrowthStatus, ['yellow','red'])) {
            $severity = $salesGrowthStatus === 'red' ? 'critical' : 'warning';
            $threshold = $severity === 'critical' ? $growthCrit : $growthWarn;
            $msg = $severity === 'critical'
                ? "Sales Growth {$salesGrowth}% is at or below critical (≤ {$growthCrit}%)."
                : "Sales Growth {$salesGrowth}% is below target (Warn ≤ {$growthWarn}%).";
            Notification::firstOrCreate([
                'business_id' => $business->id,
                'month' => $baseMonth,
                'type' => 'sales_growth',
                'severity' => $severity,
            ], [
                'message' => $msg,
                'value' => $salesGrowth,
                'threshold' => $threshold,
            ]);
            $currentAlerts[] = [ 'type' => 'Sales Growth', 'severity' => $severity, 'message' => $msg ];
        }
        if (in_array($marginStatus, ['yellow','red'])) {
            $severity = $marginStatus === 'red' ? 'critical' : 'warning';
            $threshold = $severity === 'critical' ? $marginCrit : $marginWarn;
            $msg = $severity === 'critical'
                ? "Margin Rate {$avgMarginRate}% is below critical (< {$marginCrit}%)."
                : "Margin Rate {$avgMarginRate}% is below target (Warn ≤ {$marginWarn}%).";
            Notification::firstOrCreate([
                'business_id' => $business->id,
                'month' => $baseMonth,
                'type' => 'margin_rate',
                'severity' => $severity,
            ], [
                'message' => $msg,
                'value' => $avgMarginRate,
                'threshold' => $threshold,
            ]);
            $currentAlerts[] = [ 'type' => 'Margin Rate', 'severity' => $severity, 'message' => $msg ];
        }

        // Persist low stock notifications per product
        foreach ($lowStockProducts as $p) {
            $severity = ((int)$p->stock) <= 0 ? 'critical' : 'warning';
            $msg = $severity === 'critical'
                ? "Product {$p->name} out of stock (0 ≤ min {$p->min_stock})."
                : "Product {$p->name} low stock ({$p->stock} ≤ min {$p->min_stock}).";
            Notification::firstOrCreate([
                'business_id' => $business->id,
                'month' => $baseMonth,
                'type' => 'product_low_stock:' . $p->id,
                'severity' => $severity,
            ], [
                'message' => $msg,
                'value' => (float)$p->stock,
                'threshold' => (float)$p->min_stock,
            ]);
            $currentAlerts[] = [ 'type' => 'Product', 'severity' => $severity, 'message' => $msg ];
        }

        $history = Notification::where('business_id', $business->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('notifications.index', [
            'currentAlerts' => $currentAlerts,
            'history' => $history,
        ]);
    }
}
