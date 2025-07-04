@extends('layouts.app')

@section('title', 'Alignements externes')

@section('content')
<div class="container-fluid">
    <h1>Alignements externes pour "{{ $term->preferred_label }}"</h1>

    <div class="mb-3">
        <a href="{{ route('terms.show', $term->id) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Retour au terme
        </a>
        <a href="{{ route('terms.external-alignments.create', $term->id) }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Ajouter un alignement externe
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5>Liste des alignements externes</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>URI</th>
                            <th>Libellé</th>
                            <th>Vocabulaire</th>
                            <th>Type de correspondance</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($alignments as $alignment)
                            <tr>
                                <td>
                                    <a href="{{ $alignment->external_uri }}" target="_blank" title="{{ $alignment->external_uri }}">
                                        {{ Str::limit($alignment->external_uri, 50) }}
                                    </a>
                                </td>
                                <td>{{ $alignment->external_label }}</td>
                                <td>{{ $alignment->external_vocabulary }}</td>
                                <td>
                                    @switch($alignment->match_type)
                                        @case('exact')
                                            <span class="badge bg-success">Exacte</span>
                                            @break
                                        @case('close')
                                            <span class="badge bg-primary">Proche</span>
                                            @break
                                        @case('broad')
                                            <span class="badge bg-info">Large</span>
                                            @break
                                        @case('narrow')
                                            <span class="badge bg-warning">Étroite</span>
                                            @break
                                        @case('related')
                                            <span class="badge bg-secondary">Associée</span>
                                            @break
                                    @endswitch
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('terms.external-alignments.edit', [$term->id, $alignment->id]) }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('terms.external-alignments.destroy', [$term->id, $alignment->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet alignement?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Aucun alignement externe trouvé.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
