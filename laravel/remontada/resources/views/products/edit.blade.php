@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="margin: 0;">Edit Product</h2>
        <a href="{{ route('products.index') }}" class="btn btn-secondary">Back</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-error">
            <strong>Warning</strong> Ada field yang kosong (bikin kata2)
            <ul style="margin-top: 0.5rem;">
                @foreach ($errors->all() as $error)
                {{ $error }}
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('products.update', $product->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="grid grid-2">
            <div class="form-group">
                <label for="name">Product Name</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
            </div>

            <div class="form-group">
                <label for="sku">Product Code *</label>
                <input type="text" id="sku" name="sku" class="form-control" value="{{ old('sku', $product->sku) }}" required readonly style="background-color: #f5f5f5;">
                <small style="color: #666;">Auto-generated based on category</small>
            </div>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="form-control" rows="3">{{ old('description', $product->description) }}</textarea>
        </div>

        <div class="form-group">
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id" class="form-control" onchange="generateSKU()">
                <option value="">-- Select Category --</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" data-prefix="{{ $category->prefix }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-2">
            <div class="form-group">
                <label for="price">Selling Price (Rp)</label>
                <input type="number" id="price" name="price" class="form-control" step="0.01" value="{{ old('price', $product->price) }}" required>
            </div>

            <div class="form-group">
                <label for="cost">Cost Price (Rp)</label>
                <input type="number" id="cost" name="cost" class="form-control" step="0.01" value="{{ old('cost', $product->cost) }}">
            </div>
        </div>

        <div class="grid grid-2">
            <div class="form-group">
                <label for="stock">Stock</label>
                <input type="number" id="stock" name="stock" class="form-control" value="{{ old('stock', $product->stock) }}" required readonly style="background-color: #f5f5f5;">
                <small style="color: #666; font-size: 0.875rem;">Use stock adjustment to change stock</small>
            </div>

            <div class="form-group">
                <label for="min_stock">Minimum Stock Alert</label>
                <input type="number" id="min_stock" name="min_stock" class="form-control" value="{{ old('min_stock', $product->min_stock) }}">
            </div>
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="is_inactive" id="is_inactive" value="1" {{ old('is_inactive', !$product->is_active) ? 'checked' : '' }} onchange="toggleInactiveReason()">
                Inactive Product?
            </label>
        </div>

        <div class="form-group" id="inactive_reason_field" style="{{ old('is_inactive', !$product->is_active) ? '' : 'display: none;' }}">
            <label for="inactive_reason">Alasan *</label>
            <textarea id="inactive_reason" name="inactive_reason" class="form-control" rows="2" placeholder="">{{ old('inactive_reason', $product->inactive_reason) }}</textarea>
        </div>

        <div style="display: flex; gap: 0.5rem; margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary">Update Product</button>
            <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
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
