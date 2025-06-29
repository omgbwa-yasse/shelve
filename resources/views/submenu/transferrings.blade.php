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
    </style>    <!-- Recherche Section -->
    @if(\App\Helpers\SubmenuPermissions::canAccessSubmenuSection('transferrings', 'search'))
    <div class="submenu-section">
        <div class="submenu-heading" >
            <i class="bi bi-search"></i> {{ __('search') }}
        </div>
        <div class="submenu-content" id="rechercheMenu">
            @can('viewAny', App\Models\Slip::class)
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('search.slips.advanced') }}">
                    <i class="bi bi-search"></i> {{ __('advanced') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('slips.index') }}">
                    <i class="bi bi-building"></i> {{ __('my_slips') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('slips-select-date') }}">
                    <i class="bi bi-list"></i> {{ __('dates') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('slips-select-organisation') }}?categ=organisation">
                    <i class="bi bi-list"></i> {{ __('organizations') }}
                </a>
            </div>
            @endcan
        </div>
    </div>
    @endif

    <!-- Suivi de transfert Section -->
    @can('viewAny', App\Models\Slip::class)
    <div class="submenu-section">
        <div class="submenu-heading" >
            <i class="bi bi-arrow-right-circle"></i> {{ __('transfer_tracking') }}
        </div>
        <div class="submenu-content" id="suiviTransfertMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('slips-sort') }}?categ=project">
                    <i class="bi bi-folder"></i> {{ __('projects') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('slips-sort') }}?categ=received">
                    <i class="bi bi-envelope-check"></i> {{ __('received') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('slips-sort') }}?categ=approved">
                    <i class="bi bi-check-circle"></i> {{ __('approved') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('slips-sort') }}?categ=integrated">
                    <i class="bi bi-folder-plus"></i> {{ __('integrated') }}
                </a>
            </div>
        </div>
    </div>
    @endcan

    <!-- Création Section -->
    @if(\App\Helpers\SubmenuPermissions::canAccessSubmenuSection('transferrings', 'add'))
    <div class="submenu-section add-section">
        <div class="submenu-heading" >
            <i class="bi bi-plus-circle"></i> {{ __('create') }}
        </div>
        <div class="submenu-content" id="enregistrementMenu">
            @can('create', App\Models\Slip::class)
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('slips.create') }}">
                    <i class="bi bi-building"></i> {{ __('slip') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('slips.containers.index') }}">
                    <i class="bi bi-archive"></i> {{ __('box_chrono') }}
                </a>
            </div>
            @endcan
        </div>
    </div>
    @endif

    <!-- Import / Export Section -->
    @if(\App\Helpers\SubmenuPermissions::canAccessSubmenuSection('transferrings', 'tools'))
    <div class="submenu-section">
        <div class="submenu-heading" >
            <i class="bi bi-arrow-down-up"></i> {{ __('import_export') }} (EAD, Excel, SEDA)
        </div>
        <div class="submenu-content" id="importExportMenu">
            @can('import', App\Models\Slip::class)
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('slips.import.form') }}">
                    <i class="bi bi-download"></i> {{ __('import') }}
                </a>
            </div>
            @endcan
            @can('export', App\Models\Slip::class)
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('slips.export.form') }}">
                    <i class="bi bi-upload"></i> {{ __('export') }}
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
