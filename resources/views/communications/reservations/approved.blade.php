@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                <i class="bi bi-check-circle-fill text-success"></i>
                {{ __('approved_reservations') }}
            </h1>
            <div>
                <a href="{{ route('communications.reservations.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> {{ __('back_to_reservations') }}
                </a>
            </div>
        </div>

        @if($reservations->count() > 0)
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                {{ $reservations->total() }} {{ __('approved_reservations_found') }}
            </div>

            <div class="row">
                @foreach ($reservations as $reservation)
                    <div class="col-12 mb-4">
                        <div class="card shadow-sm border-success">
                            <div class="card-header bg-success text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="bi bi-calendar-check"></i>
                                        {{ $reservation->code }} : {{ $reservation->name }}
                                    </h5>
                                    <span class="badge bg-light text-dark">
                                        {{ $reservation->status->label() }}
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
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
                                            <strong>{{ __('operator') }} :</strong>
                                            {{ $reservation->operator->name ?? 'N/A' }}
                                            @if($reservation->operatorOrganisation)
                                                <small class="text-muted">({{ $reservation->operatorOrganisation->name }})</small>
                                            @endif
                                        </div>

                                        <div class="mb-2">
                                            <strong>{{ __('reservation_date') }} :</strong>
                                            <span class="text-muted">{{ $reservation->created_at->format('d/m/Y H:i') }}</span>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <!-- Communication associée -->
                                        @if($reservation->communication)
                                            <div class="alert alert-success">
                                                <h6><i class="bi bi-chat-dots"></i> {{ __('generated_communication') }}</h6>
                                                <p class="mb-2">
                                                    <strong>{{ __('code') }} :</strong> {{ $reservation->communication->code }}<br>
                                                    <strong>{{ __('name') }} :</strong> {{ $reservation->communication->name }}<br>
                                                    <strong>{{ __('status') }} :</strong>
                                                    @if($reservation->communication && $reservation->communication->status)
                                                        <span class="badge bg-{{ $reservation->communication->status->color() }}">
                                                            {{ $reservation->communication->status->label() }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </p>
                                                <a href="{{ route('communications.transactions.show', $reservation->communication->id) }}"
                                                   class="btn btn-primary btn-sm">
                                                    <i class="bi bi-eye"></i> {{ __('view_communication') }}
                                                </a>
                                                <a href="{{ route('communications.phantom.generate', $reservation->communication->id) }}"
                                                   class="btn btn-info btn-sm ms-2" title="{{ __('Download PDF Phantom') }}">
                                                    <i class="bi bi-file-earmark-pdf"></i> {{ __('Phantom') }}
                                                </a>
                                            </div>
                                        @else
                                            <div class="alert alert-warning">
                                                <i class="bi bi-exclamation-triangle"></i>
                                                {{ __('no_communication_generated') }}
                                            </div>
                                        @endif

                                        <!-- Documents de la réservation -->
                                        @if($reservation->records->count() > 0)
                                            <div class="mt-3">
                                                <h6><i class="bi bi-files"></i> {{ __('reserved_documents') }} ({{ $reservation->records->count() }})</h6>
                                                <div class="list-group list-group-flush">
                                                    @foreach($reservation->records->take(3) as $record)
                                                        <div class="list-group-item px-0 py-1">
                                                            <small>{{ $record->name }}</small>
                                                        </div>
                                                    @endforeach
                                                    @if($reservation->records->count() > 3)
                                                        <div class="list-group-item px-0 py-1">
                                                            <small class="text-muted">
                                                                ... et {{ $reservation->records->count() - 3 }} autres documents
                                                            </small>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        {{ __('approved_on') }} {{ $reservation->updated_at->format('d/m/Y H:i') }}
                                    </small>
                                    <div>
                                        <a href="{{ route('communications.reservations.show', $reservation->id) }}"
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-eye"></i> {{ __('view_reservation') }}
                                        </a>
                                        @if($reservation->communication)
                                            <a href="{{ route('communications.transactions.show', $reservation->communication->id) }}"
                                               class="btn btn-primary btn-sm">
                                                <i class="bi bi-arrow-right"></i> {{ __('go_to_communication') }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $reservations->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-calendar-x" style="font-size: 4rem; color: #dee2e6;"></i>
                <h4 class="mt-3 text-muted">{{ __('no_approved_reservations') }}</h4>
                <p class="text-muted">{{ __('no_approved_reservations_message') }}</p>
                <a href="{{ route('communications.reservations.index') }}" class="btn btn-primary">
                    <i class="bi bi-arrow-left"></i> {{ __('back_to_reservations') }}
                </a>
            </div>
        @endif
    </div>
@endsection

@push('styles')
<style>
    .border-success {
        border-color: #28a745 !important;
    }

    .card-header.bg-success {
        background-color: #28a745 !important;
    }

    .list-group-flush .list-group-item {
        border-left: 0;
        border-right: 0;
    }
</style>
@endpush
