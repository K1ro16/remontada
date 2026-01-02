@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 style="margin-top:.5rem;">KPI Dashboard</h1>

    <form method="GET" action="{{ route('analytics.kpi') }}" class="card" style="margin-top:1rem; padding:1rem;">
        <div style="display:flex; justify-content:flex-end; align-items:center; margin:-.5rem 0 .5rem 0;">
        </div>
        <div class="grid grid-3">
            <div class="form-group">
                <label>Month</label>
                <div class="date-picker" tabindex="0">
                    <input type="month" name="base_month" class="form-control date-input" value="{{ $baseMonth }}" />
                </div>
            </div>
            <div class="form-group">
                <label>Compare With</label>
                <div class="date-picker" tabindex="0">
                    <input type="month" name="compare_month" class="form-control date-input" value="{{ $compareMonth }}" />
                </div>
            </div>
        </div>
        <div style="margin-top:.5rem; display:flex; gap:.5rem;">
            <button type="submit" class="btn btn-primary">Apply</button>
        </div>
    </form>

    {{-- Inline KPI alerts removed; use Notifications page instead. --}}

    <div class="grid grid-2" style="margin-top:1rem;">
        <div class="card">
            <div style="display:flex; align-items:center; justify-content:space-between;">
                <h2>Sales Growth</h2>
                <button type="button" class="settings-toggle btn" data-target="#sales-growth-settings" aria-expanded="false" style="background:#f4f4f4;">⚙️</button>
            </div>
            <div id="sales-growth-settings" class="settings-panel" style="display:none; margin:.5rem 0 1rem 0;">
                <form method="GET" action="{{ route('analytics.kpi') }}" style="display:flex; gap:.5rem; align-items:flex-end;">
                    <input type="hidden" name="base_month" value="{{ $baseMonth }}" />
                    <input type="hidden" name="compare_month" value="{{ $compareMonth }}" />
                    <div class="form-group" style="max-width:160px;">
                        <label>Warn ≤</label>
                        <input type="number" step="0.01" name="growth_warn" class="form-control" value="{{ $growthWarn }}" />
                    </div>
                    <div class="form-group" style="max-width:160px;">
                        <label>Critical ≤</label>
                        <input type="number" step="0.01" name="growth_crit" class="form-control" value="{{ $growthCrit }}" />
                    </div>
                    <div class="form-group">
                        <button class="btn btn-primary" type="submit">Apply</button>
                    </div>
                </form>
            </div>
            <p>Status: <span class="status-dot {{ $salesGrowthStatus }}"></span> {{ $salesGrowth }}%</p>
            <div>
                <p>Current: Rp {{ number_format($currSales,0,',','.') }}</p>
                <p>Previous: Rp {{ number_format($prevSales,0,',','.') }}</p>
            </div>
        </div>
        <div class="card">
            <h2>Average Daily Sales</h2>
            <p>Avg: Rp {{ number_format($avgDailySales,0,',','.') }}</p>
            <p>Active Days: {{ $activeDays }}</p>
        </div>
    </div>

    <div class="grid grid-1" style="margin-top:1rem;">
        <div class="card">
            <div style="display:flex; align-items:center; justify-content:space-between;">
                <h2>Gross Margin (Avg Rate)</h2>
                <button type="button" class="settings-toggle btn" data-target="#margin-settings" aria-expanded="false" style="background:#f4f4f4;">⚙️</button>
            </div>
            <div id="margin-settings" class="settings-panel" style="display:none; margin:.5rem 0 1rem 0;">
                <form method="GET" action="{{ route('analytics.kpi') }}" style="display:flex; gap:.5rem; align-items:flex-end;">
                    <input type="hidden" name="base_month" value="{{ $baseMonth }}" />
                    <input type="hidden" name="compare_month" value="{{ $compareMonth }}" />
                    <div class="form-group" style="max-width:160px;">
                        <label>Warn ≤</label>
                        <input type="number" step="0.01" name="margin_warn" class="form-control" value="{{ $marginWarn }}" />
                    </div>
                    <div class="form-group" style="max-width:160px;">
                        <label>Critical &lt;</label>
                        <input type="number" step="0.01" name="margin_crit" class="form-control" value="{{ $marginCrit }}" />
                    </div>
                    <div class="form-group">
                        <button class="btn btn-primary" type="submit">Apply</button>
                    </div>
                </form>
            </div>
            <p>Status: <span class="status-dot {{ $marginStatus }}"></span> {{ $avgMarginRate }}%</p>
            @if(count($lowMarginProducts))
                <h3 style="margin-top:.5rem;">Flagged Products (Rate &lt; {{ number_format($marginCrit,2) }}%)</h3>
                <ul class="flagged-list">
                @foreach($lowMarginProducts as $p)
                    <li><span class="product-name">{{ $p['name'] }}</span><span class="badge rate-badge">{{ number_format($p['rate'],2) }}%</span></li>
                @endforeach
                </ul>
            @else
                <p>No products below threshold.</p>
            @endif
        </div>
    </div>

    <div class="card" style="margin-top:1rem;">
        <h2>Category Sales Distribution</h2>
        <table class="table" id="kpi-category-table">
            <thead><tr><th>Category</th><th>Revenue</th><th>Detail</th></tr></thead>
            <tbody>
            @foreach($categoryKpis as $c)
                <tr class="category-row" data-cid="{{ $c['id'] }}" style="cursor:pointer;">
                    <td>{{ $c['name'] }}</td>
                    <td>Rp {{ number_format($c['revenue'],0,',','.') }}</td>
                    <td>
                        <button type="button" class="btn category-detail-btn" data-cid="{{ $c['id'] }}" data-cname="{{ $c['name'] }}" style="padding:.35rem .6rem;">Detail</button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    
