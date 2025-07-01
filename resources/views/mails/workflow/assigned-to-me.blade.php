@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ __('Mes courriers assignés') }}</h4>
                    <div class="btn-group" role="group">
                        <a href="{{ route('mails.workflow.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a href="{{ route('mails.workflow.overdue') }}" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-exclamation-triangle"></i> En retard
                        </a>
                        <a href="{{ route('mails.workflow.approaching-deadline') }}" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-clock"></i> Échéances proches
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Statistiques rapides -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $stats['total'] }}</h3>
                                    <p class="mb-0">Total assigné</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $stats['in_progress'] }}</h3>
                                    <p class="mb-0">En cours</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $stats['overdue'] }}</h3>
                                    <p class="mb-0">En retard</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $stats['completed_today'] }}</h3>
                                    <p class="mb-0">Terminés aujourd'hui</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtres -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <div class="col-md-3">
                                    <label for="status" class="form-label">Statut</label>
                                    <select name="status" id="status" class="form-select">
                                        <option value="">Tous les statuts</option>
                                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Brouillon</option>
                                        <option value="pending_review" {{ request('status') === 'pending_review' ? 'selected' : '' }}>En attente de révision</option>
                                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>En cours</option>
                                        <option value="pending_approval" {{ request('status') === 'pending_approval' ? 'selected' : '' }}>En attente d'approbation</option>
                                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Terminé</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="priority" class="form-label">Priorité</label>
                                    <select name="priority" id="priority" class="form-select">
                                        <option value="">Toutes priorités</option>
                                        <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                        <option value="normal" {{ request('priority') === 'normal' ? 'selected' : '' }}>Normal</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="search" class="form-label">Recherche</label>
                                    <input type="text" name="search" id="search" class="form-control"
                                           value="{{ request('search') }}" placeholder="Objet, expéditeur...">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="fas fa-search"></i> Filtrer
                                    </button>
                                    <a href="{{ route('mails.workflow.assigned-to-me') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-undo"></i>
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    @if($assignedMails->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>{{ __('Objet') }}</th>
                                        <th>{{ __('Expéditeur') }}</th>
                                        <th>{{ __('Date assignation') }}</th>
                                        <th>{{ __('Échéance') }}</th>
                                        <th>{{ __('Statut') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($assignedMails as $mail)
                                        @php
                                            $isOverdue = $mail->deadline && $mail->deadline->isPast();
                                            $isApproachingDeadline = $mail->deadline && $mail->deadline->diffInHours() <= 24;
                                            $rowClass = $isOverdue ? 'table-danger' : ($isApproachingDeadline ? 'table-warning' : '');
                                        @endphp
                                        <tr class="{{ $rowClass }}">
                                            <td>
                                                <a href="{{ route('mails.show', $mail->id) }}" class="text-decoration-none">
                                                    {{ Str::limit($mail->object, 50) }}
                                                </a>
                                                @if($isOverdue)
                                                    <i class="fas fa-exclamation-triangle text-danger ms-1" title="En retard"></i>
                                                @elseif($isApproachingDeadline)
                                                    <i class="fas fa-clock text-warning ms-1" title="Échéance proche"></i>
                                                @endif
                                            </td>
                                            <td>{{ $mail->sender_name ?? 'N/A' }}</td>
                                            <td>
                                                {{ $mail->assigned_at ? $mail->assigned_at->format('d/m/Y H:i') : 'N/A' }}
                                            </td>
                                            <td>
                                                @if($mail->deadline)
                                                    <span class="{{ $isOverdue ? 'text-danger fw-bold' : ($isApproachingDeadline ? 'text-warning fw-bold' : '') }}">
                                                        {{ $mail->deadline->format('d/m/Y H:i') }}
                                                        <br>
                                                        <small class="text-muted">{{ $mail->deadline->diffForHumans() }}</small>
                                                    </span>
                                                @else
                                                    <span class="text-muted">Pas d'échéance</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $mail->getStatusBadgeClass() }}">
                                                    {{ $mail->getStatusLabel() }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('mails.show', $mail->id) }}" class="btn btn-outline-primary" title="Voir">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if(auth()->user()->can('update', $mail))
                                                        <a href="{{ route('mails.edit', $mail->id) }}" class="btn btn-outline-warning" title="Modifier">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endif

                                                    <!-- Boutons de changement de statut rapide -->
                                                    @if($mail->status !== 'in_progress')
                                                        <form method="POST" action="{{ route('mails.workflow.update-status', $mail->id) }}" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="status" value="in_progress">
                                                            <button type="submit" class="btn btn-outline-info" title="Marquer en cours">
                                                                <i class="fas fa-play"></i>
                                                            </button>
                                                        </form>
                                                    @endif

                                                    @if($mail->status !== 'completed')
                                                        <form method="POST" action="{{ route('mails.workflow.update-status', $mail->id) }}" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="status" value="completed">
                                                            <button type="submit" class="btn btn-outline-success" title="Marquer terminé">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center">
                            {{ $assignedMails->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            @if(request()->hasAny(['status', 'priority', 'search']))
                                Aucun courrier ne correspond aux critères de recherche.
                            @else
                                Aucun courrier ne vous est actuellement assigné.
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
