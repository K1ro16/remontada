@extends('layouts.app')

@section('title', 'Add New Product')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="margin: 0;">Add New Product</h2>
        <a href="{{ route('products.index') }}" class="btn btn-secondary back-btn" aria-label="Back to Products">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M15 18L9 12L15 6" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span>Back</span>
        </a>
    </div>

    <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
        @csrf
        
        <div class="grid grid-2">
            <div class="form-group">
                <label for="name">Product Name *</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
                @error('name')
                    <span style="color: #e74c3c; font-size: 0.9rem;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="sku">Product Code *</label>
                <input type="text" name="sku" id="sku" class="form-control" value="{{ old('sku') }}" required readonly style="background-color: #f5f5f5;">
                <small style="color: #666;">Auto-generated based on category</small>
                @error('sku')
                    <span style="color: #e74c3c; font-size: 0.9rem;">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="form-group">
            <label for="category_id">Category</label>
            <select name="category_id" id="category_id" class="form-control" onchange="generateSKU()">
                <option value="">Select Category (Optional)</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" data-prefix="{{ $category->prefix }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id')
                <span style="color: #e74c3c; font-size: 0.9rem;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" class="form-control" rows="3">{{ old('description') }}</textarea>
            @error('description')
                <span style="color: #e74c3c; font-size: 0.9rem;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="image">Product Image</label>
            <input type="file" name="image" id="image" class="form-control" accept="image/*">
            <small style="color:#666;">Max 2MB; JPG/PNG/WebP</small>
            @error('image')
                <span style="color: #e74c3c; font-size: 0.9rem;">{{ $message }}</span>
            @enderror
        </div>

        <div class="grid grid-2">
            <div class="form-group">
                <label for="price_display">Selling Price</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #555;">Rp</span>
                    <input type="text" id="price_display" class="form-control" inputmode="numeric" style="padding-left: 36px;" placeholder="0" value="{{ number_format((float) old('price', 0), 0, ',', '.') }}" oninput="onPriceInput()" required>
                    <input type="hidden" name="price" id="price" value="{{ old('price', 0) }}">
                </div>
                @error('price')
                    <span style="color: #e74c3c; font-size: 0.9rem;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="tax_percentage_display">Tax Optional</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #555;">%</span>
                    <input type="text" id="tax_percentage_display" class="form-control" inputmode="decimal" style="padding-left: 36px;" placeholder="0" value="{{ old('tax_percentage') ? old('tax_percentage') : '' }}" oninput="onTaxInput()">
                    <input type="hidden" name="tax_percentage" id="tax_percentage" value="{{ old('tax_percentage') }}">
                </div>
                <small style="color: #666;"></small>
                @error('tax_percentage')
                    <span style="color: #e74c3c; font-size: 0.9rem;">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="grid grid-2">
            <div class="form-group">
                <label for="cost_display">Cost/item</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #555;">Rp</span>
                    <input type="text" id="cost_display" class="form-control" inputmode="numeric" style="padding-left: 36px;" placeholder="0" value="{{ old('cost') ? number_format((float) old('cost'), 0, ',', '.') : '' }}" oninput="onCostInput()">
                    <input type="hidden" name="cost" id="cost" value="{{ old('cost') }}">
                </div>
                @error('cost')
                    <span style="color: #e74c3c; font-size: 0.9rem;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="min_stock">Minimum Stock Notif</label>
                <input type="number" name="min_stock" id="min_stock" class="form-control" value="{{ old('min_stock', 5) }}" min="0" required>
                @error('min_stock')
                    <span style="color: #e74c3c; font-size: 0.9rem;">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="grid grid-2">
            <div class="form-group">
                <label for="stock">Initial Stock</label>
                <input type="number" name="stock" id="stock" class="form-control" value="{{ old('stock') }}" placeholder="0" required>
                @error('stock')
                    <span style="color: #e74c3c; font-size: 0.9rem;">{{ $message }}</span>
                @enderror
            </div>

        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="is_inactive" id="is_inactive" value="1" {{ old('is_inactive') ? 'checked' : '' }} onchange="toggleInactiveReason()">
                Inactive Product
            </label>
        </div>

        <div class="form-group" id="inactive_reason_field" style="{{ old('is_inactive') ? '' : 'display: none;' }}">
            <label for="inactive_reason">Alasan *</label>
            <textarea id="inactive_reason" name="inactive_reason" class="form-control" rows="2" placeholder="">{{ old('inactive_reason') }}</textarea>
        </div>

        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-success">Create Product</button>
            <a href="{{ route('products.index') }}" class="btn btn-danger">Cancel</a>
        </div>
    </form>
</div>

<script>
function generateSKU() {
    const categorySelect = document.getElementById('category_id');
    const skuInput = document.getElementById('sku');
    const selectedOption = categorySelect.options[categorySelect.selectedIndex];
    const prefix = selectedOption.getAttribute('data-prefix');
    
    if (prefix) {
        // Fetch next number for this category
        fetch(`/products/next-sku/${prefix}`)
            .then(response => response.json())
            .then(data => {
                skuInput.value = data.sku;
            })
            .catch(error => {
                console.error('Error generating SKU:', error);
                skuInput.value = prefix + '1';
            });
    } else {
        skuInput.value = '';
    }
}

function toggleInactiveReason() {
    const checkbox = document.getElementById('is_inactive');
    const reasonField = document.getElementById('inactive_reason_field');
    const reasonTextarea = document.getElementById('inactive_reason');
    
    if (checkbox.checked) {
        reasonField.style.display = 'block';
        reasonTextarea.required = true;
    } else {
        reasonField.style.display = 'none';
        reasonTextarea.required = false;
        reasonTextarea.value = '';
    }
}
</script>
<script>
function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

function onPriceInput() {
    const disp = document.getElementById('price_display');
    const hidden = document.getElementById('price');
    let num = parseInt((disp.value || '').toString().replace(/\D/g, '')) || 0;
    hidden.value = num;
    disp.value = formatNumber(num);
}

function onCostInput() {
    const disp = document.getElementById('cost_display');
    const hidden = document.getElementById('cost');
    let num = parseInt((disp.value || '').toString().replace(/\D/g, '')) || 0;
    hidden.value = num;
    disp.value = formatNumber(num);
}

function onTaxInput() {
    const disp = document.getElementById('tax_percentage_display');
    const hidden = document.getElementById('tax_percentage');
    let raw = (disp.value || '').toString();
    raw = raw.replace(/,/g, '.').replace(/[^0-9.]/g, '');
    const parts = raw.split('.');
    const normalized = parts[0] + (parts[1] ? '.' + parts[1].slice(0,2) : '');
    let num = parseFloat(normalized) || 0;
    if (num < 0) num = 0;
    if (num > 100) num = 100;
    hidden.value = num;
    disp.value = normalized;
}

// Initialize formatted display values on load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize formatted display values only if old values exist
    if (document.getElementById('price').value) { onPriceInput(); }
    if (document.getElementById('cost').value) { onCostInput(); }
    if (document.getElementById('tax_percentage').value) { onTaxInput(); }
});
</script>
@endsection

@section('styles')
<style>
    .back-btn { display: inline-flex; align-items: center; gap: .5rem; padding: .375rem .75rem; border: 1px solid #ced4da; border-radius: .25rem; background-color: #fff; }
    .back-btn:hover { border-color: #b5b5b5; background-color: #f8f9fa; }
    .back-btn svg { display: block; }
</style>
@endsection
