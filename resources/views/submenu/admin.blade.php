{{-- Sous-menu Administration Générale --}}

{{-- Uniquement visible pour les administrateurs --}}
@can('module_settings_access')
<div class="submenu-header mb-3">
    <h6 class="text-muted">
        <i class="bi bi-gear-fill me-2"></i>
        Administration
    </h6>
</div>

<a class="nav-link @if(Request::is('admin/users*')) active @endif" href="#">
    <i class="bi bi-people"></i>
    Utilisateurs
</a>

<a class="nav-link @if(Request::is('admin/roles*')) active @endif" href="#">
    <i class="bi bi-shield-check"></i>
    Rôles & Permissions
</a>

<a class="nav-link @if(Request::is('admin/system*')) active @endif" href="#">
    <i class="bi bi-cpu"></i>
    Système
</a>

<a class="nav-link @if(Request::is('admin/logs*')) active @endif" href="#">
    <i class="bi bi-file-text"></i>
    Journaux
</a>

<div class="nav-divider my-3"></div>

{{-- Section IA retirée --}}

@endcan

{{-- Message si pas d'accès admin --}}
@cannot('module_settings_access')
<div class="text-center text-muted py-4">
    <i class="bi bi-lock-fill" style="font-size: 2rem;"></i>
    <p class="mt-2 mb-0">Accès restreint</p>
    <small>Administration réservée</small>
</div>
@endcannot