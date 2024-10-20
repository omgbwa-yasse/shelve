<nav class="pure-menu">
    @foreach ([
        'classification_plan' => ['grid', 'activities'],
        'retention_schedule' => ['archive', 'retentions'],
        'communicability' => ['chat-square-text', 'communicabilities'],
        'organization_chart' => ['diagram-3', 'organisations'],
        'thesaurus' => ['book-half', 'terms'],
        'toolbox' => ['tools', null]
    ] as $section => [$icon, $route])
        <div class="pure-menu-section">
            <button class="pure-menu-heading" data-bs-toggle="collapse" data-bs-target="#{{ $section }}Menu">
                <i class="bi bi-{{ $icon }}"></i>
                <span>{{ __($section) }}</span>
            </button>
            <ul class="pure-menu-list collapse show" id="{{ $section }}Menu">
                @if ($route)
                    <li class="pure-menu-item">
                        <a href="{{ route($route . '.index') }}" class="pure-menu-link">
                            <i class="bi bi-list-check"></i>
                            <span>{{ __('all_' . ($section === 'organization_chart' ? 'units' : 'classes')) }}</span>
                        </a>
                    </li>
                    <li class="pure-menu-item">
                        <a href="{{ route($route . '.create') }}" class="pure-menu-link">
                            <i class="bi bi-plus-square"></i>
                            <span>{{ __('add_' . rtrim($section, 's')) }}</span>
                        </a>
                    </li>
                @elseif ($section === 'toolbox')
                    <li class="pure-menu-item">
                        <a href="{{ route('barcode.create') }}" class="pure-menu-link">
                            <i class="bi bi-upc-scan"></i>
                            <span>{{ __('barcode') }}</span>
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    @endforeach
</nav>

<style>
    .pure-menu {
        background-color: #f8f9fa;
        border-radius: 8px;
        overflow: hidden;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .pure-menu-section:not(:last-child) {
        border-bottom: 1px solid #e9ecef;
    }

    .pure-menu-heading {
        display: flex;
        align-items: center;
        width: 100%;
        padding: 12px 16px;
        background-color: transparent;
        border: none;
        text-align: left;
        font-weight: 600;
        color: #495057;
        transition: background-color 0.2s;
    }

    .pure-menu-heading:hover {
        background-color: #e9ecef;
    }

    .pure-menu-heading i {
        margin-right: 10px;
    }

    .pure-menu-list {
        list-style-type: none;
        padding: 0;
        margin: 0;
    }

    .pure-menu-item {
        margin: 4px 0;
    }

    .pure-menu-link {
        display: flex;
        align-items: center;
        padding: 8px 16px 8px 40px;
        color: #495057;
        text-decoration: none;
        transition: background-color 0.2s;
    }

    .pure-menu-link:hover {
        background-color: #e9ecef;
    }

    .pure-menu-link i {
        margin-right: 10px;
        font-size: 0.9em;
        color: #6c757d;
    }
</style>
