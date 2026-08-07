@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3>{{ __('return_available') }}</h3>
                    <div class="text-muted">
                        {{ $reservations->total() }} {{ __('reservations') }}
                    </div>
                </div>
                <div class="card-body">
                    @if($reservations->count() > 0)
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            {{ __('The following reservations have reached their return date and are awaiting return.') }}
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>{{ __('Code') }}</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('User') }}</th>
                                        <th>{{ __('Return Date') }}</th>
                                        <th>{{ __('Days Overdue') }}</th>
                                        <th>{{ __('Communication') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($reservations as $reservation)
                                        @php
                                            $daysOverdue = $reservation->return_date ? now()->diffInDays($reservation->return_date, false) : 0;
                                        @endphp
                                        <tr class="{{ $daysOverdue > 0 ? 'table-danger' : 'table-warning' }}">
                                            <td>
                                                <strong>{{ $reservation->code }}</strong>
                                            </td>
                                            <td>{{ $reservation->name }}</td>
                                            <td>
                                                {{ $reservation->user->name ?? 'N/A' }}
                                                @if($reservation->userOrganisation)
                                                    <br><small class="text-muted">{{ $reservation->userOrganisation->name }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $reservation->return_date->format('d/m/Y') }}
                                            </td>
                                            <td>
                                                @if($daysOverdue > 0)
                                                    <span class="badge badge-danger">
                                                        {{ $daysOverdue }} {{ __('day(s) overdue') }}
                                                    </span>
                                                @elseif($daysOverdue === 0)
                                                    <span class="badge badge-warning">
                                                        {{ __('Due today') }}
                                                    </span>
                                                @else
                                                    <span class="badge badge-info">
                                                        {{ abs($daysOverdue) }} {{ __('day(s) remaining') }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($reservation->communication)
                                                    <a href="{{ route('communications.transactions.show', $reservation->communication->id) }}"
                                                       class="btn btn-sm btn-primary">
                                                        <i class="bi bi-eye"></i> {{ __('View') }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">{{ __('No communication') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('communications.reservations.show', $reservation->id) }}"
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    @if($reservation->communication)
                                                        <button type="button" class="btn btn-sm btn-success"
                                                                onclick="markAsReturned({{ $reservation->id }})">
                                                            <i class="bi bi-check-circle"></i> {{ __('Mark as Returned') }}
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center">
                            {{ $reservations->links() }}
                        </div>
                    @else
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i>
                            {{ __('No reservations awaiting return.') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function markAsReturned(reservationId) {
    if (confirm('{{ __("Are you sure you want to mark this reservation as returned?") }}')) {
        fetch(`/communications/reservations/${reservationId}/mark-returned`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('{{ __("Error marking reservation as returned") }}');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __("Error marking reservation as returned") }}');
        });
    }
}
</script>
@endsection
