<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Shelve') }}</title>
    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon-v2.ico') }}" type="image/x-icon">
    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="{{ asset('css/vendor/bootstrap-icons.css') }}">

    <!-- CSS Dependencies (locaux) -->
    <link rel="stylesheet" href="{{ asset('css/vendor/bootstrap.min.css') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'apple': ['-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.2s ease-out',
                        'slide-up': 'slideUp 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(8px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' },
                        }
                    },
                    boxShadow: {
                        'apple': '0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24)',
                        'apple-lg': '0 4px 6px rgba(0, 0, 0, 0.07), 0 10px 15px rgba(0, 0, 0, 0.1)',
                        'glass': '0 8px 32px rgba(0, 0, 0, 0.1)',
                    }
                }
            }
        }
    </script>
    <!-- CSS Dependencies (locaux) -->
    <link rel="stylesheet" href="{{ asset('css/vendor/bootstrap.min.css') }}">
    <!-- Suppression globale des ombres -->
    <link rel="stylesheet" href="{{ asset('css/no-shadows.css') }}">
    <!-- Scripts (locaux) -->
    <script src="{{ asset('js/vendor/pdf.min.js') }}"></script>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        /* Apple-style header */
        .apple-header {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .apple-header.scrolled {
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 1px 20px rgba(0, 0, 0, 0.1);
        }

        /* Apple-style buttons */
        .apple-btn {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            padding: 6px 12px;
            font-weight: 500;
            color: #1d1d1f;
            text-decoration: none;
            transition: all 0.2s ease;
            backdrop-filter: blur(10px);
            font-size: 14px;
        }

        .apple-btn:hover {
            background: rgba(255, 255, 255, 1);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            transform: translateY(-1px);
            color: #1d1d1f;
            text-decoration: none;
        }

        .apple-btn:active {
            transform: translateY(0);
        }

        /* Search bar */
        .apple-search {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 16px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            height: 36px;
        }

        .apple-search:focus-within {
            background: rgba(255, 255, 255, 1);
            box-shadow: 0 0 0 4px rgba(0, 122, 255, 0.1);
            border-color: #007AFF;
        }

        /* Navigation */
        .apple-nav-item {
            background: transparent;
            border-radius: 12px;
            padding: 8px 16px;
            color: #1d1d1f;
            text-decoration: none;
            transition: all 0.2s ease;
            font-weight: 400;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .apple-nav-item:hover {
            background: rgba(0, 122, 255, 0.1);
            color: #007AFF;
            text-decoration: none;
        }

        .apple-nav-item.active {
            background: #007AFF;
            color: white;
            font-weight: 500;
        }

        .apple-nav-item i {
            width: 16px;
            text-align: center;
        }

        /* Dropdown */
        .apple-dropdown {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
            padding: 8px;
            min-width: 200px;
        }

        .apple-dropdown-item {
            padding: 8px 12px;
            border-radius: 8px;
            color: #1d1d1f;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s ease;
        }

        .apple-dropdown-item:hover {
            background: rgba(0, 122, 255, 0.1);
            color: #007AFF;
            text-decoration: none;
        }

        /* Badge */
        .apple-badge {
            background: linear-gradient(135deg, #FF6B35, #F7931E);
            color: white;
            padding: 3px 10px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Status indicator */
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        .status-green { background: #34C759; }
        .status-orange { background: #FF9500; }
        .status-red { background: #FF3B30; }
        .status-gray { background: #8E8E93; }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Icon button */
        .icon-btn {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1d1d1f;
            text-decoration: none;
            transition: all 0.2s ease;
            position: relative;
            font-size: 14px;
        }

        .icon-btn:hover {
            background: rgba(255, 255, 255, 1);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            color: #1d1d1f;
            text-decoration: none;
        }

        .notification-badge {
            position: absolute;
            top: -2px;
            right: -2px;
            background: #FF3B30;
            color: white;
            border-radius: 8px;
            padding: 1px 4px;
            font-size: 9px;
            font-weight: 600;
            min-width: 16px;
            text-align: center;
            line-height: 1.2;
        }

        /* Main navigation grid */
        .nav-grid {
            display: flex;
            align-items: center;
            gap: 1px;
            overflow-x: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .nav-grid::-webkit-scrollbar {
            display: none;
        }

        .nav-item {
            display: flex;
            flex-direction: row;
            align-items: center;
            padding: 8px 12px;
            border-radius: 12px;
            color: #8E8E93;
            text-decoration: none;
            transition: all 0.2s ease;
            white-space: nowrap;
            position: relative;
        }

        .nav-item:hover {
            background: rgba(0, 122, 255, 0.1);
            color: #007AFF;
            text-decoration: none;
        }

        .nav-item.active {
            background: rgba(0, 122, 255, 0.15);
            color: #007AFF;
        }

        .nav-item i {
            font-size: 18px;
            margin-right: 4px;
            margin-bottom: 4px;
        }

        .nav-item span {
            font-size: 11px;
            font-weight: 500;
        }

        /* Submenu */
        .submenu-card {
            background: rgba(248, 249, 250, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 16px;
            box-shadow: none !important;
        }

        .submenu-item, .submenu-card .nav-link {
            padding: 8px 16px;
            border-radius: 8px;
            color: #6B7280;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 4px;
            transition: all 0.2s ease;
        }

        .submenu-item:hover, .submenu-card .nav-link:hover {
            background: rgba(0, 122, 255, 0.1);
            color: #007AFF;
            text-decoration: none;
        }

        .submenu-item.active, .submenu-card .nav-link.active {
            background: #007AFF;
            color: white;
        }

        .submenu-card .nav-link i {
            width: 16px;
            text-align: center;
        }

        /* Hide scrollbar */
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        /* Override pour masquer l'ancien header */
        .two-band-header {
            display: none !important;
        }
    </style>
</head>
<body class="bg-gray-50 font-apple">
@auth
    <!-- Modal pour changer d'organisation -->
    <div class="modal fade" id="orgModal" tabindex="-1" role="dialog" aria-labelledby="orgModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content apple-dropdown" style="border: none; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);">
                <div class="modal-header" style="border-bottom: 1px solid rgba(0, 0, 0, 0.1); padding: 16px;">
                    <h5 class="modal-title text-lg font-semibold text-gray-900" id="orgModalLabel">{{ __('Change Organization') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="background: none; border: none; font-size: 24px; color: #8E8E93;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="padding: 16px;">
                    <form action="{{ route('switch.organisation') }}" method="POST">
                        @csrf
                        <div class="space-y-2">
                            @if(Auth::user() && Auth::user()->organisations && Auth::user()->organisations->count() > 0)
                                @foreach(Auth::user()->organisations as $organisation)
                                    <button type="submit" name="organisation_id" value="{{ $organisation->id }}" class="apple-dropdown-item w-full text-left">
                                        <i class="fas fa-building w-4"></i>
                                        <span>{{ $organisation->name }}</span>
                                    </button>
                                @endforeach
                            @else
                                <div class="apple-dropdown-item">
                                    <i class="fas fa-info-circle w-4"></i>
                                    <span>Aucune organisation disponible</span>
                                </div>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="app">
        <!-- HEADER APPLE MODERNE -->
        <header class="apple-header sticky top-0 z-50 font-apple">
            <!-- Section principale -->
            <div class="px-6 py-2 flex items-center justify-between max-w-7xl mx-auto">
                <!-- Logo et organisation -->
                <div class="flex items-center space-x-3">
                    <a href="{{ url('/') }}" class="flex items-center space-x-2 no-underline">
                        <img src="{{ asset('linear.svg') }}" alt="Shelve Logo" class="w-7 h-7 rounded-lg">
                    </a>
                    <div class="apple-badge">SAI</div>
                    @if(Auth::user()->currentOrganisation)
                    <button onclick="openOrgModal()" class="apple-btn flex items-center space-x-2">
                        <span class="font-semibold">{{ Auth::user()->currentOrganisation->code }}</span>
                        <i class="fas fa-chevron-down text-xs"></i>
                    </button>
                    @else
                    <button onclick="openOrgModal()" class="apple-btn flex items-center space-x-2">
                        <span class="font-semibold">{{ __('No Organisation') }}</span>
                        <i class="fas fa-chevron-down text-xs"></i>
                    </button>
                    @endif
                </div>

                <!-- Barre de recherche -->
                <div class="flex-1 max-w-2xl mx-6">
                    <form class="apple-search flex items-center overflow-hidden" action="{{ route('records.search') }}">
                        <input type="hidden" name="advanced" value="false">
                        <input 
                            class="flex-1 bg-transparent border-none outline-none px-3 py-2 text-gray-700 placeholder-gray-500 text-sm"
                            name="query"
                            type="search"
                            placeholder="{{ __('Search...') }}"
                            value="@if (isset($_GET['query'])) {{ preg_replace('/\s+/', ' ', trim($_GET['query'])) }} @endif"
                        >
                        <select class="bg-transparent border-none outline-none px-2 text-blue-500 font-medium cursor-pointer text-sm" name="search_type">
                            <option value="">{{ __('All') }}</option>
                            <option value="mail" @if(isset($_GET['search_type']) && $_GET['search_type'] == 'mail') selected @endif>{{ __('Mail') }}</option>
                            <option value="record" @if(isset($_GET['search_type']) && $_GET['search_type'] == 'record') selected @endif>{{ __('Records') }}</option>
                            <option value="transferring_record" @if(isset($_GET['search_type']) && $_GET['search_type'] == 'transferring_record') selected @endif>{{ __('Transfer') }}</option>
                        </select>
                        <button class="px-3 py-2 text-blue-500 hover:text-blue-600 transition-colors text-sm" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>

                <!-- Actions utilisateur -->
                <div class="flex items-center space-x-2">
                    <!-- Statut MCP -->
                    <div class="flex items-center space-x-2 apple-btn cursor-pointer" id="mcpStatusIndicator" onclick="showMcpDetails()">
                        <div class="status-dot status-green" id="mcpStatusDot"></div>
                        <span class="text-sm font-medium" id="mcpStatusText">MCP</span>
                    </div>

                    <!-- Notifications -->
                    @can('module_mails_access')
                    <div class="relative">
                        <a href="{{ route('notifications.organisation') }}" class="icon-btn" id="notificationBtn">
                            <i class="fas fa-bell"></i>
                            <span id="header-notification-badge" class="notification-badge hidden">
                                <span id="header-notification-count">0</span>
                            </span>
                        </a>
                    </div>
                    @endcan

                    <!-- Langue -->
                    <div class="relative dropdown">
                        <button class="apple-btn flex items-center space-x-1" id="langBtn" data-toggle="dropdown">
                            <span class="text-sm font-medium">{{ strtoupper(App::getLocale()) }}</span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <div class="dropdown-menu absolute right-0 mt-2 apple-dropdown hidden">
                            <a class="apple-dropdown-item" href="{{ route('language.switch', 'fr') }}">
                                <span>FR</span>
                            </a>
                            <a class="apple-dropdown-item" href="{{ route('language.switch', 'en') }}">
                                <span>EN</span>
                            </a>
                        </div>
                    </div>

                    <!-- Utilisateur -->
                    <div class="relative dropdown">
                        <button class="icon-btn" id="userBtn" data-toggle="dropdown">
                            <i class="fas fa-user"></i>
                        </button>
                        <div class="dropdown-menu absolute right-0 mt-2 apple-dropdown hidden">
                            <div class="apple-dropdown-item">
                                <i class="fas fa-user w-4"></i>
                                <span>{{ Str::limit(Auth::user()->name, 12, '...') }}</span>
                            </div>
                            <a class="apple-dropdown-item" href="#">
                                <i class="fas fa-cog w-4"></i>
                                <span>{{ __('Account Settings') }}</span>
                            </a>
                            <div class="border-t border-gray-100 my-2"></div>
                            <a class="apple-dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt w-4"></i>
                                <span>{{ __('Logout') }}</span>
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                                @csrf
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation principale -->
            <div class="border-t border-gray-200 px-6 py-0.5">
                <nav class="nav-grid max-w-7xl mx-auto">
                    @can('module_bulletin_boards_access')
                    <a href="{{ route('bulletin-boards.index') }}" class="nav-item @if (Request::segment(1) == 'bulletin-boards') active @endif">
                        <i class="fas fa-clipboard"></i>
                        <span>{{ __('Bulletin Boards') }}</span>
                    </a>
                    @endcan

                    @can('module_mails_access')
                    <a href="{{ route('mail-received.index') }}" class="nav-item @if (Request::segment(1) == 'mails') active @endif">
                        <i class="fas fa-envelope"></i>
                        <span>{{ __('Mails') }}</span>
                        <span id="mail-notification-badge" class="notification-badge hidden">
                            <span id="notification-count">0</span>
                        </span>
                    </a>
                    @endcan

                    @can('module_repositories_access')
                    <a href="{{ route('records.index') }}" class="nav-item @if (Request::segment(1) == 'repositories') active @endif">
                        <i class="fas fa-folder"></i>
                        <span>{{ __('Records') }}</span>
                    </a>
                    @endcan

                    @can('module_communications_access')
                    <a href="{{ route('communications.transactions.index') }}" class="nav-item @if (Request::segment(1) == 'communications') active @endif">
                        <i class="fas fa-comments"></i>
                        <span>{{ __('Communications') }}</span>
                    </a>
                    @endcan

                    @can('module_transferrings_access')
                    <a href="{{ route('slips.index') }}" class="nav-item @if (Request::segment(1) == 'transferrings') active @endif">
                        <i class="fas fa-exchange-alt"></i>
                        <span>{{ __('Transfers') }}</span>
                    </a>
                    @endcan

                    @can('module_deposits_access')
                    <a href="{{ route('buildings.index') }}" class="nav-item @if (Request::segment(1) == 'deposits') active @endif">
                        <i class="fas fa-building"></i>
                        <span>{{ __('Deposits') }}</span>
                    </a>
                    @endcan

                    @can('module_tools_access')
                    <a href="{{ route('activities.index') }}" class="nav-item @if (Request::segment(1) == 'tools') active @endif">
                        <i class="fas fa-tools"></i>
                        <span>{{ __('Tools') }}</span>
                    </a>
                    @endcan

                    @can('module_dollies_access')
                    <a href="{{ route('dolly.index') }}" class="nav-item @if (Request::segment(1) == 'dollies') active @endif">
                        <i class="fas fa-shopping-cart"></i>
                        <span>{{ __('Dollies') }}</span>
                    </a>
                    @endcan

                    @can('module_workflow_access')
                    <a href="{{ route('workflows.dashboard') }}" class="nav-item @if (Request::segment(1) == 'workflows') active @endif">
                        <i class="fas fa-project-diagram"></i>
                        <span>{{ __('Workflows') }}</span>
                    </a>
                    @endcan

                    <a href="{{ route('external.contacts.index') }}" class="nav-item @if (Request::segment(1) == 'external') active @endif">
                        <i class="fas fa-users"></i>
                        <span>{{ __('Contacts') }}</span>
                    </a>

                    @can('module_ai_access')
                    <a href="{{ route('ai.chats.index' ) }}" class="nav-item @if (Request::segment(1) == 'ai') active @endif">
                        <i class="fas fa-robot"></i>
                        <span>{{ __('AI') }}</span>
                    </a>
                    @endcan

                    @can('module_public_access')
                    <a href="{{ route('public.users.index') }}" class="nav-item @if (Request::segment(1) == 'public') active @endif">
                        <i class="fas fa-globe"></i>
                        <span>{{ __('Public') }}</span>
                    </a>
                    @endcan

                    @can('module_settings_access')
                    <a href="{{ route('users.show', Auth::user() ) }}" class="nav-item @if (Request::segment(1) == 'settings') active @endif">
                        <i class="fas fa-cog"></i>
                        <span>{{ __('Settings') }}</span>
                    </a>
                    @endcan
                </nav>
            </div>
        </header>

        <main class="py-2">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-2">
                        <div class="card submenu-card h-100">
                            <div class="card-body p-3">
                                <div class="nav flex-column nav-pills">
                                    @switch(Request::segment(1))
                                        @case('ai')
                                            @include('submenu.ai')
                                            @break
                                        @case('public')
                                            @include('submenu.public')
                                            @break
                                        @case('bulletin-boards')
                                            @include('submenu.bulletinboards')
                                            @break
                                        @case('mails')
                                            @include('submenu.mails')
                                            @break
                                        @case('external')
                                            @include('submenu.external')
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
                                        @case('workflows')
                                            @include('submenu.workflows')
                                            @break
                                        @case('public-admin')
                                            @include('submenu.public-admin')
                                            @break
                                    @endswitch
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-10">
                        <div id="container" class="card">
                            @yield('content')
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
@endauth

@guest
    <div id="app">
        <main class="py-3">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div id="container" class="card">
                            @yield('content')
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
@endguest

    @stack('scripts')
    @yield('scripts')
    <!-- Scripts locaux -->
    <script src="{{ asset('js/vendor/jquery-3.5.1.min.js') }}"></script>
    <script src="{{ asset('js/vendor/popper.min.js') }}"></script>
    <script src="{{ asset('js/remove-shadows.js') }}"></script>
    <script src="{{ asset('js/vendor/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/vendor/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('js/vendor/chart.min.js') }}"></script>
    
    <script>
        function openOrgModal() {
            $('#orgModal').modal('show');
        }

        $(document).ready(function() {
            $('.close').on('click', function() {
                $('#orgModal').modal('hide');
            });

            // Mise à jour automatique des badges de notifications
            @can('module_mails_access')
            updateNotificationBadges();
            setInterval(updateNotificationBadges, 30000);
            @endcan

            // Vérification périodique du statut MCP
            updateMcpStatus();
            setInterval(updateMcpStatus, 60000);

            // Effet de scroll sur le header
            $(window).scroll(function() {
                const header = $('.apple-header');
                if ($(window).scrollTop() > 10) {
                    header.addClass('scrolled');
                } else {
                    header.removeClass('scrolled');
                }
            });

            // Gestion des dropdowns
            $('[data-toggle="dropdown"]').on('click', function(e) {
                e.preventDefault();
                const menu = $(this).next('.dropdown-menu');
                $('.dropdown-menu').not(menu).addClass('hidden');
                menu.toggleClass('hidden');
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest('.dropdown').length) {
                    $('.dropdown-menu').addClass('hidden');
                }
            });
        });

        @can('module_mails_access')
        function updateNotificationBadges() {
            fetch('/mails/notifications/unread-count')
                .then(response => response.json())
                .then(data => {
                    const count = data.count;
                    const mainBadge = document.getElementById('mail-notification-badge');
                    const mainCount = document.getElementById('notification-count');
                    const headerBadge = document.getElementById('header-notification-badge');
                    const headerCount = document.getElementById('header-notification-count');
                    
                    if (count > 0) {
                        if (mainBadge && mainCount) {
                            mainBadge.classList.remove('hidden');
                            mainCount.textContent = count > 99 ? '99+' : count;
                        }
                        if (headerBadge && headerCount) {
                            headerBadge.classList.remove('hidden');
                            headerCount.textContent = count > 99 ? '99+' : count;
                        }
                    } else {
                        if (mainBadge) mainBadge.classList.add('hidden');
                        if (headerBadge) headerBadge.classList.add('hidden');
                    }
                })
                .catch(error => console.log('Erreur lors de la récupération des notifications:', error));
        }
        @endcan

        let lastMcpStatusData = null;

        function showMcpDetails() {
            if (lastMcpStatusData) {
                let message = 'Statut MCP:\n\n';
                if (lastMcpStatusData.mcp) {
                    message += `MCP Server: ${lastMcpStatusData.mcp.success ? 'Connecté' : 'Déconnecté'}\n`;
                    if (lastMcpStatusData.mcp.error) {
                        message += `Erreur MCP: ${lastMcpStatusData.mcp.error}\n`;
                    }
                }
                if (lastMcpStatusData.ollama) {
                    message += `Ollama: ${lastMcpStatusData.ollama.success ? 'Connecté' : 'Déconnecté'}\n`;
                    if (lastMcpStatusData.ollama.success && lastMcpStatusData.ollama.models) {
                        message += `Modèles disponibles: ${lastMcpStatusData.ollama.models.length}\n`;
                    }
                    if (lastMcpStatusData.ollama.error) {
                        message += `Erreur Ollama: ${lastMcpStatusData.ollama.error}\n`;
                    }
                }
                alert(message);
            } else {
                alert('Statut MCP: Vérification en cours...');
            }
        }

        function updateMcpStatus() {
            fetch('/api/mcp/status')
                .then(response => response.json())
                .then(data => {
                    lastMcpStatusData = data;
                    const statusDot = document.getElementById('mcpStatusDot');
                    const statusIndicator = document.getElementById('mcpStatusIndicator');
                    
                    if (!statusDot || !statusIndicator) return;

                    // Reset classes
                    statusDot.className = 'status-dot';
                    
                    let status = 'status-gray';
                    let title = 'Statut MCP: Inconnu';

                    if (data.mcp && data.ollama) {
                        if (data.mcp.success && data.ollama.success) {
                            status = 'status-green';
                            title = 'MCP: Opérationnel - Ollama: Connecté';
                        } else if (data.mcp.success && !data.ollama.success) {
                            status = 'status-orange';
                            title = 'MCP: Opérationnel - Ollama: Déconnecté';
                        } else if (!data.mcp.success && data.ollama.success) {
                            status = 'status-orange';
                            title = 'MCP: Déconnecté - Ollama: Connecté';
                        } else {
                            status = 'status-red';
                            title = 'MCP: Déconnecté - Ollama: Déconnecté';
                        }
                    } else {
                        status = 'status-red';
                        title = 'MCP: Service indisponible';
                    }

                    statusDot.classList.add(status);
                    statusIndicator.title = title;
                })
                .catch(error => {
                    console.log('Erreur lors de la vérification du statut MCP:', error);
                    lastMcpStatusData = null;
                    const statusDot = document.getElementById('mcpStatusDot');
                    const statusIndicator = document.getElementById('mcpStatusIndicator');
                    
                    if (statusDot && statusIndicator) {
                        statusDot.className = 'status-dot status-red';
                        statusIndicator.title = 'MCP: Erreur de communication';
                    }
                });
        }
    </script>
</body>
</html>