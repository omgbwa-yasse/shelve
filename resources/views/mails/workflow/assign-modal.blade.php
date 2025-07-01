<!-- Modal d'assignation -->
<div class="modal fade" id="assignModal{{ $mail->id }}" tabindex="-1" aria-labelledby="assignModalLabel{{ $mail->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignModalLabel{{ $mail->id }}">
                    <i class="fas fa-user-plus"></i> Assigner le courrier #{{ $mail->id }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="assignForm{{ $mail->id }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="organisation_select{{ $mail->id }}" class="form-label">
                                    <i class="fas fa-building"></i> Organisation *
                                </label>
                                <select class="form-select organisation-select"
                                        id="organisation_select{{ $mail->id }}"
                                        name="assigned_organisation_id"
                                        data-mail-id="{{ $mail->id }}"
                                        required>
                                    <option value="">Sélectionner une organisation...</option>
                                </select>
                                <div class="invalid-feedback">
                                    Veuillez sélectionner une organisation.
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="user_select{{ $mail->id }}" class="form-label">
                                    <i class="fas fa-user"></i> Utilisateur *
                                </label>
                                <select class="form-select user-select"
                                        id="user_select{{ $mail->id }}"
                                        name="assigned_to"
                                        data-mail-id="{{ $mail->id }}"
                                        required
                                        disabled>
                                    <option value="">Sélectionner d'abord une organisation...</option>
                                </select>
                                <div class="invalid-feedback">
                                    Veuillez sélectionner un utilisateur.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="comment{{ $mail->id }}" class="form-label">
                            <i class="fas fa-comment"></i> Commentaire (optionnel)
                        </label>
                        <textarea class="form-control"
                                  id="comment{{ $mail->id }}"
                                  name="comment"
                                  rows="3"
                                  placeholder="Raison de l'assignation, instructions particulières..."></textarea>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Courrier :</strong> {{ Str::limit($mail->object ?? 'Sans objet', 80) }}<br>
                        @if($mail->assignedTo)
                            <strong>Actuellement assigné à :</strong> {{ $mail->assignedTo->name }}
                            @if($mail->assignedOrganisation)
                                ({{ $mail->assignedOrganisation->name }})
                            @endif
                        @else
                            <strong>Statut :</strong> Non assigné
                        @endif
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Annuler
                </button>
                <button type="button" class="btn btn-primary" onclick="submitAssignment({{ $mail->id }})">
                    <i class="fas fa-user-plus"></i> <span class="submit-text">Assigner</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Charger les organisations au chargement du modal
    $('#assignModal{{ $mail->id }}').on('show.bs.modal', function() {
        loadOrganisations({{ $mail->id }});
    });

    // Gestionnaire de changement d'organisation
    $('#organisation_select{{ $mail->id }}').on('change', function() {
        const organisationId = $(this).val();
        const mailId = {{ $mail->id }};

        if (organisationId) {
            loadOrganisationUsers(organisationId, mailId);
        } else {
            resetUserSelect(mailId);
        }
    });
});

function loadOrganisations(mailId) {
    const select = $(`#organisation_select${mailId}`);

    select.html('<option value="">Chargement...</option>').prop('disabled', true);

    fetch('{{ route("mails.workflow.organisations") }}')
        .then(response => response.json())
        .then(data => {
            select.html('<option value="">Sélectionner une organisation...</option>');

            data.forEach(organisation => {
                select.append(`<option value="${organisation.id}">${organisation.name}</option>`);
            });

            select.prop('disabled', false);
        })
        .catch(error => {
            console.error('Erreur lors du chargement des organisations:', error);
            select.html('<option value="">Erreur de chargement</option>');
        });
}

