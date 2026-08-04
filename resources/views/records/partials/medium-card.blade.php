@php($isDigital = $medium->attachment_id !== null)
<div class="card mb-3 {{ $medium->is_principal ? 'border-primary' : '' }}">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h6 class="mb-1">
                    <i class="bi {{ $isDigital ? 'bi-hdd' : 'bi-box' }}"></i>
                    {{ $medium->support?->name ?? 'Support' }}
                    @if($isDigital && $medium->attachment)
                        <span class="text-muted">— {{ $medium->attachment->name }} ({{ $medium->attachment->file_size_human }})</span>
                    @elseif(!$isDigital && $medium->container)
                        <span class="text-muted">— {{ $medium->container->code }}</span>
                    @endif
                    @if(!$medium->is_principal)
                        <span class="badge bg-light text-dark">exemplaire secondaire</span>
                    @endif
                    @if($medium->copy_code)
                        <span class="badge bg-light text-dark">Ex. {{ $medium->copy_code }}</span>
                    @endif
                </h6>
                <div class="mt-1">
                    @if($medium->status)
                        <span class="badge {{ $medium->status === 'final' ? 'bg-success' : 'bg-warning text-dark' }}">{{ $medium->status }}</span>
                    @endif
                    @if($medium->signature_status === 'signed')
                        <span class="badge bg-success"><i class="bi bi-patch-check"></i> Signé
                            @if($medium->signed_at) le {{ $medium->signed_at->format('d/m/Y') }} @endif
                            @if($medium->signer) par {{ $medium->signer->name }} @endif
                        </span>
                    @elseif($medium->signature_status === 'rejected')
                        <span class="badge bg-danger">Signature rejetée</span>
                    @else
                        <span class="badge bg-light text-dark">Non signé</span>
                    @endif
                    @if($medium->isCheckedOut())
                        <span class="badge bg-info"><i class="bi bi-lock"></i> Check-out
                            @if($medium->checkedOutUser) par {{ $medium->checkedOutUser->name }} @endif
                        </span>
                    @endif
                </div>
            </div>
            <div class="d-flex gap-1">
                @if($isDigital && $medium->attachment)
                    <a href="{{ route('records.mediums.download', [$record, $medium]) }}" class="btn btn-sm btn-outline-primary" title="Télécharger">
                        <i class="bi bi-download"></i>
                    </a>
                @endif
                @can('records_update')
                    @if(!$medium->isCheckedOut())
                        <form method="POST" action="{{ route('records.mediums.checkout', [$record, $medium]) }}">
                            @csrf
                            <button class="btn btn-sm btn-outline-info" title="Check-out"><i class="bi bi-lock"></i></button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('records.mediums.checkin', [$record, $medium]) }}">
                            @csrf
                            <button class="btn btn-sm btn-outline-success" title="Check-in"><i class="bi bi-unlock"></i></button>
                        </form>
                        <form method="POST" action="{{ route('records.mediums.cancel-checkout', [$record, $medium]) }}">
                            @csrf
                            <button class="btn btn-sm btn-outline-secondary" title="Annuler le check-out"><i class="bi bi-x"></i></button>
                        </form>
                    @endif
                    @if($medium->signature_status !== 'signed')
                        <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#signModal{{ $medium->id }}" title="Signer">
                            <i class="bi bi-patch-check"></i>
                        </button>
                    @endif
                    <form method="POST" action="{{ route('records.remove-medium', [$record, $medium]) }}">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" title="Retirer le support" onclick="return confirm('Retirer ce support ?')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                @endcan
            </div>
        </div>
    </div>
</div>

@if($medium->signature_status !== 'signed')
    @can('records_update')
        <div class="modal fade" id="signModal{{ $medium->id }}" tabindex="-1">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('records.mediums.sign', [$record, $medium]) }}" class="modal-content">
                    @csrf
                    <div class="modal-header"><h5 class="modal-title">Signer le support</h5>
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
