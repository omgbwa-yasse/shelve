@extends('layouts.app')

@section('content')
    <div class="container">
        <h1><i class="bi bi-calendar-check"></i> {{ __('Reservations') }}</h1>
        <a href="{{ route('communications.reservations.create') }}" class="btn btn-primary mb-3">
            <i class="bi bi-plus-circle"></i> {{ __('New Reservation') }}
        </a>

        <!-- Filtres intégrés -->
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title mb-3">
                    <i class="bi bi-funnel"></i> {{ __('Filters') }}
                </h5>

                <!-- Filtres rapides par statut -->
                <div class="row mb-3">
                    <div class="col-12">
                        <fieldset class="btn-group flex-wrap gap-1" aria-label="Status filters">
                            <legend class="visually-hidden">{{ __('Filter by status') }}</legend>
                            <a href="{{ route('communications.reservations.index') }}" class="btn btn-outline-primary {{ !request('status') ? 'active' : '' }}">
                                <i class="bi bi-list-ul"></i> {{ __('All') }}
                            </a>
                            <a href="{{ route('communications.reservations.pending') }}" class="btn btn-outline-warning {{ request()->routeIs('communications.reservations.pending') ? 'active' : '' }}">
                                <i class="bi bi-clock-history"></i> {{ __('Pending') }}
                            </a>
                            <a href="{{ route('communications.reservations.approved.reservations') }}" class="btn btn-outline-success {{ request()->routeIs('communications.reservations.approved.reservations') ? 'active' : '' }}">
                                <i class="bi bi-check-circle-fill"></i> {{ __('Approved') }}
                            </a>
                            <a href="{{ route('communications.reservations.approved.list') }}" class="btn btn-outline-info {{ request()->routeIs('communications.reservations.approved.list') ? 'active' : '' }}">
                                <i class="bi bi-arrow-right-circle"></i> {{ __('With Communications') }}
                            </a>
                            <a href="{{ route('communications.reservations.return.available') }}" class="btn btn-outline-secondary {{ request()->routeIs('communications.reservations.return.available') ? 'active' : '' }}">
                                <i class="bi bi-calendar-event"></i> {{ __('Return Available') }}
                            </a>
                        </fieldset>
                    </div>
                </div>

                <!-- Recherche par date -->
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <a href="{{ route('communications.reservations.search.date-selection') }}" class="btn btn-outline-dark">
                            <i class="bi bi-calendar-range"></i> {{ __('Date Selection') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions globales -->
        <div class="d-flex justify-content-between align-items-center bg-light p-3 mb-3">
            <div class="d-flex align-items-center">
                <button type="button" class="btn btn-light btn-sm me-2" id="selectAllBtn">
                    <i class="bi bi-check-square me-1"></i>
                    {{ __('Select All') }}
                </button>
                <button type="button" class="btn btn-light btn-sm me-2" id="exportBtn">
                    <i class="bi bi-download me-1"></i>
                    {{ __('Export') }}
                </button>
            </div>
            <div class="d-flex align-items-center">
                <span class="text-muted">
                    @if(method_exists($reservations, 'total'))
                        {{ $reservations->total() }}
                    @else
                        {{ count($reservations) }}
                    @endif
                    {{ __('reservations') }}
                </span>
            </div>
        </div>

        <div class="row">
            @foreach ($reservations as $reservation)
                <div class="mb-3" style="transition: all 0.3s ease; transform: translateZ(0);">
                    <div class="card-header bg-light d-flex align-items-center py-2" style="border-bottom: 1px solid rgba(0,0,0,0.125);">
                        <div class="form-check me-3">
                            <input class="form-check-input" type="checkbox" value="{{ $reservation->id }}" id="reservation-{{ $reservation->id }}" name="selected_reservation[]" />
                        </div>
                        <button class="btn btn-link btn-sm text-secondary text-decoration-none p-0 me-3" type="button" data-bs-toggle="collapse" data-bs-target="#details-{{ $reservation->id }}" aria-expanded="false" aria-controls="details-{{ $reservation->id }}">
                            <i class="bi bi-chevron-down fs-5"></i>
                        </button>
                        <h4 class="card-title flex-grow-1 m-0 text-primary" for="reservation-{{ $reservation->id }}">
                            <a href="{{ route('communications.reservations.show', $reservation->id ?? '') }}" class="text-decoration-none text-dark">
                                <span class="fs-5 fw-semibold">{{ $reservation->code ?? 'N/A' }}</span>
                                <span class="fs-5"> : {{ $reservation->name ?? 'N/A' }}</span>
                                @if($reservation->status)
                                    <span class="badge ms-2 bg-{{ $reservation->status->color() ?? 'secondary' }}">
                                        {{ $reservation->status->label() }}
                                    </span>
                                @endif
                            </a>
                        </h4>
                    </div>
                    <div class="collapse" id="details-{{ $reservation->id }}">
                        <div class="card-body bg-white">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <p class="mb-2"><i class="bi bi-card-text me-2 text-primary"></i><strong>{{ __('Content') }} :</strong> {{ $reservation->content ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2">
                                        <i class="bi bi-person-fill me-2 text-primary"></i><strong>{{ __('Requester') }} :</strong>
                                        @if($reservation->user)
                                            <a href="{{ route('communications.reservations.index') }}?user={{ $reservation->user->id }}">{{ $reservation->user->name }}</a>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                        @if($reservation->userOrganisation)
                                            (<a href="{{ route('communications.reservations.index') }}?user_organisation={{ $reservation->userOrganisation->id }}">{{ $reservation->userOrganisation->name }}</a>)
                                        @else
                                            (<span class="text-muted">N/A</span>)
                                        @endif
                                    </p>
                                    <p class="mb-2">
                                        <i class="bi bi-person-badge-fill me-2 text-primary"></i><strong>{{ __('Operator') }} :</strong>
                                        @if($reservation->operator)
                                            <a href="{{ route('communications.reservations.index') }}?operator={{ $reservation->operator->id }}">{{ $reservation->operator->name }}</a>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                        @if($reservation->operatorOrganisation)
                                            (<a href="{{ route('communications.reservations.index') }}?operator_organisation={{ $reservation->operatorOrganisation->id }}">{{ $reservation->operatorOrganisation->name }}</a>)
                                        @else
                                            (<span class="text-muted">N/A</span>)
                                        @endif
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2">
                                        <i class="bi bi-calendar-event me-2 text-primary"></i><strong>{{ __('Return date') }} :</strong> {{ $reservation->return_date ?? 'N/A' }}
                                    </p>
                                    <p class="mb-2">
                                        <i class="bi bi-info-circle-fill me-2 text-primary"></i><strong>{{ __('Status') }} :</strong>
                                        @if($reservation->status)
                                            <span class="badge bg-{{ $reservation->status->color() ?? 'secondary' }}">{{ $reservation->status->label() }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </p>
                                </div>
                                @if($reservation->communication_id)
                                <div class="col-md-12 mb-3">
                                    <div class="alert alert-success py-2">
                                        <small>
                                            <i class="bi bi-check-circle-fill"></i>
                                            <strong>{{ __('Communication generated') }} :</strong>
                                            <a href="{{ route('communications.transactions.show', $reservation->communication_id) }}" class="alert-link">
                                                {{ __('View communication') }} #{{ $reservation->communication_id }}
                                            </a>
                                        </small>
                                    </div>
                                </div>
                                @endif
                                <div class="col-md-12">
                                    <div class="btn-group">
                                        <a href="{{ route('communications.reservations.show', $reservation->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> {{ __('View') }}
                                        </a>
                                        @can('reservations_manage')
                                        <a href="{{ route('communications.reservations.edit', $reservation->id) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-pencil"></i> {{ __('Edit') }}
                                        </a>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            @endforeach
        </div>
    </div>

    <!-- Pagination -->
    @if(method_exists($reservations, 'links'))
    <footer class="bg-light py-3">
        <div class="container">
            <nav aria-label="Page navigation">
                {{ $reservations->links() }}
            </nav>
        </div>
    </footer>
    @endif
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gestion de la sélection de toutes les réservations
            const selectAllBtn = document.getElementById('selectAllBtn');
            const checkboxes = document.querySelectorAll('input[name="selected_reservation[]"]');

            if (selectAllBtn && checkboxes.length > 0) {
                selectAllBtn.addEventListener('click', function() {
                    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                    checkboxes.forEach(cb => cb.checked = !allChecked);

                    // Mettre à jour le texte du bouton
                    const icon = selectAllBtn.querySelector('i');
                    if (!allChecked) {
                        icon.className = 'bi bi-check-square-fill me-1';
                        selectAllBtn.querySelector('span') ? selectAllBtn.querySelector('span').textContent = '{{ __("Unselect All") }}' : null;
                    } else {
                        icon.className = 'bi bi-check-square me-1';
                        selectAllBtn.querySelector('span') ? selectAllBtn.querySelector('span').textContent = '{{ __("Select All") }}' : null;
                    }
                });
            }
        });
    </script>
@endpush
