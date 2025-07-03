<div class="submenu-container submenu-repos        .submenu-repositories-isolated .submenu-link {
            display: flex;
            align-items: center;
            padding: 4px 8px;
            color: var(--text);
            text-decoration: none;
            border-radius: 4px;
            transition: all 0.2s ease;
            font-size: 12.5px;
        }olated py-2">
    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>        .submenu-container {
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            z-index: 10;
            position: relative;
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

        .submenu-content { padding: 0 0 8px 12px; margin-bottom: 8px; display: block; /* Toujours visible par défaut */ }

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

        .add-section .submenu-heading {
            background-color: #34a853;
        }

        .add-section .submenu-heading:hover {
            background-color: #188038;
        }

        /* Style pour les sections collapsibles */
        .submenu-content.collapsed {
            display: none;
        }

        .submenu-heading::after {
            content: '\F282'; /* Flèche bas (bootstrap icons) */
            margin-left: auto;
            font-family: 'bootstrap-icons';
            font-size: 14px;
            transition: transform 0.2s ease;
        }

        .submenu-heading.collapsed::after {
            transform: rotate(-90deg);
        }        /* Amélioration pour indiquer la cliquabilité */
        .submenu-heading {
            position: relative;
            overflow: hidden;
            user-select: none; /* Éviter la sélection de texte */
        }

        .submenu-heading::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            opacity: 0;
            transition: opacity 0.2s ease;
            pointer-events: none; /* S'assurer que cet élément ne capture pas les événements */
        }

        .submenu-heading:hover::before {
            opacity: 1;
        }
    </style>

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
                    <i class="bi bi-plus-square"></i> {{ __('producer') }}
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
