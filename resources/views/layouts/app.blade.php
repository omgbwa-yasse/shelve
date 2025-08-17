<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Shelve') }}</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon-v2.ico') }}" type="image/x-icon">

    <!-- Preload des assets critiques -->
    <link rel="preload" href="{{ asset('linear.svg') }}" as="image">

    <!-- Scripts PDF uniquement (avant Vite) -->
    <script src="{{ asset('js/vendor/pdf.min.js') }}"></script>

    <!-- Vite - gère Bootstrap CSS, JS et les fonts automatiquement -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <!-- Script de préchargement pour éviter FOUC -->
    <script>
        // Assurer que le DOM est prêt avant que Vite charge
        document.addEventListener('DOMContentLoaded', function() {
            console.log('✅ DOM Ready - Vite peut charger ses assets');
        });
    </script>

    <style>
        /* Styles critiques pour éviter FOUC */
        .header-logo img {
            background-color: #f8f9fa !important;
            padding: 0.25rem !important;
            border-radius: 0.25rem !important;
            transition: none !important;
            opacity: 1 !important;
            visibility: visible !important;
        }

        /* Préchargement des icônes Bootstrap */
        .bi {
            font-family: "bootstrap-icons" !important;
        }

        /* Styles pour le menu latéral */
        .submenu-card {
            background-color: #f8f9fa;
            border: none;
            box-shadow: none !important;
        }

        .submenu-card .nav-link {
            color: #495057;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            margin-bottom: 0.25rem;
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

        /* Styles pour les libellés des menus principaux */
        .header-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0 5px;
            padding-top: 2px; /* Ajout d'un léger padding supérieur */
        }

        .header-nav-link {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 8px 4px 0 4px; /* Augmentation du padding supérieur */
            text-decoration: none !important; /* Supprimer le soulignement */
        }

        .header-nav-link:hover {
            text-decoration: none !important; /* Supprimer le soulignement au survol */
        }

        .header-nav-link i {
            margin-bottom: 3px; /* Rapproche l'icône du texte */
            line-height: 1;
        }

        .nav-label {
            font-size: 0.49rem; /* Réduction de 30% par rapport à 0.7rem */
            margin-top: 0; /* Suppression complète de la marge du dessus */
            color: inherit;
            font-weight: 400;
            line-height: 1; /* Réduction de l'espacement des lignes */
        }

        /* Empêcher la mise en gras et le soulignement sur les liens du menu */
        .header-nav-link.active,
        .header-nav-link:hover,
        .header-nav-link:active,
        .header-nav-link:focus {
            text-decoration: none !important;
            font-weight: normal !important;
            color: inherit;
        }

        .header-nav-link.active .nav-label,
        .header-nav-link:hover .nav-label {
            font-weight: 400 !important;
            text-decoration: none !important;
        }

        /* Styles pour l'indicateur de statut MCP */
        .mcp-status-indicator {
            display: flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 12px;
            background-color: rgba(255, 255, 255, 0.1);
            margin-right: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .mcp-status-indicator:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 6px;
            transition: background-color 0.3s ease;
        }

        .status-dot.red {
            background-color: #dc3545; /* Rouge - Non fonctionnel */
        }

        .status-dot.yellow {
            background-color: #ffc107; /* Jaune - En cours */
        }

        .status-dot.orange {
            background-color: #fd7e14; /* Orange - Autres problèmes */
        }

        .status-dot.green {
            background-color: #28a745; /* Vert - Fonctionne */
        }

        .status-dot.grey {
            background-color: #6c757d; /* Gris - Statut inconnu */
        }

        .status-text {
            font-size: 0.75rem;
            color: white;
            font-weight: 500;
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
        <!-- Header Two-Band -->
        <header class="two-band-header">
            <!-- Top Band - Logo, SAI, Organisation, Actions -->
            <div class="top-band">
                <div class="top-band-container">
                    <!-- Logo et SAI -->
                    <div class="left-section">
                        <a href="{{ url('/') }}" class="header-logo">
                            <img src="{{ asset('linear.svg') }}" alt="Shelve Logo"
                                 class="bg-light p-1 rounded"
                                 style="background-color: #f8f9fa !important; padding: 0.25rem !important; border-radius: 0.25rem !important; transition: none !important;">
                        </a>
                        <div class="header-sai">
                            <span>SAI</span>
                        </div>
                    </div>

                    <!-- Organisation -->
                    <div class="center-section">
                        @if(Auth::user()->currentOrganisation)
                        <a href="javascript:void(0)" class="header-org" onclick="openOrgModal()">
                            <span><strong>{{ Auth::user()->currentOrganisation->code }}</strong></span>
                        </a>
                        @else
                        <a href="javascript:void(0)" class="header-org" onclick="openOrgModal()">
                            <span><strong>{{ __('No Organisation') }}</strong></span>
                        </a>
                        @endif
                    </div>

                    <!-- Barre de recherche et Actions utilisateur -->
                    <div class="right-section">
                        <!-- Barre de recherche -->
                        <div class="header-search">
                            <form class="header-search-form" action="#" id="searchForm">
                                <input type="hidden" name="advanced" value="false">
                                <input class="header-search-input" name="query" type="search" placeholder="{{ __('Search...') }}"
                                       value="@if (isset($_GET['query'])) {{ preg_replace('/\s+/', ' ', trim($_GET['query'])) }} @endif">
                                <select class="header-search-select" name="search_type" id="searchType">
                                    <option value="record" @if(isset($_GET['search_type']) && $_GET['search_type'] == 'record') selected @endif>{{ __('Records') }}</option>
                                    <option value="mail" @if(isset($_GET['search_type']) && $_GET['search_type'] == 'mail') selected @endif>{{ __('Mail') }}</option>
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
                                <a href="#" class="header-action-btn" id="langBtn" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
                                <a href="#" class="header-action-btn" id="userBtn" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-person-circle"></i>
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
                </div>
            </div>

            <!-- Bottom Band - Navigation principale -->
            <div class="bottom-band">
                <div class="bottom-band-container">
                    <nav class="main-navigation">
                        @can('module_bulletin_boards_access')
                        <div class="nav-item">
                            <a class="nav-link @if (Request::segment(1) == 'bulletin-boards') active @endif" href="{{ route('bulletin-boards.index') }}">
                                <i class="bi bi-card-text"></i>
                                <span>{{ __('Bulletin Boards') }}</span>
                            </a>
                        </div>
                        @endcan
                        @can('module_mails_access')
                        <div class="nav-item">
                            <a class="nav-link @if (Request::segment(1) == 'mails') active @endif position-relative" href="{{ route('mail-received.index') }}">
                                <i class="bi bi-envelope"></i>
                                <!-- Badge de notifications -->
                                <span id="mail-notification-badge" class="position-absolute badge badge-danger" style="top: -5px; right: -10px; font-size: 0.7rem; display: none;">
                                    <span id="notification-count">0</span>
                                </span>
                                <span>{{ __('Mails') }}</span>
                            </a>
                        </div>
                        @endcan
                        @can('module_repositories_access')
                        <div class="nav-item">
                            <a class="nav-link @if (Request::segment(1) == 'repositories') active @endif" href="{{ route('records.index') }}">
                                <i class="bi bi-folder"></i>
                                <span>{{ __('Records') }}</span>
                            </a>
                        </div>
                        @endcan
                        @can('module_communications_access')
                        <div class="nav-item">
                            <a class="nav-link @if (Request::segment(1) == 'communications') active @endif" href="{{ route('communications.transactions.index') }}">
                                <i class="bi bi-chat-dots"></i>
                                <span>{{ __('Communications') }}</span>
                            </a>
                        </div>
                        @endcan
                        @can('module_transferrings_access')
                        <div class="nav-item">
                            <a class="nav-link @if (Request::segment(1) == 'transferrings') active @endif" href="{{ route('slips.index') }}">
                                <i class="bi bi-arrow-left-right"></i>
                                <span>{{ __('Transfers') }}</span>
                            </a>
                        </div>
                        @endcan
                        @can('module_deposits_access')
                        <div class="nav-item">
                            <a class="nav-link @if (Request::segment(1) == 'deposits') active @endif" href="{{ route('buildings.index') }}">
                                <i class="bi bi-building"></i>
                                <span>{{ __('Deposits') }}</span>
                            </a>
                        </div>
                        @endcan
                        @can('module_tools_access')
                        <div class="nav-item">
                            <a class="nav-link @if (Request::segment(1) == 'tools') active @endif" href="{{ route('activities.index') }}">
                                <i class="bi bi-tools"></i>
                                <span>{{ __('Tools') }}</span>
                            </a>
                        </div>
                        @endcan
                        @can('module_dollies_access')
                        <div class="nav-item">
                            <a class="nav-link @if (Request::segment(1) == 'dollies') active @endif" href="{{ route('dolly.index') }}">
                                <i class="bi bi-cart3"></i>
                                <span>{{ __('Dollies') }}</span>
                            </a>
                        </div>
                        @endcan
                        @can('module_workflow_access')
                        <div class="nav-item">
                            <a class="nav-link @if (Request::segment(1) == 'workflows') active @endif" href="{{ route('workflows.instances.index') }}">
                                <i class="bi bi-diagram-3"></i>
                                <span>{{ __('Workflows') }}</span>
                            </a>
                        </div>
                        @endcan
                        <!-- External contacts/organizations module -->
                        <div class="nav-item">
                            <a class="nav-link @if (Request::segment(1) == 'external') active @endif" href="{{ route('external.contacts.index') }}">
                                <i class="bi bi-people"></i>
                                <span>{{ __('Contacts') }}</span>
                            </a>
                        </div>
                        @can('module_public_access')
                        <div class="nav-item">
                            <a class="nav-link @if (Request::segment(1) == 'public') active @endif" href="{{ route('public.users.index') }}">
                                <i class="bi bi-globe"></i>
                                <span>{{ __('Public') }}</span>
                            </a>
                        </div>
                        @endcan
                        @can('module_settings_access')
                        <div class="nav-item">
                            <a class="nav-link @if (Request::segment(1) == 'settings') active @endif" href="{{ route('users.show', Auth::user() ) }}">
                                <i class="bi bi-gear"></i>
                                <span>{{ __('Settings') }}</span>
                            </a>
                        </div>
                        @endcan



                    </nav>
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

    <!-- Scripts complémentaires uniquement (Bootstrap/jQuery déjà dans Vite) -->
    <script src="{{ asset('js/vendor/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('js/vendor/chart.min.js') }}"></script>

    <script>
        function openOrgModal() {
            // Support Bootstrap 5 API; fallback to jQuery if available
            try {
                if (window.bootstrap && bootstrap.Modal) {
                    var modalElement = document.getElementById('orgModal');
                    var modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);
                    modalInstance.show();
                    return;
                }
            } catch (e) {}

            if (typeof $ !== 'undefined' && typeof $('#orgModal').modal === 'function') {
                $('#orgModal').modal('show');
            } else if (document.getElementById('orgModal')) {
                // Minimal fallback
                document.getElementById('orgModal').classList.add('show');
                document.getElementById('orgModal').style.display = 'block';
                document.body.classList.add('modal-open');
            }
        }

    /**
     * Met à jour les badges de notifications
     */

        // S'assurer que jQuery est disponible
        function initializeLayoutScripts() {
            if (typeof $ === 'undefined') {
                if (typeof window.jQuery !== 'undefined') {
                    window.$ = window.jQuery;
                } else {
                    console.warn('jQuery non disponible - certaines fonctionnalités pourraient ne pas marcher');
                    return;
                }
            }

            // Close button handled by data-bs-dismiss; add defensive fallback
            $(document).on('click', '[data-bs-dismiss="modal"]', function() {
                if (typeof $('#orgModal').modal === 'function') {
                    $('#orgModal').modal('hide');
                } else if (window.bootstrap && bootstrap.Modal) {
                    var modalElement = document.getElementById('orgModal');
                    var instance = bootstrap.Modal.getOrCreateInstance(modalElement);
                    instance.hide();
                } else {
                    $('#orgModal').removeClass('show').hide();
                    $('body').removeClass('modal-open');
                }
            });

            // Gestion de la soumission du formulaire de recherche
            $('#searchForm').on('submit', function(e) {
                e.preventDefault();

                var searchType = $('#searchType').val();
                var query = $('input[name="query"]').val();
                var advanced = $('input[name="advanced"]').val();

                // Construction de l'URL selon le type de recherche
                var actionUrl = '';
                var params = new URLSearchParams();
                params.append('query', query);
                params.append('advanced', advanced);
                params.append('search_type', searchType);

                if (searchType === 'mail') {
                    actionUrl = '{{ route("mails.search") }}';
                } else if (searchType === 'record') {
                    actionUrl = '{{ route("records.search") }}';
                }

                // Redirection avec les paramètres
                if (actionUrl) {
                    window.location.href = actionUrl + '?' + params.toString();
                }
            });

            // Focus sur le champ de recherche - maintenir le fond blanc
            $('.header-search-input').focus(function() {
                $(this).closest('.header-search-form').css('background-color', 'white');
            }).blur(function() {
                $(this).closest('.header-search-form').css('background-color', 'white');
            });

            // Mise à jour automatique des badges de notifications
            @can('module_mails_access')
            updateNotificationBadges();
            setInterval(updateNotificationBadges, 30000); // Toutes les 30 secondes
            @endcan

            // Retiré: suivi du statut MCP
        }

        // Initialiser les scripts quand le DOM est prêt et que Vite a chargé les dépendances
        document.addEventListener('DOMContentLoaded', function() {
            // Attendre un peu que Vite charge jQuery
            setTimeout(initializeLayoutScripts, 200);
        });



    </script>
</body>
</html>
