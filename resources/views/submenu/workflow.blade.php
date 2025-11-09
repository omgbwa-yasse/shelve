<div class="submenu-container py-2">
    <!-- Workflow Section -->
    <div class="submenu-section">
        <div class="submenu-heading" data-menu-action="toggle">
            <i class="bi bi-diagram-3"></i> {{ __('Workflow') }}
        </div>
        <div class="submenu-content" id="workflowMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('workflows.definitions.index') }}">
                    <i class="bi bi-list-check"></i> {{ __('Définitions') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('workflows.definitions.create') }}">
                    <i class="bi bi-plus-square"></i> {{ __('Nouvelle définition') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('workflows.instances.index') }}">
                    <i class="bi bi-play-circle"></i> {{ __('Instances') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('workflows.instances.create') }}">
                    <i class="bi bi-play-fill"></i> {{ __('Démarrer workflow') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Tasks Section -->
    <div class="submenu-section">
        <div class="submenu-heading" data-menu-action="toggle">
            <i class="bi bi-list-task"></i> {{ __('Tâches') }}
        </div>
        <div class="submenu-content" id="tasksMenu">
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('tasks.index') }}">
                    <i class="bi bi-list"></i> {{ __('Toutes les tâches') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('tasks.create') }}">
                    <i class="bi bi-plus-square"></i> {{ __('Nouvelle tâche') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('tasks.index', ['status' => 'pending']) }}">
                    <i class="bi bi-hourglass-split"></i> {{ __('En attente') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('tasks.index', ['status' => 'in_progress']) }}">
                    <i class="bi bi-arrow-repeat"></i> {{ __('En cours') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('tasks.index', ['assigned_to' => 'me']) }}">
                    <i class="bi bi-person-check"></i> {{ __('Mes tâches') }}
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
