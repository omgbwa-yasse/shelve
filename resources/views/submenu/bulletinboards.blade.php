<div class="bulletinboards-submenu" id="bulletinBoardsMenu">
    <!-- Styles partagés via _submenu.scss -->

    <!-- Notifications Section -->
    <div class="submenu-section">
        <div class="submenu-heading">
            <i class="bi bi-bell"></i> {{ __('Notifications') }}
        </div>
        <div class="submenu-section-content" id="notificationsSection">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('notifications.current') }}">
                    <i class="bi bi-person"></i> Mes Notifications
                </a>
            </div>
            @if(auth()->user() && auth()->user()->current_organisation_id)
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('notifications.organisation') }}">
                    <i class="bi bi-building"></i> Notifications Organisation
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Gestion Section -->
    @can('viewAny', App\Models\BulletinBoard::class)
    <div class="submenu-section">
        <div class="submenu-heading">
            <i class="bi bi-clipboard"></i> {{ __('Gestion') }}
        </div>
        <div class="submenu-section-content" id="gestionSection">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('bulletin-boards.index') }}">
                    <i class="bi bi-list-ul"></i> {{ __('view_all') }}
                </a>
            </div>
        </div>
    </div>
    @endcan

    <!-- Add Section -->
    @can('create', App\Models\BulletinBoard::class)
    <div class="submenu-section add-section">
        <div class="submenu-heading">
            <i class="bi bi-plus-circle"></i> {{ __('add') }}
        </div>
        <div class="submenu-section-content" id="addSection">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('bulletin-boards.create') }}">
                    <i class="bi bi-clipboard-plus"></i> {{ __('Nouveau bulletin') }}
                </a>
            </div>
        </div>
    </div>
    @endcan
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Bulletin Boards submenu initialized');

    // Sélectionner uniquement les éléments de notre sous-menu
    const menuContainer = document.getElementById('bulletinBoardsMenu');
    if (!menuContainer) {
        console.error('Bulletin Boards menu container not found');
        return;
    }

    const headings = menuContainer.querySelectorAll('.submenu-heading');
    console.log('Found headings:', headings.length);

    headings.forEach(function(heading) {
        heading.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const content = this.nextElementSibling;
            console.log('Clicked heading:', this.textContent.trim());
            console.log('Content element:', content);

            if (content && content.classList.contains('submenu-section-content')) {
                // Toggle les classes
                const isCollapsed = content.classList.contains('collapsed');

                if (isCollapsed) {
                    content.classList.remove('collapsed');
                    this.classList.remove('collapsed');
                    console.log('Expanded section');
                } else {
                    content.classList.add('collapsed');
                    this.classList.add('collapsed');
                    console.log('Collapsed section');
                }
            } else {
                console.error('Content element not found or invalid');
            }
        });
    });

    // S'assurer que tous les menus sont ouverts par défaut
    const allContents = menuContainer.querySelectorAll('.submenu-section-content');
    const allMenuHeadings = menuContainer.querySelectorAll('.submenu-heading');

    allContents.forEach(function(content) {
        content.classList.remove('collapsed');
    });

    allMenuHeadings.forEach(function(heading) {
        heading.classList.remove('collapsed');
    });

    console.log('All sections opened by default');
});
</script>

