@extends('layouts.app')

@section('title', 'Analytics')

@section('content')
<div class="card" style="margin-bottom: 1rem;">
    <h2>Sales Analytics</h2>
    <form method="GET" action="{{ route('analytics.index') }}" style="margin-top: 1rem;">
        <div class="grid {{ ($groupBy ?? 'daily') === 'daily' ? 'grid-5' : 'grid-6' }}">
            <div class="form-group">
                <label>Group By</label>
                <div class="select-box">
                    <select name="group_by" class="form-control">
                        <option value="daily" {{ ($groupBy ?? 'daily') === 'daily' ? 'selected' : '' }}>Daily</option>
                        <option value="monthly" {{ ($groupBy ?? 'daily') === 'monthly' ? 'selected' : '' }}>Monthly</option>
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
</div>

@if($groupBy === 'monthly')
    <div class="grid grid-2">
        <div class="card">
            <h2>Monthly Totals</h2>
            <form method="GET" action="{{ route('analytics.index') }}" style="margin-bottom: .75rem;">
                <input type="hidden" name="group_by" value="monthly" />
                <div class="grid grid-3">
                    <div class="form-group">
                        <label>From Month</label>
                        <div class="date-picker" tabindex="0">
                            <input type="month" name="totals_from" id="totals_from" class="form-control date-input" value="{{ $totalsFrom }}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label>To Month</label>
                        <div class="date-picker" tabindex="0">
                            <input type="month" name="totals_to" id="totals_to" class="form-control date-input" value="{{ $totalsTo }}" />
                        </div>
                    </div>
                    <div class="form-group" style="display:flex; align-items:flex-end;">
                        <button type="submit" class="btn btn-primary" style="width:100%;">Apply</button>
                    </div>
                </div>
            </form>
            <canvas id="analyticsMonthlyChart" height="90"></canvas>
        </div>
        <div class="card">
            <h2>MoM Growth</h2>
            <form method="GET" action="{{ route('analytics.index') }}" style="margin-bottom: .75rem;">
                <input type="hidden" name="group_by" value="monthly" />
                <div class="grid grid-3">
                    <div class="form-group">
                        <label>From Month</label>
                        <div class="date-picker" tabindex="0">
                            <input type="month" name="growth_from" id="growth_from" class="form-control date-input" value="{{ $growthFrom }}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label>To Month</label>
                        <div class="date-picker" tabindex="0">
                            <input type="month" name="growth_to" id="growth_to" class="form-control date-input" value="{{ $growthTo }}" />
                        </div>
                    </div>
                    <div class="form-group" style="display:flex; align-items:flex-end;">
                        <button type="submit" class="btn btn-primary" style="width:100%;">Apply</button>
                    </div>
                </div>
            </form>
            <canvas id="analyticsGrowthChart" height="90"></canvas>
        </div>
    </div>
    
    <div class="grid grid-2">
        <div class="card">
            <h2>Gross Margin</h2>
            <form method="GET" action="{{ route('analytics.index') }}" style="margin-bottom: .75rem;">
                <input type="hidden" name="group_by" value="monthly" />
                <div class="grid grid-3">
                    <div class="form-group">
                        <label>From Month</label>
                        <div class="date-picker" tabindex="0">
                            <input type="month" name="margin_from" id="margin_from" class="form-control date-input" value="{{ $marginFrom }}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label>To Month</label>
                        <div class="date-picker" tabindex="0">
                            <input type="month" name="margin_to" id="margin_to" class="form-control date-input" value="{{ $marginTo }}" />
                        </div>
                    </div>
                    <div class="form-group" style="display:flex; align-items:flex-end;">
                        <button type="submit" class="btn btn-primary" style="width:100%;">Apply</button>
                    </div>
                </div>
            </form>
            <canvas id="analyticsMonthlyMarginChart" height="90"></canvas>
        </div>
        <div class="card">
            <h2>Margin Rate</h2>
            <form method="GET" action="{{ route('analytics.index') }}" style="margin-bottom: .75rem;">
                <input type="hidden" name="group_by" value="monthly" />
                <div class="grid grid-3">
                    <div class="form-group">
                        <label>From Month</label>
                        <div class="date-picker" tabindex="0">
                            <input type="month" name="margin_from" class="form-control date-input" value="{{ $marginFrom }}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label>To Month</label>
                        <div class="date-picker" tabindex="0">
                            <input type="month" name="margin_to" class="form-control date-input" value="{{ $marginTo }}" />
                        </div>
                    </div>
                    <div class="form-group" style="display:flex; align-items:flex-end;">
                        <button type="submit" class="btn btn-primary" style="width:100%;">Apply</button>
                    </div>
                </div>
            </form>
            <canvas id="analyticsMonthlyMarginRateChart" height="90"></canvas>
        </div>
    </div>
    <div class="card" style="margin-top: 1rem; position: relative;">
        <h2 style="margin-right: 3rem;">Top Products (Revenue)</h2>
        <a href="{{ route('analytics.products', ['group_by' => $groupBy, 'from_date' => $fromDate, 'to_date' => $toDate]) }}" class="btn btn-secondary" style="position:absolute; right: .75rem; top: .75rem;">View All</a>
        <canvas id="topProductsChart" height="100"></canvas>
    </div>
