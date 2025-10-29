@extends('layouts.app')

@section('title', __('OPAC Pages Management'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="page-title">{{ __('OPAC Pages Management') }}</h4>
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.opac.configurations.index') }}">{{ __('OPAC') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Pages') }}</li>
                    </ol>
                </div>
                <div class="page-title-right">
                    <a href="{{ route('admin.opac.pages.create') }}" class="btn btn-primary">
                        <i class="mdi mdi-plus me-1"></i> {{ __('Add New Page') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar-sm">
                                <span class="avatar-title bg-primary rounded-circle font-size-18">
                                    <i class="mdi mdi-file-multiple"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-0">{{ $totalPages }}</h5>
                            <p class="text-muted mb-0">{{ __('Total Pages') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar-sm">
                                <span class="avatar-title bg-success rounded-circle font-size-18">
                                    <i class="mdi mdi-check-circle"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-0">{{ $publishedPages }}</h5>
                            <p class="text-muted mb-0">{{ __('Published') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar-sm">
                                <span class="avatar-title bg-warning rounded-circle font-size-18">
                                    <i class="mdi mdi-file-edit"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-0">{{ $draftPages }}</h5>
                            <p class="text-muted mb-0">{{ __('Drafts') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar-sm">
                                <span class="avatar-title bg-info rounded-circle font-size-18">
                                    <i class="mdi mdi-eye"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <a href="{{ route('opac.pages.index') }}" target="_blank" class="text-decoration-none">
                                <h6 class="mb-0">{{ __('View OPAC') }}</h6>
                                <p class="text-muted mb-0 small">{{ __('Public View') }}</p>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.opac.pages.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Search') }}</label>
                            <input type="text" name="search" class="form-control"
                                   value="{{ request('search') }}"
                                   placeholder="{{ __('Search pages...') }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">{{ __('Status') }}</label>
                            <select name="status" class="form-select">
                                <option value="">{{ __('All Status') }}</option>
                                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>{{ __('Draft') }}</option>
                                <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>{{ __('Published') }}</option>
                                <option value="private" {{ request('status') === 'private' ? 'selected' : '' }}>{{ __('Private') }}</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">{{ __('Published') }}</label>
                            <select name="published" class="form-select">
                                <option value="">{{ __('All') }}</option>
                                <option value="yes" {{ request('published') === 'yes' ? 'selected' : '' }}>{{ __('Published') }}</option>
                                <option value="no" {{ request('published') === 'no' ? 'selected' : '' }}>{{ __('Not Published') }}</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="mdi mdi-magnify"></i> {{ __('Search') }}
                                </button>
                            </div>
                        </div>
                    </form>

                    @if(request()->hasAny(['search', 'status', 'published']))
                        <div class="mt-2">
                            <a href="{{ route('admin.opac.pages.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="mdi mdi-refresh"></i> {{ __('Clear Filters') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Pages Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if($pages->count() > 0)
                        <form id="bulkActionForm" method="POST" action="{{ route('admin.opac.pages.bulk-publish') }}">
                            @csrf
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <input type="checkbox" id="selectAll" class="form-check-input me-2">
                                    <label for="selectAll" class="form-check-label">{{ __('Select All') }}</label>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" name="action" value="publish"
                                            class="btn btn-success btn-sm bulk-action-btn" style="display: none;">
                                        <i class="mdi mdi-publish"></i> {{ __('Publish Selected') }}
                                    </button>
                                    <button type="submit" name="action" value="unpublish"
                                            class="btn btn-warning btn-sm bulk-action-btn" style="display: none;">
                                        <i class="mdi mdi-publish-off"></i> {{ __('Unpublish Selected') }}
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th width="30"><input type="checkbox" class="form-check-input"></th>
                                            <th>{{ __('Order') }}</th>
                                            <th>{{ __('Title') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Published') }}</th>
                                            <th>{{ __('Author') }}</th>
                                            <th>{{ __('Created') }}</th>
                                            <th>{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($pages as $page)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="page_ids[]"
                                                           value="{{ $page->id }}" class="form-check-input page-checkbox">
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark">{{ $page->order ?? 0 }}</span>
                                                </td>
                                                <td>
                                                    <div>
                                                        <h6 class="mb-0">
                                                            <a href="{{ route('admin.opac.pages.show', $page) }}"
                                                               class="text-decoration-none">
                                                                {{ $page->title }}
                                                            </a>
                                                        </h6>
                                                        @if($page->name)
                                                            <small class="text-muted">{{ $page->name }}</small>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    @switch($page->status)
                                                        @case('draft')
                                                            <span class="badge bg-warning">{{ __('Draft') }}</span>
                                                            @break
                                                        @case('published')
                                                            <span class="badge bg-success">{{ __('Published') }}</span>
                                                            @break
                                                        @case('private')
                                                            <span class="badge bg-secondary">{{ __('Private') }}</span>
                                                            @break
                                                        @default
                                                            <span class="badge bg-light text-dark">{{ ucfirst($page->status) }}</span>
                                                    @endswitch
                                                </td>
                                                <td>
                                                    @if($page->is_published)
                                                        <span class="badge bg-success">
                                                            <i class="mdi mdi-check"></i> {{ __('Yes') }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary">
                                                            <i class="mdi mdi-close"></i> {{ __('No') }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($page->author)
                                                        <small>{{ $page->author->name }}</small>
                                                    @else
                                                        <small class="text-muted">{{ __('Unknown') }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <small>{{ $page->created_at->format('M j, Y') }}</small>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('admin.opac.pages.show', $page) }}"
                                                           class="btn btn-sm btn-outline-info" title="{{ __('View') }}">
                                                            <i class="mdi mdi-eye"></i>
                                                        </a>
                                                        <a href="{{ route('admin.opac.pages.edit', $page) }}"
                                                           class="btn btn-sm btn-outline-primary" title="{{ __('Edit') }}">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </a>
                                                        <a href="{{ route('opac.pages.show', $page) }}" target="_blank"
                                                           class="btn btn-sm btn-outline-success" title="{{ __('View Public') }}">
                                                            <i class="mdi mdi-open-in-new"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </form>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-3">
                            {{ $pages->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="mdi mdi-file-multiple-outline" style="font-size: 4rem; color: #ddd;"></i>
                            <h4 class="mt-3">{{ __('No pages found') }}</h4>
                            <p class="text-muted">
                                @if(request()->hasAny(['search', 'status', 'published']))
                                    {{ __('No pages match your current filters.') }}
                                    <a href="{{ route('admin.opac.pages.index') }}">{{ __('Clear filters') }}</a>
                                @else
                                    {{ __('Start by creating your first OPAC page.') }}
                                @endif
                            </p>
                            <a href="{{ route('admin.opac.pages.create') }}" class="btn btn-primary">
                                <i class="mdi mdi-plus"></i> {{ __('Create First Page') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all functionality
    const selectAllCheckbox = document.getElementById('selectAll');
    const pageCheckboxes = document.querySelectorAll('.page-checkbox');
    const bulkActionButtons = document.querySelectorAll('.bulk-action-btn');

    selectAllCheckbox.addEventListener('change', function() {
        pageCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        toggleBulkActions();
    });

    pageCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', toggleBulkActions);
    });

    function toggleBulkActions() {
        const checkedBoxes = document.querySelectorAll('.page-checkbox:checked');
        const hasChecked = checkedBoxes.length > 0;

        bulkActionButtons.forEach(button => {
            button.style.display = hasChecked ? 'inline-block' : 'none';
        });

        // Update select all checkbox state
        selectAllCheckbox.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < pageCheckboxes.length;
        selectAllCheckbox.checked = checkedBoxes.length === pageCheckboxes.length;
    }
});
</script>
@endpush
@endsection
