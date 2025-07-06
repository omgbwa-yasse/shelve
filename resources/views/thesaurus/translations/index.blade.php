@extends('layouts.app')

@section('title', 'Traductions')

@section('content')
<div class="container-fluid">
    <h1>Traductions pour "{{ $term->preferred_label }}"</h1>

    <div class="mb-3">
        <a href="{{ route('terms.show', $term->id) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Retour au terme
        </a>
        <a href="{{ route('terms.translations.create', $term->id) }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Ajouter une traduction
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5>Liste des traductions</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Terme</th>
                            <th>Langue</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($translations as $translation)
                            <tr>
                                <td>
                                    <a href="{{ route('terms.show', $translation->id) }}">
                                        {{ $translation->preferred_label }}
                                    </a>
                                </td>
                                <td>
                                    @switch($translation->language)
                                        @case('fr')
                                            <span class="badge bg-primary">Français</span>
                                            @break
                                        @case('en')
                                            <span class="badge bg-success">Anglais</span>
                                            @break
                                        @case('es')
                                            <span class="badge bg-warning">Espagnol</span>
                                            @break
                                        @case('de')
                                            <span class="badge bg-danger">Allemand</span>
                                            @break
                                        @case('it')
                                            <span class="badge bg-info">Italien</span>
                                            @break
                                        @case('pt')
                                            <span class="badge bg-dark">Portugais</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ $translation->language }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    <form action="{{ route('terms.translations.destroy', [$term->id, $translation->id]) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette traduction?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">Aucune traduction trouvée.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