function loadOrganisationUsers(organisationId, mailId) {
    const userSelect = $(`#user_select${mailId}`);

    userSelect.html('<option value="">Chargement...</option>').prop('disabled', true);

    fetch(`{{ url('mails/workflow/organisations') }}/${organisationId}/users`)
        .then(response => response.json())
        .then(data => {
            userSelect.html('<option value="">Sélectionner un utilisateur...</option>');

            if (data.length === 0) {
                userSelect.append('<option value="">Aucun utilisateur actif dans cette organisation</option>');
            } else {
                data.forEach(user => {
                    userSelect.append(`<option value="${user.id}">${user.name} (${user.email})</option>`);
                });
            }

            userSelect.prop('disabled', false);
        })
        .catch(error => {
            console.error('Erreur lors du chargement des utilisateurs:', error);
            userSelect.html('<option value="">Erreur de chargement</option>');
        });
}

function resetUserSelect(mailId) {
    const userSelect = $(`#user_select${mailId}`);
    userSelect.html('<option value="">Sélectionner d\'abord une organisation...</option>').prop('disabled', true);
}

function submitAssignment(mailId) {
    const form = $(`#assignForm${mailId}`);
    const submitBtn = form.closest('.modal').find('.btn-primary');
    const submitText = submitBtn.find('.submit-text');

    // Validation simple côté client
    const organisationId = $(`#organisation_select${mailId}`).val();
    const userId = $(`#user_select${mailId}`).val();

    if (!organisationId || !userId) {
        alert('Veuillez sélectionner une organisation et un utilisateur.');
        return;
    }

    // Désactiver le bouton et changer le texte
    submitBtn.prop('disabled', true);
    submitText.html('<i class="fas fa-spinner fa-spin"></i> Assignation...');

    // Préparer les données
    const formData = new FormData();
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
    formData.append('assigned_organisation_id', organisationId);
    formData.append('assigned_to', userId);
    formData.append('comment', $(`#comment${mailId}`).val());

    // Envoyer la requête
    fetch(`{{ url('mails/workflow') }}/${mailId}/assign-ajax`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Fermer le modal
            $(`#assignModal${mailId}`).modal('hide');

            // Afficher le message de succès
            showSuccessAlert(data.message);

            // Mettre à jour l'interface si nécessaire
            updateAssignmentDisplay(mailId, data);

            // Recharger la page après 2 secondes pour voir les changements
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            showErrorAlert(data.message);
        }
    })
    .catch(error => {
        console.error('Erreur lors de l\'assignation:', error);
        showErrorAlert('Une erreur est survenue lors de l\'assignation.');
    })
    .finally(() => {
        // Réactiver le bouton
        submitBtn.prop('disabled', false);
        submitText.html('<i class="fas fa-user-plus"></i> Assigner');
    });
}

function showSuccessAlert(message) {
    const alertHtml = `
        <div class="alert alert-success alert-dismissible fade show position-fixed"
             style="top: 20px; right: 20px; z-index: 1060; min-width: 300px;">
            <i class="fas fa-check-circle"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $('body').append(alertHtml);

    // Auto-masquer après 5 secondes
    setTimeout(() => {
        $('.alert-success').fadeOut();
    }, 5000);
}

function showErrorAlert(message) {
    const alertHtml = `
        <div class="alert alert-danger alert-dismissible fade show position-fixed"
             style="top: 20px; right: 20px; z-index: 1060; min-width: 300px;">
            <i class="fas fa-exclamation-circle"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $('body').append(alertHtml);

    // Auto-masquer après 8 secondes
    setTimeout(() => {
        $('.alert-danger').fadeOut();
    }, 8000);
}

function updateAssignmentDisplay(mailId, data) {
    // Mettre à jour les badges ou textes d'assignation sur la page
    const assignmentBadge = $(`.assignment-info[data-mail-id="${mailId}"]`);
    if (assignmentBadge.length) {
        assignmentBadge.html(`
            <span class="badge badge-info">
                ${data.assigned_user.name} (${data.assigned_organisation.name})
            </span>
        `);
    }
}
</script>
