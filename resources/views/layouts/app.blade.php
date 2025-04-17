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

    <div id="app">
        <!-- Header Single-Line -->
        <header class="single-line-header">
            <div class="header-container">
                <!-- Logo -->
                <a href="{{ url('/') }}" class="header-logo">
                    <img src="{{ asset('linear.svg') }}" alt="Shelve Logo" class="bg-light p-1 rounded">
                </a>

                <!-- Organisation (cliquable pour modal) -->
                <a href="javascript:void(0)" class="header-org" onclick="openOrgModal()">
                    <i class="bi bi-building"></i>
                    <span>{{ Auth::user()->currentOrganisation->name ?? __('Not defined') }}</span>
                </a>

                <!-- Navigation principale -->
                <nav class="header-nav">
                    <div class="header-nav-item">
                        <a class="header-nav-link @if (Request::segment(1) == 'bulletin-boards') active @endif" href="{{ route('bulletin-boards.index') }}">
                            <i class="bi bi-card-text"></i> <span>{{ __('Bulletin') }}</span>
                        </a>
                    </div>
                    <div class="header-nav-item">
                        <a class="header-nav-link @if (Request::segment(1) == 'mails') active @endif position-relative" href="{{ route('mail-received.index') }}">
                            <i class="bi bi-envelope"></i> <span>{{ __('Mail') }}</span>
                            <span class="badge">5</span>
                        </a>
                    </div>
                    <div class="header-nav-item">
                        <a class="header-nav-link @if (Request::segment(1) == 'repositories') active @endif" href="{{ route('records.index') }}">
                            <i class="bi bi-folder"></i> <span>{{ __('Repository') }}</span>
                        </a>
                    </div>
                    <div class="header-nav-item">
                        <a class="header-nav-link @if (Request::segment(1) == 'communications') active @endif" href="{{ route('transactions.index') }}">
                            <i class="bi bi-chat-dots"></i> <span>{{ __('Request') }}</span>
                        </a>
                    </div>
                    <div class="header-nav-item">
                        <a class="header-nav-link @if (Request::segment(1) == 'transferrings') active @endif" href="{{ route('slips.index') }}">
                            <i class="bi bi-arrow-left-right"></i> <span>{{ __('Transfer') }}</span>
                        </a>
                    </div>
                    <div class="header-nav-item">
                        <a class="header-nav-link @if (Request::segment(1) == 'deposits') active @endif" href="{{ route('buildings.index') }}">
                            <i class="bi bi-building"></i> <span>{{ __('Building') }}</span>
                        </a>
                    </div>
                    <div class="header-nav-item">
                        <a class="header-nav-link @if (Request::segment(1) == 'tools') active @endif" href="{{ route('activities.index') }}">
                            <i class="bi bi-tools"></i> <span>{{ __('Tool') }}</span>
                        </a>
                    </div>
                    <div class="header-nav-item">
                        <a class="header-nav-link @if (Request::segment(1) == 'dollies') active @endif" href="{{ route('dolly.index') }}">
                            <i class="bi bi-cart3"></i> <span>{{ __('Dolly') }}</span>
                        </a>
                    </div>
                    <div class="header-nav-item">
                        <a class="header-nav-link @if (Request::segment(1) == 'settings') active @endif" href="{{ route('mail-typology.index') }}">
                            <i class="bi bi-gear"></i> <span>{{ __('Setting') }}</span>
                        </a>
                    </div>
                </nav>

                <!-- Barre de recherche compacte avec sélecteur fixe -->
                <div class="header-search">
                    <form class="header-search-form" action="{{ route('records.search') }}">
                        <input type="hidden" name="advanced" value="false">
                        <input class="header-search-input" name="query" type="search" placeholder="{{ __('Search...') }}"
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

                    <!-- Notifications -->
                    <div class="header-action-item">
                        <a href="#" class="header-action-btn position-relative" id="notifBtn" role="button" data-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-bell-fill"></i>
                            <span class="badge">3</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right p-0" aria-labelledby="notifBtn" style="width: 320px">
                            <div class="p-2 border-bottom">
                                <h6 class="mb-0">Notifications (coming soon)</h6>
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
                    </div>

                    <!-- Utilisateur -->
                    <div class="header-action-item">
                        <a href="#" class="header-action-btn" id="userBtn" role="button" data-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i>
                            <span>{{ Auth::user()->name }}</span>
                            <i class="bi bi-chevron-down ml-1" style="font-size: 0.75rem;"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userBtn">
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
                                        @case('portal')
                                            @include('submenu.portal')
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
                                        @default
                                            @include('submenu.mails')
                                    @endswitch
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="@auth col-md-10 @else col-md-12 @endauth">
                        @endauth
                        <div id="container" class="card">
                            @yield('content')
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

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
        });
    </script>
</body>
</html>
