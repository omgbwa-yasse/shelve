<?php

/*
|--------------------------------------------------------------------------
| Configuration des Templates OPAC
|--------------------------------------------------------------------------
|
| Configuration des templates d'exemple utilisant le nouveau système
| de composants et services OPAC. Ces templates peuvent être utilisés
| comme base ou références pour créer de nouveaux designs.
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Templates disponibles
    |--------------------------------------------------------------------------
    |
    | Liste des templates disponibles avec leurs configurations complètes.
    | Chaque template définit son layout, ses variables de thème et ses
    | configurations de composants.
    |
    */

    'templates' => [

        'modern-academic' => [
            'name' => 'Modern Academic',
            'description' => 'Template moderne pour institutions académiques avec design gradient et animations',
            'version' => '1.0.0',
            'category' => 'academic',
            'preview_image' => '/images/templates/modern-academic-preview.jpg',
            'layout_file' => 'opac.templates.modern-academic',

            'variables' => [
                'primary_color' => '#1e3a8a',
                'secondary_color' => '#3b82f6',
                'accent_color' => '#f59e0b',
                'text_color' => '#1f2937',
                'background_color' => '#ffffff',
                'font_family' => 'Inter, system-ui, sans-serif',
                'border_radius' => '0.75rem',
                'box_shadow' => '0 4px 6px rgba(0, 0, 0, 0.1)',
                'gradient_direction' => '135deg',
                'animation_duration' => '0.6s',
                'hero_height' => '400px',
                'custom_css' => '
                    .hero-section {
                        background-attachment: fixed;
                    }
                    .card-hover:hover {
                        transform: translateY(-8px);
                    }
                '
            ],

            'components' => [
                'search-bar' => [
                    'showFilters' => true,
                    'placeholder' => 'Rechercher dans nos collections académiques...',
                    'showAdvancedLink' => true,
                    'size' => 'large',
                    'variant' => 'hero'
                ],
                'document-card' => [
                    'showMetadata' => true,
                    'showBookmark' => true,
                    'imageHeight' => '200px',
                    'variant' => 'academic'
                ],
                'navigation' => [
                    'showUserMenu' => true,
                    'showLanguageSelector' => false,
                    'variant' => 'modern'
                ],
                'pagination' => [
                    'showInfo' => true,
                    'showFirstLast' => true,
                    'variant' => 'academic'
                ]
            ],

            'features' => [
                'statistics' => true,
                'quick_actions' => true,
                'bookmarks' => true,
                'social_links' => false,
                'breadcrumbs' => true,
                'animations' => true
            ],

            'ui_settings' => [
                'show_navigation' => true,
                'show_breadcrumbs' => true,
                'show_footer' => true,
                'show_banner' => false,
                'header_search' => false,
                'items_per_page' => 12,
                'grid_columns' => 3
            ]
        ],

        'classic-library' => [
            'name' => 'Classic Library',
            'description' => 'Design traditionnel et élégant pour bibliothèques classiques',
            'version' => '1.0.0',
            'category' => 'traditional',
            'preview_image' => '/images/templates/classic-library-preview.jpg',
            'layout_file' => 'opac.templates.classic-library',

            'variables' => [
                'primary_color' => '#8b4513',
                'secondary_color' => '#cd853f',
                'accent_color' => '#daa520',
                'text_color' => '#2c1810',
                'background_color' => '#faf8f5',
                'font_family' => 'Georgia, serif',
                'border_radius' => '0.25rem',
                'box_shadow' => '0 2px 4px rgba(0, 0, 0, 0.1)',
                'decorative_elements' => true,
                'serif_headings' => true,
                'custom_css' => '
                    .classic-ornament {
                        font-family: "Times New Roman", serif;
                    }
                    .traditional-border {
                        border: 2px solid var(--accent-color);
                    }
                '
            ],

            'components' => [
                'search-bar' => [
                    'showFilters' => false,
                    'placeholder' => 'Titre, auteur, sujet...',
                    'showAdvancedLink' => true,
                    'size' => 'medium',
                    'variant' => 'classic'
                ],
                'document-card' => [
                    'showMetadata' => true,
                    'showBookmark' => false,
                    'imageHeight' => '180px',
                    'variant' => 'classic'
                ],
                'navigation' => [
                    'showUserMenu' => true,
                    'showLanguageSelector' => false,
                    'variant' => 'traditional'
                ]
            ],

            'features' => [
                'statistics' => false,
                'quick_actions' => false,
                'bookmarks' => false,
                'social_links' => false,
                'breadcrumbs' => true,
                'categories_sidebar' => true,
                'news_sidebar' => true,
                'hours_display' => true
            ],

            'ui_settings' => [
                'show_navigation' => true,
                'show_breadcrumbs' => true,
                'show_footer' => true,
                'show_banner' => false,
                'header_search' => false,
                'items_per_page' => 8,
                'layout_type' => 'sidebar',
                'sidebar_position' => 'both'
            ]
        ],

        'corporate-clean' => [
            'name' => 'Corporate Clean',
            'description' => 'Design moderne et professionnel pour organisations corporatives',
            'version' => '1.0.0',
            'category' => 'corporate',
            'preview_image' => '/images/templates/corporate-clean-preview.jpg',
            'layout_file' => 'opac.templates.corporate-clean',

            'variables' => [
                'primary_color' => '#0f172a',
                'secondary_color' => '#334155',
                'accent_color' => '#0ea5e9',
                'text_color' => '#1e293b',
                'background_color' => '#f8fafc',
                'font_family' => 'system-ui, -apple-system, sans-serif',
                'border_radius' => '0.5rem',
                'box_shadow' => '0 1px 3px rgba(0, 0, 0, 0.1)',
                'corporate_branding' => true,
                'professional_layout' => true,
                'custom_css' => '
                    .corporate-card {
                        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                    }
                    .dashboard-grid {
                        gap: 1.5rem;
                    }
                '
            ],

            'components' => [
                'search-bar' => [
                    'showFilters' => true,
                    'placeholder' => 'Rechercher documents, procédures, guides...',
                    'showAdvancedLink' => true,
                    'size' => 'large',
                    'variant' => 'corporate'
                ],
                'document-card' => [
                    'showMetadata' => true,
                    'showBookmark' => true,
                    'imageHeight' => '120px',
                    'variant' => 'corporate-compact'
                ],
                'navigation' => [
                    'showUserMenu' => true,
                    'showLanguageSelector' => false,
                    'variant' => 'corporate'
                ]
            ],

            'features' => [
                'statistics' => true,
                'quick_actions' => true,
                'bookmarks' => true,
                'social_links' => false,
                'breadcrumbs' => true,
                'dashboard_cards' => true,
                'user_accounts' => true,
                'support_integration' => true
            ],

            'ui_settings' => [
                'show_navigation' => true,
                'show_breadcrumbs' => true,
                'show_footer' => true,
                'show_banner' => true,
                'header_search' => false,
                'items_per_page' => 10,
                'layout_type' => 'dashboard',
                'show_stats_bar' => true
            ]
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuration par défaut
    |--------------------------------------------------------------------------
    |
    | Template et configuration utilisés par défaut pour les nouveaux
    | utilisateurs ou les configurations sans template spécifique.
    |
    */

    'default_template' => 'modern-academic',

    'default_config' => [
        'branding' => [
            'site_name' => config('app.name', 'OPAC Library'),
            'tagline' => 'Catalogue en ligne',
            'logo_url' => '/images/logo.png',
            'favicon_url' => '/images/favicon.ico',
            'copyright' => date('Y') . ' ' . config('app.name')
        ],

        'contact' => [
            'email' => 'contact@library.org',
            'phone' => '+33 1 23 45 67 89',
            'address' => '123 Rue de la Bibliothèque, 75001 Paris'
        ],

        'features' => [
            'user_accounts' => true,
            'bookmarks' => true,
            'social_links' => false,
            'statistics' => true,
            'quick_actions' => true,
            'digital_collections' => false,
            'analytics' => false
        ],

        'ui' => [
            'show_navigation' => true,
            'show_breadcrumbs' => true,
            'show_footer' => true,
            'show_banner' => false,
            'show_language_selector' => false,
            'header_search' => false,
            'items_per_page' => 12,
            'pagination_size' => 5
        ],

        'performance' => [
            'cache_duration' => 3600,
            'lazy_loading' => true,
            'image_optimization' => true,
            'css_minification' => true,
            'js_minification' => true
        ],

        'security' => [
            'csrf_protection' => true,
            'xss_protection' => true,
            'rate_limiting' => true,
            'secure_headers' => true
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Catégories de templates
    |--------------------------------------------------------------------------
    |
    | Classification des templates pour faciliter la navigation et
    | la sélection dans l'interface d'administration.
    |
    */

    'categories' => [
        'academic' => [
            'name' => 'Académique',
            'description' => 'Templates pour universités et institutions académiques',
            'icon' => 'fas fa-graduation-cap'
        ],
        'traditional' => [
            'name' => 'Traditionnel',
            'description' => 'Designs classiques pour bibliothèques traditionnelles',
            'icon' => 'fas fa-book'
        ],
        'corporate' => [
            'name' => 'Entreprise',
            'description' => 'Templates professionnels pour organisations',
            'icon' => 'fas fa-building'
        ],
        'modern' => [
            'name' => 'Moderne',
            'description' => 'Designs contemporains et innovants',
            'icon' => 'fas fa-rocket'
        ],
        'minimalist' => [
            'name' => 'Minimaliste',
            'description' => 'Templates épurés et fonctionnels',
            'icon' => 'fas fa-circle'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Assets par défaut
    |--------------------------------------------------------------------------
    |
    | CSS et JavaScript inclus par défaut dans tous les templates.
    | Ces assets peuvent être surchargés au niveau du template.
    |
    */

    'default_assets' => [
        'css' => [
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'
        ],
        'js' => [
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuration de compilation
    |--------------------------------------------------------------------------
    |
    | Paramètres pour la compilation et l'optimisation des templates.
    |
    */

    'compilation' => [
        'cache_templates' => true,
        'minify_html' => true,
        'optimize_css' => true,
        'combine_js' => true,
        'generate_sourcemaps' => false,
        'cache_duration' => 86400 // 24 heures
    ]
];
