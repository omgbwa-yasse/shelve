<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <link rel="icon" href="{{ asset('favicon-v2.ico') }}" type="image/x-icon">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <!-- CSS de Bootstrap -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <link href="{{ asset('build/assets/app-BGUAifWy.css') }}" rel="stylesheet">
    <script src="{{ asset('build/assets/app-BziwsqBe.js') }}" defer></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.9.359/pdf.min.js"></script>
    <link rel="icon" href="{{ asset('favicon.ico') }}?v={{ time() }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ time() }}" type="image/x-icon">
    <!-- Votre CSS personnalisé -->



    <!-- Votre JS jquery -->

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Shelve') }}</title>
    <style>
        .org-dropdown {
            max-height: 300px;
            overflow-y: auto;
        }

        input,
        textarea,
        select {
            box-shadow: inset 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Style pour le conteneur principal */
        #container {
            margin-top: 0.5rem;
            border: 0;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 0.5rem;
            padding: 0.5rem;
        }

        /* Style pour l'en-tête */
        #container h1, #container h2 {
            /*background-color: #0178d4;*/
            color: #0178d4;
            /*font-weight: bold;*/

            font-family: inherit; /* Added to match .display-6 */
            font-size: 2rem; /* Typical size for .display-6, adjust if needed */
            line-height: 1.2; /* Typical line-height for .display-6 */
        }



        /* Style pour les boutons */
        #container .btn {
            margin-bottom: 0rem;
        }

        /* Style pour la table */
        #container .table {
            border: 1px solid #dee2e6;
        }

        #container .table th,
        #container .table td {
            border: 1px solid #dee2e6;
        }

        #container .table th {
            background-color: #f8f9fa;
        }

        #container .table tbody tr:hover {
            background-color: #f1f1f1;
        }
        #container a {
            text-decoration: none ;
        }
    </style>
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>

<body class="bg-light">
@auth
    <div class="bg-dark text-white py-1 px-3">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <span class="font-weight-bold">
                    <i class="bi bi-building"></i> {{ __('Current Organization') }}: {{ Auth::user()->currentOrganisation->name ?? __('Not defined') }}
                </span>
                <button class="btn btn-sm btn-outline-light" onclick="openOrgModal()">
                    <i class="bi bi-arrow-repeat"></i> {{ __('Change Organization') }}
                </button>
            </div>
        </div>
    </div>
    <!-- Modal pour changer d'organisation -->
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
                                    {{ $organisation->name }}
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
        <nav class="navbar navbar-expand-md navbar-dark bg-primary shadow-sm">
            <div class="container-fluid">
                <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
                    <img src="{{ asset('linear.svg') }}" alt="Shelve Logo" class="bg-light p-1 " style="border-radius: 5px ; height: 40px; width: auto;">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                        aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <form class="d-flex w-75" action="{{ route('records.search') }}">
                        <input type="hidden" name="advanced" value="false">
                        <input class="form-control mr-2 w-75" name="query" type="search" placeholder="{{ __('Search') }}"
                               value="@if (isset($_GET['query'])) {{ preg_replace('/\s+/', ' ', trim($_GET['query'])) }} @endif" aria-label="{{ __('Search') }}">
                        <select class="form-select w-25" name="search_type">
                            <option value="">{{ __('All') }}</option>
                            <option value="mail" @if(isset($_GET['search_type']) && $_GET['search_type'] == 'mail') selected @endif>{{ __('Mail') }}</option>
                            <option value="record" @if(isset($_GET['search_type']) && $_GET['search_type'] == 'record') selected @endif>{{ __('Archives') }}</option>
                            <option value="transferring_record" @if(isset($_GET['search_type']) && $_GET['search_type'] == 'transferring_record') selected @endif>{{ __('Transferred Archives') }}</option>
                            <option value="transferring" @if(isset($_GET['search_type']) && $_GET['search_type'] == 'transferring') selected @endif>{{ __('Transfer') }}</option>
                        </select>
                        <button class="btn btn-outline-light ml-2" type="submit"><i class="bi bi-search"></i></button>
                    </form>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Language Selector -->
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                               data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                {{ strtoupper(App::getLocale()) }}
                            </a>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('language.switch', 'fr') }}">FR</a>
                                <a class="dropdown-item" href="{{ route('language.switch', 'en') }}">EN</a>
                            </div>
                        </li>
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                                   data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm mt-2">
            <div class="container-fluid">
                <div class="navbar-nav w-100 justify-content-between">
                    <a class="nav-link @if (Request::segment(1) == 'bulletin-board') active fw-bold text-primary @endif"
                       href="{{ route('bulletin-boards.index') }}">
                       <i class="bi bi-card-text"></i> Barbillard
                    </a>
                    <a class="nav-link @if (Request::segment(1) == 'mails') active fw-bold text-primary @endif"
                        href="{{ route('mail-received.index') }}">
                        <i class="bi bi-envelope"></i> {{ __('Mail') }}
                    </a>
                    <a class="nav-link @if (Request::segment(1) == 'repositories') active fw-bold text-primary @endif"
                       href="{{ route('records.index') }}">
                        <i class="bi bi-folder"></i> {{ __('Repository') }}
                    </a>
                    <a class="nav-link @if (Request::segment(1) == 'communications') active fw-bold text-primary @endif"
                       href="{{ route('transactions.index') }}">
                        <i class="bi bi-chat-dots"></i> {{ __('Request') }}
                    </a>

                    <a class="nav-link @if (Request::segment(1) == 'transferrings') active fw-bold text-primary @endif"
                       href="{{ route('slips.index') }}">
                        <i class="bi bi-arrow-left-right"></i> {{ __('Transfer') }}
                    </a>
                    <a class="nav-link @if (Request::segment(1) == 'deposits') active fw-bold text-primary @endif"
                       href="{{ route('buildings.index') }}">
                        <i class="bi bi-building"></i> {{ __('building') }}
                    </a>
                    <a class="nav-link @if (Request::segment(1) == 'tools') active fw-bold text-primary @endif"
                       href="{{ route('activities.index') }}">
                        <i class="bi bi-tools"></i> {{ __('Tool') }}
                    </a>
                    <a class="nav-link @if (Request::segment(1) == 'dollies') active fw-bold text-primary @endif"
                       href="{{ route('dolly.index') }}">
                        <i class="bi bi-cart3"></i> {{ __('Dolly') }}
                    </a>
                    <a class="nav-link @if (Request::segment(1) == 'settings') active fw-bold text-primary @endif"
                       href="{{ route('mail-typology.index') }}">
                        <i class="bi bi-gear"></i> {{ __('Setting') }}
                    </a>
                </div>
            </div>
        </nav>

        <main class="">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-2">
                        <div class="card">
                            <div class="card-body">
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
<!-- Scripts de Bootstrap -->
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
    });
</script>

</body>

</html>
