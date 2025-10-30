<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'OPAC - ' . config('app.name'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom OPAC Styles -->
    <style>
        :root {
            --opac-primary: #2563eb;
            --opac-secondary: #64748b;
            --opac-success: #059669;
            --opac-danger: #dc2626;
            --opac-warning: #d97706;
            --opac-info: #0891b2;
            --opac-light: #f8fafc;
            --opac-dark: #1e293b;
        }

        body {
            font-family: 'Figtree', sans-serif;
            background-color: var(--opac-light);
        }

        .opac-header {
            background: linear-gradient(135deg, var(--opac-primary) 0%, var(--opac-info) 100%);
            color: white;
            padding: 1rem 0;
        }

        .opac-nav {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .opac-search-hero {
            background: linear-gradient(135deg, var(--opac-primary) 0%, var(--opac-info) 100%);
            color: white;
            padding: 4rem 0;
        }

        .opac-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: none;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .opac-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        }

        .opac-card-header {
            background: var(--opac-primary);
            color: white;
            padding: 1rem;
            border-radius: 12px 12px 0 0;
            font-weight: 600;
        }

        .opac-search-box .form-control {
            border-radius: 25px;
            border: none;
            padding: 12px 20px;
            font-size: 1.1rem;
        }

        .opac-search-btn {
            border-radius: 25px;
            background: var(--opac-success);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
        }

        .opac-badge {
            background: var(--opac-primary);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .footer {
            background: var(--opac-dark);
            color: #e2e8f0;
            padding: 3rem 0 2rem;
            margin-top: auto;
        }
    </style>

    @stack('styles')
</head>

<body class="d-flex flex-column min-vh-100">
    <!-- Header -->
    <header class="opac-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="h4 mb-0">
                        <i class="fas fa-book me-2"></i>
                        {{ config('app.name', 'OPAC') }}
                    </h1>
                    <p class="mb-0 small opacity-75">{{ __('Online Public Access Catalog') }}</p>
                </div>
                <div class="col-md-6 text-end">
                    @auth('public')
                        <div class="dropdown">
                            <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i>
                                {{ Auth::guard('public')->user()->name }}
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('opac.dashboard') }}">
                                    <i class="fas fa-tachometer-alt me-2"></i>{{ __('Dashboard') }}
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('opac.profile') }}">
                                    <i class="fas fa-user-edit me-2"></i>{{ __('Profile') }}
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('opac.logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-sign-out-alt me-2"></i>{{ __('Logout') }}
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <a href="{{ route('opac.login') }}" class="btn btn-outline-light me-2">
                            <i class="fas fa-sign-in-alt me-1"></i>{{ __('Login') }}
                        </a>
                        <a href="{{ route('opac.register') }}" class="btn btn-light">
                            <i class="fas fa-user-plus me-1"></i>{{ __('Register') }}
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="opac-nav">
        <div class="container">
            <ul class="nav nav-pills py-3">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('opac.search*') ? 'active' : '' }}" href="{{ route('opac.search') }}">
                        <i class="fas fa-search me-1"></i>{{ __('Search') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('opac.records*') ? 'active' : '' }}" href="{{ route('opac.records.index') }}">
                        <i class="fas fa-books me-1"></i>{{ __('Browse Records') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('opac.news*') ? 'active' : '' }}" href="{{ route('opac.news.index') }}">
                        <i class="fas fa-newspaper me-1"></i>{{ __('News') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('opac.events*') ? 'active' : '' }}" href="{{ route('opac.events.index') }}">
                        <i class="fas fa-calendar me-1"></i>{{ __('Events') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('opac.pages*') ? 'active' : '' }}" href="{{ route('opac.pages.index') }}">
                        <i class="fas fa-file-alt me-1"></i>{{ __('Pages') }}
                    </a>
                </li>
                @auth('public')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('opac.dashboard*') ? 'active' : '' }}" href="{{ route('opac.dashboard') }}">
                        <i class="fas fa-tachometer-alt me-1"></i>{{ __('Dashboard') }}
                    </a>
                </li>
                @endauth
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow-1">
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="container mt-3">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="container mt-3">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h6>{{ config('app.name', 'OPAC') }}</h6>
                    <p class="small">{{ __('Your digital library access portal') }}</p>
                </div>
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-6">
                            <h6>{{ __('Quick Links') }}</h6>
                            <ul class="list-unstyled">
                                <li><a href="{{ route('opac.search') }}" class="text-light">{{ __('Search') }}</a></li>
                                <li><a href="{{ route('opac.records.index') }}" class="text-light">{{ __('Browse') }}</a></li>
                                <li><a href="{{ route('opac.feedback.create') }}" class="text-light">{{ __('Feedback') }}</a></li>
                            </ul>
                        </div>
                        <div class="col-6">
                            <h6>{{ __('Support') }}</h6>
                            <ul class="list-unstyled">
                                <li><a href="#" class="text-light">{{ __('Help') }}</a></li>
                                <li><a href="#" class="text-light">{{ __('Contact') }}</a></li>
                                <li><a href="#" class="text-light">{{ __('FAQ') }}</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <hr class="my-4 border-secondary">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 small">&copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}</p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-0 small">{{ __('Powered by Shelve') }}</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- OPAC Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-dismiss alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
