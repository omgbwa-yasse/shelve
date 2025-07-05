// Fonction pour initialiser l'import AJAX avec une visualisation en temps réel
function initAjaxImport(formId, importType) {
    const importForm = document.getElementById(formId);
    const submitBtn = document.getElementById('submit-btn');
    const progressContainer = document.getElementById('progress-container');
    const progressBar = document.getElementById('progress-bar');
    const statusBadge = document.getElementById('status-badge');
    const totalItems = document.getElementById('total-items');
    const processedItems = document.getElementById('processed-items');
    const createdItems = document.getElementById('created-items');
    const updatedItems = document.getElementById('updated-items');
    const errorItems = document.getElementById('error-items');
    const relationsItems = document.getElementById('relations-items');
    const messageContainer = document.getElementById('message-container');
    const alertContainer = document.getElementById('alert-container');

    let importId = null;
    let checkStatusInterval = null;

    importForm.addEventListener('submit', function(e) {
        e.preventDefault();

        // Réinitialiser les alertes
        alertContainer.innerHTML = '';

        // Vérifier si un fichier a été sélectionné
        const fileInput = document.getElementById('file');
        if (!fileInput.files.length) {
            showAlert('danger', `Veuillez sélectionner un fichier ${importType.toUpperCase()} à importer.`);
            return;
        }

        // Désactiver le bouton d'envoi
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Import en cours...';

        // Préparer les données du formulaire
        const formData = new FormData(importForm);

        console.log('Envoi de la requête AJAX à:', importForm.action);
        console.log('Fichier sélectionné:', fileInput.files[0] ? fileInput.files[0].name : 'Aucun');
        console.log('CSRF token présent:', !!document.querySelector('meta[name="csrf-token"]'));

        // Envoyer la requête AJAX
        fetch(importForm.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : '',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            console.log('Réponse reçue:', response.status, response.statusText);
            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status} ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Données reçues:', data);
            if (data.success) {
                // Afficher la barre de progression
                progressContainer.classList.remove('d-none');

                // Stocker l'ID de l'import
                importId = data.import_id;
                console.log('Import ID reçu:', importId);

                // Commencer à vérifier l'état de l'import
                checkStatusInterval = setInterval(checkImportStatus, 1000);
            } else {
                // Afficher les erreurs
                if (data.errors) {
                    const errorMessages = Object.values(data.errors).flat();
                    showAlert('danger', errorMessages.join('<br>'));
                    console.error('Erreurs de validation:', data.errors);
                } else if (data.message) {
                    showAlert('danger', data.message);
                    console.error('Message d\'erreur:', data.message);
                } else {
                    showAlert('danger', 'Une erreur est survenue lors du démarrage de l\'import.');
                    console.error('Erreur inconnue:', data);
                }

                // Réactiver le bouton d'envoi
                submitBtn.disabled = false;
                submitBtn.textContent = 'Importer';
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showAlert('danger', `Une erreur est survenue lors de l'envoi de la requête: ${error.message}`);

            // Réactiver le bouton d'envoi
            submitBtn.disabled = false;
            submitBtn.textContent = 'Importer';
        });
    });

    function checkImportStatus() {
        if (!importId) return;

        const statusUrl = `/api/thesaurus/import/status/${importId}`;
        console.log('Vérification du statut:', statusUrl);

        fetch(statusUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status} ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Données de statut:', data);
            if (data.success) {
                // Mettre à jour la barre de progression
                const progress = data.progress;
                progressBar.style.width = `${progress}%`;
                progressBar.textContent = `${progress}%`;
                progressBar.setAttribute('aria-valuenow', progress);

                // Mettre à jour les statistiques
                totalItems.textContent = data.stats.total;
                processedItems.textContent = data.stats.processed;
                createdItems.textContent = data.stats.created;
                updatedItems.textContent = data.stats.updated;
                errorItems.textContent = data.stats.errors;
                relationsItems.textContent = data.stats.relationships;

                // Mettre à jour le badge de statut
                updateStatusBadge(data.status);

                // Afficher le message si présent
                if (data.message) {
                    messageContainer.textContent = data.message;
                    messageContainer.classList.remove('d-none');
                }

                // Si l'import est terminé
                if (data.completed) {
                    clearInterval(checkStatusInterval);

                    // Réactiver le bouton d'envoi
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Importer un autre fichier';

                    // Afficher un message de succès ou d'échec
                    if (data.status === 'completed') {
                        showAlert('success', 'Import terminé avec succès!');
                    } else {
                        showAlert('danger', 'L\'import a échoué. Consultez les détails ci-dessous.');
                    }
                }
            }
        })
        .catch(error => {
            console.error('Erreur lors de la vérification du statut:', error);
            clearInterval(checkStatusInterval);
            showAlert('danger', `Une erreur est survenue lors de la vérification de l'état de l'import: ${error.message}`);

            // Réactiver le bouton d'envoi
            submitBtn.disabled = false;
            submitBtn.textContent = 'Réessayer';
        });
    }

    function updateStatusBadge(status) {
        // Enlever toutes les classes de couleur
        statusBadge.classList.remove('bg-info', 'bg-warning', 'bg-success', 'bg-danger');

        // Ajouter la classe appropriée et définir le texte
        switch(status) {
            case 'processing':
                statusBadge.classList.add('bg-warning');
                statusBadge.textContent = 'En cours';
                break;
            case 'completed':
                statusBadge.classList.add('bg-success');
                statusBadge.textContent = 'Terminé';
                break;
            case 'failed':
                statusBadge.classList.add('bg-danger');
                statusBadge.textContent = 'Échec';
                break;
            default:
                statusBadge.classList.add('bg-info');
                statusBadge.textContent = 'En préparation';
                break;
        }
    }

    function showAlert(type, message) {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        `;

        alertContainer.appendChild(alert);
    }
}
