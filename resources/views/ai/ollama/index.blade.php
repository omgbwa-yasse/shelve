{{-- resources/views/ai/ollama/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Ollama Dashboard')

@section('content')
<div class="container mx-auto px-4 py-6">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Ollama AI Management</h1>
            <p class="text-gray-600 mt-1">Gérez vos modèles et interactions Ollama</p>
        </div>

        <div class="flex space-x-3">
            <a href="{{ route('ai.ollama.chat') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z"/>
                </svg>
                Nouveau Chat
            </a>

            <button onclick="syncModels()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1z"/>
                </svg>
                Sync Modèles
            </button>
        </div>
    </div>

    {{-- Status Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Status Ollama</p>
                    <div class="flex items-center mt-1">
                        <div id="health-indicator" class="w-3 h-3 bg-gray-400 rounded-full mr-2"></div>
                        <span id="health-text" class="text-sm text-gray-600">Vérification...</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Modèles Actifs</p>
                    <p id="active-models" class="text-2xl font-semibold text-gray-900">--</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Interactions Aujourd'hui</p>
                    <p id="daily-interactions" class="text-2xl font-semibold text-gray-900">--</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a1 1 0 00-1 1v3a1 1 0 11-2 0V6z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Temps Moyen</p>
                    <p id="avg-response-time" class="text-2xl font-semibold text-gray-900">--</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="bg-white rounded-lg shadow mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Actions Rapides</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <h3 class="font-medium text-gray-900 mb-2">Test Rapide</h3>
                    <p class="text-sm text-gray-600 mb-4">Testez rapidement un modèle avec un prompt simple</p>
                    <button onclick="openQuickTest()" class="w-full bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                        Lancer Test
                    </button>
                </div>

                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <h3 class="font-medium text-gray-900 mb-2">Batch Processing</h3>
                    <p class="text-sm text-gray-600 mb-4">Traitez plusieurs prompts en une fois</p>
                    <button onclick="openBatchProcessor()" class="w-full bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded">
                        Traitement Batch
                    </button>
                </div>

                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <h3 class="font-medium text-gray-900 mb-2">Statistiques</h3>
                    <p class="text-sm text-gray-600 mb-4">Consultez les métriques détaillées</p>
                    <a href="{{ route('ai.interactions.index') }}" class="w-full bg-purple-500 hover:bg-purple-600 text-white py-2 px-4 rounded inline-block text-center">
                        Voir Stats
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Models Table --}}
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Modèles Ollama</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200" id="models-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modèle</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Taille</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paramètres</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <!-- Contenu chargé dynamiquement -->
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Quick Test Modal --}}
<div id="quick-test-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Test Rapide</h3>

            <form id="quick-test-form">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Modèle</label>
                    <select id="test-model" class="w-full border border-gray-300 rounded-md px-3 py-2">
                        <option value="">Sélectionnez un modèle...</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Prompt</label>
                    <textarea id="test-prompt" rows="3" class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="Entrez votre prompt..."></textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Température</label>
                    <input type="range" id="test-temperature" min="0" max="2" step="0.1" value="0.7" class="w-full">
                    <span id="temp-value" class="text-sm text-gray-500">0.7</span>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeQuickTest()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Annuler
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                        Tester
                    </button>
                </div>
            </form>

            <div id="test-result" class="mt-4 hidden">
                <h4 class="font-medium text-gray-900 mb-2">Résultat:</h4>
                <div class="bg-gray-50 p-3 rounded border text-sm"></div>
                <div class="mt-2 text-xs text-gray-500">
                    <span id="test-stats"></span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Batch Processing Modal --}}
