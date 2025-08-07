@extends('layouts.app')

@section('title', 'Documentation MCP')

@push('styles')
<style>
    .doc-navigation {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 1.5rem;
        position: sticky;
        top: 20px;
        height: fit-content;
    }

    .doc-nav-item {
        display: block;
        padding: 0.5rem 1rem;
        color: #6c757d;
        text-decoration: none;
        border-radius: 6px;
        transition: all 0.3s ease;
        margin-bottom: 0.25rem;
    }

    .doc-nav-item:hover {
        background: #e9ecef;
        color: #495057;
        text-decoration: none;
    }

    .doc-nav-item.active {
        background: #007bff;
        color: white;
    }

    .doc-content {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .doc-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
    }

    .doc-body {
        padding: 2rem;
    }

    .doc-section {
        margin-bottom: 3rem;
        scroll-margin-top: 80px;
    }

    .doc-section h2 {
        border-bottom: 2px solid #e9ecef;
        padding-bottom: 0.5rem;
        margin-bottom: 1.5rem;
    }

    .doc-section h3 {
        color: #495057;
        margin-top: 2rem;
        margin-bottom: 1rem;
    }

    .api-endpoint {
        background: #f8f9fa;
        border-left: 4px solid #007bff;
        padding: 1rem;
        margin: 1rem 0;
        border-radius: 0 8px 8px 0;
    }

    .method-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-weight: 600;
        margin-right: 0.5rem;
    }

    .method-get { background: #28a745; color: white; }
    .method-post { background: #007bff; color: white; }
    .method-put { background: #ffc107; color: black; }
    .method-delete { background: #dc3545; color: white; }

    .code-block {
        background: #1e1e1e;
        color: #ffffff;
        border-radius: 8px;
        padding: 1.5rem;
        font-family: 'Courier New', monospace;
        font-size: 0.875rem;
        overflow-x: auto;
        position: relative;
    }

    .copy-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.2);
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        cursor: pointer;
    }

    .copy-btn:hover {
        background: rgba(255,255,255,0.2);
    }

    .feature-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin: 2rem 0;
    }

    .feature-card {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 1.5rem;
        transition: transform 0.3s ease;
    }

    .feature-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .feature-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        font-size: 1.5rem;
    }

    .icon-title { background: linear-gradient(45deg, #007bff, #0056b3); color: white; }
    .icon-thesaurus { background: linear-gradient(45deg, #28a745, #1e7e34); color: white; }
    .icon-summary { background: linear-gradient(45deg, #17a2b8, #117a8b); color: white; }

    .alert-tip {
        border: none;
        background: linear-gradient(45deg, #e3f2fd, #f0f9ff);
        border-left: 4px solid #2196f3;
        color: #0d47a1;
    }

    .table-responsive {
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .table th {
        background: #f8f9fa;
        border: none;
        font-weight: 600;
        color: #495057;
    }

    .search-box {
        position: relative;
        margin-bottom: 2rem;
    }

    .search-box input {
        padding-left: 3rem;
    }

    .search-box i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
    }

    .toc {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }

    .toc ul {
        list-style: none;
        padding-left: 1rem;
    }

    .toc a {
        color: #6c757d;
        text-decoration: none;
    }

    .toc a:hover {
        color: #007bff;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 fw-bold">
                <i class="bi bi-book me-3 text-primary"></i>
                Documentation MCP
            </h1>
            <p class="text-muted mb-0">Guide complet d'utilisation du Model Context Protocol</p>
        </div>
        <div>
            <a href="{{ route('admin.mcp.dashboard') }}" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left me-1"></i>Retour Dashboard
            </a>
            <button class="btn btn-primary" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>Imprimer
            </button>
        </div>
    </div>

    <div class="row">
        <!-- Navigation de la documentation -->
        <div class="col-md-3">
            <div class="doc-navigation">
                <h5 class="mb-3">Table des Matières</h5>
                <nav>
                    <a href="#introduction" class="doc-nav-item active" data-section="introduction">
                        <i class="bi bi-play-circle me-2"></i>Introduction
                    </a>
                    <a href="#features" class="doc-nav-item" data-section="features">
                        <i class="bi bi-gear me-2"></i>Fonctionnalités
                    </a>
                    <a href="#installation" class="doc-nav-item" data-section="installation">
                        <i class="bi bi-download me-2"></i>Installation
                    </a>
                    <a href="#configuration" class="doc-nav-item" data-section="configuration">
                        <i class="bi bi-sliders me-2"></i>Configuration
                    </a>
                    <a href="#usage" class="doc-nav-item" data-section="usage">
                        <i class="bi bi-terminal me-2"></i>Utilisation
                    </a>
                    <a href="#api" class="doc-nav-item" data-section="api">
                        <i class="bi bi-cloud me-2"></i>API REST
                    </a>
                    <a href="#commands" class="doc-nav-item" data-section="commands">
                        <i class="bi bi-code-square me-2"></i>Commandes Artisan
                    </a>
                    <a href="#troubleshooting" class="doc-nav-item" data-section="troubleshooting">
                        <i class="bi bi-tools me-2"></i>Dépannage
                    </a>
                    <a href="#examples" class="doc-nav-item" data-section="examples">
                        <i class="bi bi-lightbulb me-2"></i>Exemples
                    </a>
                </nav>
            </div>
        </div>

        <!-- Contenu de la documentation -->
        <div class="col-md-9">
            <div class="doc-content">
                <div class="doc-header">
                    <h2 class="mb-2">Model Context Protocol (MCP)</h2>
                    <p class="mb-0 opacity-90">Intelligence Artificielle pour l'Archivage ISAD(G)</p>
                </div>

                <div class="doc-body">
                    <!-- Barre de recherche -->
                    <div class="search-box">
                        <i class="bi bi-search"></i>
                        <input type="text" class="form-control" placeholder="Rechercher dans la documentation..." id="searchInput">
                    </div>

                    <!-- Introduction -->
                    <section id="introduction" class="doc-section">
                        <h2><i class="bi bi-play-circle me-2"></i>Introduction</h2>
                        
                        <p class="lead">
                            Le module MCP (Model Context Protocol) intègre l'intelligence artificielle dans votre système d'archivage 
                            pour automatiser et améliorer la gestion des records selon les standards ISAD(G).
                        </p>

                        <div class="alert alert-tip">
                            <i class="bi bi-lightbulb me-2"></i>
                            <strong>Nouveauté :</strong> Cette documentation est interactive. Utilisez la barre de recherche 
                            pour trouver rapidement l'information recherchée.
                        </div>

                        <h3>Qu'est-ce que le MCP ?</h3>
                        <p>
                            Le Model Context Protocol est un ensemble de fonctionnalités basées sur l'IA qui permettent :
                        </p>
                        <ul>
                            <li><strong>Reformulation automatique des titres</strong> selon les règles ISAD(G)</li>
                            <li><strong>Indexation thématique</strong> via thésaurus automatisé</li>
                            <li><strong>Génération de résumés</strong> conformes aux standards archivistiques</li>
                        </ul>

                        <h3>Architecture Technique</h3>
                        <p>
                            Le système s'appuie sur <strong>Ollama</strong> pour l'exécution locale de modèles d'IA, 
                            garantissant la confidentialité de vos données tout en offrant des performances optimales.
                        </p>
                    </section>

                    <!-- Fonctionnalités -->
                    <section id="features" class="doc-section">
                        <h2><i class="bi bi-gear me-2"></i>Fonctionnalités</h2>

                        <div class="feature-grid">
                            <div class="feature-card">
                                <div class="feature-icon icon-title">
                                    <i class="bi bi-magic"></i>
                                </div>
                                <h4>Reformulation de Titre</h4>
                                <p>
                                    Transformation automatique des titres selon les règles ISAD(G) pour une 
                                    description standardisée et professionnelle.
                                </p>
                                <div class="mt-3">
                                    <span class="badge bg-primary">ISAD(G) 3.1.2</span>
                                    <span class="badge bg-success">Automatique</span>
                                </div>
                            </div>

                            <div class="feature-card">
                                <div class="feature-icon icon-thesaurus">
                                    <i class="bi bi-tags"></i>
                                </div>
                                <h4>Indexation Thésaurus</h4>
                                <p>
                                    Extraction intelligente de mots-clés et association avec votre thésaurus 
                                    pour une indexation cohérente et exhaustive.
                                </p>
                                <div class="mt-3">
                                    <span class="badge bg-success">Automatique</span>
                                    <span class="badge bg-info">Thésaurus</span>
                                </div>
                            </div>

                            <div class="feature-card">
                                <div class="feature-icon icon-summary">
                                    <i class="bi bi-file-text"></i>
                                </div>
                                <h4>Résumé ISAD(G)</h4>
                                <p>
                                    Génération de résumés structurés selon l'élément 3.3.1 d'ISAD(G) - 
                                    "Portée et contenu" pour une description complète.
                                </p>
                                <div class="mt-3">
                                    <span class="badge bg-info">ISAD(G) 3.3.1</span>
                                    <span class="badge bg-warning">Contextuel</span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Installation -->
                    <section id="installation" class="doc-section">
                        <h2><i class="bi bi-download me-2"></i>Installation</h2>

                        <h3>Prérequis</h3>
                        <ul>
                            <li>Laravel 11+</li>
                            <li>PHP 8.2+</li>
                            <li>8GB RAM minimum (recommandé: 16GB)</li>
                            <li>Ollama installé et configuré</li>
                        </ul>

                        <h3>Installation d'Ollama</h3>
                        <div class="code-block">
                            <button class="copy-btn" onclick="copyCode(this)">Copier</button>
# Windows (avec winget)
winget install ollama

# macOS (avec Homebrew)
brew install ollama

# Linux
curl -fsSL https://ollama.ai/install.sh | sh

# Démarrer Ollama
ollama serve
                        </div>

                        <h3>Installation des Modèles</h3>
                        <div class="code-block">
                            <button class="copy-btn" onclick="copyCode(this)">Copier</button>
# Modèles recommandés pour MCP
ollama pull gemma3:4b        # Modèle unique pour toutes les fonctionnalités
ollama pull codellama:7b     # Structuration avancée (optionnel)

# Vérifier l'installation
ollama list
                        </div>

                        <h3>Configuration Laravel</h3>
                        <div class="code-block">
                            <button class="copy-btn" onclick="copyCode(this)">Copier</button>
# Variables d'environnement à ajouter dans .env
OLLAMA_URL=http://127.0.0.1:11434
OLLAMA_TIMEOUT=300

# Publier la configuration MCP
php artisan vendor:publish --tag="ollama-laravel-config"
                        </div>
                    </section>

                    <!-- Configuration -->
                    <section id="configuration" class="doc-section">
                        <h2><i class="bi bi-sliders me-2"></i>Configuration</h2>

                        <h3>Configuration Principale</h3>
                        <p>
                            La configuration se trouve dans <code>config/ollama-mcp.php</code> :
                        </p>

                        <div class="code-block">
                            <button class="copy-btn" onclick="copyCode(this)">Copier</button>
return [
    'base_url' => env('OLLAMA_URL', 'http://127.0.0.1:11434'),
    
    'models' => [
        'title' => 'gemma3:4b',
'thesaurus' => 'gemma3:4b',
'summary' => 'gemma3:4b',
    ],
    
    'options' => [
        'temperature' => 0.7,
        'num_predict' => 1000,
        'top_p' => 0.9,
    ],
    
    'auto_processing' => [
        'enabled' => false,
        'features' => ['thesaurus'],
    ],
    
    'performance' => [
        'timeout' => 300,
        'retry_attempts' => 3,
        'cache_enabled' => true,
        'cache_ttl' => 3600,
    ]
];
                        </div>

                        <h3>Configuration via Interface Web</h3>
                        <p>
                            Vous pouvez également configurer le système via l'interface d'administration :
                        </p>
                        <ol>
                            <li>Accédez à <code>/admin/mcp/configuration</code></li>
                            <li>Testez la connexion Ollama</li>
                            <li>Sélectionnez les modèles pour chaque fonctionnalité</li>
                            <li>Ajustez les paramètres de performance</li>
                            <li>Sauvegardez la configuration</li>
                        </ol>
                    </section>

                    <!-- Utilisation -->
                    <section id="usage" class="doc-section">
                        <h2><i class="bi bi-terminal me-2"></i>Utilisation</h2>

                        <h3>Interface Utilisateur</h3>
                        <p>Les boutons MCP sont intégrés directement dans les vues des records :</p>

                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Vue</th>
                                        <th>Localisation</th>
                                        <th>Fonctionnalités</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>records/show</code></td>
                                        <td>Barre d'outils principale</td>
                                        <td>Tous les traitements individuels</td>
                                    </tr>
                                    <tr>
                                        <td><code>records/index</code></td>
                                        <td>Toolbar d'actions</td>
                                        <td>Traitement par lots, Dashboard</td>
                                    </tr>
                                    <tr>
                                        <td><code>records/edit</code></td>
                                        <td>Onglets spécifiques</td>
                                        <td>Aide à la saisie contextuelle</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h3>Traitement d'un Record Individuel</h3>
                        <ol>
                            <li>Ouvrez la vue d'un record (<code>/records/{id}</code>)</li>
                            <li>Cliquez sur le bouton de la fonctionnalité souhaitée</li>
                            <li>Choisissez "Prévisualiser" ou "Appliquer directement"</li>
                            <li>Validez les changements si nécessaire</li>
                        </ol>

                        <h3>Traitement par Lots</h3>
                        <ol>
                            <li>Allez sur la liste des records (<code>/records</code>)</li>
                            <li>Cliquez sur "Traitement MCP par lots"</li>
                            <li>Sélectionnez les records à traiter</li>
                            <li>Choisissez les fonctionnalités à appliquer</li>
                            <li>Lancez le traitement (synchrone ou asynchrone)</li>
                        </ol>
                    </section>

                    <!-- API REST -->
                    <section id="api" class="doc-section">
                        <h2><i class="bi bi-cloud me-2"></i>API REST</h2>

                        <h3>Endpoints Principaux</h3>

                        @if(isset($docs['api_endpoints']))
                            @foreach($docs['api_endpoints'] as $category => $endpoints)
                                <h4>{{ $category }}</h4>
                                @foreach($endpoints as $endpoint => $description)
                                    <div class="api-endpoint">
                                        @php
                                            $method = explode(' ', $endpoint)[0];
                                            $url = substr($endpoint, strlen($method) + 1);
                                        @endphp
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="method-badge method-{{ strtolower($method) }}">{{ $method }}</span>
                                            <code>{{ $url }}</code>
                                        </div>
                                        <p class="mb-0">{{ $description }}</p>
                                    </div>
                                @endforeach
                            @endforeach
                        @endif

                        <h3>Exemples d'Utilisation</h3>

                        <h4>Test de Santé</h4>
                        <div class="code-block">
                            <button class="copy-btn" onclick="copyCode(this)">Copier</button>
curl -X GET "http://your-domain.com/api/mcp/health" \
     -H "Accept: application/json"
                        </div>

                        <h4>Traitement d'un Record</h4>
                        <div class="code-block">
                            <button class="copy-btn" onclick="copyCode(this)">Copier</button>
curl -X POST "http://your-domain.com/api/mcp/records/123/process" \
     -H "Content-Type: application/json" \
     -H "X-CSRF-TOKEN: your-csrf-token" \
     -d '{
       "features": ["title", "thesaurus", "summary"],
       "async": true
     }'
                        </div>

                        <h4>Réponse Type</h4>
                        <div class="code-block">
                            <button class="copy-btn" onclick="copyCode(this)">Copier</button>
{
    "message": "Traitement MCP programmé pour le record 123",
    "record_id": 123,
    "features": ["title", "thesaurus", "summary"],
    "async": true,
    "job_id": "uuid-job-id"
}
                        </div>
                    </section>

                    <!-- Commandes Artisan -->
                    <section id="commands" class="doc-section">
                        <h2><i class="bi bi-code-square me-2"></i>Commandes Artisan</h2>

                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Commande</th>
                                        <th>Description</th>
                                        <th>Options</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>mcp:test</code></td>
                                        <td>Test complet du système MCP</td>
                                        <td><code>--skip-ollama</code></td>
                                    </tr>
                                    <tr>
                                        <td><code>mcp:process-record</code></td>
                                        <td>Traiter un record individuel</td>
                                        <td><code>--features</code>, <code>--preview</code></td>
                                    </tr>
                                    <tr>
                                        <td><code>mcp:batch-process</code></td>
                                        <td>Traitement par lots</td>
                                        <td><code>--limit</code>, <code>--features</code></td>
                                    </tr>
                                    <tr>
                                        <td><code>mcp:installation-summary</code></td>
                                        <td>Résumé de l'installation</td>
                                        <td>-</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h3>Exemples d'Utilisation</h3>

                        <div class="code-block">
                            <button class="copy-btn" onclick="copyCode(this)">Copier</button>
# Test complet du système
php artisan mcp:test

# Traiter un record spécifique
php artisan mcp:process-record 123 --features=title,thesaurus --preview

# Traitement par lots
php artisan mcp:batch-process --limit=50 --features=thesaurus

# Résumé de l'installation
php artisan mcp:installation-summary
                        </div>
                    </section>

                    <!-- Dépannage -->
                    <section id="troubleshooting" class="doc-section">
                        <h2><i class="bi bi-tools me-2"></i>Dépannage</h2>

                        <h3>Problèmes Courants</h3>

                        <div class="accordion" id="troubleshootingAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#problem1">
                                        Connexion à Ollama échoue
                                    </button>
                                </h2>
                                <div id="problem1" class="accordion-collapse collapse show" data-bs-parent="#troubleshootingAccordion">
                                    <div class="accordion-body">
                                        <strong>Symptôme :</strong> Erreur "Connection refused" ou timeout<br>
                                        <strong>Solutions :</strong>
                                        <ul>
                                            <li>Vérifiez qu'Ollama est démarré : <code>ollama serve</code></li>
                                            <li>Contrôlez l'URL dans <code>.env</code> : <code>OLLAMA_URL=http://127.0.0.1:11434</code></li>
                                            <li>Testez la connexion : <code>curl http://127.0.0.1:11434/api/tags</code></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#problem2">
                                        Modèles non trouvés
                                    </button>
                                </h2>
                                <div id="problem2" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                                    <div class="accordion-body">
                                        <strong>Symptôme :</strong> Erreur "model not found"<br>
                                        <strong>Solutions :</strong>
                                        <ul>
                                            <li>Installez le modèle : <code>ollama pull gemma3:4b</code></li>
                                            <li>Vérifiez les modèles installés : <code>ollama list</code></li>
                                            <li>Contrôlez la configuration dans <code>config/ollama-mcp.php</code></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#problem3">
                                        Performances lentes
                                    </button>
                                </h2>
                                <div id="problem3" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                                    <div class="accordion-body">
                                        <strong>Symptôme :</strong> Temps de traitement élevés<br>
                                        <strong>Solutions :</strong>
                                        <ul>
                                            <li>Augmentez la RAM disponible (minimum 8GB)</li>
                                            <li>Utilisez des modèles plus petits (7B au lieu de 13B)</li>
                                            <li>Activez le cache : <code>'cache_enabled' => true</code></li>
                                            <li>Réduisez le nombre de tokens : <code>'num_predict' => 500</code></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h3>Logs et Débuggage</h3>
                        <div class="code-block">
                            <button class="copy-btn" onclick="copyCode(this)">Copier</button>
# Consulter les logs Laravel
tail -f storage/logs/laravel.log

# Logs des jobs en queue
php artisan queue:failed

# Debug d'un record spécifique
php artisan mcp:process-record 123 --features=title --preview
                        </div>
                    </section>

                    <!-- Exemples -->
                    <section id="examples" class="doc-section">
                        <h2><i class="bi bi-lightbulb me-2"></i>Exemples Pratiques</h2>

                        <h3>Reformulation de Titre</h3>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Avant (titre original) :</strong>
                                <div class="code-block">
Documents mairie
                                </div>
                            </div>
                            <div class="col-md-6">
                                <strong>Après (titre reformulé) :</strong>
                                <div class="code-block">
Personnel municipal, médailles du travail : listes. 1950-1960
                                </div>
                            </div>
                        </div>

                        <h3>Indexation Thésaurus</h3>
                        <div class="code-block">
                            <button class="copy-btn" onclick="copyCode(this)">Copier</button>
Texte d'entrée: "Correspondance avec les services municipaux concernant 
l'attribution des médailles du travail aux employés de la mairie."

Mots-clés extraits:
- personnel municipal
- médaille du travail  
- correspondance administrative
- services municipaux
- récompense professionnelle

Concepts thésaurus trouvés:
✓ Personnel (ID: 1234)
✓ Récompenses (ID: 5678)  
✓ Administration locale (ID: 9012)
                        </div>

                        <h3>Résumé ISAD(G)</h3>
                        <div class="code-block">
                            <button class="copy-btn" onclick="copyCode(this)">Copier</button>
Élément 3.3.1 - Portée et contenu:

"Cette série contient les listes nominatives et la correspondance 
concernant l'attribution des médailles du travail au personnel 
municipal pour la période 1950-1960. Les documents comprennent 
les propositions de récompenses, les avis des services, les 
décisions administratives et les notifications aux bénéficiaires. 

Cette documentation témoigne de la politique de reconnaissance 
du mérite professionnel mise en œuvre par la municipalité et 
constitue une source précieuse pour l'histoire sociale locale."
                        </div>

                        <h3>Script d'Automatisation</h3>
                        <p>Exemple de script pour traiter tous les nouveaux records :</p>
                        <div class="code-block">
                            <button class="copy-btn" onclick="copyCode(this)">Copier</button>
#!/bin/bash

# Script de traitement automatique MCP
# À exécuter via cron toutes les heures

cd /path/to/your/laravel/app

# Traiter les records créés dans les dernières 24h
php artisan mcp:batch-process \
    --created-since="24 hours ago" \
    --features=thesaurus \
    --async \
    --limit=100

# Vérifier l'état de santé
php artisan mcp:test --skip-ollama

echo "Traitement MCP automatique terminé"
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Navigation de la documentation
    const navItems = document.querySelectorAll('.doc-nav-item');
    const sections = document.querySelectorAll('.doc-section');
    
    // Gestion du scroll pour activer les liens de navigation
    window.addEventListener('scroll', function() {
        let current = '';
        sections.forEach(section => {
            const sectionTop = section.getBoundingClientRect().top;
            if (sectionTop <= 100) {
                current = section.getAttribute('id');
            }
        });
        
        navItems.forEach(item => {
            item.classList.remove('active');
            if (item.getAttribute('data-section') === current) {
                item.classList.add('active');
            }
        });
    });
    
    // Recherche dans la documentation
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        sections.forEach(section => {
            const content = section.textContent.toLowerCase();
            if (content.includes(searchTerm) || searchTerm === '') {
                section.style.display = 'block';
            } else {
                section.style.display = 'none';
            }
        });
    });
    
    // Smooth scroll pour les liens de navigation
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetSection = document.getElementById(targetId);
            
            if (targetSection) {
                targetSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Initialiser les tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

function copyCode(button) {
    const codeBlock = button.parentNode;
    const code = codeBlock.textContent.replace('Copier', '').trim();
    
    navigator.clipboard.writeText(code).then(function() {
        const originalText = button.textContent;
        button.textContent = 'Copié !';
        button.style.background = 'rgba(40, 167, 69, 0.8)';
        
        setTimeout(function() {
            button.textContent = originalText;
            button.style.background = 'rgba(255,255,255,0.1)';
        }, 2000);
    }).catch(function(err) {
        console.error('Erreur lors de la copie :', err);
    });
}

// Fonction pour imprimer la documentation
function printDocumentation() {
    window.print();
}
</script>
@endpush