{{--
    Template Classic Library
    Design traditionnel et élégant pour bibliothèques classiques
    Utilise le nouveau système de composants OPAC
--}}
@extends('opac.layouts.adaptive')

@section('title', 'Bibliothèque - ' . ($opacConfig['branding']['site_name'] ?? config('app.name')))
@section('description', 'Catalogue traditionnel de la bibliothèque. Recherchez et consultez nos collections.')

@push('styles')
<style>
/* Classic Library Theme Styles */
.classic-header {
    background: var(--background-color);
    border-bottom: 3px solid var(--primary-color);
    padding: 2rem 0;
    margin-bottom: 2rem;
}

.library-title {
    font-family: 'Georgia', serif;
    color: var(--primary-color);
    font-size: 2.5rem;
    font-weight: normal;
    text-align: center;
    margin-bottom: 1rem;
    position: relative;
}

.library-title::before,
.library-title::after {
    content: '❦';
    color: var(--accent-color);
    font-size: 1.5rem;
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
}

.library-title::before {
    left: -3rem;
}

.library-title::after {
    right: -3rem;
}

.library-subtitle {
    text-align: center;
    color: var(--text-color);
    font-style: italic;
    margin-bottom: 2rem;
}

.search-section {
    background: rgba(var(--bs-light-rgb), 0.5);
    padding: 2rem;
    border-radius: var(--border-radius);
    margin: 2rem 0;
    border: 1px solid rgba(var(--bs-border-color-rgb), 0.2);
}

.search-section h3 {
    color: var(--primary-color);
    font-family: 'Georgia', serif;
    margin-bottom: 1.5rem;
    text-align: center;
}

.catalog-sections {
    display: grid;
    grid-template-columns: 1fr 2fr 1fr;
    gap: 2rem;
    margin: 3rem 0;
}

.sidebar-section {
    background: white;
    padding: 1.5rem;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    border-top: 4px solid var(--primary-color);
    height: fit-content;
}

.sidebar-title {
    color: var(--primary-color);
    font-family: 'Georgia', serif;
    font-size: 1.2rem;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid rgba(var(--bs-border-color-rgb), 0.3);
}

.main-content {
    background: white;
    padding: 2rem;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
}

.section-header {
    color: var(--primary-color);
    font-family: 'Georgia', serif;
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
    text-align: center;
    position: relative;
}

.section-header::after {
    content: '';
    display: block;
    width: 100px;
    height: 2px;
    background: var(--accent-color);
    margin: 0.5rem auto;
}

.category-list {
    list-style: none;
    padding: 0;
}

.category-list li {
    padding: 0.5rem 0;
    border-bottom: 1px solid rgba(var(--bs-border-color-rgb), 0.2);
}

.category-list a {
    color: var(--text-color);
    text-decoration: none;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: color 0.3s ease;
}

.category-list a:hover {
    color: var(--primary-color);
}

.category-count {
    background: var(--primary-color);
    color: white;
    font-size: 0.8rem;
    padding: 0.2rem 0.5rem;
    border-radius: 12px;
}

.featured-documents {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin: 2rem 0;
}

.classic-card {
    background: white;
    border: 1px solid rgba(var(--bs-border-color-rgb), 0.3);
    border-radius: var(--border-radius);
    overflow: hidden;
    transition: all 0.3s ease;
}

.classic-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.news-item {
    padding: 1rem 0;
    border-bottom: 1px solid rgba(var(--bs-border-color-rgb), 0.2);
}

.news-date {
    color: var(--accent-color);
    font-size: 0.9rem;
    font-weight: 500;
}

.news-title {
    color: var(--primary-color);
    font-size: 1rem;
    margin: 0.5rem 0;
}

.hours-table {
    width: 100%;
    font-size: 0.9rem;
}

.hours-table td {
    padding: 0.5rem 0;
    border-bottom: 1px solid rgba(var(--bs-border-color-rgb), 0.2);
}

.hours-table .day {
    font-weight: 500;
}

.hours-table .hours {
    text-align: right;
    color: var(--accent-color);
}

/* Responsive adjustments */
@media (max-width: 992px) {
    .catalog-sections {
        grid-template-columns: 1fr;
    }

    .library-title::before,
    .library-title::after {
        display: none;
    }

    .library-title {
        font-size: 2rem;
    }
}

@media (max-width: 768px) {
    .featured-documents {
        grid-template-columns: 1fr;
    }

    .library-title {
        font-size: 1.8rem;
    }
}

/* Classic animation */
.fade-in-classic {
    animation: fadeInClassic 1s ease-out;
}

