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

    // Initialisation du thésaurus AJAX (si présent sur la page)
    initThesaurusAjax();

    // Initialisation des modals (si présents sur la page)
    initModals();
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
 * Initialise le système de thésaurus AJAX
 */
function initThesaurusAjax() {
    console.log('🚀 Initialisation du thésaurus AJAX...');

    // Gestion du thésaurus AJAX
    let selectedTerms = new Map(); // Map pour stocker les termes sélectionnés (id -> {name, thesaurus})
    let searchTimeout;

    const thesaurusSearch = document.getElementById('thesaurus-search');
    const thesaurusSuggestions = document.getElementById('thesaurus-suggestions');
    const selectedTermsContainer = document.getElementById('selected-terms-container');
    const termIdsInput = document.getElementById('term-ids');

    console.log('🔍 Éléments trouvés:', {
        thesaurusSearch: !!thesaurusSearch,
        thesaurusSuggestions: !!thesaurusSuggestions,
        selectedTermsContainer: !!selectedTermsContainer,
        termIdsInput: !!termIdsInput
    });

    if (!thesaurusSearch || !thesaurusSuggestions || !selectedTermsContainer || !termIdsInput) {
        console.log('❌ Éléments manquants, thésaurus non initialisé');
        return; // Éléments non trouvés, probablement pas sur la page de création
    }

    console.log('✅ Tous les éléments trouvés, initialisation complète...');

    // Les termes du thésaurus sont facultatifs - pas de vérification requise

    // Recherche AJAX avec délai
    thesaurusSearch.addEventListener('input', function() {
        const query = this.value.trim();
        console.log('⌨️ Saisie détectée:', query);

        // Effacer le timeout précédent
        clearTimeout(searchTimeout);

        if (query.length < 3) {
            console.log('⏸️ Requête trop courte, masquage des suggestions');
            hideSuggestions();
            return;
        }

        console.log('⏱️ Démarrage du timer de recherche...');
        // Attendre 300ms avant de faire la recherche
        searchTimeout = setTimeout(() => {
            console.log('🔍 Lancement de la recherche pour:', query);
            searchThesaurus(query);
        }, 300);
    });

    // Masquer les suggestions quand on clique ailleurs
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#thesaurus-search') && !e.target.closest('#thesaurus-suggestions')) {
            hideSuggestions();
        }
    });

    // Afficher les suggestions quand on focus le champ
    thesaurusSearch.addEventListener('focus', function() {
        if (this.value.trim().length >= 3) {
            showSuggestions();
        }
    });

    function searchThesaurus(query) {
        console.log('🔍 Recherche thésaurus débutée pour:', query);

        fetch(`/repositories/records/terms/autocomplete?q=${encodeURIComponent(query)}&limit=5`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            console.log('📡 Réponse reçue:', response.status, response.statusText);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const contentType = response.headers.get('content-type');
            console.log('📄 Type de contenu:', contentType);
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('La réponse n\'est pas au format JSON');
            }
            return response.json();
        })
        .then(data => {
            console.log('📦 Données reçues:', data);
            console.log('📊 Nombre de résultats:', data.length);
            displaySuggestions(data);
        })
        .catch(error => {
            console.error('❌ Erreur lors de la recherche dans le thésaurus:', error);
            console.error('🔍 Query était:', query);
            console.error('🌐 URL utilisée:', `/records/terms/autocomplete?q=${encodeURIComponent(query)}&limit=5`);
            hideSuggestions();
        });
    }

    function displaySuggestions(terms) {
        console.log('📋 Affichage des suggestions:', terms);
        thesaurusSuggestions.innerHTML = '';

        if (terms.length === 0) {
            console.log('❌ Aucun résultat à afficher');
            const noResult = document.createElement('div');
            noResult.className = 'thesaurus-suggestion text-muted';
            noResult.textContent = 'Aucun résultat trouvé';
            thesaurusSuggestions.appendChild(noResult);
        } else {
            console.log(`✅ Affichage de ${terms.length} résultats`);
            terms.forEach((term, index) => {
                console.log(`   Terme ${index + 1}:`, term);

                const suggestion = document.createElement('div');
                suggestion.className = 'thesaurus-suggestion';
                suggestion.dataset.id = term.id;

                // Adaptation pour le nouveau format d'API
                const termLabel = term.text || term.pref_label || 'Sans nom';
                const schemeTitle = term.scheme || (term.scheme && term.scheme.title) || 'Thésaurus';

                suggestion.dataset.name = termLabel;
                suggestion.dataset.thesaurus = schemeTitle;

                // Format: motLabel - thésaurus ou motlabel[termeassocié] - thésaurus
                let displayText = termLabel;

                // Si il y a un terme spécifique associé, l'ajouter entre crochets
                if (term.specific_term && term.specific_term.pref_label) {
                    displayText += `[${term.specific_term.pref_label}]`;
                }

                // Ajouter le nom du thésaurus
                displayText += ` - ${schemeTitle}`;

                suggestion.textContent = displayText;
                console.log(`   Texte affiché: "${displayText}"`);

                suggestion.addEventListener('click', function() {
                    console.log(`👆 Clic sur le terme:`, term.id, termLabel);
                    selectTerm(
                        term.id,
                        termLabel,
                        schemeTitle
                    );
                });

                thesaurusSuggestions.appendChild(suggestion);
            });
        }

        showSuggestions();
        console.log('👁️ Suggestions affichées');
    }

    function selectTerm(id, name, thesaurus) {
        // Vérifier si le terme n'est pas déjà sélectionné
        if (selectedTerms.has(id)) {
            return;
        }

        // Ajouter le terme aux sélectionnés
        selectedTerms.set(id, { name, thesaurus });

        // Créer l'élément visuel
        const termElement = document.createElement('span');
        termElement.className = 'selected-term';
        termElement.dataset.id = id;

        const termText = document.createElement('span');
        termText.textContent = `${name} - ${thesaurus}`;

        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'remove-term';
        removeButton.innerHTML = '×';
        removeButton.title = 'Supprimer ce terme';

        removeButton.addEventListener('click', function() {
            removeTerm(id);
        });

        termElement.appendChild(termText);
        termElement.appendChild(removeButton);
        selectedTermsContainer.appendChild(termElement);

        // Mettre à jour le champ caché
        updateHiddenInput();

        // Vider le champ de recherche et masquer les suggestions
        thesaurusSearch.value = '';
        hideSuggestions();

        // Enlever la classe d'erreur si elle existe
        thesaurusSearch.classList.remove('is-invalid');
    }

    function removeTerm(id) {
        // Supprimer de la Map
        selectedTerms.delete(id);

        // Supprimer l'élément visuel
        const termElement = selectedTermsContainer.querySelector(`[data-id="${id}"]`);
        if (termElement) {
            termElement.remove();
        }

        // Mettre à jour le champ caché
        updateHiddenInput();

        // Pas besoin d'ajouter la classe d'erreur car le champ est facultatif
    }

    function updateHiddenInput() {
        const ids = Array.from(selectedTerms.keys());
        termIdsInput.value = ids.join(',');

        // Le thésaurus est facultatif - pas besoin de validation
        // Nettoyer tout message d'erreur existant
        thesaurusSearch.classList.remove('is-invalid');
        const errorMsg = document.querySelector('.thesaurus-error-message');
        if (errorMsg) {
            errorMsg.remove();
        }
    }

    function showSuggestions() {
        console.log('👁️ Affichage des suggestions');
        thesaurusSuggestions.style.display = 'block';
    }

    function hideSuggestions() {
        console.log('🙈 Masquage des suggestions');
        thesaurusSuggestions.style.display = 'none';
    }

    // Validation du formulaire
    const recordForm = document.getElementById('recordForm');
    if (recordForm) {
        recordForm.addEventListener('submit', function(e) {
            // Le thésaurus est désormais facultatif, pas besoin de validation

            // Nettoyer tout message d'erreur existant avant la soumission
            const errorMsg = document.querySelector('.thesaurus-error-message');
            if (errorMsg) {
                errorMsg.remove();
            }

            // Permettre la soumission du formulaire sans termes sélectionnés
            return true;
        });
    }
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

