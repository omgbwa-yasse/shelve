{{--
    Layout adaptatif pour OPAC
    Utilise le nouveau système de services et composants
--}}
<!doctype html>
<html lang="{{ app()->getLocale() }}" data-theme="{{ $activeTheme['name'] ?? 'default' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'OPAC - ' . config('app.name'))</title>
    <meta name="description" content="@yield('description', 'Catalogue en ligne - Recherchez et explorez nos collections')">

    <!-- Theme Variables CSS -->
    <style>
        :root {
            --primary-color: {{ $activeTheme['variables']['primary_color'] ?? '#1e3a8a' }};
            --secondary-color: {{ $activeTheme['variables']['secondary_color'] ?? '#3b82f6' }};
            --accent-color: {{ $activeTheme['variables']['accent_color'] ?? '#f59e0b' }};
            --text-color: {{ $activeTheme['variables']['text_color'] ?? '#1f2937' }};
            --background-color: {{ $activeTheme['variables']['background_color'] ?? '#ffffff' }};
            --font-family: {{ $activeTheme['variables']['font_family'] ?? 'Inter, system-ui, sans-serif' }};
            --border-radius: {{ $activeTheme['variables']['border_radius'] ?? '0.5rem' }};
            --box-shadow: {{ $activeTheme['variables']['box_shadow'] ?? '0 1px 3px rgba(0, 0, 0, 0.1)' }};
        }

        body {
            font-family: var(--font-family);
            color: var(--text-color);
            background-color: var(--background-color);
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        /* Responsive utilities */
        @media (max-width: 768px) {
            .container {
                padding: 0 0.5rem;
            }

            .hide-mobile {
                display: none !important;
            }
        }

        @media (min-width: 769px) {
            .hide-desktop {
                display: none !important;
            }
        }

        /* Accessibility */
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        /* Focus styles */
        *:focus {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }

        /* Skip link */
        .skip-link {
            position: absolute;
            top: -40px;
            left: 6px;
            background: var(--primary-color);
            color: white;
            padding: 8px;
            text-decoration: none;
            border-radius: var(--border-radius);
            z-index: 1000;
        }

        .skip-link:focus {
            top: 6px;
        }
    </style>

    <!-- Bootstrap CSS (CDN for now, could be compiled later) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Custom Theme CSS -->
    @if(isset($activeTheme['variables']['custom_css']) && $activeTheme['variables']['custom_css'])
        <style>
            {!! $activeTheme['variables']['custom_css'] !!}
        </style>
    @endif

    @stack('styles')
</head>

<body class="theme-{{ $activeTheme['name'] ?? 'default' }}">
    <!-- Skip to content link for accessibility -->
    <a href="#main-content" class="skip-link">Aller au contenu principal</a>

    <div id="opac-app">
        <!-- Navigation Header -->
        @if($opacConfig['ui']['show_navigation'] ?? true)
            @include('opac.components.navigation', [
                'showUserMenu' => $opacConfig['features']['user_accounts'] ?? true,
                'showLanguageSelector' => $opacConfig['ui']['show_language_selector'] ?? false,
                'brandText' => $opacConfig['branding']['site_name'] ?? config('app.name'),
                'showSearch' => $opacConfig['ui']['header_search'] ?? false
            ])
        @endif

        <!-- Breadcrumbs -->
        @if(($opacConfig['ui']['show_breadcrumbs'] ?? true) && isset($breadcrumbs) && count($breadcrumbs) > 0)
            @include('opac.components.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
        @endif

        <!-- Flash Messages -->
        @include('opac.components.flash-messages')

        <!-- Main Content -->
        <main id="main-content" role="main" class="py-4">
            <div class="container">
                @yield('content')
            </div>
        </main>

        <!-- Footer -->
        @if($opacConfig['ui']['show_footer'] ?? true)
            @include('opac.components.footer', [
                'showSocialLinks' => $opacConfig['features']['social_links'] ?? false,
                'showStatistics' => $opacConfig['features']['statistics'] ?? false,
                'copyrightText' => $opacConfig['branding']['copyright'] ?? date('Y') . ' ' . config('app.name')
            ])
        @endif
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Global OPAC Configuration -->
    <script>
        window.OpacConfig = @json($opacConfig ?? []);
        window.OpacTheme = @json($activeTheme ?? []);
        window.OpacLocale = '{{ app()->getLocale() }}';
    </script>

    <!-- Custom Theme JS -->
    @if(isset($activeTheme['assets']['js']) && is_array($activeTheme['assets']['js']))
        @foreach($activeTheme['assets']['js'] as $jsFile => $jsContent)
            <script>
                {!! $jsContent !!}
            </script>
        @endforeach
    @endif

    @stack('scripts')
</body>
</html>
