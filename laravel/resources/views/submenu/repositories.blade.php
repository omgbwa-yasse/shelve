<div class="submenu-container py-2">
    <!-- Styles partagés via _submenu.scss -->

    <!-- Recherche Section - Archives -->
    @if(\App\Helpers\SubmenuPermissions::canAccessSubmenuSection('repositories', 'search'))
    <div class="submenu-section">
        <div class="submenu-heading" data-menu-action="toggle">
            <i class="bi bi-folder"></i> {{ __('search') }} — Archives
        </div>
        <div class="submenu-content" id="rechercheArchivesMenu">
            @can('viewAny', App\Models\Record::class)
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('records.index') }}">
                    <i class="bi bi-list-check"></i> {{ __('my_archives') }}
                </a>
            </div>
            @endcan

            @can('viewAny', App\Models\Record::class)
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('records.physical') }}">
                    <i class="bi bi-archive"></i> {{ __('Physical Records') }}
                </a>
            </div>
            @endcan

            @can('viewAny', App\Models\Record::class)
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('records.trash') }}">
                    <i class="bi bi-trash"></i> Corbeille
                </a>
            </div>
            @endcan
        </div>
    </div>

    <!-- Recherche Section - Par critère -->
    <div class="submenu-section">
        <div class="submenu-heading" data-menu-action="toggle">
            <i class="bi bi-search"></i> {{ __('search') }} — Critères
        </div>
        <div class="submenu-content" id="rechercheCriteresMenu">
            @can('viewAny', App\Models\Author::class)
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('record-author.index') }}">
                    <i class="bi bi-person"></i> {{ __('holders') }}
                </a>
            </div>
            @endcan
            @can('viewAny', App\Models\Record::class)
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('record-select-date')}}">
                    <i class="bi bi-calendar"></i> {{ __('dates') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('record-select-word')}}">
                    <i class="bi bi-key"></i> {{ __('keywords') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('record-select-activity')}}">
                    <i class="bi bi-briefcase"></i> {{ __('activities') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('record-select-building')}}">
                    <i class="bi bi-building"></i> {{ __('premises') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('record-select-last')}}">
                    <i class="bi bi-clock-history"></i> {{ __('recent') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('records.advanced.form')}}">
                    <i class="bi bi-search"></i> {{ __('advanced') }}
                </a>
            </div>
            @endcan
        </div>
    </div>
    @endif

    <!-- Enregistrement Section -->
    @if(\App\Helpers\SubmenuPermissions::canAccessSubmenuSection('repositories', 'add'))
    <div class="submenu-section add-section">
        <div class="submenu-heading" data-menu-action="toggle">
            <i class="bi bi-journal-plus"></i> {{ __('registration') }}
        </div>
        <div class="submenu-content" id="enregistrementMenu">
            @can('create', App\Models\Record::class)
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('records.create') }}">
                    <i class="bi bi-plus-square"></i> {{ __('new') }}
                </a>
            </div>
            @endcan

            @can('create', App\Models\Author::class)
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('record-author.create') }}">
                    <i class="bi bi-person-plus"></i> {{ __('author') }}
                </a>
            </div>
            @endcan

            @can('create', App\Models\Record::class)
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('records.drag-drop') }}">
                    <i class="bi bi-cloud-upload"></i> Drag & Drop
                </a>
            </div>
            @endcan
        </div>
    </div>
    @endif

    <!-- Import / Export Section -->
    @if(\App\Helpers\SubmenuPermissions::canAccessSubmenuSection('repositories', 'tools'))
    <div class="submenu-section">
        <div class="submenu-heading" data-menu-action="toggle">
            <i class="bi bi-arrow-down-up"></i> {{ __('import_export') }} (EAD, Excel, SEDA)
        </div>
        <div class="submenu-content" id="importExportMenu">
            @can('viewAny', App\Models\Record::class)
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('records.tree.view') }}">
                    <i class="bi bi-diagram-3"></i> {{ __('Arbre des notices') }}
                </a>
            </div>
            @endcan
        </div>
    </div>
    @endif
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fonctionnalité de collapse optionnelle pour les sous-menus
    const headings = document.querySelectorAll('[data-menu-action="toggle"]');

    headings.forEach(function(heading) {
        heading.addEventListener('click', function(event) {
            // Important: utiliser currentTarget pour référencer l'élément qui a l'écouteur d'événement
            // et non pas nécessairement l'élément sur lequel l'utilisateur a cliqué
            const clickedHeading = event.currentTarget;
            const content = clickedHeading.nextElementSibling;

            if (content && content.classList.contains('submenu-content')) {
                // Toggle la classe collapsed
                content.classList.toggle('collapsed');
                clickedHeading.classList.toggle('collapsed');

                // Empêcher seulement la navigation par défaut, sans perturber le reste du document
                event.preventDefault();
            }
        });
    });

    // Fix pour formulaires - s'assurer que les éléments de formulaire fonctionnent correctement
    const formElements = document.querySelectorAll('input, select, textarea, button');
    formElements.forEach(function(element) {
        // S'assurer que les événements de formulaire sont toujours traités correctement
        element.addEventListener('click', function(event) {
            event.stopPropagation();
        });
    });
});</script>