/**
 * Initialise les modals pour la sélection d'auteurs et d'activités
 */
function initModals() {
    // Configuration des modals
    const modals = [
        {
            modalId: 'authorModal',
            searchId: 'author-search',
            listId: 'author-list',
            displayId: 'selected-authors-display',
            hiddenInputId: 'author-ids',
            saveButtonId: 'save-authors',
            multiSelect: true
        },
        {
            modalId: 'activityModal',
            searchId: 'activity-search',
            listId: 'activity-list',
            displayId: 'selected-activity-display',
            hiddenInputId: 'activity-id',
            saveButtonId: 'save-activity',
            multiSelect: false,
            required: true
        }
    ];

    // Initialiser chaque modal
    modals.forEach(config => {
        const modal = document.getElementById(config.modalId);
        const search = document.getElementById(config.searchId);
        const list = document.getElementById(config.listId);
        const saveButton = document.getElementById(config.saveButtonId);
        const displayInput = document.getElementById(config.displayId);
        const hiddenInput = document.getElementById(config.hiddenInputId);

        if (!modal || !search || !list || !saveButton || !displayInput || !hiddenInput) {
            console.log(`Elements manquants pour ${config.modalId}:`, {
                modal: !!modal,
                search: !!search,
                list: !!list,
                saveButton: !!saveButton,
                displayInput: !!displayInput,
                hiddenInput: !!hiddenInput
            });
            return;
        }

        console.log(`Initialisation du modal ${config.modalId}`);

        // Fonctionnalité de recherche standard
        const items = list.querySelectorAll('.list-group-item');
        search.addEventListener('input', () => filterList(search, items));

        // Sélection d'éléments
        items.forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                console.log(`Click sur item ${item.dataset.id} dans ${config.modalId}`);

                if (config.multiSelect) {
                    item.classList.toggle('active');
                } else {
                    // Pour single select, enlever active de tous les autres et l'ajouter à celui-ci
                    items.forEach(i => i.classList.remove('active'));
                    item.classList.add('active');
                }

                console.log(`Item ${item.dataset.id} actif:`, item.classList.contains('active'));
            });
        });

        // Sauvegarder la sélection
        saveButton.addEventListener('click', () => {
            const selectedItems = list.querySelectorAll('.list-group-item.active');
            console.log(`Sauvegarde pour ${config.modalId}, items sélectionnés:`, selectedItems.length);

            const selectedNames = Array.from(selectedItems).map(item => item.textContent.trim());
            const selectedIds = Array.from(selectedItems).map(item => item.dataset.id);

            displayInput.value = selectedNames.join('; ');
            if (config.multiSelect) {
                hiddenInput.value = selectedIds.join(',');
            } else {
                hiddenInput.value = selectedIds[0] || '';
            }

            console.log(`Valeurs sauvegardées - Display: "${displayInput.value}", Hidden: "${hiddenInput.value}"`);

            // Ajouter une classe de validation si requis
            if (config.required && hiddenInput.value === '') {
                displayInput.classList.add('is-invalid');
            } else {
                displayInput.classList.remove('is-invalid');
            }

            bootstrap.Modal.getInstance(modal).hide();
        });
    });

    // Fonction pour filtrer les éléments de liste dans les modals
    function filterList(searchInput, listItems) {
        const filter = searchInput.value.toLowerCase();
        listItems.forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(filter) ? '' : 'none';
        });
    }
}
