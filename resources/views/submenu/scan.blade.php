<div class="submenu-container py-2">
    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        .submenu-container {
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
        }

        .submenu-heading {
            background-color: #4285f4;
            color: white;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 6px;
            font-weight: 500;
            font-size: 13px;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .submenu-heading:hover {
            background-color: #3367d6;
        }

        .submenu-heading i {
            margin-right: 8px;
            font-size: 14px;
        }

        .submenu-content { padding: 0 0 8px 12px; margin-bottom: 8px; display: block; }

        .submenu-item {
            margin-bottom: 2px;
        }

        .submenu-link {
            display: flex;
            align-items: center;
            padding: 4px 8px;
            color: #202124;
            text-decoration: none;
            border-radius: 4px;
            transition: all 0.2s ease;
            font-size: 12.5px;
        }

        .submenu-link:hover {
            background-color: #f1f3f4;
            color: #4285f4;
            text-decoration: none;
        }

        .submenu-link i {
            margin-right: 8px;
            color: #5f6368;
            font-size: 13px;
        }

        .submenu-link:hover i {
            color: #4285f4;
        }

        .submenu-link.active {
            background-color: #e8f0fe;
            color: #1a73e8;
        }

        .submenu-link.active i {
            color: #1a73e8;
        }
    </style>

    <!-- Numérisation Section -->
    <div class="submenu-section">
        <div class="submenu-heading">
            <i class="bi bi-scanner"></i> {{ __('Numérisation') }}
        </div>
        <div class="submenu-content">
            <div class="submenu-item">
                <a class="submenu-link @if(Route::currentRouteName() == 'scan.index') active @endif" href="{{ route('scan.index') }}">
                    <i class="bi bi-camera"></i> {{ __('Numériser') }}
                </a>
            </div>

            <div class="submenu-item">
                <a class="submenu-link @if(Route::currentRouteName() == 'scan.list') active @endif" href="{{ route('scan.list') }}">
                    <i class="bi bi-images"></i> {{ __('Mes numérisations') }}
                </a>
            </div>
        </div>
    </div>
</div>
