@extends('layouts.app')

@section('title', 'New Sale')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="margin: 0;">Record New Sale</h2>
        <a href="{{ route('sales.index') }}" class="btn btn-secondary">Back</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-error">
            <strong>Please fix the following errors:</strong>
            <ul style="margin-top: 0.5rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('sales.store') }}" method="POST" id="saleForm">
        @csrf
        
        <div class="grid grid-2">
            <div class="form-group">
                <label for="sale_date">Sale Date *</label>
                <input type="date" id="sale_date" name="sale_date" class="form-control" value="{{ old('sale_date', now()->format('Y-m-d')) }}" required style="cursor: pointer;" onclick="this.showPicker && this.showPicker()" onfocus="this.showPicker && this.showPicker()">
            </div>

            <div class="form-group">
                <label for="customer_id">Customer (Optional)</label>
                <select id="customer_id" name="customer_id" class="form-control">
                    <option value="">Customer</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                            {{ $customer->name }} {{ $customer->phone ? '- ' . $customer->phone : '' }}
                        </option>
                    @endforeach
                </select>
                <small style="color: #666;">
                    <a href="{{ route('customers.create') }}" target="_blank" style="color: #3498db;">Add new customer</a>
                </small>
            </div>
        </div>

        <div class="card" style="background: #f8f9fa; margin-bottom: 1.5rem;">
            <h3 style="margin-bottom: 1rem;">Products</h3>
            
            <div id="items-container">
                <!-- Items will be added here -->
            </div>

            <button type="button" class="btn btn-success" onclick="addItem()">+ Add Product</button>
        </div>

        <div class="form-group">
            <label for="discount_display">Discount Optional</label>
            <div style="position: relative;">
                <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #555;">Rp</span>
                <input type="text" id="discount_display" class="form-control" inputmode="numeric" style="padding-left: 36px;" placeholder="0" value="{{ number_format((float) old('discount', 0), 0, ',', '.') }}" oninput="onDiscountInput()">
                <input type="hidden" name="discount" id="discount" value="{{ old('discount', 0) }}">
            </div>
        </div>

        <div class="form-group">
            <label for="notes">Notes (Optional)</label>
            <textarea id="notes" name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
        </div>

        <div class="card" style="background: #e8f4f8; margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                <span style="font-size: 1rem;">Subtotal:</span>
                <strong id="subtotal-display" style="font-size: 1.1rem;">Rp 0</strong>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                <span style="font-size: 1rem;">Tax:</span>
                <strong id="tax-display" style="font-size: 1.1rem;">Rp 0</strong>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                <span style="font-size: 1rem;">Discount:</span>
                <strong id="discount-display" style="font-size: 1.1rem;">Rp 0</strong>
            </div>
            <hr style="margin: 1rem 0; border: none; border-top: 2px solid #3498db;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 1.3rem; font-weight: bold;">Total:</span>
                <strong id="total-display" style="font-size: 1.5rem; color: #27ae60;">Rp 0</strong>
            </div>
        </div>

        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-success">Record Sale</button>
            <a href="{{ route('sales.index') }}" class="btn btn-danger">Cancel</a>
        </div>
    </form>
</div>

<script>
let itemCount = 0;
const products = @json($products);

