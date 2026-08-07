@extends('layouts.app')

@section('content')
    <div class="container-fluid py-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1><i class="bi bi-trash"></i> {{ $declassementList->code }} : {{ $declassementList->name }}</h1>
            <span class="badge bg-info fs-6">{{ $declassementList->status->name ?? '' }}</span>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if($declassementList->rejection_reason)
            <div class="alert alert-warning">
                <strong>Motif du rejet :</strong> {{ $declassementList->rejection_reason }}
            </div>
        @endif

        <p class="lead">{{ $declassementList->description }}</p>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="text-muted">Demande d'approbation</h6>
                        @if($declassementList->is_approval_requested)
                            <p class="mb-0">{{ $declassementList->approvalRequestedBy->name ?? '' }}<br>{{ optional($declassementList->approval_requested_date)->format('d/m/Y H:i') }}</p>
                        @else
                            <p class="text-muted mb-0">En attente</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="text-muted">Approbation</h6>
                        @if($declassementList->is_approved)
                            <p class="mb-0">{{ $declassementList->approvedBy->name ?? '' }}<br>{{ optional($declassementList->approved_date)->format('d/m/Y H:i') }}</p>
                        @else
                            <p class="text-muted mb-0">En attente</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="text-muted">Validation</h6>
                        @if($declassementList->is_validated)
                            <p class="mb-0">{{ $declassementList->validatedBy->name ?? '' }}<br>{{ optional($declassementList->validated_date)->format('d/m/Y H:i') }}</p>
                        @else
                            <p class="text-muted mb-0">En attente</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="text-muted">Traitement</h6>
                        @if($declassementList->is_treated)
                            <p class="mb-0">{{ $declassementList->treatedBy->name ?? '' }}<br>{{ optional($declassementList->treated_date)->format('d/m/Y H:i') }}</p>
                        @else
                            <p class="text-muted mb-0">En attente</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="text-primary border-bottom pb-2 mb-0">Dossiers ({{ $declassementList->records->count() }})</h3>
        </div>

        <div class="table-responsive mb-4">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Nom</th>
                        <th>Activité</th>
                        <th>Statut</th>
                        <th>Ajouté par</th>
                        @if(!$declassementList->is_approval_requested)
                            <th></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($declassementList->records as $declassementRecord)
                        <tr>
                            <td>{{ $declassementRecord->record->code ?? '' }}</td>
                            <td>{{ $declassementRecord->record->name ?? '' }}</td>
                            <td>{{ $declassementRecord->record->activity->name ?? '' }}</td>
                            <td>{{ $declassementRecord->record->status->name ?? '' }}</td>
                            <td>{{ $declassementRecord->addedBy->name ?? '' }}</td>
                            @if(!$declassementList->is_approval_requested)
                                <td>
                                    <form method="POST" action="{{ route('declassement-lists.records.remove', [$declassementList, $declassementRecord]) }}" onsubmit="return confirm('Retirer ce dossier de la liste ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle"></i></button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Aucun dossier dans cette liste.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mb-4">
            <h3 class="text-primary border-bottom pb-2">Commentaires</h3>
            @forelse($declassementList->comments as $comment)
                <div class="border-bottom py-2">
                    <strong>{{ $comment->user->name ?? '' }}</strong>
                    <small class="text-muted">{{ $comment->created_at->format('d/m/Y H:i') }}</small>
                    <p class="mb-0">{{ $comment->content }}</p>
                </div>
            @empty
                <p class="text-muted">Aucun commentaire.</p>
            @endforelse

            <form method="POST" action="{{ route('declassement-lists.comments.store', $declassementList) }}" class="mt-3">
                @csrf
                <div class="input-group">
                    <input type="text" name="content" class="form-control" placeholder="Ajouter un commentaire..." required>
                    <button type="submit" class="btn btn-outline-primary">Envoyer</button>
                </div>
            </form>
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <a href="{{ route('declassement-lists.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Retour
            </a>
            <div class="d-flex gap-2">
                @can('update', $declassementList)
                    @if(!$declassementList->is_approval_requested)
                        <a href="{{ route('declassement-lists.edit', $declassementList) }}" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Modifier
                        </a>
                        <form method="POST" action="{{ route('declassement-lists.request-approval', $declassementList) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary" {{ $declassementList->records->isEmpty() ? 'disabled' : '' }}>
                                <i class="bi bi-send"></i> Demander l'approbation
                            </button>
                        </form>
                    @elseif(!$declassementList->is_approved)
                        <form method="POST" action="{{ route('declassement-lists.approve', $declassementList) }}">
                            @csrf
                            <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Approuver</button>
                        </form>
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="bi bi-x-circle"></i> Rejeter
                        </button>
                    @elseif(!$declassementList->is_validated)
                        <form method="POST" action="{{ route('declassement-lists.validate', $declassementList) }}">
                            @csrf
                            <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Valider</button>
                        </form>
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="bi bi-x-circle"></i> Rejeter
                        </button>
                    @elseif(!$declassementList->is_treated)
                        <form method="POST" action="{{ route('declassement-lists.process', $declassementList) }}" onsubmit="return confirm('Cette action marquera les dossiers comme éliminés. Confirmer ?');">
                            @csrf
                            <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> Traiter (exécuter l'élimination)</button>
                        </form>
                    @endif
                @endcan
            </div>
        </div>
    </div>

    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('declassement-lists.reject', $declassementList) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Rejeter la liste</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">Motif du rejet</label>
                        <textarea name="reason" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-danger">Rejeter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