@else
    <div class="grid grid-2">
        <div class="card">
            <h2>Daily Totals</h2>
            <canvas id="analyticsDailyChart" height="90"></canvas>
        </div>
        <div class="card">
            <h2>Gross Margin</h2>
            <canvas id="analyticsDailyMarginChart" height="90"></canvas>
        </div>
    </div>
    
    <div class="card" style="margin-top: 1rem; position: relative;">
        <h2 style="margin-right: 3rem;">Top Products (Revenue)</h2>
        <a href="{{ route('analytics.products', ['group_by' => $groupBy, 'from_date' => $fromDate, 'to_date' => $toDate]) }}" class="btn btn-secondary" style="position:absolute; right: .75rem; top: .75rem;">View All</a>
        <canvas id="topProductsChart" height="100"></canvas>
    </div>
@endif

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Make the entire date box clickable (no right-side date overlay)
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

    // Toggle input types on group_by change for instant UX feedback
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
                // Update labels
                const fromLabel = from.closest('.form-group').querySelector('label');
                const toLabel = to.closest('.form-group').querySelector('label');
                if (fromLabel) fromLabel.textContent = monthly ? 'From Month' : 'From Date';
                if (toLabel) toLabel.textContent = monthly ? 'To Month' : 'To Date';

                // Sync placeholder state and mode classes
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
            // When user explicitly sets from/to, reset quick selectors
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

    // Always submit on group_by change so user can switch modes
    (function() {
        const sel = document.querySelector('select[name="group_by"]');
        if (sel) {
            sel.addEventListener('change', function() {
                if (this.form) this.form.submit();
            });
        }
    })();

    // Monthly quick range and quarter handlers
    (function() {
        const mSel = document.getElementById('quick_month_range');
        const qSel = document.getElementById('quarter');
        const ySel = document.getElementById('quarter_year');
        const from = document.getElementById('from_date');
        const to = document.getElementById('to_date');

        function pad(n) { return (n < 10 ? '0' : '') + n; }

        function offsetMonths(baseY, baseM, delta) {
            // baseM is 1..12; delta negative for past months
            let y = baseY;
            let m = baseM + delta;
            while (m < 1) { m += 12; y -= 1; }
            while (m > 12) { m -= 12; y += 1; }
            return y + '-' + pad(m);
        }

        function applyMonthsRange(months) {
            if (!months || !to || !from) return;
            // Clear inputs to show mm/yyyy and let server compute based on today
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

        // Quarter picker widget
        const qpYearEl = document.getElementById('qp-year-display');
        const qpPrev = document.getElementById('qp-prev');
        const qpNext = document.getElementById('qp-next');
        const qpButtons = document.querySelectorAll('.qp-quarters [data-q]');
        const ddToggle = document.getElementById('quarterDropdownToggle');
        const dd = document.getElementById('quarterDropdown');

        function getQpYear() { return parseInt((qpYearEl && qpYearEl.textContent) || (new Date()).getFullYear(), 10); }
        function setQpYear(y) {
            if (qpYearEl) qpYearEl.textContent = String(y);
        }
        if (qpPrev) qpPrev.addEventListener('click', () => setQpYear(getQpYear() - 1));
        if (qpNext) qpNext.addEventListener('click', () => setQpYear(getQpYear() + 1));

        // Dropdown toggle + close on outside click
        function toggleDd() {
            if (!dd) return;
            const visible = dd.style.display === 'block';
            dd.style.display = visible ? 'none' : 'block';
            if (ddToggle) ddToggle.setAttribute('aria-expanded', (!visible).toString());
        }
        if (ddToggle) ddToggle.addEventListener('click', toggleDd);
        if (ddToggle) ddToggle.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggleDd(); }
        });
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
            // keep selects in sync
            if (ySel) ySel.value = String(y);
            if (qSel) qSel.value = q;
            // clear quick month range
            if (mSel) mSel.value = '';
            // submit form
            const form = (from.closest('form'));
            if (form) form.submit();
            if (dd) dd.style.display = 'none';
        }
        if (qpButtons && qpButtons.length) {
            qpButtons.forEach(btn => btn.addEventListener('click', () => applyQuarterByYear(getQpYear(), btn.getAttribute('data-q'))));
        }
    })();

    // Quick range helper (daily): clears inputs and lets server compute from today
    (function() {
        const sel = document.getElementById('quick_range');
        const to = document.getElementById('to_date');
        const from = document.getElementById('from_date');
        function adjustAndSubmit() {
            const v = parseInt(sel.value || '0', 10);
            if (!v || !to || !from) return;
            // Clear to show placeholders and compute on server using today
            from.value = '';
            to.value = '';
            sel.form && sel.form.submit();
        }
        if (sel) {
            sel.addEventListener('change', adjustAndSubmit);
        }
    })();

    const labels = {!! json_encode($labels) !!};
    const values = {!! json_encode($values) !!};
    const groupBy = {!! json_encode($groupBy) !!};
    const growth = {!! json_encode($growth ?? []) !!};
    const monthlyLabelsTotals = {!! json_encode($monthlyLabelsTotals ?? []) !!};
    const monthlyTotals = {!! json_encode($monthlyTotals ?? []) !!};
    const monthlyLabelsGrowth = {!! json_encode($monthlyLabelsGrowth ?? []) !!};
    const monthlyGrowth = {!! json_encode($monthlyGrowth ?? []) !!};
    const topProductLabels = {!! json_encode($topProductLabels ?? []) !!};
    const topProductValues = {!! json_encode($topProductValues ?? []) !!};
    const topProductQuantities = {!! json_encode($topProductQuantities ?? []) !!};
    const topProductMargins = {!! json_encode($topProductMargins ?? []) !!};
    const topProductMarginRates = {!! json_encode($topProductMarginRates ?? []) !!};
    const marginLabels = {!! json_encode($marginLabels ?? []) !!};
    const marginTotals = {!! json_encode($marginTotals ?? []) !!};
    const marginRates = {!! json_encode($marginRates ?? []) !!};

    if (groupBy === 'monthly') {
        const monthlyCtx = document.getElementById('analyticsMonthlyChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: monthlyLabelsTotals,
                datasets: [{
                    label: 'Total Bulanan',
                    data: monthlyTotals,
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.15)',
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

        const growthCtx = document.getElementById('analyticsGrowthChart').getContext('2d');
        new Chart(growthCtx, {
            type: 'bar',
            data: {
                labels: monthlyLabelsGrowth,
                datasets: [{
                    label: 'MoM Growth %',
                    data: monthlyGrowth,
                    backgroundColor: '#27ae60'
                }]
            },
            options: {
                // Easier hover: anywhere along the month column shows tooltip
                interaction: { mode: 'index', intersect: false, axis: 'x' },
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: (v) => v + '%' }
                    }
                }
            }
        });

        // Monthly gross margin (amount)
        const monthlyMarginCtx = document.getElementById('analyticsMonthlyMarginChart').getContext('2d');
        new Chart(monthlyMarginCtx, {
            type: 'line',
            data: {
                labels: marginLabels,
                datasets: [{
                    label: 'Gross Margin',
                    data: marginTotals,
                    borderColor: '#e67e22',
                    backgroundColor: 'rgba(230, 126, 34, 0.15)',
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
                                const idx = ctx.dataIndex;
                                const rate = marginRates && marginRates[idx] !== undefined ? marginRates[idx] : 0;
                                return [
                                    'Margin: Rp ' + Number(v).toLocaleString('id-ID'),
                                    'Rate: ' + Number(rate).toLocaleString('id-ID', { maximumFractionDigits: 2 }) + '%'
                                ];
                            }
                        }
                    }
                },
                scales: {
                    y: { ticks: { callback: (v) => 'Rp ' + Number(v).toLocaleString('id-ID') } }
                }
            }
        });

        // Monthly margin rate (%)
        const monthlyMarginRateCtx = document.getElementById('analyticsMonthlyMarginRateChart').getContext('2d');
        new Chart(monthlyMarginRateCtx, {
            type: 'bar',
            data: {
                labels: marginLabels,
                datasets: [{
                    label: 'Margin Rate %',
                    data: marginRates,
                    backgroundColor: '#f39c12'
                }]
            },
            options: {
                // Easier hover on bars across the same index
                interaction: { mode: 'index', intersect: false, axis: 'x' },
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: (v) => Number(v).toLocaleString('id-ID', { maximumFractionDigits: 2 }) + '%' }
                    }
                }
            }
        });

        
    } else {
        const dailyCtx = document.getElementById('analyticsDailyChart').getContext('2d');
        new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total Harian',
                    data: values,
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.15)',
                    tension: 0.25,
                    fill: true,
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        ticks: { callback: (v) => 'Rp ' + Number(v).toLocaleString('id-ID') }
                    }
                }
            }
        });

        

        // Daily gross margin
        const dailyMarginCtx = document.getElementById('analyticsDailyMarginChart').getContext('2d');
        new Chart(dailyMarginCtx, {
            type: 'line',
            data: {
                labels: marginLabels,
                datasets: [{
                    label: 'Gross Margin',
                    data: marginTotals,
                    borderColor: '#e67e22',
                    backgroundColor: 'rgba(230, 126, 34, 0.15)',
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
                                const idx = ctx.dataIndex;
                                const rate = marginRates && marginRates[idx] !== undefined ? marginRates[idx] : 0;
                                return [
                                    'Margin: Rp ' + Number(v).toLocaleString('id-ID'),
                                    'Rate: ' + Number(rate).toLocaleString('id-ID', { maximumFractionDigits: 2 }) + '%'
                                ];
                            }
                        }
                    }
                },
                scales: {
                    y: { ticks: { callback: (v) => 'Rp ' + Number(v).toLocaleString('id-ID') } }
                }
            }
        });
    }

    // Top products chart (if data available)
    (function() {
        const el = document.getElementById('topProductsChart');
        if (!el) return;
        const hasData = Array.isArray(topProductLabels) && topProductLabels.length > 0;
        const ctx = el.getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: topProductLabels,
                datasets: [{
                    label: 'Revenue',
                    data: topProductValues,
                    backgroundColor: '#8e44ad'
                }]
            },
            options: {
                // Make hovering easier: trigger tooltips anywhere along the same X index
                interaction: { mode: 'index', intersect: false, axis: 'x' },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const v = ctx.parsed.y || 0;
                                const idx = ctx.dataIndex;
                                const qty = topProductQuantities && topProductQuantities[idx] !== undefined ? topProductQuantities[idx] : 0;
                                const m = topProductMargins && topProductMargins[idx] !== undefined ? topProductMargins[idx] : 0;
                                const mr = topProductMarginRates && topProductMarginRates[idx] !== undefined ? topProductMarginRates[idx] : 0;
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
                    x: { ticks: { autoSkip: false } },
                    y: {
                        beginAtZero: true,
                        ticks: { callback: (v) => 'Rp ' + Number(v).toLocaleString('id-ID') }
                    }
                }
            }
        });
    })();
