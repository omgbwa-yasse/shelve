@extends('layouts.app')

@section('title', 'Documentation MCP (Essentiel)')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-0"><i class="bi bi-book me-2"></i>Documentation MCP – Essentiel</h1>
        <a href="{{ route('admin.mcp.dashboard') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Dashboard</a>
    </div>

    <div class="alert alert-info small mb-4">
        Version condensée : uniquement les informations nécessaires pour installer, configurer et utiliser rapidement le MCP.
    </div>

    <div class="card mb-4">
        <div class="card-header fw-semibold">1. Introduction</div>
        <div class="card-body small">
            <p>Le MCP (Model Context Protocol) ajoute des fonctionnalités IA pour : reformulation de titres, indexation thésaurus, génération de résumés ISAD(G). Basé sur <strong>Ollama</strong> (exécution locale des modèles) pour confidentialité et performance.</p>
            <ul class="mb-0">
                <li>Titres normalisés (ISAD(G) 3.1.2)</li>
                <li>Mots-clés/thésaurus automatiques</li>
                <li>Résumé (Portée et contenu 3.3.1)</li>
            </ul>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header fw-semibold">2. Prérequis & Installation rapide</div>
        <div class="card-body small">
            <ul>
                <li>PHP 8.2+, Laravel 11+</li>
                <li>RAM : 8GB (16GB conseillé)</li>
                <li>Ollama installé : <code>winget install ollama</code> (Win) / <code>brew install ollama</code> (mac) / script install (Linux)</li>
            </ul>
            <pre class="bg-light p-2 small mb-2"><code># Démarrer Ollama
ollama serve

# Modèle minimal recommandé
ollama pull gemma3:4b
ollama list</code></pre>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header fw-semibold">3. Configuration minimale</div>
        <div class="card-body small">
<pre class="bg-light p-2 small"><code># .env
OLLAMA_URL=http://127.0.0.1:11434
OLLAMA_TIMEOUT=300

# (si nécessaire) publier la config
php artisan vendor:publish --tag="ollama-laravel-config" --force</code></pre>
<pre class="bg-light p-2 small mb-0"><code>// config/ollama-mcp.php (extrait)
return [
  'base_url' => env('OLLAMA_URL'),
  'models' => [
    'title' => 'gemma3:4b',
    'thesaurus' => 'gemma3:4b',
    'summary' => 'gemma3:4b',
  ],
  'options' => ['temperature'=>0.7,'num_predict'=>800],
];</code></pre>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header fw-semibold">4. Utilisation rapide (UI)</div>
        <div class="card-body small">
            <ol class="mb-2">
                <li>Ouvrir un record (<code>/records/{id}</code>).</li>
                <li>Cliquer sur l'action MCP souhaitée (Titre / Thésaurus / Résumé).</li>
                <li>Prévisualiser puis appliquer.</li>
            </ol>
            <p class="mb-1 fw-semibold">Traitement par lots :</p>
            <ol class="mb-0">
                <li>Aller à la liste des records.</li>
                <li>Sélectionner plusieurs éléments.</li>
                <li>Lancer le batch MCP (synchrone ou via jobs).</li>
            </ol>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header fw-semibold">5. Commandes Artisan clés</div>
        <div class="card-body small">
            <table class="table table-sm align-middle mb-3">
                <thead><tr><th>Commande</th><th>But</th></tr></thead>
                <tbody>
                    <tr><td><code>php artisan mcp:test</code></td><td>Diagnostic rapide</td></tr>
                    <tr><td><code>php artisan mcp:process-record 123 --features=title,thesaurus</code></td><td>Record unique</td></tr>
                    <tr><td><code>php artisan mcp:batch-process --limit=50 --features=thesaurus</code></td><td>Lot simple</td></tr>
                    <tr><td><code>php artisan queue:failed</code></td><td>Vérifier échecs jobs</td></tr>
                </tbody>
            </table>
<pre class="bg-light p-2 small mb-0"><code># Exemple script périodique (cron)
php artisan mcp:batch-process --limit=100 --features=thesaurus --async
php artisan mcp:test --skip-ollama</code></pre>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header fw-semibold">6. API minimale</div>
        <div class="card-body small">
<pre class="bg-light p-2 small mb-2"><code># Health
curl -s {{ url('/api/mcp/health') }}

# Traitement record (POST JSON)
curl -X POST {{ url('/api/mcp/records/123/process') }} \
  -H "Content-Type: application/json" \
  -d '{"features":["title","thesaurus"],"async":true}'</code></pre>
            <p class="mb-0 text-muted">Inclure auth/CSRF si nécessaire selon votre configuration.</p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header fw-semibold">7. Dépannage rapide</div>
        <div class="card-body small">
            <ul class="mb-2">
                <li><strong>Connexion refusée :</strong> vérifier <code>ollama serve</code> & URL <code>OLLAMA_URL</code>.</li>
                <li><strong>Model not found :</strong> <code>ollama pull gemma3:4b</code>.</li>
                <li><strong>Lent :</strong> réduire <code>num_predict</code>, utiliser modèle plus petit.</li>
                <li><strong>Jobs en échec :</strong> <code>php artisan queue:failed</code>.</li>
            </ul>
<pre class="bg-light p-2 small mb-0"><code># Logs
tail -f storage/logs/laravel.log</code></pre>
        </div>
    </div>

    <p class="text-center text-muted small mb-0">Fin – Version essentielle. Pour détails avancés : se référer à la version complète (historique Git).</p>
</div>
@endsection
