@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('ai_jobs') }}</h5>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-secondary me-2" onclick="window.location.reload()">
                            <i class="bi bi-arrow-clockwise"></i> {{ __('refresh') }}
                        </button>
                        <a href="{{ route('ai.jobs.create') }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-lg"></i> {{ __('add_job') }}
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
                                <option value="training">{{ __('training') }}</option>
                                <option value="inference">{{ __('inference') }}</option>
                                <option value="evaluation">{{ __('evaluation') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="statusFilter">
                                <option value="">{{ __('all_statuses') }}</option>
                                <option value="pending">{{ __('pending') }}</option>
                                <option value="running">{{ __('running') }}</option>
                                <option value="completed">{{ __('completed') }}</option>
                                <option value="failed">{{ __('failed') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="sortFilter">
                                <option value="newest">{{ __('newest_first') }}</option>
                                <option value="oldest">{{ __('oldest_first') }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Tableau des jobs -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>{{ __('type') }}</th>
                                    <th>{{ __('model') }}</th>
                                    <th>{{ __('status') }}</th>
                                    <th>{{ __('progress') }}</th>
                                    <th>{{ __('started_at') }}</th>
                                    <th>{{ __('completed_at') }}</th>
                                    <th>{{ __('actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($jobs as $job)
                                    <tr>
                                        <td>{{ $job->id }}</td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ __($job->type) }}
                                            </span>
                                        </td>
                                        <td>{{ $job->model->name }}</td>
                                        <td>
                                            <span class="badge bg-{{ $job->status === 'completed' ? 'success' :
                                                ($job->status === 'failed' ? 'danger' :
                                                ($job->status === 'running' ? 'primary' : 'warning')) }}">
                                                {{ __($job->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($job->status === 'running')
                                                <div class="progress" style="height: 5px;">
                                                    <div class="progress-bar progress-bar-striped progress-bar-animated"
                                                         role="progressbar"
                                                         style="width: {{ $job->progress }}%"
                                                         aria-valuenow="{{ $job->progress }}"
                                                         aria-valuemin="0"
                                                         aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <small class="text-muted">{{ $job->progress }}%</small>
                                            @elseif($job->status === 'completed')
                                                <span class="text-success">100%</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $job->started_at ? $job->started_at->format('Y-m-d H:i:s') : '-' }}</td>
                                        <td>{{ $job->completed_at ? $job->completed_at->format('Y-m-d H:i:s') : '-' }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('ai.jobs.show', $job) }}"
                                                   class="btn btn-outline-primary"
                                                   title="{{ __('view') }}">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                @if($job->status === 'pending')
                                                    <button type="button"
                                                            class="btn btn-outline-success"
                                                            title="{{ __('start') }}"
                                                            onclick="startJob({{ $job->id }})">
                                                        <i class="bi bi-play-fill"></i>
                                                    </button>
                                                @endif
                                                @if(in_array($job->status, ['pending', 'running']))
                                                    <button type="button"
                                                            class="btn btn-outline-danger"
                                                            title="{{ __('cancel') }}"
                                                            onclick="cancelJob({{ $job->id }})">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                                            <p class="mt-2 mb-0 text-muted">{{ __('no_jobs_found') }}</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-end mt-4">
                        {{ $jobs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation d'annulation -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('confirm_cancellation') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{ __('cancel_job_confirmation') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {{ __('cancel') }}
                </button>
                <form id="cancelForm" method="POST" style="display: inline;">
                    @csrf
                    @method('POST')
                    <button type="submit" class="btn btn-danger">
                        {{ __('confirm_cancel') }}
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

.progress {
    background-color: #e9ecef;
    border-radius: 0.25rem;
    margin-bottom: 0.25rem;
}

.modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.modal-footer {
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
}
</style>
@endpush

@push('scripts')
<script>
function startJob(id) {
    fetch(`/ai/jobs/${id}/start`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        }
    });
}

function cancelJob(id) {
    const modal = new bootstrap.Modal(document.getElementById('cancelModal'));
    const form = document.getElementById('cancelForm');
    form.action = `/ai/jobs/${id}/cancel`;
    modal.show();
}

// Filtrage en temps réel
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const typeFilter = document.getElementById('typeFilter');
    const statusFilter = document.getElementById('statusFilter');
    const sortFilter = document.getElementById('sortFilter');
    const table = document.querySelector('table tbody');
    const rows = table.getElementsByTagName('tr');

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const typeValue = typeFilter.value;
        const statusValue = statusFilter.value;
        const sortValue = sortFilter.value;

        Array.from(rows).forEach(row => {
            if (row.cells.length === 1) return; // Skip empty message row

            const typeCell = row.cells[1].textContent.trim();
            const statusCell = row.cells[3].textContent.trim();
            const modelCell = row.cells[2].textContent.toLowerCase();

            const matchesSearch = modelCell.includes(searchTerm);
            const matchesType = !typeValue || typeCell === typeValue;
            const matchesStatus = !statusValue || statusCell === statusValue;

            row.style.display = matchesSearch && matchesType && matchesStatus ? '' : 'none';
        });

        // Trier les lignes
        const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');
        visibleRows.sort((a, b) => {
            if (a.cells.length === 1 || b.cells.length === 1) return 0;
            const dateA = new Date(a.cells[5].textContent);
            const dateB = new Date(b.cells[5].textContent);
            return sortValue === 'newest' ? dateB - dateA : dateA - dateB;
        });

        // Réorganiser les lignes dans le tableau
        visibleRows.forEach(row => table.appendChild(row));
    }

    searchInput.addEventListener('input', filterTable);
    typeFilter.addEventListener('change', filterTable);
    statusFilter.addEventListener('change', filterTable);
    sortFilter.addEventListener('change', filterTable);
});
</script>
@endpush
