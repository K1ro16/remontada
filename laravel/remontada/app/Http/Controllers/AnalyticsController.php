<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Category;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $business = $user->currentBusiness;

        $groupBy = $request->input('group_by', 'daily');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $quickRange = (int)($request->input('quick_range') ?: 0);
        $quickMonthRange = (int)($request->input('quick_month_range') ?: 0);
        $quarter = $request->input('quarter');
        $quarterYear = $request->input('quarter_year');
        $uiFromDate = $fromDate;
        $uiToDate = $toDate;

        $labels = [];
        $values = [];
        $marginLabels = [];
        $marginTotals = [];
        $marginRates = [];
        $monthlyLabelsTotals = [];
        $monthlyTotals = [];
        $monthlyLabelsGrowth = [];
        $monthlyGrowth = [];

        // Defaults for monthly filter values to avoid undefined variables in the view
        $totalsFrom = null;
        $totalsTo = null;
        $growthFrom = null;
        $growthTo = null;
        $marginFrom = null;
        $marginTo = null;

        if ($groupBy === 'monthly') {

            // Base range from top-level monthly filters (From Month/To Month)
            if (!$fromDate || !$toDate) {
                if (in_array($quickMonthRange, [3,6,12], true)) {
                    $baseTo = now()->format('Y-m');
                    $baseFrom = now()->copy()->subMonths($quickMonthRange - 1)->format('Y-m');
                    // Clear UI inputs so placeholders show month format nicely
                    $uiFromDate = '';
                    $uiToDate = '';
                } else {
                    $baseFrom = now()->subMonths(11)->format('Y-m');
                    $baseTo   = now()->format('Y-m');
                }
            } else {
                $baseFrom = $fromDate;
                $baseTo   = $toDate;
            }

            // Per-chart overrides (if the dedicated form fields are provided)
            $totalsFrom = $request->input('totals_from') ?: $baseFrom;
            $totalsTo   = $request->input('totals_to')   ?: $baseTo;
            $growthFrom = $request->input('growth_from') ?: $baseFrom;
            $growthTo   = $request->input('growth_to')   ?: $baseTo;
            $marginFrom = $request->input('margin_from') ?: $baseFrom;
            $marginTo   = $request->input('margin_to')   ?: $baseTo;

            /** TOTAL SALES */
            $tStart = Carbon::parse($totalsFrom . '-01')->startOfMonth();
            $tEnd   = Carbon::parse($totalsTo . '-01')->endOfMonth();

            $rawTotals = Sale::where('business_id', $business->id)
                ->whereBetween('sale_date', [$tStart, $tEnd])
                ->selectRaw('DATE_FORMAT(sale_date, "%Y-%m") as m, SUM(total) as t')
                ->groupBy('m')
                ->pluck('t', 'm')
                ->toArray();

            $cursor = $tStart->copy();
            while ($cursor->lte($tEnd)) {
                $key = $cursor->format('Y-m');
                $monthlyLabelsTotals[] = $cursor->format('M Y');
                $monthlyTotals[] = (float)($rawTotals[$key] ?? 0);
                $cursor->addMonth();
            }

            /** MARGIN */
            $mStart = Carbon::parse($marginFrom . '-01')->startOfMonth();
            $mEnd   = Carbon::parse($marginTo . '-01')->endOfMonth();

            $rawMargin = SaleItem::query()
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->where('sales.business_id', $business->id)
                ->whereBetween('sales.sale_date', [$mStart, $mEnd])
                ->selectRaw('
                    DATE_FORMAT(sales.sale_date, "%Y-%m") as m,
                    SUM(sale_items.subtotal) as revenue,
                    SUM(sale_items.quantity * COALESCE(products.cost,0)) as cogs
                ')
                ->groupBy('m')
                ->get()
                ->keyBy('m');

            $cursor = $mStart->copy();
            while ($cursor->lte($mEnd)) {
                $key = $cursor->format('Y-m');
                $rev = (float)($rawMargin[$key]->revenue ?? 0);
                $cogs = (float)($rawMargin[$key]->cogs ?? 0);
                $margin = $rev - $cogs;

                $marginLabels[] = $cursor->format('M Y');
                $marginTotals[] = $margin;
                $marginRates[] = $rev > 0 ? round(($margin / $rev) * 100, 2) : 0;

                $cursor->addMonth();
            }

            /** GROWTH */
            $gStart = Carbon::parse($growthFrom . '-01')->startOfMonth();
            $gEnd   = Carbon::parse($growthTo . '-01')->endOfMonth();

            $rawGrowth = Sale::where('business_id', $business->id)
                ->whereBetween('sale_date', [$gStart, $gEnd])
                ->selectRaw('DATE_FORMAT(sale_date, "%Y-%m") as m, SUM(total) as t')
                ->groupBy('m')
                ->pluck('t', 'm')
                ->toArray();

            $totals = [];
            $cursor = $gStart->copy();
            while ($cursor->lte($gEnd)) {
                $key = $cursor->format('Y-m');
                $monthlyLabelsGrowth[] = $cursor->format('M Y');
                $totals[] = (float)($rawGrowth[$key] ?? 0);
                $cursor->addMonth();
            }

            foreach ($totals as $i => $curr) {
                if ($i === 0) {
                    $monthlyGrowth[] = 0;
                } else {
                    $prev = $totals[$i - 1];
                    $monthlyGrowth[] = $prev > 0 ? round((($curr - $prev) / $prev) * 100, 2) : 0;
                }
            }

            // Top products use the base monthly range
            $tpStart = Carbon::parse($baseFrom . '-01')->startOfMonth();
            $tpEnd   = Carbon::parse($baseTo   . '-01')->endOfMonth();

        } else {

            if (!$fromDate || !$toDate) {
                if (in_array($quickRange, [7,15,30], true)) {
                    $toDate = now()->format('Y-m-d');
                    $fromDate = now()->copy()->subDays($quickRange - 1)->format('Y-m-d');
                    $uiFromDate = '';
                    $uiToDate = '';
                } else {
                    $fromDate = ($fromDate ?: now()->subDays(6)->format('Y-m-d'));
                    $toDate = ($toDate ?: now()->format('Y-m-d'));
                }
            }

            $start = Carbon::parse($fromDate)->startOfDay();
            $end   = Carbon::parse($toDate)->endOfDay();

            $rawDaily = Sale::where('business_id', $business->id)
                ->whereBetween('sale_date', [$start, $end])
                ->selectRaw('DATE(sale_date) as d, SUM(total) as t')
                ->groupBy('d')
                ->pluck('t', 'd')
                ->toArray();

            $cursor = $start->copy();
            while ($cursor->lte($end)) {
                $key = $cursor->format('Y-m-d');
                $labels[] = $cursor->format('d M');
                $values[] = (float)($rawDaily[$key] ?? 0);
                $cursor->addDay();
            }

            // Daily gross margin
            $rawDailyMargin = SaleItem::query()
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->where('sales.business_id', $business->id)
                ->whereBetween('sales.sale_date', [$start, $end])
                ->selectRaw('DATE(sales.sale_date) as d, SUM(sale_items.subtotal) as revenue, SUM(sale_items.quantity * COALESCE(products.cost, 0)) as cogs')
                ->groupBy('d')
                ->get()
                ->keyBy('d');

            $cursor = $start->copy();
            while ($cursor->lte($end)) {
                $key = $cursor->format('Y-m-d');
                $marginLabels[] = $cursor->format('d M');
                $rev = (float)($rawDailyMargin[$key]->revenue ?? 0);
                $cogs = (float)($rawDailyMargin[$key]->cogs ?? 0);
                $margin = $rev - $cogs;
                $marginTotals[] = $margin;
                $marginRates[] = $rev > 0 ? round(($margin / $rev) * 100, 2) : 0;
                $cursor->addDay();
            }

            $tpStart = $start;
            $tpEnd   = $end;
        }

        // Top products aggregated for the selected range (common to both modes)
        $topProductLabels = [];
        $topProductValues = [];
        $topProductQuantities = [];
        $topProductMargins = [];
        $topProductMarginRates = [];

        $topProducts = SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.business_id', $business->id)
            ->whereBetween('sales.sale_date', [$tpStart, $tpEnd])
            ->groupBy('sale_items.product_id', 'products.name')
            ->selectRaw('products.name as name, SUM(sale_items.subtotal) as revenue, SUM(sale_items.quantity) as qty, SUM(sale_items.quantity * COALESCE(products.cost, 0)) as cogs')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        foreach ($topProducts as $row) {
            $topProductLabels[] = $row->name;
            $topProductValues[] = (float)($row->revenue ?? 0);
            $topProductQuantities[] = (int)($row->qty ?? 0);
            $cogs = (float)($row->cogs ?? 0);
            $margin = (float)($row->revenue ?? 0) - $cogs;
            $rate = ($row->revenue ?? 0) > 0 ? round(($margin / (float)$row->revenue) * 100, 2) : 0;
            $topProductMargins[] = $margin;
            $topProductMarginRates[] = $rate;
        }

        return view('analytics.index', [
            'groupBy' => $groupBy,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'uiFromDate' => $uiFromDate,
            'uiToDate' => $uiToDate,
            'quickRange' => $quickRange ? (string)$quickRange : '',
            'quickMonthRange' => $quickMonthRange ? (string)$quickMonthRange : '',
            'quarter' => $quarter,
            'quarterYear' => $quarterYear,
            'labels' => $labels,
            'values' => $values,
            'marginLabels' => $marginLabels,
            'marginTotals' => $marginTotals,
            'marginRates' => $marginRates,
            'monthlyLabelsTotals' => $monthlyLabelsTotals,
            'monthlyTotals' => $monthlyTotals,
            'monthlyLabelsGrowth' => $monthlyLabelsGrowth,
            'monthlyGrowth' => $monthlyGrowth,
            'totalsFrom' => $totalsFrom,
            'totalsTo' => $totalsTo,
            'growthFrom' => $growthFrom,
            'growthTo' => $growthTo,
            'marginFrom' => $marginFrom,
            'marginTo' => $marginTo,
            'topProductLabels' => $topProductLabels,
            'topProductValues' => $topProductValues,
            'topProductQuantities' => $topProductQuantities,
            'topProductMargins' => $topProductMargins,
            'topProductMarginRates' => $topProductMarginRates,
        ]);
    }

    public function products(Request $request)
    {
        $user = Auth::user();
        $business = $user->currentBusiness;

        $groupBy = $request->input('group_by', 'daily');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $categoryId = $request->input('category_id');
        $uiFromDate = $fromDate;
        $uiToDate = $toDate;

        if ($groupBy === 'monthly') {
            $start = Carbon::parse(($fromDate ?: now()->subMonths(11)->format('Y-m')).'-01')->startOfMonth();
            $end = Carbon::parse(($toDate ?: now()->format('Y-m')).'-01')->endOfMonth();
        } else {
            $start = Carbon::parse(($fromDate ?: now()->subDays(6)->format('Y-m-d')))->startOfDay();
            $end = Carbon::parse(($toDate ?: now()->format('Y-m-d')))->endOfDay();
        }

        $query = SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.business_id', $business->id)
            ->whereBetween('sales.sale_date', [$start, $end])
            ->when($categoryId, function($q) use ($categoryId) { return $q->where('products.category_id', $categoryId); })
            ->groupBy('sale_items.product_id', 'products.name')
            ->selectRaw('products.name as name, SUM(sale_items.subtotal) as revenue, SUM(sale_items.quantity) as qty, SUM(sale_items.quantity * COALESCE(products.cost, 0)) as cogs')
            ->orderByDesc('revenue')
            ->get();

        $labels = [];
        $values = [];
        $quantities = [];
        $margins = [];
        $marginRates = [];
        foreach ($query as $row) {
            $labels[] = $row->name;
            $values[] = (float)$row->revenue;
            $quantities[] = (int)($row->qty ?? 0);
            $cogs = (float)($row->cogs ?? 0);
            $margin = (float)$row->revenue - $cogs;
            $rate = $row->revenue > 0 ? round(($margin / (float)$row->revenue) * 100, 2) : 0.0;
            $margins[] = $margin;
            $marginRates[] = $rate;
        }

        $categories = Category::where('business_id', $business->id)->orderBy('name')->get(['id','name']);

        return view('analytics.products', [
            'groupBy' => $groupBy,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'uiFromDate' => $uiFromDate,
            'uiToDate' => $uiToDate,
            'categoryId' => $categoryId,
            'categories' => $categories,
            'quickRange' => $request->input('quick_range'),
            'quickMonthRange' => $request->input('quick_month_range'),
            'quarter' => $request->input('quarter'),
            'quarterYear' => $request->input('quarter_year'),
            'productLabels' => $labels,
            'productValues' => $values,
            'productQuantities' => $quantities,
            'productMargins' => $margins,
            'productMarginRates' => $marginRates,
        ]);
    }

    public function kpi(Request $request)
    {
        $user = Auth::user();
        $business = $user->currentBusiness;

        $baseMonth = $request->input('base_month', now()->format('Y-m'));
        $compareMonth = $request->input('compare_month')
            ?: Carbon::parse($baseMonth . '-01')->subMonth()->format('Y-m');

        // Thresholds persisted in DB (fallback to session/defaults)
        $session = $request->session();
        $growthWarn = (float) ($business->kpi_growth_warn ?? 10.0);
        $growthCrit = (float) ($business->kpi_growth_crit ?? -10.0);
        $marginWarn = (float) ($business->kpi_margin_warn ?? 30.0);
        $marginCrit = (float) ($business->kpi_margin_crit ?? 10.0);

        // Allow overrides via request, persist to DB and session
        $inputs = [
            'growth_warn' => 'kpi_growth_warn',
            'growth_crit' => 'kpi_growth_crit',
            'margin_warn' => 'kpi_margin_warn',
            'margin_crit' => 'kpi_margin_crit',
        ];
        $updated = false;
        foreach ($inputs as $reqKey => $col) {
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

        // Current month range
        $start = Carbon::parse($baseMonth . '-01')->startOfMonth();
        $end   = Carbon::parse($baseMonth . '-01')->endOfMonth();

        // Compare month range
        $cmpStart = Carbon::parse($compareMonth . '-01')->startOfMonth();
        $cmpEnd   = Carbon::parse($compareMonth . '-01')->endOfMonth();

        // Sales totals for current and compare month
        $currSales = (float) Sale::where('business_id', $business->id)
            ->whereBetween('sale_date', [$start, $end])
            ->sum('total');

        $prevSales = (float) Sale::where('business_id', $business->id)
            ->whereBetween('sale_date', [$cmpStart, $cmpEnd])
            ->sum('total');

        // Growth (%), fallback to 0 when compare month has no sales
        $salesGrowth = $prevSales > 0
            ? round((($currSales - $prevSales) / $prevSales) * 100, 2)
            : 0.0;

        // Status coloring based on thresholds
        $salesGrowthStatus = $salesGrowth >= $growthWarn ? 'green' : ($salesGrowth <= $growthCrit ? 'red' : 'yellow');

        // Average daily sales for the base month
        $dailySales = Sale::where('business_id', $business->id)
            ->whereBetween('sale_date', [$start, $end])
            ->selectRaw('DATE(sale_date) as d, SUM(total) as t')
            ->groupBy('d')
            ->pluck('t', 'd')
            ->toArray();

        $activeDays = 0;
        foreach ($dailySales as $total) { if ((float)$total > 0) $activeDays++; }
        $avgDailySales = $activeDays > 0 ? (float) round($currSales / $activeDays, 2) : 0.0;

        // Margin rate for base month
        $marginAgg = SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.business_id', $business->id)
            ->whereBetween('sales.sale_date', [$start, $end])
            ->selectRaw('SUM(sale_items.subtotal) as revenue, SUM(sale_items.quantity * COALESCE(products.cost,0)) as cogs')
            ->first();

        $rev = (float) ($marginAgg->revenue ?? 0);
        $cogs = (float) ($marginAgg->cogs ?? 0);
        $margin = $rev - $cogs;
        $avgMarginRate = $rev > 0 ? round(($margin / $rev) * 100, 2) : 0.0;
        $marginStatus = $avgMarginRate >= $marginWarn ? 'green' : ($avgMarginRate < $marginCrit ? 'red' : 'yellow');

        // Low margin products (rate < marginCrit) for base month
        $lowMarginProducts = SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.business_id', $business->id)
            ->whereBetween('sales.sale_date', [$start, $end])
            ->groupBy('products.id', 'products.name')
            ->selectRaw('products.id as id, products.name as name, SUM(sale_items.subtotal) as revenue, SUM(sale_items.quantity * COALESCE(products.cost,0)) as cogs')
            ->get()
            ->map(function($row){
                $rev = (float)($row->revenue ?? 0);
                $cogs = (float)($row->cogs ?? 0);
                $rate = $rev > 0 ? round((($rev - $cogs) / $rev) * 100, 2) : 0.0;
                return [ 'id' => (int)$row->id, 'name' => (string)$row->name, 'rate' => $rate ];
            })
            ->filter(function($p) use ($marginCrit){ return $p['rate'] < $marginCrit; })
            ->sortBy('rate')
            ->values()
            ->all();

        // Category sales distribution for base month
        $categoryKpis = SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('sales.business_id', $business->id)
            ->whereBetween('sales.sale_date', [$start, $end])
            ->groupBy('categories.id', 'categories.name')
            ->selectRaw('categories.id as id, categories.name as name, SUM(sale_items.subtotal) as revenue')
            ->orderByDesc('revenue')
            ->get()
            ->map(function($row){ return [ 'id' => (int)$row->id, 'name' => (string)$row->name, 'revenue' => (float)$row->revenue ]; })
            ->all();

        return view('analytics.kpi', [
            'baseMonth' => $baseMonth,
            'compareMonth' => $compareMonth,
            'growthWarn' => $growthWarn,
            'growthCrit' => $growthCrit,
            'salesGrowthStatus' => $salesGrowthStatus,
            'salesGrowth' => $salesGrowth,
            'currSales' => $currSales,
            'prevSales' => $prevSales,
            'avgDailySales' => $avgDailySales,
            'activeDays' => $activeDays,
            'marginWarn' => $marginWarn,
            'marginCrit' => $marginCrit,
            'marginStatus' => $marginStatus,
            'avgMarginRate' => $avgMarginRate,
            'lowMarginProducts' => $lowMarginProducts,
            'categoryKpis' => $categoryKpis,
        ]);
    }

    public function kpiCategoryItems(Request $request)
    {
        $user = Auth::user();
        $business = $user->currentBusiness;

        $categoryId = (int) $request->input('category_id');
        $baseMonth  = $request->input('base_month', now()->format('Y-m'));

        $start = Carbon::parse($baseMonth . '-01')->startOfMonth();
        $end   = Carbon::parse($baseMonth . '-01')->endOfMonth();

        $items = SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.business_id', $business->id)
            ->whereBetween('sales.sale_date', [$start, $end])
            ->where('products.category_id', $categoryId)
            ->groupBy('products.id', 'products.name')
            ->selectRaw('
                products.id as id,
                products.name,
                SUM(sale_items.subtotal) as revenue,
                SUM(sale_items.quantity) as qty
            ')
            ->orderByDesc('revenue')
            ->get();

        return response()->json([
            'items' => $items,
            'total' => $items->sum('revenue'),
        ]);
    }
}
