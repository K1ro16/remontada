@extends('layouts.app')

@section('title', 'Edit Category')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="margin: 0;">Edit Category</h2>
        <a href="{{ route('categories.index') }}" class="btn btn-secondary">Back</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-error">
            <strong>Whoops!</strong> There were some problems with your input.
            <ul style="margin-top: 0.5rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('categories.update', $category->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label for="name">Category Name *</label>
            <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $category->name) }}" required autofocus>
        </div>

        <div class="form-group">
            <label for="prefix">Code Prefix * <small>(e.g., FD for Food, BV for Beverage)</small></label>
            <input type="text" id="prefix" name="prefix" class="form-control" value="{{ old('prefix', $category->prefix) }}" maxlength="10" required style="text-transform: uppercase;">
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="form-control" rows="3">{{ old('description', $category->description) }}</textarea>
        </div>

        <div class="form-group" style="margin-top: 0.75rem; background:#f9f9f9; padding:0.75rem; border:1px solid #e0e0e0; border-radius:6px;">
            <label style="display:flex; gap:0.5rem; align-items:center;">
                <input type="checkbox" name="update_skus" value="1" {{ old('update_skus') ? 'checked' : '' }}>
                <strong>Update Product Code</strong>
            </label>
            <small style="color:#666; display:block; margin-top:0.25rem;">If checked, product codes starting with <code>{{ $category->prefix }}</code> will switch to the new Code but keep their numbers (e.g. {{ $category->prefix }}12 → NEWPREFIX12).</small>
        </div>

        <div style="display: flex; gap: 0.5rem; margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary">Update Category</button>
            <a href="{{ route('categories.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
