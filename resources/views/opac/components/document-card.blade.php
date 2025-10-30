{{-- Composant carte de document OPAC --}}
<div class="document-card" data-document-id="{{ $document->id }}">
    <div class="card h-100">
        @if($showThumbnail ?? true)
            <div class="card-img-top-wrapper">
                @if($document->thumbnail_url)
                    <img src="{{ $document->thumbnail_url }}"
                         class="card-img-top document-thumbnail"
                         alt="Couverture de {{ $document->title }}"
                         loading="lazy">
                @else
                    <div class="document-placeholder">
                        <i class="fas {{ $this->getDocumentIcon($document->type) }}"></i>
                    </div>
                @endif
            </div>
        @endif

        <div class="card-body d-flex flex-column">
            <h5 class="card-title">
                <a href="{{ route('opac.document.show', $document->id) }}"
                   class="document-link text-decoration-none">
                    {{ Str::limit($document->title, $titleLimit ?? 80) }}
                </a>
            </h5>

            @if($document->author && ($showAuthor ?? true))
                <p class="card-text document-author">
                    <small class="text-muted">
                        <i class="fas fa-user me-1"></i>
                        {{ $document->author }}
                    </small>
                </p>
            @endif

            @if($document->description && ($showDescription ?? true))
                <p class="card-text document-description flex-grow-1">
                    {{ Str::limit($document->description, $descriptionLimit ?? 120) }}
                </p>
            @endif

            <div class="document-metadata mt-auto">
                @if($document->publication_date && ($showDate ?? true))
                    <small class="text-muted d-block">
                        <i class="fas fa-calendar me-1"></i>
                        {{ $document->publication_date->format('Y') }}
                    </small>
                @endif

                @if($document->type && ($showType ?? true))
                    <span class="badge bg-{{ $this->getTypeColor($document->type) }} mt-1">
                        {{ ucfirst($document->type) }}
                    </span>
                @endif

                @if($document->language && ($showLanguage ?? false))
                    <span class="badge bg-secondary mt-1 ms-1">
                        {{ strtoupper($document->language) }}
                    </span>
                @endif
            </div>

            @if($showActions ?? true)
                <div class="document-actions mt-2">
                    <div class="btn-group btn-group-sm" role="group">
                        <a href="{{ route('opac.document.show', $document->id) }}"
                           class="btn btn-outline-primary">
                            <i class="fas fa-eye"></i>
                            <span class="d-none d-lg-inline">Voir</span>
                        </a>

                        @if($document->downloadable && ($showDownload ?? true))
                            <a href="{{ route('opac.document.download', $document->id) }}"
                               class="btn btn-outline-success">
                                <i class="fas fa-download"></i>
                                <span class="d-none d-lg-inline">Télécharger</span>
                            </a>
                        @endif

                        @if($showBookmark ?? true)
                            <button type="button"
                                    class="btn btn-outline-warning bookmark-btn"
                                    data-document-id="{{ $document->id }}"
                                    title="Ajouter aux favoris">
                                <i class="fas fa-bookmark"></i>
                            </button>
                        @endif

                        @if($showShare ?? true)
                            <button type="button"
                                    class="btn btn-outline-info share-btn"
                                    data-document-id="{{ $document->id }}"
                                    title="Partager">
                                <i class="fas fa-share-alt"></i>
                            </button>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        @if($showStatus ?? false)
            <div class="card-footer">
                @if($document->availability_status)
                    <small class="availability-status status-{{ $document->availability_status }}">
                        <i class="fas {{ $this->getStatusIcon($document->availability_status) }}"></i>
                        {{ $this->getStatusLabel($document->availability_status) }}
                    </small>
                @endif
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
.document-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.document-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.card-img-top-wrapper {
    position: relative;
    height: 200px;
    overflow: hidden;
    background: var(--light-color, #f8f9fa);
}

.document-thumbnail {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.2s ease-in-out;
}

.document-card:hover .document-thumbnail {
    transform: scale(1.05);
}

.document-placeholder {
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--light-color, #f8f9fa), var(--secondary-color, #6c757d));
    color: var(--dark-color, #343a40);
    font-size: 3rem;
}

.document-link {
    color: var(--dark-color, #343a40);
    transition: color 0.2s ease-in-out;
}

.document-link:hover {
    color: var(--primary-color, #007bff);
}

.card-title {
    font-size: 1.1rem;
    font-weight: 600;
    line-height: 1.3;
    margin-bottom: 0.75rem;
}

.document-author {
    margin-bottom: 0.5rem;
}

.document-description {
    font-size: 0.9rem;
    line-height: 1.4;
    color: var(--body-color, #212529);
}

.document-metadata {
    border-top: 1px solid var(--border-color, #dee2e6);
    padding-top: 0.5rem;
    margin-top: 0.5rem;
}

.document-actions {
    border-top: 1px solid var(--border-color, #dee2e6);
    padding-top: 0.75rem;
}

.availability-status {
    font-weight: 500;
}

.status-available {
    color: var(--success-color, #28a745);
}

.status-borrowed {
    color: var(--warning-color, #ffc107);
}

.status-reserved {
    color: var(--info-color, #17a2b8);
}

.status-unavailable {
    color: var(--danger-color, #dc3545);
}

/* Responsive adjustments */
@media (max-width: 576px) {
    .card-img-top-wrapper {
        height: 150px;
    }

    .document-placeholder {
        font-size: 2rem;
    }

    .card-title {
        font-size: 1rem;
    }

    .document-actions .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
    }
}

/* Animation pour le chargement */
.document-card.loading {
    opacity: 0.7;
    pointer-events: none;
}

.document-card.loading::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(90deg,
        transparent 0%,
        rgba(255, 255, 255, 0.4) 50%,
        transparent 100%);
    animation: loading-shimmer 1.5s infinite;
}

@keyframes loading-shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des favoris
    document.querySelectorAll('.bookmark-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const documentId = this.dataset.documentId;
            toggleBookmark(documentId, this);
        });
    });

    // Gestion du partage
    document.querySelectorAll('.share-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const documentId = this.dataset.documentId;
            shareDocument(documentId);
        });
    });
});

function toggleBookmark(documentId, button) {
    const icon = button.querySelector('i');
    const isBookmarked = icon.classList.contains('fas');

    // Animation de chargement
    button.disabled = true;
    icon.className = 'fas fa-spinner fa-spin';

    fetch(`{{ route('opac.bookmark.toggle') }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ document_id: documentId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.bookmarked) {
            icon.className = 'fas fa-bookmark';
            button.classList.remove('btn-outline-warning');
            button.classList.add('btn-warning');
            button.title = 'Retirer des favoris';
        } else {
            icon.className = 'far fa-bookmark';
            button.classList.remove('btn-warning');
            button.classList.add('btn-outline-warning');
            button.title = 'Ajouter aux favoris';
        }
    })
    .catch(error => {
        console.error('Erreur lors de la gestion du favori:', error);
        // Restaurer l'état précédent
        icon.className = isBookmarked ? 'fas fa-bookmark' : 'far fa-bookmark';
    })
    .finally(() => {
        button.disabled = false;
    });
}

function shareDocument(documentId) {
    const url = `{{ route('opac.document.show', ':id') }}`.replace(':id', documentId);
    const title = document.querySelector(`[data-document-id="${documentId}"] .card-title a`).textContent.trim();

    if (navigator.share) {
        navigator.share({
            title: title,
            url: url
        }).catch(err => console.log('Erreur lors du partage:', err));
    } else {
        // Fallback: copier dans le presse-papiers
        navigator.clipboard.writeText(url).then(() => {
            // Afficher une notification
            showNotification('Lien copié dans le presse-papiers !', 'success');
        }).catch(err => {
            console.error('Erreur lors de la copie:', err);
            // Fallback: ouvrir une modal de partage
            openShareModal(url, title);
        });
    }
}

function showNotification(message, type = 'info') {
    // Créer et afficher une notification temporaire
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    document.body.appendChild(notification);

    // Auto-suppression après 3 secondes
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}

function openShareModal(url, title) {
    // Créer une modal de partage simple
    const modal = document.createElement('div');
    modal.innerHTML = `
        <div class="modal fade" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Partager le document</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p><strong>${title}</strong></p>
                        <div class="input-group">
                            <input type="text" class="form-control" value="${url}" readonly>
                            <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText('${url}')">
                                Copier
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
    const modalInstance = new bootstrap.Modal(modal.querySelector('.modal'));
    modalInstance.show();

    // Nettoyer après fermeture
    modal.addEventListener('hidden.bs.modal', () => {
        modal.remove();
    });
}
</script>
@endpush

@php
// Méthodes helper pour le composant
if (!function_exists('getDocumentIcon')) {
    function getDocumentIcon($type) {
        return match($type) {
            'book' => 'fa-book',
            'article' => 'fa-newspaper',
            'multimedia' => 'fa-play-circle',
            'thesis' => 'fa-graduation-cap',
            'report' => 'fa-file-alt',
            'map' => 'fa-map',
            'image' => 'fa-image',
            'audio' => 'fa-music',
            'video' => 'fa-video',
            default => 'fa-file'
        };
    }
}

if (!function_exists('getTypeColor')) {
    function getTypeColor($type) {
        return match($type) {
            'book' => 'primary',
            'article' => 'info',
            'multimedia' => 'warning',
            'thesis' => 'success',
            'report' => 'secondary',
            default => 'light'
        };
    }
}

if (!function_exists('getStatusIcon')) {
    function getStatusIcon($status) {
        return match($status) {
            'available' => 'fa-check-circle',
            'borrowed' => 'fa-clock',
            'reserved' => 'fa-bookmark',
            'unavailable' => 'fa-times-circle',
            default => 'fa-question-circle'
        };
    }
}

if (!function_exists('getStatusLabel')) {
    function getStatusLabel($status) {
        return match($status) {
            'available' => 'Disponible',
            'borrowed' => 'Emprunté',
            'reserved' => 'Réservé',
            'unavailable' => 'Indisponible',
            default => 'Statut inconnu'
        };
    }
}
@endphp
