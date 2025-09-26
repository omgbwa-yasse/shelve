@extends('layouts.app')

@section('title', __('AI Search Tests'))

@section('content')
<div class="card-header bg-info text-white">
    <h4 class="mb-0">
        <i class="bi bi-check-square me-2"></i>
        {{ __('AI Search System Tests') }}
    </h4>
</div>
<div class="card-body">
    <div class="row">
        <!-- Tests disponibles -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Tests Disponibles</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        @foreach($test_cases as $test)
                        <button class="list-group-item list-group-item-action"
                                onclick="runSingleTest('{{ $test['name'] }}')">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">{{ $test['name'] }}</h6>
                                <span class="test-status" id="status-{{ $test['name'] }}"></span>
                            </div>
                            <p class="mb-1">{{ $test['description'] }}</p>
                            <small class="text-muted">{{ $test['query'] }}</small>
                        </button>
                        @endforeach
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-primary w-100" onclick="runAllTests()">
                            <i class="bi bi-play-fill me-1"></i> Exécuter tous les tests
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Résultats des tests -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Résultats des Tests</h5>
                    <div id="test-summary" class="badge bg-secondary">Aucun test exécuté</div>
                </div>
                <div class="card-body">
                    <div id="test-results">
                        <div class="text-center text-muted p-4">
                            <i class="bi bi-clipboard-data fs-1"></i>
                            <p class="mt-2">Cliquez sur un test pour l'exécuter</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
.test-passed {
    background-color: #d4edda !important;
    border-color: #c3e6cb !important;
}

.test-failed {
    background-color: #f8d7da !important;
    border-color: #f5c6cb !important;
}

.test-running {
    background-color: #fff3cd !important;
    border-color: #ffecb5 !important;
}

.test-result {
    border-left: 4px solid #dee2e6;
    margin-bottom: 15px;
    padding: 15px;
    background-color: #f8f9fa;
    border-radius: 0.375rem;
}

.test-result.passed {
    border-left-color: #28a745;
    background-color: #d4edda;
}

.test-result.failed {
    border-left-color: #dc3545;
    background-color: #f8d7da;
}

.test-details {
    font-family: 'Courier New', monospace;
    font-size: 12px;
    background-color: #f1f3f4;
    padding: 10px;
    border-radius: 4px;
    margin-top: 10px;
}

.error-item {
    color: #dc3545;
    margin-bottom: 5px;
}

.success-item {
    color: #28a745;
    margin-bottom: 5px;
}
</style>
@endsection

@section('scripts')
<script>
let testResults = {};

