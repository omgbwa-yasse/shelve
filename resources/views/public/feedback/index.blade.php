@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>Retours d'expérience</h2>
                    <a href="{{ route('public.feedback.create') }}" class="btn btn-primary">Nouveau retour</a>
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
                                    <th>Contenu</th>
                                    <th>Utilisateur</th>
                                    <th>Date</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($feedback as $item)
                                    <tr>
                                        <td>{{ $item->title }}</td>
                                        <td>{{ $item->type }}</td>
                                        <td>{{ Str::limit($item->content, 100) }}</td>
                                        <td>{{ $item->user->name ?? 'Anonyme' }}</td>
                                        <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @switch($item->status)
                                                @case('new')
                                                    <span class="badge bg-primary">Nouveau</span>
                                                    @break
                                                @case('in_progress')
                                                    <span class="badge bg-warning">En cours</span>
                                                    @break
                                                @case('resolved')
                                                    <span class="badge bg-success">Résolu</span>
                                                    @break
                                                @case('closed')
                                                    <span class="badge bg-secondary">Fermé</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-light">{{ $item->status }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            <a href="{{ route('public.feedback.show', $item) }}" class="btn btn-info btn-sm">Voir</a>
                                            <a href="{{ route('public.feedback.edit', $item) }}" class="btn btn-warning btn-sm">Modifier</a>
                                            <form action="{{ route('public.feedback.destroy', $item) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce retour ?')">Supprimer</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
