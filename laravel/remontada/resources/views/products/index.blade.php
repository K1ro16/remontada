@extends('layouts.app')

@section('title', 'Products & Inventory')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2>Products & Inventory</h2>
        <a href="{{ route('products.create') }}" class="btn btn-primary">Add New Product</a>
    </div>

    @if($products->count() > 0)
        @php
            $groupedProducts = $products->groupBy(function($product) {
                return $product->category_id ?? 'uncategorized';
            });
            $uncategorized = $groupedProducts->pull('uncategorized') ?? collect();
        @endphp

        @foreach($groupedProducts as $categoryId => $categoryProducts)
            @php
                $category = $categoryProducts->first()->category;
            @endphp
            <div style="margin-bottom: 2rem;">
                <h3 style="margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #3498db;">
                    {{ $category ? $category->name : 'Uncategorized' }}
                </h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem;">
                    @foreach($categoryProducts as $product)
                        <div class="card" style="margin: 0; {{ !$product->is_active ? 'opacity: 0.6; background-color: #f5f5f5;' : '' }}">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.75rem;">
                                <div>
                                    <h3 style="margin: 0; font-size: 1.1rem;">{{ $product->name }}</h3>
                                    <p style="margin: 0.25rem 0 0 0; color: #666; font-size: 0.85rem;">#{{ $product->sku }}</p>
                                </div>
                                @if(!$product->is_active)
                                    <span style="padding: 0.25rem 0.5rem; background: #95a5a6; color: white; border-radius: 4px; font-size: 0.75rem;" title="{{ $product->inactive_reason }}">INACTIVE</span>
                                @elseif($product->stock > $product->min_stock)
                                    <span style="padding: 0.25rem 0.5rem; background: #27ae60; color: white; border-radius: 4px; font-size: 0.75rem;">In Stock</span>
                                @elseif($product->stock > 0)
                                    <span style="padding: 0.25rem 0.5rem; background: #f39c12; color: white; border-radius: 4px; font-size: 0.75rem;">Low Stock</span>
                                @else
                                    <span style="padding: 0.25rem 0.5rem; background: #e74c3c; color: white; border-radius: 4px; font-size: 0.75rem;">Out of Stock</span>
                                @endif
                            </div>

                            <div style="margin-bottom: 0.75rem; padding-bottom: 0.75rem; border-bottom: 1px solid #eee;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span style="color: #666;">Price:</span>
                                    <strong>Rp {{ number_format($product->price, 0, ',', '.') }}</strong>
                                </div>
                                <div style="display: flex; justify-content: space-between;">
                                    <span style="color: #666;">Stock:</span>
                                    <strong>
                                        {{ $product->stock }}
                                        @if($product->is_active && $product->isLowStock())
                                            <span style="color: #e74c3c; font-size: 0.85rem;">(Low)</span>
                                        @endif
                                    </strong>
                                </div>
                            </div>

                            <div style="display: flex; gap: 0.5rem;">
                                @if($product->is_active)
                                    <button onclick="showAdjustModal({{ $product->id }}, '{{ $product->name }}')" class="btn btn-primary btn-sm">Stock</button>
                                @endif
                                <button onclick="window.location.href='{{ route('products.edit', $product) }}'" class="btn btn-warning btn-sm">Edit</button>
                                <form method="POST" action="{{ route('products.destroy', $product) }}" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        @if($uncategorized->count() > 0)
            <div style="margin-bottom: 2rem;">
                <h3 style="margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #95a5a6;">
                    Uncategorized
                </h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem;">
                    @foreach($uncategorized as $product)
                        <div class="card" style="margin: 0; {{ !$product->is_active ? 'opacity: 0.6; background-color: #f5f5f5;' : '' }}">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.75rem;">
                                <div>
                                    <h3 style="margin: 0; font-size: 1.1rem;">{{ $product->name }}</h3>
                                    <p style="margin: 0.25rem 0 0 0; color: #666; font-size: 0.85rem;">#{{ $product->sku }}</p>
                                </div>
                                @if(!$product->is_active)
                                    <span style="padding: 0.25rem 0.5rem; background: #95a5a6; color: white; border-radius: 4px; font-size: 0.75rem;" title="{{ $product->inactive_reason }}">INACTIVE</span>
                                @elseif($product->stock > $product->min_stock)
                                    <span style="padding: 0.25rem 0.5rem; background: #27ae60; color: white; border-radius: 4px; font-size: 0.75rem;">In Stock</span>
                                @elseif($product->stock > 0)
                                    <span style="padding: 0.25rem 0.5rem; background: #f39c12; color: white; border-radius: 4px; font-size: 0.75rem;">Low Stock</span>
                                @else
                                    <span style="padding: 0.25rem 0.5rem; background: #e74c3c; color: white; border-radius: 4px; font-size: 0.75rem;">Out of Stock</span>
                                @endif
                            </div>

                            <div style="margin-bottom: 0.75rem; padding-bottom: 0.75rem; border-bottom: 1px solid #eee;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span style="color: #666;">Price:</span>
                                    <strong>Rp {{ number_format($product->price, 0, ',', '.') }}</strong>
                                </div>
                                <div style="display: flex; justify-content: space-between;">
                                    <span style="color: #666;">Stock:</span>
                                    <strong>
                                        {{ $product->stock }}
                                        @if($product->is_active && $product->isLowStock())
                                            <span style="color: #e74c3c; font-size: 0.85rem;">(Low)</span>
                                        @endif
                                    </strong>
                                </div>
                            </div>

                            <div style="display: flex; gap: 0.5rem;">
                                @if($product->is_active)
                                    <button onclick="showAdjustModal({{ $product->id }}, '{{ $product->name }}')" class="btn btn-primary btn-sm">Stock</button>
                                @endif
                                <button onclick="window.location.href='{{ route('products.edit', $product) }}'" class="btn btn-warning btn-sm">Edit</button>
                                <form method="POST" action="{{ route('products.destroy', $product) }}" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @else
        <p style="text-align: center; color: #666; padding: 2rem;">No products found. Add your first product!</p>
    @endif
</div>

<!-- Stock Adjustment Modal -->
<div id="adjustModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="position: relative; background: white; max-width: 500px; margin: 100px auto; padding: 2rem; border-radius: 8px;">
        <h3 id="modalTitle">Adjust Stock</h3>
        <form id="adjustForm" method="POST" style="margin-top: 1rem;">
            @csrf
            <div class="form-group">
                <label>Type</label>
                <select name="type" class="form-control" required>
                    <option value="in">Stock In (Add)</option>
                    <option value="out">Stock Out (Remove)</option>
                    <option value="adjustment">Adjustment (Set Exact)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Quantity</label>
                <input type="number" name="quantity" class="form-control" min="1" required>
            </div>
            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" class="form-control" rows="3"></textarea>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-success">Save</button>
                <button type="button" class="btn btn-danger" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function showAdjustModal(productId, productName) {
    document.getElementById('modalTitle').textContent = 'Adjust Stock - ' + productName;
    document.getElementById('adjustForm').action = '/products/' + productId + '/adjust-stock';
    document.getElementById('adjustModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('adjustModal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('adjustModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>
@endsection
