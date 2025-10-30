@extends('layouts.app')

@section('title', 'Gestion des Templates OPAC')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.css" rel="stylesheet">
<style>
/* Interface d'administration moderne */
.admin-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem 0;
    margin-bottom: 2rem;
    border-radius: 0 0 1rem 1rem;
}

.admin-title {
    font-size: 2.5rem;
    font-weight: 300;
    margin: 0;
}

.admin-subtitle {
    opacity: 0.9;
    margin-top: 0.5rem;
}

.toolbar {
    background: white;
    padding: 1.5rem;
    border-radius: 0.75rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.07);
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.template-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.template-card {
    background: white;
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    transition: all 0.3s ease;
    border: 2px solid transparent;
    position: relative;
}

.template-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
}

.template-card.active {
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.template-preview {
    height: 200px;
    background: #f8fafc;
    position: relative;
    overflow: hidden;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.template-preview:hover .preview-overlay {
    opacity: 1;
}

.preview-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.preview-btn {
    background: white;
    color: #1f2937;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
}

.preview-btn:hover {
    transform: scale(1.05);
    background: #f9fafb;
}

.template-info {
    padding: 1.5rem;
}

.template-name {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 0.5rem 0;
}

.template-description {
    color: #6b7280;
    font-size: 0.9rem;
    line-height: 1.5;
    margin-bottom: 1rem;
}

.template-status {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.status-active {
    background: #dcfce7;
    color: #166534;
}

.status-inactive {
    background: #fef3c7;
    color: #92400e;
}

.template-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-top: 1rem;
}

.btn-action {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
}

.btn-primary { background: #4f46e5; color: white; }
.btn-primary:hover { background: #4338ca; color: white; }

.btn-secondary { background: #6b7280; color: white; }
.btn-secondary:hover { background: #4b5563; color: white; }

.btn-success { background: #059669; color: white; }
.btn-success:hover { background: #047857; color: white; }

.btn-warning { background: #d97706; color: white; }
.btn-warning:hover { background: #b45309; color: white; }

.btn-danger { background: #dc2626; color: white; }
.btn-danger:hover { background: #b91c1c; color: white; }

.filter-tabs {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.filter-tab {
    padding: 0.5rem 1rem;
    border: 1px solid #e5e7eb;
    background: white;
    color: #6b7280;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.875rem;
}

.filter-tab.active {
    background: #4f46e5;
    color: white;
    border-color: #4f46e5;
}

.search-input {
    flex: 1;
    max-width: 300px;
    padding: 0.75rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 0.9rem;
}

.template-miniature {
    transform: scale(0.7);
    transform-origin: top left;
    width: 143%; /* 100% / 0.7 pour compenser le scale */
    pointer-events: none;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.07);
}

.empty-icon {
    font-size: 4rem;
    color: #d1d5db;
    margin-bottom: 1rem;
}

@media (max-width: 768px) {
    .template-grid {
        grid-template-columns: 1fr;
    }

    .toolbar {
        flex-direction: column;
        align-items: stretch;
    }
}

/* Animation */
@keyframes slideInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.template-card {
    animation: slideInUp 0.3s ease-out;
}
</style>
@endpush

@section('content')
<div id="templates-admin">
    <!-- Header moderne -->
    <div class="admin-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="admin-title">Gestion des Templates OPAC</h1>
                    <p class="admin-subtitle">
                        Créez, modifiez et gérez les templates de votre catalogue en ligne avec le nouveau système modulaire
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="d-flex gap-2 justify-content-end">
                        <button class="btn btn-outline-light" onclick="exportAllTemplates()">
                            <i class="fas fa-download me-2"></i>
                            Exporter tout
                        </button>
                        <button class="btn btn-outline-light" onclick="importTemplates()">
                            <i class="fas fa-upload me-2"></i>
                            Importer
                        </button>
                        <a href="{{ route('public.opac-templates.create') }}" class="btn btn-light">
                            <i class="fas fa-plus me-2"></i>
                            Nouveau Template
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

<div class="container-fluid">
    <!-- Toolbar améliorée -->
    <div class="toolbar">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <!-- Filtres par onglets -->
            <div class="filter-tabs">
                <button class="filter-tab active" data-filter="all">
                    Tous ({{ $templates->count() }})
                </button>
                <button class="filter-tab" data-filter="active">
                    Actifs ({{ $templates->where('status', 'active')->count() }})
                </button>
                <button class="filter-tab" data-filter="inactive">
                    Inactifs ({{ $templates->where('status', 'inactive')->count() }})
                </button>
            </div>

            <!-- Recherche améliorée -->
            <div>
                <input type="text" class="search-input" placeholder="Rechercher un template..."
                       id="template-search" value="{{ request('search') }}" autocomplete="off">
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <!-- Menu actions -->
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-cog me-2"></i>
                    Actions
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="refreshTemplates()">
                        <i class="fas fa-sync-alt me-2"></i>Actualiser
                    </a></li>
                    <li><a class="dropdown-item" href="{{ route('public.opac-templates.settings') ?? '#' }}">
                        <i class="fas fa-sliders-h me-2"></i>Paramètres
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" onclick="showHelp()">
                        <i class="fas fa-question-circle me-2"></i>Aide
                    </a></li>
                </ul>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('public.opac-templates.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Rechercher</label>
                    <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}"
                           placeholder="Nom ou description...">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Statut</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Tous les statuts</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actif</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactif</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="sort" class="form-label">Trier par</label>
                    <select class="form-select" id="sort" name="sort">
                        <option value="name" {{ request('sort', 'name') === 'name' ? 'selected' : '' }}>Nom</option>
                        <option value="created_at" {{ request('sort') === 'created_at' ? 'selected' : '' }}>Date de création</option>
                        <option value="updated_at" {{ request('sort') === 'updated_at' ? 'selected' : '' }}>Dernière modification</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary me-2">Filtrer</button>
                    <a href="{{ route('public.opac-templates.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Grille des templates moderne -->
    <div class="template-grid" id="templates-container">
        @forelse($templates as $template)
            <div class="template-card {{ $template->status === 'active' ? 'active' : '' }}"
                 data-template-id="{{ $template->id }}"
                 data-status="{{ $template->status }}"
                 data-name="{{ strtolower($template->name) }}">

                <!-- Aperçu moderne du template -->
                <div class="template-preview" onclick="previewTemplate({{ $template->id }})">
                    @if(isset($template->variables) && is_array($template->variables))
                        <!-- Aperçu généré dynamiquement -->
                        <div class="template-miniature" style="width: 100%; height: 100%; background: {{ $template->variables['background_color'] ?? '#ffffff' }}; padding: 10px;">
                            <!-- Header simulé -->
                            <div style="background: {{ $template->variables['primary_color'] ?? '#4f46e5' }}; height: 25px; width: 100%; margin-bottom: 8px; border-radius: {{ $template->variables['border_radius'] ?? '0.5rem' }};"></div>

                            <!-- Navigation simulée -->
                            <div class="d-flex gap-1 mb-2">
                                <div style="background: {{ $template->variables['secondary_color'] ?? '#6b7280' }}; height: 12px; width: 20%; border-radius: 2px;"></div>
                                <div style="background: {{ $template->variables['secondary_color'] ?? '#6b7280' }}; height: 12px; width: 15%; border-radius: 2px;"></div>
                                <div style="background: {{ $template->variables['accent_color'] ?? '#f59e0b' }}; height: 12px; width: 18%; border-radius: 2px;"></div>
                            </div>

                            <!-- Contenu simulé -->
                            <div class="d-flex gap-1">
                                <div style="flex: 2;">
                                    <div style="background: #e5e7eb; height: 6px; width: 90%; border-radius: 1px; margin-bottom: 3px;"></div>
                                    <div style="background: #e5e7eb; height: 6px; width: 75%; border-radius: 1px; margin-bottom: 3px;"></div>
                                    <div style="background: #e5e7eb; height: 6px; width: 85%; border-radius: 1px; margin-bottom: 8px;"></div>

                                    <!-- Cards simulées -->
                                    <div class="d-flex gap-1">
                                        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 2px; width: 30%; height: 20px;"></div>
                                        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 2px; width: 30%; height: 20px;"></div>
                                        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 2px; width: 30%; height: 20px;"></div>
                                    </div>
                                </div>

                                <!-- Sidebar simulée -->
                                <div style="flex: 1; margin-left: 4px;">
                                    <div style="background: white; border: 1px solid #e5e7eb; border-radius: 2px; padding: 2px; margin-bottom: 3px;">
                                        <div style="background: #f3f4f6; height: 4px; width: 80%; border-radius: 1px; margin-bottom: 1px;"></div>
                                        <div style="background: #f3f4f6; height: 3px; width: 60%; border-radius: 1px;"></div>
                                    </div>
                                    <div style="background: white; border: 1px solid #e5e7eb; border-radius: 2px; padding: 2px;">
                                        <div style="background: #f3f4f6; height: 4px; width: 70%; border-radius: 1px; margin-bottom: 1px;"></div>
                                        <div style="background: #f3f4f6; height: 3px; width: 90%; border-radius: 1px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Aperçu par défaut -->
                        <div class="d-flex align-items-center justify-content-center h-100 bg-gradient"
                             style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);">
                            <div class="text-center text-white">
                                <i class="fas fa-palette fa-2x mb-2"></i>
                                <div class="h6">{{ $template->name }}</div>
                                <small class="opacity-75">Template OPAC</small>
                            </div>
                        </div>
                    @endif

                    <div class="preview-overlay">
                        <button class="preview-btn">
                            <i class="fas fa-eye"></i>
                            Prévisualiser
                        </button>
                    </div>
                </div>

                <!-- Informations du template -->
                <div class="template-info">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h3 class="template-name">{{ $template->name }}</h3>
                        <span class="template-status {{ $template->status === 'active' ? 'status-active' : 'status-inactive' }}">
                            <i class="fas fa-circle fa-xs"></i>
                            {{ $template->status === 'active' ? 'Actif' : 'Inactif' }}
                        </span>
                    </div>

                    <p class="template-description">
                        {{ $template->description ?? 'Template OPAC personnalisé pour votre catalogue en ligne.' }}
                    </p>

                    <div class="d-flex justify-content-between text-muted small mb-3">
                        <span>
                            <i class="fas fa-calendar-alt me-1"></i>
                            {{ $template->updated_at->diffForHumans() }}
                        </span>
                        @if(method_exists($template, 'usage_count'))
                            <span>
                                <i class="fas fa-eye me-1"></i>
                                {{ $template->usage_count ?? 0 }} vues
                            </span>
                        @endif
                    </div>

                    <!-- Actions modernes -->
                    <div class="template-actions">
                        <a href="{{ route('public.opac-templates.edit', $template) }}"
                           class="btn-action btn-primary">
                            <i class="fas fa-edit"></i>
                            Éditer
                        </a>

                        <a href="{{ route('public.opac-templates.preview', $template) }}"
                           class="btn-action btn-secondary" target="_blank">
                            <i class="fas fa-external-link-alt"></i>
                            Aperçu
                        </a>

                        <button class="btn-action btn-success"
                                onclick="duplicateTemplate({{ $template->id }})">
                            <i class="fas fa-copy"></i>
                            Dupliquer
                        </button>

                        @if($template->status === 'active')
                            <button class="btn-action btn-warning"
                                    onclick="toggleTemplate({{ $template->id }}, 'inactive')">
                                <i class="fas fa-pause"></i>
                                Désactiver
                            </button>
                        @else
                            <button class="btn-action btn-success"
                                    onclick="toggleTemplate({{ $template->id }}, 'active')">
                                <i class="fas fa-play"></i>
                                Activer
                            </button>
                        @endif

                        <button class="btn-action btn-danger"
                                onclick="deleteTemplate({{ $template->id }})">
                            <i class="fas fa-trash"></i>
                            Supprimer
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-palette"></i>
                    </div>
                    <h3>Aucun template trouvé</h3>
                    <p class="text-muted mb-4">
                        Commencez par créer votre premier template OPAC personnalisé avec le nouveau système modulaire.
                    </p>
                    <a href="{{ route('public.opac-templates.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        Créer un template
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($templates->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $templates->appends(request()->query())->links() }}
        </div>
    @endif
</div>
</div>

<!-- Modal de prévisualisation -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Prévisualisation du Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="preview-frame" src="" style="width: 100%; height: 100%; border: none;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" onclick="editFromPreview()">
                    <i class="fas fa-edit me-2"></i>Éditer ce template
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variables globales
    let currentPreviewId = null;

    // Initialisation
    initializeSearch();
    initializeFilters();

    // Fonctions de recherche en temps réel
    function initializeSearch() {
        const searchInput = document.getElementById('template-search');
        let searchTimeout;

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                filterTemplates(this.value);
            }, 300);
        });
    }

    function filterTemplates(query) {
        const cards = document.querySelectorAll('.template-card');
        const searchTerm = query.toLowerCase();

        cards.forEach(card => {
            const name = card.dataset.name || '';
            const description = card.querySelector('.template-description').textContent.toLowerCase();
            const matches = name.includes(searchTerm) || description.includes(searchTerm);

            if (matches) {
                card.style.display = 'block';
                card.style.animation = 'slideInUp 0.3s ease-out';
            } else {
                card.style.display = 'none';
            }
        });
    }

    // Filtres par onglets
    function initializeFilters() {
        document.querySelectorAll('.filter-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Mise à jour des onglets actifs
                document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                // Filtrage
                const filter = this.dataset.filter;
                const cards = document.querySelectorAll('.template-card');

                cards.forEach(card => {
                    let show = true;

                    if (filter === 'active') {
                        show = card.dataset.status === 'active';
                    } else if (filter === 'inactive') {
                        show = card.dataset.status === 'inactive';
                    }
                    // 'all' montre tous les templates

                    if (show) {
                        card.style.display = 'block';
                        card.style.animation = 'slideInUp 0.3s ease-out';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    }

    // Actions globales
    window.previewTemplate = function(templateId) {
        currentPreviewId = templateId;
        const previewUrl = `{{ route('public.opac-templates.preview', ':id') }}`.replace(':id', templateId);

        document.getElementById('preview-frame').src = previewUrl;
        new bootstrap.Modal(document.getElementById('previewModal')).show();
    };

    window.editFromPreview = function() {
        if (currentPreviewId) {
            const editUrl = `{{ route('public.opac-templates.edit', ':id') }}`.replace(':id', currentPreviewId);
            window.location.href = editUrl;
        }
    };

    window.toggleTemplate = function(templateId, newStatus) {
        const card = document.querySelector(`[data-template-id="${templateId}"]`);
        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        formData.append('_method', 'PATCH');
        formData.append('status', newStatus);

        fetch(`{{ route('public.opac-templates.index') }}/${templateId}`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mise à jour visuelle
                card.dataset.status = newStatus;

                if (newStatus === 'active') {
                    card.classList.add('active');
                    card.querySelector('.template-status').className = 'template-status status-active';
                    card.querySelector('.template-status').innerHTML = '<i class="fas fa-circle fa-xs"></i> Actif';
                } else {
                    card.classList.remove('active');
                    card.querySelector('.template-status').className = 'template-status status-inactive';
                    card.querySelector('.template-status').innerHTML = '<i class="fas fa-circle fa-xs"></i> Inactif';
                }

                showNotification(`Template ${newStatus === 'active' ? 'activé' : 'désactivé'} avec succès`, 'success');
            } else {
                showNotification('Erreur lors de la mise à jour', 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showNotification('Erreur de communication', 'error');
        });
    };

    window.duplicateTemplate = function(templateId) {
        if (confirm('Voulez-vous dupliquer ce template ?')) {
            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

            fetch(`{{ route('public.opac-templates.index') }}/${templateId}/duplicate`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Template dupliqué avec succès', 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showNotification('Erreur lors de la duplication', 'error');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                showNotification('Erreur de communication', 'error');
            });
        }
    };

    window.deleteTemplate = function(templateId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer ce template ? Cette action est irréversible.')) {
            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            formData.append('_method', 'DELETE');

            fetch(`{{ route('public.opac-templates.index') }}/${templateId}`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const card = document.querySelector(`[data-template-id="${templateId}"]`);
                    card.style.animation = 'slideInUp 0.3s ease-out reverse';
                    setTimeout(() => card.remove(), 300);
                    showNotification('Template supprimé avec succès', 'success');
                } else {
                    showNotification('Erreur lors de la suppression', 'error');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                showNotification('Erreur de communication', 'error');
            });
        }
    };

    // Fonctions utilitaires
    function showNotification(message, type = 'info') {
        const alertClass = type === 'success' ? 'alert-success' :
                          type === 'error' ? 'alert-danger' : 'alert-info';

        const notification = document.createElement('div');
        notification.className = `alert ${alertClass} position-fixed top-0 end-0 m-3`;
        notification.style.zIndex = '9999';
        notification.style.minWidth = '300px';
        notification.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                <span>${message}</span>
                <button type="button" class="btn-close ms-auto" onclick="this.parentElement.parentElement.remove()"></button>
            </div>
        `;

        document.body.appendChild(notification);

        // Auto-remove après 4 secondes
        setTimeout(() => {
            if (notification.parentElement) {
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 300);
            }
        }, 4000);
    }

    // Actions du menu
    window.refreshTemplates = () => {
        showNotification('Actualisation des templates...', 'info');
        setTimeout(() => window.location.reload(), 1000);
    };

    window.exportAllTemplates = () => {
        showNotification('Export en cours...', 'info');
        window.location.href = `{{ route('public.opac-templates.index') }}/export-all`;
    };

    window.importTemplates = () => {
        showNotification('Fonction d\'import à venir dans la prochaine version', 'info');
    };

    window.showHelp = () => {
        showNotification('Documentation disponible dans le menu aide', 'info');
    };
});
</script>
@endpush
