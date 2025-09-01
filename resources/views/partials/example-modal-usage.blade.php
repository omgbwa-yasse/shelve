{{-- Examples of how to use the new modal components --}}

{{-- Example 1: List Modal for displaying shelves --}}
@include('partials.list-modal', [
    'modalId' => 'shelvesListModal',
    'title' => 'Toutes les étagères',
    'icon' => 'bi bi-bookshelf',
    'searchPlaceholder' => 'Rechercher par code, salle, description...',
    'useCards' => true,
    'filters' => [
        'available' => 'Étagères disponibles',
        'full' => 'Étagères pleines',
        'empty' => 'Étagères vides'
    ],
    'items' => [
        [
            'title' => 'Étagère E001',
            'subtitle' => 'Salle Archives - Bâtiment A',
            'description' => 'Étagère métallique 5 niveaux, capacité 100 contenants',
            'search_text' => 'e001 archives batiment a metallique',
            'filter_value' => 'available',
            'image' => [
                'icon' => 'bi bi-bookshelf',
                'class' => 'text-primary'
            ],
            'badges' => [
                ['text' => 'Disponible', 'type' => 'success', 'icon' => 'bi bi-check-circle'],
                ['text' => '75/100', 'type' => 'warning', 'icon' => 'bi bi-archive']
            ],
            'actions' => [
                ['url' => route('shelves.show', 1), 'icon' => 'bi bi-eye', 'type' => 'outline-primary', 'label' => 'Voir'],
                ['url' => route('shelves.edit', 1), 'icon' => 'bi bi-pencil', 'type' => 'outline-warning', 'label' => 'Modifier']
            ]
        ],
        // More items...
    ],
    'quickActions' => [
        [
            'label' => 'Nouvelle étagère',
            'icon' => 'bi bi-plus-circle',
            'onclick' => "window.location.href = '" . route('shelves.create') . "'"
        ],
        [
            'label' => 'Exporter la liste',
            'icon' => 'bi bi-download',
            'onclick' => 'exportShelves()'
        ]
    ],
    'footerActions' => [
        [
            'label' => 'Imprimer la liste',
            'icon' => 'bi bi-printer',
            'type' => 'outline-secondary',
            'onclick' => 'window.print()'
        ],
        [
            'label' => 'Fermer',
            'icon' => 'bi bi-x-circle',
            'type' => 'secondary',
            'dismiss' => true
        ]
    ]
])

{{-- Example 2: Selection Modal for choosing containers --}}
@include('partials.selection-modal', [
    'modalId' => 'containerSelectionModal',
    'title' => 'Sélectionner des contenants',
    'icon' => 'bi bi-check-square',
    'searchPlaceholder' => 'Rechercher par code, étagère...',
    'multiple' => true,
    'confirmLabel' => 'Sélectionner les contenants',
    'onConfirm' => 'handleContainerSelection()',
    'items' => [
        [
            'value' => '1',
            'title' => 'Contenant C001',
            'subtitle' => 'Étagère E001 - Position (1,1,1)',
            'description' => 'Boîte d\'archives standard, documents 2023',
            'search_text' => 'c001 e001 boite archives 2023',
            'icon' => [
                'name' => 'bi bi-archive',
                'class' => 'text-warning'
            ],
            'badges' => [
                ['text' => 'Actif', 'type' => 'success']
            ],
            'meta' => [
                ['icon' => 'bi bi-calendar', 'text' => '2023'],
                ['icon' => 'bi bi-geo-alt', 'text' => '(1,1,1)']
            ]
        ],
        // More items...
    ]
])

{{-- Example 3: Form Modal for creating a new shelf --}}
@include('partials.form-modal', [
    'modalId' => 'createShelfModal',
    'title' => 'Créer une nouvelle étagère',
    'icon' => 'bi bi-plus-circle',
    'headerColor' => 'primary',
    'method' => 'POST',
    'action' => route('shelves.store'),
    'submitLabel' => 'Créer l\'étagère',
    'submitIcon' => 'bi bi-plus-lg',
    'description' => 'Remplissez les informations ci-dessous pour créer une nouvelle étagère.',
    'fields' => [
        [
            'name' => 'code',
            'label' => 'Code de l\'étagère',
            'type' => 'text',
            'required' => true,
            'placeholder' => 'Ex: E001',
            'width' => '6',
            'help' => 'Code unique pour identifier l\'étagère'
        ],
        [
            'name' => 'name',
            'label' => 'Nom de l\'étagère',
            'type' => 'text',
            'placeholder' => 'Ex: Étagère principale',
            'width' => '6'
        ],
        [
            'name' => 'room_id',
            'label' => 'Salle',
            'type' => 'select',
            'required' => true,
            'width' => '6',
            'options' => [
                '1' => 'Salle Archives A',
                '2' => 'Salle Archives B',
                '3' => 'Local tampon'
            ]
        ],
        [
            'name' => 'capacity',
            'label' => 'Capacité maximale',
            'type' => 'number',
            'required' => true,
            'min' => '1',
            'max' => '1000',
            'width' => '6',
            'help' => 'Nombre maximum de contenants'
        ],
        [
            'name' => 'description',
            'label' => 'Description',
            'type' => 'textarea',
            'rows' => '3',
            'width' => '12',
            'placeholder' => 'Description optionnelle de l\'étagère...'
        ],
        [
            'name' => 'visibility',
            'label' => 'Visibilité',
            'type' => 'radio',
            'required' => true,
            'width' => '6',
            'options' => [
                'public' => 'Public',
                'private' => 'Privé',
                'inherit' => 'Hériter de la salle'
            ]
        ],
        [
            'name' => 'is_active',
            'label' => 'Étagère active',
            'type' => 'checkbox',
            'checked' => true,
            'width' => '6'
        ]
    ],
    'leftActions' => [
        [
            'label' => 'Réinitialiser',
            'icon' => 'bi bi-arrow-clockwise',
            'type' => 'outline-warning',
            'onclick' => 'document.getElementById("createShelfModal_form").reset()'
        ]
    ]
])

