@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ __('Échéances proches') }}</h4>
                    <div class="btn-group" role="group">
                        <a href="{{ route('mails.workflow.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a href="{{ route('mails.workflow.overdue') }}" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-exclamation-triangle"></i> En retard
                        </a>
                        <a href="{{ route('mails.workflow.assigned-to-me') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-user"></i> Mes tâches
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if($approachingDeadlineMails->count() > 0)
                        <div class="alert alert-warning">
                            <i class="fas fa-clock"></i>
                            <strong>{{ $approachingDeadlineMails->count() }}</strong> courrier(s) approchent de leur échéance.
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>{{ __('Objet') }}</th>
                                        <th>{{ __('Expéditeur') }}</th>
                                        <th>{{ __('Assigné à') }}</th>
                                        <th>{{ __('Échéance') }}</th>
                                        <th>{{ __('Temps restant') }}</th>
                                        <th>{{ __('Statut') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($approachingDeadlineMails as $mail)
                                        @php
                                            $hoursLeft = $mail->deadline ? now()->diffInHours($mail->deadline, false) : 0;
                                            $urgencyClass = $hoursLeft <= 24 ? 'table-warning' : '';
                                        @endphp
                                        <tr class="{{ $urgencyClass }}">
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
                                                <span class="{{ $hoursLeft <= 24 ? 'text-warning fw-bold' : '' }}">
                                                    <i class="fas fa-calendar-alt"></i>
                                                    {{ $mail->deadline ? $mail->deadline->format('d/m/Y H:i') : 'N/A' }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($mail->deadline)
                                                    @php
                                                        $badgeClass = $hoursLeft <= 24 ? 'badge-warning' : 'badge-info';
                                                    @endphp
                                                    <span class="badge {{ $badgeClass }}">
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
                            {{ $approachingDeadlineMails->links() }}
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Aucun courrier n'approche de son échéance dans les prochains jours.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals d'assignation -->
@foreach($approachingDeadlineMails as $mail)
    @include('mails.workflow.assign-modal', ['mail' => $mail])
@endforeach
@endsection

@section('scripts')
<script>
// Auto-refresh la page toutes les 10 minutes
setTimeout(function() {
    location.reload();
}, 600000); // 10 minutes
</script>
@endsection
