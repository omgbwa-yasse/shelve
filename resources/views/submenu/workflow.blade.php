<div class="submenu-container py-2">

    {{-- ── Processus Section ──────────────────────────── --}}
    <div class="submenu-section">
        <div class="submenu-heading" data-menu-action="toggle">
            <i class="bi bi-diagram-3"></i> {{ __('Processus') }}
        </div>
        <div class="submenu-content" id="workflowMenu">
            <div class="submenu-item">
                <a class="submenu-link {{ request()->routeIs('workflows.definitions.*') ? 'active' : '' }}"
                   href="{{ route('workflows.definitions.index') }}">
                    <i class="bi bi-grid-3x3-gap"></i> {{ __('Tous les processus') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link {{ request()->routeIs('workflows.definitions.create') ? 'active' : '' }}"
                   href="{{ route('workflows.definitions.create') }}">
                    <i class="bi bi-plus-square"></i> {{ __('Nouveau processus') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link {{ request()->routeIs('workflows.instances.*') ? 'active' : '' }}"
                   href="{{ route('workflows.instances.index') }}">
                    <i class="bi bi-collection-play"></i> {{ __('Instances en cours') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link {{ request()->routeIs('workflows.instances.create') ? 'active' : '' }}"
                   href="{{ route('workflows.instances.create') }}">
                    <i class="bi bi-play-circle"></i> {{ __('Démarrer un processus') }}
                </a>
            </div>
        </div>
    </div>

    {{-- ── Tâches Section ──────────────────────────────── --}}
    <div class="submenu-section">
        <div class="submenu-heading" data-menu-action="toggle">
            <i class="bi bi-check2-square"></i> {{ __('Tâches') }}
        </div>
        <div class="submenu-content" id="tasksMenu">
            <div class="submenu-item">
                <a class="submenu-link {{ request()->is('tasks') && !request()->has('status') && !request()->has('assigned_to') ? 'active' : '' }}"
                   href="{{ route('tasks.index') }}">
                    <i class="bi bi-list-task"></i> {{ __('Toutes les tâches') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link {{ request()->routeIs('tasks.create') ? 'active' : '' }}"
                   href="{{ route('tasks.create') }}">
                    <i class="bi bi-plus-square"></i> {{ __('Nouvelle tâche simple') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link {{ request('status') == 'pending' ? 'active' : '' }}"
                   href="{{ route('tasks.index', ['status' => 'pending']) }}">
                    <i class="bi bi-hourglass-split"></i> {{ __('En attente') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link {{ request('status') == 'in_progress' ? 'active' : '' }}"
                   href="{{ route('tasks.index', ['status' => 'in_progress']) }}">
                    <i class="bi bi-arrow-repeat"></i> {{ __('En cours') }}
                </a>
            </div>
            <div class="submenu-item">
                <a class="submenu-link {{ request('assigned_to') == 'me' ? 'active' : '' }}"
                   href="{{ route('tasks.index', ['assigned_to' => 'me']) }}">
                    <i class="bi bi-person-check"></i> {{ __('Mes tâches') }}
                </a>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const headings = document.querySelectorAll('[data-menu-action="toggle"]');
    headings.forEach(function(heading) {
        heading.addEventListener('click', function(event) {
            const content = event.currentTarget.nextElementSibling;
            if (content && content.classList.contains('submenu-content')) {
                content.classList.toggle('collapsed');
                event.currentTarget.classList.toggle('collapsed');
                event.preventDefault();
            }
        });
    });
});
</script>
