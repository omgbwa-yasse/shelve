{{-- Composant pagination OPAC --}}
<nav aria-label="Navigation des pages" class="pagination-wrapper">
    @if($paginator->hasPages())
        <div class="pagination-info">
            <span class="pagination-text">
                {{ __('Affichage de :start à :end sur :total résultats', [
                    'start' => $paginator->firstItem() ?: 0,
                    'end' => $paginator->lastItem() ?: 0,
                    'total' => $paginator->total()
                ]) }}
            </span>
        </div>

        <ul class="pagination {{ $size ?? 'pagination-md' }} {{ $alignment ?? 'justify-content-center' }}">
            {{-- Lien précédent --}}
            @if($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link" tabindex="-1">
                        <i class="fas fa-angle-left" aria-hidden="true"></i>
                        <span class="sr-only">{{ __('Précédent') }}</span>
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link"
                       href="{{ $paginator->previousPageUrl() }}"
                       rel="prev"
                       aria-label="{{ __('Aller à la page précédente') }}">
                        <i class="fas fa-angle-left" aria-hidden="true"></i>
                        <span class="sr-only">{{ __('Précédent') }}</span>
                    </a>
                </li>
            @endif

            {{-- Liens des pages --}}
            @foreach($elements as $element)
                {{-- Séparateur "..." --}}
                @if(is_string($element))
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link">{{ $element }}</span>
                    </li>
                @endif

                {{-- Liens de pages --}}
                @if(is_array($element))
                    @foreach($element as $page => $url)
                        @if($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page">
                                <span class="page-link">
                                    {{ $page }}
                                    <span class="sr-only">{{ __('(page courante)') }}</span>
                                </span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link"
                                   href="{{ $url }}"
                                   aria-label="{{ __('Aller à la page :page', ['page' => $page]) }}">
                                    {{ $page }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Lien suivant --}}
            @if($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link"
                       href="{{ $paginator->nextPageUrl() }}"
                       rel="next"
                       aria-label="{{ __('Aller à la page suivante') }}">
                        <span class="sr-only">{{ __('Suivant') }}</span>
                        <i class="fas fa-angle-right" aria-hidden="true"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link" tabindex="-1">
                        <span class="sr-only">{{ __('Suivant') }}</span>
                        <i class="fas fa-angle-right" aria-hidden="true"></i>
                    </span>
                </li>
            @endif
        </ul>

        {{-- Navigation rapide pour les grandes collections --}}
        @if($paginator->lastPage() > 10 && ($showQuickNav ?? true))
            <div class="quick-navigation">
                <form class="quick-nav-form" method="GET" action="{{ request()->url() }}">
                    {{-- Préserver les paramètres de recherche existants --}}
                    @foreach(request()->except('page') as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach

                    <div class="input-group input-group-sm">
                        <span class="input-group-text">{{ __('Aller à la page') }}</span>
                        <input type="number"
                               name="page"
                               class="form-control"
                               min="1"
                               max="{{ $paginator->lastPage() }}"
                               value="{{ $paginator->currentPage() }}"
                               aria-label="{{ __('Numéro de page') }}">
                        <span class="input-group-text">{{ __('sur') }} {{ $paginator->lastPage() }}</span>
                        <button type="submit" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </form>
            </div>
        @endif

        {{-- Sélecteur de nombre d'éléments par page --}}
        @if($showPerPageSelector ?? true)
            <div class="per-page-selector">
                <form class="per-page-form" method="GET" action="{{ request()->url() }}">
                    {{-- Préserver les paramètres existants --}}
                    @foreach(request()->except(['page', 'per_page']) as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach

                    <div class="input-group input-group-sm">
                        <span class="input-group-text">{{ __('Afficher') }}</span>
                        <select name="per_page" class="form-select" aria-label="{{ __('Nombre d\'éléments par page') }}">
                            @foreach([10, 20, 50, 100] as $perPage)
                                <option value="{{ $perPage }}"
                                        {{ request('per_page', 20) == $perPage ? 'selected' : '' }}>
                                    {{ $perPage }}
                                </option>
                            @endforeach
                        </select>
                        <span class="input-group-text">{{ __('par page') }}</span>
                    </div>
                </form>
            </div>
        @endif
    @else
        {{-- Affichage quand il n'y a qu'une page --}}
        <div class="pagination-info single-page">
            <span class="pagination-text">
                @if($paginator->total() > 0)
                    {{ trans_choice('pagination.showing_results', $paginator->total(), [
                        'total' => $paginator->total()
                    ]) }}
                @else
                    {{ __('Aucun résultat trouvé') }}
                @endif
            </span>
        </div>
    @endif
</nav>

@push('styles')
<style>
.pagination-wrapper {
    margin: 2rem 0;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    align-items: center;
}

.pagination-info {
    text-align: center;
    color: var(--text-muted, #6c757d);
    font-size: 0.9rem;
}

.pagination-info.single-page {
    margin: 1rem 0;
}

.pagination {
    margin: 0;
    --bs-pagination-bg: var(--card-bg, #ffffff);
    --bs-pagination-border-color: var(--border-color, #dee2e6);
    --bs-pagination-hover-bg: var(--light-color, #f8f9fa);
    --bs-pagination-hover-border-color: var(--primary-color, #007bff);
    --bs-pagination-focus-bg: var(--light-color, #f8f9fa);
    --bs-pagination-focus-border-color: var(--primary-color, #007bff);
    --bs-pagination-active-bg: var(--primary-color, #007bff);
    --bs-pagination-active-border-color: var(--primary-color, #007bff);
    --bs-pagination-disabled-bg: var(--light-color, #f8f9fa);
    --bs-pagination-disabled-border-color: var(--border-color, #dee2e6);
}

.page-link {
    transition: all 0.2s ease-in-out;
    font-weight: 500;
}

.page-link:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.page-item.active .page-link {
    font-weight: 600;
    box-shadow: 0 2px 4px rgba(0, 123, 255, 0.25);
}

.quick-navigation,
.per-page-selector {
    display: flex;
    justify-content: center;
    margin-top: 0.5rem;
}

.quick-nav-form,
.per-page-form {
    max-width: 300px;
}

.per-page-form .form-select {
    width: auto;
    min-width: 80px;
}

/* Styles pour les petites tailles */
.pagination-sm .page-link {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
}

.pagination-lg .page-link {
    padding: 0.75rem 1.5rem;
    font-size: 1.125rem;
}

/* Responsive design */
@media (max-width: 768px) {
    .pagination-wrapper {
        margin: 1rem 0;
    }

    .pagination {
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.25rem;
    }

    .page-item {
        margin: 0.125rem;
    }

    .page-link {
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }

    .quick-navigation,
    .per-page-selector {
        width: 100%;
        max-width: none;
    }

    .quick-nav-form,
    .per-page-form {
        width: 100%;
        max-width: none;
    }

    /* Masquer certains éléments sur mobile si nécessaire */
    .pagination .page-item:not(.active):not(:first-child):not(:last-child):not(:nth-child(2)):not(:nth-last-child(2)) {
        display: none;
    }
}

@media (max-width: 576px) {
    .pagination-info {
        font-size: 0.8rem;
        text-align: center;
    }

    .quick-navigation {
        display: none; /* Masquer la navigation rapide sur très petits écrans */
    }

    .per-page-selector .input-group {
        flex-direction: column;
        gap: 0.5rem;
    }

    .per-page-selector .input-group-text,
    .per-page-selector .form-select {
        width: 100%;
        text-align: center;
    }
}

/* Animation de chargement */
.pagination-wrapper.loading {
    opacity: 0.6;
    pointer-events: none;
}

.pagination-wrapper.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid transparent;
    border-top: 2px solid var(--primary-color, #007bff);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Accessibilité */
.sr-only {
    position: absolute !important;
    width: 1px !important;
    height: 1px !important;
    padding: 0 !important;
    margin: -1px !important;
    overflow: hidden !important;
    clip: rect(0, 0, 0, 0) !important;
    white-space: nowrap !important;
    border: 0 !important;
}

.page-link:focus {
    outline: 2px solid var(--primary-color, #007bff);
    outline-offset: 2px;
}

/* Mode sombre */
@media (prefers-color-scheme: dark) {
    .pagination-wrapper {
        --bs-pagination-bg: #495057;
        --bs-pagination-border-color: #6c757d;
        --bs-pagination-color: #ffffff;
        --bs-pagination-hover-bg: #6c757d;
        --bs-pagination-disabled-bg: #343a40;
    }

    .pagination-info {
        color: #adb5bd;
    }
}

/* Animations personnalisées */
.pagination .page-item {
    transition: transform 0.1s ease;
}

.pagination .page-item:hover:not(.disabled):not(.active) {
    transform: scale(1.05);
}

.pagination .page-item.active {
    transform: scale(1.1);
}

/* Style pour les ellipsis */
.pagination .page-item:has(.page-link:contains("...")) {
    pointer-events: none;
}

.pagination .page-item .page-link:contains("...") {
    color: var(--text-muted, #6c757d);
    cursor: default;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-soumission du formulaire de sélection du nombre d'éléments par page
    const perPageSelect = document.querySelector('.per-page-form select[name="per_page"]');
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            this.closest('form').submit();
        });
    }

    // Validation du formulaire de navigation rapide
    const quickNavForm = document.querySelector('.quick-nav-form');
    if (quickNavForm) {
        const pageInput = quickNavForm.querySelector('input[name="page"]');
        const maxPage = parseInt(pageInput.getAttribute('max'));

        quickNavForm.addEventListener('submit', function(e) {
            const pageValue = parseInt(pageInput.value);

            if (isNaN(pageValue) || pageValue < 1 || pageValue > maxPage) {
                e.preventDefault();
                pageInput.focus();
                pageInput.classList.add('is-invalid');

                // Retirer la classe d'erreur après correction
                pageInput.addEventListener('input', function() {
                    this.classList.remove('is-invalid');
                }, { once: true });

                return false;
            }
        });

        // Navigation au clavier pour la saisie rapide
        pageInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                quickNavForm.dispatchEvent(new Event('submit'));
            }
        });
    }

    // Raccourcis clavier pour la pagination
    document.addEventListener('keydown', function(e) {
        // Éviter les raccourcis si on est dans un champ de saisie
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT') {
            return;
        }

        const prevLink = document.querySelector('.pagination .page-item:not(.disabled) a[rel="prev"]');
        const nextLink = document.querySelector('.pagination .page-item:not(.disabled) a[rel="next"]');

        // Flèche gauche ou Page précédente
        if ((e.key === 'ArrowLeft' || e.key === 'PageUp') && prevLink) {
            e.preventDefault();
            prevLink.click();
        }

        // Flèche droite ou Page suivante
        if ((e.key === 'ArrowRight' || e.key === 'PageDown') && nextLink) {
            e.preventDefault();
            nextLink.click();
        }

        // Accueil = première page
        if (e.key === 'Home' && !document.querySelector('.pagination .page-item.active:first-of-type')) {
            e.preventDefault();
            const firstPageLink = document.querySelector('.pagination .page-item:not(.disabled) a');
            if (firstPageLink && firstPageLink.textContent.trim() !== '1') {
                // Aller à la première page
                const url = new URL(firstPageLink.href);
                url.searchParams.set('page', '1');
                window.location.href = url.toString();
            }
        }

        // Fin = dernière page
        if (e.key === 'End') {
            e.preventDefault();
            const lastPageNumber = document.querySelector('.per-page-selector .input-group-text')?.textContent.match(/(\d+)$/)?.[1];
            if (lastPageNumber) {
                const url = new URL(window.location.href);
                url.searchParams.set('page', lastPageNumber);
                window.location.href = url.toString();
            }
        }
    });

    // Animation de chargement lors de la navigation
    document.querySelectorAll('.pagination a, .quick-nav-form, .per-page-form').forEach(element => {
        element.addEventListener('click', function(e) {
            if (this.tagName === 'A') {
                addLoadingState();
            }
        });

        if (element.tagName === 'FORM') {
            element.addEventListener('submit', function() {
                addLoadingState();
            });
        }
    });

    function addLoadingState() {
        const paginationWrapper = document.querySelector('.pagination-wrapper');
        if (paginationWrapper) {
            paginationWrapper.classList.add('loading');
        }
    }

    // Préchargement intelligent des pages suivantes
    if (window.location.protocol === 'https:' && 'requestIdleCallback' in window) {
        window.requestIdleCallback(function() {
            const nextLink = document.querySelector('.pagination a[rel="next"]');
            if (nextLink) {
                const link = document.createElement('link');
                link.rel = 'prefetch';
                link.href = nextLink.href;
                document.head.appendChild(link);
            }
        });
    }
});
</script>
@endpush
