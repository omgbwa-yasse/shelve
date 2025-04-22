/**
 * event-attachments.js
 *
 * Ce fichier gère toutes les interactions relatives aux pièces jointes
 * pour les événements et les publications du tableau d'affichage.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialiser la gestion des pièces jointes
    initAttachmentsManager();

    // Initialiser la prévisualisation des images lors de l'upload
    initImagePreview();

    // Initialiser les modals de suppression
    initDeleteModals();

    // Initialiser l'upload AJAX si le conteneur est présent
    initAjaxUpload();
});

/**
 * Initialise les gestionnaires de pièces jointes
 */
function initAttachmentsManager() {
    // Gestionnaire pour les boutons de suppression standard
    const deleteButtons = document.querySelectorAll('.delete-attachment');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const attachmentId = this.dataset.attachmentId;
            const deleteForm = document.getElementById('delete-attachment-form');

            if (deleteForm && attachmentId) {
                // Construire l'URL avec l'ID de la pièce jointe
                const baseUrl = deleteForm.getAttribute('action');
                const finalUrl = baseUrl.replace(':attachment', attachmentId);

                deleteForm.setAttribute('action', finalUrl);

                // Ouvrir la modal de confirmation
                const modal = new bootstrap.Modal(document.getElementById('deleteAttachmentModal'));
                modal.show();
            }
        });
    });
}

/**
 * Initialise la prévisualisation des images lors de l'upload
 */
function initImagePreview() {
    const fileInput = document.getElementById('file');
    if (!fileInput) return;

    const previewContainer = document.getElementById('preview-container');
    const imagePreview = document.getElementById('image-preview');
    const thumbnailData = document.getElementById('thumbnail-data');

    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (!file) {
            previewContainer.style.display = 'none';
            return;
        }

        // Vérifier si c'est une image
        if (file.type.match('image.*')) {
            previewContainer.style.display = 'block';

            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;

                // Créer une vignette pour l'image
                const img = new Image();
                img.onload = function() {
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');

                    // Dimensions maximales pour la vignette
                    const MAX_WIDTH = 300;
                    const MAX_HEIGHT = 300;

                    let width = img.width;
                    let height = img.height;

                    // Redimensionner proportionnellement
                    if (width > height) {
                        if (width > MAX_WIDTH) {
                            height *= MAX_WIDTH / width;
                            width = MAX_WIDTH;
                        }
                    } else {
                        if (height > MAX_HEIGHT) {
                            width *= MAX_HEIGHT / height;
                            height = MAX_HEIGHT;
                        }
                    }

                    canvas.width = width;
                    canvas.height = height;

                    ctx.drawImage(img, 0, 0, width, height);

                    // Convertir en base64 pour l'envoi
                    const dataUrl = canvas.toDataURL('image/jpeg', 0.7);
                    thumbnailData.value = dataUrl;
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        } else {
            previewContainer.style.display = 'none';
        }
    });
}

/**
 * Initialise les modals de suppression
 */
function initDeleteModals() {
    // Supprimer les pièces jointes via AJAX si nécessaire
    const ajaxDeleteButtons = document.querySelectorAll('.delete-attachment-ajax');
    ajaxDeleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const attachmentId = this.dataset.attachmentId;
            const attachmentName = this.dataset.attachmentName;
            const deleteUrl = this.dataset.deleteUrl;

            if (confirm(`Êtes-vous sûr de vouloir supprimer la pièce jointe "${attachmentName}" ?`)) {
                deleteAttachmentAjax(deleteUrl, attachmentId);
            }
        });
    });
}

/**
 * Supprime une pièce jointe via AJAX
 */
function deleteAttachmentAjax(url, attachmentId) {
    // Récupérer le token CSRF
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: '_method=DELETE'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Supprimer l'élément du DOM
            const element = document.querySelector(`.attachment-item[data-attachment-id="${attachmentId}"]`);
            if (element) {
                element.remove();
            }

            // Afficher un message de succès
            showAlert('success', 'Pièce jointe supprimée avec succès');

            // Actualiser le compteur si présent
            updateAttachmentCounter();
        } else {
            showAlert('danger', data.message || 'Erreur lors de la suppression');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showAlert('danger', 'Une erreur est survenue lors de la suppression');
    });
}

