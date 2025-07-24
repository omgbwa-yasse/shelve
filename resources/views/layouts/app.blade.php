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

    <!-- Icons (locaux) -->
    <link rel="stylesheet" href="{{ asset('css/vendor/bootstrap-icons.css') }}">

    <!-- CSS Dependencies (locaux) -->
    <link rel="stylesheet" href="{{ asset('css/vendor/bootstrap.min.css') }}">

    <!-- Suppression globale des ombres -->
    <link rel="stylesheet" href="{{ asset('css/no-shadows.css') }}">

    <!-- Scripts (locaux) -->
    <script src="{{ asset('js/vendor/pdf.min.js') }}"></script>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
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
        <!-- Header Two-Band -->
        <header class="two-band-header">
            <!-- Top Band - Logo, SAI, Organisation, Actions -->
            <div class="top-band">
                <div class="top-band-container">
                    <!-- Logo et SAI -->
                    <div class="left-section">
                        <a href="{{ url('/') }}" class="header-logo">
                            <img src="{{ asset('linear.svg') }}" alt="Shelve Logo" class="bg-light p-1 rounded">
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
                            <!-- Indicateur de statut MCP -->
                            <div class="header-action-item">
                                <div class="mcp-status-indicator" id="mcpStatusIndicator" title="Statut MCP" onclick="showMcpDetails()">
                                    <div class="status-dot" id="mcpStatusDot"></div>
                                    <span class="status-text" id="mcpStatusText">MCP</span>
                                </div>
                            </div>

                            <!-- Notifications -->
                            @can('module_mails_access')
                            <div class="header-action-item">
                                <a href="{{ route('notifications.organisation') }}" class="header-action-btn position-relative" id="notificationBtn" title="Notifications">
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
                            <a class="nav-link @if (Request::segment(1) == 'workflows') active @endif" href="{{ route('workflows.dashboard') }}">
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
                        @can('module_ai_access')
                        <div class="nav-item">
                            <a class="nav-link @if (Request::segment(1) == 'ai') active @endif" href="{{ route('ai.chats.index' ) }}">
                                <i class="bi bi-robot"></i>
                                <span>{{ __('AI') }}</span>
                            </a>
                        </div>
                        @endcan
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

    <!-- Script de suppression des ombres -->
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

            // Vérification périodique du statut MCP
            updateMcpStatus();
            setInterval(updateMcpStatus, 60000); // Toutes les 60 secondes
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

        // Variable globale pour stocker les dernières données de statut MCP
        let lastMcpStatusData = null;

        // Fonction pour afficher les détails du statut MCP
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

        // Fonction pour mettre à jour le statut MCP
        function updateMcpStatus() {
            fetch('/api/mcp/status')
                .then(response => response.json())
                .then(data => {
                    lastMcpStatusData = data; // Stocker les données pour les détails

                    const statusDot = document.getElementById('mcpStatusDot');
                    const statusIndicator = document.getElementById('mcpStatusIndicator');

                    if (!statusDot || !statusIndicator) return;

                    // Reset classes
                    statusDot.className = 'status-dot';

                    // Déterminer le statut global basé sur MCP et Ollama
                    let status = 'grey';
                    let title = 'Statut MCP: Inconnu';

                    if (data.mcp && data.ollama) {
                        if (data.mcp.success && data.ollama.success) {
                            status = 'green';
                            title = 'MCP: Opérationnel - Ollama: Connecté';
                        } else if (data.mcp.success && !data.ollama.success) {
                            status = 'orange';
                            title = 'MCP: Opérationnel - Ollama: Déconnecté';
                        } else if (!data.mcp.success && data.ollama.success) {
                            status = 'yellow';
                            title = 'MCP: Déconnecté - Ollama: Connecté';
                        } else {
                            status = 'red';
                            title = 'MCP: Déconnecté - Ollama: Déconnecté';
                        }
                    } else {
                        status = 'red';
                        title = 'MCP: Service indisponible';
                    }

                    // Appliquer le statut
                    statusDot.classList.add(status);
                    statusIndicator.title = title;
                })
                .catch(error => {
                    console.log('Erreur lors de la vérification du statut MCP:', error);
                    lastMcpStatusData = null;

                    const statusDot = document.getElementById('mcpStatusDot');
                    const statusIndicator = document.getElementById('mcpStatusIndicator');

                    if (statusDot && statusIndicator) {
                        statusDot.className = 'status-dot red';
                        statusIndicator.title = 'MCP: Erreur de communication';
                    }
                });
        }
    </script>
</body>
</html>
