@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>Archives publiques</h2>
                    <a href="{{ route('public.records.create') }}" class="btn btn-primary">Nouveau document</a>
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
                                    <th>Titre</th>
                                    <th>Type</th>
                                    <th>Référence</th>
                                    <th>Utilisateur</th>
                                    <th>Date</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($records as $record)
                                    <tr>
                                        <td>{{ $record->title }}</td>
                                        <td>{{ $record->record_type }}</td>
                                        <td>{{ $record->reference_number }}</td>
                                        <td>{{ $record->publisher->name ?? 'Inconnu' }}</td>
                                        <td>{{ $record->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @switch($record->status)
                                                @case('draft')
                                                    <span class="badge bg-secondary">Brouillon</span>
                                                    @break
                                                @case('published')
                                                    <span class="badge bg-success">Publié</span>
                                                    @break
                                                @case('archived')
                                                    <span class="badge bg-warning">Archivé</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-light">{{ $record->status }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            <a href="{{ route('public.records.show', $record) }}" class="btn btn-info btn-sm">Voir</a>
                                            <a href="{{ route('public.records.edit', $record) }}" class="btn btn-warning btn-sm">Modifier</a>
                                            <form action="{{ route('public.records.destroy', $record) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce document ?')">Supprimer</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{ $records->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
