// records.js - Gestion des fonctionnalités pour les records

document.addEventListener('DOMContentLoaded', function() {
    initRecordsManager();
});

/**
 * Initialise toutes les fonctionnalités de gestion des records
 */
function initRecordsManager() {
    // Initialisation des boutons principaux
    initExportButton();
    initPrintButton();
    initCheckAllButton();
    initTransferButton();
    initCommunicateButton();
    initContentToggleButtons();
}

/**
 * Initialise le bouton d'exportation et le modal associé
 */
function initExportButton() {
    const exportBtn = document.getElementById('exportBtn');
    const confirmExportBtn = document.getElementById('confirmExport');

    if (!exportBtn || !confirmExportBtn) return;

    exportBtn.addEventListener('click', function(e) {
        e.preventDefault();
        let checkedRecords = getSelectedRecordIds();

        if (checkedRecords.length === 0) {
            alert(getTranslation('pleaseSelectAtLeastOneRecordToExport'));
            return;
        }

        var exportModal = new bootstrap.Modal(document.getElementById('exportModal'));
        exportModal.show();
    });

    confirmExportBtn.addEventListener('click', function() {
        let checkedRecords = getSelectedRecordIds();
        let format = document.querySelector('input[name="exportFormat"]:checked').value;
        const exportUrl = `/records/export?records=${checkedRecords.join(',')}&format=${format}`;

        fetch(exportUrl, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error(getTranslation('networkError'));

            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json().then(data => {
                    throw new Error(data.error || getTranslation('anErrorOccurred'));
                });
            }

            return response.blob();
        })
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;

            let extension;
            switch (format) {
                case 'excel': extension = 'xlsx'; break;
                case 'ead': extension = 'xml'; break;
                case 'seda': extension = 'zip'; break;
                default: extension = 'txt';
            }

            a.download = `records_export.${extension}`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
        })
        .catch(error => {
            console.error(getTranslation('error'), error);
            alert(error.message || getTranslation('errorOccurredDuringExport'));
        });

        bootstrap.Modal.getInstance(document.getElementById('exportModal')).hide();
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
        let checkedRecords = getSelectedRecordIds();

        if (checkedRecords.length === 0) {
            alert(getTranslation('pleaseSelectAtLeastOneRecordToPrint'));
            return;
        }

        const printUrl = '/records/print';
        fetch(printUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ records: checkedRecords })
        })
        .then(response => response.blob())
        .then(blob => {
            let url = window.URL.createObjectURL(blob);
            let a = document.createElement('a');
            a.href = url;
            a.download = 'records_print.pdf';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
        })
        .catch(error => {
            console.error(getTranslation('error'), error);
            alert(getTranslation('errorOccurredDuringPrint'));
        });
    });
}

/**
 * Initialise le bouton "Tout cocher/décocher"
 */
