@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="card">
    <h2>Edit User</h2>

    <form method="POST" action="{{ route('users.update', $user) }}">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label for="name">Name *</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $user->name) }}" required>
            @error('name')
                <span style="color: #e74c3c; font-size: 0.9rem;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $user->email) }}" required>
            @error('email')
                <span style="color: #e74c3c; font-size: 0.9rem;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="role_id">Role *</label>
            <select name="role_id" id="role_id" class="form-control" required>
                <option value="">Select Role</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ old('role_id', $currentRole?->id) == $role->id ? 'selected' : '' }}>
                        {{ ucfirst($role->name) }} - {{ $role->description }}
                    </option>
                @endforeach
            </select>
            @error('role_id')
                <span style="color: #e74c3c; font-size: 0.9rem;">{{ $message }}</span>
            @enderror
        </div>

        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-success">Update User</button>
            <a href="{{ route('users.index') }}" class="btn btn-danger">Cancel</a>
        </div>
    </form>
</div>
@endsection