/**
 * Initialise l'upload de fichiers via AJAX
 */
function initAjaxUpload() {
    const dropZone = document.getElementById('dropzone-upload');
    if (!dropZone) return;

    const fileInput = document.getElementById('ajax-file-input');
    const progressBar = document.getElementById('upload-progress-bar');
    const progressContainer = document.getElementById('upload-progress');
    const attachmentsContainer = document.getElementById('attachments-container');

    // Empêcher les comportements par défaut sur la zone de dépôt
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    // Ajouter des effets visuels lors du survol
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
    });

    function highlight() {
        dropZone.classList.add('border-primary', 'bg-light');
    }

    function unhighlight() {
        dropZone.classList.remove('border-primary', 'bg-light');
    }

    // Gérer le dépôt de fichiers
    dropZone.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;

        handleFiles(files);
    }

    // Gérer le clic sur la zone de dépôt
    dropZone.addEventListener('click', function() {
        fileInput.click();
    });

    // Gérer la sélection de fichiers via l'input
    fileInput.addEventListener('change', function() {
        handleFiles(this.files);
    });

    function handleFiles(files) {
        if (files.length === 0) return;

        // Afficher la barre de progression
        progressContainer.style.display = 'block';
        progressBar.style.width = '0%';
        progressBar.setAttribute('aria-valuenow', 0);

        const formData = new FormData();

        // Ajouter les fichiers au formData
        for (let i = 0; i < files.length; i++) {
            formData.append('files[]', files[i]);
        }

        // Ajouter le token CSRF
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        formData.append('_token', token);

        // Récupérer l'URL d'upload
        const uploadUrl = dropZone.dataset.uploadUrl;

        // Envoyer les fichiers
        const xhr = new XMLHttpRequest();
        xhr.open('POST', uploadUrl, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        // Gérer la progression
        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                const percentComplete = Math.round((e.loaded / e.total) * 100);
                progressBar.style.width = percentComplete + '%';
                progressBar.setAttribute('aria-valuenow', percentComplete);
                progressBar.textContent = percentComplete + '%';
            }
        });

        // Gérer la fin du téléversement
        xhr.onload = function() {
            progressContainer.style.display = 'none';

            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);

                    if (response.success) {
                        showAlert('success', 'Fichiers téléversés avec succès');

                        // Rafraîchir la liste des pièces jointes
                        refreshAttachmentsList();
                    } else {
                        showAlert('danger', response.message || 'Erreur lors du téléversement');
                    }
                } catch (e) {
                    showAlert('danger', 'Erreur lors du traitement de la réponse');
                }
            } else {
                showAlert('danger', 'Erreur lors du téléversement (code ' + xhr.status + ')');
            }
        };

        xhr.onerror = function() {
            progressContainer.style.display = 'none';
            showAlert('danger', 'Erreur réseau lors du téléversement');
        };

        xhr.send(formData);
    }
}

/**
 * Actualise la liste des pièces jointes
 */
function refreshAttachmentsList() {
    const container = document.getElementById('attachments-container');
    if (!container) return;

    const listUrl = container.dataset.listUrl;
    if (!listUrl) return;

    fetch(listUrl, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        container.innerHTML = html;

        // Réinitialiser les gestionnaires d'événements
        initAttachmentsManager();
        initDeleteModals();

        // Mettre à jour le compteur
        updateAttachmentCounter();
    })
    .catch(error => {
        console.error('Erreur:', error);
    });
}

/**
 * Met à jour le compteur de pièces jointes
 */
function updateAttachmentCounter() {
    const container = document.getElementById('attachments-container');
    if (!container) return;

    const count = container.querySelectorAll('.attachment-item').length;
    const counter = document.getElementById('attachment-counter');

    if (counter) {
        counter.textContent = count;
    }
}

/**
 * Affiche une alerte
 */
function showAlert(type, message) {
    const alertContainer = document.getElementById('alert-container');
    if (!alertContainer) return;

    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.role = 'alert';

    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    alertContainer.appendChild(alert);

    // Masquer l'alerte après 5 secondes
    setTimeout(() => {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    }, 5000);
}

/**
 * Formater la taille de fichier en unités lisibles
 */
function human_filesize(bytes, decimals = 2) {
    if (bytes === 0) return '0 Bytes';

    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}
