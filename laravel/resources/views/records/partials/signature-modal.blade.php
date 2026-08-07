@if($medium->signature_status !== 'signed')
    @can('records_update')
        <div class="modal fade" id="signModal{{ $medium->id }}" tabindex="-1">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('records.mediums.sign', [$record, $medium]) }}" class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Signer le support</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Mot de passe</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button class="btn btn-warning"><i class="bi bi-patch-check"></i> Signer</button>
                    </div>
                </form>
            </div>
        </div>
    @endcan
@endif
