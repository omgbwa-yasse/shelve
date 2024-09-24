@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Communication</h1>
        <a href="{{ route('transactions.create') }}" class="btn btn-primary mb-3">
            <i class="bi bi-plus-circle"></i> Remplir une fiche
        </a>

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

        <div class="row">
            @foreach ($communications as $communication)

            <div class="col-12 ml-3 mb-1">
                <h5 class="card-title">
                    <input class="form-check-input" type="checkbox" value="{{$communication->id}}" id="communication_id" />
                    <label class="form-check-label" for="">
                        <span style="font-size: 1.4em; font-weight: bold;">
                            <a href="{{ route('transactions.show', $communication->id) }}">
                                <strong> {{ $communication->code ?? 'N/A' }} : {{ $communication->name ?? 'N/A' }}</strong>
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
            @endforeach
        </div>
    </div>

    <footer class="bg-light py-3">
        <div class="container">
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <li class="page-item {{ $communications->currentPage() == 1 ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $communications->previousPageUrl() }}" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    @for ($i = 1; $i <= $communications->lastPage(); $i++)
                        <li class="page-item {{ $communications->currentPage() == $i ? 'active' : '' }}">
                            <a class="page-link" href="{{ $communications->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor
                    <li class="page-item {{ $communications->currentPage() == $communications->lastPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $communications->nextPageUrl() }}" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </footer>
@endsection