function initCheckAllButton() {
    let checkAllBtn = document.getElementById('checkAllBtn');
    if (!checkAllBtn) return;

    checkAllBtn.addEventListener('click', function(e) {
        e.preventDefault();
        let checkboxes = document.querySelectorAll('input[type="checkbox"][name="selected_record[]"]');
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
 * Initialise le bouton de transfert et le modal associé
 */
function initTransferButton() {
    const transferBtn = document.getElementById('transferBtn');
    if (!transferBtn) return;

    transferBtn.addEventListener('click', function(e) {
        e.preventDefault();
        let checkedRecords = document.querySelectorAll('input[type="checkbox"][name="selected_record[]"]:checked');

        if (checkedRecords.length === 0) {
            alert(getTranslation('pleaseSelectAtLeastOneRecord'));
            return;
        }

        let selectedRecordsContainer = document.querySelector('#transferSelectedRecords');
        if (!selectedRecordsContainer) return;
        
        selectedRecordsContainer.innerHTML = '';

        checkedRecords.forEach(checkbox => {
            const recordCard = checkbox.closest('.card-header');
            const titleElement = recordCard.querySelector('.card-title');
            const recordName = titleElement ? titleElement.textContent.trim() : `Record ${checkbox.value}`;

            selectedRecordsContainer.innerHTML += `
                <div class="mb-3 p-3 border rounded">
                    <h6 class="mb-2">${recordName}</h6>
                    <input type="hidden" name="selected_records[]" value="${checkbox.value}">
                </div>
            `;
        });

        var transferModal = new bootstrap.Modal(document.getElementById('transferModal'));
        transferModal.show();
    });
}

/**
 * Initialise le bouton de communication et le modal associé
 */
function initCommunicateButton() {
    const communicateBtn = document.getElementById('communicateBtn');
    if (!communicateBtn) return;

    communicateBtn.addEventListener('click', function(e) {
        e.preventDefault();
        let checkedRecords = document.querySelectorAll('input[type="checkbox"][name="selected_record[]"]:checked');

        if (checkedRecords.length === 0) {
            alert(getTranslation('pleaseSelectAtLeastOneRecord'));
            return;
        }

        let selectedRecordsContainer = document.querySelector('#communicationSelectedRecords');
        if (!selectedRecordsContainer) return;
        
        selectedRecordsContainer.innerHTML = '';

        checkedRecords.forEach(checkbox => {
            const recordCard = checkbox.closest('.card-header');
            const titleElement = recordCard.querySelector('.card-title');
            const recordName = titleElement ? titleElement.textContent.trim() : `Record ${checkbox.value}`;

            selectedRecordsContainer.innerHTML += `
                <div class="mb-3 p-3 border rounded">
                    <h6 class="mb-2">${recordName}</h6>
                    <input type="hidden" name="selected_records[]" value="${checkbox.value}">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="original-${checkbox.value}"
                               name="original[${checkbox.value}]" value="1">
                        <label class="form-check-label" for="original-${checkbox.value}">
                            ${getTranslation('original')}
                        </label>
                    </div>
                    <div class="mb-2">
                        <label for="content-${checkbox.value}" class="form-label">${getTranslation('content')}</label>
                        <textarea class="form-control" id="content-${checkbox.value}"
                                  name="content[${checkbox.value}]" rows="2"></textarea>
                    </div>
                </div>
            `;
        });

        var communicationModal = new bootstrap.Modal(document.getElementById('communicationModal'));
        communicationModal.show();
    });
}

/**
 * Initialise les boutons voir plus/voir moins pour le contenu
 */
function initContentToggleButtons() {
    document.querySelectorAll('.content-toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-target');
            const targetElement = document.getElementById(targetId);
            const fullText = this.getAttribute('data-full-text');

            if (this.textContent === 'Voir plus') {
                targetElement.textContent = fullText;
                this.textContent = 'Voir moins';
            } else {
                targetElement.textContent = fullText.substr(0, 200) + '...';
                this.textContent = 'Voir plus';
            }
        });
    });
}

/**
 * Récupère les IDs des records sélectionnés
 * @returns {Array} Tableau d'IDs
 */
function getSelectedRecordIds() {
    return Array.from(document.querySelectorAll('input[type="checkbox"][name="selected_record[]"]:checked'))
        .map(checkbox => checkbox.value);
}

/**
 * Fonction helper pour les traductions
 * @param {string} key Clé de traduction
 * @returns {string} Texte traduit ou clé si non trouvée
 */
function getTranslation(key) {
    // Simuler un système de traduction basique
    // Dans un environnement réel, ces valeurs seraient importées de ressources Laravel
    const translations = {
        'pleaseSelectAtLeastOneRecord': 'Veuillez sélectionner au moins un document',
        'pleaseSelectAtLeastOneRecordToExport': 'Veuillez sélectionner au moins un document à exporter',
        'pleaseSelectAtLeastOneRecordToPrint': 'Veuillez sélectionner au moins un document à imprimer',
        'networkError': 'Erreur réseau',
        'anErrorOccurred': 'Une erreur est survenue',
        'error': 'Erreur',
        'errorOccurredDuringExport': 'Une erreur est survenue lors de l\'exportation',
        'errorOccurredDuringPrint': 'Une erreur est survenue lors de l\'impression',
        'checkAll': 'Tout cocher',
        'uncheckAll': 'Tout décocher',
        'original': 'Original',
        'content': 'Contenu'
    };
    
    return translations[key] || key;
}

// Exposer la fonction getSelectedRecordIds pour une utilisation par d'autres scripts
window.getSelectedRecordIds = getSelectedRecordIds;