</div>
@endsection

@section('styles')
<style>
.status-dot { display:inline-block; width:12px; height:12px; border-radius:50%; vertical-align:middle; margin-right:.25rem; }
.status-dot.green { background:#27ae60; }
.status-dot.yellow { background:#f1c40f; }
.status-dot.red { background:#e74c3c; }
.card { padding: 1rem; background: #fff; border-radius: .5rem; box-shadow: 0 1px 2px rgba(0,0,0,.08); }
.grid { display:grid; gap:1rem; }
.grid-2 { grid-template-columns: 1fr 1fr; }
.grid-4 { grid-template-columns: repeat(4, 1fr); gap:1rem; }
.table { width:100%; border-collapse: collapse; }
.table th, .table td { padding:.5rem; border-bottom:1px solid #eee; text-align:left; }
/* Right-align the numeric (last) column across header/body/footer */
.table thead th:nth-child(2),
.table tbody td:nth-child(2),
.table tfoot th:nth-child(2) { text-align: right; }
/* First column wider for names */
.table th:first-child,
.table td:first-child { width: 60%; }
/* Align the Detail button to the right */
.table thead th:last-child,
.table tbody td:last-child,
.table tfoot th:last-child { text-align: right; }
/* Modal */
.modal-backdrop { position:fixed; inset:0; background:rgba(0,0,0,.45); display:none; align-items:center; justify-content:center; z-index:1000; }
.modal { background:#fff; border-radius:8px; box-shadow:0 10px 30px rgba(0,0,0,.2); width:min(720px, 92vw); max-height:80vh; overflow:auto; }
.modal header { display:flex; justify-content:space-between; align-items:center; padding:1rem 1.25rem; border-bottom:1px solid #eee; }
.modal .content { padding:1rem 1.25rem; }
.close-btn { background:#eee; border:none; padding:.4rem .7rem; border-radius:4px; cursor:pointer; }
.close-btn:hover { background:#e0e0e0; }
.settings-toggle { border: 1px solid #ddd; }
.settings-toggle:hover { background:#e9e9e9; }
.settings-panel { background:#fafafa; border:1px solid #eee; padding:.75rem; border-radius:6px; }
/* Flagged products list styling */
.flagged-list { list-style:none; padding-left:0; margin:.25rem 0 0 0; }
.flagged-list li { display:flex; align-items:center; gap:.5rem; padding:.15rem 0; }
.product-name { font-weight:500; color:#2c3e50; }
.badge.rate-badge { background:#fff0f0; color:#c0392b; border:1px solid #f5c6cb; padding:.15rem .45rem; border-radius:.35rem; font-size:.85rem; }
</style>
@endsection

@section('scripts')
<script>
(function(){
    // Build modal DOM once
    const backdrop = document.createElement('div');
    backdrop.className = 'modal-backdrop';
    backdrop.innerHTML = `
        <div class="modal">
            <header>
                <h3 id="modal-title">Category Detail</h3>
                <button class="close-btn" id="modal-close">Close</button>
            </header>
            <div class="content">
                <div id="modal-body">Loading...</div>
            </div>
        </div>
    `;
    document.body.appendChild(backdrop);
    const close = () => { backdrop.style.display = 'none'; };
    backdrop.addEventListener('click', (e) => { if (e.target === backdrop) close(); });
    document.getElementById('modal-close')?.addEventListener('click', close);

    function fmtRp(n){ return 'Rp ' + Number(n||0).toLocaleString('id-ID'); }

    async function openCategory(cid, cname){
        const params = new URLSearchParams({
            category_id: String(cid),
            base_month: '{{ $baseMonth }}',
        });
        const url = '{{ route('analytics.kpi.category-items') }}' + '?' + params.toString();
        const title = document.getElementById('modal-title'); if (title) title.textContent = 'Category: ' + cname;
        const body = document.getElementById('modal-body'); if (body) body.textContent = 'Loading...';
        backdrop.style.display = 'flex';
        try{
            const res = await fetch(url, { headers: { 'Accept':'application/json' } });
            const data = await res.json();
            const items = Array.isArray(data.items) ? data.items : [];
            const rows = items.map(it => `<tr><td>${it.name}</td><td style="text-align:right;">${fmtRp(it.revenue)}</td></tr>`).join('');
            const html = `
                <table class="table"><thead><tr><th>Item</th><th>Revenue</th></tr></thead>
                <tbody>${rows || '<tr><td colspan="2">No items</td></tr>'}</tbody>
                <tfoot><tr><th>Total</th><th style="text-align:right;">${fmtRp(data.total||0)}</th></tr></tfoot>
                </table>`;
            if (body) body.innerHTML = html;
        } catch(err){ if (body) body.textContent = 'Failed to load'; }
    }

    // Attach click handlers
    document.querySelectorAll('.category-row').forEach(tr => {
        tr.addEventListener('click', () => openCategory(tr.getAttribute('data-cid'), tr.children[0].textContent.trim()));
        tr.addEventListener('keydown', (e) => { if(e.key==='Enter' || e.key===' '){ e.preventDefault(); openCategory(tr.getAttribute('data-cid'), tr.children[0].textContent.trim()); } });
        tr.setAttribute('tabindex','0');
    });
    // Attach explicit Detail button handlers (stop row click propagation)
    document.querySelectorAll('.category-detail-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const cid = btn.getAttribute('data-cid');
            const cname = btn.getAttribute('data-cname');
            openCategory(cid, cname);
        });
    });
})();

// Toggle settings panels
(function(){
    document.querySelectorAll('.settings-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            const sel = btn.getAttribute('data-target');
            if (!sel) return;
            const panel = document.querySelector(sel);
            if (!panel) return;
            const visible = panel.style.display !== 'none';
            panel.style.display = visible ? 'none' : 'block';
            btn.setAttribute('aria-expanded', (!visible).toString());
        });
    });
})();

// KPI inline alerts removed; no dismiss handler needed.
</script>
@endsection
