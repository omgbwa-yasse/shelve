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

    <!-- Styles additionnels poussés par les vues -->
    @stack('styles')

    <!-- FontAwesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
          integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
          crossorigin="anonymous" referrerpolicy="no-referrer" />

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

        /* Styles pour le bouton AI */
        .header-ai-button {
            background-color: #007bff;
            border: none;
            color: white;
            padding: 8px 12px;
            margin-left: 5px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .header-ai-button:hover {
            background-color: #0056b3;
        }

        .header-ai-button i {
            font-size: 16px;
        }

        /* Styles pour le modal AI */
        .ai-chat-modal {
            max-width: 600px;
        }

        .ai-chat-messages {
            height: 400px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            background-color: #f8f9fa;
        }

        .ai-message, .user-message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 8px;
            max-width: 85%;
        }

        .ai-message {
            background-color: #e3f2fd;
            margin-right: auto;
        }

        .user-message {
            background-color: #007bff;
            color: white;
            margin-left: auto;
            text-align: right;
        }

        .search-type-selector {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .search-type-btn {
            padding: 8px 16px;
            border: 2px solid #dee2e6;
            background-color: white;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .search-type-btn:hover {
            border-color: #007bff;
            color: #007bff;
        }

        .search-type-btn.active {
            background-color: #007bff;
            border-color: #007bff;
            color: white;
        }

        .ai-chat-input-group {
            margin-top: 15px;
        }

        .result-link {
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            margin: 2px;
            font-size: 12px;
        }

        .result-link:hover {
            background-color: #218838;
            color: white;
            text-decoration: none;
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
                                    <i class="bi bi-info-circle mr-2"></i> {{ __('no_organisation_available') }}
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
                            <span>{{ __('intelligent_archiving_system') }}</span>
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
                                <a href="{{ route('ai-search.index') }}" class="header-ai-button" style="text-decoration: none;">
                                    <i class="bi bi-robot"></i>
                                </a>
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
                            <a class="nav-link @if (Request::segment(1) == 'mails' || Request::segment(1) == '') active @endif position-relative" href="{{ route('mail-received.index') }}">
                                <i class="bi bi-envelope"></i>
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
                        <!-- Module Workflow a été supprimé -->
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

                        <!-- Module AI -->
                        <div class="nav-item">
                            <a class="nav-link @if (Request::segment(1) == 'ai-search') active @endif" href="{{ route('ai-search.index') }}">
                                <i class="bi bi-robot"></i>
                                <span>{{ __('AI') }}</span>
                            </a>
                        </div>

                        <!-- Module OPAC -->
                        <div class="nav-item">
                            <a class="nav-link @if (Request::segment(1) == 'opac') active @endif" href="{{ route('opac.index') }}" target="_blank">
                                <i class="bi bi-globe"></i>
                                <span>{{ __('OPAC') }}</span>
                            </a>
                        </div>

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
                                        @case('')
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
                                        @case('ai-search')
                                            @include('submenu.ai-search')
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

    <!-- Modal pour l'assistant IA de recherche -->
    <div class="modal fade" id="aiSearchModal" tabindex="-1" role="dialog" aria-labelledby="aiSearchModalLabel" aria-hidden="true">
        <div class="modal-dialog ai-chat-modal" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="aiSearchModalLabel">
                        <i class="bi bi-robot me-2"></i>{{ __('AI Search Assistant') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Sélecteur du type de recherche -->
                    <div class="search-type-selector">
                        <button class="search-type-btn active" data-type="records">
                            <i class="bi bi-folder me-1"></i>{{ __('Records') }}
                        </button>
                        <button class="search-type-btn" data-type="mails">
                            <i class="bi bi-envelope me-1"></i>{{ __('Mails') }}
                        </button>
                        <button class="search-type-btn" data-type="communications">
                            <i class="bi bi-chat-dots me-1"></i>{{ __('Communications') }}
                        </button>
                        <button class="search-type-btn" data-type="slips">
                            <i class="bi bi-arrow-left-right me-1"></i>{{ __('Transfers') }}
                        </button>
                    </div>

                    <!-- Zone des messages -->
                    <div class="ai-chat-messages" id="aiChatMessages">
                        <div class="ai-message">
                            <i class="bi bi-robot me-2"></i>
                            {{ __('Hello! I\'m your search assistant. Tell me what you\'re looking for and I\'ll help you find it.') }}
                        </div>
                    </div>

                    <!-- Zone de saisie -->
                    <div class="ai-chat-input-group">
                        <div class="input-group">
                            <input type="text" class="form-control" id="aiChatInput"
                                   placeholder="{{ __('Ask me what you\'re looking for...') }}">
                            <button class="btn btn-primary" type="button" onclick="sendAiMessage()">
                                <i class="bi bi-send"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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

            // Fonction pour mettre à jour les badges de notifications
            function updateNotificationBadges() {
                @can('module_mails_access')
                // Ici vous pouvez ajouter la logique pour récupérer les notifications
                // Pour l'instant, on laisse une fonction vide pour éviter l'erreur
                @endcan
            }

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
            setTimeout(function() {
                initializeLayoutScripts();
                initSearchTypeButtons(); // Initialiser les boutons de type
            }, 200);
        });

        // Ne plus réinitialiser à l'ouverture du modal pour éviter les doublons

        // Variables globales pour l'assistant IA
        let aiCurrentSearchType = 'records';

        // Fonction pour ouvrir le modal de l'assistant IA
        function openAiSearchModal() {
            // Réinitialiser les boutons à l'ouverture du modal (JavaScript vanilla)
            setTimeout(function() {
                const buttons = document.querySelectorAll('.search-type-btn');
                buttons.forEach(btn => btn.classList.remove('active'));

                const recordsBtn = document.querySelector('.search-type-btn[data-type="records"]');
                if (recordsBtn) {
                    recordsBtn.classList.add('active');
                }
                aiCurrentSearchType = 'records';
                // Ne PAS réinitialiser les event listeners à chaque ouverture
            }, 100);

            try {
                if (window.bootstrap && bootstrap.Modal) {
                    var modalElement = document.getElementById('aiSearchModal');
                    var modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);
                    modalInstance.show();
                    return;
                }
            } catch (e) {}

            if (typeof $ !== 'undefined' && typeof $('#aiSearchModal').modal === 'function') {
                $('#aiSearchModal').modal('show');
            }
        }

        // Variable pour éviter les doublons d'event listeners
        let searchTypeButtonsInitialized = false;

        // Gestion des boutons de type de recherche (JavaScript vanilla)
        function initSearchTypeButtons() {
            if (searchTypeButtonsInitialized) {
                return; // Éviter les doublons
            }

            const buttons = document.querySelectorAll('.search-type-btn');

            buttons.forEach(button => {
                button.addEventListener('click', handleSearchTypeClick);
            });

            searchTypeButtonsInitialized = true;
        }

        function handleSearchTypeClick(e) {
            e.preventDefault();
            console.log('Button clicked:', this.dataset.type);

            const buttons = document.querySelectorAll('.search-type-btn');

            // Retirer la classe active de tous les boutons
            buttons.forEach(btn => {
                btn.classList.remove('active');
            });

            // Ajouter la classe active au bouton cliqué
            this.classList.add('active');
            aiCurrentSearchType = this.dataset.type;

            // Ajouter un message pour confirmer le changement SEULEMENT si c'est différent
            const typeNames = {
                'records': 'Documents/Records',
                'mails': 'Mails',
                'communications': 'Communications',
                'slips': 'Transferts'
            };
            const typeName = typeNames[aiCurrentSearchType] || aiCurrentSearchType;

            // Ne pas répéter le message si c'est le même type
            const lastMessage = document.querySelector('#aiChatMessages .ai-message:last-child');
            const expectedMessage = `Recherche maintenant dans : ${typeName}`;

            if (!lastMessage || !lastMessage.textContent.includes(expectedMessage)) {
                addChatMessage(`Recherche maintenant dans : ${typeName}`, false);
            }
        }

        // Envoi d'un message à l'assistant IA
        function sendAiMessage() {
            const input = $('#aiChatInput');
            const message = input.val().trim();

            if (!message) return;

            // Ajouter le message de l'utilisateur
            addChatMessage(message, true);
            input.val('');

            // Envoyer la requête à l'API
            fetch('/ai-search/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    message: message,
                    search_type: aiCurrentSearchType
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    addChatMessage(data.response, false, data.results);
                } else {
                    addChatMessage('{{ __("Sorry, an error occurred. Please try again.") }}', false);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                addChatMessage('{{ __("Sorry, an error occurred. Please try again.") }}', false);
            });
        }

        // Ajouter un message au chat
        function addChatMessage(message, isUser, results = null) {
            const chatContainer = $('#aiChatMessages');
            const messageClass = isUser ? 'user-message' : 'ai-message';
            const icon = isUser ? '' : '<i class="bi bi-robot me-2"></i>';

            let messageHtml = `<div class="${messageClass}">${icon}${message}`;

            // Ajouter les liens vers les résultats si disponibles
            if (results && results.length > 0) {
                messageHtml += '<div class="mt-2">';
                results.forEach(result => {
                    messageHtml += `<a href="${result.url}" class="result-link" target="_blank">
                        <i class="${result.icon} me-1"></i>${result.title}
                    </a>`;
                });
                messageHtml += '</div>';
            }

            messageHtml += '</div>';

            chatContainer.append(messageHtml);
            chatContainer.scrollTop(chatContainer[0].scrollHeight);
        }

        // Envoyer le message avec Entrée
        $(document).on('keypress', '#aiChatInput', function(e) {
            if (e.which === 13) {
                sendAiMessage();
            }
        });

    </script>
</body>
</html>
