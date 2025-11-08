<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Template;

class OpacTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Template Modern Academic
        Template::firstOrCreate(
            ['slug' => 'modern-academic'],
            [
            'name' => 'Modern Academic',
            'description' => 'Template moderne et élégant pour les institutions académiques',
            'type' => 'opac',
            'status' => 'active',
            'theme' => 'modern',
            'is_default' => true,
            'layout' => $this->getModernAcademicLayout(),
            'custom_css' => $this->getModernAcademicCss(),
            'custom_js' => $this->getModernAcademicJs(),
            'variables' => [
                'primary_color' => '#2563eb',
                'secondary_color' => '#64748b',
                'accent_color' => '#10b981',
                'background_color' => '#ffffff',
                'text_color' => '#1e293b',
                'header_height' => '80px',
                'sidebar_width' => '280px',
                'border_radius' => '8px',
                'font_family' => 'Inter, sans-serif',
                'font_size_base' => '16px'
            ],
            'components' => ['search', 'navigation', 'results', 'filters', 'pagination'],
            'meta' => [
                'author' => 'OPAC System',
                'version' => '1.0.0',
                'features' => ['responsive', 'accessibility', 'dark-mode'],
                'compatibility' => ['modern-browsers']
            ],
            'created_by' => 'system'
        ]);

        // Template Classic Library
        Template::firstOrCreate(
            ['slug' => 'classic-library'],
            [
            'name' => 'Classic Library',
            'description' => 'Template classique inspiré des bibliothèques traditionnelles',
            'type' => 'opac',
            'status' => 'active',
            'theme' => 'classic',
            'layout' => $this->getClassicLibraryLayout(),
            'custom_css' => $this->getClassicLibraryCss(),
            'custom_js' => $this->getClassicLibraryJs(),
            'variables' => [
                'primary_color' => '#8b4513',
                'secondary_color' => '#d2b48c',
                'accent_color' => '#cd853f',
                'background_color' => '#faf7f2',
                'text_color' => '#2c1810',
                'header_height' => '100px',
                'sidebar_width' => '300px',
                'border_radius' => '4px',
                'font_family' => 'Crimson Text, serif',
                'font_size_base' => '18px'
            ],
            'components' => ['search', 'navigation', 'results', 'filters'],
            'meta' => [
                'author' => 'OPAC System',
                'version' => '1.0.0',
                'features' => ['responsive', 'classic-design'],
                'compatibility' => ['all-browsers']
            ],
            'created_by' => 'system'
        ]);

        // Template Corporate Clean
        Template::firstOrCreate(
            ['slug' => 'corporate-clean'],
            [
            'name' => 'Corporate Clean',
            'description' => 'Template épuré pour les environnements corporatifs',
            'type' => 'opac',
            'status' => 'active',
            'theme' => 'corporate',
            'layout' => $this->getCorporateCleanLayout(),
            'custom_css' => $this->getCorporateCleanCss(),
            'custom_js' => $this->getCorporateCleanJs(),
            'variables' => [
                'primary_color' => '#374151',
                'secondary_color' => '#9ca3af',
                'accent_color' => '#3b82f6',
                'background_color' => '#f9fafb',
                'text_color' => '#111827',
                'header_height' => '70px',
                'sidebar_width' => '260px',
                'border_radius' => '6px',
                'font_family' => 'system-ui, sans-serif',
                'font_size_base' => '14px'
            ],
            'components' => ['search', 'navigation', 'results', 'filters', 'breadcrumb'],
            'meta' => [
                'author' => 'OPAC System',
                'version' => '1.0.0',
                'features' => ['responsive', 'minimal-design', 'high-contrast'],
                'compatibility' => ['modern-browsers', 'enterprise']
            ],
            'created_by' => 'system'
        ]);
    }

    private function getModernAcademicLayout(): string
    {
        return '
<!DOCTYPE html>
<html lang="{{ $locale ?? \'fr\' }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page_title ?? \'OPAC - Catalogue en ligne\' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="h-full bg-gray-50 font-sans antialiased">
    <div id="app" class="min-h-full">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-20">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <img class="h-12 w-auto" src="{{ $logo_url ?? \'/images/logo.svg\' }}" alt="{{ $site_name ?? \'OPAC\' }}">
                        </div>
                        <div class="ml-6">
                            <h1 class="text-2xl font-semibold text-gray-900">{{ $site_name ?? \'Catalogue en ligne\' }}</h1>
                        </div>
                    </div>
                    <nav class="hidden md:flex space-x-8">
                        <a href="#" class="text-gray-600 hover:text-blue-600 px-3 py-2 text-sm font-medium">Accueil</a>
                        <a href="#" class="text-gray-600 hover:text-blue-600 px-3 py-2 text-sm font-medium">Recherche avancée</a>
                        <a href="#" class="text-gray-600 hover:text-blue-600 px-3 py-2 text-sm font-medium">Mon compte</a>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <!-- Sidebar -->
                <aside class="lg:col-span-1">
                    <x-opac-search-filters />
                </aside>

                <!-- Content Area -->
                <div class="lg:col-span-3 space-y-6">
                    <!-- Search Bar -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <x-opac-search-form />
                    </div>

                    <!-- Results -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <x-opac-search-results />
                    </div>

                    <!-- Pagination -->
                    <div class="flex justify-center">
                        <x-opac-pagination />
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-gray-800 text-white mt-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div>
                        <h3 class="text-lg font-semibold mb-4">{{ $site_name ?? \'OPAC\' }}</h3>
                        <p class="text-gray-300">{{ $site_description ?? \'Catalogue en ligne de la bibliothèque\' }}</p>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Liens rapides</h3>
                        <ul class="space-y-2 text-gray-300">
                            <li><a href="#" class="hover:text-white">Aide</a></li>
                            <li><a href="#" class="hover:text-white">Contact</a></li>
                            <li><a href="#" class="hover:text-white">Mentions légales</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Contact</h3>
                        <p class="text-gray-300">{{ $contact_info ?? \'Contactez-nous pour plus d\'informations\' }}</p>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>';
    }

    private function getModernAcademicCss(): string
    {
        return '
/* Modern Academic Theme Styles */
:root {
    --primary-color: var(--template-primary-color, #2563eb);
    --secondary-color: var(--template-secondary-color, #64748b);
    --accent-color: var(--template-accent-color, #10b981);
    --background-color: var(--template-background-color, #ffffff);
    --text-color: var(--template-text-color, #1e293b);
    --header-height: var(--template-header-height, 80px);
    --sidebar-width: var(--template-sidebar-width, 280px);
    --border-radius: var(--template-border-radius, 8px);
    --font-family: var(--template-font-family, Inter, sans-serif);
}

/* Custom animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.search-result {
    animation: fadeIn 0.3s ease-out;
}

.search-result:nth-child(even) {
    animation-delay: 0.1s;
}

.search-result:nth-child(odd) {
    animation-delay: 0.2s;
}

/* Interactive elements */
.filter-checkbox:checked + label {
    background-color: var(--accent-color);
    color: white;
}

.search-input:focus {
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    border-color: var(--primary-color);
}

/* Responsive improvements */
@media (max-width: 768px) {
    .header-nav {
        display: none;
    }

    .mobile-menu-button {
        display: block;
    }
}';
    }

    private function getModernAcademicJs(): string
    {
        return '
// Modern Academic Theme JavaScript
document.addEventListener("DOMContentLoaded", function() {
    // Mobile menu toggle
    const mobileMenuButton = document.querySelector(".mobile-menu-button");
    const mobileMenu = document.querySelector(".mobile-menu");

    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener("click", function() {
            mobileMenu.classList.toggle("hidden");
        });
    }

    // Smooth scrolling for anchor links
    document.querySelectorAll(\'a[href^="#"]\').forEach(anchor => {
        anchor.addEventListener("click", function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute("href"));
            if (target) {
                target.scrollIntoView({ behavior: "smooth" });
            }
        });
    });

    // Search form enhancements
    const searchInput = document.querySelector(".search-input");
    if (searchInput) {
        searchInput.addEventListener("input", function() {
            // Auto-suggest functionality could be added here
            console.log("Search query:", this.value);
        });
    }

    // Filter animations
    document.querySelectorAll(".filter-toggle").forEach(toggle => {
        toggle.addEventListener("click", function() {
            const target = document.querySelector(this.getAttribute("data-target"));
            if (target) {
                target.classList.toggle("hidden");
            }
        });
    });
});';
    }

    private function getClassicLibraryLayout(): string
    {
        return '
<!DOCTYPE html>
<html lang="{{ $locale ?? \'fr\' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page_title ?? \'Bibliothèque - Catalogue\' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Text:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body class="classic-theme">
    <div class="page-wrapper">
        <header class="classic-header">
            <div class="container">
                <div class="header-content">
                    <div class="library-emblem">
                        <img src="{{ $logo_url ?? \'/images/library-seal.png\' }}" alt="Sceau de la bibliothèque">
                    </div>
                    <div class="header-text">
                        <h1 class="library-name">{{ $site_name ?? \'Bibliothèque Municipale\' }}</h1>
                        <p class="library-motto">{{ $motto ?? \'Savoir et Culture pour tous\' }}</p>
                    </div>
                </div>
                <nav class="classic-nav">
                    <ul>
                        <li><a href="#">Catalogue</a></li>
                        <li><a href="#">Collections</a></li>
                        <li><a href="#">Services</a></li>
                        <li><a href="#">Mon compte</a></li>
                    </ul>
                </nav>
            </div>
        </header>

        <main class="main-content">
            <div class="container">
                <div class="content-grid">
                    <aside class="sidebar">
                        <div class="search-panel">
                            <x-opac-search-form />
                        </div>
                        <div class="filters-panel">
                            <x-opac-search-filters />
                        </div>
                    </aside>

                    <section class="results-area">
                        <x-opac-search-results />
                    </section>
                </div>
            </div>
        </main>

        <footer class="classic-footer">
            <div class="container">
                <p>&copy; {{ date(\'Y\') }} {{ $site_name ?? \'Bibliothèque\' }}. Tous droits réservés.</p>
            </div>
        </footer>
    </div>
</body>
</html>';
    }

    private function getClassicLibraryCss(): string
    {
        return '
/* Classic Library Theme */
.classic-theme {
    font-family: "Crimson Text", serif;
    background: #faf7f2;
    color: #2c1810;
    line-height: 1.7;
}

.classic-header {
    background: linear-gradient(135deg, #8b4513, #a0522d);
    color: #faf7f2;
    padding: 2rem 0;
    border-bottom: 4px solid #cd853f;
}

.header-content {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
}

.library-emblem img {
    height: 80px;
    margin-right: 2rem;
}

.library-name {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 0;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.library-motto {
    font-style: italic;
    font-size: 1.1rem;
    margin: 0.5rem 0 0 0;
    opacity: 0.9;
}

.classic-nav ul {
    list-style: none;
    display: flex;
    gap: 2rem;
    margin: 0;
    padding: 0;
}

.classic-nav a {
    color: #faf7f2;
    text-decoration: none;
    font-weight: 600;
    font-size: 1.1rem;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    transition: background-color 0.3s;
}

.classic-nav a:hover {
    background-color: rgba(205, 133, 63, 0.3);
}

.content-grid {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 2rem;
    margin: 2rem 0;
}

.search-panel, .filters-panel {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(139, 69, 19, 0.1);
    border: 1px solid #d2b48c;
    margin-bottom: 1.5rem;
}

.classic-footer {
    background: #2c1810;
    color: #d2b48c;
    text-align: center;
    padding: 1.5rem 0;
    margin-top: 3rem;
}';
    }

    private function getClassicLibraryJs(): string
    {
        return '
// Classic Library Theme JavaScript
document.addEventListener("DOMContentLoaded", function() {
    // Add vintage book opening animation
    const searchResults = document.querySelectorAll(".search-result");
    searchResults.forEach((result, index) => {
        result.style.animationDelay = `${index * 0.1}s`;
        result.classList.add("book-reveal");
    });

    // Classic search enhancements
    const searchForm = document.querySelector(".search-form");
    if (searchForm) {
        searchForm.addEventListener("submit", function(e) {
            const loader = document.createElement("div");
            loader.className = "classic-loader";
            loader.innerHTML = "Recherche en cours...";
            this.appendChild(loader);
        });
    }
});';
    }

    private function getCorporateCleanLayout(): string
    {
        return '
<!DOCTYPE html>
<html lang="{{ $locale ?? \'fr\' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page_title ?? \'Corporate Library - Search\' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body class="corporate-theme">
    <div class="app-container">
        <header class="top-header">
            <div class="header-container">
                <div class="brand-section">
                    <img src="{{ $logo_url ?? \'/images/corp-logo.svg\' }}" alt="{{ $company_name ?? \'Corporation\' }}" class="brand-logo">
                    <span class="brand-text">{{ $site_name ?? \'Knowledge Base\' }}</span>
                </div>
                <nav class="primary-nav">
                    <a href="#" class="nav-link">Dashboard</a>
                    <a href="#" class="nav-link">Search</a>
                    <a href="#" class="nav-link">Reports</a>
                    <a href="#" class="nav-link active">Library</a>
                </nav>
                <div class="user-section">
                    <button class="user-menu">{{ $user_name ?? \'User\' }}</button>
                </div>
            </div>
        </header>

        <div class="layout-container">
            <aside class="filter-sidebar">
                <div class="sidebar-header">
                    <h3>Filters</h3>
                    <button class="clear-filters">Clear all</button>
                </div>
                <div class="sidebar-content">
                    <x-opac-search-filters />
                </div>
            </aside>

            <main class="main-area">
                <div class="search-section">
                    <x-opac-search-form />
                </div>

                <div class="results-section">
                    <div class="results-header">
                        <h2>Search Results</h2>
                        <div class="results-actions">
                            <button class="sort-btn">Sort by relevance</button>
                            <button class="view-toggle">Grid view</button>
                        </div>
                    </div>
                    <x-opac-search-results />
                </div>
            </main>
        </div>

        <footer class="app-footer">
            <div class="footer-content">
                <p>&copy; {{ date(\'Y\') }} {{ $company_name ?? \'Corporation\' }}. All rights reserved.</p>
                <div class="footer-links">
                    <a href="#">Privacy</a>
                    <a href="#">Terms</a>
                    <a href="#">Support</a>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>';
    }

    private function getCorporateCleanCss(): string
    {
        return '
/* Corporate Clean Theme */
.corporate-theme {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    background: #f9fafb;
    color: #111827;
    font-size: 14px;
    line-height: 1.5;
}

.app-container {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

.top-header {
    background: white;
    border-bottom: 1px solid #e5e7eb;
    padding: 0 1rem;
    height: 64px;
    display: flex;
    align-items: center;
}

.header-container {
    width: 100%;
    max-width: 1400px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.brand-section {
    display: flex;
    align-items: center;
    gap: 12px;
}

.brand-logo {
    height: 32px;
    width: auto;
}

.brand-text {
    font-weight: 600;
    font-size: 18px;
    color: #374151;
}

.primary-nav {
    display: flex;
    gap: 2rem;
}

.nav-link {
    color: #6b7280;
    text-decoration: none;
    font-weight: 500;
    padding: 0.5rem 0;
    border-bottom: 2px solid transparent;
    transition: all 0.2s;
}

.nav-link:hover,
.nav-link.active {
    color: #3b82f6;
    border-bottom-color: #3b82f6;
}

.layout-container {
    flex: 1;
    display: grid;
    grid-template-columns: 280px 1fr;
    max-width: 1400px;
    margin: 0 auto;
    width: 100%;
}

.filter-sidebar {
    background: white;
    border-right: 1px solid #e5e7eb;
    padding: 1.5rem;
}

.sidebar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.sidebar-header h3 {
    font-size: 16px;
    font-weight: 600;
    margin: 0;
}

.clear-filters {
    background: none;
    border: 1px solid #d1d5db;
    color: #6b7280;
    font-size: 12px;
    padding: 4px 8px;
    border-radius: 4px;
    cursor: pointer;
}

.main-area {
    padding: 1.5rem;
    background: #f9fafb;
}

.search-section {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border: 1px solid #e5e7eb;
}

.results-section {
    background: white;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    overflow: hidden;
}

.results-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.results-header h2 {
    font-size: 18px;
    font-weight: 600;
    margin: 0;
}

.results-actions {
    display: flex;
    gap: 12px;
}

.sort-btn,
.view-toggle {
    background: #f3f4f6;
    border: 1px solid #d1d5db;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 14px;
    cursor: pointer;
}

.app-footer {
    background: #374151;
    color: #d1d5db;
    padding: 1rem;
    margin-top: auto;
}

.footer-content {
    max-width: 1400px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.footer-links {
    display: flex;
    gap: 1.5rem;
}

.footer-links a {
    color: #9ca3af;
    text-decoration: none;
    font-size: 13px;
}';
    }

    private function getCorporateCleanJs(): string
    {
        return '
// Corporate Clean Theme JavaScript
document.addEventListener("DOMContentLoaded", function() {
    // User menu dropdown
    const userMenu = document.querySelector(".user-menu");
    if (userMenu) {
        userMenu.addEventListener("click", function() {
            // Toggle user dropdown (would need HTML structure)
            console.log("User menu clicked");
        });
    }

    // Clear filters functionality
    const clearFilters = document.querySelector(".clear-filters");
    if (clearFilters) {
        clearFilters.addEventListener("click", function() {
            document.querySelectorAll(".filter-input").forEach(input => {
                if (input.type === "checkbox" || input.type === "radio") {
                    input.checked = false;
                } else {
                    input.value = "";
                }
            });
        });
    }

    // View toggle functionality
    const viewToggle = document.querySelector(".view-toggle");
    if (viewToggle) {
        viewToggle.addEventListener("click", function() {
            const resultsContainer = document.querySelector(".search-results");
            if (resultsContainer) {
                resultsContainer.classList.toggle("grid-view");
                this.textContent = resultsContainer.classList.contains("grid-view")
                    ? "List view" : "Grid view";
            }
        });
    }

    // Sort functionality
    const sortBtn = document.querySelector(".sort-btn");
    if (sortBtn) {
        sortBtn.addEventListener("click", function() {
            // Show sort dropdown (would need implementation)
            console.log("Sort clicked");
        });
    }
});';
    }
}
