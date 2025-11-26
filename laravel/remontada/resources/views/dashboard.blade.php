@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="card">
    <h2>Welcome, {{ Auth::user()->name }}!</h2>
    <p>Kata kata apalah disini</p>
</div>

<div class="grid grid-4">
    <div class="stat-card">
        <h3>Total Sales</h3>
        <div class="value">Rp 0</div>
    </div>
    <div class="stat-card">
        <h3>Products</h3>
        <div class="value">0</div>
    </div>
    <div class="stat-card">
        <h3>Customers</h3>
        <div class="value">0</div>
    </div>
    <div class="stat-card">
        <h3>Inventory Items</h3>
        <div class="value">0</div>
    </div>
</div>

<div class="card">
    <h2>Quick Actions</h2>
    <div class="grid @if(Auth::user()->hasRole('pemilik')) grid-4 @else grid-3 @endif" style="margin-top: 1rem;">
        <a href="#" class="btn btn-primary" style="text-align: center; text-decoration: none;">New Sale</a>
        <a href="{{ route('products.create') }}" class="btn btn-success" style="text-align: center; text-decoration: none;">Add Product</a>
        <a href="{{ route('products.index') }}" class="btn btn-warning" style="text-align: center; text-decoration: none;">Manage Inventory</a>
        @if(Auth::user()->hasRole('pemilik'))
            <a href="{{ route('users.index') }}" class="btn btn-primary" style="text-align: center; text-decoration: none;">Manage Users</a>
        @endif
    </div>
</div>

<div class="card">
    <h2>Recent Activity</h2>
    <p>No recent activity to display.</p>
</div>
@endsection
