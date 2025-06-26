@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>Détails de la pièce jointe</h2>
                    <div>
                        <a href="{{ route('public.response-attachments.edit', $attachment) }}" class="btn btn-warning">Modifier</a>
                        <a href="{{ route('public.response-attachments.index') }}" class="btn btn-secondary">Retour</a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Informations du fichier</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Nom original :</th>
                                    <td>{{ $attachment->original_name }}</td>
                                </tr>
                                <tr>
                                    <th>Nom système :</th>
                                    <td>{{ $attachment->file_name }}</td>
                                </tr>
                                <tr>
                                    <th>Taille :</th>
                                    <td>{{ $attachment->file_size ? number_format($attachment->file_size / 1024, 2) . ' KB' : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Type MIME :</th>
                                    <td>{{ $attachment->mime_type }}</td>
                                </tr>
                                <tr>
                                    <th>Chemin :</th>
                                    <td>{{ $attachment->file_path }}</td>
                                </tr>
                                <tr>
                                    <th>Date d'ajout :</th>
                                    <td>{{ $attachment->created_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Dernière modification :</th>
                                    <td>{{ $attachment->updated_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <h5>Réponse associée</h5>
                            @if($attachment->response)
                                <div class="card">
                                    <div class="card-body">
                                        <p><strong>Contenu :</strong></p>
                                        <p>{{ Str::limit($attachment->response->content, 200) }}</p>
                                        <p><strong>Utilisateur :</strong> {{ $attachment->response->user->name ?? 'Inconnu' }}</p>
                                        <p><strong>Date :</strong> {{ $attachment->response->created_at->format('d/m/Y H:i') }}</p>
                                        <a href="{{ route('public.responses.show', $attachment->response) }}" class="btn btn-sm btn-outline-primary">Voir la réponse complète</a>
                                    </div>
                                </div>
                            @else
                                <p class="text-muted">Aucune réponse associée</p>
                            @endif
                        </div>
                    </div>

                    @if($attachment->description)
                        <div class="mt-4">
                            <h5>Description</h5>
                            <div class="card">
                                <div class="card-body">
                                    {{ $attachment->description }}
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="mt-4">
                        <h5>Actions</h5>
                        @if(Storage::exists($attachment->file_path))
                            <a href="{{ Storage::url($attachment->file_path) }}" class="btn btn-success" target="_blank">Télécharger</a>
                        @else
                            <span class="text-danger">Fichier non trouvé</span>
                        @endif

                        <form action="{{ route('public.response-attachments.destroy', $attachment) }}" method="POST" class="d-inline ms-2">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette pièce jointe ?')">Supprimer</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
