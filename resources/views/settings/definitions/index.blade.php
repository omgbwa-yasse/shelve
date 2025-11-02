@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <!-- Header avec titre et bouton d'action -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-primary">
                <i class="bi bi-gear-wide-connected me-2"></i>{{ __('Parameters') }}
            </h1>
            <p class="text-muted mb-0">{{ __('Parameter List') }}</p>
        </div>
        <a href="{{ route('settings.definitions.create') }}" class="btn btn-primary btn-lg">
            <i class="bi bi-plus-circle me-2"></i>{{ __('New Parameter') }}
        </a>
    </div>

    <!-- Filtres et recherche -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="searchInput" placeholder="{{ __('Search parameters...') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-outline-primary w-100" onclick="expandAllCategories()">
                        <i class="bi bi-arrows-expand me-1"></i>{{ __('Expand All') }}
                    </button>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-outline-secondary w-100" onclick="collapseAllCategories()">
                        <i class="bi bi-arrows-collapse me-1"></i>{{ __('Collapse All') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des paramètres organisés par catégorie -->
    @if($settings->isEmpty())
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-gear-wide fs-1 text-muted mb-3"></i>
                <h5 class="text-muted">{{ __('No Parameters Found') }}</h5>
                <p class="text-muted mb-4">{{ __('Get started by creating your first parameter') }}</p>
                <a href="{{ route('settings.definitions.create') }}" class="btn btn-primary btn-lg">
                    <i class="bi bi-plus-circle me-2"></i>{{ __('Create First Parameter') }}
                </a>
            </div>
        </div>
    @else
        @php
            $settingsByCategory = $settings->groupBy(function($setting) {
                return $setting->category ? $setting->category->name : 'Sans catégorie';
            });
        @endphp

        @foreach($settingsByCategory as $categoryName => $categorySettings)
            <div class="card shadow-sm mb-3 category-card">
                <div class="card-header bg-light cursor-pointer category-header" onclick="toggleCategory('category-{{ $loop->index }}')">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-plus-circle me-2 toggle-icon" id="icon-category-{{ $loop->index }}"></i>
                            @if($categoryName === 'Sans catégorie')
                                <i class="bi bi-folder-x me-2 text-muted"></i>
                                <h6 class="mb-0 text-muted">{{ __('Uncategorized Parameters') }}</h6>
                            @else
                                <i class="bi bi-folder me-2 text-primary"></i>
                                <h6 class="mb-0">{{ $categoryName }}</h6>
                            @endif
                        </div>
                        <span class="badge bg-secondary rounded-pill">{{ $categorySettings->count() }}</span>
                    </div>
                </div>
                <div class="card-body p-0 category-content" id="category-{{ $loop->index }}" style="display: none;">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0 ps-4">
                                        <i class="bi bi-info-circle me-1"></i>{{ __('Description') }}
                                    </th>
                                    <th class="border-0">
                                        <i class="bi bi-code-slash me-1"></i>{{ __('Type') }}
                                    </th>
                                    <th class="border-0">
                                        <i class="bi bi-shield me-1"></i>{{ __('Status') }}
                                    </th>
                                    <th class="border-0 text-end">
                                        <i class="bi bi-eye me-1"></i>{{ __('View') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categorySettings as $setting)
                                    <tr class="parameter-row" data-name="{{ strtolower($setting->name) }}">
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="parameter-icon me-3">
                                                    <i class="bi bi-gear-fill text-primary"></i>
                                                </div>
                                                <div>
                                                    <strong class="d-block">{{ $setting->name }}</strong>
                                                    <small class="text-muted">{{ Str::limit($setting->description, 60) }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <i class="bi bi-code me-1"></i>{{ $setting->type }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($setting->is_system)
                                                <span class="badge bg-warning">
                                                    <i class="bi bi-cpu me-1"></i>{{ __('System') }}
                                                </span>
                                            @else
                                                <span class="badge bg-success">
                                                    <i class="bi bi-person me-1"></i>{{ __('User') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('settings.definitions.show', $setting) }}" class="btn btn-sm btn-outline-info" title="{{ __('View') }}">
                                                <i class="bi bi-eye me-1"></i>{{ __('View') }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>

<style>
.parameter-icon {
    width: 40px;
    height: 40px;
    background: rgba(13, 110, 253, 0.1);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.parameter-row:hover {
    background-color: rgba(13, 110, 253, 0.05);
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

.card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.card-header {
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    border-radius: 12px 12px 0 0 !important;
}

.table th {
    font-weight: 600;
    color: #6c757d;
    border-bottom: 2px solid #dee2e6;
}

.badge {
    font-size: 0.75rem;
    padding: 0.5em 0.75em;
}

.btn-group .btn {
    border-radius: 6px;
    margin: 0 2px;
}

.btn-group .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Nouveaux styles pour les catégories */
.category-card {
    transition: all 0.3s ease;
}

.category-header {
    user-select: none;
    transition: all 0.2s ease;
}

.category-header:hover {
    background-color: rgba(13, 110, 253, 0.05) !important;
}

.cursor-pointer {
    cursor: pointer;
}

.toggle-icon {
    transition: transform 0.3s ease;
    font-size: 1.1rem;
}

.toggle-icon.expanded {
    transform: rotate(45deg);
}

.category-content {
    transition: all 0.3s ease;
    overflow: hidden;
}

.category-content.show {
    display: block !important;
    animation: slideDown 0.3s ease;
}

.category-content.hide {
    animation: slideUp 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        max-height: 0;
    }
    to {
        opacity: 1;
        max-height: 1000px;
    }
}

@keyframes slideUp {
    from {
        opacity: 1;
        max-height: 1000px;
    }
    to {
        opacity: 0;
        max-height: 0;
    }
}

.category-card .table th:first-child {
    padding-left: 2rem;
}

@media (max-width: 768px) {
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 1rem;
    }

    .btn-lg {
        width: 100%;
    }

    .table-responsive {
        font-size: 0.875rem;
    }

    .category-card .table th:first-child,
    .parameter-row td:first-child {
        padding-left: 1rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const parameterRows = document.querySelectorAll('.parameter-row');
    const categoryCards = document.querySelectorAll('.category-card');

    // Fonction pour basculer l'affichage d'une catégorie
    window.toggleCategory = function(categoryId) {
        const content = document.getElementById(categoryId);
        const icon = document.getElementById('icon-' + categoryId);

        if (content && icon) {
            if (content.style.display === 'none' || content.style.display === '') {
                // Ouvrir la catégorie
                content.style.display = 'block';
                content.classList.add('show');
                content.classList.remove('hide');
                icon.classList.remove('bi-plus-circle');
                icon.classList.add('bi-dash-circle', 'expanded');
            } else {
                // Fermer la catégorie
                content.classList.add('hide');
                content.classList.remove('show');
                icon.classList.remove('bi-dash-circle', 'expanded');
                icon.classList.add('bi-plus-circle');

                // Attendre la fin de l'animation avant de cacher
                setTimeout(() => {
                    content.style.display = 'none';
                }, 300);
            }
        }
    };

    // Fonction pour développer toutes les catégories
    window.expandAllCategories = function() {
        categoryCards.forEach((card, index) => {
            const categoryId = 'category-' + index;
            const content = document.getElementById(categoryId);
            const icon = document.getElementById('icon-' + categoryId);

            if (content && icon && content.style.display === 'none') {
                content.style.display = 'block';
                content.classList.add('show');
                content.classList.remove('hide');
                icon.classList.remove('bi-plus-circle');
                icon.classList.add('bi-dash-circle', 'expanded');
            }
        });
    };

    // Fonction pour réduire toutes les catégories
    window.collapseAllCategories = function() {
        categoryCards.forEach((card, index) => {
            const categoryId = 'category-' + index;
            const content = document.getElementById(categoryId);
            const icon = document.getElementById('icon-' + categoryId);

            if (content && icon && content.style.display !== 'none') {
                content.classList.add('hide');
                content.classList.remove('show');
                icon.classList.remove('bi-dash-circle', 'expanded');
                icon.classList.add('bi-plus-circle');

                setTimeout(() => {
                    content.style.display = 'none';
                }, 300);
            }
        });
    };

    // Fonction de recherche dans les paramètres
    function filterParameters() {
        const searchTerm = searchInput.value.toLowerCase();
        let visibleCategories = [];

        categoryCards.forEach((card, categoryIndex) => {
            const categoryRows = card.querySelectorAll('.parameter-row');
            let hasVisibleRows = false;

            categoryRows.forEach(row => {
                const name = row.dataset.name;
                const matchesSearch = !searchTerm || name.includes(searchTerm);

                if (matchesSearch) {
                    row.style.display = '';
                    row.style.animation = 'fadeIn 0.3s ease-in';
                    hasVisibleRows = true;
                } else {
                    row.style.display = 'none';
                }
            });

            // Afficher/masquer la catégorie selon qu'elle contient des résultats
            if (hasVisibleRows) {
                card.style.display = '';
                visibleCategories.push(categoryIndex);

                // Si on recherche, ouvrir automatiquement la catégorie
                if (searchTerm) {
                    const categoryId = 'category-' + categoryIndex;
                    const content = document.getElementById(categoryId);
                    const icon = document.getElementById('icon-' + categoryId);

                    if (content && content.style.display === 'none') {
                        content.style.display = 'block';
                        content.classList.add('show');
                        content.classList.remove('hide');
                        icon.classList.remove('bi-plus-circle');
                        icon.classList.add('bi-dash-circle', 'expanded');
                    }
                }
            } else {
                card.style.display = 'none';
            }
        });

        // Afficher un message si aucun résultat
        updateEmptyState(visibleCategories.length === 0 && searchTerm);
    }

    function updateEmptyState(show) {
        let emptyState = document.getElementById('no-results-message');

        if (show && !emptyState) {
            // Créer le message d'absence de résultats
            emptyState = document.createElement('div');
            emptyState.id = 'no-results-message';
            emptyState.className = 'card shadow-sm';
            emptyState.innerHTML = `
                <div class="card-body text-center py-5">
                    <i class="bi bi-search fs-1 text-muted mb-3"></i>
                    <h5 class="text-muted">{{ __('No parameters match your search') }}</h5>
                    <p class="text-muted mb-4">{{ __('Try a different search term') }}</p>
                    <button class="btn btn-outline-primary" onclick="clearSearch()">
                        <i class="bi bi-x-circle me-2"></i>{{ __('Clear Search') }}
                    </button>
                </div>
            `;

            // Insérer après les filtres
            const filterCard = document.querySelector('.card.mb-4');
            if (filterCard && filterCard.nextSibling) {
                filterCard.parentNode.insertBefore(emptyState, filterCard.nextSibling);
            }
        } else if (!show && emptyState) {
            emptyState.remove();
        }
    }

    // Fonction pour effacer la recherche
    window.clearSearch = function() {
        searchInput.value = '';
        filterParameters();

        // Rétablir l'état initial (toutes les catégories fermées)
        collapseAllCategories();
    };

    // Event listener pour la recherche
    if (searchInput) {
        searchInput.addEventListener('input', filterParameters);
    }

    // Animation pour les lignes
    parameterRows.forEach((row, index) => {
        row.style.animationDelay = `${index * 0.02}s`;
    });

    // Ajouter support clavier pour les en-têtes de catégorie
    document.querySelectorAll('.category-header').forEach(header => {
        header.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });

        // Rendre focusable pour l'accessibilité
        header.setAttribute('tabindex', '0');
        header.setAttribute('role', 'button');
        header.setAttribute('aria-expanded', 'false');
    });
});

// Animation CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .parameter-row {
        animation: fadeIn 0.3s ease-out;
    }
`;
document.head.appendChild(style);
</script>
@endsection
