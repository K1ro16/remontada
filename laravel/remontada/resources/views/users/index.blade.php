@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h2>User Management</h2>
        <a href="{{ route('users.create') }}" class="btn btn-primary">Add New User</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    @php
                        $role = $user->roles->where('pivot.business_id', auth()->user()->current_business_id)->first();
                    @endphp
                    <span style="padding: 0.25rem 0.5rem; background: #3498db; color: white; border-radius: 4px; font-size: 0.85rem;">
                        {{ $role ? ucfirst($role->name) : 'No Role' }}
                    </span>
                </td>
                <td>
                    <form method="GET" action="{{ route('users.edit', $user) }}" style="display: inline; margin-right: 0.5rem;">
                        <button type="submit" class="btn btn-warning">Edit</button>
                    </form>
                    @if($user->id !== auth()->id())
                    <form method="POST" action="{{ route('users.destroy', $user) }}" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align: center;">No users found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
