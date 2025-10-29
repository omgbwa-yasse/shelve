@extends('layouts.app')

@section('title', __('View OPAC Page'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="page-title">{{ __('View OPAC Page') }}</h4>
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.opac.configurations.index') }}">{{ __('OPAC') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.opac.pages.index') }}">{{ __('Pages') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('View') }}</li>
                    </ol>
                </div>
                <div class="page-title-right">
                    <div class="btn-group">
                        <a href="{{ route('admin.opac.pages.edit', $page) }}" class="btn btn-primary">
                            <i class="mdi mdi-pencil me-1"></i> {{ __('Edit') }}
                        </a>
                        <a href="{{ route('opac.pages.show', $page) }}" target="_blank" class="btn btn-success">
                            <i class="mdi mdi-open-in-new me-1"></i> {{ __('View Public') }}
                        </a>
                        <a href="{{ route('admin.opac.pages.index') }}" class="btn btn-secondary">
                            <i class="mdi mdi-arrow-left me-1"></i> {{ __('Back') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Page Content -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">{{ $page->title }}</h5>
                    <div>
                        @if($page->is_published)
                            <span class="badge bg-success">
                                <i class="mdi mdi-check-circle"></i> {{ __('Published') }}
                            </span>
                        @else
                            <span class="badge bg-secondary">
                                <i class="mdi mdi-clock"></i> {{ __('Not Published') }}
                            </span>
                        @endif

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
                    </div>
                </div>

                @if($page->image_path)
                    <div class="card-img-top">
                        <img src="{{ Storage::url($page->image_path) }}" alt="{{ $page->title }}" class="img-fluid">
                    </div>
                @endif

                <div class="card-body">
                    @if($page->meta_description)
                        <div class="alert alert-info">
                            <strong>{{ __('Description:') }}</strong> {{ $page->meta_description }}
                        </div>
                    @endif

                    @if($page->content)
                        <div class="content">
                            {!! $page->content !!}
                        </div>
                    @else
                        <div class="text-muted text-center py-4">
                            <i class="mdi mdi-file-document-outline" style="font-size: 3rem;"></i>
                            <p class="mt-2">{{ __('No content available') }}</p>
                        </div>
                    @endif
                </div>

                @if($page->updated_at != $page->created_at)
                    <div class="card-footer text-muted">
                        <small>
                            <i class="mdi mdi-clock-outline"></i>
                            {{ __('Last updated: :date', ['date' => $page->updated_at->format('M j, Y g:i A')]) }}
                        </small>
                    </div>
                @endif
            </div>

            <!-- Sub-pages -->
            @if($page->children && $page->children->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="mdi mdi-file-tree"></i> {{ __('Sub-pages') }} ({{ $page->children->count() }})
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($page->children as $child)
                                <div class="col-md-6 mb-3">
                                    <div class="card border">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <a href="{{ route('admin.opac.pages.show', $child) }}" class="text-decoration-none">
                                                    {{ $child->title }}
                                                </a>
                                            </h6>
                                            @if($child->meta_description)
                                                <p class="card-text">{{ Str::limit($child->meta_description, 100) }}</p>
                                            @endif
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">{{ $child->created_at->format('M j, Y') }}</small>
                                                <div>
                                                    @if($child->is_published)
                                                        <span class="badge bg-success">{{ __('Published') }}</span>
                                                    @else
                                                        <span class="badge bg-secondary">{{ __('Not Published') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Page Details -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('Page Details') }}</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>{{ __('Title:') }}</strong></td>
                            <td>{{ $page->title }}</td>
                        </tr>
                        @if($page->name)
                            <tr>
                                <td><strong>{{ __('URL Name:') }}</strong></td>
                                <td>{{ $page->name }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td><strong>{{ __('Status:') }}</strong></td>
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
                        </tr>
                        <tr>
                            <td><strong>{{ __('Published:') }}</strong></td>
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
                        </tr>
                        <tr>
                            <td><strong>{{ __('Order:') }}</strong></td>
                            <td>{{ $page->order ?? 0 }}</td>
                        </tr>
                        @if($page->author)
                            <tr>
                                <td><strong>{{ __('Author:') }}</strong></td>
                                <td>{{ $page->author->name }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td><strong>{{ __('Created:') }}</strong></td>
                            <td>{{ $page->created_at->format('M j, Y g:i A') }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('Modified:') }}</strong></td>
                            <td>{{ $page->updated_at->format('M j, Y g:i A') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Page Hierarchy -->
            @if($page->parent || ($page->children && $page->children->count() > 0))
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Page Hierarchy') }}</h5>
                    </div>
                    <div class="card-body">
                        @if($page->parent)
                            <div class="mb-3">
                                <strong>{{ __('Parent Page:') }}</strong><br>
                                <a href="{{ route('admin.opac.pages.show', $page->parent) }}" class="text-decoration-none">
                                    <i class="mdi mdi-file-document"></i> {{ $page->parent->title }}
                                </a>
                            </div>
                        @endif

                        @if($page->children && $page->children->count() > 0)
                            <div>
                                <strong>{{ __('Sub-pages:') }}</strong>
                                <ul class="list-unstyled mt-2">
                                    @foreach($page->children->sortBy('order') as $child)
                                        <li class="mb-1">
                                            <a href="{{ route('admin.opac.pages.show', $child) }}" class="text-decoration-none">
                                                <i class="mdi mdi-file-document-outline"></i> {{ $child->title }}
                                            </a>
                                            @if(!$child->is_published)
                                                <small class="text-muted">({{ __('not published') }})</small>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Public URLs -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('Public URLs') }}</h5>
                </div>
                <div class="card-body">
                    @if($page->is_published)
                        <div class="mb-3">
                            <strong>{{ __('Public URL:') }}</strong><br>
                            <a href="{{ route('opac.pages.show', $page) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="mdi mdi-open-in-new"></i> {{ __('View Page') }}
                            </a>
                        </div>

                        <div class="form-group">
                            <label for="pageUrl" class="form-label">{{ __('Copy URL:') }}</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="pageUrl" value="{{ route('opac.pages.show', $page) }}" readonly>
                                <button class="btn btn-outline-secondary" type="button" id="copyUrl">
                                    <i class="mdi mdi-content-copy"></i>
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="mdi mdi-alert-circle"></i>
                            {{ __('Page is not published and not visible to public users.') }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.opac.pages.edit', $page) }}" class="btn btn-primary">
                            <i class="mdi mdi-pencil me-1"></i> {{ __('Edit Page') }}
                        </a>

                        @if($page->is_published)
                            <a href="{{ route('opac.pages.show', $page) }}" target="_blank" class="btn btn-success">
                                <i class="mdi mdi-open-in-new me-1"></i> {{ __('View Public') }}
                            </a>
                        @endif

                        <a href="{{ route('admin.opac.pages.index') }}" class="btn btn-outline-secondary">
                            <i class="mdi mdi-arrow-left me-1"></i> {{ __('Back to List') }}
                        </a>

                        <hr>

                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="mdi mdi-delete me-1"></i> {{ __('Delete Page') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">{{ __('Delete Page') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('Are you sure you want to delete this page?') }}</p>
                <p><strong>{{ __('Title:') }}</strong> {{ $page->title }}</p>
                @if($page->children && $page->children->count() > 0)
                    <div class="alert alert-warning">
                        <i class="mdi mdi-alert-circle me-1"></i>
                        {{ __('This page has :count sub-pages. They will become top-level pages.', ['count' => $page->children->count()]) }}
                    </div>
                @endif
                <p class="text-danger mb-0">{{ __('This action cannot be undone.') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <form action="{{ route('admin.opac.pages.destroy', $page) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">{{ __('Delete') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Copy URL functionality
    const copyUrlBtn = document.getElementById('copyUrl');
    const pageUrlInput = document.getElementById('pageUrl');

    if (copyUrlBtn && pageUrlInput) {
        copyUrlBtn.addEventListener('click', function() {
            pageUrlInput.select();
            pageUrlInput.setSelectionRange(0, 99999); // For mobile devices

            try {
                document.execCommand('copy');

                // Change button text temporarily
                const originalHTML = this.innerHTML;
                this.innerHTML = '<i class="mdi mdi-check"></i>';
                this.classList.add('btn-success');
                this.classList.remove('btn-outline-secondary');

                setTimeout(() => {
                    this.innerHTML = originalHTML;
                    this.classList.remove('btn-success');
                    this.classList.add('btn-outline-secondary');
                }, 2000);
            } catch (err) {
                console.error('Failed to copy URL: ', err);
            }
        });
    }
});
</script>
@endpush
@endsection
