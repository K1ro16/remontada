@extends('layouts.app')

@section('title', 'Add Category')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="margin: 0;">Add New Category</h2>
        <a href="{{ route('categories.index') }}" class="btn btn-secondary back-btn" aria-label="Back to Categories">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M15 18L9 12L15 6" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span>Back</span>
        </a>
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

    <form action="{{ route('categories.store') }}" method="POST">
        @csrf
        
        <div class="form-group">
            <label for="name">Category Name</label>
            <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required autofocus>
        </div>

        <div class="form-group">
            <label for="prefix">Code</label>
            <input type="text" id="prefix" name="prefix" class="form-control" value="{{ old('prefix') }}" maxlength="10" required style="text-transform: uppercase;">
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
        </div>

        <div style="display: flex; gap: 0.5rem; margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary">Create Category</button>
            <a href="{{ route('categories.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

@section('styles')
<style>
    .back-btn { display: inline-flex; align-items: center; gap: .5rem; padding: .375rem .75rem; border: 1px solid #ced4da; border-radius: .25rem; background-color: #fff; }
    .back-btn:hover { border-color: #b5b5b5; background-color: #f8f9fa; }
    .back-btn svg { display: block; }
</style>
@endsection
