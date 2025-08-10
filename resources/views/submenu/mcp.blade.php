<div class="submenu-container py-2">
    <!-- Styles partagés via _submenu.scss -->

    <!-- Dashboard Principal -->
    <div class="submenu-section">
        <div class="submenu-heading mcp-heading">
            <i class="bi bi-robot"></i>
            {{ __('MCP Dashboard') ?? 'MCP Dashboard' }}
        </div>
        <div class="submenu-content">
            <div class="submenu-item">
                <a href="/admin/mcp" class="submenu-link {{ request()->is('admin/mcp') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>
                    {{ __('Vue d\'ensemble') ?? 'Vue d\'ensemble' }}
                    <div class="status-indicator online" id="mcpSystemStatus"></div>
                </a>
            </div>
            <div class="submenu-item">
                <a href="/admin/mcp/statistics" class="submenu-link {{ request()->is('admin/mcp/statistics') ? 'active' : '' }}">
                    <i class="bi bi-graph-up"></i>
                    {{ __('Statistiques') ?? 'Statistiques' }}
                </a>
            </div>
            {{-- Lien désactivé: page Historique non implémentée --}}
        </div>
    </div>

    <!-- Fonctionnalités MCP -->
    <div class="submenu-section">
        <div class="submenu-heading feature-heading">
            <i class="bi bi-gear"></i>
            {{ __('Fonctionnalités IA') ?? 'Fonctionnalités IA' }}
        </div>
        <div class="submenu-content">
            {{-- Liens de fonctionnalités non implémentés masqués pour l'instant --}}
        </div>
    </div>

    <!-- Monitoring et Logs -->
    <div class="submenu-section">
        <div class="submenu-heading monitoring-heading">
            <i class="bi bi-activity"></i>
            {{ __('Surveillance') ?? 'Surveillance' }}
        </div>
        <div class="submenu-content">
            <div class="submenu-item">
                <a href="/admin/mcp/health-check" class="submenu-link {{ request()->is('admin/mcp/health-check') ? 'active' : '' }}">
                    <i class="bi bi-heart-pulse"></i>
                    {{ __('État de Santé') ?? 'État de Santé' }}
                    <div class="status-indicator online" id="healthStatus"></div>
                </a>
            </div>
            {{-- Liens monitoring non nécessaires masqués (queue-monitor, logs, performance) --}}
        </div>
    </div>

    <!-- Administration -->
    <div class="submenu-section">
        <div class="submenu-heading admin-heading">
            <i class="bi bi-tools"></i>
            {{ __('Administration') ?? 'Administration' }}
        </div>
        <div class="submenu-content">
            <div class="submenu-item">
                <a href="/admin/mcp/configuration" class="submenu-link {{ request()->is('admin/mcp/configuration') ? 'active' : '' }}">
                    <i class="bi bi-sliders"></i>
                    {{ __('Configuration') ?? 'Configuration' }}
                </a>
            </div>
            {{-- Lien gestion des modèles masqué (non prioritaire) --}}
        </div>
    </div>

    <!-- Actions Rapides -->
    <div class="submenu-section">
        <div class="submenu-heading">
            <i class="bi bi-lightning"></i>
            {{ __('Actions Rapides') ?? 'Actions Rapides' }}
        </div>
        <div class="submenu-content">
            <div class="submenu-item">
                <button type="button" class="submenu-link btn btn-link p-0 text-start" onclick="testMcpConnection()">
                    <i class="bi bi-wifi"></i>
                    {{ __('Test Connexion') ?? 'Test Connexion' }}
                </button>
            </div>
            <div class="submenu-item">
                <button type="button" class="submenu-link btn btn-link p-0 text-start" onclick="clearMcpCache()">
                    <i class="bi bi-arrow-clockwise"></i>
                    {{ __('Vider Cache') ?? 'Vider Cache' }}
                </button>
            </div>
            <div class="submenu-item">
                <button type="button" class="submenu-link btn btn-link p-0 text-start" onclick="openBatchModal()">
                    <i class="bi bi-play-circle"></i>
                    {{ __('Traitement Express') ?? 'Traitement Express' }}
                </button>
            </div>
            <div class="submenu-item">
                <a href="/admin/mcp/documentation" class="submenu-link {{ request()->is('admin/mcp/documentation') ? 'active' : '' }}">
                    <i class="bi bi-book"></i>
                    {{ __('Documentation') ?? 'Documentation' }}
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mettre à jour les indicateurs de statut
    updateMcpStatusIndicators();

    // Actualiser toutes les 30 secondes
    setInterval(updateMcpStatusIndicators, 30000);
});

