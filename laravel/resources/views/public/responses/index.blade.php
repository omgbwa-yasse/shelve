@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>Réponses aux demandes</h2>
                    <a href="{{ route('public.responses.create') }}" class="btn btn-primary">Nouvelle réponse</a>
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
                                    <th>Demande</th>
                                    <th>Réponse</th>
                                    <th>Utilisateur</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($responses as $response)
                                    <tr>
                                        <td>{{ $response->documentRequest->title ?? 'N/A' }}</td>
                                        <td>{{ Str::limit($response->content, 100) }}</td>
                                        <td>{{ $response->user->name ?? 'Inconnu' }}</td>
                                        <td>{{ $response->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('public.responses.show', $response) }}" class="btn btn-info btn-sm">Voir</a>
                                            <a href="{{ route('public.responses.edit', $response) }}" class="btn btn-warning btn-sm">Modifier</a>
                                            <form action="{{ route('public.responses.destroy', $response) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr ?')">Supprimer</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{ $responses->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
