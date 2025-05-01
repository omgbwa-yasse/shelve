// dollies.js - Fonctions pour la gestion des chariots (dollies)

document.addEventListener('DOMContentLoaded', function() {
    initDolliesManager();
});

function initDolliesManager() {
    const addDollyBtn = document.getElementById('addDollyBtn');
    const dolliesList = document.getElementById('dolliesList');
    const dollyForm = document.getElementById('dollyForm');
    const backToListBtn = document.getElementById('backToListBtn');
    const createDollyForm = document.getElementById('createDollyForm');

    if (!addDollyBtn || !dolliesList || !dollyForm || !backToListBtn || !createDollyForm) {
        console.warn('Certains éléments nécessaires pour dollies.js sont manquants');
        return;
    }

    // Afficher le formulaire de création
    addDollyBtn.addEventListener('click', function() {
        dolliesList.style.display = 'none';
        dollyForm.style.display = 'block';
    });

    // Retour à la liste des chariots
    backToListBtn.addEventListener('click', function() {
        dolliesList.style.display = 'block';
        dollyForm.style.display = 'none';
    });

    // Gestion du formulaire de création de chariot
    createDollyForm.addEventListener('submit', handleDollyFormSubmit);

    // Chargement initial des chariots
    refreshDolliesList();

    // Délégation d'événements pour les boutons "Remplir"
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('fillDollyBtn')) {
            const dollyId = event.target.getAttribute('data-id');
            fillDolly(dollyId);
        }
    });
}











function handleDollyFormSubmit(event) {
    event.preventDefault();
    const category = document.getElementById('category') ? document.getElementById('category').value : detectCategory();
    const name = dollyForm.querySelector('input[name="name"]').value;
    const description = dollyForm.querySelector('textarea[name="description"]').value;

    const formData = {
        name: name,
        description: description,
        category: category
    };


    fetch('/dolly-handler/create', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(formData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erreur réseau: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        alert('Chariot créé avec succès!');
        document.getElementById('createDollyForm').reset();
        document.getElementById('dolliesList').style.display = 'block';
        document.getElementById('dollyForm').style.display = 'none';
        refreshDolliesList();
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Une erreur est survenue lors de la création du chariot.');
    });
}











function refreshDolliesList() {
    const category = detectCategory();

    fetch(`/dolly-handler/list?category=${category}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erreur réseau: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        const dollies = data.dollies;
        const dolliesList = document.getElementById('dolliesList');

        if (!dolliesList) return;

        if (dollies.length === 0) {
            dolliesList.innerHTML = '<p>Aucun chariot chargé</p>';
            return;
        }

        let dolliesListHTML = '';
        let baseUrl = window.location.origin;
        dollies.forEach(dolly => {
            const itemCount = getItemCount(dolly, category);

            dolliesListHTML += `
                <div class="card mb-1 shadow-sm border-0 rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex flex-column">
                            <h5 class="card-title fw-bold mb-1">${dolly.name}</h5>
                            <p class="card-text text-muted mb-1">${dolly.description}</p>
                            <div class="d-flex flex-wrap gap-1 mb-1">
                                <span class="badge bg-info text-dark rounded-pill px-3 py-2">
                                    ${itemCount} élement(s)
                                </span>
                            </div>
                            <div class="d-flex justify-content-end gap-2 mt-1">
                                <button class="btn btn-success btn-sm fillDollyBtn" data-id="${dolly.id}">
                                    <i class="bi bi-plus-circle me-1"></i> Remplir
                                </button>
                                <a href="${baseUrl}/dollies/dolly/${dolly.id}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> Ouvrir
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        dolliesList.innerHTML = dolliesListHTML;
    })
    .catch(error => {
        console.error('Erreur:', error);
        if (dolliesList) {
            dolliesList.innerHTML = '<p>Erreur lors du chargement des chariots</p>';
        }
    });
}






function fillDolly(dollyId) {
    const category = detectCategory();
    const selectedIds = getSelectedIds();

    if (selectedIds.length === 0) {
        alert(`Veuillez sélectionner au moins un ${getItemLabel(category)}.`);
        return;
    }

    fetch('/dolly-handler/add-items', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            items: selectedIds,
            'category': category,
            dolly_id: dollyId
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erreur réseau: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        alert(`Les ${getItemLabelPlural(category)} ont été ajoutés au chariot avec succès.`);
        refreshDolliesList();
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert(`Une erreur est survenue lors de l'ajout des ${getItemLabelPlural(category)} au chariot.`);
    });
}










/**
 * Détermine la catégorie d'élément basée sur le contexte de la page
 * @return {string} La catégorie ('mail' ou 'record')
 */

