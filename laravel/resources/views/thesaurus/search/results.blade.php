@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Résultats de la recherche dans le thésaurus</h4>
                    <div>
                        <a href="{{ route('thesaurus.search.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fa fa-search"></i> Nouvelle recherche
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Récapitulatif des critères de recherche -->
                    <div class="alert alert-secondary">
                        <h5>Critères de recherche</h5>
                        <ul class="list-unstyled">
                            @if($request->filled('query'))
                                <li><strong>Terme : </strong> {{ $request->query }}</li>
                            @endif
                            @if($request->filled('language'))
                                <li><strong>Langue : </strong> {{ $languages[$request->language] ?? $request->language }}</li>
                            @endif
                            @if($request->filled('status'))
                                <li><strong>Statut : </strong> {{ $statuses[$request->status] ?? $request->status }}</li>
                            @endif
                            @if($request->filled('category'))
                                <li><strong>Catégorie : </strong> {{ $request->category }}</li>
                            @endif
                            @if($request->filled('content_search'))
                                <li><strong>Notes/Définitions : </strong> "{{ $request->content_search }}"</li>
                            @endif
                            @if($request->filled('external_uri'))
                                <li><strong>URI externe : </strong> {{ $request->external_uri }}</li>
                            @endif
                            @if($request->filled('external_vocabulary'))
                                <li><strong>Vocabulaire externe : </strong> {{ $request->external_vocabulary }}</li>
                            @endif
                            @if($request->filled('is_top_term'))
                                <li><strong>Top termes uniquement</strong></li>
                            @endif
                            @if($request->filled('has_broader'))
                                <li><strong>Avec termes génériques</strong></li>
                            @endif
                            @if($request->filled('has_narrower'))
                                <li><strong>Avec termes spécifiques</strong></li>
                            @endif
                            @if($request->filled('has_related'))
                                <li><strong>Avec termes associés</strong></li>
                            @endif
                            @if($request->filled('has_translations'))
                                <li><strong>Avec traductions</strong></li>
                            @endif
                        </ul>
                    </div>

                    <!-- Affichage des résultats -->
                    <div class="mt-4">
                        <h5>{{ $terms->total() }} résultats trouvés</h5>

                        <div id="search-results-container">
                        @if($terms->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Terme préféré</th>
                                            <th>Langue</th>
                                            <th>Catégorie</th>
                                            <th>Statut</th>
                                            <th>Relations</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($terms as $term)
                                            <tr>
                                                <td>
                                                    <strong>{{ $term->preferred_label }}</strong>
                                                    @if($term->is_top_term)
                                                        <span class="badge bg-info">TOP</span>
                                                    @endif
                                                </td>
                                                <td>{{ $languages[$term->language] ?? $term->language }}</td>
                                                <td>{{ $term->category ?? '-' }}</td>
                                                <td>
                                                    @if($term->status == 'approved')
                                                        <span class="badge bg-success">{{ $statuses[$term->status] ?? $term->status }}</span>
                                                    @elseif($term->status == 'candidate')
                                                        <span class="badge bg-warning">{{ $statuses[$term->status] ?? $term->status }}</span>
                                                    @elseif($term->status == 'deprecated')
                                                        <span class="badge bg-danger">{{ $statuses[$term->status] ?? $term->status }}</span>
                                                    @else
                                                        <span class="badge bg-secondary">{{ $term->status }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <small>
                                                        <div><strong>TG:</strong> {{ $term->broaderTerms->count() }}</div>
                                                        <div><strong>TS:</strong> {{ $term->narrowerTerms->count() }}</div>
                                                        <div><strong>TA:</strong> {{ $term->associatedTerms->count() }}</div>
                                                        <div><strong>EM:</strong> {{ $term->nonDescriptors->count() }}</div>
                                                        <div><strong>TR:</strong> {{ $term->translationsSource->count() + $term->translationsTarget->count() }}</div>
                                                        <div><strong>Alignements:</strong> {{ $term->externalAlignments->count() }}</div>
                                                    </small>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-sm btn-primary"
                                                                onclick="showConceptDetails({{ $term->id }})" title="Voir les détails">
                                                            <i class="fa fa-eye"></i>
                                                        </button>
                                                        <a href="{{ route('thesaurus.record-concept-relations', ['concept_id' => $term->id]) }}"
                                                           class="btn btn-sm btn-secondary" title="Voir les relations">
                                                            <i class="fa fa-link"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-center mt-4 ajax-pagination">
                                {{ $terms->links() }}
                            </div>
                        @else
                            <div class="alert alert-info">
                                Aucun terme trouvé correspondant aux critères de recherche.
                            </div>
                        @endif
                        </div>
                    </div>

                    <!-- Actions d'export -->
                    <div class="mt-4">
                        <h5>Exporter les résultats</h5>
                        <div class="btn-group">
                            <a href="{{ route('thesaurus.export.skos', request()->query()) }}" class="btn btn-outline-primary">
                                <i class="fa fa-download"></i> Export SKOS
                            </a>
                            <a href="{{ route('thesaurus.export.csv', request()->query()) }}" class="btn btn-outline-success">
                                <i class="fa fa-file-excel"></i> Export CSV
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Gestion de la pagination AJAX
    setupAjaxPagination();

    function setupAjaxPagination() {
        $('.ajax-pagination a').on('click', function(e) {
            e.preventDefault();

            const url = $(this).attr('href');

            // Afficher un indicateur de chargement
            $('#search-results-container').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Chargement...</span></div></div>');

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
                    $('#search-results-container').html(response.html);

                    // Reconfigurer la pagination AJAX pour les nouveaux liens
                    setupAjaxPagination();

                    // Scroll jusqu'au début des résultats
                    $('html, body').animate({
                        scrollTop: $('#search-results-container').offset().top - 100
                    }, 200);
                },
                error: function(xhr, status, error) {
                    $('#search-results-container').html(`
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

// Fonction pour afficher les détails d'un concept
function showConceptDetails(conceptId) {
    // Pour l'instant, on redirige vers la page des relations
    window.location.href = '{{ route("thesaurus.record-concept-relations") }}?concept_id=' + conceptId;
}
</script>
@endsection
