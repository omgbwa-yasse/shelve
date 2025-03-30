<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Shelve') }}</title>
    
    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon-v2.ico') }}" type="image/x-icon">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    
    <!-- CSS Dependencies - Gardons la même version de Bootstrap -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    
    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.9.359/pdf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    
    <style>
    
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--body-bg);
            color: #0178d4;
            line-height: 1.6;
        }
        
        /* ===== EXPRESSIF NAVBAR STYLES ===== */
        .navbar-brand img {
            height: 38px;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
            transition: transform 0.3s ease;
        }
        
        .navbar-brand:hover img {
            transform: scale(1.05);
        }
        
        .navbar-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            padding: 0.75rem 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .navbar-secondary {
            background-color: var(--white);
            padding: 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-top: 0 !important;
            margin-bottom: 1.5rem;
            border-radius: 0.5rem;
        }
        
        .navbar-nav .nav-item {
            position: relative;
            margin: 0 0.25rem;
        }
        
        .navbar-secondary .nav-link {
            padding: 1rem 1.25rem;
            font-weight: 500;
            position: relative;
            transition: all 0.3s;
            border-radius: 0;
            color: var(--secondary);
            text-align: center;
        }
        
        .navbar-secondary .nav-link:hover {
            color: var(--primary);
            background-color: rgba(1, 120, 212, 0.04);
            transform: translateY(-2px);
        }
        
        .navbar-secondary .nav-link.active {
            color: var(--primary);
            font-weight: 600;
            background-color: rgba(1, 120, 212, 0.08);
        }
        
        .navbar-secondary .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
            border-radius: 2px 2px 0 0;
        }
        
        .navbar-secondary .nav-link i {
            font-size: 1.25rem;
            margin-right: 0.5rem;
            vertical-align: middle;
            transition: transform 0.3s;
        }
        
        .navbar-secondary .nav-link:hover i {
            transform: translateY(-2px);
        }
        
        .navbar-dark .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.9);
            border-radius: 0.375rem;
            padding: 0.625rem 1rem;
            margin: 0 0.25rem;
            transition: all 0.3s;
        }
        
        .navbar-dark .navbar-nav .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }
        
        .search-container {
            position: relative;
            max-width: 550px;
            width: 100%;
        }
        
        .search-container .form-control {
            border-radius: 50rem 0 0 50rem;
            padding-left: 1rem;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: 42px;
        }
        
        .search-container .form-select {
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: 42px;
        }
        
        .search-container .btn {
            border-radius: 0 50rem 50rem 0;
            padding: 0.375rem 1.2rem;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            background: linear-gradient(135deg, var(--light), #e2e8f0);
            color: var(--primary);
            height: 42px;
        }
        
        .search-container .btn:hover {
            background: var(--white);
            color: var(--primary-dark);
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-radius: 0.5rem;
            padding: 0.5rem;
        }
        
        .dropdown-item {
            border-radius: 0.325rem;
            padding: 0.625rem 1rem;
            transition: all 0.2s;
        }
        
        .dropdown-item:hover {
            background-color: rgba(1, 120, 212, 0.08);
            color: var(--primary);
            transform: translateX(3px);
        }
        
        /* Badge for notification */
        .nav-link .badge {
            position: absolute;
            top: 0;
            right: 5px;
            font-size: 0.65rem;
            padding: 0.2rem 0.4rem;
            border-radius: 50%;
            background-color: var(--danger);
            color: var(--white);
            transform: translate(25%, -25%);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                transform: translate(25%, -25%) scale(1);
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
            }
            70% {
                transform: translate(25%, -25%) scale(1.1);
                box-shadow: 0 0 0 10px rgba(239, 68, 68, 0);
            }
            100% {
                transform: translate(25%, -25%) scale(1);
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
            }
        }
        
        /* Organization header bar */
        .org-bar {
            background: linear-gradient(135deg, var(--dark), #1e293b);
            color: var(--white);
            padding: 0.75rem 1rem;
            box-shadow: 0 1px 8px rgba(0, 0, 0, 0.2);
        }
        
        .org-bar .btn-outline-light {
            border-radius: 50rem;
            font-size: 0.875rem;
            transition: all 0.3s;
        }
        
        .org-bar .btn-outline-light:hover {
            background-color: var(--white);
            color: var(--primary);
            transform: translateY(-2px);
        }
        
        /* Card & Container Styles */
        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .card:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }
        
        #container {
            margin-top: 1rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            background-color: var(--white);
            padding: 1.5rem;
        }
        
        /* Form & Input Styling */
        .form-control, .form-select {
            border-radius: 0.375rem;
            padding: 0.6rem 0.75rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03);
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(1, 120, 212, 0.15);
        }
        
        /* Table Styles */
        .table {
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .table th {
            background-color: #f8fafc;
            font-weight: 600;
            border-bottom: 2px solid rgba(0, 0, 0, 0.05);
            padding: 1rem;
        }
        
        .table td {
            padding: 1rem;
            vertical-align: middle;
        }
        
        .table tbody tr {
            transition: all 0.2s;
        }
        
        .table tbody tr:hover {
            background-color: rgba(1, 120, 212, 0.03);
        }
        
        /* Submenu Styles */
        .submenu-card {
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        .nav-pills .nav-link {
            color: var(--secondary);
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            margin-bottom: 0.5rem;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .nav-pills .nav-link:hover {
            background-color: rgba(1, 120, 212, 0.05);
            color: var(--primary);
            transform: translateX(4px);
        }
        
        .nav-pills .nav-link.active {
            background-color: var(--primary);
            color: white;
            box-shadow: 0 4px 8px rgba(1, 120, 212, 0.25);
        }
        
        .nav-pills .nav-link i {
            margin-right: 0.5rem;
            font-size: 1.1rem;
        }
    </style>
</head>

<body>
    @auth
    <div class="org-bar">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <span class="d-flex align-items-center">
                    <i class="bi bi-building mr-2"></i> {{ __('Current Organization') }}: <strong class="ml-2">{{ Auth::user()->currentOrganisation->name ?? __('Not defined') }}</strong>
                </span>
                <button class="btn btn-sm btn-outline-light" onclick="openOrgModal()">
                    <i class="bi bi-arrow-repeat mr-1"></i> {{ __('Change Organization') }}
                </button>
            </div>
        </div>
    </div>
    
    <!-- Modal pour changer d'organisation - CONSERVER LA STRUCTURE ORIGINALE -->
    <div class="modal fade" id="orgModal" tabindex="-1" role="dialog" aria-labelledby="orgModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="orgModalLabel">{{ __('Change Organization') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('switch.organisation') }}" method="POST">
                        @csrf
                        <div class="list-group org-list">
                            @foreach(Auth::user()->organisations as $organisation)
                                <button type="submit" name="organisation_id" value="{{ $organisation->id }}" class="list-group-item list-group-item-action">
                                    <i class="bi bi-building mr-2"></i> {{ $organisation->name }}
                                </button>
                            @endforeach
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endauth

    <div id="app">
        @guest
        @else
        <!-- Main Navigation - CONSERVER LA STRUCTURE ORIGINALE POUR LE COLLAPSE -->
        <nav class="navbar navbar-expand-md navbar-dark bg-primary shadow-sm navbar-primary sticky-top">
            <div class="container-fluid">
                <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
                    <img src="{{ asset('linear.svg') }}" alt="Shelve Logo" class="bg-light p-1 rounded">
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse"
                        data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                        aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Search Form -->
                    <div class="search-container mx-auto">
                        <form class="d-flex" action="{{ route('records.search') }}">
                            <div class="input-group">
                                <input type="hidden" name="advanced" value="false">
                                <input class="form-control" name="query" type="search" placeholder="{{ __('Search') }}"
                                       value="@if (isset($_GET['query'])) {{ preg_replace('/\s+/', ' ', trim($_GET['query'])) }} @endif" aria-label="{{ __('Search') }}">
                                <select class="custom-select" name="search_type" style="max-width: 150px;">
                                    <option value="">{{ __('All') }}</option>
                                    <option value="mail" @if(isset($_GET['search_type']) && $_GET['search_type'] == 'mail') selected @endif>{{ __('Mail') }}</option>
                                    <option value="record" @if(isset($_GET['search_type']) && $_GET['search_type'] == 'record') selected @endif>{{ __('Archives') }}</option>
                                    <option value="transferring_record" @if(isset($_GET['search_type']) && $_GET['search_type'] == 'transferring_record') selected @endif>{{ __('Transferred Archives') }}</option>
                                    <option value="transferring" @if(isset($_GET['search_type']) && $_GET['search_type'] == 'transferring') selected @endif>{{ __('Transfer') }}</option>
                                </select>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-light" type="submit"><i class="bi bi-search"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Language Selector -->
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                {{ strtoupper(App::getLocale()) }}
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('language.switch', 'fr') }}">
                                    FR
                                </a>
                                <a class="dropdown-item" href="{{ route('language.switch', 'en') }}">
                                    EN
                                </a>
                            </div>
                        </li>
                        
                        <!-- Notifications -->
                        <li class="nav-item dropdown">
                            <a class="nav-link position-relative" href="#" role="button" 
                               data-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-bell-fill"></i>
                                <span class="badge">3</span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right p-0" style="width: 320px">
                                <div class="p-2 border-bottom">
                                    <h6 class="mb-0">Notifications (comming soon)</h6>
                                </div>
                                <div class="list-group list-group-flush">
                                    <a href="#" class="list-group-item list-group-item-action p-3">
                                        <div class="d-flex w-100 justify-content-between align-items-center">
                                            <h6 class="mb-1">New mail received</h6>
                                            <small>3 mins ago</small>
                                        </div>
                                        <p class="mb-1 small text-secondary">From: Department of Finance</p>
                                    </a>
                                    <a href="#" class="list-group-item list-group-item-action p-3">
                                        <div class="d-flex w-100 justify-content-between align-items-center">
                                            <h6 class="mb-1">Transfer request approved</h6>
                                            <small>1 hour ago</small>
                                        </div>
                                        <p class="mb-1 small text-secondary">Transfer #TRF-2023-045</p>
                                    </a>
                                    <a href="#" class="list-group-item list-group-item-action p-3">
                                        <div class="d-flex w-100 justify-content-between align-items-center">
                                            <h6 class="mb-1">System maintenance</h6>
                                            <small>5 hours ago</small>
                                        </div>
                                        <p class="mb-1 small text-secondary">Scheduled for tomorrow at 2:00 AM</p>
                                    </a>
                                </div>
                                <div class="p-2 border-top text-center">
                                    <a href="#" class="small text-primary">View all notifications</a>
                                </div>
                            </div>
                        </li>
                        
                        <!-- User Menu -->
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                <i class="bi bi-person-circle mr-1"></i> {{ Auth::user()->name }}
                            </a>

                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="#">
                                    <i class="bi bi-person mr-2"></i> {{ __('Profile') }}
                                </a>
                                <a class="dropdown-item" href="#">
                                    <i class="bi bi-gear mr-2"></i> {{ __('Account Settings') }}
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="bi bi-box-arrow-right mr-2"></i> {{ __('Logout') }}
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Secondary Navigation - CONSERVER LA STRUCTURE DE COLLAPSE BOOTSTRAP 4 -->
        <div class="container-fluid mt-3">
            <nav class="navbar navbar-expand-md navbar-light navbar-secondary mb-4">
                <div class="container-fluid px-0">
                    <button class="navbar-toggler mx-2" type="button" data-toggle="collapse" 
                            data-target="#secondaryNavbar" aria-controls="secondaryNavbar" 
                            aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    
                    <div class="collapse navbar-collapse" id="secondaryNavbar">
                        <ul class="navbar-nav w-100 justify-content-around">
                            <li class="nav-item">
                                <a class="nav-link @if (Request::segment(1) == 'bulletin-board') active @endif"
                                   href="{{ route('bulletin-boards.index') }}">
                                   <i class="bi bi-card-text"></i> Barbillard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link @if (Request::segment(1) == 'mails') active @endif"
                                    href="{{ route('mail-received.index') }}">
                                    <i class="bi bi-envelope"></i> {{ __('Mail') }}
                                    <span class="badge">5</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link @if (Request::segment(1) == 'repositories') active @endif"
                                   href="{{ route('records.index') }}">
                                    <i class="bi bi-folder"></i> {{ __('Repository') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link @if (Request::segment(1) == 'communications') active @endif"
                                   href="{{ route('transactions.index') }}">
                                    <i class="bi bi-chat-dots"></i> {{ __('Request') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link @if (Request::segment(1) == 'transferrings') active @endif"
                                   href="{{ route('slips.index') }}">
                                    <i class="bi bi-arrow-left-right"></i> {{ __('Transfer') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link @if (Request::segment(1) == 'deposits') active @endif"
                                   href="{{ route('buildings.index') }}">
                                    <i class="bi bi-building"></i> {{ __('Building') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link @if (Request::segment(1) == 'tools') active @endif"
                                   href="{{ route('activities.index') }}">
                                    <i class="bi bi-tools"></i> {{ __('Tool') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link @if (Request::segment(1) == 'dollies') active @endif"
                                   href="{{ route('dolly.index') }}">
                                    <i class="bi bi-cart3"></i> {{ __('Dolly') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link @if (Request::segment(1) == 'settings') active @endif"
                                   href="{{ route('mail-typology.index') }}">
                                    <i class="bi bi-gear"></i> {{ __('Setting') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        </div>

        <main class="py-2">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-2">
                        <div class="card submenu-card h-100">
                            <div class="card-body p-3">
                                <div class="nav flex-column nav-pills">
                                    @switch(Request::segment(1))
                                        @case('portal')
                                            @include('submenu.portal')
                                            @break
                                        @case('bulletin-board')
                                            @include('submenu.bulletinboard')
                                            @break
                                        @case('mails')
                                            @include('submenu.mails')
                                            @break
                                        @case('repositories')
                                            @include('submenu.repositories')
                                            @break
                                        @case('communications')
                                            @include('submenu.communications')
                                            @break
                                        @case('accessions')
                                            @include('submenu.accessions')
                                            @break
                                        @case('monitorings')
                                            @include('submenu.monitorings')
                                            @break
                                        @case('settings')
                                            @include('submenu.settings')
                                            @break
                                        @case('deposits')
                                            @include('submenu.deposits')
                                            @break
                                        @case('tools')
                                            @include('submenu.tools')
                                            @break
                                        @case('transferrings')
                                            @include('submenu.transferrings')
                                            @break
                                        @case('dollies')
                                            @include('submenu.dollies')
                                            @break
                                        @default
                                            @include('submenu.mails')
                                    @endswitch
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="@auth col-md-10 @else col-md-12 @endauth">
                        @endguest
                        <div id="container" class="card">
                            @yield('content')
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    @stack('scripts')
    
    <!-- Scripts - CONSERVER LES SCRIPTS ORIGINAUX -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        function openOrgModal() {
            $('#orgModal').modal('show');
        }
        $(document).ready(function() {
            $('.close').on('click', function() {
                $('#orgModal').modal('hide');
            });
            
            // Animation pour navbar
            $('.navbar-secondary .nav-link').hover(
                function() {
                    $(this).find('i').css('transform', 'translateY(-2px)');
                },
                function() {
                    $(this).find('i').css('transform', 'translateY(0)');
                }
            );
        });
    </script>
</body>
</html>