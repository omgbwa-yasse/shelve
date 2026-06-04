<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ __('Online Public Access Catalog - Search and browse library resources') }}">

    <title>@yield('title', 'OPAC - ' . config('app.name'))</title>

    <!-- Fonts: Fraunces (editorial serif display) + Source Sans 3 (refined body) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700;9..144,900&family=Source+Sans+3:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- OPAC Editorial Design System — Fraunces + Source Sans 3, paper & terracotta -->
    <style>
        :root {
            /* Accent — terracotta / archive */
            --opac-primary: #9c4221;
            --opac-primary-light: #c05621;
            --opac-primary-dark: #7b341e;
            --opac-secondary: #6f655c;
            --opac-accent: #b7791f; /* warm ochre, used sparingly */

            /* Status Colors (muted, editorial) */
            --opac-success: #2f7a52;
            --opac-danger: #b03a2e;
            --opac-warning: #b7791f;
            --opac-info: #2c6e7f;

            /* Paper neutrals */
            --opac-paper: #faf7f2;          /* page background */
            --opac-paper-deep: #f3ede3;     /* subtle panels */
            --opac-light: #f3ede3;
            --opac-light-gray: #e7ddcf;
            --opac-medium-gray: #6f655c;
            --opac-dark: #1f1b17;           /* ink */
            --opac-white: #ffffff;

            /* Text Colors */
            --opac-text-primary: #2a241e;
            --opac-text-secondary: #6f655c;
            --opac-text-muted: #9b9085;

            /* Borders & Shadows */
            --opac-border-color: #e3d8c8;
            --opac-shadow-sm: 0 1px 2px rgba(43, 33, 20, 0.05);
            --opac-shadow: 0 6px 18px -8px rgba(43, 33, 20, 0.18);
            --opac-shadow-lg: 0 18px 40px -16px rgba(43, 33, 20, 0.28);

            /* Layout */
            --opac-border-radius: 6px;
            --opac-border-radius-lg: 10px;
            --opac-header-height: 76px;
            --opac-nav-height: 54px;

            --opac-serif: 'Fraunces', Georgia, 'Times New Roman', serif;
            --opac-sans: 'Source Sans 3', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: var(--opac-sans);
            background-color: var(--opac-paper);
            /* faint paper grain */
            background-image:
                radial-gradient(circle at 20% 0%, rgba(156, 66, 33, 0.025), transparent 45%),
                radial-gradient(circle at 100% 100%, rgba(183, 121, 31, 0.03), transparent 40%);
            background-attachment: fixed;
            color: var(--opac-text-primary);
            line-height: 1.65;
            font-size: 16px;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
        }

        /* Typography */
        h1, h2, h3, h4, h5, h6 {
            font-family: var(--opac-serif);
            font-weight: 600;
            color: var(--opac-dark);
            line-height: 1.18;
            letter-spacing: -0.01em;
        }

        .display-1, .display-2, .display-3, .display-4, .display-5, .display-6 {
            font-family: var(--opac-serif);
            font-weight: 600;
            letter-spacing: -0.02em;
        }

        a { color: var(--opac-primary); }
        a:hover { color: var(--opac-primary-dark); }

        ::selection { background: rgba(156, 66, 33, 0.16); }

        /* Header Styles — sober masthead on paper, ink rule */
        .opac-masthead {
            background: var(--opac-paper);
            color: var(--opac-dark);
            padding: 0.85rem 0;
            border-bottom: 1px solid var(--opac-border-color);
            box-shadow: var(--opac-shadow-sm);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .opac-logo {
            font-family: var(--opac-serif);
            font-size: 1.7rem;
            font-weight: 700;
            color: var(--opac-dark);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            letter-spacing: -0.02em;
        }

        .opac-logo:hover {
            color: var(--opac-primary);
            opacity: 1;
        }

        .opac-logo-icon {
            width: 44px;
            height: 44px;
            background: var(--opac-primary);
            color: #fff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            box-shadow: var(--opac-shadow-sm);
        }

        .opac-tagline {
            font-size: 0.72rem;
            color: var(--opac-text-muted);
            font-family: var(--opac-sans);
            font-weight: 500;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-top: 1px;
        }

        .opac-user-menu {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .opac-user-btn {
            background: transparent;
            border: 1px solid var(--opac-border-color);
            color: var(--opac-text-primary);
            padding: 0.5rem 1.15rem;
            border-radius: var(--opac-border-radius);
            font-size: 0.92rem;
            font-weight: 600;
            transition: all 0.18s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .opac-user-btn:hover {
            background: var(--opac-primary);
            border-color: var(--opac-primary);
            color: #fff;
        }

        /* Register/primary call-to-action variant in masthead */
        .opac-user-menu .opac-user-btn:last-child:not(.dropdown-toggle) {
            background: var(--opac-primary);
            border-color: var(--opac-primary);
            color: #fff;
        }
        .opac-user-menu .opac-user-btn:last-child:not(.dropdown-toggle):hover {
            background: var(--opac-primary-dark);
            border-color: var(--opac-primary-dark);
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
            padding: 0.95rem 1.25rem 0.95rem 3.25rem;
            border: 1px solid var(--opac-border-color);
            border-radius: var(--opac-border-radius);
            font-size: 1.02rem;
            font-family: var(--opac-sans);
            transition: all 0.2s ease;
            background: #fff;
            color: var(--opac-text-primary);
        }

        .opac-search-input::placeholder { color: var(--opac-text-muted); }

        .opac-search-input:focus {
            outline: none;
            border-color: var(--opac-primary);
            box-shadow: 0 0 0 3px rgba(156, 66, 33, 0.12);
        }

        .opac-search-btn {
            padding: 0.95rem 2rem;
            background: var(--opac-primary);
            color: white;
            border: 1px solid var(--opac-primary);
            border-radius: var(--opac-border-radius);
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
            border-color: var(--opac-primary-dark);
        }

        .opac-search-btn:active {
            transform: translateY(1px);
        }

        /* Card Styles — flat editorial panels */
        .opac-card {
            background: #fff;
            border: 1px solid var(--opac-border-color);
            border-radius: var(--opac-border-radius);
            box-shadow: var(--opac-shadow-sm);
            overflow: hidden;
            transition: border-color 0.25s ease, box-shadow 0.25s ease, transform 0.25s ease;
        }

        .opac-card:hover {
            box-shadow: var(--opac-shadow);
            border-color: var(--opac-light-gray);
            transform: translateY(-2px);
        }

        .opac-card-header {
            background: var(--opac-paper-deep);
            color: var(--opac-dark);
            padding: 0.9rem 1.5rem;
            font-family: var(--opac-serif);
            font-weight: 600;
            font-size: 1.05rem;
            border-bottom: 1px solid var(--opac-border-color);
            display: flex;
            align-items: center;
            gap: 0.65rem;
        }

        .opac-card-header i {
            font-size: 1.05rem;
            color: var(--opac-primary);
        }

        .opac-card-body {
            padding: 1.5rem;
        }

        .opac-card-simple {
            background: #fff;
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
            border: 1px solid var(--opac-primary);
        }

        /* Button Styles */
        .btn-opac-primary {
            background: var(--opac-primary);
            color: white;
            border: 1px solid var(--opac-primary);
            padding: 0.6rem 1.5rem;
            border-radius: var(--opac-border-radius);
            font-weight: 600;
            transition: all 0.18s ease;
        }

        .btn-opac-primary:hover {
            background: var(--opac-primary-dark);
            border-color: var(--opac-primary-dark);
            color: white;
        }

        .btn-opac-outline {
            background: transparent;
            color: var(--opac-primary);
            border: 1px solid var(--opac-primary);
            padding: 0.6rem 1.5rem;
            border-radius: var(--opac-border-radius);
            font-weight: 600;
            transition: all 0.18s ease;
        }

        .btn-opac-outline:hover {
            background: var(--opac-primary);
            color: white;
            border-color: var(--opac-primary);
        }

        /* Bootstrap primary buttons used across OPAC views -> terracotta */
        .btn-primary, .btn-outline-primary {
            --bs-btn-color: var(--opac-primary);
            --bs-btn-border-color: var(--opac-primary);
            --bs-btn-bg: transparent;
            --bs-btn-hover-bg: var(--opac-primary);
            --bs-btn-hover-border-color: var(--opac-primary);
            --bs-btn-hover-color: #fff;
            --bs-btn-active-bg: var(--opac-primary-dark);
            --bs-btn-active-border-color: var(--opac-primary-dark);
        }
        .btn-primary {
            --bs-btn-color: #fff;
            --bs-btn-bg: var(--opac-primary);
        }
        .text-primary { color: var(--opac-primary) !important; }
        .badge.bg-primary, .opac-badge.bg-primary { background-color: var(--opac-primary) !important; }
        /* Harmonize Bootstrap contextual badges with editorial palette */
        .badge.bg-info, .opac-badge.bg-info { background-color: var(--opac-info) !important; color:#fff !important; }
        .badge.bg-secondary, .opac-badge.bg-secondary { background-color: var(--opac-secondary) !important; color:#fff !important; }
        .badge.bg-success, .opac-badge.bg-success { background-color: var(--opac-success) !important; }
        .badge.bg-light { background-color: var(--opac-paper-deep) !important; color: var(--opac-text-primary) !important; border-color: var(--opac-border-color) !important; }

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

        /* Footer Styles — deep ink with terracotta rule */
        .opac-footer {
            background: #211c17;
            color: #b8ac9c;
            padding: 3.5rem 0 1.5rem;
            margin-top: 4.5rem;
            border-top: 3px solid var(--opac-primary);
        }

        .opac-footer h6 {
            color: #f3ede3;
            font-family: var(--opac-sans);
            font-size: 0.78rem;
            font-weight: 700;
            margin-bottom: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 0.12em;
        }

        .opac-footer a {
            color: #b8ac9c;
            text-decoration: none;
            font-size: 0.92rem;
            transition: color 0.2s ease, padding-left 0.2s ease;
            display: inline-block;
            padding: 0.22rem 0;
        }

        .opac-footer a:hover {
            color: #fff;
            padding-left: 0.4rem;
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

        /* Editorial harmonization for Bootstrap utilities used across OPAC */
        .bg-opac-light { background-color: var(--opac-paper-deep) !important; }
        .text-opac-primary, .text-opac-primary a { color: var(--opac-primary) !important; }
        .text-muted { color: var(--opac-text-muted) !important; }
        .text-success { color: var(--opac-success) !important; }
        .text-info { color: var(--opac-info) !important; }
        .card { border-color: var(--opac-border-color); border-radius: var(--opac-border-radius); }

        .form-control, .form-select {
            border-color: var(--opac-border-color);
            border-radius: var(--opac-border-radius);
            font-family: var(--opac-sans);
            color: var(--opac-text-primary);
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--opac-primary);
            box-shadow: 0 0 0 3px rgba(156, 66, 33, 0.12);
        }

        /* Section heading helper — small terracotta eyebrow above a serif title */
        .opac-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-family: var(--opac-sans);
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--opac-primary);
        }
        .opac-eyebrow::before {
            content: "";
            width: 28px;
            height: 2px;
            background: var(--opac-primary);
            display: inline-block;
        }

        /* Staggered page-load reveal (home/hero) */
        @keyframes opacRise {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .opac-reveal { opacity: 0; animation: opacRise 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .opac-reveal-1 { animation-delay: 0.05s; }
        .opac-reveal-2 { animation-delay: 0.18s; }
        .opac-reveal-3 { animation-delay: 0.31s; }
        @media (prefers-reduced-motion: reduce) {
            .opac-reveal { animation: none; opacity: 1; }
            html { scroll-behavior: auto; }
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
