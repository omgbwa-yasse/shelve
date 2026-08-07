@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>Modèles</h2>
                    <a href="{{ route('public.templates.create') }}" class="btn btn-primary">Nouveau modèle</a>
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
                                    <th>Nom</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Dernière modification</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($templates as $template)
                                    <tr>
                                        <td>{{ $template->name }}</td>
                                        <td>{{ $template->type }}</td>
                                        <td>{{ Str::limit($template->description, 100) }}</td>
                                        <td>{{ $template->updated_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('public.templates.show', $template) }}" class="btn btn-info btn-sm">Voir</a>
                                            <a href="{{ route('public.templates.edit', $template) }}" class="btn btn-warning btn-sm">Modifier</a>
                                            <form action="{{ route('public.templates.destroy', $template) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce modèle ?')">Supprimer</button>
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
