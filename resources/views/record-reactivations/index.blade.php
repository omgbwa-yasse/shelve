@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1><i class="bi bi-arrow-counterclockwise"></i> Réactivations de dossiers</h1>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Dossier</th>
                        <th>Statut antérieur</th>
                        <th>Motif</th>
                        <th>Demandé par</th>
                        <th>Statut</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reactivations as $reactivation)
                        <tr>
                            <td>{{ $reactivation->record->code ?? '' }} : {{ $reactivation->record->name ?? '' }}</td>
                            <td>{{ $reactivation->previousStatus->name ?? '' }}</td>
                            <td>{{ $reactivation->reason }}</td>
                            <td>{{ $reactivation->requestedBy->name ?? '' }}<br><small class="text-muted">{{ optional($reactivation->requested_date)->format('d/m/Y H:i') }}</small></td>
                            <td>
                                @if($reactivation->is_approved)
                                    <span class="badge bg-success">Approuvée</span>
                                @elseif($reactivation->rejection_reason)
                                    <span class="badge bg-danger">Rejetée</span>
                                @else
                                    <span class="badge bg-secondary">En attente</span>
                                @endif
                            </td>
                            <td>
                                @can('update', $reactivation)
                                    @if(!$reactivation->is_approved && !$reactivation->rejection_reason)
                                        <form method="POST" action="{{ route('record-reactivations.approve', $reactivation) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-check-circle"></i></button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $reactivation->id }}">
                                            <i class="bi bi-x-circle"></i>
                                        </button>

                                        <div class="modal fade" id="rejectModal{{ $reactivation->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST" action="{{ route('record-reactivations.reject', $reactivation) }}">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Rejeter la réactivation</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <label class="form-label">Motif</label>
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
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Aucune demande de réactivation.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $reactivations->links() }}
    </div>
@endsection
