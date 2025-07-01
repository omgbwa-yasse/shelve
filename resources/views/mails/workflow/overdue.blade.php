@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ __('Courriers en retard') }}</h4>
                    <div class="btn-group" role="group">
                        <a href="{{ route('mails.workflow.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a href="{{ route('mails.workflow.approaching-deadline') }}" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-clock"></i> Échéances proches
                        </a>
                        <a href="{{ route('mails.workflow.assigned-to-me') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-user"></i> Mes tâches
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if($overdueMails->count() > 0)
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>{{ $overdueMails->count() }}</strong> courrier(s) en retard nécessitent une attention immédiate.
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>{{ __('Objet') }}</th>
                                        <th>{{ __('Expéditeur') }}</th>
                                        <th>{{ __('Assigné à') }}</th>
                                        <th>{{ __('Échéance') }}</th>
                                        <th>{{ __('Retard') }}</th>
                                        <th>{{ __('Statut') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($overdueMails as $mail)
                                        <tr class="table-danger">
                                            <td>
                                                <a href="{{ route('mails.show', $mail->id) }}" class="text-decoration-none">
                                                    {{ Str::limit($mail->object, 50) }}
                                                </a>
                                            </td>
                                            <td>{{ $mail->sender_name ?? 'N/A' }}</td>
                                            <td>
                                                @if($mail->assignedTo)
                                                    <span class="badge badge-info">
                                                        {{ $mail->assignedTo->name }}
                                                    </span>
                                                @else
                                                    <span class="badge badge-secondary">Non assigné</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="text-danger">
                                                    <i class="fas fa-calendar-times"></i>
                                                    {{ $mail->deadline ? $mail->deadline->format('d/m/Y H:i') : 'N/A' }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($mail->deadline)
                                                    <span class="badge badge-danger">
                                                        {{ $mail->deadline->diffForHumans() }}
                                                    </span>
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
                                                    <button type="button" class="btn btn-outline-success"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#assignModal{{ $mail->id }}"
                                                            title="Assigner">
                                                        <i class="fas fa-user-plus"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center">
                            {{ $overdueMails->links() }}
                        </div>
                    @else
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            Aucun courrier en retard. Excellent travail !
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals d'assignation -->
@foreach($overdueMails as $mail)
    @include('mails.workflow.assign-modal', ['mail' => $mail])
@endforeach
@endsection

@section('scripts')
<script>
// Auto-refresh la page toutes les 5 minutes pour les courriers en retard
setTimeout(function() {
    location.reload();
}, 300000); // 5 minutes
</script>
@endsection
