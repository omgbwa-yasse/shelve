@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ __('Journal d\'audit des courriers') }}</h4>
                    <div class="btn-group" role="group">
                        <a href="{{ route('mails.workflow.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#exportModal">
                            <i class="fas fa-download"></i> Exporter
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filtres -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <div class="col-md-2">
                                    <label for="mail_id" class="form-label">ID Courrier</label>
                                    <input type="number" name="mail_id" id="mail_id" class="form-control"
                                           value="{{ request('mail_id') }}" placeholder="ID">
                                </div>
                                <div class="col-md-3">
                                    <label for="action" class="form-label">Action</label>
                                    <select name="action" id="action" class="form-select">
                                        <option value="">Toutes les actions</option>
                                        <option value="created" {{ request('action') === 'created' ? 'selected' : '' }}>Créé</option>
                                        <option value="updated" {{ request('action') === 'updated' ? 'selected' : '' }}>Modifié</option>
                                        <option value="status_changed" {{ request('action') === 'status_changed' ? 'selected' : '' }}>Statut changé</option>
                                        <option value="assigned" {{ request('action') === 'assigned' ? 'selected' : '' }}>Assigné</option>
                                        <option value="unassigned" {{ request('action') === 'unassigned' ? 'selected' : '' }}>Désassigné</option>
                                        <option value="deadline_set" {{ request('action') === 'deadline_set' ? 'selected' : '' }}>Échéance définie</option>
                                        <option value="commented" {{ request('action') === 'commented' ? 'selected' : '' }}>Commenté</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="user_id" class="form-label">Utilisateur</label>
                                    <select name="user_id" id="user_id" class="form-select">
                                        <option value="">Tous les utilisateurs</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="date_from" class="form-label">Date début</label>
                                    <input type="date" name="date_from" id="date_from" class="form-control"
                                           value="{{ request('date_from') }}">
                                </div>
                                <div class="col-md-2">
                                    <label for="date_to" class="form-label">Date fin</label>
                                    <input type="date" name="date_to" id="date_to" class="form-control"
                                           value="{{ request('date_to') }}">
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="fas fa-search"></i> Filtrer
                                    </button>
                                    <a href="{{ route('mails.workflow.audit-trail') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-undo"></i> Réinitialiser
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    @if($auditTrail->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-sm">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>{{ __('Date/Heure') }}</th>
                                        <th>{{ __('Courrier') }}</th>
                                        <th>{{ __('Action') }}</th>
                                        <th>{{ __('Utilisateur') }}</th>
                                        <th>{{ __('Détails') }}</th>
                                        <th>{{ __('IP') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($auditTrail as $history)
                                        <tr>
                                            <td>
                                                <small>
                                                    {{ $history->created_at->format('d/m/Y H:i:s') }}
                                                    <br>
                                                    <span class="text-muted">{{ $history->created_at->diffForHumans() }}</span>
                                                </small>
                                            </td>
                                            <td>
                                                @if($history->mail)
                                                    <a href="{{ route('mails.show', $history->mail->id) }}" class="text-decoration-none">
                                                        <strong>#{{ $history->mail->id }}</strong>
                                                        <br>
                                                        <small>{{ Str::limit($history->mail->object, 30) }}</small>
                                                    </a>
                                                @else
                                                    <span class="text-muted">Courrier supprimé</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $history->getActionBadgeClass() }}">
                                                    {{ $history->getActionLabel() }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($history->user)
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-user-circle me-1"></i>
                                                        {{ $history->user->name }}
                                                    </div>
                                                    <small class="text-muted">{{ $history->user->email }}</small>
                                                @else
                                                    <span class="text-muted">Système</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($history->details)
                                                    <button type="button" class="btn btn-sm btn-outline-info"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#detailsModal{{ $history->id }}">
                                                        <i class="fas fa-info-circle"></i> Voir détails
                                                    </button>

                                                    <!-- Modal pour les détails -->
                                                    <div class="modal fade" id="detailsModal{{ $history->id }}" tabindex="-1">
                                                        <div class="modal-dialog modal-lg">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Détails de l'action</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <pre class="bg-light p-3 rounded">{{ json_encode($history->details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ $history->ip_address ?? 'N/A' }}
                                                </small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center">
                            {{ $auditTrail->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            @if(request()->hasAny(['mail_id', 'action', 'user_id', 'date_from', 'date_to']))
                                Aucune entrée d'audit ne correspond aux critères de recherche.
                            @else
                                Aucune entrée d'audit disponible.
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal d'export -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Exporter les données d'audit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('mails.workflow.export-audit') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="export_format" class="form-label">Format d'export</label>
                        <select name="format" id="export_format" class="form-select" required>
                            <option value="csv">CSV</option>
                            <option value="excel">Excel</option>
                            <option value="pdf">PDF</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="export_date_from" class="form-label">Date début</label>
                        <input type="date" name="date_from" id="export_date_from" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="export_date_to" class="form-label">Date fin</label>
                        <input type="date" name="date_to" id="export_date_to" class="form-control">
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-download"></i> Exporter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Auto-actualisation périodique pour les nouvelles entrées
setInterval(function() {
    // Vérifier s'il y a de nouvelles entrées sans rafraîchir toute la page
    fetch(window.location.href + '&ajax=1')
        .then(response => response.json())
        .then(data => {
            if (data.new_entries > 0) {
                // Afficher une alerte discrète
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-info alert-dismissible fade show position-fixed';
                alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 1050;';
                alertDiv.innerHTML = `
                    <i class="fas fa-info-circle"></i>
                    ${data.new_entries} nouvelle(s) entrée(s) d'audit disponible(s).
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.body.appendChild(alertDiv);

                // Auto-masquer après 5 secondes
                setTimeout(() => {
                    alertDiv.remove();
                }, 5000);
            }
        })
        .catch(error => console.log('Erreur vérification nouvelles entrées:', error));
}, 30000); // Vérifier toutes les 30 secondes
</script>
@endsection
