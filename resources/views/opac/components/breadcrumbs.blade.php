{{--
    Composant Breadcrumbs OPAC
    Usage: @include('opac.components.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
--}}
@if(isset($breadcrumbs) && is_array($breadcrumbs) && count($breadcrumbs) > 0)
<nav aria-label="Fil d'Ariane" class="breadcrumb-container py-2">
    <div class="container">
        <ol class="breadcrumb mb-0" itemscope itemtype="https://schema.org/BreadcrumbList">
            @foreach($breadcrumbs as $index => $breadcrumb)
                <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}"
                    itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">

                    @if($loop->last)
                        <span aria-current="page" itemprop="name">
                            {{ $breadcrumb['label'] }}
                        </span>
                    @else
                        <a href="{{ $breadcrumb['url'] }}" itemprop="item">
                            <span itemprop="name">{{ $breadcrumb['label'] }}</span>
                        </a>
                    @endif

                    <meta itemprop="position" content="{{ $index + 1 }}">
                </li>
            @endforeach
        </ol>
    </div>
</nav>

<style>
.breadcrumb-container {
    background-color: rgba(var(--bs-light-rgb), 0.5);
    border-bottom: 1px solid rgba(var(--bs-border-color-rgb), 0.2);
}

.breadcrumb {
    font-size: 0.875rem;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: var(--bs-secondary);
    font-weight: bold;
}

.breadcrumb-item a {
    color: var(--primary-color);
    text-decoration: none;
    transition: color 0.2s ease;
}

.breadcrumb-item a:hover {
    color: var(--secondary-color);
    text-decoration: underline;
}

.breadcrumb-item.active {
    color: var(--text-color);
    font-weight: 500;
}

@media (max-width: 768px) {
    .breadcrumb {
        font-size: 0.8rem;
    }

    .breadcrumb-item {
        max-width: 120px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
}
</style>
@endif
