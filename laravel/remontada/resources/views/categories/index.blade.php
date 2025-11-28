@extends('layouts.app')

@section('title', 'Categories')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="margin: 0;">Product Categories</h2>
        <a href="{{ route('categories.create') }}" class="btn btn-primary">Add Category</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    @if($categories->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Prefix</th>
                    <th>Description</th>
                    <th>Products</th>
                    <th style="width: 200px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $category)
                    <tr>
                        <td><strong>{{ $category->name }}</strong></td>
                        <td><span style="font-family: monospace; background: #e3f2fd; padding: 0.25rem 0.5rem; border-radius: 4px;">{{ $category->prefix }}</span></td>
                        <td>{{ $category->description ?? '-' }}</td>
                        <td>{{ $category->products_count }} products</td>
                        <td>
                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                <button type="button" onclick="window.location='{{ route('categories.edit', $category->id) }}'" class="btn btn-warning btn-sm" style="flex: 1; text-align: center; display: block;">Edit</button>
                                @if($category->products_count == 0)
                                    <form action="{{ route('categories.destroy', $category->id) }}" method="POST" style="flex: 1;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this category?')" style="width: 100%;">Delete</button>
                                    </form>
                                @else
                                    <button class="btn btn-danger btn-sm" disabled title="Cannot delete category with products" style="flex: 1;">Delete</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="text-align: center; color: #666; padding: 2rem;">No categories found. Create your first category to organize products.</p>
    @endif
</div>
@endsection
