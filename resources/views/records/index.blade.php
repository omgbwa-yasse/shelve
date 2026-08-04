@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h1><i class="bi bi-archive"></i> {{ $title ?? 'Archives' }}</h1>
            </div>
            <div class="col-auto">
                @can('records_create')
                    <a href="{{ route('records.create') }}" class="btn btn-primary btn-lg">
                        <i class="bi bi-plus-circle"></i> Nouvelle notice
                    </a>
                @endcan
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Nom</th>
                        <th>Type</th>
                        <th>Niveau</th>
                        <th>Statut</th>
                        <th>Supports</th>
                        <th>Version</th>
                        <th>Mis à jour</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                        <tr class="clickable-row" data-href="{{ route('records.show', $record) }}">
                            <td><span class="badge bg-secondary">{{ $record->code }}</span></td>
                            <td><a href="{{ route('records.show', $record) }}">{{ $record->name }}</a></td>
                            <td>
                                <span class="badge {{ $record->isContainer() ? 'bg-success' : 'bg-info' }}">
                                    {{ $record->type?->name ?? '—' }}
                                </span>
                            </td>
                            <td>{{ $record->level?->name ?? '—' }}</td>
                            <td><span class="badge bg-light text-dark">{{ $record->status?->name ?? '—' }}</span></td>
                            <td>
                                @foreach($record->mediums as $medium)
                                    <span class="badge {{ $medium->support_id === 1 ? 'bg-primary' : 'bg-dark' }}">
                                        {{ $medium->support?->name ?? '?' }}
                                        @if($medium->attachment) <i class="bi bi-file-earmark"></i> @endif
                                        @if($medium->isCheckedOut()) <i class="bi bi-lock"></i> @endif
                                    </span>
                                @endforeach
                            </td>
                            <td>
                                @if($record->version_number > 1)
                                    <span class="badge bg-warning text-dark">v{{ $record->version_number }}</span>
                                @endif
                            </td>
                            <td>{{ $record->updated_at?->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">Aucune notice trouvée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-3">
            {{ $records->links() }}
        </div>
    </div>
@endsection
