@extends('layouts.app')

@section('title', 'Export Sales Data')

@section('content')
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
        <h2 style="margin:0;">Export Sales Data</h2>
        <a href="{{ route('sales.index') }}" class="back-btn" aria-label="Back to Sales">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M15 18L9 12L15 6" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span>Back</span>
        </a>
    </div>

    <form method="GET" action="{{ route('sales.export') }}" style="margin-bottom:1rem;">
        <div class="grid grid-4">
            <div class="form-group">
                <label>From Date</label>
                <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}" />
            </div>
            <div class="form-group">
                <label>To Date</label>
                <input type="date" name="to_date" class="form-control" value="{{ $toDate }}" />
            </div>
            <div class="form-group" style="align-self:end;">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
            </div>
            <div class="form-group" style="align-self:end; text-align:right;">
                <a class="btn btn-success" href="{{ route('sales.export.excel', ['from_date' => $fromDate, 'to_date' => $toDate]) }}">Export to Excel</a>
            </div>
        </div>
    </form>

    <div class="card" style="padding:0;">
        @if($sales->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Invoice</th>
                        <th>User</th>
                        <th>Items</th>
                        <th style="text-align:right;">Total</th>
                        <th style="width:150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($sales as $sale)
                    <tr>
                        <td>{{ optional($sale->sale_date)->format('d M Y') }}</td>
                        <td>{{ $sale->invoice_number }}</td>
                        <td>{{ optional($sale->user)->name }}</td>
                        <td>{{ $sale->items_count }}</td>
                        <td style="text-align:right;">Rp {{ number_format($sale->total,0,',','.') }}</td>
                        <td>
                            <a href="{{ route('sales.show', $sale) }}" class="btn btn-primary btn-sm">View</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div style="padding:1rem;">
                {{ $sales->links() }}
            </div>
        @else
            <p style="padding:1rem; color:#7f8c8d;">No sales found for the selected range.</p>
        @endif
    </div>
</div>
@endsection
