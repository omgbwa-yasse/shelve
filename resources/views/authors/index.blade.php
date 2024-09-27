@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="card border-0 shadow-sm rounded-lg">
            <div class="card-header bg-primary text-white rounded-top">
                <h2 class="mb-0">Authors</h2>
            </div>
            <div class="card-body">
                <a href="{{ route('mail-author.create') }}" class="btn btn-primary mb-3">Ajouter un auteur</a>

                @if ($message = Session::get('success'))
                    <div class="alert alert-success">
                        {{ $message }}
                    </div>
                @endif

                <table class="table table-bordered table-hover">
                    <thead class="bg-light">
                    <tr>
                        <th>Type d'entité</th>
                        <th>Nom</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($authors as $author)
                        <tr>
                            <td>{{ $author->authorType->name }}</td>
                            <td>{{ $author->name }}</td>
                            <td>
                                <a class="btn btn-info btn-sm" href="{{ route('mail-author.show', $author) }}"><i class="bi bi-eye"></i> Afficher</a>
                                <a class="btn btn-primary btn-sm" href="{{ route('mail-author.edit', $author) }}"><i class="bi bi-pencil"></i> Modifier</a>
                                <form action="{{ route('mail-author.destroy', $author) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const authors = @json($authors);
    </script>
@endsection
