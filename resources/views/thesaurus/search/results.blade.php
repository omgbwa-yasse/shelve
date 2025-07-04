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
                                                        <a href="{{ route('terms.show', $term->id) }}" class="btn btn-sm btn-primary">
                                                            <i class="fa fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('terms.edit', $term->id) }}" class="btn btn-sm btn-secondary">
                                                            <i class="fa fa-edit"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-center mt-4">
                                {{ $terms->links() }}
                            </div>
                        @else
                            <div class="alert alert-info">
                                Aucun terme trouvé correspondant aux critères de recherche.
                            </div>
                        @endif
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
