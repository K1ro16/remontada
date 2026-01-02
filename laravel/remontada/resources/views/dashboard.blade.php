@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="card">
    <h2>Welcome, {{ Auth::user()->name }}!</h2>
</div>

@php
    $business = Auth::user()->currentBusiness;
    $totalSales = \App\Models\Sale::where('business_id', $business->id)->sum('total');
    $productCount = \App\Models\Product::where('business_id', $business->id)->count();
    $customerCount = \App\Models\Customer::where('business_id', $business->id)->count();
    $inventoryItems = \App\Models\Product::where('business_id', $business->id)->sum('stock');
@endphp

<div class="grid grid-3" style="margin: 0 0 1.5rem 0;">
    <div class="stat-card">
        <h3>Total Sales</h3>
        <div class="value">Rp {{ number_format($totalSales, 0, ',', '.') }}</div>
    </div>
    <div class="stat-card">
        <h3>Products</h3>
        <div class="value">{{ $productCount }}</div>
    </div>
    <div class="stat-card">
        <h3>Inventory Items</h3>
        <div class="value">{{ $inventoryItems }}</div>
    </div>
</div>



<div class="grid grid-2" style="margin-bottom: 1.5rem;">
    <div class="card">
        <h2>Sales</h2>
        <canvas id="salesChart" height="80"></canvas>
    </div>
    <div class="card">
        <h2>Trend</h2>
        <canvas id="growthChart" height="80"></canvas>
    </div>
</div>

<div class="card">
    <h2>Recent Activity</h2>
    @php
        $logs = \App\Models\ActivityLog::with('user')
            ->where('business_id', $business->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    @endphp

    @if($logs->isEmpty())
        <p>No recent activity to display.</p>
    @else
        <ul style="list-style: none; padding-left: 0;">
            @foreach($logs as $log)
                <li style="padding: .5rem 0; border-bottom: 1px solid #eee;">
                    <strong>{{ $log->user->name }}</strong>
                    <span style="color:#7f8c8d;">{{ $log->created_at->format('Y-m-d H:i') }}</span>
                    —
                    @php $message = data_get($log->new_values, 'message'); @endphp
                    <span>
                        @if($message)
                            {{ $message }}
                        @else
                            {{ ucfirst($log->action) }} {{ $log->model }}
                        @endif
                    </span>
                </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@php
    // Build daily sales for last 30 days
    $startDate = now()->subDays(29)->startOfDay();
    $rawDaily = \App\Models\Sale::where('business_id', $business->id)
        ->where('sale_date', '>=', $startDate)
        ->selectRaw('DATE(sale_date) as d, SUM(total) as t')
        ->groupBy('d')
        ->orderBy('d')
        ->pluck('t', 'd')
        ->toArray();

    $dailyLabels = [];
    $dailyValues = [];
    for ($i = 0; $i < 30; $i++) {
        $date = now()->subDays(29 - $i)->format('Y-m-d');
        $dailyLabels[] = now()->subDays(29 - $i)->format('d M');
        $dailyValues[] = (float)($rawDaily[$date] ?? 0);
    }

    // Build monthly totals and MoM growth for last 12 months
    $monthStart = now()->subMonths(11)->startOfMonth();
    $rawMonthly = \App\Models\Sale::where('business_id', $business->id)
        ->where('sale_date', '>=', $monthStart)
        ->selectRaw('DATE_FORMAT(sale_date, "%Y-%m") as m, SUM(total) as t')
        ->groupBy('m')
        ->orderBy('m')
        ->pluck('t', 'm')
        ->toArray();

    $monthlyLabels = [];
    $monthlyTotals = [];
    $monthlyGrowth = [];
    for ($i = 0; $i < 12; $i++) {
        $mKey = now()->subMonths(11 - $i)->format('Y-m');
        $monthlyLabels[] = now()->subMonths(11 - $i)->format('M Y');
        $total = (float)($rawMonthly[$mKey] ?? 0);
        $monthlyTotals[] = $total;
        $prev = $i === 0 ? null : $monthlyTotals[$i-1];
        if ($prev === null) {
            $monthlyGrowth[] = 0;
        } else {
            $monthlyGrowth[] = $prev > 0 ? round((($total - $prev) / $prev) * 100, 2) : ($total > 0 ? 100 : 0);
        }
    }
@endphp

<script>
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($dailyLabels) !!},
            datasets: [{
                label: 'Total Harian',
                data: {!! json_encode($dailyValues) !!},
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.15)',
                tension: 0.25,
                fill: true,
            }]
        },
        options: {
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    ticks: {
                        callback: (v) => 'Rp ' + Number(v).toLocaleString('id-ID')
                    }
                }
            }
        }
    });

    const growthCtx = document.getElementById('growthChart').getContext('2d');
    // Single line chart for monthly totals; tooltip shows MoM growth
    const monthlyGrowthData = {!! json_encode($monthlyGrowth) !!};
    new Chart(growthCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($monthlyLabels) !!},
            datasets: [{
                label: 'Total Bulanan',
                data: {!! json_encode($monthlyTotals) !!},
                borderColor: '#f39c12',
                backgroundColor: 'rgba(243, 156, 18, 0.15)',
                tension: 0.25,
                fill: true,
            }]
        },
        options: {
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            const v = ctx.parsed.y || 0;
                            return 'Total: Rp ' + Number(v).toLocaleString('id-ID');
                        },
                        afterLabel: function(ctx) {
                            const idx = ctx.dataIndex;
                            const mom = Number(monthlyGrowthData[idx] || 0);
                            const sign = mom > 0 ? '+' : '';
                            return 'MoM: ' + sign + mom.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + '%';
                        }
                    }
                }
            },
            scales: {
                y: {
                    ticks: { callback: (v) => 'Rp ' + Number(v).toLocaleString('id-ID') }
                }
            }
        }
    });
</script>
@endsection
