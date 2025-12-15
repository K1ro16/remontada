@extends('layouts.app')

@section('title', 'Activity Log')

@section('content')
<div class="card">
    <h2>Activity Log</h2>

    <form method="GET" class="grid grid-3" style="margin-bottom:1rem;">
        <div>
            <label for="action">Action</label>
            <select class="form-control" name="action" id="action">
                <option value="">All actions</option>
                @foreach($actions as $a)
                    <option value="{{ $a }}" {{ request('action') === $a ? 'selected' : '' }}>{{ ucfirst($a) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="model">Feature</label>
            <select class="form-control" name="model" id="model">
                <option value="">All models</option>
                @foreach($models as $m)
                    <option value="{{ $m }}" {{ request('model') === $m ? 'selected' : '' }}>{{ $m }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="user_id">User ID</label>
            <select class="form-control" name="user_id" id="user_id">
                <option value="">All users</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ (string)request('user_id') === (string)$u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        <div style="align-self: end;">
            <button class="btn btn-primary" type="submit">Filter</button>
            <a href="{{ route('activity.index') }}" class="btn" style="margin-left:.5rem;">Reset</a>
        </div>
    </form>

    <table>
        <thead>
            <tr>
                <th style="width: 200px;">Time</th>
                <th style="width: 180px;">User</th>
                <th>Message</th>
                <th style="width: 140px;">Action</th>
                <th style="width: 140px;">Feature</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                @php $message = data_get($log->new_values, 'message'); @endphp
                <tr>
                    <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                    <td>{{ $log->user->name }}</td>
                    <td>{{ $message ?: (ucfirst($log->action) . ' ' . $log->model) }}</td>
                    <td>{{ $log->action }}</td>
                    <td>{{ $log->model }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No activity found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:1rem;">
        {{ $logs->links() }}
    </div>
</div>
@endsection
