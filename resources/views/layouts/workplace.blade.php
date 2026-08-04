<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Shelve') }}</title>

    <link rel="icon" href="{{ asset('favicon-v2.ico') }}" type="image/x-icon">
    <link rel="preload" href="{{ asset('linear.svg') }}" as="image">

    <script src="{{ asset('js/vendor/pdf.min.js') }}"></script>

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @stack('styles')

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
          integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
          crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <div id="app">
        <main class="py-3">
            <div class="container-fluid">
                <div class="d-flex justify-content-end mb-3">
                    <a href="{{ route('report.dashboard') }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-box-arrow-left me-1"></i>Retour à l'application principale
                    </a>
                </div>
                @yield('content')
            </div>
        </main>
    </div>

    @stack('scripts')
    @yield('scripts')

    <script src="{{ asset('js/vendor/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('js/vendor/chart.min.js') }}"></script>
</body>
</html>
