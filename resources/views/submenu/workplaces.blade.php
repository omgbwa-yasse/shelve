<div class="submenu-container py-2">
    <!-- WorkPlace Section -->
    <div class="submenu-section">
        <div class="submenu-heading" data-menu-action="toggle">
            <i class="bi bi-briefcase"></i> {{ __('Espaces de travail') }}
        </div>
        <div class="submenu-content" id="workplaceMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('workplaces.index') }}">
                    <i class="bi bi-grid"></i> {{ __('Tous les workplaces') }}
                </a>
            </div>

            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('workplaces.index', ['status' => 'active']) }}">
                    <i class="bi bi-check-circle"></i> {{ __('Actifs') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('workplaces.index', ['status' => 'archived']) }}">
                    <i class="bi bi-archive"></i> {{ __('Archivés') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Categories Section -->
    <div class="submenu-section">
        <div class="submenu-heading" data-menu-action="toggle">
            <i class="bi bi-plus-circle"></i> {{ __('Actions') }}
        </div>
        <div class="submenu-content" id="actionsMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('workplaces.create') }}">
                    <i class="bi bi-plus-square"></i> {{ __('Nouveau workplace') }}
                </a>
            </div>
        </div>

    </div>

    <!-- My WorkPlaces Section -->
    <div class="submenu-section">
        <div class="submenu-heading" data-menu-action="toggle">
            <i class="bi bi-person-workspace"></i> {{ __('Mes espaces') }}
        </div>
        <div class="submenu-content" id="myWorkplacesMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('workplaces.index', ['owner' => 'me']) }}">
                    <i class="bi bi-person-badge"></i> {{ __('Propriétaire') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('workplaces.index', ['member' => 'me']) }}">
                    <i class="bi bi-person-check"></i> {{ __('Membre') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('workplaces.index', ['is_public' => 1]) }}">
                    <i class="bi bi-globe"></i> {{ __('Publics') }}
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Collapse functionality
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
});
</script>
