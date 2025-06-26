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
                                    <th>Référence</th>
                                    <th>Publié par</th>
                                    <th>Date de publication</th>
                                    <th>Date d'expiration</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($records as $record)
                                    <tr>
                                        <td>{{ $record->title }}</td>
                                        <td>{{ $record->code }}</td>
                                        <td>{{ $record->publisher->name ?? 'Inconnu' }}</td>
                                        <td>{{ $record->published_at ? $record->published_at->format('d/m/Y H:i') : '-' }}</td>
                                        <td>{{ $record->expires_at ? $record->expires_at->format('d/m/Y H:i') : 'Pas d\'expiration' }}</td>
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
