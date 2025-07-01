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
        /* Styles pour le menu latéral */
        .submenu-card {
            background-color: #f8f9fa;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .submenu-card .nav-link {
            color: #495057;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            margin-bottom: 0.25rem;
            transition: all 0.2s ease-in-out;
        }

        .submenu-card .nav-link:hover {
            background-color: #e9ecef;
            color: #212529;
        }

        .submenu-card .nav-link.active {
            background-color: #007bff;
            color: white;
        }

        .submenu-card .nav-link i {
            margin-right: 0.5rem;
            font-size: 1rem;
        }

        /* Animation sur hover */
        .submenu-card .nav-link:hover i {
            transform: translateX(3px);
            transition: transform 0.2s ease-in-out;
        }
    </style>
</head>

<body>
@auth
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
                            @if(Auth::user() && Auth::user()->organisations && Auth::user()->organisations->count() > 0)
                                @foreach(Auth::user()->organisations as $organisation)
                                    <button type="submit" name="organisation_id" value="{{ $organisation->id }}" class="list-group-item list-group-item-action">
                                        <i class="bi bi-building mr-2"></i> {{ $organisation->name }}
                                    </button>
                                @endforeach
                            @else
                                <div class="list-group-item">
                                    <i class="bi bi-info-circle mr-2"></i> Aucune organisation disponible
                                </div>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="app">
        <!-- Header Single-Line -->
        <header class="single-line-header">
            <div class="header-container">
                <!-- Logo -->
                <a href="{{ url('/') }}" class="header-logo">
                    <img src="{{ asset('linear.svg') }}" alt="Shelve Logo" class="bg-light p-1 rounded">
                </a>

                <!-- Organisation (cliquable pour modal) -->
                @if(Auth::user()->currentOrganisation)
                <a href="javascript:void(0)" class="header-org" onclick="openOrgModal()">
                    <span><strong>({{ Auth::user()->currentOrganisation->code }})  {{ Str::limit(Auth::user()->currentOrganisation->name, 20, '...') }}</strong></span>
                </a>
                @else
                <a href="javascript:void(0)" class="header-org" onclick="openOrgModal()">
                    <span><strong>{{ __('No Organisation') }}</strong></span>
                </a>
                @endif

                <!-- Navigation principale -->
                <nav class="header-nav">
                    @can('module_bulletin_boards_access')
                    <div class="header-nav-item">
                        <a class="header-nav-link @if (Request::segment(1) == 'bulletin-boards') active @endif" href="{{ route('bulletin-boards.index') }}">
                            <i class="bi bi-card-text" style="font-size: 1.5rem;"></i>
                        </a>
                    </div>
                    @endcan
                    @can('module_mails_access')
                    <div class="header-nav-item">
                        <a class="header-nav-link @if (Request::segment(1) == 'mails') active @endif position-relative" href="{{ route('mail-received.index') }}">
                            <i class="bi bi-envelope" style="font-size: 1.5rem;"></i>
                            <!-- Badge de notifications -->
                            <span id="mail-notification-badge" class="position-absolute badge badge-danger" style="top: -5px; right: -10px; font-size: 0.7rem; display: none;">
                                <span id="notification-count">0</span>
                            </span>
                        </a>
                    </div>
                    @endcan
                    @can('module_repositories_access')
                    <div class="header-nav-item">
                        <a class="header-nav-link @if (Request::segment(1) == 'repositories') active @endif" href="{{ route('records.index') }}">
                            <i class="bi bi-folder" style="font-size: 1.5rem;"></i>
                        </a>
                    </div>
                    @endcan
                    @can('module_communications_access')
                    <div class="header-nav-item">
                        <a class="header-nav-link @if (Request::segment(1) == 'communications') active @endif" href="{{ route('communications.transactions.index') }}">
                            <i class="bi bi-chat-dots" style="font-size: 1.5rem;"></i>
                        </a>
                    </div>
                    @endcan
                    @can('module_transferrings_access')
                    <div class="header-nav-item">
                        <a class="header-nav-link @if (Request::segment(1) == 'transferrings') active @endif" href="{{ route('slips.index') }}">
                            <i class="bi bi-arrow-left-right" style="font-size: 1.5rem;"></i>
                        </a>
                    </div>
                    @endcan
                    @can('module_deposits_access')
                    <div class="header-nav-item">
                        <a class="header-nav-link @if (Request::segment(1) == 'deposits') active @endif" href="{{ route('buildings.index') }}">
                            <i class="bi bi-building" style="font-size: 1.5rem;"></i>
                        </a>
                    </div>
                    @endcan
                    @can('module_tools_access')
                    <div class="header-nav-item">
                        <a class="header-nav-link @if (Request::segment(1) == 'tools') active @endif" href="{{ route('activities.index') }}">
                            <i class="bi bi-tools" style="font-size: 1.5rem;"></i>
                        </a>
                    </div>
                    @endcan
                    @can('module_dollies_access')
                    <div class="header-nav-item">
                        <a class="header-nav-link @if (Request::segment(1) == 'dollies') active @endif" href="{{ route('dolly.index') }}">
                            <i class="bi bi-cart3" style="font-size: 1.5rem;"></i>
                        </a>
                    </div>
                    @endcan
                    @can('module_ai_access')
                    <div class="header-nav-item">
                        <a class="header-nav-link @if (Request::segment(1) == 'ai') active @endif" href="{{ route('ai.chats.index' ) }}">
                            <i class="bi bi-robot" style="font-size: 1.5rem;"></i>
                        </a>
                    </div>
                    @endcan
                    @can('module_public_access')
                    <div class="header-nav-item">
                        <a class="header-nav-link @if (Request::segment(1) == 'public') active @endif" href="{{ route('public.users.index') }}">
                            <i class="bi bi-globe" style="font-size: 1.5rem;"></i>
                        </a>
                    </div>
                    @endcan
                    @can('module_settings_access')
                    <div class="header-nav-item">
                        <a class="header-nav-link @if (Request::segment(1) == 'settings') active @endif" href="{{ route('users.show', Auth::user() ) }}">
                            <i class="bi bi-gear" style="font-size: 1.5rem;"></i>
                        </a>
                    </div>
                    @endcan
                </nav>

                <!-- Barre de recherche compacte avec sélecteur fixe -->
                <div class="header-search">
                    <form class="header-search-form" action="{{ route('records.search') }}">
                        <input type="hidden" name="advanced" value="false">
                        <input class="header-search-input"  name="query" type="search" placeholder="{{ __('Search...') }}"
                               value="@if (isset($_GET['query'])) {{ preg_replace('/\s+/', ' ', trim($_GET['query'])) }} @endif">
                        <select class="header-search-select" name="search_type">
                            <option value="">{{ __('All') }}</option>
                            <option value="mail" @if(isset($_GET['search_type']) && $_GET['search_type'] == 'mail') selected @endif>{{ __('Mail') }}</option>
                            <option value="record" @if(isset($_GET['search_type']) && $_GET['search_type'] == 'record') selected @endif>{{ __('Records') }}</option>
                            <option value="transferring_record" @if(isset($_GET['search_type']) && $_GET['search_type'] == 'transferring_record') selected @endif>{{ __('Transfer') }}</option>
                        </select>
                        <button class="header-search-button" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>
                </div>

                <!-- Actions utilisateur -->
                <div class="header-actions">
                    <!-- Notifications -->
                    @can('module_mails_access')
                    <div class="header-action-item">
                        <a href="{{ route('mail-notifications.show') }}" class="header-action-btn position-relative" id="notificationBtn" title="Notifications">
                            <i class="bi bi-bell" style="font-size: 1.2rem;"></i>
                            <span id="header-notification-badge" class="position-absolute badge badge-danger" style="top: -8px; right: -8px; font-size: 0.6rem; display: none;">
                                <span id="header-notification-count">0</span>
                            </span>
                        </a>
                    </div>
                    @endcan

                    <!-- Sélecteur de langue -->
                    <div class="header-action-item">
                        <a href="#" class="header-action-btn" id="langBtn" role="button" data-toggle="dropdown" aria-expanded="false">
                            <span>{{ strtoupper(App::getLocale()) }}</span>
                            <i class="bi bi-chevron-down ml-1" style="font-size: 0.75rem;"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="langBtn">
                            <a class="dropdown-item" href="{{ route('language.switch', 'fr') }}">FR</a>
                            <a class="dropdown-item" href="{{ route('language.switch', 'en') }}">EN</a>
                        </div>
                    </div>



                    <!-- Utilisateur -->
                    <div class="header-action-item">
                        <a href="#" class="header-action-btn" id="userBtn" role="button" data-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle">  </i>
                            <i class="bi bi-chevron-down ml-1" style="font-size: 0.75rem;"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userBtn">
                            <a class="dropdown-item" href="#">
                                <i class="bi bi-person mr-2"></i>{{ Str::limit(Auth::user()->name, 12, '...') }}
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
                    </div>
                </div>
            </div>
        </header>

        <main class="py-3">
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

    <!-- Scripts avec Popper.js inclus -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
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

            // Animation sur hover
            $('.header-nav-link').hover(
                function() {
                    $(this).find('i').css('transform', 'translateY(-2px)');
                },
                function() {
                    $(this).find('i').css('transform', 'translateY(0)');
                }
            );

            // Focus sur le champ de recherche
            $('.header-search-input').focus(function() {
                $(this).closest('.header-search-form').css('background-color', 'rgba(255, 255, 255, 0.2)');
            }).blur(function() {
                $(this).closest('.header-search-form').css('background-color', 'rgba(255, 255, 255, 0.15)');
            });

            // Mise à jour automatique des badges de notifications
            @can('module_mails_access')
            updateNotificationBadges();
            setInterval(updateNotificationBadges, 30000); // Toutes les 30 secondes
            @endcan


        });

        @can('module_mails_access')
        function updateNotificationBadges() {
            fetch('/mails/notifications/unread-count')
                .then(response => response.json())
                .then(data => {
                    const count = data.count;

                    // Badge dans la navigation principale
                    const mainBadge = document.getElementById('mail-notification-badge');
                    const mainCount = document.getElementById('notification-count');

                    // Badge dans le header
                    const headerBadge = document.getElementById('header-notification-badge');
                    const headerCount = document.getElementById('header-notification-count');

                    if (count > 0) {
                        if (mainBadge && mainCount) {
                            mainBadge.style.display = 'inline-block';
                            mainCount.textContent = count > 99 ? '99+' : count;
                        }
                        if (headerBadge && headerCount) {
                            headerBadge.style.display = 'inline-block';
                            headerCount.textContent = count > 99 ? '99+' : count;
                        }
                    } else {
                        if (mainBadge) mainBadge.style.display = 'none';
                        if (headerBadge) headerBadge.style.display = 'none';
                    }
                })
                .catch(error => console.log('Erreur lors de la récupération des notifications:', error));
        }
        @endcan


        });
    </script>
</body>
</html>
