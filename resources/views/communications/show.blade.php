@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Fiche de communication </h1>
        <div class="d-flex justify-content-between align-items-center bg-light p-3 mb-3">
            <div class="d-flex align-items-center">
                <a href="#" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-cart me-1"></i>
                    Chariot
                </a>
                <a href="#" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-download me-1"></i>
                    Exporter
                </a>
                <a href="#" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-printer me-1"></i>
                    Imprimer
                </a>
            </div>
            <div class="d-flex align-items-center">
                <a href="#" class="btn btn-light btn-sm">
                    <i class="bi bi-check-square me-1"></i>
                    Tout chocher
                </a>
            </div>
        </div>


                <div class="col-13 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="card-title">
                                       <strong>{{ $communication->code ?? 'N/A' }} : {{ $communication->name ?? 'N/A' }}</strong>
                                    </h5>
                                    <p class="card-text">
                                        <strong>Contenu :</strong> {{ $communication->content ?? 'N/A' }}<br>
                                    </p>
                                </div>
                                <div class="card-text d-flex flex-wrap">
                                    <div class="mr-3">
                                        <strong>Demandeur :</strong>
                                        <span>{{ $communication->user->name ?? 'N/A' }} ({{ $communication->userOrganisation->name ?? 'N/A' }})</span>
                                    </div>
                                </div>

                                <div class="card-text d-flex flex-wrap">
                                    <div class="mr-3">
                                        <strong>Opérateur :</strong>
                                        <span>{{ $communication->operator->name ?? 'N/A' }} ({{ $communication->operatorOrganisation->name ?? 'N/A' }})</span>
                                    </div>
                                </div>

                                <div class="card-text d-flex flex-wrap">
                                    <div class="mr-3">
                                        <strong>Date de retour :</strong> {{ $communication->return_date ?? 'N/A' }}
                                    </div>
                                    <div class="mr-3">
                                        <strong>Date de retour effectif :</strong> {{ $communication->return_effective ?? 'N/A' }}
                                    </div>
                                    <div>
                                        <strong>Statut :</strong> {{ $communication->status->name ?? 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <a href="{{ route('transactions.show', $communication->id) }}" class="btn btn-secondary">Back</a>
                <a href="{{ route('transactions.edit', $communication->id) }}" class="btn btn-warning">Edit</a>
                <form action="{{ route('transactions.destroy', $communication->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this communication?')">Delete</button>
                </form>
                @if($communication->return_effective ==  NULL)
                    <a href="{{ route('return-effective') }}?id={{$communication->id}}" class="btn btn-success">Retour effectif</a>
                @else
                    <a href="{{ route('return-cancel') }}?id={{$communication->id}}" class="btn btn-danger">Annuler le retour effectif</a>
                @endif
                <hr>
                <a href="{{ route('transactions.records.create', $communication->id) }}" class="btn btn-warning">Ajouter des documents</a>
            </div>
        </div>
        @foreach($communication->records as $record)
            <ul class="list-group list-group-numbered">
                <li class="list-group-item">
                    {{ $record->record->name }} / {{ $record->is_original }} : {{ $record->return_date }} ; date effective : {{ $record->return_effective }}
                    <a href="{{ route('transactions.records.show', [$communication, $record]) }}" class="btn btn-secondary">Voir</a>
                    @if($record->return_effective == NULL)
                        <a href="{{ route('record-return-effective') }}?id={{  $record->id }}" class="btn btn-success"> Returner </a>
                    @else
                    <a href="{{ route('record-return-cancel') }}?id={{  $record->id }}" class="btn btn-danger"> Annuler le retour </a>
                    @endif

                </li>
            </ul>
        @endforeach
    </div>
@endsection
