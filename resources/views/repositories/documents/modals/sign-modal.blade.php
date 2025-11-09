{{-- Modal Signature Électronique --}}
<div class="modal fade" id="signModal" tabindex="-1" role="dialog" aria-labelledby="signModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('documents.sign', $document) }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="signModalLabel">
                        <i class="fas fa-signature"></i> Signature Électronique
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Attention:</strong> La signature électronique garantit que vous approuvez
                        l'intégrité et le contenu de ce document. Cette action est irréversible (sauf révocation).
                    </div>

                    {{-- Password Confirmation --}}
                    <div class="form-group">
                        <label for="signature-password">Votre mot de passe <span class="text-danger">*</span></label>
                        <input type="password" name="signature_password" id="signature-password" class="form-control"
                               placeholder="Confirmez votre identité" required autofocus>
                        <small class="form-text text-muted">
                            Requis pour valider votre signature électronique.
                        </small>
                    </div>

                    {{-- Signature Reason --}}
                    <div class="form-group">
                        <label for="signature-reason">Raison de la signature (optionnel)</label>
                        <input type="text" name="signature_reason" id="signature-reason" class="form-control"
                               placeholder="Ex: Validation technique, Approbation managériale...">
                    </div>

                    {{-- Info Signature --}}
                    <div class="card bg-light">
                        <div class="card-body p-2">
                            <small class="text-muted">
                                <strong>Informations de signature:</strong><br>
                                <i class="fas fa-user"></i> Signataire: {{ Auth::user()->name }}<br>
                                <i class="fas fa-calendar"></i> Date: {{ now()->format('d/m/Y à H:i') }}<br>
                                <i class="fas fa-hashtag"></i> Hash: SHA256 (calculé automatiquement)
                            </small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-pen"></i> Signer le document
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
