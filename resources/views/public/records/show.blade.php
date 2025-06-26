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
                            <strong>Type :</strong> {{ $record->record_type }}
                        </div>
                        <div class="col-md-6">
                            <strong>Référence :</strong> {{ $record->reference_number }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Statut :</strong>
                            @switch($record->status)
                                @case('draft')
                                    <span class="badge bg-secondary">Brouillon</span>
                                    @break
                                @case('published')
                                    <span class="badge bg-success">Publié</span>
                                    @break
                                @case('archived')
                                    <span class="badge bg-warning">Archivé</span>
                                    @break
                                @default
                                    <span class="badge bg-light">{{ $record->status }}</span>
                            @endswitch
                        </div>
                        <div class="col-md-6">
                            <strong>Créé par :</strong> {{ $record->publisher->name ?? 'Inconnu' }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong>Description :</strong>
                        <div class="mt-2 p-3 bg-light border rounded">
                            {{ $record->description }}
                        </div>
                    </div>

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
