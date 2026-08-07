@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3>{{ __('approved_reservations') }}</h3>
                    <div class="text-muted">
                        {{ $reservations->total() }} {{ __('reservations') }}
                    </div>
                </div>
                <div class="card-body">
                    @if($reservations->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>{{ __('Code') }}</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('User') }}</th>
                                        <th>{{ __('Operator') }}</th>
                                        <th>{{ __('Return Date') }}</th>
                                        <th>{{ __('Communication') }}</th>
                                        <th>{{ __('Created') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($reservations as $reservation)
                                        <tr>
                                            <td>
                                                <strong>{{ $reservation->code }}</strong>
                                            </td>
                                            <td>{{ $reservation->name }}</td>
                                            <td>
                                                <span class="badge badge-success">
                                                    {{ $reservation->status->label() }}
                                                </span>
                                            </td>
                                            <td>
                                                {{ $reservation->user->name ?? 'N/A' }}
                                                @if($reservation->userOrganisation)
                                                    <br><small class="text-muted">{{ $reservation->userOrganisation->name }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $reservation->operator->name ?? 'N/A' }}
                                                @if($reservation->operatorOrganisation)
                                                    <br><small class="text-muted">{{ $reservation->operatorOrganisation->name }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($reservation->return_date)
                                                    {{ $reservation->return_date->format('d/m/Y') }}
                                                    @if($reservation->return_date <= now())
                                                        <span class="badge badge-warning">{{ __('Due') }}</span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">{{ __('Not set') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($reservation->communication)
                                                    <a href="{{ route('communications.transactions.show', $reservation->communication->id) }}"
                                                       class="btn btn-sm btn-primary">
                                                        <i class="bi bi-eye"></i> {{ __('View Communication') }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">{{ __('No communication') }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $reservation->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('communications.reservations.show', $reservation->id) }}"
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    @can('update', $reservation)
                                                        <a href="{{ route('communications.reservations.edit', $reservation->id) }}"
                                                           class="btn btn-sm btn-outline-secondary">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                    @endcan
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
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            {{ __('No approved reservations found.') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
