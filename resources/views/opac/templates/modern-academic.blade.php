{{--
    Template Modern Academic
    Design moderne pour institutions académiques
    Utilise le nouveau système de composants OPAC
--}}
@extends('opac.layouts.adaptive')

@section('title', 'Catalogue Académique - ' . ($opacConfig['branding']['site_name'] ?? config('app.name')))
@section('description', 'Explorez notre catalogue académique et découvrez nos collections universitaires.')

@push('styles')
<style>
/* Modern Academic Theme Styles */
.hero-section {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    color: white;
    padding: 4rem 0;
    margin-bottom: 3rem;
    border-radius: 0 0 2rem 2rem;
    position: relative;
    overflow: hidden;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>');
    background-size: 50px 50px;
    animation: float 20s infinite linear;
}

@keyframes float {
    0% { transform: translateY(0px) translateX(0px); }
    50% { transform: translateY(-10px) translateX(5px); }
    100% { transform: translateY(0px) translateX(0px); }
}

.hero-content {
    position: relative;
    z-index: 2;
    text-align: center;
}

.hero-title {
    font-size: 3rem;
    font-weight: 700;
    margin-bottom: 1rem;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.hero-subtitle {
    font-size: 1.25rem;
    opacity: 0.9;
    margin-bottom: 2rem;
}

.stats-section {
    margin: 3rem 0;
    padding: 2rem;
    background: rgba(var(--bs-light-rgb), 0.3);
    border-radius: var(--border-radius);
    border-left: 4px solid var(--primary-color);
}

.recent-section {
    margin: 3rem 0;
}

.section-title {
    color: var(--primary-color);
    font-weight: 600;
    margin-bottom: 2rem;
    position: relative;
    padding-bottom: 0.5rem;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 50px;
    height: 3px;
    background: var(--accent-color);
    border-radius: 2px;
}

.documents-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
}

