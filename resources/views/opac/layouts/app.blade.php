<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ __('Online Public Access Catalog - Search and browse library resources') }}">

    <title>@yield('title', 'OPAC - ' . config('app.name'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Lora:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom OPAC Styles - Inspired by Koha -->
    <style>
        :root {
            /* Primary Colors - Professional Blue Palette */
            --opac-primary: #004a99;
            --opac-primary-light: #0066cc;
            --opac-primary-dark: #003366;
            --opac-secondary: #6c757d;
            --opac-accent: #ff6b35;

            /* Status Colors */
            --opac-success: #28a745;
            --opac-danger: #dc3545;
            --opac-warning: #ffc107;
            --opac-info: #17a2b8;

            /* Neutral Colors */
            --opac-light: #f8f9fa;
            --opac-light-gray: #e9ecef;
            --opac-medium-gray: #6c757d;
            --opac-dark: #212529;
            --opac-white: #ffffff;

            /* Text Colors */
            --opac-text-primary: #212529;
            --opac-text-secondary: #6c757d;
            --opac-text-muted: #999999;

            /* Borders & Shadows */
            --opac-border-color: #dee2e6;
            --opac-shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.08);
            --opac-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --opac-shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.15);

            /* Layout */
            --opac-border-radius: 8px;
            --opac-border-radius-lg: 12px;
            --opac-header-height: 80px;
            --opac-nav-height: 56px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--opac-light);
            color: var(--opac-text-primary);
            line-height: 1.6;
            font-size: 15px;
        }

        /* Typography */
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Lora', Georgia, serif;
            font-weight: 600;
            color: var(--opac-dark);
            line-height: 1.3;
        }

        .display-1, .display-2, .display-3, .display-4, .display-5, .display-6 {
            font-family: 'Lora', Georgia, serif;
        }

        /* Header Styles - Koha Inspired */
        .opac-masthead {
            background: linear-gradient(135deg, var(--opac-primary) 0%, var(--opac-primary-dark) 100%);
            color: white;
            padding: 0.75rem 0;
            box-shadow: var(--opac-shadow);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .opac-logo {
            font-family: 'Lora', Georgia, serif;
            font-size: 1.75rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .opac-logo:hover {
            color: white;
            opacity: 0.9;
        }

        .opac-logo-icon {
            width: 42px;
            height: 42px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .opac-tagline {
            font-size: 0.8rem;
            opacity: 0.9;
            font-family: 'Inter', sans-serif;
            font-weight: 400;
            margin-top: -4px;
        }

        .opac-user-menu {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .opac-user-btn {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: white;
            padding: 0.5rem 1.25rem;
            border-radius: var(--opac-border-radius);
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .opac-user-btn:hover {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.35);
            color: white;
            transform: translateY(-1px);
        }

        .opac-user-dropdown {
            background: white;
            border: 1px solid var(--opac-border-color);
            border-radius: var(--opac-border-radius);
            box-shadow: var(--opac-shadow-lg);
            min-width: 220px;
        }

        .opac-user-dropdown .dropdown-item {
            padding: 0.65rem 1.25rem;
            font-size: 0.9rem;
            color: var(--opac-text-primary);
            transition: all 0.15s ease;
        }

        .opac-user-dropdown .dropdown-item:hover {
            background: var(--opac-light);
            color: var(--opac-primary);
            padding-left: 1.5rem;
        }

        .opac-user-dropdown .dropdown-item i {
            width: 20px;
            text-align: center;
        }

        /* Navigation Bar - Koha Style */
        .opac-navbar {
            background: white;
            border-bottom: 1px solid var(--opac-border-color);
            box-shadow: var(--opac-shadow-sm);
            position: sticky;
            top: var(--opac-header-height);
            z-index: 999;
        }

        .opac-nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0;
        }

        .opac-nav-links {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
            gap: 0.5rem;
        }

        .opac-nav-item {
            margin: 0;
        }

        .opac-nav-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 1.25rem;
            color: var(--opac-text-primary);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            border-bottom: 3px solid transparent;
            transition: all 0.2s ease;
            position: relative;
        }

        .opac-nav-link:hover {
            color: var(--opac-primary);
            background: var(--opac-light);
        }

        .opac-nav-link.active {
            color: var(--opac-primary);
            border-bottom-color: var(--opac-primary);
            font-weight: 600;
        }

        .opac-nav-link i {
            font-size: 1rem;
        }

        /* Search Box Styles */
        .opac-search-input-group {
            position: relative;
            display: flex;
            align-items: stretch;
            width: 100%;
        }

        .opac-search-icon {
            position: absolute;
            left: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--opac-text-muted);
            font-size: 1.1rem;
            z-index: 10;
        }

        .opac-search-input {
            flex: 1;
            padding: 0.9rem 1.25rem 0.9rem 3.25rem;
            border: 2px solid var(--opac-border-color);
            border-radius: var(--opac-border-radius-lg);
            font-size: 1rem;
            transition: all 0.2s ease;
            background: white;
        }

        .opac-search-input:focus {
            outline: none;
            border-color: var(--opac-primary);
            box-shadow: 0 0 0 4px rgba(0, 74, 153, 0.1);
        }

        .opac-search-btn {
            padding: 0.9rem 2rem;
            background: var(--opac-primary);
            color: white;
            border: none;
            border-radius: var(--opac-border-radius-lg);
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            white-space: nowrap;
        }

        .opac-search-btn:hover {
            background: var(--opac-primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--opac-shadow);
        }

        .opac-search-btn:active {
            transform: translateY(0);
        }

        /* Card Styles - Koha Inspired */
        .opac-card {
            background: white;
            border: 1px solid var(--opac-border-color);
            border-radius: var(--opac-border-radius-lg);
            box-shadow: var(--opac-shadow-sm);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .opac-card:hover {
            box-shadow: var(--opac-shadow);
            transform: translateY(-2px);
        }

        .opac-card-header {
            background: linear-gradient(to right, var(--opac-primary), var(--opac-primary-light));
            color: white;
            padding: 1rem 1.5rem;
            font-weight: 600;
            font-size: 1.05rem;
            border-bottom: none;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .opac-card-header i {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .opac-card-body {
            padding: 1.5rem;
        }

        .opac-card-simple {
            background: white;
            border: 1px solid var(--opac-border-color);
            border-radius: var(--opac-border-radius);
            padding: 1.5rem;
        }

        /* Badge Styles */
        .opac-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.85rem;
            background: var(--opac-primary);
            color: white;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        .opac-badge-success {
            background: var(--opac-success);
        }

        .opac-badge-warning {
            background: var(--opac-warning);
            color: var(--opac-dark);
        }

        .opac-badge-danger {
            background: var(--opac-danger);
        }

        .opac-badge-info {
            background: var(--opac-info);
        }

        .opac-badge-outline {
            background: transparent;
            color: var(--opac-primary);
            border: 2px solid var(--opac-primary);
        }

        /* Button Styles */
        .btn-opac-primary {
            background: var(--opac-primary);
            color: white;
            border: none;
            padding: 0.65rem 1.5rem;
            border-radius: var(--opac-border-radius);
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-opac-primary:hover {
            background: var(--opac-primary-dark);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--opac-shadow);
        }

        .btn-opac-outline {
            background: transparent;
            color: var(--opac-primary);
            border: 2px solid var(--opac-primary);
            padding: 0.65rem 1.5rem;
            border-radius: var(--opac-border-radius);
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-opac-outline:hover {
            background: var(--opac-primary);
            color: white;
            border-color: var(--opac-primary);
        }

        /* Alert Styles */
        .alert {
            border-radius: var(--opac-border-radius);
            border: none;
            padding: 1rem 1.25rem;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert i {
            font-size: 1.25rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
        }

        /* Footer Styles */
        .opac-footer {
            background: var(--opac-dark);
            color: #adb5bd;
            padding: 3rem 0 1.5rem;
            margin-top: 4rem;
            border-top: 4px solid var(--opac-primary);
        }

        .opac-footer h6 {
            color: white;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .opac-footer a {
            color: #adb5bd;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s ease;
            display: inline-block;
            padding: 0.25rem 0;
        }

        .opac-footer a:hover {
            color: white;
            padding-left: 0.5rem;
        }

        .opac-footer .list-unstyled li {
            margin-bottom: 0.5rem;
        }

        .opac-footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 2rem;
            padding-top: 1.5rem;
            font-size: 0.85rem;
        }

        /* Utilities */
        .text-opac-primary {
            color: var(--opac-primary) !important;
        }

        .bg-opac-light {
            background-color: var(--opac-light) !important;
        }

        .border-opac {
            border-color: var(--opac-border-color) !important;
        }

        /* Responsive */
        @media (max-width: 991px) {
            .opac-nav-links {
                flex-direction: column;
                width: 100%;
            }

            .opac-nav-link {
                width: 100%;
                border-bottom: none;
                border-left: 3px solid transparent;
            }

            .opac-nav-link.active {
                border-left-color: var(--opac-primary);
                border-bottom-color: transparent;
            }

            .opac-navbar {
                position: relative;
            }
        }

        @media (max-width: 767px) {
            .opac-logo {
                font-size: 1.35rem;
            }

            .opac-logo-icon {
                width: 36px;
                height: 36px;
            }

            .opac-user-btn {
                padding: 0.4rem 0.9rem;
                font-size: 0.85rem;
            }

            .opac-search-btn {
                padding: 0.75rem 1.25rem;
            }
        }

        /* Loading Animation */
        .opac-loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Smooth Scrolling */
        html {
            scroll-behavior: smooth;
        }
    </style>

    @stack('styles')
</head>

<body class="d-flex flex-column min-vh-100">
    <!-- Masthead Header -->
    <header class="opac-masthead">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between">
                <!-- Logo -->
                <a href="{{ route('opac.index') }}" class="opac-logo">
                    <div class="opac-logo-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div>
                        <div>{{ config('app.name', 'Library OPAC') }}</div>
                        <div class="opac-tagline">{{ __('Online Public Access Catalog') }}</div>
                    </div>
                </a>

                <!-- User Menu -->
                <div class="opac-user-menu">
                    @auth('public')
                        <div class="dropdown">
                            <button class="opac-user-btn dropdown-toggle" type="button" id="userMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle"></i>
                                <span class="d-none d-md-inline">{{ Auth::guard('public')->user()->name }}</span>
                            </button>
                            <ul class="dropdown-menu opac-user-dropdown dropdown-menu-end" aria-labelledby="userMenuButton">
                                <li>
                                    <a class="dropdown-item" href="{{ route('opac.dashboard') }}">
                                        <i class="fas fa-tachometer-alt"></i>
                                        {{ __('My Dashboard') }}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('opac.profile') }}">
                                        <i class="fas fa-user-edit"></i>
                                        {{ __('My Profile') }}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('opac.document-requests.index') }}">
                                        <i class="fas fa-file-request"></i>
                                        {{ __('My Requests') }}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('opac.search.history') }}">
                                        <i class="fas fa-history"></i>
                                        {{ __('Search History') }}
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('opac.logout') }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-sign-out-alt"></i>
                                            {{ __('Logout') }}
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <a href="{{ route('opac.login') }}" class="opac-user-btn">
                            <i class="fas fa-sign-in-alt"></i>
                            <span class="d-none d-sm-inline">{{ __('Login') }}</span>
                        </a>
                        <a href="{{ route('opac.register') }}" class="opac-user-btn">
                            <i class="fas fa-user-plus"></i>
                            <span class="d-none d-sm-inline">{{ __('Register') }}</span>
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation Bar -->
    <nav class="opac-navbar">
        <div class="container">
            <div class="opac-nav-container">
                <ul class="opac-nav-links">
                    <li class="opac-nav-item">
                        <a class="opac-nav-link {{ request()->routeIs('opac.index') || request()->routeIs('opac.search*') ? 'active' : '' }}" href="{{ route('opac.search') }}">
                            <i class="fas fa-search"></i>
                            {{ __('Search') }}
                        </a>
                    </li>
                    <li class="opac-nav-item">
                        <a class="opac-nav-link {{ request()->routeIs('opac.records*') ? 'active' : '' }}" href="{{ route('opac.records.index') }}">
                            <i class="fas fa-book"></i>
                            {{ __('Browse Catalog') }}
                        </a>
                    </li>
                    <li class="opac-nav-item">
                        <a class="opac-nav-link {{ request()->routeIs('opac.news*') ? 'active' : '' }}" href="{{ route('opac.news.index') }}">
                            <i class="fas fa-newspaper"></i>
                            {{ __('News') }}
                        </a>
                    </li>
                    <li class="opac-nav-item">
                        <a class="opac-nav-link {{ request()->routeIs('opac.events*') ? 'active' : '' }}" href="{{ route('opac.events.index') }}">
                            <i class="fas fa-calendar-alt"></i>
                            {{ __('Events') }}
                        </a>
                    </li>
                    <li class="opac-nav-item">
                        <a class="opac-nav-link {{ request()->routeIs('opac.pages*') ? 'active' : '' }}" href="{{ route('opac.pages.index') }}">
                            <i class="fas fa-info-circle"></i>
                            {{ __('Information') }}
                        </a>
                    </li>
                    @auth('public')
                    <li class="opac-nav-item">
                        <a class="opac-nav-link {{ request()->routeIs('opac.dashboard*') ? 'active' : '' }}" href="{{ route('opac.dashboard') }}">
                            <i class="fas fa-user"></i>
                            {{ __('My Account') }}
                        </a>
                    </li>
                    @endauth
                </ul>

                <!-- Quick Search in Navbar (optional) -->
                <div class="d-none d-xl-block ms-auto">
                    <form method="GET" action="{{ route('opac.search.results') }}" class="d-flex">
                        <input type="text" name="q" class="form-control form-control-sm me-2" placeholder="{{ __('Quick search...') }}" style="width: 200px;">
                        <button type="submit" class="btn btn-sm btn-opac-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow-1">
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="container mt-3">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="container mt-3">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        @if(session('warning'))
            <div class="container mt-3">
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>{{ session('warning') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        @if(session('info'))
            <div class="container mt-3">
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle"></i>
                    <div>{{ session('info') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="opac-footer">
        <div class="container">
            <div class="row">
                <!-- About -->
                <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
                    <h6>{{ config('app.name', 'Library OPAC') }}</h6>
                    <p class="mb-3">{{ __('Your gateway to discovering and accessing our digital collections. Search, browse, and request documents online.') }}</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-decoration-none" style="font-size: 1.5rem;" title="{{ __('Facebook') }}">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="#" class="text-decoration-none" style="font-size: 1.5rem;" title="{{ __('Twitter') }}">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-decoration-none" style="font-size: 1.5rem;" title="{{ __('Instagram') }}">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="text-decoration-none" style="font-size: 1.5rem;" title="{{ __('LinkedIn') }}">
                            <i class="fab fa-linkedin"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6 mb-4 mb-lg-0">
                    <h6>{{ __('Discover') }}</h6>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('opac.search') }}"><i class="fas fa-angle-right me-2"></i>{{ __('Search Catalog') }}</a></li>
                        <li><a href="{{ route('opac.records.index') }}"><i class="fas fa-angle-right me-2"></i>{{ __('Browse Collections') }}</a></li>
                        <li><a href="{{ route('opac.news.index') }}"><i class="fas fa-angle-right me-2"></i>{{ __('Latest News') }}</a></li>
                        <li><a href="{{ route('opac.events.index') }}"><i class="fas fa-angle-right me-2"></i>{{ __('Upcoming Events') }}</a></li>
                    </ul>
                </div>

                <!-- My Account -->
                <div class="col-lg-2 col-md-6 mb-4 mb-lg-0">
                    <h6>{{ __('My Account') }}</h6>
                    <ul class="list-unstyled">
                        @auth('public')
                            <li><a href="{{ route('opac.dashboard') }}"><i class="fas fa-angle-right me-2"></i>{{ __('Dashboard') }}</a></li>
                            <li><a href="{{ route('opac.profile') }}"><i class="fas fa-angle-right me-2"></i>{{ __('My Profile') }}</a></li>
                            <li><a href="{{ route('opac.document-requests.index') }}"><i class="fas fa-angle-right me-2"></i>{{ __('My Requests') }}</a></li>
                            <li><a href="{{ route('opac.search.history') }}"><i class="fas fa-angle-right me-2"></i>{{ __('Search History') }}</a></li>
                        @else
                            <li><a href="{{ route('opac.login') }}"><i class="fas fa-angle-right me-2"></i>{{ __('Login') }}</a></li>
                            <li><a href="{{ route('opac.register') }}"><i class="fas fa-angle-right me-2"></i>{{ __('Register') }}</a></li>
                        @endauth
                    </ul>
                </div>

                <!-- Help & Support -->
                <div class="col-lg-4 col-md-6">
                    <h6>{{ __('Help & Support') }}</h6>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('opac.pages.index') }}"><i class="fas fa-angle-right me-2"></i>{{ __('User Guide') }}</a></li>
                        <li><a href="{{ route('opac.feedback.create') }}"><i class="fas fa-angle-right me-2"></i>{{ __('Contact Us') }}</a></li>
                        <li><a href="#"><i class="fas fa-angle-right me-2"></i>{{ __('FAQ') }}</a></li>
                        <li><a href="#"><i class="fas fa-angle-right me-2"></i>{{ __('Accessibility') }}</a></li>
                    </ul>
                    <div class="mt-3">
                        <p class="mb-1"><i class="fas fa-envelope me-2"></i>support@library.org</p>
                        <p class="mb-1"><i class="fas fa-phone me-2"></i>+33 1 23 45 67 89</p>
                    </div>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="opac-footer-bottom">
                <div class="row align-items-center">
                    <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
                        <p class="mb-0">&copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}</p>
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        <p class="mb-0">
                            {{ __('Powered by') }}
                            <a href="#" class="text-white text-decoration-none fw-bold">Shelve</a>
                            |
                            <a href="#" class="text-white text-decoration-none">{{ __('Privacy Policy') }}</a>
                            |
                            <a href="#" class="text-white text-decoration-none">{{ __('Terms of Use') }}</a>
                        </p>
                    </div>
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
