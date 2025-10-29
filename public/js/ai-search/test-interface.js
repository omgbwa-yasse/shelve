/**
 * Gestionnaire pour l'interface de test AI Search
 */
class AISearchTestInterface {
    constructor() {
        this.testResults = {};
        this.init();
    }

    init() {
        this.bindEvents();
    }

    bindEvents() {
        // Délégation d'événements pour les boutons de test
        document.addEventListener('click', (e) => {
            if (e.target.matches('[onclick^="runSingleTest"]')) {
                e.preventDefault();
                const testName = this.extractTestName(e.target);
                if (testName) {
                    this.runSingleTest(testName);
                }
            }

            if (e.target.matches('[onclick="runAllTests()"]')) {
                e.preventDefault();
                this.runAllTests();
            }
        });
    }

    extractTestName(element) {
        const onclick = element.getAttribute('onclick');
        const match = onclick.match(/runSingleTest\('(.+?)'\)/);
        return match ? match[1] : null;
    }

    async runSingleTest(testName) {
        this.updateTestStatus(testName, 'running');

        try {
            const response = await fetch(`/ai-search/test/${testName}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const result = await response.json();
            this.testResults[testName] = result;

            this.updateTestStatus(testName, result.passed ? 'passed' : 'failed');
            this.displayTestResult(result);
            this.updateSummary();

        } catch (error) {
            console.error('Error running test:', error);
            this.updateTestStatus(testName, 'failed');
        }
    }

    async runAllTests() {
        this.testResults = {};
        const resultsContainer = document.getElementById('test-results');

        if (resultsContainer) {
            resultsContainer.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Exécution des tests...</p></div>';
        }

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
                this.testResults[result.name] = result;
                this.updateTestStatus(result.name, result.passed ? 'passed' : 'failed');
            });

            this.displayAllResults(allResults);
            this.updateSummary();

        } catch (error) {
            console.error('Error running all tests:', error);
            if (resultsContainer) {
                resultsContainer.innerHTML = '<div class="alert alert-danger">Erreur lors de l\'exécution des tests</div>';
            }
        }
    }

    updateTestStatus(testName, status) {
        const statusElement = document.getElementById(`status-${testName}`);
        if (!statusElement) return;

        const buttonElement = statusElement.closest('.list-group-item');
        if (!buttonElement) return;

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

    displayTestResult(result) {
        const resultHtml = this.createTestResultHtml(result);
        const resultsContainer = document.getElementById('test-results');
        if (resultsContainer) {
            resultsContainer.innerHTML = resultHtml;
        }
    }

    displayAllResults(allResults) {
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
            html += this.createTestResultHtml(result);
        });

        const resultsContainer = document.getElementById('test-results');
        if (resultsContainer) {
            resultsContainer.innerHTML = html;
        }
    }

    createTestResultHtml(result) {
        const statusClass = result.passed ? 'passed' : 'failed';
        const statusIcon = result.passed ? 'bi-check-circle text-success' : 'bi-x-circle text-danger';

        let html = `
            <div class="test-result ${statusClass}">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">
                        <i class="bi ${statusIcon} me-2"></i>
                        ${this.escapeHtml(result.name)}
                    </h6>
                    <span class="badge ${result.passed ? 'bg-success' : 'bg-danger'}">
                        ${result.passed ? 'RÉUSSI' : 'ÉCHOUÉ'}
                    </span>
                </div>
                <p class="mb-2">${this.escapeHtml(result.description || '')}</p>
                <p class="mb-2"><strong>Requête:</strong> "${this.escapeHtml(result.query || '')}"</p>
        `;

        if (!result.passed && result.errors && result.errors.length > 0) {
            html += '<div class="mb-2"><strong>Erreurs:</strong>';
            result.errors.forEach(error => {
                html += `<div class="error-item">• ${this.escapeHtml(error)}</div>`;
            });
            html += '</div>';
        }

        if (result.analysis_result) {
            html += `
                <div class="test-details">
                    <strong>Analyse IA:</strong><br>
                    Action: ${this.escapeHtml(result.analysis_result.action || 'N/A')}<br>
                    Mots-clés: ${this.escapeHtml((result.analysis_result.keywords || []).join(', ') || 'N/A')}<br>
                    Filtres: ${this.escapeHtml(JSON.stringify(result.analysis_result.filters || {}))}<br>
                    Limite: ${this.escapeHtml(result.analysis_result.limit || 'N/A')}
                </div>
            `;
        }

        html += '</div>';
        return html;
    }

    updateSummary() {
        const total = Object.keys(this.testResults).length;
        const passed = Object.values(this.testResults).filter(r => r.passed).length;
        const summaryElement = document.getElementById('test-summary');

        if (!summaryElement) return;

        if (total === 0) {
            summaryElement.className = 'badge bg-secondary';
            summaryElement.textContent = 'Aucun test exécuté';
        } else {
            const percentage = Math.round((passed / total) * 100);
            const badgeClass = percentage === 100 ? 'bg-success' : percentage >= 70 ? 'bg-warning' : 'bg-danger';

            summaryElement.className = `badge ${badgeClass}`;
            summaryElement.textContent = `${passed}/${total} réussis (${percentage}%)`;
        }
    }

    escapeHtml(text) {
        if (typeof text !== 'string') return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Fonctions globales pour la rétrocompatibilité
window.runSingleTest = function(testName) {
    if (window.testInterface) {
        window.testInterface.runSingleTest(testName);
    }
};

window.runAllTests = function() {
    if (window.testInterface) {
        window.testInterface.runAllTests();
    }
};

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    window.testInterface = new AISearchTestInterface();
});
