<div class="submenu-container py-2">
    @php
    use App\Helpers\SubmenuPermissions;
    @endphp

    <!-- Styles partagés via _submenu.scss -->



    <!-- Section Modèles -->
    <div class="submenu-section">
        <div class="submenu-heading">
            <i class="bi bi-file-earmark-text"></i>
            {{ __('Modèles') }}
        </div>
        <div class="submenu-content">
            @can('workflow_template_viewAny')
            <div class="submenu-item">
                <a href="{{ route('workflows.templates.index') }}" class="submenu-link {{ request()->routeIs('workflows.templates.index') ? 'active' : '' }}">
                    <i class="bi bi-list-ul"></i>
                    {{ __('Liste des modèles') }}
                </a>
            </div>
            @endcan

            @can('workflow_template_create')
            <div class="submenu-item">
                <a href="{{ route('workflows.templates.create') }}" class="submenu-link {{ request()->routeIs('workflows.templates.create') ? 'active' : '' }}">
                    <i class="bi bi-plus-circle"></i>
                    {{ __('Créer un modèle') }}
                </a>
            </div>
            @endcan
        </div>
    </div>

    <!-- Section Instances -->
    <div class="submenu-section">
        <div class="submenu-heading">
            <i class="bi bi-diagram-3"></i>
            {{ __('Instances') }}
        </div>
        <div class="submenu-content">
            @can('workflow_instance_viewAny')
            <div class="submenu-item">
                <a href="{{ route('workflows.instances.index') }}" class="submenu-link {{ request()->routeIs('workflows.instances.index') ? 'active' : '' }}">
                    <i class="bi bi-collection"></i>
                    {{ __('Toutes les instances') }}
                </a>
            </div>
            @endcan

            @can('workflow_instance_create')
            <div class="submenu-item">
                <a href="{{ route('workflows.instances.create') }}" class="submenu-link {{ request()->routeIs('workflows.instances.create') ? 'active' : '' }}">
                    <i class="bi bi-plus-circle"></i>
                    {{ __('Démarrer une instance') }}
                </a>
            </div>
            @endcan
        </div>
    </div>

    <!-- Section Tâches -->
    <div class="submenu-section">
        <div class="submenu-heading">
            <i class="bi bi-list-task"></i>
            {{ __('Tâches') }}
        </div>
        <div class="submenu-content">
            @can('task_viewAny')
            <div class="submenu-item">
                <a href="{{ route('workflows.tasks.index') }}" class="submenu-link {{ request()->routeIs('workflows.tasks.index') ? 'active' : '' }}">
                    <i class="bi bi-card-list"></i>
                    {{ __('Toutes les tâches') }}
                </a>
            </div>
            @endcan

            @can('task_viewOwn')
            <div class="submenu-item">
                <a href="{{ route('workflows.tasks.my') }}" class="submenu-link {{ request()->routeIs('workflows.tasks.my') ? 'active' : '' }}">
                    <i class="bi bi-person-check"></i>
                    {{ __('Mes tâches') }}
                </a>
            </div>
            @endcan

            @can('task_create')
            <div class="submenu-item">
                <a href="{{ route('workflows.tasks.create') }}" class="submenu-link {{ request()->routeIs('workflows.tasks.create') ? 'active' : '' }}">
                    <i class="bi bi-plus-circle"></i>
                    {{ __('Créer une tâche') }}
                </a>
            </div>
            @endcan
        </div>
    </div>

</div>
