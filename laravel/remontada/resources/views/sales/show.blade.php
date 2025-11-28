@extends('layouts.app')

@section('title', 'Sale Details')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="margin: 0;">Sale Invoice</h2>
        <div style="display: flex; gap: 0.5rem;">
            <button onclick="window.print()" class="btn btn-primary">🖨️ Print</button>
            <a href="{{ route('sales.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
        <div>
            <h3 style="margin-bottom: 0.5rem;">Invoice Information</h3>
            <p style="margin: 0.25rem 0;"><strong>Invoice #:</strong> {{ $sale->invoice_number }}</p>
            <p style="margin: 0.25rem 0;"><strong>Date:</strong> {{ $sale->sale_date->format('d M Y H:i') }}</p>
            <p style="margin: 0.25rem 0;"><strong>Status:</strong> 
                <span style="padding: 0.25rem 0.5rem; background: {{ $sale->status === 'completed' ? '#27ae60' : ($sale->status === 'pending' ? '#f39c12' : '#e74c3c') }}; color: white; border-radius: 4px; font-size: 0.75rem;">
                    {{ ucfirst($sale->status) }}
                </span>
            </p>
            <p style="margin: 0.25rem 0;"><strong>Cashier:</strong> {{ $sale->user->name }}</p>
        </div>

        <div>
            <h3 style="margin-bottom: 0.5rem;">Customer</h3>
            @if($sale->customer)
                <p style="margin: 0.25rem 0;"><strong>Name:</strong> {{ $sale->customer->name }}</p>
                @if($sale->customer->phone)
                    <p style="margin: 0.25rem 0;"><strong>Phone:</strong> {{ $sale->customer->phone }}</p>
                @endif
                @if($sale->customer->email)
                    <p style="margin: 0.25rem 0;"><strong>Email:</strong> {{ $sale->customer->email }}</p>
                @endif
            @else
                <p style="margin: 0.25rem 0; color: #666;">Customer</p>
            @endif
        </div>
    </div>

    <h3 style="margin-bottom: 1rem;">Items</h3>
    <table style="margin-bottom: 2rem;">
        <thead>
            <tr>
                <th>Product</th>
                <th style="text-align: center;">Quantity</th>
                <th style="text-align: right;">Price</th>
                <th style="text-align: right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
                <tr>
                    <td>
                        <strong>{{ $item->product->name }}</strong><br>
                        <small style="color: #666;">#{{ $item->product->sku }}</small>
                    </td>
                    <td style="text-align: center;">{{ $item->quantity }}</td>
                    <td style="text-align: right;">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td style="text-align: right;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="max-width: 400px; margin-left: auto;">
        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #ddd;">
            <span>Subtotal:</span>
            <strong>Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</strong>
        </div>
        @if($sale->tax > 0)
            <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #ddd;">
                <span>Tax:</span>
                <strong>Rp {{ number_format($sale->tax, 0, ',', '.') }}</strong>
            </div>
        @endif
        @if($sale->discount > 0)
            <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #ddd;">
                <span>Discount:</span>
                <strong style="color: #e74c3c;">- Rp {{ number_format($sale->discount, 0, ',', '.') }}</strong>
            </div>
        @endif
        <div style="display: flex; justify-content: space-between; padding: 1rem 0; background: #e8f4f8; margin: 0 -1rem; padding-left: 1rem; padding-right: 1rem; margin-top: 0.5rem;">
            <span style="font-size: 1.2rem; font-weight: bold;">Total:</span>
            <strong style="font-size: 1.4rem; color: #27ae60;">Rp {{ number_format($sale->total, 0, ',', '.') }}</strong>
        </div>
    </div>

    @if($sale->notes)
        <div style="margin-top: 2rem; padding: 1rem; background: #f8f9fa; border-radius: 4px;">
            <strong>Notes:</strong>
            <p style="margin: 0.5rem 0 0 0;">{{ $sale->notes }}</p>
        </div>
    @endif
</div>
@endsection
