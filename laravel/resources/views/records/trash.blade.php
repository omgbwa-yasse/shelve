@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Corbeille — notices supprimées</h1>
        <a href="{{ route('records.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Retour aux notices</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Nom</th>
                        <th>Typologie</th>
                        <th>Organisation</th>
                        <th>Supprimé le</th>
                        <th style="width: 200px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                        <tr>
                            <td><code class="bg-light px-2 py-1 rounded">{{ $record->code }}</code></td>
                            <td>{{ $record->name }}</td>
                            <td>{{ $record->type?->name ?? '—' }}</td>
                            <td>{{ $record->organisation?->name ?? '—' }}</td>
                            <td>{{ $record->deleted_at?->format('d/m/Y H:i') }}</td>
                            <td>
                                <form method="POST" action="{{ route('records.restore', $record) }}" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-success" title="Restaurer">
                                        <i class="bi bi-arrow-counterclockwise"></i> Restaurer
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('records.force-delete', $record) }}" class="d-inline"
                                      onsubmit="return confirm('Supprimer définitivement cette notice ? Cette action est irréversible.');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Supprimer définitivement">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <p class="text-muted mb-0">La corbeille est vide</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($records->hasPages())
            <div class="card-footer bg-light">
                {{ $records->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
