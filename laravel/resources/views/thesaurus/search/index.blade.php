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
                    <form action="{{ route('thesaurus.search.results') }}" method="GET" id="search-form">
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

@section('scripts')
<script>
$(document).ready(function() {
    // Conteneur des résultats
    const resultsContainer = $('<div id="search-results" class="mt-4"></div>');
    $('.card-body').append(resultsContainer);

    // Gestion du formulaire en AJAX
    $('#search-form').on('submit', function(e) {
        e.preventDefault();

        // Afficher un indicateur de chargement
        $('#search-results').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Chargement...</span></div></div>');

        // Récupérer les données du formulaire
        const formData = $(this).serialize();

        // Effectuer la requête AJAX
        $.ajax({
            url: $(this).attr('action'),
            type: 'GET',
            data: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            success: function(response) {
                // Afficher les résultats
                $('#search-results').html(`
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4>Résultats de la recherche</h4>
                            <div>
                                <button id="new-search-btn" class="btn btn-sm btn-outline-secondary">
                                    <i class="fa fa-search"></i> Modifier la recherche
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <h5>${response.total} résultats trouvés</h5>
                            </div>
                            <div id="results-content">
                                ${response.html}
                            </div>

                            <!-- Actions d'export -->
                            <div class="mt-4">
                                <h5>Exporter les résultats</h5>
                                <div class="btn-group">
                                    <a href="{{ route('thesaurus.export.skos') }}?${formData}" class="btn btn-outline-primary">
                                        <i class="fa fa-download"></i> Export SKOS
                                    </a>
                                    <a href="{{ route('thesaurus.export.csv') }}?${formData}" class="btn btn-outline-success">
                                        <i class="fa fa-file-excel"></i> Export CSV
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                `);

                // Gestion du bouton pour revenir au formulaire
                $('#new-search-btn').on('click', function() {
                    $('#search-results').hide();
                    $('#search-form').closest('.card').show();
                });

                // Gestion de la pagination AJAX
                setupAjaxPagination();

                // Cacher le formulaire
                $('#search-form').closest('.card').hide();
            },
            error: function(xhr, status, error) {
                $('#search-results').html(`
                    <div class="alert alert-danger">
                        <h4>Erreur lors de la recherche</h4>
                        <p>${error}</p>
                        <button id="try-again-btn" class="btn btn-outline-danger">Réessayer</button>
                    </div>
                `);

                $('#try-again-btn').on('click', function() {
                    $('#search-form').submit();
                });
            }
        });
    });

    // Fonction pour configurer la pagination AJAX
    function setupAjaxPagination() {
        $('.ajax-pagination a').on('click', function(e) {
            e.preventDefault();

            const url = $(this).attr('href');

            // Afficher un indicateur de chargement
            $('#results-content').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Chargement...</span></div></div>');

            // Effectuer la requête AJAX
            $.ajax({
                url: url,
                type: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(response) {
                    // Mettre à jour uniquement la partie des résultats
                    $('#results-content').html(response.html);

                    // Reconfigurer la pagination AJAX pour les nouveaux liens
                    setupAjaxPagination();
                },
                error: function(xhr, status, error) {
                    $('#results-content').html(`
                        <div class="alert alert-danger">
                            <h4>Erreur lors du chargement de la page</h4>
                            <p>${error}</p>
                            <button class="btn btn-outline-danger reload-btn">Réessayer</button>
                        </div>
                    `);

                    $('.reload-btn').on('click', function() {
                        window.location.href = url;
                    });
                }
            });
        });
    }
});
</script>
@endsection
