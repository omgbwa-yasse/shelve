<nav class="flex flex-col space-y-1">
    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <i class="bi bi-speedometer2"></i>
        <span>{{ __('Overview') }}</span>
    </a>

    <a href="{{ route('records.index') }}" class="nav-link">
        <i class="bi bi-folder"></i>
        <span>{{ __('Digital Folders') }}</span>
    </a>

    <a href="#" class="nav-link">
        <i class="bi bi-file-earmark"></i>
        <span>{{ __('Documents') }}</span>
    </a>

    <a href="#" class="nav-link">
        <i class="bi bi-box"></i>
        <span>{{ __('Artifacts') }}</span>
    </a>

    <a href="#" class="nav-link">
        <i class="bi bi-newspaper"></i>
        <span>{{ __('Periodicals') }}</span>
    </a>

    <hr class="my-2 border-gray-200 dark:border-gray-700">

    <a href="#" class="nav-link">
        <i class="bi bi-search"></i>
        <span>{{ __('Search') }}</span>
    </a>

    <a href="#" class="nav-link">
        <i class="bi bi-gear"></i>
        <span>{{ __('Settings') }}</span>
    </a>
</nav>
