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

        <div style="display: flex; gap: 0.5rem; margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary">Update Category</button>
            <a href="{{ route('categories.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
