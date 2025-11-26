@extends('layouts.app')

@section('title', 'Add New Product')

@section('content')
<div class="card">
    <h2>Add New Product</h2>

    <form method="POST" action="{{ route('products.store') }}">
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

        <div class="grid grid-2">
            <div class="form-group">
                <label for="price">Selling Price *</label>
                <input type="number" name="price" id="price" class="form-control" value="{{ old('price') }}" step="0.01" min="0" required>
                @error('price')
                    <span style="color: #e74c3c; font-size: 0.9rem;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="cost">Cost (Optional)</label>
                <input type="number" name="cost" id="cost" class="form-control" value="{{ old('cost') }}" step="0.01" min="0">
                @error('cost')
                    <span style="color: #e74c3c; font-size: 0.9rem;">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="grid grid-2">
            <div class="form-group">
                <label for="stock">Initial Stock *</label>
                <input type="number" name="stock" id="stock" class="form-control" value="{{ old('stock', 0) }}" min="0" required>
                @error('stock')
                    <span style="color: #e74c3c; font-size: 0.9rem;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="min_stock">Minimum Stock Notif *</label>
                <input type="number" name="min_stock" id="min_stock" class="form-control" value="{{ old('min_stock', 5) }}" min="0" required>
                @error('min_stock')
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
@endsection
