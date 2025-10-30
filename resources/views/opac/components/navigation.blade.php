{{-- Composant navigation OPAC --}}
<nav class="opac-navigation" role="navigation" aria-label="Navigation principale OPAC">
    <div class="container-fluid">
        <div class="row align-items-center">
            {{-- Logo et titre --}}
            <div class="col-md-3">
                <div class="brand-section">
                    @if($logoUrl ?? false)
                        <img src="{{ $logoUrl }}" alt="Logo" class="brand-logo">
                    @endif
                    <h1 class="brand-title">
                        <a href="{{ route('opac.index') }}" class="brand-link">
                            {{ $title ?? 'OPAC' }}
                        </a>
                    </h1>
                </div>
            </div>

            {{-- Menu principal --}}
            <div class="col-md-6">
                <ul class="nav-menu" role="menubar">
                    <li class="nav-item" role="none">
                        <a href="{{ route('opac.index') }}"
                           class="nav-link {{ request()->routeIs('opac.index') ? 'active' : '' }}"
                           role="menuitem">
                            <i class="fas fa-home"></i>
                            <span>{{ $labels['home'] ?? 'Accueil' }}</span>
                        </a>
                    </li>

                    @if($showAdvancedSearch ?? true)
                        <li class="nav-item" role="none">
                            <a href="{{ route('opac.advanced-search') }}"
                               class="nav-link {{ request()->routeIs('opac.advanced-search') ? 'active' : '' }}"
                               role="menuitem">
                                <i class="fas fa-search-plus"></i>
                                <span>{{ $labels['advanced_search'] ?? 'Recherche avancée' }}</span>
                            </a>
                        </li>
                    @endif

                    @if($showBrowse ?? true)
                        <li class="nav-item dropdown" role="none">
                            <a class="nav-link dropdown-toggle"
                               href="#"
                               id="browseDropdown"
                               role="menuitem"
                               data-bs-toggle="dropdown"
                               aria-expanded="false">
                                <i class="fas fa-list"></i>
                                <span>{{ $labels['browse'] ?? 'Parcourir' }}</span>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="browseDropdown" role="menu">
                                <li role="none">
                                    <a class="dropdown-item" href="{{ route('opac.browse.subjects') }}" role="menuitem">
                                        <i class="fas fa-tags"></i> {{ $labels['subjects'] ?? 'Par sujet' }}
                                    </a>
                                </li>
                                <li role="none">
                                    <a class="dropdown-item" href="{{ route('opac.browse.authors') }}" role="menuitem">
                                        <i class="fas fa-user"></i> {{ $labels['authors'] ?? 'Par auteur' }}
                                    </a>
                                </li>
                                <li role="none">
                                    <a class="dropdown-item" href="{{ route('opac.browse.collections') }}" role="menuitem">
                                        <i class="fas fa-folder"></i> {{ $labels['collections'] ?? 'Par collection' }}
                                    </a>
                                </li>
                                <li role="none">
                                    <a class="dropdown-item" href="{{ route('opac.browse.recent') }}" role="menuitem">
                                        <i class="fas fa-clock"></i> {{ $labels['recent'] ?? 'Récemment ajoutés' }}
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif

                    @if($showHelp ?? true)
                        <li class="nav-item" role="none">
                            <a href="{{ route('opac.help') }}"
                               class="nav-link {{ request()->routeIs('opac.help') ? 'active' : '' }}"
                               role="menuitem">
                                <i class="fas fa-question-circle"></i>
                                <span>{{ $labels['help'] ?? 'Aide' }}</span>
                            </a>
                        </li>
                    @endif
                </ul>
            </div>

            {{-- Actions utilisateur --}}
            <div class="col-md-3">
                <div class="user-actions">
                    @auth
                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle"
                                    type="button"
                                    id="userDropdown"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                <i class="fas fa-user"></i>
                                <span class="d-none d-lg-inline">{{ Auth::user()->name }}</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                @if($showBookmarks ?? true)
                                    <li>
                                        <a class="dropdown-item" href="{{ route('opac.bookmarks') }}">
                                            <i class="fas fa-bookmark"></i> {{ $labels['bookmarks'] ?? 'Mes favoris' }}
                                        </a>
                                    </li>
                                @endif
                                @if($showHistory ?? true)
                                    <li>
                                        <a class="dropdown-item" href="{{ route('opac.history') }}">
                                            <i class="fas fa-history"></i> {{ $labels['history'] ?? 'Historique' }}
                                        </a>
                                    </li>
                                @endif
                                @if($showProfile ?? true)
                                    <li>
                                        <a class="dropdown-item" href="{{ route('profile.show') }}">
                                            <i class="fas fa-cog"></i> {{ $labels['profile'] ?? 'Profil' }}
                                        </a>
                                    </li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-sign-out-alt"></i> {{ $labels['logout'] ?? 'Se déconnecter' }}
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <div class="guest-actions">
                            <a href="{{ route('login') }}" class="btn btn-outline-primary me-2">
                                <i class="fas fa-sign-in-alt"></i>
                                <span class="d-none d-lg-inline">{{ $labels['login'] ?? 'Connexion' }}</span>
                            </a>
                            @if($showRegister ?? true)
                                <a href="{{ route('register') }}" class="btn btn-primary">
                                    <i class="fas fa-user-plus"></i>
                                    <span class="d-none d-lg-inline">{{ $labels['register'] ?? 'S\'inscrire' }}</span>
                                </a>
                            @endif
                        </div>
                    @endauth

                    {{-- Sélecteur de langue --}}
                    @if($showLanguageSelector ?? false)
                        <div class="language-selector ms-3">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                        type="button"
                                        id="languageDropdown"
                                        data-bs-toggle="dropdown">
                                    <i class="fas fa-globe"></i>
                                    {{ strtoupper(app()->getLocale()) }}
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
                                    <li>
                                        <a class="dropdown-item {{ app()->getLocale() == 'fr' ? 'active' : '' }}"
                                           href="{{ route('opac.language', 'fr') }}">
                                            🇫🇷 Français
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item {{ app()->getLocale() == 'en' ? 'active' : '' }}"
                                           href="{{ route('opac.language', 'en') }}">
                                            🇬🇧 English
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Navigation mobile --}}
        <div class="mobile-nav d-lg-none">
            <button class="btn btn-outline-primary mobile-nav-toggle"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#mobileNavMenu">
                <i class="fas fa-bars"></i>
            </button>

            <div class="collapse" id="mobileNavMenu">
                <div class="mobile-nav-menu">
                    <a href="{{ route('opac.index') }}" class="mobile-nav-item {{ request()->routeIs('opac.index') ? 'active' : '' }}">
                        <i class="fas fa-home"></i> {{ $labels['home'] ?? 'Accueil' }}
                    </a>

                    @if($showAdvancedSearch ?? true)
                        <a href="{{ route('opac.advanced-search') }}" class="mobile-nav-item">
                            <i class="fas fa-search-plus"></i> {{ $labels['advanced_search'] ?? 'Recherche avancée' }}
                        </a>
                    @endif

                    @if($showBrowse ?? true)
                        <div class="mobile-nav-section">
                            <div class="mobile-nav-title">{{ $labels['browse'] ?? 'Parcourir' }}</div>
                            <a href="{{ route('opac.browse.subjects') }}" class="mobile-nav-subitem">
                                <i class="fas fa-tags"></i> {{ $labels['subjects'] ?? 'Par sujet' }}
                            </a>
                            <a href="{{ route('opac.browse.authors') }}" class="mobile-nav-subitem">
                                <i class="fas fa-user"></i> {{ $labels['authors'] ?? 'Par auteur' }}
                            </a>
                            <a href="{{ route('opac.browse.collections') }}" class="mobile-nav-subitem">
                                <i class="fas fa-folder"></i> {{ $labels['collections'] ?? 'Par collection' }}
                            </a>
                        </div>
                    @endif

                    @if($showHelp ?? true)
                        <a href="{{ route('opac.help') }}" class="mobile-nav-item">
                            <i class="fas fa-question-circle"></i> {{ $labels['help'] ?? 'Aide' }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</nav>

@push('styles')
<style>
.opac-navigation {
    background: var(--header-bg, #ffffff);
    border-bottom: var(--border-width, 1px) solid var(--border-color, #dee2e6);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    padding: 0.75rem 0;
    position: sticky;
    top: 0;
    z-index: 1020;
}

.brand-section {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.brand-logo {
    height: 40px;
    width: auto;
}

.brand-title {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 700;
}

.brand-link {
    color: var(--primary-color, #007bff);
    text-decoration: none;
    transition: color 0.2s ease;
}

.brand-link:hover {
    color: var(--primary-color-hover, #0056b3);
}

.nav-menu {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
    justify-content: center;
    gap: 1rem;
}

.nav-item {
    position: relative;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    color: var(--header-color, #212529);
    text-decoration: none;
    border-radius: var(--border-radius, 0.375rem);
    transition: all 0.2s ease;
    font-weight: 500;
}

.nav-link:hover,
.nav-link.active {
    background: var(--primary-color, #007bff);
    color: white;
}

.nav-link i {
    font-size: 0.9rem;
}

.dropdown-menu {
    border: none;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border-radius: var(--border-radius, 0.375rem);
    margin-top: 0.5rem;
}

.dropdown-item {
    padding: 0.75rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: background-color 0.2s ease;
}

.dropdown-item:hover {
    background-color: var(--light-color, #f8f9fa);
}

.dropdown-item.active {
    background-color: var(--primary-color, #007bff);
    color: white;
}

.user-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0.5rem;
}

.guest-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.language-selector .dropdown-toggle {
    border: none;
    background: transparent;
    color: var(--header-color, #212529);
}

.mobile-nav {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: var(--border-width, 1px) solid var(--border-color, #dee2e6);
}

.mobile-nav-toggle {
    width: 100%;
    justify-content: center;
}

.mobile-nav-menu {
    padding: 1rem 0;
}

.mobile-nav-item,
.mobile-nav-subitem {
    display: block;
    padding: 0.75rem 1rem;
    color: var(--header-color, #212529);
    text-decoration: none;
    border-radius: var(--border-radius, 0.375rem);
    margin: 0.25rem 0;
    transition: background-color 0.2s ease;
}

.mobile-nav-item:hover,
.mobile-nav-item.active,
.mobile-nav-subitem:hover {
    background-color: var(--light-color, #f8f9fa);
    color: var(--primary-color, #007bff);
}

.mobile-nav-section {
    margin: 1rem 0;
}

.mobile-nav-title {
    font-weight: 600;
    color: var(--dark-color, #343a40);
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.mobile-nav-subitem {
    padding-left: 2rem;
    font-size: 0.9rem;
}

/* Animations */
.nav-link,
.dropdown-item,
.mobile-nav-item,
.mobile-nav-subitem {
    transition: all 0.2s ease-in-out;
}

.dropdown-menu {
    animation: fadeIn 0.15s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Responsive design */
@media (max-width: 991.98px) {
    .nav-menu {
        display: none;
    }

    .brand-title {
        font-size: 1.25rem;
    }

    .brand-logo {
        height: 35px;
    }

    .user-actions .d-none {
        display: none !important;
    }
}

@media (max-width: 575.98px) {
    .opac-navigation {
        padding: 0.5rem 0;
    }

    .brand-section {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .brand-title {
        font-size: 1.1rem;
    }

    .user-actions {
        flex-direction: column;
        align-items: stretch;
        gap: 0.25rem;
    }

    .guest-actions {
        flex-direction: column;
        gap: 0.25rem;
    }

    .guest-actions .btn {
        font-size: 0.9rem;
        padding: 0.5rem 0.75rem;
    }
}

/* Accessibilité */
.nav-link:focus,
.dropdown-item:focus,
.mobile-nav-item:focus {
    outline: 2px solid var(--primary-color, #007bff);
    outline-offset: 2px;
}

/* Mode sombre */
@media (prefers-color-scheme: dark) {
    .opac-navigation {
        --header-bg: #343a40;
        --header-color: #ffffff;
        --border-color: #495057;
    }

    .dropdown-menu {
        background-color: #343a40;
        border: 1px solid #495057;
    }

    .dropdown-item {
        color: #ffffff;
    }

    .dropdown-item:hover {
        background-color: #495057;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion de la navigation mobile
    const mobileToggle = document.querySelector('.mobile-nav-toggle');
    const mobileMenu = document.querySelector('#mobileNavMenu');

    if (mobileToggle && mobileMenu) {
        mobileToggle.addEventListener('click', function() {
            const icon = this.querySelector('i');
            const isExpanded = mobileMenu.classList.contains('show');

            if (isExpanded) {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            } else {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            }
        });

        // Fermer le menu mobile lors du clic sur un lien
        mobileMenu.addEventListener('click', function(e) {
            if (e.target.classList.contains('mobile-nav-item')) {
                const collapse = new bootstrap.Collapse(mobileMenu, { hide: true });
                const icon = mobileToggle.querySelector('i');
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
    }

    // Gestion du scroll pour la navigation sticky
    let lastScrollTop = 0;
    const navigation = document.querySelector('.opac-navigation');

    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

        if (scrollTop > lastScrollTop && scrollTop > 100) {
            // Scroll vers le bas - masquer la navigation
            navigation.style.transform = 'translateY(-100%)';
        } else {
            // Scroll vers le haut - afficher la navigation
            navigation.style.transform = 'translateY(0)';
        }

        lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
    }, { passive: true });

    // Amélioration de l'accessibilité pour les dropdowns
    document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });

    // Gestion des raccourcis clavier
    document.addEventListener('keydown', function(e) {
        // Alt + H = Accueil
        if (e.altKey && e.key === 'h') {
            e.preventDefault();
            window.location.href = "{{ route('opac.index') }}";
        }

        // Alt + S = Recherche
        if (e.altKey && e.key === 's') {
            e.preventDefault();
            const searchInput = document.querySelector('#opac-search-input');
            if (searchInput) {
                searchInput.focus();
            }
        }

        // Échap = Fermer les menus
        if (e.key === 'Escape') {
            document.querySelectorAll('.dropdown.show').forEach(dropdown => {
                bootstrap.Dropdown.getInstance(dropdown.querySelector('.dropdown-toggle'))?.hide();
            });

            if (mobileMenu?.classList.contains('show')) {
                bootstrap.Collapse.getInstance(mobileMenu)?.hide();
            }
        }
    });
});
</script>
@endpush
