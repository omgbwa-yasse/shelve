{{-- Modal Checkin - Déposer nouvelle version --}}
<div class="modal fade" id="checkinModal" tabindex="-1" role="dialog" aria-labelledby="checkinModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('documents.checkin', $document) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="checkinModalLabel">
                        <i class="fas fa-upload"></i> Déposer une nouvelle version
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <small>
                            <i class="fas fa-info-circle"></i>
                            Cette action créera automatiquement la <strong>version {{ $document->version_number + 1 }}</strong>
                            et libérera la réservation.
                        </small>
                    </div>

                    {{-- Upload File --}}
                    <div class="form-group">
                        <label for="checkin-file">Fichier <span class="text-danger">*</span></label>
                        <input type="file" name="file" id="checkin-file" class="form-control-file" required>
                        <small class="form-text text-muted">
                            Taille max: 50 MB
                            @if($document->type && $document->type->allowed_mime_types)
                                <br>Types acceptés: {{ implode(', ', json_decode($document->type->allowed_mime_types, true) ?? []) }}
                            @endif
                        </small>
                    </div>

                    {{-- Version Notes --}}
                    <div class="form-group">
                        <label for="checkin-notes">Notes de version</label>
                        <textarea name="checkin_notes" id="checkin-notes" class="form-control" rows="3"
                                  placeholder="Décrivez les modifications apportées..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Déposer la version
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