.quick-actions {
    background: white;
    border-radius: var(--border-radius);
    padding: 2rem;
    box-shadow: var(--box-shadow);
    margin: 2rem 0;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    justify-content: center;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: var(--border-radius);
    background: var(--primary-color);
    color: white;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.action-btn:hover {
    background: var(--secondary-color);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.action-btn.secondary {
    background: var(--accent-color);
}

.action-btn.outline {
    background: transparent;
    color: var(--primary-color);
    border: 2px solid var(--primary-color);
}

.action-btn.outline:hover {
    background: var(--primary-color);
    color: white;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .hero-title {
        font-size: 2rem;
    }

    .hero-subtitle {
        font-size: 1rem;
    }

    .documents-grid {
        grid-template-columns: 1fr;
    }

    .action-buttons {
        flex-direction: column;
        align-items: stretch;
    }
}

/* Animation utilities */
.fade-in {
    animation: fadeIn 0.6s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.slide-up {
    animation: slideUp 0.8s ease-out;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
@endpush

@section('content')
<div class="modern-academic-layout">
    <!-- Hero Section avec recherche -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content fade-in">
                <h1 class="hero-title">
                    {{ $opacConfig['branding']['site_name'] ?? 'Bibliothèque Universitaire' }}
                </h1>
                <p class="hero-subtitle">
                    Explorez nos collections académiques et ressources numériques
                </p>

                <!-- Barre de recherche principale -->
                @include('opac.components.search-bar', [
                    'showFilters' => true,
                    'placeholder' => 'Rechercher dans nos collections académiques...',
                    'showAdvancedLink' => true,
                    'size' => 'large',
                    'variant' => 'hero'
                ])
            </div>
        </div>
    </section>

    <!-- Actions rapides -->
    @if($opacConfig['features']['quick_actions'] ?? true)
    <section class="quick-actions slide-up">
        <div class="text-center">
            <h3 class="section-title d-inline-block">Accès rapide</h3>
            <div class="action-buttons">
                <a href="{{ route('opac.browse') }}" class="action-btn">
                    <i class="fas fa-list"></i>
                    Parcourir par catégorie
                </a>
                <a href="{{ route('opac.recent') }}" class="action-btn secondary">
                    <i class="fas fa-clock"></i>
                    Nouvelles acquisitions
                </a>
                <a href="{{ route('opac.popular') }}" class="action-btn outline">
                    <i class="fas fa-star"></i>
                    Documents populaires
                </a>
                @if($opacConfig['features']['digital_collections'] ?? false)
                    <a href="{{ route('opac.digital') }}" class="action-btn outline">
                        <i class="fas fa-laptop"></i>
                        Collections numériques
                    </a>
                @endif
            </div>
        </div>
    </section>
    @endif

    <!-- Statistiques -->
    @if($opacConfig['features']['statistics'] ?? true)
    <section class="stats-section slide-up">
        <div class="row text-center">
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-item">
                    <i class="fas fa-book fa-2x text-primary mb-2"></i>
                    <div class="h4 mb-0">{{ number_format($statistics['total_documents'] ?? 0) }}</div>
                    <small class="text-muted">Documents</small>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-item">
                    <i class="fas fa-users fa-2x text-success mb-2"></i>
                    <div class="h4 mb-0">{{ number_format($statistics['active_users'] ?? 0) }}</div>
                    <small class="text-muted">Utilisateurs actifs</small>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-item">
                    <i class="fas fa-download fa-2x text-info mb-2"></i>
                    <div class="h4 mb-0">{{ number_format($statistics['downloads_month'] ?? 0) }}</div>
                    <small class="text-muted">Téléchargements ce mois</small>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-item">
                    <i class="fas fa-globe fa-2x text-warning mb-2"></i>
                    <div class="h4 mb-0">{{ number_format($statistics['digital_resources'] ?? 0) }}</div>
                    <small class="text-muted">Ressources numériques</small>
                </div>
            </div>
        </div>
    </section>
    @endif

    <!-- Documents récents -->
    <section class="recent-section slide-up">
        <h2 class="section-title">Dernières acquisitions</h2>
        <p class="text-muted mb-4">
            Découvrez les derniers ouvrages ajoutés à notre collection
        </p>

        <div class="documents-grid">
            @forelse($recentDocuments ?? [] as $document)
                @include('opac.components.document-card', [
                    'document' => $document,
                    'showMetadata' => true,
                    'showBookmark' => $opacConfig['features']['bookmarks'] ?? false,
                    'imageHeight' => '200px',
                    'variant' => 'academic'
                ])
            @empty
                <div class="col-12 text-center py-5">
                    <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">Aucun document récent</h4>
                    <p class="text-muted">Les nouvelles acquisitions apparaîtront ici.</p>
                </div>
            @endforelse
        </div>

        @if(($recentDocuments ?? collect())->isNotEmpty())
            <div class="text-center mt-4">
                <a href="{{ route('opac.recent') }}" class="action-btn outline">
                    Voir toutes les acquisitions
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        @endif
    </section>

    <!-- Pagination si nécessaire -->
    @if(isset($documents) && $documents->hasPages())
        @include('opac.components.pagination', [
            'paginator' => $documents,
            'showInfo' => true,
            'showFirstLast' => true,
            'variant' => 'academic'
        ])
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animation d'apparition progressive
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observer tous les éléments avec animation
    document.querySelectorAll('.slide-up').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });

    // Amélioration UX pour les boutons d'action
    document.querySelectorAll('.action-btn').forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });

        btn.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // Tracking des interactions (optionnel)
    if (window.OpacConfig && window.OpacConfig.features && window.OpacConfig.features.analytics) {
        document.querySelectorAll('.action-btn, .document-card a').forEach(link => {
            link.addEventListener('click', function() {
                console.log('OPAC Interaction:', {
                    type: 'click',
                    target: this.href || this.textContent.trim(),
                    timestamp: new Date().toISOString()
                });
            });
        });
    }
});
</script>
@endpush
