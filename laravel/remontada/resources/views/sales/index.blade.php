@extends('layouts.app')

@section('title', 'Sales')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="margin: 0;">Sales Transactions</h2>
        <a href="{{ route('sales.create') }}" class="btn btn-primary">+ New Sale</a>
    </div>

    @if($sales->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th style="text-align: right;">Total</th>
                    <th>Status</th>
                    <th style="width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sales as $sale)
                    <tr>
                        <td><strong>{{ $sale->invoice_number }}</strong></td>
                        <td>{{ $sale->sale_date->format('d M Y H:i') }}</td>
                        <td>{{ $sale->customer ? $sale->customer->name : 'Customer' }}</td>
                        <td>{{ $sale->items->count() }} item(s)</td>
                        <td style="text-align: right;"><strong>Rp {{ number_format($sale->total, 0, ',', '.') }}</strong></td>
                        <td>
                            <span style="padding: 0.25rem 0.5rem; background: {{ $sale->status === 'completed' ? '#27ae60' : ($sale->status === 'pending' ? '#f39c12' : '#e74c3c') }}; color: white; border-radius: 4px; font-size: 0.75rem;">
                                {{ ucfirst($sale->status) }}
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="{{ route('sales.show', $sale) }}" class="btn btn-primary btn-sm">View</a>
                                <form method="POST" action="{{ route('sales.destroy', $sale) }}" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this sale? Stock will be restored.')">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top: 1.5rem;">
            {{ $sales->links() }}
        </div>
    @else
        <p style="text-align: center; color: #666; padding: 2rem;">No sales recorded yet. Create your first sale!</p>
    @endif
</div>
@endsection
