@extends('layouts.app')

@section('title', 'Non-descripteurs')

@section('content')
<div class="container-fluid">
    <h1>Non-descripteurs pour "{{ $term->preferred_label }}"</h1>

    <div class="mb-3">
        <a href="{{ route('terms.show', $term->id) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Retour au terme
        </a>
        <a href="{{ route('terms.non-descriptors.create', $term->id) }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Ajouter un non-descripteur
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5>Liste des non-descripteurs</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Libellé</th>
                            <th>Type de relation</th>
                            <th>Caché</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($nonDescriptors as $nonDescriptor)
                            <tr>
                                <td>{{ $nonDescriptor->non_descriptor_label }}</td>
                                <td>
                                    @switch($nonDescriptor->relation_type)
                                        @case('synonym')
                                            Synonyme
                                            @break
                                        @case('quasi_synonym')
                                            Quasi-synonyme
                                            @break
                                        @case('abbreviation')
                                            Abréviation
                                            @break
                                        @case('acronym')
                                            Acronyme
                                            @break
                                        @case('scientific_name')
                                            Nom scientifique
                                            @break
                                        @case('common_name')
                                            Nom commun
                                            @break
                                        @case('brand_name')
                                            Nom de marque
                                            @break
                                        @case('variant_spelling')
                                            Variante orthographique
                                            @break
                                        @case('old_form')
                                            Forme ancienne
                                            @break
                                        @case('modern_form')
                                            Forme moderne
                                            @break
                                        @case('antonym')
                                            Antonyme
                                            @break
                                        @default
                                            {{ $nonDescriptor->relation_type }}
                                    @endswitch
                                </td>
                                <td>{{ $nonDescriptor->hidden ? 'Oui' : 'Non' }}</td>
                                <td>
                                    <a href="{{ route('terms.non-descriptors.edit', [$term->id, $nonDescriptor->id]) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('terms.non-descriptors.destroy', [$term->id, $nonDescriptor->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce non-descripteur?');">
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
                                <td colspan="4" class="text-center">Aucun non-descripteur trouvé.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
