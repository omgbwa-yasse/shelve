{{--
    Template Corporate Clean
    Design moderne et professionnel pour organisations corporatives
    Utilise le nouveau système de composants OPAC
--}}
@extends('opac.layouts.adaptive')

@section('title', 'Centre de Documentation - ' . ($opacConfig['branding']['site_name'] ?? config('app.name')))
@section('description', 'Centre de documentation d\'entreprise. Accédez facilement à nos ressources documentaires.')

@push('styles')
<style>
/* Corporate Clean Theme Styles */
.corporate-layout {
    font-family: var(--font-family);
}

.top-banner {
    background: linear-gradient(90deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    color: white;
    padding: 1rem 0;
    font-size: 0.9rem;
}

.corporate-header {
    background: white;
    border-bottom: 1px solid rgba(var(--bs-border-color-rgb), 0.2);
    padding: 1.5rem 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.04);
}

.brand-section {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.brand-logo {
    width: 60px;
    height: 60px;
    background: var(--primary-color);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    font-weight: bold;
}

.brand-text h1 {
    font-size: 1.8rem;
    font-weight: 600;
    color: var(--primary-color);
    margin: 0;
    line-height: 1.2;
}

.brand-text p {
    margin: 0;
    color: var(--text-color);
    font-size: 0.9rem;
    opacity: 0.8;
}

.search-container {
    background: rgba(var(--bs-light-rgb), 0.3);
    padding: 2rem 0;
    border-bottom: 1px solid rgba(var(--bs-border-color-rgb), 0.2);
}

.dashboard-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 1.5rem;
    margin: 2rem 0;
}

.dashboard-card {
    background: white;
    border: 1px solid rgba(var(--bs-border-color-rgb), 0.2);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    text-align: center;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.dashboard-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--accent-color);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.dashboard-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.1);
}

.dashboard-card:hover::before {
    transform: scaleX(1);
}

.card-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 1.5rem;
    color: white;
}

.card-icon.primary { background: var(--primary-color); }
.card-icon.secondary { background: var(--secondary-color); }
.card-icon.accent { background: var(--accent-color); }

.card-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.card-description {
    color: var(--text-color);
    font-size: 0.9rem;
    line-height: 1.4;
    opacity: 0.8;
}

.card-action {
    margin-top: 1rem;
}

.btn-corporate {
    background: var(--primary-color);
    border: none;
    color: white;
    padding: 0.5rem 1.5rem;
    border-radius: var(--border-radius);
    font-size: 0.9rem;
    font-weight: 500;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
}

.btn-corporate:hover {
    background: var(--secondary-color);
    color: white;
    transform: translateY(-1px);
}

.btn-corporate.outline {
    background: transparent;
    color: var(--primary-color);
    border: 2px solid var(--primary-color);
}

.btn-corporate.outline:hover {
    background: var(--primary-color);
    color: white;
}

.stats-bar {
    background: white;
    border: 1px solid rgba(var(--bs-border-color-rgb), 0.2);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    margin: 2rem 0;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 2rem;
    text-align: center;
}

.stat-item {
    position: relative;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary-color);
    display: block;
}

.stat-label {
    font-size: 0.9rem;
    color: var(--text-color);
    opacity: 0.7;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.content-sections {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
    margin: 2rem 0;
}

.main-section {
    background: white;
    border-radius: var(--border-radius);
    padding: 2rem;
    border: 1px solid rgba(var(--bs-border-color-rgb), 0.2);
}

.sidebar-widget {
    background: white;
    border-radius: var(--border-radius);
    padding: 1.5rem;
    border: 1px solid rgba(var(--bs-border-color-rgb), 0.2);
    margin-bottom: 1.5rem;
}

.widget-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--primary-color);
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--accent-color);
}

.document-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.document-list li {
    padding: 0.75rem 0;
    border-bottom: 1px solid rgba(var(--bs-border-color-rgb), 0.2);
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.document-list li:last-child {
    border-bottom: none;
}

.document-icon {
    width: 32px;
    height: 32px;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    color: white;
    background: var(--accent-color);
    flex-shrink: 0;
}

.document-info {
    flex: 1;
}

.document-title {
    font-size: 0.9rem;
    font-weight: 500;
    color: var(--primary-color);
    margin: 0;
    line-height: 1.3;
}

.document-meta {
    font-size: 0.8rem;
    color: var(--text-color);
    opacity: 0.7;
    margin: 0;
}

/* Responsive adjustments */
@media (max-width: 992px) {
    .dashboard-grid {
        grid-template-columns: 1fr 1fr;
    }

    .content-sections {
        grid-template-columns: 1fr;
    }

    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }

    .stats-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .brand-section {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }

    .brand-text h1 {
        font-size: 1.5rem;
    }
}

