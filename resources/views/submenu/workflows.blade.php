<div class="submenu-container py-2">
    @php
    use App\Helpers\SubmenuPermissions;
    @endphp

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
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .submenu-heading i {
            margin-right: 8px;
            font-size: 14px;
        }

        .submenu-content {
            padding: 0 0 8px 12px;
            margin-bottom: 8px;
            display: block;
        }

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

        .submenu-link.active {
            background-color: #e8f0fe;
            color: #1a73e8;
        }

        .submenu-link.active i {
            color: #1a73e8;
        }
    </style>



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
