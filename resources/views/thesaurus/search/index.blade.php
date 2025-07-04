@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Recherche avancée dans le thésaurus</h4>
                </div>

                <div class="card-body">
                    <form action="{{ route('thesaurus.search.results') }}" method="GET">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="query">Terme recherché</label>
                                    <input type="text" name="query" id="query" class="form-control" placeholder="Rechercher un terme ou non-descripteur">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="language">Langue</label>
                                    <select name="language" id="language" class="form-control">
                                        <option value="">Toutes les langues</option>
                                        @foreach($languages as $code => $name)
                                            <option value="{{ $code }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="status">Statut</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="">Tous les statuts</option>
                                        @foreach($statuses as $code => $name)
                                            <option value="{{ $code }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="category">Catégorie</label>
                                    <select name="category" id="category" class="form-control">
                                        <option value="">Toutes les catégories</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category }}">{{ $category }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="content_search">Recherche dans les notes et définitions</label>
                                    <input type="text" name="content_search" id="content_search" class="form-control" placeholder="Rechercher dans les notes, définitions et exemples">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="external_uri">URI externe</label>
                                    <input type="text" name="external_uri" id="external_uri" class="form-control" placeholder="Rechercher par URI externe">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="external_vocabulary">Vocabulaire externe</label>
                                    <input type="text" name="external_vocabulary" id="external_vocabulary" class="form-control" placeholder="Rechercher par vocabulaire externe">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Relations</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_top_term" name="is_top_term" value="1">
                                        <label class="form-check-label" for="is_top_term">
                                            Top termes uniquement
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="has_broader" name="has_broader" value="1">
                                        <label class="form-check-label" for="has_broader">
                                            Avec termes génériques
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="has_narrower" name="has_narrower" value="1">
                                        <label class="form-check-label" for="has_narrower">
                                            Avec termes spécifiques
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="has_related" name="has_related" value="1">
                                        <label class="form-check-label" for="has_related">
                                            Avec termes associés
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="has_translations" name="has_translations" value="1">
                                        <label class="form-check-label" for="has_translations">
                                            Avec traductions
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="sort_by">Trier par</label>
                                    <select name="sort_by" id="sort_by" class="form-control">
                                        <option value="preferred_label">Libellé</option>
                                        <option value="created_at">Date de création</option>
                                        <option value="updated_at">Date de mise à jour</option>
                                        <option value="status">Statut</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="sort_direction">Direction</label>
                                    <select name="sort_direction" id="sort_direction" class="form-control">
                                        <option value="asc">Ascendant</option>
                                        <option value="desc">Descendant</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary form-control">
                                        <i class="fa fa-search"></i> Rechercher
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
