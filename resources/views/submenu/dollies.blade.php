<div class="submenu-container py-2">
    <!-- Styles partagés via _submenu.scss -->

    <!-- Search Section -->
    @can('viewAny', App\Models\Dolly::class)
    <div class="submenu-section">
        <div class="submenu-heading" >
            <i class="bi bi-search"></i> {{ __('search') }}
        </div>
        <div class="submenu-content" id="searchMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('dolly.index') }}">
                    <i class="bi bi-cart3"></i> {{ __('all_carts') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('dollies-sort')}}?categ=mail">
                    <i class="bi bi-cart3"></i> {{ __('mail') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('dollies-sort')}}?categ=record">
                    <i class="bi bi-cart3"></i> {{ __('archives') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('dollies-sort')}}?categ=communication">
                    <i class="bi bi-cart3"></i> {{ __('communication') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('dollies-sort')}}?categ=room">
                    <i class="bi bi-cart3"></i> {{ __('room') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('dollies-sort')}}?categ=shelf">
                    <i class="bi bi-cart3"></i> {{ __('shelf') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('dollies-sort')}}?categ=container">
                    <i class="bi bi-cart3"></i> {{ __('archive_boxes') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('dollies-sort')}}?categ=slip_record">
                    <i class="bi bi-cart3"></i> {{ __('archives_transfer') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('dollies-sort')}}?categ=slip">
                    <i class="bi bi-cart3"></i> {{ __('transfer') }}
                </a>
            </div>
        </div>
    </div>
    @endcan

    <!-- Création Section -->
    @can('create', App\Models\Dolly::class)
    <div class="submenu-section add-section">
        <div class="submenu-heading" >
            <i class="bi bi-plus-circle"></i> Création
        </div>
        <div class="submenu-content" id="createMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('dolly.create') }}">
                    <i class="bi bi-cart3"></i> {{ __('cart') }}
                </a>
            </div>
        </div>
    </div>
    @endcan
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
