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

    <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
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

        <div class="grid grid-2">
            <div class="form-group">
                <label for="image">Product Image</label>
                <input type="file" id="image" name="image" class="form-control" accept="image/*">
                <small style="color:#666;">Upload to replace current image (max 2MB)</small>
                @error('image')
                    <span style="color: #e74c3c; font-size: 0.9rem;">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group">
                <label>Current Image</label>
                @if($product->image_path)
                    <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" style="width: 100%; max-height: 180px; object-fit: cover; border:1px solid #eee; border-radius: 4px;" />
                @else
                    <div style="padding: .75rem; color:#7f8c8d; border:1px dashed #ccc; border-radius:4px;">No image uploaded</div>
                @endif
            </div>
        </div>

        <div class="form-group">
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id" class="form-control" onchange="generateSKU()" data-original-category-id="{{ $product->category_id }}" data-original-sku="{{ $product->sku }}">
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
                <label for="price_display">Selling Price</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #555;">Rp</span>
                    <input type="text" id="price_display" class="form-control" inputmode="numeric" style="padding-left: 36px;" placeholder="0" value="{{ number_format((float) old('price', $product->price), 0, ',', '.') }}" oninput="onPriceInput()" required>
                    <input type="hidden" id="price" name="price" value="{{ old('price', $product->price) }}">
                </div>
            </div>

            <div class="form-group">
                <label for="tax_percentage_display">Tax Optional</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #555;">%</span>
                    <input type="text" id="tax_percentage_display" class="form-control" inputmode="decimal" style="padding-left: 36px;" placeholder="0" value="{{ old('tax_percentage', $product->tax_percentage) }}" oninput="onTaxInput()">
                    <input type="hidden" id="tax_percentage" name="tax_percentage" value="{{ old('tax_percentage', $product->tax_percentage) }}">
                </div>
                <small style="color: #666;">e.g., 10 for 10% tax</small>
            </div>
        </div>

        <div class="grid grid-2">
            <div class="form-group">
                <label for="cost_display">Cost</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #555;">Rp</span>
                    <input type="text" id="cost_display" class="form-control" inputmode="numeric" style="padding-left: 36px;" placeholder="0" value="{{ number_format((float) old('cost', $product->cost), 0, ',', '.') }}" oninput="onCostInput()">
                    <input type="hidden" id="cost" name="cost" value="{{ old('cost', $product->cost) }}">
                </div>
            </div>

            <div class="form-group">
                <label for="min_stock">Minimum Stock Alert</label>
                <input type="number" id="min_stock" name="min_stock" class="form-control" value="{{ old('min_stock', $product->min_stock) }}">
            </div>
        </div>

        <div class="grid grid-2">
            <div class="form-group">
                <label for="stock">Stock</label>
                <input type="number" id="stock" name="stock" class="form-control" value="{{ old('stock', $product->stock) }}" required readonly style="background-color: #f5f5f5;">
                <small style="color: #666; font-size: 0.875rem;">Use stock adjustment to change stock</small>
            </div>

            <div class="form-group">
                <!-- Empty div for grid alignment -->
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

    const originalCategoryId = categorySelect.dataset.originalCategoryId;
    const originalSku = categorySelect.dataset.originalSku;
    const selectedCategoryId = categorySelect.value;

    // If switched back to original category during edit, restore original SKU
    if (originalCategoryId && originalSku && selectedCategoryId === String(originalCategoryId)) {
        skuInput.value = originalSku;
        return;
    }

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
    onPriceInput();
    onCostInput();
    onTaxInput();
});
</script>
@endsection
