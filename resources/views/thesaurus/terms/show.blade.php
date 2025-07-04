@extends('layouts.app')

@section('title', 'Détails du terme')

@section('content')
<div class="container-fluid">
    <h1>{{ $term->preferred_label }}</h1>

    <div class="mb-3">
        <a href="{{ route('terms.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Retour à la liste
        </a>
        <a href="{{ route('terms.edit', $term->id) }}" class="btn btn-primary">
            <i class="bi bi-pencil"></i> Modifier
        </a>
        <form action="{{ route('terms.destroy', $term->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce terme?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">
                <i class="bi bi-trash"></i> Supprimer
            </button>
        </form>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Informations générales</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 30%">Libellé préféré</th>
                            <td>{{ $term->preferred_label }}</td>
                        </tr>
                        <tr>
                            <th>Langue</th>
                            <td>{{ $term->language }}</td>
                        </tr>
                        <tr>
                            <th>Catégorie</th>
                            <td>{{ $term->category ?? 'Non définie' }}</td>
                        </tr>
                        <tr>
                            <th>Statut</th>
                            <td>
                                @if($term->status == 'approved')
                                    <span class="badge bg-success">Approuvé</span>
                                @elseif($term->status == 'candidate')
                                    <span class="badge bg-warning">Candidat</span>
                                @else
                                    <span class="badge bg-danger">Obsolète</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Notation</th>
                            <td>{{ $term->notation ?? 'Non définie' }}</td>
                        </tr>
                        <tr>
                            <th>Terme de tête</th>
                            <td>{{ $term->is_top_term ? 'Oui' : 'Non' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Notes et définition</h5>
                </div>
                <div class="card-body">
                    <h6>Définition</h6>
                    <p>{{ $term->definition ?? 'Aucune définition' }}</p>

                    <h6>Note d'application</h6>
                    <p>{{ $term->scope_note ?? 'Aucune note d\'application' }}</p>

                    <h6>Note historique</h6>
                    <p>{{ $term->history_note ?? 'Aucune note historique' }}</p>

                    <h6>Exemple</h6>
                    <p>{{ $term->example ?? 'Aucun exemple' }}</p>

                    <h6>Note éditoriale</h6>
                    <p>{{ $term->editorial_note ?? 'Aucune note éditoriale' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Relations hiérarchiques -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Relations hiérarchiques</h5>
                    <div>
                        <a href="{{ route('terms.hierarchical-relations.broader.create', $term->id) }}" class="btn btn-sm btn-success">
                            <i class="bi bi-plus-circle"></i> Ajouter TG
                        </a>
                        <a href="{{ route('terms.hierarchical-relations.narrower.create', $term->id) }}" class="btn btn-sm btn-success">
                            <i class="bi bi-plus-circle"></i> Ajouter TS
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <h6>Termes génériques (TG)</h6>
                    <ul class="list-group mb-3">
                        @forelse($term->broaderTerms as $broaderTerm)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <a href="{{ route('terms.show', $broaderTerm->id) }}">{{ $broaderTerm->preferred_label }}</a>
                                    <span class="badge bg-info">{{ $broaderTerm->pivot->relation_type }}</span>
                                </div>
                                <form action="{{ route('terms.hierarchical-relations.destroy', [$term->id, 'broader', $broaderTerm->id]) }}" method="POST" onsubmit="return confirm('Supprimer cette relation?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">Aucun terme générique</li>
                        @endforelse
                    </ul>

                    <h6>Termes spécifiques (TS)</h6>
                    <ul class="list-group">
                        @forelse($term->narrowerTerms as $narrowerTerm)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <a href="{{ route('terms.show', $narrowerTerm->id) }}">{{ $narrowerTerm->preferred_label }}</a>
                                    <span class="badge bg-info">{{ $narrowerTerm->pivot->relation_type }}</span>
                                </div>
                                <form action="{{ route('terms.hierarchical-relations.destroy', [$term->id, 'narrower', $narrowerTerm->id]) }}" method="POST" onsubmit="return confirm('Supprimer cette relation?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">Aucun terme spécifique</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <!-- Relations associatives -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Termes associés (TA)</h5>
                    <a href="{{ route('terms.associative-relations.create', $term->id) }}" class="btn btn-sm btn-success">
                        <i class="bi bi-plus-circle"></i> Ajouter une relation
                    </a>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        @forelse($term->associatedTerms as $associatedTerm)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <a href="{{ route('terms.show', $associatedTerm->id) }}">{{ $associatedTerm->preferred_label }}</a>
                                    <span class="badge bg-info">{{ $associatedTerm->pivot->relation_subtype }}</span>
                                </div>
                                <form action="{{ route('terms.associative-relations.destroy', [$term->id, $associatedTerm->id]) }}" method="POST" onsubmit="return confirm('Supprimer cette relation?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">Aucun terme associé</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Non-descripteurs -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Termes non-descripteurs</h5>
                    <a href="{{ route('terms.non-descriptors.create', $term->id) }}" class="btn btn-sm btn-success">
                        <i class="bi bi-plus-circle"></i> Ajouter un non-descripteur
                    </a>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        @forelse($term->nonDescriptors as $nonDescriptor)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-bold">{{ $nonDescriptor->non_descriptor_label }}</span>
                                    <span class="badge bg-secondary">{{ $nonDescriptor->relation_type }}</span>
                                    @if($nonDescriptor->hidden)
                                        <span class="badge bg-dark">Caché</span>
                                    @endif
                                </div>
                                <div>
                                    <a href="{{ route('terms.non-descriptors.edit', [$term->id, $nonDescriptor->id]) }}" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i></a>
                                    <form action="{{ route('terms.non-descriptors.destroy', [$term->id, $nonDescriptor->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce non-descripteur?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">Aucun terme non-descripteur</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <!-- Traductions -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Traductions</h5>
                    <a href="{{ route('terms.translations.create', $term->id) }}" class="btn btn-sm btn-success">
                        <i class="bi bi-plus-circle"></i> Ajouter une traduction
                    </a>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        @php
                            $translations = collect([]);
                            if(isset($term->translationsSource)) {
                                $translations = $translations->merge($term->translationsSource);
                            }
                            if(isset($term->translationsTarget)) {
                                $translations = $translations->merge($term->translationsTarget);
                            }
                        @endphp

                        @forelse($translations as $relatedTerm)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <a href="{{ route('terms.show', $relatedTerm->id) }}">{{ $relatedTerm->preferred_label }}</a>
                                    <span class="badge bg-info">{{ $relatedTerm->language }}</span>
                                </div>
                                <form action="{{ route('terms.translations.destroy', [$term->id, $relatedTerm->id]) }}" method="POST" onsubmit="return confirm('Supprimer cette traduction?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">Aucune traduction</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Alignements externes -->
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Alignements externes</h5>
                    <a href="{{ route('terms.external-alignments.create', $term->id) }}" class="btn btn-sm btn-success">
                        <i class="bi bi-plus-circle"></i> Ajouter un alignement
                    </a>
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
                                @forelse($term->externalAlignments as $alignment)
                                    <tr>
                                        <td><a href="{{ $alignment->external_uri }}" target="_blank">{{ Str::limit($alignment->external_uri, 50) }}</a></td>
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
                                            <a href="{{ route('terms.external-alignments.edit', [$term->id, $alignment->id]) }}" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i></a>
                                            <form action="{{ route('terms.external-alignments.destroy', [$term->id, $alignment->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cet alignement?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Aucun alignement externe</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
