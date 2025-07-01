<nav class="nav flex-column nav-pills submenu">
    <div class="nav-item-header">{{ __('Workflows') }}</div>

    @can('workflow_dashboard')
    <a class="nav-link {{ request()->routeIs('workflow.dashboard') ? 'active' : '' }}" href="{{ route('workflow.dashboard') }}">
        <i class="bi bi-grid me-2"></i>{{ __('Tableau de bord') }}
    </a>
    @endcan

    @can('workflow_template_viewAny')
    <a class="nav-link {{ request()->routeIs('workflow.templates.*') ? 'active' : '' }}" href="{{ route('workflow.templates.index') }}">
        <i class="bi bi-file-earmark-text me-2"></i>{{ __('Modèles de workflow') }}
    </a>
    @endcan

    @can('workflow_instance_viewAny')
    <a class="nav-link {{ request()->routeIs('workflow.instances.*') ? 'active' : '' }}" href="{{ route('workflow.instances.index') }}">
        <i class="bi bi-diagram-3 me-2"></i>{{ __('Instances de workflow') }}
    </a>
    @endcan

    <div class="nav-item-header">{{ __('Tâches') }}</div>

    @can('task_viewAny')
    <a class="nav-link {{ request()->routeIs('tasks.index') ? 'active' : '' }}" href="{{ route('tasks.index') }}">
        <i class="bi bi-list-task me-2"></i>{{ __('Toutes les tâches') }}
    </a>
    @endcan

    @can('task_viewOwn')
    <a class="nav-link {{ request()->routeIs('tasks.my') ? 'active' : '' }}" href="{{ route('tasks.my') }}">
        <i class="bi bi-person-check me-2"></i>{{ __('Mes tâches') }}
    </a>
    @endcan

    <div class="nav-item-header">{{ __('Notifications') }}</div>

    @can('notification_viewAny')
    <a class="nav-link {{ request()->routeIs('notifications.index') ? 'active' : '' }}" href="{{ route('notifications.index') }}">
        <i class="bi bi-bell me-2"></i>{{ __('Mes notifications') }}
    </a>
    @endcan

    @can('systemNotification_viewAny')
    <a class="nav-link {{ request()->routeIs('notifications.system.*') ? 'active' : '' }}" href="{{ route('notifications.system.index') }}">
        <i class="bi bi-bell-fill me-2"></i>{{ __('Notifications système') }}
    </a>
    @endcan
</nav>
