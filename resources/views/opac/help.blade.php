@extends('opac.layouts.app')

@section('title', __('Help') . ' - OPAC')

@section('content')
<div class="container my-5">
    <div class="row">
        <div class="col-md-3">
            <!-- Table des matières -->
            <div class="opac-card">
                <div class="opac-card-header">
                    <i class="fas fa-list me-2"></i>{{ __('Contents') }}
                </div>
                <div class="card-body">
                    <nav class="nav flex-column">
                        <a class="nav-link" href="#getting-started">{{ __('Getting Started') }}</a>
                        <a class="nav-link" href="#searching">{{ __('How to Search') }}</a>
                        <a class="nav-link" href="#browsing">{{ __('Browsing Collections') }}</a>
                        <a class="nav-link" href="#understanding-results">{{ __('Understanding Results') }}</a>
                        <a class="nav-link" href="#advanced-search">{{ __('Advanced Search') }}</a>
                        <a class="nav-link" href="#contact">{{ __('Contact Us') }}</a>
                    </nav>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <h1 class="mb-4">{{ __('Help & User Guide') }}</h1>

            <!-- Getting Started -->
            <section id="getting-started" class="opac-card mb-4">
                <div class="opac-card-header">
                    <i class="fas fa-play-circle me-2"></i>{{ __('Getting Started') }}
                </div>
                <div class="card-body">
                    <p>{{ __('Welcome to our Online Public Access Catalog (OPAC). This system allows you to search and discover documents from our archive collections.') }}</p>

                    <h5>{{ __('What you can do:') }}</h5>
                    <ul>
                        <li>{{ __('Search documents by title, content, author, or keywords') }}</li>
                        <li>{{ __('Browse collections by category') }}</li>
                        <li>{{ __('View detailed information about each document') }}</li>
                        <li>{{ __('Filter results by date, category, and other criteria') }}</li>
                    </ul>
                </div>
            </section>

            <!-- How to Search -->
            <section id="searching" class="opac-card mb-4">
                <div class="opac-card-header">
                    <i class="fas fa-search me-2"></i>{{ __('How to Search') }}
                </div>
                <div class="card-body">
                    <h5>{{ __('Basic Search') }}</h5>
                    <p>{{ __('Enter keywords in the search box on the homepage or use the search page. The system will look for your terms in document titles, content, and descriptions.') }}</p>

                    <h5 class="mt-4">{{ __('Search Tips') }}</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-lightbulb me-2"></i>{{ __('Exact Phrases') }}</h6>
                                <p class="mb-0">{{ __('Use quotes to search for exact phrases: "annual report"') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-success">
                                <h6><i class="fas fa-asterisk me-2"></i>{{ __('Wildcards') }}</h6>
                                <p class="mb-0">{{ __('Use * for variations: report* finds report, reports, reporting') }}</p>
                            </div>
                        </div>
                    </div>

                    <h5 class="mt-4">{{ __('Search Examples') }}</h5>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('Search Term') }}</th>
                                    <th>{{ __('What it finds') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>budget</code></td>
                                    <td>{{ __('Documents containing the word "budget"') }}</td>
                                </tr>
                                <tr>
                                    <td><code>"annual budget"</code></td>
                                    <td>{{ __('Documents containing the exact phrase "annual budget"') }}</td>
                                </tr>
                                <tr>
                                    <td><code>budget*</code></td>
                                    <td>{{ __('Documents containing budget, budgets, budgetary, etc.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Browsing Collections -->
            <section id="browsing" class="opac-card mb-4">
                <div class="opac-card-header">
                    <i class="fas fa-sitemap me-2"></i>{{ __('Browsing Collections') }}
                </div>
                <div class="card-body">
                    <p>{{ __('If you prefer to explore rather than search, use the Browse feature to navigate through our document collections organized by categories.') }}</p>

                    <div class="row">
                        <div class="col-md-6">
                            <h5>{{ __('How to Browse') }}</h5>
                            <ol>
                                <li>{{ __('Click "Browse" in the main navigation') }}</li>
                                <li>{{ __('Select a category that interests you') }}</li>
                                <li>{{ __('Explore subcategories if available') }}</li>
                                <li>{{ __('View the documents in that collection') }}</li>
                            </ol>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-primary">
                                <h6><i class="fas fa-info-circle me-2"></i>{{ __('Tip') }}</h6>
                                <p class="mb-0">{{ __('Browsing is perfect when you want to discover documents you might not have found through searching.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Understanding Results -->
            <section id="understanding-results" class="opac-card mb-4">
                <div class="opac-card-header">
                    <i class="fas fa-list-ul me-2"></i>{{ __('Understanding Search Results') }}
                </div>
                <div class="card-body">
                    <p>{{ __('Each search result shows key information about the document:') }}</p>

                    <div class="row">
                        <div class="col-md-6">
                            <h5>{{ __('Document Information') }}</h5>
                            <ul>
                                <li><strong>{{ __('Title') }}</strong> - {{ __('The document name') }}</li>
                                <li><strong>{{ __('Category') }}</strong> - {{ __('Collection it belongs to') }}</li>
                                <li><strong>{{ __('Date') }}</strong> - {{ __('Document date') }}</li>
                                <li><strong>{{ __('Authors') }}</strong> - {{ __('Document creators') }}</li>
                                <li><strong>{{ __('Description') }}</strong> - {{ __('Brief content summary') }}</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5>{{ __('Actions Available') }}</h5>
                            <ul>
                                <li><strong>{{ __('View Details') }}</strong> - {{ __('See complete information') }}</li>
                                <li><strong>{{ __('Browse Category') }}</strong> - {{ __('Explore related documents') }}</li>
                                <li><strong>{{ __('Download') }}</strong> - {{ __('Access files (if permitted)') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Advanced Search -->
            <section id="advanced-search" class="opac-card mb-4">
                <div class="opac-card-header">
                    <i class="fas fa-cogs me-2"></i>{{ __('Advanced Search Features') }}
                </div>
                <div class="card-body">
                    <p>{{ __('Use the advanced search page to create more precise queries using multiple criteria:') }}</p>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <i class="fas fa-folder me-2"></i>{{ __('Category Filter') }}
                                </div>
                                <div class="card-body">
                                    <p class="card-text small">{{ __('Limit results to specific collections or document categories.') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <i class="fas fa-calendar me-2"></i>{{ __('Date Range') }}
                                </div>
                                <div class="card-body">
                                    <p class="card-text small">{{ __('Find documents from specific time periods using date filters.') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <i class="fas fa-user me-2"></i>{{ __('Author Filter') }}
                                </div>
                                <div class="card-body">
                                    <p class="card-text small">{{ __('Search for documents by specific authors or creators.') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Contact -->
            <section id="contact" class="opac-card mb-4">
                <div class="opac-card-header">
                    <i class="fas fa-envelope me-2"></i>{{ __('Contact Us') }}
                </div>
                <div class="card-body">
                    <p>{{ __('Need help or have questions about our collections? Contact our archive team:') }}</p>

                    <div class="row">
                        <div class="col-md-6">
                            <h5>{{ __('General Information') }}</h5>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-envelope me-2"></i> info@example.org</li>
                                <li><i class="fas fa-phone me-2"></i> +1 234 567 8900</li>
                                <li><i class="fas fa-map-marker-alt me-2"></i> 123 Archive Street</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5>{{ __('Research Assistance') }}</h5>
                            <p>{{ __('Our archivists can help you find specific documents or provide research guidance. Please contact us with details about your research needs.') }}</p>
                        </div>
                    </div>

                    <div class="alert alert-warning mt-3">
                        <h6><i class="fas fa-clock me-2"></i>{{ __('Office Hours') }}</h6>
                        <p class="mb-0">{{ __('Monday - Friday: 9:00 AM - 5:00 PM') }}<br>{{ __('Response time: Usually within 24 hours') }}</p>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scrolling for navigation links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Highlight active section in navigation
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-link[href^="#"]');

    function highlightNavigation() {
        let current = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            if (window.pageYOffset >= sectionTop - 200) {
                current = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === '#' + current) {
                link.classList.add('active');
            }
        });
    }

    window.addEventListener('scroll', highlightNavigation);
});
</script>
@endpush
