@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Détails du Prompt</h5>
                    <div>
                        <a href="{{ route('settings.prompts.edit', $prompt) }}" class="btn btn-warning btn-sm me-2">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                        <a href="{{ route('settings.prompts.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Retour à la liste
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Informations générales</h6>
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 30%">Titre</th>
                                    <td>{{ $prompt->title ?? 'Sans titre' }}</td>
                                </tr>
                                <tr>
                                    <th>Type</th>
                                    <td>
                                        @if($prompt->is_system)
                                            <span class="badge bg-primary">Système</span>
                                        @else
                                            <span class="badge bg-secondary">Utilisateur</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Organisation</th>
                                    <td>{{ $prompt->organisation ? $prompt->organisation->name : 'Global' }}</td>
                                </tr>
                                <tr>
                                    <th>Créé par</th>
                                    <td>{{ $prompt->user ? $prompt->user->name : 'Système' }}</td>
                                </tr>
                                <tr>
                                    <th>Date de création</th>
                                    <td>{{ $prompt->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Dernière modification</th>
                                    <td>{{ $prompt->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Statistiques d'utilisation</h6>
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 50%">Nombre total d'utilisations</th>
                                    <td>{{ $prompt->transactions->count() }}</td>
                                </tr>
                                <tr>
                                    <th>Succès</th>
                                    <td>{{ $prompt->transactions->where('status', 'succeeded')->count() }}</td>
                                </tr>
                                <tr>
                                    <th>Échecs</th>
                                    <td>{{ $prompt->transactions->where('status', 'failed')->count() }}</td>
                                </tr>
                                <tr>
                                    <th>Annulés</th>
                                    <td>{{ $prompt->transactions->where('status', 'cancelled')->count() }}</td>
                                </tr>
                                <tr>
                                    <th>En cours</th>
                                    <td>{{ $prompt->transactions->where('status', 'started')->count() }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <h6>Contenu du prompt</h6>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <pre style="white-space: pre-wrap; word-break: break-word;">{{ $prompt->content }}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Historique des transactions</h5>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date début</th>
                                    <th>Statut</th>
                                    <th>Modèle</th>
                                    <th>Fournisseur</th>
                                    <th>Entité</th>
                                    <th>Tokens IN/OUT</th>
                                    <th>Latence</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->id }}</td>
                                        <td>{{ $transaction->started_at ? $transaction->started_at->format('d/m/Y H:i:s') : 'N/A' }}</td>
                                        <td>
                                            @if($transaction->status === 'succeeded')
                                                <span class="badge bg-success">Succès</span>
                                            @elseif($transaction->status === 'failed')
                                                <span class="badge bg-danger" data-toggle="tooltip" title="{{ $transaction->error_message }}">Échec</span>
                                            @elseif($transaction->status === 'cancelled')
                                                <span class="badge bg-warning">Annulé</span>
                                            @else
                                                <span class="badge bg-info">En cours</span>
                                            @endif
                                        </td>
                                        <td>{{ $transaction->model ?? 'N/A' }}</td>
                                        <td>{{ $transaction->model_provider ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $transaction->entity }}</span>
                                            @if($transaction->entity_ids)
                                                <small>({{ is_array($transaction->entity_ids) ? count($transaction->entity_ids) : 1 }} éléments)</small>
                                            @endif
                                        </td>
                                        <td>{{ $transaction->tokens_input ?? '0' }} / {{ $transaction->tokens_output ?? '0' }}</td>
                                        <td>{{ $transaction->latency_ms ? $transaction->latency_ms . ' ms' : 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">Aucune transaction trouvée pour ce prompt</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $transactions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@endpush
