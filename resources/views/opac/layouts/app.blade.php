<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'OPAC - ' . config('app.name', 'Shelve'))</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon-v2.ico') }}" type="image/x-icon">

    <!-- Bootstrap CSS -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- OPAC Specific Styles -->
    <style>
        :root {
            --opac-primary: #2c3e50;
            --opac-secondary: #3498db;
            --opac-success: #27ae60;
            --opac-info: #17a2b8;
            --opac-warning: #f39c12;
            --opac-danger: #e74c3c;
            --opac-light: #ecf0f1;
            --opac-dark: #2c3e50;
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .opac-header {
            background: linear-gradient(135deg, var(--opac-primary) 0%, var(--opac-secondary) 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .opac-header .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: white !important;
        }

        .opac-header .navbar-brand:hover {
            color: #ecf0f1 !important;
        }

        .opac-nav-link {
            color: rgba(255,255,255,0.8) !important;
            margin: 0 10px;
            transition: all 0.3s ease;
        }

        .opac-nav-link:hover {
            color: white !important;
            background-color: rgba(255,255,255,0.1);
            border-radius: 4px;
        }

        .opac-search-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 0;
            text-align: center;
        }

        .opac-search-box {
            max-width: 600px;
            margin: 0 auto;
        }

        .opac-search-input {
            border-radius: 25px;
            border: none;
            padding: 15px 20px;
            font-size: 1.1rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .opac-search-btn {
            border-radius: 25px;
            padding: 15px 30px;
            background-color: var(--opac-success);
            border: none;
            color: white;
            font-weight: bold;
            margin-left: 10px;
            transition: all 0.3s ease;
        }

        .opac-search-btn:hover {
            background-color: #229954;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        }

        .opac-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .opac-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .opac-card-header {
            background-color: var(--opac-primary);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 15px 20px;
        }

        .opac-badge {
            background-color: var(--opac-secondary);
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
        }

        .opac-footer {
            background-color: var(--opac-dark);
            color: #ecf0f1;
            padding: 3rem 0 2rem;
            margin-top: 4rem;
        }

        .opac-footer h5 {
            color: white;
            margin-bottom: 1rem;
        }

        .opac-footer a {
            color: #bdc3c7;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .opac-footer a:hover {
            color: white;
        }

        .opac-stats {
            background-color: white;
            border-radius: 10px;
            padding: 2rem;
            margin: 2rem 0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .opac-stat-item {
            text-align: center;
            padding: 1rem;
        }

        .opac-stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--opac-secondary);
        }

        .opac-stat-label {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .opac-breadcrumb {
            background-color: transparent;
            padding: 1rem 0;
        }

        .opac-breadcrumb .breadcrumb-item + .breadcrumb-item::before {
            content: "›";
            color: var(--opac-secondary);
        }

        .opac-record-detail {
            background-color: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .opac-record-title {
            color: var(--opac-primary);
            margin-bottom: 1rem;
        }

        .opac-field-label {
            font-weight: bold;
            color: var(--opac-dark);
            margin-bottom: 0.5rem;
        }

        .opac-field-value {
            margin-bottom: 1rem;
            padding-left: 1rem;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .opac-search-hero {
                padding: 2rem 0;
            }

            .opac-search-btn {
                margin-left: 0;
                margin-top: 10px;
                width: 100%;
            }
        }
    </style>

    @stack('styles')
</head>

<body>
    <div id="opac-app">
        <!-- Header -->
        <header class="opac-header">
            <nav class="navbar navbar-expand-lg">
                <div class="container">
                    <a class="navbar-brand" href="{{ route('opac.index') }}">
                        <i class="fas fa-books me-2"></i>
                        {{ config('app.name', 'Shelve') }} - OPAC
                    </a>

                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#opacNavbar">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="opacNavbar">
                        <ul class="navbar-nav me-auto">
                            <li class="nav-item">
                                <a class="nav-link opac-nav-link" href="{{ route('opac.dashboard') }}">
                                    <i class="fas fa-home me-1"></i> {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link opac-nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-search me-1"></i> {{ __('Search') }}
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('opac.search.index') }}">
                                        <i class="fas fa-search me-2"></i> {{ __('Advanced Search') }}
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('opac.records.index') }}">
                                        <i class="fas fa-book me-2"></i> {{ __('Browse Catalog') }}
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('opac.search.history') }}">
                                        <i class="fas fa-history me-2"></i> {{ __('Search History') }}
                                    </a></li>
                                </ul>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link opac-nav-link" href="{{ route('opac.news.index') }}">
                                    <i class="fas fa-newspaper me-1"></i> {{ __('News') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link opac-nav-link" href="{{ route('opac.pages.index') }}">
                                    <i class="fas fa-file-alt me-1"></i> {{ __('Pages') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link opac-nav-link" href="{{ route('opac.events.index') }}">
                                    <i class="fas fa-calendar me-1"></i> {{ __('Events') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link opac-nav-link" href="{{ route('opac.feedback.create') }}">
                                    <i class="fas fa-comment-dots me-1"></i> {{ __('Feedback') }}
                                </a>
                            </li>
                        </ul>

                        <!-- Right side menu -->
                        <ul class="navbar-nav ms-auto">
                            <!-- Language Selector -->
                            <li class="nav-item dropdown">
                                <a class="nav-link opac-nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-globe me-1"></i> {{ strtoupper(App::getLocale()) }}
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('language.switch', 'fr') }}">Français</a></li>
                                    <li><a class="dropdown-item" href="{{ route('language.switch', 'en') }}">English</a></li>
                                </ul>
                            </li>

                            <!-- User Authentication -->
                            @guest('public')
                                <li class="nav-item">
                                    <a class="nav-link opac-nav-link" href="{{ route('opac.login') }}">
                                        <i class="fas fa-sign-in-alt me-1"></i> {{ __('Login') }}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link opac-nav-link" href="{{ route('opac.register') }}">
                                        <i class="fas fa-user-plus me-1"></i> {{ __('Register') }}
                                    </a>
                                </li>
                            @else
                                <li class="nav-item dropdown">
                                    <a class="nav-link opac-nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-user me-1"></i> {{ auth('public')->user()->name }}
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('opac.profile') }}">
                                            <i class="fas fa-user-edit me-2"></i> {{ __('My Profile') }}
                                        </a></li>
                                        <li><a class="dropdown-item" href="{{ route('opac.dashboard') }}">
                                            <i class="fas fa-tachometer-alt me-2"></i> {{ __('Dashboard') }}
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="{{ route('opac.reservations.index') }}">
                                            <i class="fas fa-bookmark me-2"></i> {{ __('My Reservations') }}
                                        </a></li>
                                        <li><a class="dropdown-item" href="{{ route('opac.document-requests.index') }}">
                                            <i class="fas fa-file-request me-2"></i> {{ __('Document Requests') }}
                                        </a></li>
                                        <li><a class="dropdown-item" href="{{ route('opac.requests.index') }}">
                                            <i class="fas fa-file-export me-2"></i> {{ __('Other Requests') }}
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="{{ route('opac.feedback.my-feedback') }}">
                                            <i class="fas fa-comments me-2"></i> {{ __('My Feedback') }}
                                        </a></li>
                                        <li><a class="dropdown-item" href="{{ route('opac.search.history') }}">
                                            <i class="fas fa-history me-2"></i> {{ __('Search History') }}
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('opac.logout') }}"
                                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                                <i class="fas fa-sign-out-alt me-2"></i> {{ __('Logout') }}
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                <form id="logout-form" action="{{ route('opac.logout') }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                            @endguest
                        </ul>
                    </div>
                </div>
            </nav>
        </header>

        <!-- Breadcrumb -->
        @if(isset($breadcrumbs))
        <div class="container">
            <nav class="opac-breadcrumb" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('opac.index') }}">{{ __('Home') }}</a></li>
                    @foreach($breadcrumbs as $breadcrumb)
                        @if($loop->last)
                            <li class="breadcrumb-item active" aria-current="page">{{ $breadcrumb['title'] }}</li>
                        @else
                            <li class="breadcrumb-item"><a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['title'] }}</a></li>
                        @endif
                    @endforeach
                </ol>
            </nav>
        </div>
        @endif

        <!-- Main Content -->
        <main>
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="opac-footer">
            <div class="container">
                <div class="row">
                    <div class="col-md-4">
                        <h5>{{ __('About the Archive') }}</h5>
                        <p>{{ __('Access to the digital catalog of our organization. Search and discover our collections.') }}</p>
                    </div>
                    <div class="col-md-4">
                        <h5>{{ __('Quick Links') }}</h5>
                        <ul class="list-unstyled">
                            <li><a href="{{ route('opac.search.index') }}">{{ __('Advanced Search') }}</a></li>
                            <li><a href="{{ route('opac.records.index') }}">{{ __('Browse Collections') }}</a></li>
                            <li><a href="{{ route('opac.document-requests.create') }}">{{ __('Request Documents') }}</a></li>
                            <li><a href="{{ route('opac.feedback.create') }}">{{ __('Send Feedback') }}</a></li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h5>{{ __('Contact') }}</h5>
                        <p>
                            <i class="fas fa-envelope me-2"></i> info@example.org<br>
                            <i class="fas fa-phone me-2"></i> +1 234 567 8900<br>
                            <i class="fas fa-map-marker-alt me-2"></i> 123 Archive Street
                        </p>
                    </div>
                </div>
                <hr class="my-4" style="border-color: #34495e;">
                <div class="row">
                    <div class="col-md-6">
                        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <p>{{ __('Powered by Shelve Archive Management System') }}</p>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    @stack('scripts')
</body>
</html>
