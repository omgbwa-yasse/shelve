@extends('layouts.app')

@section('title', 'Dashboard Workflow - Courrier')

@section('content')
<div class="container-fluid px-4 py-6">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-diagram-3 mr-2"></i>Dashboard Workflow
        </h1>
        <div class="d-flex">
            <button class="btn btn-primary btn-sm" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Actualiser
            </button>
        </div>
    </div>

    <!-- Statistiques en cartes -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                                Assignés à moi
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['assigned_to_me'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-person-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                En retard
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['overdue'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
    <!-- Actions rapides -->
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Échéances proches
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['approaching_deadline'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-alarm fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                En cours
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['in_progress'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Courriers récents -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Courriers récents assignés</h6>
                    <a href="{{ route('mails.workflow.assigned-to-me') }}" class="btn btn-sm btn-primary">
                        Voir tout
                    </a>
                </div>
                <div class="card-body">
                    @forelse($recentMails as $mail)
                        <div class="d-flex align-items-center py-2 border-bottom">
                            <div class="mr-3">
                                @switch($mail->status->value)
                                    @case('draft')
                                        <span class="badge badge-secondary">{{ $mail->status->label() }}</span>
                                        @break
                                    @case('in_progress')
                                        <span class="badge badge-primary">{{ $mail->status->label() }}</span>
                                        @break
                                    @case('overdue')
                                        <span class="badge badge-danger">{{ $mail->status->label() }}</span>
                                        @break
                                    @case('completed')
                                        <span class="badge badge-success">{{ $mail->status->label() }}</span>
                                        @break
                                    @default
                                        <span class="badge badge-info">{{ $mail->status->label() }}</span>
                                @endswitch
                            </div>
                            <div class="flex-grow-1">
                                <div class="font-weight-bold">{{ $mail->code }}</div>
                                <div class="text-muted small">{{ Str::limit($mail->name, 50) }}</div>
                                @if($mail->deadline)
                                    <div class="text-muted small">
                                        <i class="bi bi-clock"></i>
                                        Échéance: {{ $mail->deadline->format('d/m/Y H:i') }}
                                        @if($mail->deadline->isPast())
                                            <span class="text-danger">(En retard)</span>
                                        @elseif($mail->deadline->diffInHours() < 24)
                                            <span class="text-warning">(Urgent)</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <div class="ml-auto">
                                <a href="{{ route('mail-incoming.show', $mail->id) }}" class="btn btn-sm btn-outline-primary">
                                    Voir
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fa-3x mb-3"></i>
                            <p>Aucun courrier assigné pour le moment</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Notifications récentes -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Notifications récentes</h6>
                    <a href="{{ route('notifications.organisation') }}" class="btn btn-sm btn-primary">
                        Voir tout
                    </a>
                </div>
                <div class="card-body">
                    @forelse($recentNotifications as $notification)
                        <div class="d-flex align-items-start py-2 border-bottom">
                            <div class="mr-2">
                                @switch($notification->type->icon())
                                    @case('exclamation-triangle')
                                        <i class="bi bi-exclamation-triangle text-danger"></i>
                                        @break
                                    @case('clock')
                                        <i class="bi bi-clock text-warning"></i>
                                        @break
                                    @case('check')
                                        <i class="bi bi-check-circle text-success"></i>
                                        @break
                                    @default
                                        <i class="bi bi-bell text-info"></i>
                                @endswitch
                            </div>
                            <div class="flex-grow-1">
                                <div class="font-weight-bold small">{{ $notification->title }}</div>
                                <div class="text-muted small">{{ Str::limit($notification->message, 80) }}</div>
                                <div class="text-muted small">{{ $notification->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-bell-slash fa-2x mb-2"></i>
                            <p class="small">Aucune notification</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actions rapides</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('mails.workflow.overdue') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-exclamation-triangle text-danger mr-2"></i>Courriers en retard</span>
                            @if($stats['overdue'] > 0)
                                <span class="badge badge-danger">{{ $stats['overdue'] }}</span>
                            @endif
                        </a>
                        <a href="{{ route('mails.workflow.approaching-deadline') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-alarm text-warning mr-2"></i>Échéances proches</span>
                            @if($stats['approaching_deadline'] > 0)
                                <span class="badge badge-warning">{{ $stats['approaching_deadline'] }}</span>
                            @endif
                        </a>
                        <a href="{{ route('mails.workflow.assigned-to-me') }}" class="list-group-item list-group-item-action">
                            <i class="bi bi-person-check text-primary mr-2"></i>Mes courriers
                        </a>
                        <a href="{{ route('mails.workflow.audit-trail') }}" class="list-group-item list-group-item-action">
                            <i class="bi bi-journal-text text-info mr-2"></i>Historique
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-danger {
    border-left: 0.25rem solid #e74a3b !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}
.fa-2x {
    font-size: 2em;
}
.fa-3x {
    font-size: 3em;
}
</style>
@endsection