function updateMcpStatusIndicators() {
    // Vérifier l'état de santé MCP (utilise route admin interne)
    fetch('/admin/mcp/actions/system-info')
        .then(response => response.json())
        .then(data => {
            const ok = !!data.php_version;
            updateStatusIndicator('mcpSystemStatus', ok);
            updateStatusIndicator('healthStatus', ok);
            updateStatusIndicator('ollamaStatus', ok);
        })
        .catch(() => {
            updateStatusIndicator('mcpSystemStatus', false);
            updateStatusIndicator('healthStatus', false);
            updateStatusIndicator('ollamaStatus', false);
        });

    // Mettre à jour le compteur de jobs
    fetch('/admin/mcp/actions/queue-stats')
        .then(response => response.json())
        .then(data => {
            updateBadge('queueCount', data.total_pending || 0);
            updateBadge('activeJobs', data.failed_jobs || 0);
        })
        .catch(() => {
            updateBadge('queueCount', '?');
            updateBadge('activeJobs', '?');
        });
}

function updateStatusIndicator(elementId, isOnline) {
    const indicator = document.getElementById(elementId);
    if (indicator) {
        indicator.className = 'status-indicator ' + (isOnline ? 'online' : 'offline');
    }
}

function updateBadge(elementId, value) {
    const badge = document.getElementById(elementId);
    if (badge) {
        badge.textContent = value;
        badge.className = 'submenu-badge ' + (value > 0 ? 'warning' : 'success');
    }
}

function testMcpConnection() {
    const link = event.target.closest('.submenu-link');
    const originalText = link.innerHTML;

    link.innerHTML = '<i class="bi bi-hourglass-split"></i> Test en cours...';

    fetch('/api/mcp/health')
        .then(response => response.json())
        .then(data => {
            const success = data.overall_status === 'ok';
            link.innerHTML = `<i class="bi bi-${success ? 'check-circle text-success' : 'x-circle text-danger'}"></i> ${success ? 'Connexion OK' : 'Connexion KO'}`;

            setTimeout(() => {
                link.innerHTML = originalText;
            }, 3000);
        })
        .catch(() => {
            link.innerHTML = '<i class="bi bi-x-circle text-danger"></i> Erreur';
            setTimeout(() => {
                link.innerHTML = originalText;
            }, 3000);
        });
}

function clearMcpCache() {
    if (confirm('Vider le cache MCP ? Cette action supprimera tous les résultats mis en cache.')) {
        const link = event.target.closest('.submenu-link');
        const originalText = link.innerHTML;

        link.innerHTML = '<i class="bi bi-hourglass-split"></i> Nettoyage...';

        fetch('/api/mcp/cache/clear', { method: 'POST' })
            .then(response => response.json())
            .then(data => {
                link.innerHTML = '<i class="bi bi-check-circle text-success"></i> Cache vidé';
                setTimeout(() => {
                    link.innerHTML = originalText;
                }, 3000);
            })
            .catch(() => {
                link.innerHTML = '<i class="bi bi-x-circle text-danger"></i> Erreur';
                setTimeout(() => {
                    link.innerHTML = originalText;
                }, 3000);
            });
    }
}

function openBatchModal() {
    // Rediriger vers la page des records avec la modale ouverte
    window.location.href = '/records?open_mcp_batch=1';
}
</script>
