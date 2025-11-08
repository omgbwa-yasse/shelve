<div class="submenu-container py-2">
    <!-- Styles partagés via _submenu.scss -->

    <!-- Recherche Section -->
    @if(\App\Helpers\SubmenuPermissions::canAccessSubmenuSection('repositories', 'search'))
    <div class="submenu-section">
        <div class="submenu-heading" data-menu-action="toggle">
            <i class="bi bi-search"></i> {{ __('search') }}
        </div>
        <div class="submenu-content" id="rechercheMenu">
            @can('viewAny', App\Models\Record::class)
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('records.index') }}">
                    <i class="bi bi-list-check"></i> {{ __('my_archives') }}
                </a>
            </div>
            @endcan
            
            @can('viewAny', App\Models\RecordPhysical::class)
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('records.index') }}?type=physical">
                    <i class="bi bi-archive"></i> {{ __('Physical Records') }}
                </a>
            </div>
            @endcan
            
            @can('viewAny', App\Models\RecordDigitalFolder::class)
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('folders.index') }}">
                    <i class="bi bi-folder"></i> {{ __('Digital Folders') }}
                </a>
            </div>
            @endcan
            
            @can('viewAny', App\Models\RecordDigitalDocument::class)
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('documents.index') }}">
                    <i class="bi bi-file-earmark-text"></i> {{ __('Digital Documents') }}
                </a>
            </div>
            @endcan
            
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
            @can('create', App\Models\RecordPhysical::class)
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('records.create') }}">
                    <i class="bi bi-plus-square"></i> {{ __('new') }} {{ __('(Physical)') }}
                </a>
            </div>
            @endcan

            @can('create', App\Models\RecordDigitalFolder::class)
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('folders.create') }}">
                    <i class="bi bi-folder-plus"></i> {{ __('Folder (Digital)') }}
                </a>
            </div>
            @endcan

            @can('create', App\Models\RecordDigitalDocument::class)
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('documents.create') }}">
                    <i class="bi bi-file-earmark-plus"></i> {{ __('Document (Digital)') }}
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

    <!-- lifeCycle Section -->
    @can('viewAny', App\Models\Record::class)
    <div class="submenu-section">
        <div class="submenu-heading" data-menu-action="toggle">
            <i class="bi bi-cart"></i> {{ __('life_cycle') }}
        </div>
        <div class="submenu-content" id="lifeCycleMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('records.tostore')}}">
                    <i class="bi bi-folder-check"></i> {{ __('to_transfer') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('records.toretain')}}">
                    <i class="bi bi-folder-check"></i> {{ __('active_files') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('records.totransfer')}}">
                    <i class="bi bi-arrow-right-square"></i> {{ __('to_deposit') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('records.toeliminate')}}">
                    <i class="bi bi-trash"></i> {{ __('to_eliminate') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('records.tokeep')}}">
                    <i class="bi bi-archive"></i> {{ __('to_keep') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('records.tosort')}}">
                    <i class="bi bi-sort-down"></i> {{ __('to_sort') }}
                </a>
            </div>
        </div>
    </div>
    @endcan

    <!-- Import / Export Section -->
    @if(\App\Helpers\SubmenuPermissions::canAccessSubmenuSection('repositories', 'tools'))
    <div class="submenu-section">
        <div class="submenu-heading" data-menu-action="toggle">
            <i class="bi bi-arrow-down-up"></i> {{ __('import_export') }} (EAD, Excel, SEDA)
        </div>
        <div class="submenu-content" id="importExportMenu">
            @can('records_import')
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('records.import.form') }}">
                    <i class="bi bi-download"></i> {{ __('record_import') }}
                </a>
            </div>
            @endcan
            @can('records_export')
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('records.export.form') }}">
                    <i class="bi bi-upload"></i> {{ __('record_export') }}
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
