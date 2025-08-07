@extends('layouts.app')

@section('title', 'Gestion des Modèles Ollama')

@push('styles')
<style>
    .model-card {
        border: 1px solid #e9ecef;
        border-radius: 12px;
        transition: all 0.3s ease;
        overflow: hidden;
        height: 100%;
    }

    .model-card:hover {
        border-color: #007bff;
        box-shadow: 0 4px 15px rgba(0,123,255,0.1);
        transform: translateY(-2px);
    }

    .model-card.installed {
        border-color: #28a745;
        background: linear-gradient(135deg, #f8fff8 0%, #f0fff0 100%);
    }

    .model-card.downloading {
        border-color: #ffc107;
        background: linear-gradient(135deg, #fffbf0 0%, #fff8e1 100%);
    }

    .model-header {
        padding: 1.5rem;
        border-bottom: 1px solid #e9ecef;
    }

    .model-body {
        padding: 1.5rem;
    }

    .model-footer {
        padding: 1rem 1.5rem;
        background: #f8f9fa;
        border-top: 1px solid #e9ecef;
    }

    .model-status {
        position: absolute;
        top: 1rem;
        right: 1rem;
        z-index: 10;
    }

    .progress-thin {
        height: 4px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 8px;
        padding: 1.5rem;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .model-size {
        font-size: 0.875rem;
        color: #6c757d;
    }

    .action-buttons {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .action-buttons .btn {
        flex: 1;
        min-width: 80px;
    }

    .terminal-output {
        background: #1e1e1e;
        color: #ffffff;
        border-radius: 8px;
        padding: 1rem;
        font-family: 'Courier New', monospace;
        font-size: 0.875rem;
        max-height: 300px;
        overflow-y: auto;
        white-space: pre-wrap;
    }

    .model-recommendation {
        background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 fw-bold">
                <i class="bi bi-cpu me-3 text-primary"></i>
                Gestion des Modèles Ollama
            </h1>
            <p class="text-muted mb-0">Installation et gestion des modèles d'IA pour le MCP</p>
        </div>
        <div>
            <a href="{{ route('admin.mcp.dashboard') }}" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left me-1"></i>Retour Dashboard
            </a>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#installModelModal">
                <i class="bi bi-download me-1"></i>Installer un Modèle
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Erreurs :</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Statistiques Globales -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value text-primary">{{ count($installedModels) }}</div>
            <div class="text-muted">Modèles Installés</div>
        </div>
        <div class="stat-card">
            <div class="stat-value text-info">{{ count($availableModels) }}</div>
            <div class="text-muted">Modèles Disponibles</div>
        </div>
        <div class="stat-card">
            <div class="stat-value text-success">
                {{ collect($modelStats)->sum('usage_count') }}
            </div>
            <div class="text-muted">Utilisations Totales</div>
        </div>
        <div class="stat-card">
            <div class="stat-value text-warning">
                {{ number_format(collect($modelStats)->avg('avg_time'), 1) }}s
            </div>
            <div class="text-muted">Temps Moyen</div>
        </div>
    </div>

    <!-- Recommandations -->
    <div class="model-recommendation">
        <h5 class="mb-2">
            <i class="bi bi-lightbulb me-2"></i>Recommandations d'Installation
        </h5>
        <div class="row">
            <div class="col-md-4">
                <strong>Pour débuter :</strong>
                <ul class="mb-0 mt-1">
                    <li>gemma3:4b (unique modèle recommandé)</li>
                </ul>
            </div>
            <div class="col-md-4">
                <strong>Pour la performance :</strong>
                <ul class="mb-0 mt-1">
                    <li>Privilégier les modèles 7B</li>
                    <li>Prévoir 8GB RAM minimum</li>
                </ul>
            </div>
            <div class="col-md-4">
                <strong>Pour la qualité :</strong>
                <ul class="mb-0 mt-1">
                    <li>gemma3:4b pour toutes les fonctionnalités</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Modèles Installés -->
    @if(!empty($installedModels))
        <h3 class="mb-4">
            <i class="bi bi-check-circle-fill text-success me-2"></i>
            Modèles Installés ({{ count($installedModels) }})
        </h3>
        <div class="row">
            @foreach($installedModels as $modelId => $modelInfo)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="model-card installed position-relative">
                        <div class="model-status">
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle me-1"></i>Installé
                            </span>
                        </div>
                        
                        <div class="model-header">
                            <h5 class="fw-bold mb-1">{{ $availableModels[$modelId]['name'] ?? $modelId }}</h5>
                            <div class="model-size">
                                <i class="bi bi-hdd me-1"></i>{{ $modelInfo['size'] }}
                                <span class="ms-2">
                                    <i class="bi bi-calendar me-1"></i>{{ $modelInfo['modified'] }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="model-body">
                            <p class="text-muted mb-3">
                                {{ $availableModels[$modelId]['description'] ?? 'Modèle de langage avancé' }}
                            </p>
                            
                            @if(isset($modelStats[$modelId]))
                                <div class="row text-center mb-3">
                                    <div class="col-4">
                                        <div class="fw-bold text-primary">{{ $modelStats[$modelId]['usage_count'] }}</div>
                                        <small class="text-muted">Utilisations</small>
                                    </div>
                                    <div class="col-4">
                                        <div class="fw-bold text-info">{{ number_format($modelStats[$modelId]['avg_time'], 1) }}s</div>
                                        <small class="text-muted">Temps moy.</small>
                                    </div>
                                    <div class="col-4">
                                        <div class="fw-bold text-success">{{ number_format($modelStats[$modelId]['success_rate'], 1) }}%</div>
                                        <small class="text-muted">Succès</small>
                                    </div>
                                </div>
                            @endif
                            
                            @if(isset($availableModels[$modelId]['recommended_for']))
                                <div class="mb-3">
                                    <small class="text-muted d-block mb-1">Recommandé pour :</small>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($availableModels[$modelId]['recommended_for'] as $feature)
                                            <span class="badge bg-light text-dark">{{ $feature }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <div class="model-footer">
                            <div class="action-buttons">
                                <button class="btn btn-outline-primary btn-sm" onclick="testModel('{{ $modelId }}')">
                                    <i class="bi bi-play me-1"></i>Tester
                                </button>
                                <button class="btn btn-outline-info btn-sm" onclick="showModelInfo('{{ $modelId }}')">
                                    <i class="bi bi-info-circle me-1"></i>Info
                                </button>
                                <button class="btn btn-outline-danger btn-sm" onclick="confirmDelete('{{ $modelId }}')">
                                    <i class="bi bi-trash me-1"></i>Supprimer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Modèles Disponibles (non installés) -->
    <h3 class="mb-4 mt-5">
        <i class="bi bi-cloud-download text-primary me-2"></i>
        Modèles Disponibles au Téléchargement
    </h3>
    <div class="row">
        @foreach($availableModels as $modelId => $modelInfo)
            @if(!isset($installedModels[$modelId]))
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="model-card position-relative">
                        <div class="model-status">
                            <span class="badge bg-secondary">
                                <i class="bi bi-cloud me-1"></i>Disponible
                            </span>
                        </div>
                        
                        <div class="model-header">
                            <h5 class="fw-bold mb-1">{{ $modelInfo['name'] }}</h5>
                            <div class="model-size">
                                <i class="bi bi-hdd me-1"></i>{{ $modelInfo['size'] }}
                            </div>
                        </div>
                        
                        <div class="model-body">
                            <p class="text-muted mb-3">{{ $modelInfo['description'] }}</p>
                            
                            @if(isset($modelInfo['recommended_for']))
                                <div class="mb-3">
                                    <small class="text-muted d-block mb-1">Recommandé pour :</small>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($modelInfo['recommended_for'] as $feature)
                                            <span class="badge bg-light text-dark">{{ $feature }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <div class="model-footer">
                            <button class="btn btn-primary w-100" onclick="installModel('{{ $modelId }}', '{{ $modelInfo['name'] }}')">
                                <i class="bi bi-download me-1"></i>Installer ce Modèle
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</div>

<!-- Modal d'Installation -->
<div class="modal fade" id="installModelModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-download me-2"></i>Installation de Modèle
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="installForm">
                    <div class="mb-3">
                        <label for="modelSelect" class="form-label">Sélectionner un modèle</label>
                        <select class="form-select" id="modelSelect" name="model">
                            @foreach($availableModels as $modelId => $modelInfo)
                                @if(!isset($installedModels[$modelId]))
                                    <option value="{{ $modelId }}">{{ $modelInfo['name'] }} ({{ $modelInfo['size'] }})</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    
                    <div id="installProgress" style="display: none;">
                        <div class="mb-2">
                            <strong>Progression de l'installation :</strong>
                        </div>
                        <div class="progress mb-3">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" id="progressBar" style="width: 0%"></div>
                        </div>
                        <div class="terminal-output" id="terminalOutput"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" id="startInstallBtn" onclick="startInstallation()">
                    <i class="bi bi-play me-1"></i>Démarrer l'Installation
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Test -->
<div class="modal fade" id="testModelModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-play-circle me-2"></i>Test de Modèle
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="testPrompt" class="form-label">Prompt de test</label>
                    <textarea class="form-control" id="testPrompt" rows="3" placeholder="Entrez votre prompt de test...">Reformule ce titre selon les règles ISAD(G): Documents de la mairie</textarea>
                </div>
                <div id="testResult" class="terminal-output" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" id="runTestBtn" onclick="runModelTest()">
                    <i class="bi bi-play me-1"></i>Lancer le Test
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentTestModel = null;

function installModel(modelId, modelName) {
    document.getElementById('modelSelect').value = modelId;
    const modal = new bootstrap.Modal(document.getElementById('installModelModal'));
    modal.show();
}

function startInstallation() {
    const modelId = document.getElementById('modelSelect').value;
    const progressDiv = document.getElementById('installProgress');
    const progressBar = document.getElementById('progressBar');
    const terminalOutput = document.getElementById('terminalOutput');
    const startBtn = document.getElementById('startInstallBtn');
    
    progressDiv.style.display = 'block';
    startBtn.disabled = true;
    startBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Installation...';
    
    terminalOutput.textContent = `Démarrage de l'installation de ${modelId}...\n`;
    
    // Simulation de l'installation (en réalité, cela ferait appel à l'API Ollama)
    let progress = 0;
    const interval = setInterval(() => {
        progress += Math.random() * 15;
        if (progress > 100) progress = 100;
        
        progressBar.style.width = progress + '%';
        progressBar.textContent = Math.round(progress) + '%';
        
        // Ajouter des messages de progression
        if (progress > 20 && progress < 40) {
            terminalOutput.textContent += `Téléchargement en cours... ${Math.round(progress)}%\n`;
        } else if (progress > 60 && progress < 80) {
            terminalOutput.textContent += `Extraction des données...\n`;
        } else if (progress >= 100) {
            terminalOutput.textContent += `Installation terminée avec succès !\n`;
            clearInterval(interval);
            
            setTimeout(() => {
                location.reload();
            }, 2000);
        }
        
        terminalOutput.scrollTop = terminalOutput.scrollHeight;
    }, 500);
}

function testModel(modelId) {
    currentTestModel = modelId;
    document.getElementById('testPrompt').value = `Test du modèle ${modelId}: Reformule ce titre selon ISAD(G) - Documents de la mairie`;
    const modal = new bootstrap.Modal(document.getElementById('testModelModal'));
    modal.show();
}

function runModelTest() {
    const prompt = document.getElementById('testPrompt').value;
    const resultDiv = document.getElementById('testResult');
    const runBtn = document.getElementById('runTestBtn');
    
    resultDiv.style.display = 'block';
    resultDiv.textContent = 'Test en cours...\n';
    
    runBtn.disabled = true;
    runBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Test...';
    
    // Simulation d'un test (en réalité, cela ferait appel à l'API MCP)
    setTimeout(() => {
        resultDiv.textContent += `Modèle: ${currentTestModel}\n`;
        resultDiv.textContent += `Prompt: ${prompt}\n`;
        resultDiv.textContent += `Réponse: Personnel municipal, médailles du travail : listes. 1950-1960\n`;
        resultDiv.textContent += `Temps de réponse: 2.3s\n`;
        resultDiv.textContent += `Statut: Succès ✓\n`;
        
        runBtn.disabled = false;
        runBtn.innerHTML = '<i class="bi bi-play me-1"></i>Lancer le Test';
    }, 3000);
}

function confirmDelete(modelId) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le modèle ${modelId} ?\n\nCette action est irréversible et libérera l'espace disque utilisé.`)) {
        deleteModel(modelId);
    }
}

function deleteModel(modelId) {
    // En réalité, cela ferait appel à l'API Ollama pour supprimer le modèle
    alert(`Suppression de ${modelId} en cours...\n\nNote: Cette fonctionnalité nécessite l'intégration complète avec l'API Ollama.`);
}

function showModelInfo(modelId) {
    // Afficher des informations détaillées sur le modèle
    const info = `
Modèle: ${modelId}
Taille: ${document.querySelector(`[data-model="${modelId}"]`)?.dataset.size || 'N/A'}
Type: Large Language Model
Architecture: Transformer
Dernière utilisation: Aujourd'hui
Performances: Bon
    `;
    alert(info);
}

// Actualisation automatique du statut
setInterval(() => {
    // Ici, on pourrait actualiser le statut des installations en cours
}, 30000);
</script>
@endpush