function addItem() {
    itemCount++;
    const container = document.getElementById('items-container');
    const itemDiv = document.createElement('div');
    itemDiv.id = `item-${itemCount}`;
    itemDiv.className = 'card';
    itemDiv.style.marginBottom = '1rem';
    itemDiv.style.background = 'white';
    
    itemDiv.innerHTML = `
        <div style="display: flex; gap: 1rem; align-items: end;">
            <div class="form-group" style="flex: 2; margin-bottom: 0;">
                <label>Product *</label>
                <select name="items[${itemCount}][product_id]" class="form-control" onchange="updatePrice(${itemCount})" required>
                    <option value="">Select Product</option>
                    ${products.map(p => `<option value="${p.id}" data-price="${p.price}" data-tax="${p.tax_percentage || 0}" data-stock="${p.stock}" ${p.stock <= 0 ? 'disabled style="color: #999;"' : ''}>${p.name} (#${p.sku}) - Stock: ${p.stock}</option>`).join('')}
                </select>
            </div>
            <div class="form-group" style="flex: 1; margin-bottom: 0;">
                <label>Quantity *</label>
                <input type="text" id="quantity-display-${itemCount}" class="form-control" inputmode="numeric" placeholder="0" value="1" oninput="onQuantityInput(${itemCount})" required>
                <input type="hidden" name="items[${itemCount}][quantity]" id="quantity-${itemCount}" value="1" required>
            </div>
            <div class="form-group" style="flex: 1; margin-bottom: 0;">
                <label>Price</label>
                <input type="text" id="price-${itemCount}" class="form-control" readonly style="background-color: #f5f5f5;">
            </div>
            <div class="form-group" style="flex: 1; margin-bottom: 0;">
                <label>Subtotal</label>
                <input type="text" id="subtotal-${itemCount}" class="form-control" readonly style="background-color: #f5f5f5;">
            </div>
            <button type="button" class="btn btn-danger" onclick="removeItem(${itemCount})" style="margin-bottom: 0;">Remove</button>
        </div>
    `;
    
    container.appendChild(itemDiv);
}

function removeItem(id) {
    const item = document.getElementById(`item-${id}`);
    if (item) {
        item.remove();
        calculateTotal();
    }
}

function updatePrice(id) {
    const select = document.querySelector(`#item-${id} select`);
    const option = select.options[select.selectedIndex];
    const price = option.getAttribute('data-price') || 0;
    const stock = parseInt(option.getAttribute('data-stock') || 0);
    
    document.getElementById(`price-${id}`).value = `Rp ${formatNumber(price)}`;
    
    const quantityDisplay = document.getElementById(`quantity-display-${id}`);
    quantityDisplay.dataset.stock = stock;
    quantityDisplay.title = `Max: ${stock}`;
    // Re-validate and format current quantity against new stock
    onQuantityInput(id);
    
    calculateTotal();
}

function onQuantityInput(id) {
    const display = document.getElementById(`quantity-display-${id}`);
    const hidden = document.getElementById(`quantity-${id}`);
    const stock = parseInt(display?.dataset?.stock || '0');
    let raw = (display.value || '').toString();
    let num = parseInt(raw.replace(/\D/g, '')) || 0;
    if (num < 1) num = 1;
    if (stock && num > stock) {
        num = stock;
        display.setCustomValidity(`Stok tersedia: ${stock}`);
        display.reportValidity();
    } else {
        display.setCustomValidity('');
    }
    hidden.value = num;
    display.value = formatNumber(num);
    calculateTotal();
}

function onDiscountInput() {
    const disp = document.getElementById('discount_display');
    const hidden = document.getElementById('discount');
    let num = parseInt((disp.value || '').toString().replace(/\D/g, '')) || 0;
    hidden.value = num;
    disp.value = formatNumber(num);
    calculateTotal();
}

function calculateTotal() {
    let subtotal = 0;
    let totalTax = 0;
    
    // Calculate subtotal and tax from all items
    for (let i = 1; i <= itemCount; i++) {
        const itemDiv = document.getElementById(`item-${i}`);
        if (!itemDiv) continue;
        
        const select = itemDiv.querySelector('select');
        const quantityInput = itemDiv.querySelector(`input[type="hidden"][name$="[quantity]"]`);
        
        if (select.value && quantityInput && quantityInput.value) {
            const option = select.options[select.selectedIndex];
            const price = parseFloat(option.getAttribute('data-price') || 0);
            const taxPercentage = parseFloat(option.getAttribute('data-tax') || 0);
            const quantity = parseInt(quantityInput.value) || 0;
            const itemSubtotal = price * quantity;
            const itemTax = itemSubtotal * (taxPercentage / 100);
            
            subtotal += itemSubtotal;
            totalTax += itemTax;
            document.getElementById(`subtotal-${i}`).value = `Rp ${formatNumber(itemSubtotal)}`;
        }
    }
    
    const discount = parseFloat(document.getElementById('discount').value) || 0;
    const total = subtotal + totalTax - discount;
    
    document.getElementById('subtotal-display').textContent = `Rp ${formatNumber(subtotal)}`;
    document.getElementById('tax-display').textContent = `Rp ${formatNumber(totalTax)}`;
    document.getElementById('discount-display').textContent = `Rp ${formatNumber(discount)}`;
    document.getElementById('total-display').textContent = `Rp ${formatNumber(total)}`;
}

function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

// Add first item on load
document.addEventListener('DOMContentLoaded', function() {
    addItem();
});
</script>
@endsection
