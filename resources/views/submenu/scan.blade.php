<div class="submenu-container py-2">
    <!-- Styles partagés via _submenu.scss -->

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
