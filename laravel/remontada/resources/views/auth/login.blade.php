@extends('layouts.app')

@section('title', 'Login')

@section('styles')
<style>
    .auth-container {
        max-width: 400px;
        margin: 3rem auto;
    }

    .auth-card {
        background: white;
        padding: 2rem;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .auth-card h2 {
        text-align: center;
        margin-bottom: 1.5rem;
        color: #2c3e50;
    }

    .form-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 1rem;
    }

    .form-actions a {
        color: #3498db;
        text-decoration: none;
        font-size: 0.9rem;
    }

    .form-actions a:hover {
        text-decoration: underline;
    }
</style>
@endsection

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <h2>Login to Your Account</h2>
        
        @if ($errors->any())
            <div class="alert alert-error">
                <ul style="list-style: none;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="remember"> Remember me
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Login</button>
                <a href="{{ route('register') }}">Don't have an account?</a>
            </div>
        </form>
    </div>
</div>
@endsection
