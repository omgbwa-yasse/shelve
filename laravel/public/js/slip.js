// slip.js - Fonctions pour la gestion des bordereaux de versement

document.addEventListener('DOMContentLoaded', function() {
    initSlipManager();
});

/**
 * Initialise les fonctionnalités de gestion des bordereaux
 */
function initSlipManager() {
    initExportButton();
    initPrintButton();
    initCheckAllButton();
}

/**
 * Initialise le bouton d'exportation
 */
function initExportButton() {
    const exportBtn = document.getElementById('exportBtn');
    if (!exportBtn) return;

    exportBtn.addEventListener('click', function(e) {
        e.preventDefault();
        let selectedSlips = getSelectedSlipIds();

        if (selectedSlips.length === 0) {
            alert('Veuillez sélectionner au moins un bordereau à exporter.');
            return;
        }

        const exportRoute = this.getAttribute('data-route') || '/slips/export';
        window.location.href = `${exportRoute}?slips=${selectedSlips.join(',')}`;
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
        let selectedSlips = getSelectedSlipIds();

        if (selectedSlips.length === 0) {
            alert('Veuillez sélectionner au moins un bordereau à imprimer.');
            return;
        }

        const printRoute = this.getAttribute('data-route') || '/slips/print';
        
        fetch(printRoute, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ slips: selectedSlips })
        })
        .then(response => response.blob())
        .then(blob => {
            let url = window.URL.createObjectURL(blob);
            let a = document.createElement('a');
            a.href = url;
            a.download = 'bordereaux_impression.pdf';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Une erreur est survenue lors de la préparation de l\'impression.');
        });
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
        let checkboxes = document.querySelectorAll('input[name="selected_slip[]"]');
        let allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);

        checkboxes.forEach(function(checkbox) {
            checkbox.checked = !allChecked;
        });

        this.innerHTML = allChecked ? 
            '<i class="bi bi-check-square me-1"></i>Tout cocher' : 
            '<i class="bi bi-square me-1"></i>Tout décocher';
    });
}

/**
 * Récupère les IDs des bordereaux sélectionnés
 * @returns {Array} Tableau d'IDs
 */

function getSelectedSlipIds() {
    return Array.from(document.querySelectorAll('input[name="selected_slip[]"]:checked'))
        .map(checkbox => checkbox.value);
}

// Exposer la fonction getSelectedSlipIds pour dollies.js
window.getSelectedSlipIds = getSelectedSlipIds;