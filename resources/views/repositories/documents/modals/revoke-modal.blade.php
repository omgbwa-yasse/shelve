{{-- Modal Révocation Signature --}}
<div class="modal fade" id="revokeModal" tabindex="-1" aria-labelledby="revokeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('documents.revoke-signature', $document) }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="revokeModalLabel">
                        <i class="bi bi-x-circle"></i> Révoquer la signature
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-octagon"></i>
                        <strong>Action critique:</strong> La révocation invalide la signature électronique.
                        Le document ne sera plus considéré comme signé.
                    </div>

                    {{-- Revocation Reason --}}
                    <div class="mb-3">
                        <label for="revocation-reason" class="form-label">Raison de la révocation <span class="text-danger">*</span></label>
                        <textarea name="revocation_reason" id="revocation-reason" class="form-control" rows="3"
                                  placeholder="Expliquez pourquoi vous révoquez cette signature..."
                                  required></textarea>
                        <div class="form-text">
                            Cette raison sera enregistrée et visible dans l'historique.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash-fill"></i> Révoquer définitivement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
