@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="bi bi-clock-history"></i> Historique des versions — {{ $record->name }}</h1>
            <div>
                <a href="{{ route('records.show', $record) }}" class="btn btn-outline-secondary">Fiche</a>
                @can('records_update')
                    <form method="POST" action="{{ route('records.create-version', $record) }}" class="d-inline">
                        @csrf
                        <button class="btn btn-outline-warning"><i class="bi bi-files"></i> Nouvelle version</button>
                    </form>
                @endcan
            </div>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Version</th>
                            <th>Nom</th>
                            <th>Statut</th>
                            <th>Support</th>
                            <th>Créée le</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($record->getAllVersions() as $version)
                            <tr>
                                <td><span class="badge bg-warning text-dark">v{{ $version->version_number }}</span></td>
                                <td><a href="{{ route('records.show', $version) }}">{{ $version->name }}</a></td>
                                <td>
                                    @if($version->is_current_version)
                                        <span class="badge bg-success">Courante</span>
                                    @else
                                        <span class="badge bg-light text-dark">Ancienne</span>
                                    @endif
                                </td>
                                <td>
                                    @foreach($version->mediums as $medium)
                                        <span class="badge bg-light text-dark">{{ $medium->support?->name }}</span>
                                    @endforeach
                                </td>
                                <td>{{ $version->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="text-end">
                                    @if(!$version->is_current_version)
                                        <form method="POST" action="{{ route('records.create-version', $version) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-warning" title="Repartir de cette version"><i class="bi bi-arrow-counterclockwise"></i></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
