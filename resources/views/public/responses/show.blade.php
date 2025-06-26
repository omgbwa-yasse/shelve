@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>Détails de la réponse</h2>
                    <div>
                        <a href="{{ route('public.responses.edit', $response) }}" class="btn btn-warning">Modifier</a>
                        <a href="{{ route('public.responses.index') }}" class="btn btn-secondary">Retour à la liste</a>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Demande associée :</strong>
                            @if($response->documentRequest)
                                <a href="{{ route('public.document-requests.show', $response->documentRequest) }}">
                                    {{ $response->documentRequest->title }}
                                </a>
                            @else
                                N/A
                            @endif
                        </div>
                        <div class="col-md-6">
                            <strong>Statut :</strong>
                            @switch($response->status)
                                @case('draft')
                                    <span class="badge bg-secondary">Brouillon</span>
                                    @break
                                @case('sent')
                                    <span class="badge bg-success">Envoyé</span>
                                    @break
                                @default
                                    <span class="badge bg-light">{{ $response->status }}</span>
                            @endswitch
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Créé par :</strong> {{ $response->user->name ?? 'Inconnu' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Date de création :</strong> {{ $response->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>

                    @if($response->sent_at)
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <strong>Envoyé le :</strong> {{ $response->sent_at->format('d/m/Y H:i') }}
                            </div>
                        </div>
                    @endif

                    <div class="mb-3">
                        <strong>Contenu de la réponse :</strong>
                        <div class="mt-2 p-3 bg-light border rounded">
                            {{ $response->content }}
                        </div>
                    </div>

                    @if($response->attachments && $response->attachments->count() > 0)
                        <div class="mb-3">
                            <strong>Pièces jointes :</strong>
                            <div class="mt-2">
                                @foreach($response->attachments as $attachment)
                                    <div class="border p-2 mb-2 rounded">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $attachment->original_name }}</strong>
                                                <small class="text-muted">({{ number_format($attachment->size / 1024, 2) }} KB)</small>
                                            </div>
                                            <a href="{{ asset('storage/' . $attachment->file_path) }}" class="btn btn-sm btn-outline-primary" target="_blank">Télécharger</a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between">
                        <form action="{{ route('public.responses.destroy', $response) }}" method="POST"
                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette réponse ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Supprimer</button>
                        </form>

                        <div>
                            <small class="text-muted">
                                Dernière modification : {{ $response->updated_at->format('d/m/Y H:i') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