async function runSingleTest(testName) {
    updateTestStatus(testName, 'running');

    try {
        const response = await fetch(`/ai-search/test/${testName}`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const result = await response.json();
        testResults[testName] = result;

        updateTestStatus(testName, result.passed ? 'passed' : 'failed');
        displayTestResult(result);
        updateSummary();

    } catch (error) {
        console.error('Error running test:', error);
        updateTestStatus(testName, 'failed');
    }
}

async function runAllTests() {
    const testButtons = document.querySelectorAll('.list-group-item');
    testResults = {};

    document.getElementById('test-results').innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Exécution des tests...</p></div>';

    try {
        const response = await fetch('/ai-search/tests', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const allResults = await response.json();

        // Mettre à jour les statuts individuels
        allResults.results.forEach(result => {
            testResults[result.name] = result;
            updateTestStatus(result.name, result.passed ? 'passed' : 'failed');
        });

        displayAllResults(allResults);
        updateSummary();

    } catch (error) {
        console.error('Error running all tests:', error);
        document.getElementById('test-results').innerHTML = '<div class="alert alert-danger">Erreur lors de l\'exécution des tests</div>';
    }
}

function updateTestStatus(testName, status) {
    const statusElement = document.getElementById(`status-${testName}`);
    const buttonElement = statusElement.closest('.list-group-item');

    // Nettoyer les classes précédentes
    buttonElement.classList.remove('test-passed', 'test-failed', 'test-running');

    switch (status) {
        case 'passed':
            statusElement.innerHTML = '<i class="bi bi-check-circle text-success"></i>';
            buttonElement.classList.add('test-passed');
            break;
        case 'failed':
            statusElement.innerHTML = '<i class="bi bi-x-circle text-danger"></i>';
            buttonElement.classList.add('test-failed');
            break;
        case 'running':
            statusElement.innerHTML = '<div class="spinner-border spinner-border-sm text-warning" role="status"></div>';
            buttonElement.classList.add('test-running');
            break;
        default:
            statusElement.innerHTML = '';
    }
}

function displayTestResult(result) {
    const resultHtml = createTestResultHtml(result);
    document.getElementById('test-results').innerHTML = resultHtml;
}

function displayAllResults(allResults) {
    let html = `
        <div class="mb-3">
            <h6>Résumé Global</h6>
            <div class="row">
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h5 class="text-primary">${allResults.total_tests}</h5>
                            <small>Tests Total</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h5>${allResults.passed}</h5>
                            <small>Réussis</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h5>${allResults.failed}</h5>
                            <small>Échoués</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    html += '<h6>Détails des Tests</h6>';
    allResults.results.forEach(result => {
        html += createTestResultHtml(result);
    });

    document.getElementById('test-results').innerHTML = html;
}

function createTestResultHtml(result) {
    const statusClass = result.passed ? 'passed' : 'failed';
    const statusIcon = result.passed ? 'bi-check-circle text-success' : 'bi-x-circle text-danger';

    let html = `
        <div class="test-result ${statusClass}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">
                    <i class="bi ${statusIcon} me-2"></i>
                    ${result.name}
                </h6>
                <span class="badge ${result.passed ? 'bg-success' : 'bg-danger'}">
                    ${result.passed ? 'RÉUSSI' : 'ÉCHOUÉ'}
                </span>
            </div>
            <p class="mb-2">${result.description}</p>
            <p class="mb-2"><strong>Requête:</strong> "${result.query}"</p>
    `;

    if (!result.passed && result.errors && result.errors.length > 0) {
        html += '<div class="mb-2"><strong>Erreurs:</strong>';
        result.errors.forEach(error => {
            html += `<div class="error-item">• ${error}</div>`;
        });
        html += '</div>';
    }

    if (result.analysis_result) {
        html += `
            <div class="test-details">
                <strong>Analyse IA:</strong><br>
                Action: ${result.analysis_result.action || 'N/A'}<br>
                Mots-clés: ${(result.analysis_result.keywords || []).join(', ') || 'N/A'}<br>
                Filtres: ${JSON.stringify(result.analysis_result.filters || {})}<br>
                Limite: ${result.analysis_result.limit || 'N/A'}
            </div>
        `;
    }

    html += '</div>';
    return html;
}

function updateSummary() {
    const total = Object.keys(testResults).length;
    const passed = Object.values(testResults).filter(r => r.passed).length;
    const failed = total - passed;

    if (total === 0) {
        document.getElementById('test-summary').className = 'badge bg-secondary';
        document.getElementById('test-summary').textContent = 'Aucun test exécuté';
    } else {
        const percentage = Math.round((passed / total) * 100);
        const badgeClass = percentage === 100 ? 'bg-success' : percentage >= 70 ? 'bg-warning' : 'bg-danger';

        document.getElementById('test-summary').className = `badge ${badgeClass}`;
        document.getElementById('test-summary').textContent = `${passed}/${total} réussis (${percentage}%)`;
    }
}

// Exécuter automatiquement quelques tests de base au chargement
document.addEventListener('DOMContentLoaded', function() {
    // Optionnel: lancer quelques tests automatiquement
    // runSingleTest('test_count_all_records');
});
</script>
@endsection