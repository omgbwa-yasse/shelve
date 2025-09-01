<div class="submenu-container py-2">
    <!-- Styles partagés via _submenu.scss -->


    <!-- Search Section -->
    <div class="submenu-section">
        <div class="submenu-heading" >
            <i class="bi bi-search"></i> {{ __('search') }}
        </div>
        <div class="submenu-content" id="searchMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('buildings.index') }}">
                    <i class="bi bi-building"></i> {{ __('building') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('rooms.index') }}">
                    <i class="bi bi-house"></i> {{ __('room') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('shelves.index') }}">
                    <i class="bi bi-bookshelf"></i> {{ __('shelve') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('containers.index') }}">
                    <i class="bi bi-box"></i> {{ __('archive_container') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Création Section -->
    <div class="submenu-section add-section">
        <div class="submenu-heading" >
            <i class="bi bi-plus-circle"></i> Création
        </div>
        <div class="submenu-content" id="createMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('buildings.create') }}">
                    <i class="bi bi-building"></i> {{ __('building') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('rooms.create') }}">
                    <i class="bi bi-house"></i> {{ __('room') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('shelves.create') }}">
                    <i class="bi bi-bookshelf"></i> {{ __('shelve') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('containers.create') }}">
                    <i class="bi bi-archive"></i> {{ __('archive_container') }}
                </a>
            </div>
        </div>
    </div>
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

<style>
</style>
