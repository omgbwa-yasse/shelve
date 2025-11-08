<div class="submenu-container py-2">
    <!-- Styles partagés via _submenu.scss -->

    <!-- Catalogue Section -->
    <div class="submenu-section">
        <div class="submenu-heading" data-menu-action="toggle">
            <i class="bi bi-book"></i> {{ __('Catalogue') }}
        </div>
        <div class="submenu-content" id="catalogueMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('library.books.index') }}">
                    <i class="bi bi-list-check"></i> {{ __('Tous les ouvrages') }}
                </a>
            </div>
            @if(Route::has('library.authors.index'))
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('library.authors.index') }}">
                    <i class="bi bi-person"></i> {{ __('Auteurs') }}
                </a>
            </div>
            @endif

            @if(Route::has('library.periodicals.index'))
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('library.periodicals.index') }}">
                    <i class="bi bi-newspaper"></i> {{ __('Périodiques') }}
                </a>
            </div>
            @endif

            @if(Route::has('library.search.index'))
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('library.search.index') }}">
                    <i class="bi bi-search"></i> {{ __('Recherche') }}
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Gestion Section -->
    <div class="submenu-section add-section">
        <div class="submenu-heading" data-menu-action="toggle">
            <i class="bi bi-journal-plus"></i> {{ __('Gestion') }}
        </div>
        <div class="submenu-content" id="gestionMenu">
            @if(Route::has('library.books.create'))
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('library.books.create') }}">
                    <i class="bi bi-plus-square"></i> {{ __('Nouvel ouvrage') }}
                </a>
            </div>
            @endif

            @if(Route::has('library.authors.create'))
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('library.authors.create') }}">
                    <i class="bi bi-person-plus"></i> {{ __('Nouvel auteur') }}
                </a>
            </div>
            @endif

            @if(Route::has('library.statistics.categories'))
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('library.statistics.categories') }}">
                    <i class="bi bi-tag"></i> {{ __('Catégories') }}
                </a>
            </div>
            @endif

            @if(Route::has('library.search.advanced'))
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('library.search.advanced') }}">
                    <i class="bi bi-funnel"></i> {{ __('Recherche avancée') }}
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Prêts Section -->
    <div class="submenu-section">
        <div class="submenu-heading" data-menu-action="toggle">
            <i class="bi bi-arrow-left-right"></i> {{ __('Prêts') }}
        </div>
        <div class="submenu-content" id="pretsMenu">
            @if(Route::has('library.loans.create'))
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('library.loans.create') }}">
                    <i class="bi bi-box-arrow-right"></i> {{ __('Nouveau prêt') }}
                </a>
            </div>
            @endif

            @if(Route::has('library.loans.index'))
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('library.loans.index') }}">
                    <i class="bi bi-list"></i> {{ __('Prêts en cours') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('library.loans.index', ['status' => 'active']) }}">
                    <i class="bi bi-box-arrow-in-left"></i> {{ __('Retours') }}
                </a>
            </div>
            @endif

            @if(Route::has('library.loans.overdue'))
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('library.loans.overdue') }}">
                    <i class="bi bi-exclamation-triangle"></i> {{ __('Retards') }}
                </a>
            </div>
            @endif

            @if(Route::has('library.loans.history'))
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('library.loans.history') }}">
                    <i class="bi bi-clock-history"></i> {{ __('Historique') }}
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Lecteurs Section -->
    <div class="submenu-section">
        <div class="submenu-heading" data-menu-action="toggle">
            <i class="bi bi-people"></i> {{ __('Lecteurs') }}
        </div>
        <div class="submenu-content" id="lecteursMenu">
            @if(Route::has('library.readers.index'))
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('library.readers.index') }}">
                    <i class="bi bi-list-check"></i> {{ __('Tous les lecteurs') }}
                </a>
            </div>
            @endif

            @if(Route::has('library.readers.create'))
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('library.readers.create') }}">
                    <i class="bi bi-person-plus"></i> {{ __('Nouveau lecteur') }}
                </a>
            </div>
            @endif

            @if(Route::has('library.reports.readers'))
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('library.reports.readers') }}">
                    <i class="bi bi-card-checklist"></i> {{ __('Rapport lecteurs') }}
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Statistiques & Rapports Section -->
    <div class="submenu-section">
        <div class="submenu-heading" data-menu-action="toggle">
            <i class="bi bi-graph-up"></i> {{ __('Statistiques & Rapports') }}
        </div>
        <div class="submenu-content" id="statistiquesMenu">
            @if(Route::has('library.statistics.index'))
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('library.statistics.index') }}">
                    <i class="bi bi-speedometer2"></i> {{ __('Dashboard') }}
                </a>
            </div>
            @endif

            @if(Route::has('library.statistics.loans'))
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('library.statistics.loans') }}">
                    <i class="bi bi-bar-chart"></i> {{ __('Statistiques prêts') }}
                </a>
            </div>
            @endif

            @if(Route::has('library.statistics.categories'))
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('library.statistics.categories') }}">
                    <i class="bi bi-pie-chart"></i> {{ __('Par catégories') }}
                </a>
            </div>
            @endif

            @if(Route::has('library.reports.index'))
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('library.reports.index') }}">
                    <i class="bi bi-file-earmark-bar-graph"></i> {{ __('Rapports') }}
                </a>
            </div>
            @endif

            @if(Route::has('library.reports.overdue'))
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('library.reports.overdue') }}">
                    <i class="bi bi-exclamation-circle"></i> {{ __('Rapport retards') }}
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fonctionnalité de collapse optionnelle pour les sous-menus
    const headings = document.querySelectorAll('[data-menu-action="toggle"]');

    headings.forEach(function(heading) {
        heading.addEventListener('click', function(event) {
            const clickedHeading = event.currentTarget;
            const content = clickedHeading.nextElementSibling;

            if (content && content.classList.contains('submenu-content')) {
                content.classList.toggle('collapsed');
                clickedHeading.classList.toggle('collapsed');
                event.preventDefault();
            }
        });
    });

    // Fix pour formulaires
    const formElements = document.querySelectorAll('input, select, textarea, button');
    formElements.forEach(function(element) {
        element.addEventListener('click', function(event) {
            event.stopPropagation();
        });
    });
});
</script>
