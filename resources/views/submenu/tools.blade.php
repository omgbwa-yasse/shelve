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

        .submenu-content { padding: 0 0 8px 12px; margin-bottom: 8px; display: block; /* Toujours visible par d�faut */ }

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
            content: '';
            margin-left: auto;
            font-family: 'bootstrap-icons';
            font-size: 12px;
            transition: transform 0.2s ease;
        }

        .submenu-heading.collapsed::after {
            transform: rotate(-90deg);
        }
    </style>

    <!-- Plan de classement Section -->
    @can('viewAny', App\Models\Activity::class)
    <div class="submenu-section">
        <div class="submenu-heading" >
            <i class="bi bi-grid"></i> {{ __('classification_plan') }}
        </div>
        <div class="submenu-content" id="planClassementMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('activities.index') }}">
                    <i class="bi bi-list-check"></i> {{ __('all_classes') }}
                </a>
            </div>
            @can('create', App\Models\Activity::class)
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('activities.create') }}">
                    <i class="bi bi-plus-square"></i> {{ __('add_class') }}
                </a>
            </div>
            @endcan
        </div>
    </div>
    @endcan

    <!-- Référentiel de conservation Section -->
    @can('viewAny', App\Models\Retention::class)
    <div class="submenu-section">
        <div class="submenu-heading" >
            <i class="bi bi-archive"></i> {{ __('retention_schedule') }}
        </div>
        <div class="submenu-content" id="referentielConservationMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('retentions.index') }}">
                    <i class="bi bi-clock-history"></i> {{ __('all_durations') }}
                </a>
            </div>
            @can('create', App\Models\Retention::class)
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('retentions.create') }}">
                    <i class="bi bi-plus-square"></i> {{ __('add_rule') }}
                </a>
            </div>
            @endcan
        </div>
    </div>
    @endcan

    <!-- Communicabilité Section -->
    @can('viewAny', App\Models\Communicability::class)
    <div class="submenu-section">
        <div class="submenu-heading" >
            <i class="bi bi-chat-square-text"></i> {{ __('communicability') }}
        </div>
        <div class="submenu-content" id="communicabiliteMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('communicabilities.index')}}">
                    <i class="bi bi-list-check"></i> {{ __('all_classes') }}
                </a>
            </div>
            @can('create', App\Models\Communicability::class)
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('communicabilities.create')}}">
                    <i class="bi bi-plus-square"></i> {{ __('add_class') }}
                </a>
            </div>
            @endcan
        </div>
    </div>
    @endcan

    <!-- Organigramme Section -->
    @can('viewAny', App\Models\Organisation::class)
    <div class="submenu-section">
        <div class="submenu-heading" >
            <i class="bi bi-diagram-3"></i> {{ __('organization_chart') }}
        </div>
        <div class="submenu-content" id="organigrammeMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('organisations.index')}}">
                    <i class="bi bi-building"></i> {{ __('all_units') }}
                </a>
            </div>
            @can('create', App\Models\Organisation::class)
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('organisations.create')}}">
                    <i class="bi bi-plus-square"></i> {{ __('add_organization') }}
                </a>
            </div>
            @endcan
        </div>
    </div>
    @endcan

    <!-- Thésaurus Section -->

    <div class="submenu-section">
        <div class="submenu-heading" >
            <i class="bi bi-book-half"></i> {{ __('thesaurus') }}
        </div>
        <div class="submenu-content" id="thesaurusMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('thesaurus.index') }}">
                    <i class="bi bi-house"></i> {{ __('thesaurus_home') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('thesaurus.hierarchy') }}">
                    <i class="bi bi-tree"></i> {{ __('view_branches') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('thesaurus.schemes.index') }}">
                    <i class="bi bi-card-list"></i> {{ __('Schémas de thésaurus') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('thesaurus.search.index') }}">
                    <i class="bi bi-search"></i> {{ __('search_thesaurus') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('thesaurus.export-import') }}">
                    <i class="bi bi-arrow-down-up"></i> {{ __('import_export') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('thesaurus.concepts') }}">
                    <i class="bi bi-plus-square"></i> {{ __('add_word') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('thesaurus.concepts') }}">
                    <i class="bi bi-diagram-2"></i> {{ __('term_hierarchy') }}
                </a>
            </div>
        </div>
    </div>

    @auth
    <div class="submenu-section">
        <div class="submenu-heading" >
            <i class="bi bi-tools"></i> {{ __('toolbox') }}
        </div>
        <div class="submenu-content" id="outilsMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('barcode.create') }}">
                    <i class="bi bi-upc-scan"></i> {{ __('barcode') }}
                </a>
            </div>
        </div>
    </div>
    @endauth
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fonctionnalité de collapse optionnelle pour les sous-menus
    const headings = document.querySelectorAll('.submenu-heading');

    headings.forEach(function(heading) {
        heading.addEventListener('click', function() {
            const content = this.nextElementSibling;

            if (content && content.classList.contains('submenu-content')) {
                // Toggle la classe collapsed
                content.classList.toggle('collapsed');
                this.classList.toggle('collapsed');
            }
        });
    });
});
</script>
