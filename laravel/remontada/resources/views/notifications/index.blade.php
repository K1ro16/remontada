@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:.5rem;">
        <h2 style="margin:0;">Notifications</h2>
        <a href="{{ route('dashboard') }}" class="back-btn" aria-label="Back to Dashboard">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M15 18L9 12L15 6" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span>Back</span>
        </a>
    </div>

    <div class="card" style="margin-bottom:1rem;">
        <h3>Current Alerts</h3>
        @if(!empty($currentAlerts))
            <ul>
                @foreach($currentAlerts as $a)
                    <li><strong>{{ ucfirst($a['severity']) }}</strong> — {{ $a['type'] }}: {{ $a['message'] }}</li>
                @endforeach
            </ul>
        @else
            <p style="color:#7f8c8d;">No KPI alerts right now.</p>
        @endif
    </div>

    <div class="card" style="margin-bottom:1rem;">
        <h3>History</h3>
        @if($history->count() > 0)
            <table class="table">
                <thead><tr><th>Date</th><th>Month</th><th>Type</th><th>Severity</th><th>Message</th></tr></thead>
                <tbody>
                @foreach($history as $n)
                    <tr>
                        <td>{{ $n->created_at->format('Y-m-d H:i') }}</td>
                        <td>{{ $n->month }}</td>
                        <td>{{ str_replace('_',' ', ucfirst($n->type)) }}</td>
                        <td>{{ ucfirst($n->severity) }}</td>
                        <td>{{ $n->message }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div style="margin-top:.5rem;">{{ $history->links() }}</div>
        @else
            <p style="color:#7f8c8d;">No history yet.</p>
        @endif
    </div>

    
@endsection

@section('styles')
<style>
.alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
</style>
@endsection
