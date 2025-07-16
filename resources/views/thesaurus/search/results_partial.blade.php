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
                                <a href="{{ route('thesaurus.show', $term->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fa fa-eye"></i>
                                </a>
                                <a href="{{ route('thesaurus.edit', $term->id) }}" class="btn btn-sm btn-secondary">
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
    <div class="d-flex justify-content-center mt-4 ajax-pagination">
        {{ $terms->links() }}
    </div>
@else
    <div class="alert alert-info">
        Aucun terme trouvé correspondant aux critères de recherche.
    </div>
@endif
