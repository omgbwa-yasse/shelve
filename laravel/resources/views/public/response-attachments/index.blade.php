@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>Pièces jointes des réponses</h2>
                    <a href="{{ route('public.response-attachments.create') }}" class="btn btn-primary">Ajouter une pièce jointe</a>
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
                                    <th>Nom du fichier</th>
                                    <th>Réponse</th>
                                    <th>Taille</th>
                                    <th>Type</th>
                                    <th>Date d'ajout</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($attachments as $attachment)
                                    <tr>
                                        <td>{{ $attachment->original_name }}</td>
                                        <td>{{ Str::limit($attachment->response->content ?? 'N/A', 50) }}</td>
                                        <td>{{ $attachment->file_size ? number_format($attachment->file_size / 1024, 2) . ' KB' : 'N/A' }}</td>
                                        <td>{{ $attachment->mime_type }}</td>
                                        <td>{{ $attachment->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('public.response-attachments.show', $attachment) }}" class="btn btn-info btn-sm">Voir</a>
                                            <a href="{{ route('public.response-attachments.edit', $attachment) }}" class="btn btn-warning btn-sm">Modifier</a>
                                            <form action="{{ route('public.response-attachments.destroy', $attachment) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette pièce jointe ?')">Supprimer</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{ $attachments->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
