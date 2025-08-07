<div class="submenu-container py-2">
    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        .submenu-container {
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
        }

        .submenu-heading {
            background-color: #4285f4;
            color: white;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 6px;
            font-weight: 500;
            font-size: 13px;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .submenu-heading:hover {
            background-color: #3367d6;
        }

        .submenu-heading i {
            margin-right: 8px;
            font-size: 14px;
        }

        .submenu-content { 
            padding: 0 0 8px 12px; 
            margin-bottom: 8px; 
            display: block; 
        }

        .submenu-item {
            margin-bottom: 2px;
        }

        .submenu-link {
            display: flex;
            align-items: center;
            padding: 4px 8px;
            color: #202124;
            text-decoration: none;
            border-radius: 4px;
            transition: all 0.2s ease;
            font-size: 12px;
            min-height: 24px;
        }

        .submenu-link:hover {
            background-color: #f8f9fa;
            color: #1a73e8;
            text-decoration: none;
        }

        .submenu-link i {
            margin-right: 6px;
            font-size: 12px;
            width: 12px;
            text-align: center;
        }

        .submenu-link.active {
            background-color: #e8f0fe;
            color: #1a73e8;
            font-weight: 500;
        }

        .status-indicator {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            margin-left: auto;
            margin-right: 4px;
        }

        .status-indicator.online {
            background-color: #34a853;
        }

        .status-indicator.offline {
            background-color: #ea4335;
        }

        .status-indicator.warning {
            background-color: #fbbc04;
        }

        .mcp-heading {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .mcp-heading:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }

        .feature-heading {
            background-color: #34a853;
        }

        .feature-heading:hover {
            background-color: #2d8f47;
        }

        .admin-heading {
            background-color: #ea4335;
        }

        .admin-heading:hover {
            background-color: #d33b2c;
        }

        .monitoring-heading {
            background-color: #fbbc04;
        }

        .monitoring-heading:hover {
            background-color: #f29900;
        }

        .submenu-badge {
            background-color: #ff4444;
            color: white;
            border-radius: 10px;
            padding: 1px 6px;
            font-size: 10px;
            margin-left: auto;
            font-weight: 500;
        }

        .submenu-badge.success {
            background-color: #00C851;
        }

        .submenu-badge.warning {
            background-color: #ffbb33;
        }
    </style>

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
            <div class="submenu-item">
                <a href="/admin/mcp/history" class="submenu-link {{ request()->is('admin/mcp/history') ? 'active' : '' }}">
                    <i class="bi bi-clock-history"></i>
                    {{ __('Historique') ?? 'Historique' }}
                </a>
            </div>
        </div>
    </div>

    <!-- Fonctionnalités MCP -->
    <div class="submenu-section">
        <div class="submenu-heading feature-heading">
            <i class="bi bi-gear"></i>
            {{ __('Fonctionnalités IA') ?? 'Fonctionnalités IA' }}
        </div>
        <div class="submenu-content">
            <div class="submenu-item">
                <a href="/admin/mcp/title-reformulation" class="submenu-link {{ request()->is('admin/mcp/title-reformulation') ? 'active' : '' }}">
                    <i class="bi bi-magic"></i>
                    {{ __('Reformulation Titre') ?? 'Reformulation Titre' }}
                    <span class="submenu-badge success">ISAD(G)</span>
                </a>
            </div>
            <div class="submenu-item">
                <a href="/admin/mcp/thesaurus-indexing" class="submenu-link {{ request()->is('admin/mcp/thesaurus-indexing') ? 'active' : '' }}">
                    <i class="bi bi-tags"></i>
                    {{ __('Indexation Thésaurus') ?? 'Indexation Thésaurus' }}
                    <span id="thesaurusCount" class="submenu-badge warning">304</span>
                </a>
            </div>
            <div class="submenu-item">
                <a href="/admin/mcp/content-summary" class="submenu-link {{ request()->is('admin/mcp/content-summary') ? 'active' : '' }}">
                    <i class="bi bi-file-text"></i>
                    {{ __('Résumé ISAD(G)') ?? 'Résumé ISAD(G)' }}
                    <span class="submenu-badge success">3.3.1</span>
                </a>
            </div>
            <div class="submenu-item">
                <a href="/admin/mcp/batch-processing" class="submenu-link {{ request()->is('admin/mcp/batch-processing') ? 'active' : '' }}">
                    <i class="bi bi-layers"></i>
                    {{ __('Traitement par Lots') ?? 'Traitement par Lots' }}
                    <span id="queueCount" class="submenu-badge">0</span>
                </a>
            </div>
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
            <div class="submenu-item">
                <a href="/admin/mcp/queue-monitor" class="submenu-link {{ request()->is('admin/mcp/queue-monitor') ? 'active' : '' }}">
                    <i class="bi bi-list-task"></i>
                    {{ __('Files d\'attente') ?? 'Files d\'attente' }}
                    <span id="activeJobs" class="submenu-badge">0</span>
                </a>
            </div>
            <div class="submenu-item">
                <a href="/admin/mcp/logs" class="submenu-link {{ request()->is('admin/mcp/logs') ? 'active' : '' }}">
                    <i class="bi bi-journal-text"></i>
                    {{ __('Logs MCP') ?? 'Logs MCP' }}
                </a>
            </div>
            <div class="submenu-item">
                <a href="/admin/mcp/performance" class="submenu-link {{ request()->is('admin/mcp/performance') ? 'active' : '' }}">
                    <i class="bi bi-speedometer"></i>
                    {{ __('Performance') ?? 'Performance' }}
                </a>
            </div>
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
            <div class="submenu-item">
                <a href="/admin/mcp/models" class="submenu-link {{ request()->is('admin/mcp/models') ? 'active' : '' }}">
                    <i class="bi bi-cpu"></i>
                    {{ __('Modèles Ollama') ?? 'Modèles Ollama' }}
                    <div class="status-indicator online" id="ollamaStatus"></div>
                </a>
            </div>
            <div class="submenu-item">
                <a href="/admin/mcp/users" class="submenu-link {{ request()->is('admin/mcp/users') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    {{ __('Utilisateurs') ?? 'Utilisateurs' }}
                </a>
            </div>
            <div class="submenu-item">
                <a href="/admin/mcp/maintenance" class="submenu-link {{ request()->is('admin/mcp/maintenance') ? 'active' : '' }}">
                    <i class="bi bi-wrench"></i>
                    {{ __('Maintenance') ?? 'Maintenance' }}
                </a>
            </div>
        </div>
    </div>

    <!-- Actions Rapides -->
    <div class="submenu-section">
        <div class="submenu-heading" style="background-color: #9c27b0;">
            <i class="bi bi-lightning"></i>
            {{ __('Actions Rapides') ?? 'Actions Rapides' }}
        </div>
        <div class="submenu-content">
            <div class="submenu-item">
                <a href="#" class="submenu-link" onclick="testMcpConnection()">
                    <i class="bi bi-wifi"></i>
                    {{ __('Test Connexion') ?? 'Test Connexion' }}
                </a>
            </div>
            <div class="submenu-item">
                <a href="#" class="submenu-link" onclick="clearMcpCache()">
                    <i class="bi bi-arrow-clockwise"></i>
                    {{ __('Vider Cache') ?? 'Vider Cache' }}
                </a>
            </div>
            <div class="submenu-item">
                <a href="#" class="submenu-link" onclick="openBatchModal()">
                    <i class="bi bi-play-circle"></i>
                    {{ __('Traitement Express') ?? 'Traitement Express' }}
                </a>
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
    // Vérifier l'état de santé MCP
    fetch('/api/mcp/health')
        .then(response => response.json())
        .then(data => {
            updateStatusIndicator('mcpSystemStatus', data.overall_status === 'ok');
            updateStatusIndicator('healthStatus', data.overall_status === 'ok');
            updateStatusIndicator('ollamaStatus', data.components?.ollama_connection?.status === 'ok');
        })
        .catch(() => {
            updateStatusIndicator('mcpSystemStatus', false);
            updateStatusIndicator('healthStatus', false);
            updateStatusIndicator('ollamaStatus', false);
        });
    
    // Mettre à jour le compteur de jobs
    fetch('/api/mcp/queue/status')
        .then(response => response.json())
        .then(data => {
            updateBadge('queueCount', data.pending || 0);
            updateBadge('activeJobs', data.active || 0);
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