/* Animation utilities */
.slide-in {
    animation: slideIn 0.6s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.fade-up {
    animation: fadeUp 0.6s ease-out;
}

@keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
@endpush

@section('content')
<div class="corporate-layout">
    <!-- Bannière informative -->
    @if($opacConfig['ui']['show_banner'] ?? true)
    <div class="top-banner">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <span>
                    <i class="fas fa-info-circle me-2"></i>
                    Centre de documentation - Accès 24h/7j aux ressources numériques
                </span>
                <span class="d-none d-md-inline">
                    <i class="fas fa-phone me-1"></i>
                    Support : {{ $opacConfig['contact']['phone'] ?? '+33 1 23 45 67 89' }}
                </span>
            </div>
        </div>
    </div>
    @endif

    <!-- En-tête corporate -->
    <header class="corporate-header slide-in">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="brand-section">
                    <div class="brand-logo">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="brand-text">
                        <h1>{{ $opacConfig['branding']['site_name'] ?? 'Centre de Documentation' }}</h1>
                        <p>{{ $opacConfig['branding']['tagline'] ?? 'Ressources documentaires d\'entreprise' }}</p>
                    </div>
                </div>

                @if($opacConfig['features']['user_accounts'] ?? true)
                <div class="user-section">
                    @auth
                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-2"></i>
                                {{ Auth::user()->name }}
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('user.profile') }}">Mon profil</a></li>
                                <li><a class="dropdown-item" href="{{ route('user.bookmarks') }}">Mes favoris</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('logout') }}">Se déconnecter</a></li>
                            </ul>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-corporate me-2">
                            <i class="fas fa-sign-in-alt me-1"></i>
                            Connexion
                        </a>
                    @endauth
                </div>
                @endif
            </div>
        </div>
    </header>

    <!-- Section de recherche -->
    <section class="search-container fade-up">
        <div class="container">
            @include('opac.components.search-bar', [
                'showFilters' => true,
                'placeholder' => 'Rechercher documents, procédures, guides...',
                'showAdvancedLink' => true,
                'size' => 'large',
                'variant' => 'corporate'
            ])
        </div>
    </section>

    <div class="container">
        <!-- Tableau de bord -->
        <section class="dashboard-grid fade-up">
            <div class="dashboard-card">
                <div class="card-icon primary">
                    <i class="fas fa-search"></i>
                </div>
                <h3 class="card-title">Recherche avancée</h3>
                <p class="card-description">
                    Utilisez notre moteur de recherche avancé pour trouver rapidement les documents dont vous avez besoin.
                </p>
                <div class="card-action">
                    <a href="{{ route('opac.search.advanced') }}" class="btn-corporate">
                        Rechercher
                    </a>
                </div>
            </div>

            <div class="dashboard-card">
                <div class="card-icon secondary">
                    <i class="fas fa-folder-open"></i>
                </div>
                <h3 class="card-title">Parcourir</h3>
                <p class="card-description">
                    Explorez nos collections organisées par catégories et départements pour une navigation intuitive.
                </p>
                <div class="card-action">
                    <a href="{{ route('opac.browse') }}" class="btn-corporate outline">
                        Explorer
                    </a>
                </div>
            </div>

            <div class="dashboard-card">
                <div class="card-icon accent">
                    <i class="fas fa-star"></i>
                </div>
                <h3 class="card-title">Ressources populaires</h3>
                <p class="card-description">
                    Découvrez les documents les plus consultés et les ressources recommandées par nos experts.
                </p>
                <div class="card-action">
                    <a href="{{ route('opac.popular') }}" class="btn-corporate">
                        Découvrir
                    </a>
                </div>
            </div>
        </section>

        <!-- Statistiques -->
        @if($opacConfig['features']['statistics'] ?? true)
        <section class="stats-bar fade-up">
            <div class="stats-grid">
                <div class="stat-item">
                    <span class="stat-value">{{ number_format($statistics['total_documents'] ?? 2547) }}</span>
                    <span class="stat-label">Documents</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">{{ number_format($statistics['downloads_month'] ?? 1205) }}</span>
                    <span class="stat-label">Téléchargements</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">{{ number_format($statistics['active_users'] ?? 342) }}</span>
                    <span class="stat-label">Utilisateurs actifs</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">{{ number_format($statistics['new_this_month'] ?? 89) }}</span>
                    <span class="stat-label">Nouveautés</span>
                </div>
            </div>
        </section>
        @endif

        <!-- Contenu principal -->
        <div class="content-sections">
            <main class="main-section fade-up">
                <h2 class="section-title mb-4">Documents récents</h2>

                <div class="row">
                    @forelse($recentDocuments ?? [] as $document)
                        <div class="col-md-6 mb-3">
                            @include('opac.components.document-card', [
                                'document' => $document,
                                'showMetadata' => true,
                                'showBookmark' => $opacConfig['features']['bookmarks'] ?? true,
                                'imageHeight' => '120px',
                                'variant' => 'corporate-compact'
                            ])
                        </div>
                    @empty
                        @for($i = 1; $i <= 4; $i++)
                            <div class="col-md-6 mb-3">
                                <div class="d-flex border rounded p-3">
                                    <div class="document-icon me-3">
                                        <i class="fas fa-file-alt"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Procédure {{ $i }} - Guide d'utilisation</h6>
                                        <small class="text-muted">Département IT • Ajouté le {{ date('d/m/Y') }}</small>
                                    </div>
                                </div>
                            </div>
                        @endfor
                    @endforelse
                </div>

                <div class="text-center mt-4">
                    <a href="{{ route('opac.recent') }}" class="btn-corporate">
                        Voir tous les documents récents
                        <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </main>

            <aside class="sidebar" aria-label="Informations complémentaires">
                <!-- Documents populaires -->
                <div class="sidebar-widget fade-up">
                    <h3 class="widget-title">
                        <i class="fas fa-chart-line me-2"></i>
                        Les plus consultés
                    </h3>
                    <ul class="document-list">
                        @forelse($popularDocuments ?? [] as $document)
                            <li>
                                <div class="document-icon">
                                    <i class="fas fa-file"></i>
                                </div>
                                <div class="document-info">
                                    <h6 class="document-title">{{ Str::limit($document->title, 40) }}</h6>
                                    <p class="document-meta">{{ $document->views ?? 0 }} consultations</p>
                                </div>
                            </li>
                        @empty
                            @for($i = 1; $i <= 5; $i++)
                                <li>
                                    <div class="document-icon">
                                        <i class="fas fa-file-alt"></i>
                                    </div>
                                    <div class="document-info">
                                        <h6 class="document-title">Guide procédure {{ $i }}</h6>
                                        <p class="document-meta">{{ rand(50, 200) }} consultations</p>
                                    </div>
                                </li>
                            @endfor
                        @endforelse
                    </ul>
                </div>

                <!-- Accès rapide -->
                <div class="sidebar-widget fade-up">
                    <h3 class="widget-title">
                        <i class="fas fa-bolt me-2"></i>
                        Accès rapide
                    </h3>
                    <div class="d-grid gap-2">
                        <a href="{{ route('opac.forms') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-wpforms me-2"></i>
                            Formulaires
                        </a>
                        <a href="{{ route('opac.procedures') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-list-ol me-2"></i>
                            Procédures
                        </a>
                        <a href="{{ route('opac.policies') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-shield-alt me-2"></i>
                            Politiques
                        </a>
                        <a href="{{ route('opac.contact') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-headset me-2"></i>
                            Support
                        </a>
                    </div>
                </div>

                <!-- Contact support -->
                <div class="sidebar-widget fade-up">
                    <h3 class="widget-title">
                        <i class="fas fa-life-ring me-2"></i>
                        Besoin d'aide ?
                    </h3>
                    <p class="small text-muted mb-3">
                        Notre équipe support est disponible pour vous aider.
                    </p>
                    <div class="d-grid">
                        <a href="mailto:{{ $opacConfig['contact']['email'] ?? 'support@company.com' }}"
                           class="btn btn-corporate btn-sm">
                            <i class="fas fa-envelope me-2"></i>
                            Contacter le support
                        </a>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animation d'apparition progressive
    const animatedElements = document.querySelectorAll('.slide-in, .fade-up');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = entry.target.classList.contains('slide-in')
                    ? 'translateX(0)'
                    : 'translateY(0)';
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    animatedElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = el.classList.contains('slide-in')
            ? 'translateX(-20px)'
            : 'translateY(20px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });

    // Amélioration UX pour les cartes dashboard
    document.querySelectorAll('.dashboard-card').forEach(card => {
        card.addEventListener('click', function(e) {
            if (!e.target.closest('a')) {
                const link = this.querySelector('a');
                if (link) {
                    window.location.href = link.href;
                }
            }
        });
    });

    // Statistiques animées
    document.querySelectorAll('.stat-value').forEach(stat => {
        const finalValue = parseInt(stat.textContent.replace(/,/g, ''));
        let currentValue = 0;
        const increment = finalValue / 50;
        const timer = setInterval(() => {
            currentValue += increment;
            if (currentValue >= finalValue) {
                currentValue = finalValue;
                clearInterval(timer);
            }
            stat.textContent = new Intl.NumberFormat().format(Math.floor(currentValue));
        }, 50);
    });

    console.log('Corporate Clean template loaded successfully');
});
</script>
@endpush
