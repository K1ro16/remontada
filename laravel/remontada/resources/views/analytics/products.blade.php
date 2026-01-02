@extends('layouts.app')

@section('title', 'All Products')

@section('content')
<div class="card" style="margin-bottom: 1rem; position: relative;">
    <h2>All Products (Revenue)</h2>
    <form method="GET" action="{{ route('analytics.products') }}" style="margin-top: 1rem;">
        <div class="grid {{ ($groupBy ?? 'daily') === 'daily' ? 'grid-6' : 'grid-7' }}">
            <div class="form-group">
                <label>Group By</label>
                <div class="select-box">
                    <select name="group_by" class="form-control">
                        <option value="daily" {{ ($groupBy ?? 'daily') === 'daily' ? 'selected' : '' }}>Daily</option>
                        <option value="monthly" {{ ($groupBy ?? 'daily') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Category</label>
                <div class="select-box">
                    <select name="category_id" class="form-control">
                        <option value="">All</option>
                        @foreach(($categories ?? []) as $cat)
                            <option value="{{ $cat->id }}" {{ ($categoryId ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @if($groupBy === 'daily')
            <div class="form-group">
                <label>From Date</label>
                <div class="date-picker" tabindex="0">
                    <input type="date" name="from_date" id="from_date" class="form-control date-input" value="{{ $uiFromDate ?? $fromDate }}" />
                </div>
            </div>
            <div class="form-group">
                <label>To Date</label>
                <div class="date-picker" tabindex="0">
                    <input type="date" name="to_date" id="to_date" class="form-control date-input" value="{{ $uiToDate ?? $toDate }}" />
                </div>
            </div>
            <div class="form-group">
                <label>Quick Range</label>
                <div class="select-box">
                    <select name="quick_range" id="quick_range" class="form-control">
                        <option value="">Select</option>
                        <option value="7" {{ ($quickRange ?? '') == '7' ? 'selected' : '' }}>Last 7 days</option>
                        <option value="15" {{ ($quickRange ?? '') == '15' ? 'selected' : '' }}>Last 15 days</option>
                        <option value="30" {{ ($quickRange ?? '') == '30' ? 'selected' : '' }}>Last 30 days</option>
                    </select>
                </div>
            </div>
            <div class="form-group" style="display:flex; align-items:flex-end;">
                <button type="submit" class="btn btn-primary" style="width:100%;">Apply Filters</button>
            </div>
            @else
            <div class="form-group">
                <label>From Month</label>
                <div class="date-picker" tabindex="0">
                    <input type="month" name="from_date" id="from_date" class="form-control date-input" value="{{ $uiFromDate ?? $fromDate }}" />
                </div>
            </div>
            <div class="form-group">
                <label>To Month</label>
                <div class="date-picker" tabindex="0">
                    <input type="month" name="to_date" id="to_date" class="form-control date-input" value="{{ $uiToDate ?? $toDate }}" />
                </div>
            </div>
            <div class="form-group">
                <label>Quick Range</label>
                <div class="select-box">
                    <select name="quick_month_range" id="quick_month_range" class="form-control">
                        <option value="">Select</option>
                        <option value="3" {{ ($quickMonthRange ?? '') == '3' ? 'selected' : '' }}>Last 3 months</option>
                        <option value="6" {{ ($quickMonthRange ?? '') == '6' ? 'selected' : '' }}>Last 6 months</option>
                        <option value="12" {{ ($quickMonthRange ?? '') == '12' ? 'selected' : '' }}>Last 12 months</option>
                    </select>
                </div>
            </div>
            <div class="form-group" style="position:relative;">
                <label>Quarter</label>
                <div class="quarter-dropdown-container">
                    <div id="quarterDropdownToggle" class="form-control quarter-toggle" tabindex="0" aria-haspopup="true" aria-expanded="false" role="button">Select</div>
                    <div id="quarterDropdown" class="dropdown-menu" style="position:absolute; z-index:1000; background:#fff; border:1px solid #ddd; border-radius:6px; padding:.75rem; margin-top:.5rem; min-width: 320px; display:none; box-shadow: 0 6px 16px rgba(0,0,0,.08);">
                        <div class="quarter-picker" style="display:flex; flex-direction:column; gap:.75rem;">
                            <div class="qp-year" style="display:flex; align-items:center; justify-content:center; gap:.75rem;">
                                <button type="button" class="btn btn-secondary qp-nav-btn" id="qp-prev" aria-label="Previous Year">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M15 18L9 12L15 6" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </button>
                                <strong id="qp-year-display">{{ ($quarterYear ?? now()->year) ?: now()->year }}</strong>
                                <button type="button" class="btn btn-secondary qp-nav-btn" id="qp-next" aria-label="Next Year">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M9 6L15 12L9 18" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </button>
                            </div>
                            <div class="qp-quarters" style="display:flex; gap:.5rem; flex-wrap: wrap; justify-content:center;">
                                <button type="button" class="btn btn-primary" data-q="Q1">Q1 (Jan–Mar)</button>
                                <button type="button" class="btn btn-primary" data-q="Q2">Q2 (Apr–Jun)</button>
                                <button type="button" class="btn btn-primary" data-q="Q3">Q3 (Jul–Sep)</button>
                                <button type="button" class="btn btn-primary" data-q="Q4">Q4 (Oct–Dec)</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group" style="display:flex; align-items:flex-end;">
                <button type="submit" class="btn btn-primary" style="width:100%;">Apply Filters</button>
            </div>
            @endif
        </div>
    </form>
    <a href="{{ route('analytics.index', ['group_by' => $groupBy, 'from_date' => $fromDate, 'to_date' => $toDate]) }}" class="btn btn-secondary back-btn" aria-label="Back to Analytics" style="position:absolute; right:.75rem; top:.75rem;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M15 18L9 12L15 6" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <span>Back</span>
    </a>
</div>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h3 style="margin:0;">Products by Revenue</h3>
    </div>
    <canvas id="allProductsChart"></canvas>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Replicate date-picker UX and filter behavior from Analytics
    (function() {
        document.querySelectorAll('.date-picker').forEach(wrapper => {
            const input = wrapper.querySelector('input.date-input');
            const applyMode = () => {
                const isMonth = input.type === 'month';
                wrapper.classList.toggle('month-mode', isMonth);
                wrapper.classList.toggle('is-empty', isMonth && !input.value);
            };
            wrapper.addEventListener('click', () => { if (input.showPicker) input.showPicker(); else input.focus(); });
            wrapper.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); if (input.showPicker) input.showPicker(); else input.focus(); }
            });
            input.addEventListener('focus', () => { if (input.showPicker) input.showPicker(); });
            input.addEventListener('change', applyMode);
            input.style.cursor = 'pointer';
            applyMode();
        });
    })();

    (function() {
        const groupSel = document.querySelector('select[name="group_by"]');
        const from = document.getElementById('from_date');
        const to = document.getElementById('to_date');
        const quickDaily = document.getElementById('quick_range');
        const quickMonthly = document.getElementById('quick_month_range');
        const quarterSel = document.getElementById('quarter');
        if (groupSel && from && to) {
            groupSel.addEventListener('change', function() {
                const monthly = this.value === 'monthly';
                from.setAttribute('type', monthly ? 'month' : 'date');
                to.setAttribute('type', monthly ? 'month' : 'date');
                const fromLabel = from.closest('.form-group').querySelector('label');
                const toLabel = to.closest('.form-group').querySelector('label');
                if (fromLabel) fromLabel.textContent = monthly ? 'From Month' : 'From Date';
                if (toLabel) toLabel.textContent = monthly ? 'To Month' : 'To Date';
                const sync = (w, i) => {
                    const isMonth = i.type === 'month';
                    w.classList.toggle('month-mode', isMonth);
                    w.classList.toggle('is-empty', isMonth && !i.value);
                };
                const fromWrapper = from.closest('.date-picker');
                const toWrapper = to.closest('.date-picker');
                if (fromWrapper) sync(fromWrapper, from);
                if (toWrapper) sync(toWrapper, to);
            });
            function resetQuick() {
                if (quickDaily) quickDaily.value = '';
                if (quickMonthly) quickMonthly.value = '';
                if (quarterSel) quarterSel.value = '';
                const ySel = document.getElementById('quarter_year');
                if (ySel) ySel.value = '';
            }
            from.addEventListener('change', resetQuick);
            to.addEventListener('change', resetQuick);
        }
    })();

    (function() {
        const sel = document.querySelector('select[name="group_by"]');
        if (sel) sel.addEventListener('change', function() { if (this.form) this.form.submit(); });
    })();

    (function() {
        const mSel = document.getElementById('quick_month_range');
        const qSel = document.getElementById('quarter');
        const ySel = document.getElementById('quarter_year');
        const from = document.getElementById('from_date');
        const to = document.getElementById('to_date');
        function applyMonthsRange(months) {
            if (!months || !to || !from) return;
            from.value = '';
            to.value = '';
            if (qSel) qSel.value = '';
            (mSel && mSel.form) && mSel.form.submit();
        }
        function applyQuarter(q) {
            if (!q || !to || !from) return;
            let year = (ySel && ySel.value) ? parseInt(ySel.value, 10) : null;
            if (!year) {
                const toVal = to.value || new Date().toISOString().slice(0,7);
                year = parseInt(toVal.split('-')[0], 10);
            }
            const map = {
                'Q1': { s: year + '-01', e: year + '-03' },
                'Q2': { s: year + '-04', e: year + '-06' },
                'Q3': { s: year + '-07', e: year + '-09' },
                'Q4': { s: year + '-10', e: year + '-12' },
            };
            const r = map[q];
            if (!r) return;
            from.value = r.s;
            to.value = r.e;
            (qSel && qSel.form) && qSel.form.submit();
        }
        if (mSel) mSel.addEventListener('change', () => applyMonthsRange(parseInt(mSel.value||'0',10)));
        if (qSel) qSel.addEventListener('change', () => applyQuarter(qSel.value));
        if (ySel) ySel.addEventListener('change', () => { if (qSel && qSel.value) applyQuarter(qSel.value); });
        const qpYearEl = document.getElementById('qp-year-display');
        const qpPrev = document.getElementById('qp-prev');
        const qpNext = document.getElementById('qp-next');
        const qpButtons = document.querySelectorAll('.qp-quarters [data-q]');
        const ddToggle = document.getElementById('quarterDropdownToggle');
        const dd = document.getElementById('quarterDropdown');
        function getQpYear() { return parseInt((qpYearEl && qpYearEl.textContent) || (new Date()).getFullYear(), 10); }
        function setQpYear(y) { if (qpYearEl) qpYearEl.textContent = String(y); }
        if (qpPrev) qpPrev.addEventListener('click', () => setQpYear(getQpYear() - 1));
        if (qpNext) qpNext.addEventListener('click', () => setQpYear(getQpYear() + 1));
        function toggleDd() {
            if (!dd) return;
            const visible = dd.style.display === 'block';
            dd.style.display = visible ? 'none' : 'block';
            if (ddToggle) ddToggle.setAttribute('aria-expanded', (!visible).toString());
        }
        if (ddToggle) ddToggle.addEventListener('click', toggleDd);
        if (ddToggle) ddToggle.addEventListener('keydown', (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggleDd(); } });
        document.addEventListener('click', (e) => {
            if (!dd || !ddToggle) return;
            if (!dd.contains(e.target) && !ddToggle.contains(e.target)) {
                dd.style.display = 'none';
                ddToggle.setAttribute('aria-expanded', 'false');
            }
        });
        function applyQuarterByYear(y, q) {
            if (!from || !to) return;
            const map = {
                'Q1': { s: y + '-01', e: y + '-03' },
                'Q2': { s: y + '-04', e: y + '-06' },
                'Q3': { s: y + '-07', e: y + '-09' },
                'Q4': { s: y + '-10', e: y + '-12' },
            };
            const r = map[q];
            if (!r) return;
            from.value = r.s;
            to.value = r.e;
            if (ySel) ySel.value = String(y);
            if (qSel) qSel.value = q;
            if (mSel) mSel.value = '';
            const form = (from.closest('form'));
            if (form) form.submit();
            if (dd) dd.style.display = 'none';
        }
        if (qpButtons && qpButtons.length) { qpButtons.forEach(btn => btn.addEventListener('click', () => applyQuarterByYear(getQpYear(), btn.getAttribute('data-q')))); }
    })();

    (function() {
        const sel = document.getElementById('quick_range');
        const to = document.getElementById('to_date');
        const from = document.getElementById('from_date');
        function adjustAndSubmit() {
            const v = parseInt(sel.value || '0', 10);
            if (!v || !to || !from) return;
            from.value = '';
            to.value = '';
            sel.form && sel.form.submit();
        }
        if (sel) sel.addEventListener('change', adjustAndSubmit);
    })();
    const labels = {!! json_encode($productLabels ?? []) !!};
    const values = {!! json_encode($productValues ?? []) !!};
    const groupBy = {!! json_encode($groupBy ?? 'daily') !!};
    const quantities = {!! json_encode($productQuantities ?? []) !!};
    const margins = {!! json_encode($productMargins ?? []) !!};
    const marginRates = {!! json_encode($productMarginRates ?? []) !!};

    // Adjust canvas height based on item count; compact when few items
    const canvas = document.getElementById('allProductsChart');
    if (canvas) {
        const perItem = 28; // separation per product (unchanged)
        const minH = 240; // enlarge overall chart area
        const desiredH = Math.max(minH, (labels.length || 0) * perItem);
        const wrap = canvas.parentElement;
        if (wrap) wrap.style.height = desiredH + 'px';
        canvas.style.height = desiredH + 'px';
        // Do NOT set canvas.height attribute; Chart.js manages device-pixel scaling.
        canvas.style.display = 'block';
        const ctx = canvas.getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenue',
                    data: values,
                    backgroundColor: '#2ecc71',
                    // Make bars visually thicker but keep gaps
                    barThickness: 14,
                    maxBarThickness: 18,
                    categoryPercentage: 0.42, // keep noticeable separation between bars
                    barPercentage: 0.72, // fill more of the allocated category space
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                // Add inner spacing at the top and bottom of the chart area
                layout: { padding: { top: 12, bottom: 12 } },
                indexAxis: 'y', // horizontal bars, vertical list of products
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const v = ctx.parsed.x || 0;
                                const idx = ctx.dataIndex;
                                const qty = quantities && quantities[idx] !== undefined ? quantities[idx] : 0;
                                const m = margins && margins[idx] !== undefined ? margins[idx] : 0;
                                const mr = marginRates && marginRates[idx] !== undefined ? marginRates[idx] : 0;
                                return [
                                    'Revenue: Rp ' + Number(v).toLocaleString('id-ID'),
                                    'Gross Margin: Rp ' + Number(m).toLocaleString('id-ID') + ' (' + Number(mr).toLocaleString('id-ID', { maximumFractionDigits: 2 }) + '%)',
                                    'Qty: ' + Number(qty).toLocaleString('id-ID')
                                ];
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            callback: (v) => 'Rp ' + Number(v).toLocaleString('id-ID')
                        },
                        grid: { drawBorder: false }
                    },
                    y: {
                        // Add offset so first/last categories have space from edges
                        offset: true,
                        alignToPixels: true,
                        ticks: { autoSkip: false, padding: 10, font: { size: 12 } },
                        grid: { display: false }
                    }
                }
            }
        });
    }
