@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Reservations</h1>
        <a href="{{ route('communications.reservations.create') }}" class="btn btn-primary mb-3">
            <i class="bi bi-plus-circle"></i> Nouvelle réservation
        </a>
        <div class="row">
            @foreach ($reservations as $reservation)
                    <div class="col-12 ml-3 mb-1">
                        <h5 class="card-title">
                            <input class="form-check-input" type="checkbox" value="{{$reservation->id}}" id="communication_id" />
                            <label class="form-check-label" for="">
                                <span style="font-size: 1.4em; font-weight: bold;">
                                    <a href="{{ route('communications.reservations.show', $reservation->id) }}">
                                        <strong> {{ $reservation->code ?? 'N/A' }} : {{ $reservation->name ?? 'N/A' }}</strong>
                                    </a>
                                </span>
                            </label>
                        </h5>
                    </div>
                    <div class="col-12 mb-4">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="card-text">
                                                <strong>Contenu :</strong> {{ $reservation->content ?? 'N/A' }}<br>
                                            </p>
                                        </div>
                                        <div class="card-text d-flex flex-wrap">
                                            <div class="mr-3">
                                                <strong>Demandeur :</strong>
                                                <span>                                    @if($reservation->user)
                                        <a href="{{ route('communications.reservations.search.index')}}?categ=user&id={{ $reservation->user->id }}">
                                            {{ $reservation->user->name }}
                                        </a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif

                                    @if($reservation->userOrganisation)
                                        (<a href="{{ route('communications.reservations.search.index')}}?categ=user-organisation&id={{ $reservation->userOrganisation->id }}">
                                                {{ $reservation->userOrganisation->name }}
                                        </a>)
                                    @else
                                        (<span class="text-muted">N/A</span>)
                                    @endif
                                                </span>
                                            </div>
                                        </div>

                                        <div class="card-text d-flex flex-wrap">
                                            <div class="mr-3">
                                                <strong>Opérateur :</strong>
                                                <span>                                    @if($reservation->operator)
                                        <a href="{{ route('communications.reservations.search.index')}}?categ=operator&id={{ $reservation->operator->id }}">
                                            {{ $reservation->operator->name }}
                                        </a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif

                                    @if($reservation->operatorOrganisation)
                                        (<a href="{{ route('communications.reservations.search.index')}}?categ=operator-organisation&id={{ $reservation->operatorOrganisation->id }}">
                                            {{ $reservation->operatorOrganisation->name }}
                                        </a>)
                                    @else
                                        (<span class="text-muted">N/A</span>)
                                    @endif
                                                </span>
                                            </div>
                                        </div>

                                        <div class="card-text d-flex flex-wrap">
                                            <div class="mr-3">
                                                <strong>Date de retour :</strong> {{ $reservation->return_date ?? 'N/A' }}
                                            </div>
                                            <div>
                                                <strong>Statut :</strong>
                                                @if($reservation->status)
                                                    <span class="badge bg-{{ $reservation->status->color() }}">{{ $reservation->status->label() }}</span>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </div>
                                        </div>

                                        @if($reservation->communication_id)
                                            <div class="card-text mt-2">
                                                <div class="alert alert-success py-2">
                                                    <small>
                                                        <i class="bi bi-check-circle-fill"></i>
                                                        <strong>Communication générée :</strong>
                                                        <a href="{{ route('communications.transactions.show', $reservation->communication_id) }}" class="alert-link">
                                                            Voir la communication #{{ $reservation->communication_id }}
                                                        </a>
                                                    </small>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                </div>
                            </div>
                        </div>

            @endforeach
        </div>
    </div>
@endsection
