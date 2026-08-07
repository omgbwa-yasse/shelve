// communications.js - Fonctions pour la gestion des communications

document.addEventListener('DOMContentLoaded', function() {
    initCommunicationsManager();
});

/**
 * Initialise toutes les fonctionnalités des communications
 */
function initCommunicationsManager() {
    initExportButton();
    initPrintButton();
    initCheckAllButton();
    initCollapseHandling();
}

/**
 * Initialise le bouton d'exportation
 */
function initExportButton() {
    const exportBtn = document.getElementById('exportBtn');
    if (!exportBtn) return;

    exportBtn.addEventListener('click', function(e) {
        e.preventDefault();
        let selectedCommunications = getSelectedCommunicationIds();

        if (selectedCommunications.length === 0) {
            alert(getTranslation('pleaseSelectAtLeastOneCommunicationToExport'));
            return;
        }

        const exportRoute = this.getAttribute('data-route');
        window.location.href = `${exportRoute}?communications=${selectedCommunications.join(',')}`;
    });
}

/**
 * Initialise le bouton d'impression
 */
function initPrintButton() {
    const printBtn = document.getElementById('printBtn');
    if (!printBtn) return;

    printBtn.addEventListener('click', function(e) {
        e.preventDefault();
        let selectedCommunications = getSelectedCommunicationIds();

        if (selectedCommunications.length === 0) {
            alert(getTranslation('pleaseSelectAtLeastOneCommunicationToPrint'));
            return;
        }

        const printRoute = this.getAttribute('data-route');
        window.location.href = `${printRoute}?communications=${selectedCommunications.join(',')}`;
    });
}

/**
 * Initialise le bouton "Tout cocher/décocher"
 */
function initCheckAllButton() {
    const checkAllBtn = document.getElementById('checkAllBtn');
    if (!checkAllBtn) return;

    checkAllBtn.addEventListener('click', function(e) {
        e.preventDefault();
        let checkboxes = document.querySelectorAll('input[type="checkbox"][name="selected_communication[]"]');
        let allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);

        checkboxes.forEach(function(checkbox) {
            checkbox.checked = !allChecked;
        });

        this.innerHTML = allChecked ? 
            '<i class="bi bi-check-square me-1"></i>' + getTranslation('checkAll') : 
            '<i class="bi bi-square me-1"></i>' + getTranslation('uncheckAll');
    });
}

/**
 * Initialise la gestion des éléments collapse (expansion/réduction des détails)
 */
function initCollapseHandling() {
    const collapseElements = document.querySelectorAll('.collapse');
    collapseElements.forEach(collapse => {
        collapse.addEventListener('show.bs.collapse', function () {
            const button = document.querySelector(`[data-bs-target="#${this.id}"]`);
            if (button && button.querySelector('i')) {
                button.querySelector('i').classList.replace('bi-chevron-down', 'bi-chevron-up');
            }
        });
        
        collapse.addEventListener('hide.bs.collapse', function () {
            const button = document.querySelector(`[data-bs-target="#${this.id}"]`);
            if (button && button.querySelector('i')) {
                button.querySelector('i').classList.replace('bi-chevron-up', 'bi-chevron-down');
            }
        });
    });

    // Gestion du "voir plus / voir moins" pour le contenu
    document.querySelectorAll('.content-toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-target');
            const targetElement = document.getElementById(targetId);
            const fullText = this.getAttribute('data-full-text');

            if (this.textContent === getTranslation('seeMore')) {
                targetElement.textContent = fullText;
                this.textContent = getTranslation('seeLess');
            } else {
                targetElement.textContent = fullText.substr(0, 200) + '...';
                this.textContent = getTranslation('seeMore');
            }
        });
    });
}

/**
 * Récupère les IDs des communications sélectionnées
 * @returns {Array} Tableau d'IDs
 */
function getSelectedCommunicationIds() {
    return Array.from(document.querySelectorAll('input[type="checkbox"][name="selected_communication[]"]:checked'))
        .map(checkbox => checkbox.value);
}

/**
 * Fonction helper pour les traductions
 * @param {string} key Clé de traduction
 * @returns {string} Texte traduit ou clé si non trouvée
 */
function getTranslation(key) {
    // Simuler un système de traduction basique
    const translations = {
        'pleaseSelectAtLeastOneCommunicationToExport': 'Veuillez sélectionner au moins une communication à exporter.',
        'pleaseSelectAtLeastOneCommunicationToPrint': 'Veuillez sélectionner au moins une communication à imprimer.',
        'checkAll': 'Tout cocher',
        'uncheckAll': 'Tout décocher',
        'seeMore': 'Voir plus',
        'seeLess': 'Voir moins'
    };
    
    return translations[key] || key;
}

// Exposer la fonction getSelectedCommunicationIds pour dollies.js
window.getSelectedCommunicationIds = getSelectedCommunicationIds;