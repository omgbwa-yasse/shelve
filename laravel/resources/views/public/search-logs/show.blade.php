@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>Détails de la recherche</h2>
                    <a href="{{ route('public.search-logs.index') }}" class="btn btn-secondary">Retour à la liste</a>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Terme de recherche :</strong>
                            <div class="mt-1">
                                <span class="badge bg-primary fs-6">{{ $searchLog->query }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <strong>Utilisateur :</strong> {{ $searchLog->user->name ?? 'Anonyme' }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Nombre de résultats :</strong>
                            <span class="badge bg-info">{{ $searchLog->results_count ?? 0 }}</span>
                        </div>
                        <div class="col-md-6">
                            <strong>Date de recherche :</strong> {{ $searchLog->created_at->format('d/m/Y H:i:s') }}
                        </div>
                    </div>

                    @if($searchLog->filters)
                        <div class="mb-3">
                            <strong>Filtres appliqués :</strong>
                            <div class="mt-2 p-3 bg-light border rounded">
                                <pre>{{ json_encode($searchLog->filters, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        </div>
                    @endif

                    @if($searchLog->response_time)
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <strong>Temps de réponse :</strong> {{ $searchLog->response_time }} ms
                            </div>
                        </div>
                    @endif

                    @if($searchLog->user_agent)
                        <div class="mb-3">
                            <strong>User Agent :</strong>
                            <div class="mt-2 p-2 bg-light border rounded small">
                                {{ $searchLog->user_agent }}
                            </div>
                        </div>
                    @endif

                    @if($searchLog->ip_address)
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <strong>Adresse IP :</strong> {{ $searchLog->ip_address }}
                            </div>
                        </div>
                    @endif

                    <div class="mt-4 pt-3 border-top">
                        <small class="text-muted">
                            Journal créé le : {{ $searchLog->created_at->format('d/m/Y H:i:s') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