@keyframes fadeInClassic {
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
<div class="classic-library-layout">
    <!-- En-tête classique -->
    <header class="classic-header fade-in-classic">
        <div class="container">
            <h1 class="library-title">
                {{ $opacConfig['branding']['site_name'] ?? 'Bibliothèque Municipale' }}
            </h1>
            <p class="library-subtitle">
                {{ $opacConfig['branding']['tagline'] ?? 'Gardienne du savoir et de la culture' }}
            </p>
        </div>
    </header>

    <!-- Section de recherche -->
    <section class="search-section fade-in-classic">
        <div class="container">
            <h3>Recherche dans le catalogue</h3>
            @include('opac.components.search-bar', [
                'showFilters' => false,
                'placeholder' => 'Titre, auteur, sujet...',
                'showAdvancedLink' => true,
                'size' => 'medium',
                'variant' => 'classic'
            ])
        </div>
    </section>

    <!-- Sections du catalogue -->
    <div class="container">
        <div class="catalog-sections">
            <!-- Sidebar gauche - Catégories -->
            <aside class="sidebar-section fade-in-classic">
                <h3 class="sidebar-title">Parcourir par catégorie</h3>
                <ul class="category-list">
                    @foreach($categories ?? [] as $category)
                        <li>
                            <a href="{{ route('opac.category', $category->id) }}">
                                <span>{{ $category->name }}</span>
                                <span class="category-count">{{ $category->documents_count ?? 0 }}</span>
                            </a>
                        </li>
                    @endforeach

                    @if(($categories ?? collect())->isEmpty())
                        <li><a href="{{ route('opac.browse') }}">Romans <span class="category-count">245</span></a></li>
                        <li><a href="{{ route('opac.browse') }}">Histoire <span class="category-count">189</span></a></li>
                        <li><a href="{{ route('opac.browse') }}">Sciences <span class="category-count">156</span></a></li>
                        <li><a href="{{ route('opac.browse') }}">Biographies <span class="category-count">98</span></a></li>
                        <li><a href="{{ route('opac.browse') }}">Art <span class="category-count">87</span></a></li>
                    @endif
                </ul>

                <div class="mt-4">
                    <a href="{{ route('opac.browse') }}" class="btn btn-outline-primary btn-sm w-100">
                        Voir toutes les catégories
                    </a>
                </div>
            </aside>

            <!-- Contenu principal -->
            <main class="main-content fade-in-classic">
                <h2 class="section-header">Documents en vedette</h2>

                <div class="featured-documents">
                    @forelse($featuredDocuments ?? [] as $document)
                        @include('opac.components.document-card', [
                            'document' => $document,
                            'showMetadata' => true,
                            'showBookmark' => false,
                            'imageHeight' => '180px',
                            'variant' => 'classic'
                        ])
                    @empty
                        @for($i = 1; $i <= 4; $i++)
                            <div class="classic-card">
                                <div class="p-3 text-center">
                                    <i class="fas fa-book fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Document {{ $i }}</h5>
                                    <p class="text-muted small">Description du document en vedette...</p>
                                </div>
                            </div>
                        @endfor
                    @endforelse
                </div>

                @if(($featuredDocuments ?? collect())->isNotEmpty())
                    <div class="text-center mt-4">
                        <a href="{{ route('opac.featured') }}" class="btn btn-primary">
                            Voir tous les documents en vedette
                        </a>
                    </div>
                @endif
            </main>

            <!-- Sidebar droite - Informations -->
            <aside class="sidebar-section fade-in-classic">
                <!-- Actualités -->
                <h3 class="sidebar-title">Actualités</h3>
                <div class="news-list">
                    @forelse($news ?? [] as $newsItem)
                        <article class="news-item">
                            <div class="news-date">{{ $newsItem->created_at->format('d/m/Y') }}</div>
                            <h4 class="news-title">{{ $newsItem->title }}</h4>
                            <p class="small text-muted">{{ Str::limit($newsItem->excerpt, 80) }}</p>
                        </article>
                    @empty
                        <article class="news-item">
                            <div class="news-date">{{ date('d/m/Y') }}</div>
                            <h4 class="news-title">Nouvelles acquisitions</h4>
                            <p class="small text-muted">Découvrez nos dernières acquisitions...</p>
                        </article>
                    @endforelse
                </div>

                <!-- Horaires -->
                <h3 class="sidebar-title mt-4">Horaires d'ouverture</h3>
                <table class="hours-table">
                    <tr>
                        <td class="day">Lundi</td>
                        <td class="hours">9h - 18h</td>
                    </tr>
                    <tr>
                        <td class="day">Mardi</td>
                        <td class="hours">9h - 18h</td>
                    </tr>
                    <tr>
                        <td class="day">Mercredi</td>
                        <td class="hours">9h - 18h</td>
                    </tr>
                    <tr>
                        <td class="day">Jeudi</td>
                        <td class="hours">9h - 20h</td>
                    </tr>
                    <tr>
                        <td class="day">Vendredi</td>
                        <td class="hours">9h - 18h</td>
                    </tr>
                    <tr>
                        <td class="day">Samedi</td>
                        <td class="hours">9h - 17h</td>
                    </tr>
                    <tr>
                        <td class="day">Dimanche</td>
                        <td class="hours text-danger">Fermé</td>
                    </tr>
                </table>

                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i>
                        Horaires susceptibles de modification
                    </small>
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animation progressive des éléments
    const elements = document.querySelectorAll('.fade-in-classic');

    elements.forEach((el, index) => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.8s ease, transform 0.8s ease';

        setTimeout(() => {
            el.style.opacity = '1';
            el.style.transform = 'translateY(0)';
        }, index * 200);
    });

    // Interaction hover pour les cartes classiques
    document.querySelectorAll('.classic-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.borderColor = 'var(--primary-color)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.borderColor = 'rgba(var(--bs-border-color-rgb), 0.3)';
        });
    });

    // Amélioration de l'accessibilité pour les liens de catégories
    document.querySelectorAll('.category-list a').forEach(link => {
        link.addEventListener('focus', function() {
            this.style.backgroundColor = 'rgba(var(--bs-primary-rgb), 0.1)';
        });

        link.addEventListener('blur', function() {
            this.style.backgroundColor = 'transparent';
        });
    });

    console.log('Classic Library template loaded successfully');
});
</script>
@endpush