</script>
@endsection

@section('styles')
<style>
    .date-picker { position: relative; }
    .date-picker .form-control { cursor: pointer; }
    .date-picker input::-webkit-calendar-picker-indicator { cursor: pointer; }
    .date-picker.month-mode.is-empty { user-select: none; }
    .date-picker.month-mode.is-empty input[type="month"] { color: transparent; caret-color: transparent; }
    .date-picker.month-mode.is-empty input[type="month"]::-webkit-datetime-edit { color: transparent; }
    .date-picker.month-mode.is-empty::before {
        content: 'mm/yyyy';
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #000000ff;
        font-weight: 400;
        font-size: 0.95rem;
        pointer-events: none;
    }
    .quarter-toggle {
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background-color: #fff;
        border: 1px solid #ced4da;
        border-radius: .25rem;
        padding: .375rem .75rem;
        line-height: 1.5;
        min-height: 38px;
        position: relative;
        padding-right: 2rem;
    }
    .quarter-toggle::after {
        content: '';
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        border-left: 4px solid transparent;
        border-right: 4px solid transparent;
        border-top: 5px solid #555;
        pointer-events: none;
    }
    .select-box { position: relative; }
    .select-box select.form-control {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        padding-right: 2rem;
        background-color: #fff;
        border: 1px solid #ced4da;
        border-radius: .25rem;
        line-height: 1.5;
        min-height: 38px;
    }
    .select-box::after {
        content: '';
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-top: 6px solid #333;
        pointer-events: none;
    }
    .qp-nav-btn { display: inline-flex; align-items: center; justify-content: center; padding: .25rem .5rem; }
    .qp-nav-btn svg { display: block; }
    .back-btn { display: inline-flex; align-items: center; gap: .5rem; padding: .375rem .75rem; border: 1px solid #ced4da; border-radius: .25rem; background-color: #fff; }
    .back-btn:hover { border-color: #b5b5b5; background-color: #f8f9fa; }
    .back-btn svg { display: block; }
</style>
@endsection
