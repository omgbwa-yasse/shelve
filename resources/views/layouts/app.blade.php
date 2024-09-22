<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <!-- CSS de Bootstrap -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Votre CSS personnalisé -->

    <!-- Votre JS jquery -->

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Shelve') }}</title>
    <style>

        a {
            text-decoration: none;
            color: #0178d4;
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
        #container h1,
        #container h2 {
            background-color: #0178d4;
            color: #fff;
            font-weight: lighter;
            padding: 0.3rem;
            border-top-left-radius: 0.3rem;
            border-top-right-radius: 0.3rem;
            margin-top: 0;
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
        body a, .container a, .content a {
            text-decoration: none !important;
            color: #0178d4 !important;
        }
    </style>
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>

<body class="bg-light">
<div id="app">
    @guest
    @else
    <nav class="navbar navbar-expand-md navbar-dark bg-primary shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ url('/') }}">
                {{ config('app.name', 'Shelve') }}
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
                    <input class="form-control mr-2 w-75" name="query" type="search" placeholder="Rechercher"
                           aria-label="Search">
                    <select class="form-select w-25" name="search_type">
                        <option value="">Par tout</option>
                        <option value="mail">Courriel</option>
                        <option value="record">Archives</option>
                        <option value="transferring_record">Archives versées</option>
                        <option value="transferring">Versement</option>
                    </select>
                    <button class="btn btn-outline-light" type="submit"><i class="bi bi-search"></i></button>
                </form>

                <div class="d-grid gap-2">

                    <button type="button" name="" id="" class="btn btn-primary">
                        @php

                            @endphp
                    </button>

                </div>


                <!-- Right Side Of Navbar -->
                <ul class="navbar-nav ms-auto">
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
                <a class="nav-link @if (Request::segment(1) == 'mails') active fw-bold text-primary @endif"
                   href="{{ route('mails.index') }}">
                    <i class="bi bi-envelope"></i> Courrier
                </a>
                <a class="nav-link @if (Request::segment(1) == 'repositories') active fw-bold text-primary @endif"
                   href="{{ route('records.index') }}">
                    <i class="bi bi-folder"></i> Repertoire
                </a>
                <a class="nav-link @if (Request::segment(1) == 'communications') active fw-bold text-primary @endif"
                   href="{{ route('transactions.index') }}">
                    <i class="bi bi-chat-dots"></i> Demande
                </a>
                <a class="nav-link @if (Request::segment(1) == 'transferrings') active fw-bold text-primary @endif"
                   href="{{ route('slips.index') }}">
                    <i class="bi bi-arrow-left-right"></i> Transfert
                </a>
                <a class="nav-link @if (Request::segment(1) == 'tasks') active fw-bold text-primary @endif"
                   href="{{ route('tasks.index') }}">
                    <i class="bi bi-clipboard-check"></i> Tâches
                </a>
                <a class="nav-link @if (Request::segment(1) == 'deposits') active fw-bold text-primary @endif"
                   href="{{ route('buildings.index') }}">
                    <i class="bi bi-building"></i> Dépôt
                </a>
                <a class="nav-link @if (Request::segment(1) == 'tools') active fw-bold text-primary @endif"
                   href="{{ route('activities.index') }}">
                    <i class="bi bi-tools"></i> Outil
                </a>

                <a class="nav-link @if (Request::segment(1) == 'dollies') active fw-bold text-primary @endif"
                   href="{{ route('dolly.index') }}">
                    <i class="bi bi-cart3"></i> Chariots
                </a>

                <a class="nav-link @if (Request::segment(1) == 'settings') active fw-bold text-primary @endif"
                   href="{{ route('mail-typology.index') }}">
                    <i class="bi bi-gear"></i> Paramètre
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

                                @case('tasks')
                                    @include('submenu.tasks')
                                    @break

                                @default
                                    @include('submenu.mails')
                            @endswitch
                        </div>
                    </div>
                </div>

                <div class="col-md-10">@endguest
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
<script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>
