{{-- Modal Checkin - Déposer nouvelle version --}}
<div class="modal fade" id="checkinModal" tabindex="-1" aria-labelledby="checkinModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('documents.checkin', $document) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="checkinModalLabel">
                        <i class="bi bi-upload"></i> Déposer une nouvelle version
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <small>
                            <i class="bi bi-info-circle"></i>
                            Cette action créera automatiquement la <strong>version {{ $document->version_number + 1 }}</strong>
                            et libérera la réservation.
                        </small>
                    </div>

                    {{-- Upload File --}}
                    <div class="mb-3">
                        <label for="checkin-file" class="form-label">Fichier <span class="text-danger">*</span></label>
                        <input type="file" name="file" id="checkin-file" class="form-control" required>
                        <div class="form-text">
                            Taille max: 50 MB
                            @if($document->type && $document->type->allowed_mime_types)
                                <br>Types acceptés: {{ implode(', ', json_decode($document->type->allowed_mime_types, true) ?? []) }}
                            @endif
                        </div>
                    </div>

                    {{-- Version Notes --}}
                    <div class="mb-3">
                        <label for="checkin-notes" class="form-label">Notes de version</label>
                        <textarea name="checkin_notes" id="checkin-notes" class="form-control" rows="3"
                                  placeholder="Décrivez les modifications apportées..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Déposer la version
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
