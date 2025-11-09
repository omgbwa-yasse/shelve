{{-- Modal Révocation Signature --}}
<div class="modal fade" id="revokeModal" tabindex="-1" role="dialog" aria-labelledby="revokeModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('documents.revoke-signature', $document) }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="revokeModalLabel">
                        <i class="fas fa-ban"></i> Révoquer la signature
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <strong>Action critique:</strong> La révocation invalide la signature électronique.
                        Le document ne sera plus considéré comme signé.
                    </div>

                    {{-- Revocation Reason --}}
                    <div class="form-group">
                        <label for="revocation-reason">Raison de la révocation <span class="text-danger">*</span></label>
                        <textarea name="revocation_reason" id="revocation-reason" class="form-control" rows="3"
                                  placeholder="Expliquez pourquoi vous révoquez cette signature..."
                                  required></textarea>
                        <small class="form-text text-muted">
                            Cette raison sera enregistrée et visible dans l'historique.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt"></i> Révoquer définitivement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
