const saveFile = document.getElementById('import-button');
const cancelImport = document.getElementById('cancel-import');
const formImport = document.getElementById('import-form');
const showImport = document.getElementById('show-import-form-alt');


const bulletinBoardId = document.querySelector('meta[name="bulletin-board-id"]').content;
const eventId = document.querySelector('meta[name="event-id"]').content;






saveFile.addEventListener('click', (event) => {
    event.preventDefault();
    
    // Récupérer les fichiers du champ d'entrée
    const fileInput = document.getElementById('attachment-files'); // ID mis à jour
    if (!fileInput || !fileInput.files.length) {
        console.error("Aucun fichier sélectionné");
        return;
    }
    
    const formData = new FormData();
    
    // Ajouter chaque fichier
    for (const file of fileInput.files) {
        formData.append('attachments[]', file);
    }
    
    // Ajouter le nom personnalisé si fourni
    const nameInput = document.getElementById('attachment-name');
    if (nameInput && nameInput.value) {
        formData.append('name', nameInput.value);
    }
    
    // Afficher un indicateur de chargement si nécessaire
    const progressBar = document.querySelector('#upload-progress .progress-bar');
    const uploadProgress = document.getElementById('upload-progress');
    
    if (uploadProgress) {
        uploadProgress.classList.remove('d-none');
        progressBar.style.width = '0%';
    }

    formData.append('event_id', eventId);

    

    const uploadUrl = `/bulletin-boards/bulletin-board/${bulletinBoardId}/events/${eventId}/attachments`;


    fetch(uploadUrl, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Erreur HTTP: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Succès:', data);
        
        // Mettre à jour la barre de progression à 100%
        if (progressBar) {
            progressBar.style.width = '100%';
        }
        
        // Afficher un message de succès
        const uploadFeedback = document.getElementById('upload-feedback');
        if (uploadFeedback) {
            uploadFeedback.innerHTML = `
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    ${data.files.length} fichier(s) téléversé(s) avec succès!
                </div>
            `;
        }
        
        // Rafraîchir la liste des pièces jointes
        refreshAttachmentsList();
        
        // Réinitialiser le formulaire
        if (fileInput) fileInput.value = '';
        if (nameInput) nameInput.value = '';
    })
    .catch(error => {
        console.error('Erreur:', error);
        
        // Afficher un message d'erreur
        const uploadFeedback = document.getElementById('upload-feedback');
        if (uploadFeedback) {
            uploadFeedback.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Erreur lors du téléversement: ${error.message}
                </div>
            `;
        }
    })
    .finally(() => {
        // Cacher la barre de progression
        if (uploadProgress) {
            setTimeout(() => {
                uploadProgress.classList.add('d-none');
            }, 2000);
        }
    });
});










cancelImport.addEventListener('click', () => {
    formImport.classList.add('d-none');
});

showImport.addEventListener('click', () => {
    formImport.classList.remove('d-none');
});
