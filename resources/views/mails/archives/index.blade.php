@extends('layouts.app')

@section('content')

<table class="table">
    <thead>
        <tr>
            <th>Conteneur</th>
            <th>Mail</th>
            <th>Type de document</th>
            <th>Archivé par</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($mailArchives as $mailArchive)
            <tr>
                <td>{{ $mailArchive->container->name }}</td>
                <td>{{ $mailArchive->mail->name }}</td>
                <td>{{ $mailArchive->document_type }}</td>
                <td>{{ $mailArchive->user->name }}</td>
                <td>
                    <a href="{{ route('mail-archive.show', $mailArchive->id) }}" class="btn btn-primary">Afficher</a>
                    <a href="{{ route('mail-archive.edit', $mailArchive->id) }}" class="btn btn-secondary">Modifier</a>
                    <form action="{{ route('mail-archive.destroy', $mailArchive->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette archive ?')">Supprimer</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@endsection