<div id="batch-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-2xl w-full p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Traitement par Lot</h3>

            <form id="batch-form">
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Modèle</label>
                        <select id="batch-model" class="w-full border border-gray-300 rounded-md px-3 py-2">
                            <option value="">Sélectionnez un modèle...</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Type de Job</label>
                        <select id="batch-type" class="w-full border border-gray-300 rounded-md px-3 py-2">
                            <option value="text_generation">Génération de texte</option>
                            <option value="summarization">Résumé</option>
                            <option value="translation">Traduction</option>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Prompts (un par ligne)</label>
                    <textarea id="batch-prompts" rows="8" class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="Entrez vos prompts, un par ligne..."></textarea>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeBatchProcessor()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Annuler
                    </button>
                    <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
                        Lancer le Traitement
                    </button>
                </div>
            </form>

            <div id="batch-result" class="mt-4 hidden">
                <h4 class="font-medium text-gray-900 mb-2">Job créé:</h4>
                <div class="bg-green-50 border border-green-200 p-3 rounded">
                    <p class="text-sm text-green-800">Job ID: <span id="job-id"></span></p>
                    <p class="text-sm text-green-600">Le traitement a été mis en file d'attente.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Variables globales
let modelsData = [];

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    checkHealth();
    loadModels();
    loadStats();

    // Setup des événements
    setupEventListeners();

    // Refresh automatique toutes les 30 secondes
    setInterval(() => {
        checkHealth();
        loadStats();
    }, 30000);
});

function setupEventListeners() {
    // Temperature slider
    document.getElementById('test-temperature').addEventListener('input', function(e) {
        document.getElementById('temp-value').textContent = e.target.value;
    });

    // Form submissions
    document.getElementById('quick-test-form').addEventListener('submit', handleQuickTest);
    document.getElementById('batch-form').addEventListener('submit', handleBatchSubmit);
}

async function checkHealth() {
    try {
        const response = await fetch('/api/ai/ollama/health');
        const data = await response.json();

        const indicator = document.getElementById('health-indicator');
        const text = document.getElementById('health-text');

        if (data.status === 'healthy') {
            indicator.className = 'w-3 h-3 bg-green-500 rounded-full mr-2';
            text.textContent = 'En ligne';
            text.className = 'text-sm text-green-600';
        } else {
            indicator.className = 'w-3 h-3 bg-red-500 rounded-full mr-2';
            text.textContent = 'Hors ligne';
            text.className = 'text-sm text-red-600';
        }
    } catch (error) {
        console.error('Erreur health check:', error);
        document.getElementById('health-indicator').className = 'w-3 h-3 bg-red-500 rounded-full mr-2';
        document.getElementById('health-text').textContent = 'Erreur';
    }
}

async function loadModels() {
    try {
        const response = await fetch('/api/ai/ollama/models');
        const data = await response.json();

        if (data.success) {
            modelsData = data.models;
            updateModelsTable();
            updateModelSelects();
            document.getElementById('active-models').textContent = data.models.filter(m => m.is_active).length;
        }
    } catch (error) {
        console.error('Erreur chargement modèles:', error);
    }
}

