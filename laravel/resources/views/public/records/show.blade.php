@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>{{ $record->title }}</h2>
                    <div>
                        <a href="{{ route('public.records.edit', $record) }}" class="btn btn-warning">Modifier</a>
                        <a href="{{ route('public.records.index') }}" class="btn btn-secondary">Retour à la liste</a>
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
                            <strong>Titre :</strong> {{ $record->title }}
                        </div>
                        <div class="col-md-6">
                            <strong>Référence :</strong> {{ $record->code }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Date :</strong> {{ $record->formatted_date_range }}
                        </div>
                        <div class="col-md-6">
                            <strong>Statut :</strong>
                            @if($record->is_expired)
                                <span class="badge bg-danger">Expiré</span>
                            @elseif($record->is_available)
                                <span class="badge bg-success">Disponible</span>
                            @else
                                <span class="badge bg-warning">En attente</span>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Date de publication :</strong> {{ $record->published_at ? $record->published_at->format('d/m/Y H:i') : '-' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Date d'expiration :</strong> {{ $record->expires_at ? $record->expires_at->format('d/m/Y H:i') : 'Pas d\'expiration' }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Publié par :</strong> {{ $record->publishers->pluck('name')->join(', ') ?? 'Inconnu' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Créé le :</strong> {{ $record->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>

                    @if($record->publication_notes)
                    <div class="mb-3">
                        <strong>Notes de publication :</strong>
                        <div class="mt-2 p-3 bg-light border rounded">
                            {{ $record->publication_notes }}
                        </div>
                    </div>
                    @endif

                    @if($record->content)
                    <div class="mb-3">
                        <strong>Contenu du document :</strong>
                        <div class="mt-2 p-3 bg-light border rounded">
                            {{ $record->content }}
                        </div>
                    </div>
                    @endif

                    @if($record->biographical_history)
                    <div class="mb-3">
                        <strong>Histoire biographique :</strong>
                        <div class="mt-2 p-3 bg-light border rounded">
                            {{ $record->biographical_history }}
                        </div>
                    </div>
                    @endif

                    @if($record->access_conditions)
                    <div class="mb-3">
                        <strong>Conditions d'accès :</strong>
                        <div class="mt-2 p-3 bg-light border rounded">
                            {{ $record->access_conditions }}
                        </div>
                    </div>
                    @endif

                    @if($record->language_material)
                    <div class="mb-3">
                        <strong>Langue du matériel :</strong> {{ $record->language_material }}
                    </div>
                    @endif

                    @if($record->attachments && $record->attachments->count() > 0)
                        <div class="mb-3">
                            <strong>Pièces jointes :</strong>
                            <div class="mt-2">
                                @foreach($record->attachments as $attachment)
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
                        <form action="{{ route('public.records.destroy', $record) }}" method="POST"
                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce document ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Supprimer</button>
                        </form>

                        <div>
                            <small class="text-muted">
                                Créé le : {{ $record->created_at->format('d/m/Y H:i') }} |
                                Modifié le : {{ $record->updated_at->format('d/m/Y H:i') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
