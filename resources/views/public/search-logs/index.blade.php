@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h2>Journaux de recherche</h2>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Terme de recherche</th>
                                    <th>Utilisateur</th>
                                    <th>Résultats</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($searchLogs as $log)
                                    <tr>
                                        <td>
                                            <strong>{{ $log->query }}</strong>
                                            @if($log->filters)
                                                <br><small class="text-muted">Filtres : {{ json_encode($log->filters) }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $log->user->name ?? 'Anonyme' }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ $log->results_count ?? 0 }} résultats</span>
                                        </td>
                                        <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('public.search-logs.show', $log) }}" class="btn btn-info btn-sm">Détails</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{ $searchLogs->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
