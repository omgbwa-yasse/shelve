<div class="submenu-container py-2">
    <!-- Styles partagés via _submenu.scss -->

    <!-- Collections Section -->
    <div class="submenu-section">
        <div class="submenu-heading" data-menu-action="toggle">
            <i class="bi bi-collection"></i> {{ __('Collections') }}
        </div>
        <div class="submenu-content" id="collectionsMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('museum.collections.index') }}">
                    <i class="bi bi-list-check"></i> {{ __('Toutes les collections') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('museum.artifacts.index') }}">
                    <i class="bi bi-palette"></i> {{ __('Artefacts') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('museum.artifacts.create') }}">
                    <i class="bi bi-plus-square"></i> {{ __('Nouvel artefact') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('museum.search.index') }}">
                    <i class="bi bi-search"></i> {{ __('Recherche') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Catalogage Section -->
    <div class="submenu-section add-section">
        <div class="submenu-heading" data-menu-action="toggle">
            <i class="bi bi-journal-plus"></i> {{ __('Catalogage') }}
        </div>
        <div class="submenu-content" id="catalogageMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('museum.artifacts.create') }}">
                    <i class="bi bi-plus-square"></i> {{ __('Nouvelle pièce') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('museum.artifacts.index') }}">
                    <i class="bi bi-list"></i> {{ __('Liste artefacts') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('museum.search.advanced') }}">
                    <i class="bi bi-funnel"></i> {{ __('Recherche avancée') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('museum.inventory.index') }}">
                    <i class="bi bi-clipboard-data"></i> {{ __('Inventaire') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Conservation Section -->
    <div class="submenu-section">
        <div class="submenu-heading" data-menu-action="toggle">
            <i class="bi bi-shield-check"></i> {{ __('Conservation') }}
        </div>
        <div class="submenu-content" id="conservationMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('museum.conservation.index') }}">
                    <i class="bi bi-list"></i> {{ __('Rapports conservation') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('museum.conservation.create') }}">
                    <i class="bi bi-plus-square"></i> {{ __('Nouveau rapport') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('museum.reports.conservation') }}">
                    <i class="bi bi-clipboard-check"></i> {{ __('État de conservation') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('museum.inventory.index') }}">
                    <i class="bi bi-thermometer"></i> {{ __('Conditions stockage') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Expositions Section -->
    <div class="submenu-section">
        <div class="submenu-heading" data-menu-action="toggle">
            <i class="bi bi-easel"></i> {{ __('Expositions') }}
        </div>
        <div class="submenu-content" id="expositionsMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('museum.exhibitions.index') }}">
                    <i class="bi bi-list"></i> {{ __('Toutes les expositions') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('museum.exhibitions.index', ['status' => 'current']) }}">
                    <i class="bi bi-calendar-check"></i> {{ __('En cours') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('museum.exhibitions.index', ['status' => 'upcoming']) }}">
                    <i class="bi bi-calendar-plus"></i> {{ __('À venir') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('museum.reports.exhibitions') }}">
                    <i class="bi bi-archive"></i> {{ __('Rapport expositions') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Inventaire Section -->
    <div class="submenu-section">
        <div class="submenu-heading" data-menu-action="toggle">
            <i class="bi bi-clipboard-data"></i> {{ __('Inventaire') }}
        </div>
        <div class="submenu-content" id="inventaireMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('museum.inventory.index') }}">
                    <i class="bi bi-clipboard-check"></i> {{ __('Dashboard inventaire') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('museum.inventory.recolement') }}">
                    <i class="bi bi-check2-square"></i> {{ __('Récolement') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('museum.collections.index') }}">
                    <i class="bi bi-map"></i> {{ __('Par collection') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('museum.reports.collection') }}">
                    <i class="bi bi-file-earmark-text"></i> {{ __('Rapport inventaire') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Recherche Section -->
    <div class="submenu-section">
        <div class="submenu-heading" data-menu-action="toggle">
            <i class="bi bi-search"></i> {{ __('Recherche') }}
        </div>
        <div class="submenu-content" id="rechercheMuseumMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('museum.search.index') }}">
                    <i class="bi bi-search"></i> {{ __('Recherche simple') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('museum.search.advanced') }}">
                    <i class="bi bi-funnel"></i> {{ __('Recherche avancée') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('museum.collections.index') }}">
                    <i class="bi bi-collection"></i> {{ __('Par collection') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('museum.artifacts.index') }}">
                    <i class="bi bi-list-ul"></i> {{ __('Liste complète') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Rapports & Statistiques Section -->
    <div class="submenu-section">
        <div class="submenu-heading" data-menu-action="toggle">
            <i class="bi bi-file-earmark-bar-graph"></i> {{ __('Rapports & Statistiques') }}
        </div>
        <div class="submenu-content" id="rapportsMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('museum.reports.index') }}">
                    <i class="bi bi-speedometer2"></i> {{ __('Dashboard rapports') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('museum.reports.statistics') }}">
                    <i class="bi bi-bar-chart"></i> {{ __('Statistiques générales') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('museum.reports.valuation') }}">
                    <i class="bi bi-cash-stack"></i> {{ __('Valorisation') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('museum.reports.collection') }}">
                    <i class="bi bi-file-earmark-text"></i> {{ __('Rapport collection') }}
                </a>
            </div>
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
