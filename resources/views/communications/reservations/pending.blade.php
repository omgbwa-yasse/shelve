@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                <i class="bi bi-clock-history text-warning"></i>
                {{ __('pending_reservations') }}
            </h1>
            <div>
                <a href="{{ route('communications.reservations.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> {{ __('back_to_reservations') }}
                </a>
                <a href="{{ route('communications.reservations.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> {{ __('add_reservation') }}
                </a>
            </div>
        </div>

        @php
            $pendingReservations = \App\Models\Reservation::with(['operator', 'user', 'userOrganisation', 'operatorOrganisation', 'communication', 'records'])
                ->where('status', \App\Enums\ReservationStatus::PENDING)
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        @endphp

        @if($pendingReservations->count() > 0)
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i>
                {{ $pendingReservations->total() }} {{ __('pending_reservations_awaiting_approval') }}
            </div>

            <div class="row">
                @foreach ($pendingReservations as $reservation)
                    <div class="col-12 mb-4">
                        <div class="card shadow-sm border-warning">
                            <div class="card-header bg-warning text-dark">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="bi bi-clock-history"></i>
                                        <a href="{{ route('communications.reservations.show', $reservation->id) }}" class="text-dark text-decoration-none">
                                            {{ $reservation->code }} : {{ $reservation->name }}
                                        </a>
                                    </h5>
                                    <span class="badge bg-light text-dark">
                                        {{ $reservation->status->label() }}
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <p class="card-text">
                                            <strong>{{ __('content') }} :</strong><br>
                                            {{ $reservation->content ?? __('no_content') }}
                                        </p>

                                        <div class="mb-2">
                                            <strong>{{ __('requester') }} :</strong>
                                            {{ $reservation->user->name ?? 'N/A' }}
                                            @if($reservation->userOrganisation)
                                                <small class="text-muted">({{ $reservation->userOrganisation->name }})</small>
                                            @endif
                                        </div>

                                        <div class="mb-2">
                                            <strong>{{ __('requested_on') }} :</strong>
                                            <span class="text-muted">{{ $reservation->created_at->format('d/m/Y H:i') }}</span>
                                        </div>

                                        @if($reservation->records->count() > 0)
                                            <div class="mt-3">
                                                <h6><i class="bi bi-files"></i> {{ __('reserved_documents') }} ({{ $reservation->records->count() }})</h6>
                                                <div class="list-group list-group-flush">
                                                    @foreach($reservation->records->take(2) as $record)
                                                        <div class="list-group-item px-0 py-1">
                                                            <small>{{ $record->name }}</small>
                                                        </div>
                                                    @endforeach
                                                    @if($reservation->records->count() > 2)
                                                        <div class="list-group-item px-0 py-1">
                                                            <small class="text-muted">
                                                                ... et {{ $reservation->records->count() - 2 }} autres documents
                                                            </small>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="col-md-4">
                                        <div class="d-grid gap-2">
                                            <a href="{{ route('communications.reservations.show', $reservation->id) }}"
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="bi bi-eye"></i> {{ __('view_details') }}
                                            </a>

                                            <form action="{{ route('communications.reservations.actions.approved') }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $reservation->id }}">
                                                <button type="submit" class="btn btn-success btn-sm w-100"
                                                        onclick="return confirm('{{ __('confirm_approve_reservation') }}')">
                                                    <i class="bi bi-check-circle"></i> {{ __('approve') }}
                                                </button>
                                            </form>

                                            <a href="{{ route('communications.reservations.edit', $reservation->id) }}"
                                               class="btn btn-outline-warning btn-sm">
                                                <i class="bi bi-pencil"></i> {{ __('edit') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-light">
                                <small class="text-muted">
                                    <i class="bi bi-clock"></i>
                                    {{ __('waiting_since') }} {{ $reservation->created_at->diffForHumans() }}
                                </small>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $pendingReservations->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-check-circle" style="font-size: 4rem; color: #28a745;"></i>
                <h4 class="mt-3 text-success">{{ __('no_pending_reservations') }}</h4>
                <p class="text-muted">{{ __('all_reservations_processed') }}</p>
                <a href="{{ route('communications.reservations.index') }}" class="btn btn-primary">
                    <i class="bi bi-list-ul"></i> {{ __('view_all_reservations') }}
                </a>
            </div>
        @endif
    </div>
@endsection

@push('styles')
<style>
    .border-warning {
        border-color: #ffc107 !important;
    }

    .card-header.bg-warning {
        background-color: #ffc107 !important;
    }

    .list-group-flush .list-group-item {
        border-left: 0;
        border-right: 0;
    }
</style>
@endpush
