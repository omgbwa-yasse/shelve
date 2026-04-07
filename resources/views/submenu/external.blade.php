<div class="submenu-container py-2">
    @php
    use App\Helpers\SubmenuPermissions;
    @endphp

    <!-- Styles partagés via _submenu.scss -->

    <!-- Organisations -->
    <div class="submenu-section mb-3">
        <div class="submenu-heading" data-toggle="collapse" data-target="#organizationsSubmenu">
            <i class="bi bi-building"></i>
            {{ __('Organisations externes') }}
        </div>
        <div class="submenu-content" id="organizationsSubmenu">
            <div class="submenu-item">
                <a class="submenu-link {{ Request::routeIs('external.organizations.index') ? 'active' : '' }}" href="{{ route('external.organizations.index') }}">
                    <i class="bi bi-list"></i> {{ __('Liste des organisations') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link {{ Request::routeIs('external.organizations.create') ? 'active' : '' }}" href="{{ route('external.organizations.create') }}">
                    <i class="bi bi-plus-circle"></i> {{ __('Nouvelle organisation') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Contacts -->
    <div class="submenu-section mb-3">
        <div class="submenu-heading" data-toggle="collapse" data-target="#contactsSubmenu">
            <i class="bi bi-person"></i>
            {{ __('Contacts externes') }}
        </div>
        <div class="submenu-content" id="contactsSubmenu">
            <div class="submenu-item">
                <a class="submenu-link {{ Request::routeIs('external.contacts.index') ? 'active' : '' }}" href="{{ route('external.contacts.index') }}">
                    <i class="bi bi-list"></i> {{ __('Liste des contacts') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link {{ Request::routeIs('external.contacts.create') ? 'active' : '' }}" href="{{ route('external.contacts.create') }}">
                    <i class="bi bi-plus-circle"></i> {{ __('Nouveau contact') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Retour -->
    <div class="submenu-section mb-3">
        <div class="submenu-content">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ url('/') }}">
                    <i class="bi bi-arrow-left"></i> {{ __('Retour à l\'accueil') }}
                </a>
            </div>
        </div>
    </div>

    @once
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const headings = document.querySelectorAll('.submenu-heading');
            headings.forEach(heading => {
                heading.addEventListener('click', function() {
                    const target = document.querySelector(this.getAttribute('data-target'));
                    if (target) {
                        target.style.display = target.style.display === 'none' ? 'block' : 'none';
                    }
                });
            });
        });
    </script>
    @endonce
</div>
