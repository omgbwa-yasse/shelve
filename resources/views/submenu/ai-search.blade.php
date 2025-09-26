<div class="submenu-container py-2">
    @php
    use App\Helpers\SubmenuPermissions;
    @endphp

    <!-- Styles partagés via _submenu.scss -->

    <!-- Section Recherche Rapide -->
    <div class="submenu-section">
        <div class="submenu-heading">
            <i class="bi bi-lightning-charge"></i> {{ __('Quick Searches') }}
        </div>
        <div class="submenu-content" id="quickSearchMenu">

            <div class="submenu-category-title">{{ __('Statistiques') }}</div>
            <div class="submenu-item">
                <button class="submenu-link quick-search-btn" data-query="combien d'éléments au total">
                    <i class="bi bi-123"></i> {{ __('Count All') }}
                </button>
            </div>
            <div class="submenu-item">
                <button class="submenu-link quick-search-btn" data-query="combien d'éléments en 2024">
                    <i class="bi bi-calendar"></i> {{ __('Count 2024') }}
                </button>
            </div>
            <div class="submenu-item">
                <button class="submenu-link quick-search-btn" data-query="combien d'éléments ajoutés aujourd'hui">
                    <i class="bi bi-calendar-day"></i> {{ __('Count Today') }}
                </button>
            </div>

            <div class="submenu-divider"></div>
            <div class="submenu-category-title">{{ __('Éléments récents') }}</div>
            <div class="submenu-item">
                <button class="submenu-link quick-search-btn" data-query="les 10 derniers documents">
                    <i class="bi bi-file-earmark"></i> {{ __('Recent Records') }}
                </button>
            </div>
            <div class="submenu-item">
                <button class="submenu-link quick-search-btn" data-query="mails récents">
                    <i class="bi bi-envelope"></i> {{ __('Recent Mails') }}
                </button>
            </div>
            <div class="submenu-item">
                <button class="submenu-link quick-search-btn" data-query="communications récentes">
                    <i class="bi bi-chat-dots"></i> {{ __('Recent Communications') }}
                </button>
            </div>
            <div class="submenu-item">
                <button class="submenu-link quick-search-btn" data-query="bordereaux récents">
                    <i class="bi bi-arrow-left-right"></i> {{ __('Recent Transfers') }}
                </button>
            </div>

            <div class="submenu-divider"></div>
            <div class="submenu-category-title">{{ __('Recherches avancées') }}</div>
            <div class="submenu-item">
                <button class="submenu-link quick-search-btn" data-query="mails urgents">
                    <i class="bi bi-exclamation-triangle"></i> {{ __('Urgent Mails') }}
                </button>
            </div>
            <div class="submenu-item">
                <button class="submenu-link quick-search-btn" data-query="communications en cours">
                    <i class="bi bi-play-circle"></i> {{ __('Active Communications') }}
                </button>
            </div>
            <div class="submenu-item">
                <button class="submenu-link quick-search-btn" data-query="bordereaux approuvés">
                    <i class="bi bi-check-circle"></i> {{ __('Approved Transfers') }}
                </button>
            </div>
            <div class="submenu-item">
                <button class="submenu-link quick-search-btn" data-query="documents avec terme contrat">
                    <i class="bi bi-tags"></i> {{ __('Contract Documents') }}
                </button>
            </div>

        </div>
    </div>

    <!-- Section Historique -->
    <div class="submenu-section">
        <div class="submenu-heading">
            <i class="bi bi-clock-history"></i> {{ __('Search History') }}
        </div>
        <div class="submenu-content" id="historyMenu">

            <div class="submenu-category-title">{{ __('Historique récent') }}</div>
            <div class="search-history-container" id="searchHistoryContainer">
                <div class="submenu-item">
                    <small class="text-muted">{{ __('No search history yet') }}</small>
                </div>
            </div>

            <div class="submenu-divider"></div>
            <div class="submenu-item">
                <button class="submenu-link" id="clearHistoryBtn">
                    <i class="bi bi-trash"></i> {{ __('Clear History') }}
                </button>
            </div>

        </div>
    </div>


    <!-- Section Actions Rapides -->
    <div class="submenu-section">
        <div class="submenu-heading">
            <i class="bi bi-tools"></i> {{ __('Quick Actions') }}
        </div>
        <div class="submenu-content" id="actionsMenu">

            <div class="submenu-category-title">{{ __('Gestion de session') }}</div>
            <div class="submenu-item">
                <button class="submenu-link" id="exportChatSidebarBtn">
                    <i class="bi bi-download"></i> {{ __('Export Chat') }}
                </button>
            </div>
            <div class="submenu-item">
                <button class="submenu-link" id="clearChatSidebarBtn">
                    <i class="bi bi-trash2"></i> {{ __('Clear Chat') }}
                </button>
            </div>

            <div class="submenu-divider"></div>
            <div class="submenu-category-title">{{ __('Configuration IA') }}</div>
            <div class="submenu-item">
                <a class="submenu-link" href="{{ route('ai-search.test.interface') }}">
                    <i class="bi bi-bug"></i> {{ __('Test System') }}
                </a>
            </div>
            <div class="submenu-item">
                <button class="submenu-link" id="aiSettingsBtn">
                    <i class="bi bi-gear"></i> {{ __('AI Settings') }}
                </button>
            </div>

        </div>
    </div>

