// Fonctions pour la gestion des courriers
function initMailManager() {
    // Gérer la sélection de tous les courriers
    const checkAllBtn = document.getElementById('checkAllBtn');
    checkAllBtn.addEventListener('click', toggleAllMails);

    // Gestion de l'exportation
    const exportBtn = document.getElementById('exportBtn');
    exportBtn.addEventListener('click', exportSelectedMails);

    // Gestion de l'impression
    const printBtn = document.getElementById('printBtn');
    printBtn.addEventListener('click', printSelectedMails);
}

function toggleAllMails() {
    const checkboxes = document.querySelectorAll('input[name="selected_mail[]"]');
    const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = !allChecked;
    });
}

function getSelectedMailIds() {
    const selectedIds = [];
    document.querySelectorAll('input[name="selected_mail[]"]:checked').forEach(checkbox => {
        selectedIds.push(checkbox.value);
    });
    return selectedIds;
}

function exportSelectedMails() {
    const selectedIds = getSelectedMailIds();
    
    if (selectedIds.length === 0) {
        alert('Veuillez sélectionner au moins un courrier.');
        return;
    }
    
    const exportRoute = document.getElementById('exportBtn').getAttribute('data-route');
    
    fetch(exportRoute, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ selectedIds: selectedIds })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erreur réseau: ' + response.status);
        }
        return response.blob();
    })
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'export_courriers.csv';
        document.body.appendChild(a);
        a.click();

        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
    })
    .catch(error => {
        console.error('Erreur lors de l\'exportation:', error);
        alert('Une erreur est survenue lors de l\'exportation.');
    });
}

function printSelectedMails() {
    const selectedIds = getSelectedMailIds();
    
    if (selectedIds.length === 0) {
        alert('Veuillez sélectionner au moins un courrier.');
        return;
    }
    
    const printRoute = document.getElementById('printBtn').getAttribute('data-route');
    
    fetch(printRoute, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ selectedIds: selectedIds })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erreur réseau: ' + response.status);
        }
        return response.blob();
    })
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const printWindow = window.open(url, '_blank');
        
        if (printWindow) {
            printWindow.addEventListener('load', function() {
                printWindow.print();
            });
        }
    })
    .catch(error => {
        console.error('Erreur lors de l\'impression:', error);
        alert('Une erreur est survenue lors de la préparation de l\'impression.');
    });
}

// Exposer la fonction getSelectedMailIds pour les autres scripts
window.getSelectedMailIds = getSelectedMailIds;

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    initMailManager();
});