{{-- Example 4: Form Modal for bulk operations --}}
@include('partials.form-modal', [
    'modalId' => 'bulkOperationsModal',
    'title' => 'Opérations en lot',
    'icon' => 'bi bi-gear',
    'headerColor' => 'warning',
    'method' => 'POST',
    'action' => route('containers.bulk-update'),
    'submitLabel' => 'Appliquer les changements',
    'submitColor' => 'warning',
    'description' => 'Appliquer des modifications à plusieurs contenants sélectionnés.',
    'fields' => [
        [
            'name' => 'operation',
            'label' => 'Type d\'opération',
            'type' => 'select',
            'required' => true,
            'width' => '12',
            'options' => [
                'move' => 'Déplacer vers une autre étagère',
                'update_status' => 'Changer le statut',
                'update_property' => 'Changer la propriété',
                'delete' => 'Supprimer les contenants'
            ]
        ],
        [
            'name' => 'target_shelf_id',
            'label' => 'Étagère de destination',
            'type' => 'select',
            'width' => '6',
            'options' => [
                '1' => 'Étagère E001',
                '2' => 'Étagère E002',
                '3' => 'Étagère E003'
            ]
        ],
        [
            'name' => 'new_status_id',
            'label' => 'Nouveau statut',
            'type' => 'select',
            'width' => '6',
            'options' => [
                '1' => 'Actif',
                '2' => 'Archivé',
                '3' => 'En traitement'
            ]
        ],
        [
            'name' => 'selected_containers',
            'type' => 'hidden'
        ]
    ]
])

{{-- Buttons to trigger the modals --}}
<div class="example-buttons mt-4">
    <h5>Exemples d'utilisation :</h5>
    <div class="btn-group" role="group">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#shelvesListModal">
            <i class="bi bi-list"></i> Liste des étagères
        </button>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#containerSelectionModal">
            <i class="bi bi-check-square"></i> Sélectionner contenants
        </button>
        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#createShelfModal">
            <i class="bi bi-plus"></i> Nouvelle étagère
        </button>
        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#bulkOperationsModal">
            <i class="bi bi-gear"></i> Opérations en lot
        </button>
    </div>
</div>

<script>
// Example JavaScript functions for modal interactions
function handleContainerSelection() {
    const modal = document.getElementById('containerSelectionModal');
    const selectedValues = modal.getSelectedValues();
    
    console.log('Selected containers:', selectedValues);
    
    // You can now use the selected values
    if (selectedValues.length > 0) {
        // Example: populate bulk operations form
        const bulkForm = document.getElementById('bulkOperationsModal_form');
        if (bulkForm) {
            const hiddenInput = bulkForm.querySelector('[name="selected_containers"]');
            if (hiddenInput) {
                hiddenInput.value = JSON.stringify(selectedValues);
            }
        }
        
        alert(`${selectedValues.length} contenant(s) sélectionné(s)`);
    } else {
        alert('Aucun contenant sélectionné');
    }
}

function exportShelves() {
    // Example export functionality
    window.open('/shelves/export', '_blank');
}

// Example of how to pre-select values in selection modal
function preselectContainers(containerIds) {
    const modal = document.getElementById('containerSelectionModal');
    if (modal && modal.setSelectedValues) {
        modal.setSelectedValues(containerIds);
    }
}

// Example of how to populate form modal with existing data
function editShelf(shelfData) {
    const form = document.getElementById('createShelfModal_form');
    
    // Change modal title
    document.getElementById('createShelfModalLabel').textContent = 'Modifier l\'étagère';
    
    // Change form action and method
    form.action = `/shelves/${shelfData.id}`;
    form.method = 'POST';
    
    // Add method field for PUT request
    let methodField = form.querySelector('[name="_method"]');
    if (!methodField) {
        methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        form.appendChild(methodField);
    }
    methodField.value = 'PUT';
    
    // Populate form fields
    Object.keys(shelfData).forEach(key => {
        const field = form.querySelector(`[name="${key}"]`);
        if (field) {
            if (field.type === 'checkbox') {
                field.checked = shelfData[key];
            } else if (field.type === 'radio') {
                const radio = form.querySelector(`[name="${key}"][value="${shelfData[key]}"]`);
                if (radio) radio.checked = true;
            } else {
                field.value = shelfData[key];
            }
        }
    });
    
    // Show the modal
    new bootstrap.Modal(document.getElementById('createShelfModal')).show();
}
</script>