</div>

<style>
/* Styles spécifiques pour la sidebar AI */
.quick-search-btn {
    border: none;
    background: none;
    text-align: left;
    width: 100%;
    cursor: pointer;
}

.quick-search-btn:hover {
    background-color: rgba(0, 123, 255, 0.1);
}

.search-history-container {
    max-height: 200px;
    overflow-y: auto;
}

.history-item {
    padding: 5px 10px;
    margin: 2px 0;
    background: #f8f9fa;
    border-radius: 3px;
    cursor: pointer;
    font-size: 12px;
}

.history-item:hover {
    background: #e9ecef;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variables globales
    let searchHistory = JSON.parse(localStorage.getItem('ai-search-history') || '[]');

    // Fonctionnalité de collapse pour les sous-menus
    const headings = document.querySelectorAll('.submenu-heading');

    headings.forEach(function(heading) {
        heading.addEventListener('click', function() {
            const content = this.nextElementSibling;

            if (content && content.classList.contains('submenu-content')) {
                content.classList.toggle('collapsed');
                this.classList.toggle('collapsed');
            }
        });
    });

    // Gestion des boutons de recherche rapide
    document.querySelectorAll('.quick-search-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const query = this.getAttribute('data-query');
            if (window.sendMessageFromSidebar) {
                window.sendMessageFromSidebar(query);
            }
        });
    });


    // Actions rapides
    document.getElementById('clearChatSidebarBtn')?.addEventListener('click', function() {
        if (window.clearChatFromSidebar) {
            window.clearChatFromSidebar();
        }
    });

    document.getElementById('exportChatSidebarBtn')?.addEventListener('click', function() {
        if (window.exportChatFromSidebar) {
            window.exportChatFromSidebar();
        }
    });

    document.getElementById('clearHistoryBtn')?.addEventListener('click', function() {
        clearSearchHistory();
    });


    // Gestion de l'historique de recherche
    function addToSearchHistory(query) {
        const historyItem = {
            query: query,
            timestamp: Date.now(),
            searchType: window.currentSearchType || 'records'
        };

        searchHistory.unshift(historyItem);
        searchHistory = searchHistory.slice(0, 10); // Garder seulement les 10 derniers

        localStorage.setItem('ai-search-history', JSON.stringify(searchHistory));
        renderSearchHistory();
    }

    function renderSearchHistory() {
        const container = document.getElementById('searchHistoryContainer');
        if (!container) return;

        if (searchHistory.length === 0) {
            container.innerHTML = '<div class="submenu-item"><small class="text-muted">{{ __("No search history yet") }}</small></div>';
            return;
        }

        let html = '';
        searchHistory.forEach(item => {
            const timeAgo = getTimeAgo(item.timestamp);
            html += `
                <div class="history-item" data-query="${item.query}">
                    <div style="font-weight: 500;">${item.query.substring(0, 30)}${item.query.length > 30 ? '...' : ''}</div>
                    <div style="color: #6c757d; font-size: 10px;">${timeAgo} - ${getTypeName(item.searchType)}</div>
                </div>
            `;
        });

        container.innerHTML = html;

        // Ajouter les event listeners pour l'historique
        container.querySelectorAll('.history-item').forEach(item => {
            item.addEventListener('click', function() {
                const query = this.getAttribute('data-query');
                if (window.sendMessageFromSidebar) {
                    window.sendMessageFromSidebar(query);
                }
            });
        });
    }

    function clearSearchHistory() {
        searchHistory = [];
        localStorage.removeItem('ai-search-history');
        renderSearchHistory();
    }

    function getTimeAgo(timestamp) {
        const diff = Date.now() - timestamp;
        const minutes = Math.floor(diff / 60000);
        const hours = Math.floor(minutes / 60);
        const days = Math.floor(hours / 24);

        if (days > 0) return `${days}j`;
        if (hours > 0) return `${hours}h`;
        if (minutes > 0) return `${minutes}m`;
        return "maintenant";
    }

    function getTypeName(type) {
        const types = {
            'records': 'Documents',
            'mails': 'Mails',
            'communications': 'Communications',
            'slips': 'Transferts'
        };
        return types[type] || type;
    }

    // Exposer des fonctions globales pour l'interaction avec la page principale
    window.addToSearchHistorySidebar = addToSearchHistory;

    // Initialisation
    renderSearchHistory();
});
</script>