function detectCategory() {
    // Vérifier si nous sommes sur une page de mails, records ou communications
    if (document.querySelector('input[name="selected_mail[]"]')) {
        return 'mail';
    } else if (document.querySelector('input[name="selected_record[]"]')) {
        return 'record';
    } else if (document.querySelector('input[name="selected_communication[]"]')) {
        return 'communication';
    }

    // Vérifier le champ select s'il existe
    const categorySelect = document.getElementById('category');
    if (categorySelect && categorySelect.value) {
        return categorySelect.value;
    }

    // Vérifier l'URL de la page
    const url = window.location.pathname.toLowerCase();
    if (url.includes('mail')) {
        return 'mail';
    } else if (url.includes('record')) {
        return 'record';
    } else if (url.includes('communication') || url.includes('transaction')) {
        return 'communication';
    }

    // Valeur par défaut
    return 'mail';
}








/**
 * Obtient le nombre d'éléments dans un chariot en fonction de la catégorie
 * @param {Object} dolly - L'objet chariot
 * @param {string} category - La catégorie ('mail' ou 'record')
 * @return {number} Le nombre d'éléments
 */

function getItemCount(dolly, category) {
    if (category === 'mail' && dolly.mails) {
        return dolly.mails.length;
    } else if (category === 'record' && dolly.records) {
        return dolly.records.length;
    } else if (category === 'communication' && dolly.communications) {
        return dolly.communications.length;
    }
    return 0;
}







// Dans la fonction detectCategory()
function detectCategory() {
    // Vérifier si nous sommes sur une page de mails, records, communications ou slips
    if (document.querySelector('input[name="selected_mail[]"]')) {
        return 'mail';
    } else if (document.querySelector('input[name="selected_record[]"]')) {
        return 'record';
    } else if (document.querySelector('input[name="selected_communication[]"]')) {
        return 'communication';
    } else if (document.querySelector('input[name="selected_slip[]"]')) {
        return 'slip';
    }

    // Vérifier le champ select s'il existe
    const categorySelect = document.getElementById('category');
    if (categorySelect && categorySelect.value) {
        return categorySelect.value;
    }

    // Vérifier l'URL de la page
    const url = window.location.pathname.toLowerCase();
    if (url.includes('mail')) {
        return 'mail';
    } else if (url.includes('record')) {
        return 'record';
    } else if (url.includes('communication') || url.includes('transaction')) {
        return 'communication';
    } else if (url.includes('slip') || url.includes('bordereau')) {
        return 'slip';
    }

    // Valeur par défaut
    return 'mail';
}

// Mise à jour de la fonction getItemCount
function getItemCount(dolly, category) {
    if (category === 'mail' && dolly.mails) {
        return dolly.mails.length;
    } else if (category === 'record' && dolly.records) {
        return dolly.records.length;
    } else if (category === 'communication' && dolly.communications) {
        return dolly.communications.length;
    } else if (category === 'slip' && dolly.slips) {
        return dolly.slips.length;
    }
    return 0;
}

// Mise à jour de la fonction getSelectedIds
function getSelectedIds() {
    const category = detectCategory();

    if (category === 'mail') {
        if (window.getSelectedMailIds) {
            return window.getSelectedMailIds();
        }
        return Array.from(document.querySelectorAll('input[name="selected_mail[]"]:checked'))
            .map(checkbox => checkbox.value);
    } else if (category === 'record') {
        if (window.getSelectedRecordIds) {
            return window.getSelectedRecordIds();
        }
        return Array.from(document.querySelectorAll('input[name="selected_record[]"]:checked'))
            .map(checkbox => checkbox.value);
    } else if (category === 'communication') {
        if (window.getSelectedCommunicationIds) {
            return window.getSelectedCommunicationIds();
        }
        return Array.from(document.querySelectorAll('input[name="selected_communication[]"]:checked'))
            .map(checkbox => checkbox.value);
    } else if (category === 'slip') {
        if (window.getSelectedSlipIds) {
            return window.getSelectedSlipIds();
        }
        return Array.from(document.querySelectorAll('input[name="selected_slip[]"]:checked'))
            .map(checkbox => checkbox.value);
    }

    return [];
}

// Mise à jour des fonctions de labels
function getItemLabel(category) {
    if (category === 'mail') return 'courrier';
    if (category === 'record') return 'document';
    if (category === 'communication') return 'communication';
    if (category === 'slip') return 'bordereau';
    return 'élément';
}

function getItemLabelPlural(category) {
    if (category === 'mail') return 'courriers';
    if (category === 'record') return 'documents';
    if (category === 'communication') return 'communications';
    if (category === 'slip') return 'bordereaux';
    return 'éléments';
}