</script>
@section('styles')
<style>
    .date-picker { position: relative; }
    .date-picker .form-control { cursor: pointer; }
    .date-picker input::-webkit-calendar-picker-indicator { cursor: pointer; }
    .date-picker input::-ms-clear { display: none; }
    .date-picker input::-ms-reveal { display: none; }
    /* Show custom hint only for empty month inputs */
    .date-picker.month-mode.is-empty { user-select: none; }
    .date-picker.month-mode.is-empty input[type="month"] { color: transparent; caret-color: transparent; }
    .date-picker.month-mode.is-empty input[type="month"]::-webkit-datetime-edit { color: transparent; }
    .date-picker.month-mode.is-empty input[type="month"]:focus::-webkit-datetime-edit { color: transparent; }
    .date-picker.month-mode.is-empty input[type="month"]::-webkit-datetime-edit-fields-wrapper { color: transparent; }
    .date-picker.month-mode.is-empty input[type="month"]::-webkit-datetime-edit-month-field { color: transparent; }
    .date-picker.month-mode.is-empty input[type="month"]::-webkit-datetime-edit-year-field { color: transparent; }
    .date-picker.month-mode.is-empty input[type="month"]::-webkit-datetime-edit-text { color: transparent; }
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
    /* Quarter dropdown trigger box matches input style */
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
        min-height: 38px; /* approx Bootstrap form-control height */
        position: relative;
        padding-right: 2rem; /* space for caret */
    }
    .quarter-toggle:focus {
        outline: 0;
        border-color: #86b7fe;
        box-shadow: 0 0 0 .25rem rgba(13,110,253,.25);
    }
    .quarter-toggle::after {
        content: '';
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        width: 0; height: 0;
        border-left: 4px solid transparent;
        border-right: 4px solid transparent;
        border-top: 5px solid #555; /* smaller, subtler caret */
        pointer-events: none;
    }
    .quarter-dropdown-container .dropdown-menu { min-width: 320px; }
    /* Smaller caret for custom selects */
    .select-box::after {
        content: '';
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        width: 0; height: 0;
        border-left: 4px solid transparent;
        border-right: 4px solid transparent;
        border-top: 5px solid #555;
        pointer-events: none;
    }
    .qp-nav-btn { display: inline-flex; align-items: center; justify-content: center; padding: .25rem .5rem; }
    .qp-nav-btn svg { display: block; }
    /* Custom caret for selects to match quarter toggle */
    .select-box { position: relative; }
    .select-box select.form-control {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        padding-right: 2rem; /* room for caret */
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
        width: 0; height: 0;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-top: 6px solid #333;
        pointer-events: none;
    }
</style>
@endsection
@endsection
