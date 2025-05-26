@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('ai_feedback') }}</h5>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-secondary me-2" onclick="window.location.reload()">
                            <i class="bi bi-arrow-clockwise"></i> {{ __('refresh') }}
                        </button>
                        <a href="{{ route('ai.feedback.create') }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-lg"></i> {{ __('add_feedback') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtres -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="searchInput" placeholder="{{ __('search') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="typeFilter">
                                <option value="">{{ __('all_types') }}</option>
                                <option value="rating">{{ __('rating') }}</option>
                                <option value="comment">{{ __('comment') }}</option>
                                <option value="bug">{{ __('bug_report') }}</option>
                                <option value="suggestion">{{ __('suggestion') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="ratingFilter">
                                <option value="">{{ __('all_ratings') }}</option>
                                <option value="5">⭐⭐⭐⭐⭐</option>
                                <option value="4">⭐⭐⭐⭐</option>
                                <option value="3">⭐⭐⭐</option>
                                <option value="2">⭐⭐</option>
                                <option value="1">⭐</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="sortFilter">
                                <option value="newest">{{ __('newest_first') }}</option>
                                <option value="oldest">{{ __('oldest_first') }}</option>
                                <option value="rating">{{ __('highest_rating') }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Tableau des feedbacks -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>{{ __('type') }}</th>
                                    <th>{{ __('model') }}</th>
                                    <th>{{ __('rating') }}</th>
                                    <th>{{ __('feedback') }}</th>
                                    <th>{{ __('user') }}</th>
                                    <th>{{ __('created_at') }}</th>
                                    <th>{{ __('actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($feedbacks as $feedback)
                                    <tr>
                                        <td>{{ $feedback->id }}</td>
                                        <td>
                                            <span class="badge bg-{{ $feedback->type === 'rating' ? 'success' :
                                                ($feedback->type === 'comment' ? 'info' :
                                                ($feedback->type === 'bug' ? 'danger' : 'warning')) }}">
                                                {{ __($feedback->type) }}
                                            </span>
                                        </td>
                                        <td>{{ $feedback->model->name }}</td>
                                        <td>
                                            @if($feedback->type === 'rating')
                                                <div class="text-warning">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <i class="bi bi-star{{ $i <= $feedback->rating ? '-fill' : '' }}"></i>
                                                    @endfor
                                                </div>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 200px;" title="{{ $feedback->content }}">
                                                {{ $feedback->content }}
                                            </div>
                                        </td>
                                        <td>{{ $feedback->user->name }}</td>
                                        <td>{{ $feedback->created_at->format('Y-m-d H:i:s') }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('ai.feedback.show', $feedback) }}"
                                                   class="btn btn-outline-primary"
                                                   title="{{ __('view') }}">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('ai.feedback.edit', $feedback) }}"
                                                   class="btn btn-outline-secondary"
                                                   title="{{ __('edit') }}">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button type="button"
                                                        class="btn btn-outline-danger"
                                                        title="{{ __('delete') }}"
                                                        onclick="deleteFeedback({{ $feedback->id }})">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                                            <p class="mt-2 mb-0 text-muted">{{ __('no_feedback_found') }}</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-end mt-4">
                        {{ $feedbacks->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('confirm_deletion') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{ __('delete_feedback_confirmation') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {{ __('cancel') }}
                </button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        {{ __('delete') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.table th {
    font-weight: 500;
    background-color: #f8f9fa;
}

.badge {
    font-weight: 500;
    padding: 0.5em 0.75em;
}

.btn-group .btn {
    padding: 0.25rem 0.5rem;
}

.text-truncate {
    max-width: 200px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.modal-footer {
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
}

.text-warning .bi-star-fill {
    color: #ffc107;
}

.text-warning .bi-star {
    color: #dee2e6;
}
</style>
@endpush

@push('scripts')
<script>
function deleteFeedback(id) {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const form = document.getElementById('deleteForm');
    form.action = `/ai/feedback/${id}`;
    modal.show();
}

// Filtrage en temps réel
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const typeFilter = document.getElementById('typeFilter');
    const ratingFilter = document.getElementById('ratingFilter');
    const sortFilter = document.getElementById('sortFilter');
    const table = document.querySelector('table tbody');
    const rows = table.getElementsByTagName('tr');

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const typeValue = typeFilter.value;
        const ratingValue = ratingFilter.value;
        const sortValue = sortFilter.value;

        Array.from(rows).forEach(row => {
            if (row.cells.length === 1) return; // Skip empty message row

            const typeCell = row.cells[1].textContent.trim();
            const ratingCell = row.cells[3].textContent.trim();
            const contentCell = row.cells[4].textContent.toLowerCase();
            const modelCell = row.cells[2].textContent.toLowerCase();

            const matchesSearch = contentCell.includes(searchTerm) || modelCell.includes(searchTerm);
            const matchesType = !typeValue || typeCell === typeValue;
            const matchesRating = !ratingValue || ratingCell.includes('⭐'.repeat(ratingValue));

            row.style.display = matchesSearch && matchesType && matchesRating ? '' : 'none';
        });

        // Trier les lignes
        const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');
        visibleRows.sort((a, b) => {
            if (a.cells.length === 1 || b.cells.length === 1) return 0;

            if (sortValue === 'rating') {
                const ratingA = a.cells[3].textContent.split('⭐').length - 1;
                const ratingB = b.cells[3].textContent.split('⭐').length - 1;
                return ratingB - ratingA;
            }

            const dateA = new Date(a.cells[6].textContent);
            const dateB = new Date(b.cells[6].textContent);
            return sortValue === 'newest' ? dateB - dateA : dateA - dateB;
        });

        // Réorganiser les lignes dans le tableau
        visibleRows.forEach(row => table.appendChild(row));
    }

    searchInput.addEventListener('input', filterTable);
    typeFilter.addEventListener('change', filterTable);
    ratingFilter.addEventListener('change', filterTable);
    sortFilter.addEventListener('change', filterTable);
});
</script>
@endpush
