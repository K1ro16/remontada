<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Nama UMKM')</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            line-height: 1.6;
            display: flex;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            box-shadow: 2px 0 4px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 1.5rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header h1 {
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
        }

        .sidebar-header .business-name {
            font-size: 0.85rem;
            color: #bdc3c7;
        }

        .sidebar-menu {
            flex: 1;
            padding: 1rem 0;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            transition: background 0.3s;
        }

        .sidebar-menu a:hover {
            background: rgba(255,255,255,0.1);
        }

        .sidebar-menu a.active {
            background: rgba(255,255,255,0.2);
            border-left: 3px solid #3498db;
        }

        .sidebar-footer {
            padding: 1rem;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .user-info {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .user-info span {
            font-size: 0.9rem;
        }

        /* Main Content Area */
        .main-wrapper {
            margin-left: 250px;
            flex: 1;
            display: flex;
            flex-direction: column;
            width: calc(100% - 250px);
        }

        .top-bar {
            background: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Buttons */
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        /* Reusable Back button (matches Add Product page) */
        .back-btn { display: inline-flex; align-items: center; gap: .5rem; padding: .375rem .75rem; border: 1px solid #ced4da; border-radius: .25rem; background-color: #fff; color: inherit; }
        .back-btn:hover { border-color: #b5b5b5; background-color: #f8f9fa; }
        .back-btn svg { display: block; }

        .btn-primary {
            background: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background: #2980b9;
        }

        .btn-success {
            background: #27ae60;
            color: white;
        }

        .btn-success:hover {
            background: #229954;
        }

        .btn-danger {
            background: #e74c3c;
            color: white;
        }

        .btn-danger:hover {
            background: #c0392b;
        }

        .btn-warning {
            background: #f39c12;
            color: white;
        }

        .btn-warning:hover {
            background: #d68910;
        }

        /* Cards */
        .card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .card h2 {
            margin-bottom: 1rem;
            color: #2c3e50;
        }

        /* Forms */
        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2c3e50;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .form-control:focus {
            outline: none;
            border-color: #3498db;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }

        tr:hover {
            background: #f8f9fa;
        }

        /* Alerts */
        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Main content */
        main {
            padding: 2rem;
            flex: 1;
        }

        main .container {
            max-width: 100%;
        }

        /* Grid */
        .grid {
            display: grid;
            gap: 1.5rem;
        }

        .grid-2 {
            grid-template-columns: repeat(2, 1fr);
        }

        .grid-3 {
            grid-template-columns: repeat(3, 1fr);
        }

        .grid-4 {
            grid-template-columns: repeat(4, 1fr);
        }
        .grid-5 {
            grid-template-columns: repeat(5, 1fr);
        }
        .grid-6 {
            grid-template-columns: repeat(6, 1fr);
        }
        .grid-7 {
            grid-template-columns: repeat(7, 1fr);
        }

        @media (max-width: 768px) {
            .grid-2, .grid-3, .grid-4, .grid-5, .grid-6, .grid-7 {
                grid-template-columns: 1fr;
            }

            .sidebar {
                width: 200px;
            }

            .main-wrapper {
                margin-left: 200px;
                width: calc(100% - 200px);
            }
        }

        @media (max-width: 576px) {
            .sidebar {
                width: 60px;
            }

            .sidebar-header h1,
            .sidebar-header .business-name,
            .sidebar-menu a span,
            .sidebar-footer span {
                display: none;
            }

            .main-wrapper {
                margin-left: 60px;
                width: calc(100% - 60px);
            }
        }

        /* Stats */
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-card h3 {
            color: #7f8c8d;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .stat-card .value {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
        }

        /* Print: only main content (hide sidebar, buttons, alerts) */
        @media print {
            .sidebar { display: none !important; }
            .main-wrapper { margin-left: 0 !important; width: 100% !important; }
            .btn { display: none !important; }
            .alert { display: none !important; }
        }
    </style>
    @yield('styles')
</head>
<body>
    @if(Auth::check())
    <div class="sidebar">
        <div class="sidebar-header">
            <h1>UMKM System</h1>
            <h1 class="business-name">{{ Auth::user()->currentBusiness->name ?? 'Management System' }}</h1>
        </div>
        
        <div class="sidebar-menu">
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <span>📊 Dashboard</span>
            </a>
            <a href="{{ route('sales.index') }}" class="{{ request()->routeIs('sales.*') ? 'active' : '' }}">
                <span>💰 Sales</span>
            </a>
            <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'active' : '' }}">
                <span>📦 Products</span>
            </a>
            <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.*') ? 'active' : '' }}">
                <span>📁 Categories</span>
            </a>
            <a href="{{ route('activity.index') }}" class="{{ request()->routeIs('activity.*') ? 'active' : '' }}">
                <span>📝 Activity Log</span>
            </a>
                <a href="{{ route('analytics.index') }}" class="{{ request()->routeIs('analytics.*') && !request()->routeIs('analytics.kpi') ? 'active' : '' }}">
                    <span>📈 Analytics</span>
                </a>
                <a href="{{ route('analytics.kpi') }}" class="{{ request()->routeIs('analytics.kpi') ? 'active' : '' }}">
                    <span>🎯 KPI</span>
                </a>
                <a href="{{ route('notifications.index') }}" class="{{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                    <span>🔔 Notifications</span>
                </a>
            @if(Auth::user()->hasRole('pemilik'))
                <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <span>👥 Users</span>
                </a>
            @endif
        </div>

        <div class="sidebar-footer">
            <div class="user-info">
                <span>{{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-danger" style="width: 100%; margin-top: 0.5rem;">Logout</button>
                </form>
            </div>
        </div>
    </div>

    <div class="main-wrapper">
        <main>
            <div class="container">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                    <div class="alert alert-error">{{ session('error') }}</div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
    @else
    <main>
        <div class="container">
            @yield('content')
        </div>
    </main>
    @endif

    @yield('scripts')
</body>
</html>