function updateModelsTable() {
    const tbody = document.querySelector('#models-table tbody');
    tbody.innerHTML = '';

    modelsData.forEach(model => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                            <svg class="w-6 h-6 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">${model.name}</div>
                        <div class="text-sm text-gray-500">${model.model_family || 'Unknown'}</div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${model.formatted_size || 'N/A'}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${model.parameter_size_formatted || 'N/A'}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${model.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                    ${model.is_active ? 'Actif' : 'Inactif'}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <button onclick="testModel('${model.name}')" class="text-blue-600 hover:text-blue-900 mr-3">Test</button>
                <button onclick="viewModelStats('${model.id}')" class="text-green-600 hover:text-green-900">Stats</button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function updateModelSelects() {
    const activeModels = modelsData.filter(m => m.is_active);

    ['test-model', 'batch-model'].forEach(selectId => {
        const select = document.getElementById(selectId);
        select.innerHTML = '<option value="">Sélectionnez un modèle...</option>';

        activeModels.forEach(model => {
            const option = document.createElement('option');
            option.value = model.name;
            option.textContent = `${model.name} (${model.parameter_size_formatted || 'N/A'})`;
            select.appendChild(option);
        });
    });
}

async function loadStats() {
    try {
        const response = await fetch('/api/ai/ollama/dashboard');
        const data = await response.json();

        document.getElementById('daily-interactions').textContent = data.stats?.total_interactions || 0;
        document.getElementById('avg-response-time').textContent = data.stats?.avg_response_time ?
            Math.round(data.stats.avg_response_time / 1000000) + 'ms' : '--';
    } catch (error) {
        console.error('Erreur chargement stats:', error);
    }
}

async function syncModels() {
    if (!confirm('Synchroniser les modèles avec Ollama?')) return;

    try {
        const response = await fetch('/api/ai/ollama/models/sync', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        const data = await response.json();
        if (data.success) {
            alert(`${data.synced_count} modèles synchronisés avec succès`);
            await loadModels();
        } else {
            alert('Erreur: ' + data.message);
        }
    } catch (error) {
        alert('Erreur: ' + error.message);
    }
}

// Modal handlers
function openQuickTest() {
    document.getElementById('quick-test-modal').classList.remove('hidden');
    document.getElementById('test-result').classList.add('hidden');
}

function closeQuickTest() {
    document.getElementById('quick-test-modal').classList.add('hidden');
    document.getElementById('quick-test-form').reset();
}

function openBatchProcessor() {
    document.getElementById('batch-modal').classList.remove('hidden');
    document.getElementById('batch-result').classList.add('hidden');
}

function closeBatchProcessor() {
    document.getElementById('batch-modal').classList.add('hidden');
    document.getElementById('batch-form').reset();
}

async function handleQuickTest(e) {
    e.preventDefault();

    const model = document.getElementById('test-model').value;
    const prompt = document.getElementById('test-prompt').value;
    const temperature = document.getElementById('test-temperature').value;

    if (!model || !prompt) {
        alert('Veuillez remplir tous les champs');
        return;
    }

    const submitBtn = e.target.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Test en cours...';

    try {
        const response = await fetch('/api/ai/ollama/test', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                model: model,
                prompt: prompt,
                options: { temperature: parseFloat(temperature) }
            })
        });

        const data = await response.json();

        const resultDiv = document.getElementById('test-result');
        const resultContent = resultDiv.querySelector('.bg-gray-50');
        const statsSpan = document.getElementById('test-stats');

        if (data.success) {
            resultContent.textContent = data.response;
            statsSpan.textContent = `Tokens: ${(data.prompt_eval_count || 0) + (data.eval_count || 0)} | Temps: ${data.total_duration ? Math.round(data.total_duration / 1000000) + 'ms' : 'N/A'}`;
            resultDiv.classList.remove('hidden');
        } else {
            resultContent.textContent = 'Erreur: ' + data.error;
            statsSpan.textContent = '';
            resultDiv.classList.remove('hidden');
        }
    } catch (error) {
        alert('Erreur: ' + error.message);
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Tester';
    }
}

async function handleBatchSubmit(e) {
    e.preventDefault();

    const model = document.getElementById('batch-model').value;
    const type = document.getElementById('batch-type').value;
    const prompts = document.getElementById('batch-prompts').value.split('\n').filter(p => p.trim());

    if (!model || prompts.length === 0) {
        alert('Veuillez remplir tous les champs');
        return;
    }

    const submitBtn = e.target.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Création du job...';

    try {
        const response = await fetch('/api/ai/ollama/batch', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                job_type: type,
                ai_model_id: modelsData.find(m => m.name === model)?.id,
                inputs: prompts
            })
        });

        const data = await response.json();

        if (data.success) {
            document.getElementById('job-id').textContent = data.job_id;
            document.getElementById('batch-result').classList.remove('hidden');
        } else {
            alert('Erreur: ' + data.message);
        }
    } catch (error) {
        alert('Erreur: ' + error.message);
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Lancer le Traitement';
    }
}

function testModel(modelName) {
    document.getElementById('test-model').value = modelName;
    openQuickTest();
}

function viewModelStats(modelId) {
    window.location.href = `/ai/models/${modelId}`;
}
</script>

<style>
.transition-shadow {
    transition: box-shadow 0.15s ease-in-out;
}

.hover\:shadow-md:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

/* Custom scrollbar pour les modals */
.overflow-y-auto::-webkit-scrollbar {
    width: 8px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>
@endsection


