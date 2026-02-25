<div class="submenu-container py-2">
    <!-- Styles partagés via _submenu.scss -->
    <!-- Recherche Section -->
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
            <i class="bi bi-plus-circle"></i> Création
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

    <!-- Cycle de vie Section -->
    @can('viewAny', App\Models\Record::class)
    <div class="submenu-section">
        <div class="submenu-heading">
            <i class="bi bi-cart"></i> {{ __('life_cycle') }}
        </div>
        <div class="submenu-content" id="lifeCycleMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('records.tostore') }}">
                    <i class="bi bi-folder-check"></i> {{ __('to_transfer') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('records.toretain') }}">
                    <i class="bi bi-folder-check"></i> {{ __('active_files') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('records.totransfer') }}">
                    <i class="bi bi-arrow-right-square"></i> {{ __('to_deposit') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('records.toeliminate') }}">
                    <i class="bi bi-trash"></i> {{ __('to_eliminate') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('records.tokeep') }}">
                    <i class="bi bi-archive"></i> {{ __('to_keep') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('records.tosort') }}">
                    <i class="bi bi-sort-down"></i> {{ __('to_sort') }}
                </a>
            </div>
        </div>
    </div>
    @endcan

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
