@extends('layouts.opac')

@section('title', __('My Document Requests'))

@section('content')
<div class="container py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">
            <i class="bi bi-file-earmark-text text-primary"></i>
            {{ __('My Document Requests') }}
        </h1>
        <a href="{{ route('opac.document-requests.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i>
            {{ __('New Request') }}
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Requests List -->
    @if($requests->count() > 0)
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Request #') }}</th>
                                <th>{{ __('Title') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Priority') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requests as $request)
                            <tr>
                                <td>
                                    <span class="badge bg-secondary">{{ $request->request_number }}</span>
                                </td>
                                <td>
                                    <strong>{{ $request->title }}</strong>
                                    @if($request->author)
                                        <br><small class="text-muted">{{ __('by') }} {{ $request->author }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        {{ __(ucfirst($request->document_type)) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'under_review' => 'info',
                                            'approved' => 'success',
                                            'fulfilled' => 'success',
                                            'rejected' => 'danger',
                                            'cancelled' => 'secondary'
                                        ];
                                        $color = $statusColors[$request->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }}">
                                        {{ __(ucfirst(str_replace('_', ' ', $request->status))) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $urgencyColors = [
                                            'low' => 'success',
                                            'normal' => 'primary',
                                            'high' => 'warning',
                                            'urgent' => 'danger'
                                        ];
                                        $urgencyColor = $urgencyColors[$request->urgency] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $urgencyColor }}">
                                        {{ __(ucfirst($request->urgency)) }}
                                    </span>
                                </td>
                                <td>
                                    <small>{{ $request->created_at->format('M j, Y') }}</small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('opac.document-requests.show', $request) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="{{ __('View Details') }}">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        @if(in_array($request->status, ['pending', 'under_review']))
                                            <a href="{{ route('opac.document-requests.edit', $request) }}"
                                               class="btn btn-sm btn-outline-warning"
                                               title="{{ __('Edit') }}">
                                                <i class="bi bi-pencil"></i>
                                            </a>

                                            <form action="{{ route('opac.document-requests.cancel', $request) }}"
                                                  method="POST"
                                                  class="d-inline"
                                                  onsubmit="return confirm('{{ __('Are you sure you want to cancel this request?') }}')">
                                                @csrf
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-danger"
                                                        title="{{ __('Cancel') }}">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $requests->links() }}
        </div>

    @else
        <!-- Empty State -->
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-file-earmark-text display-1 text-muted mb-3"></i>
                <h4 class="text-muted">{{ __('No Document Requests') }}</h4>
                <p class="text-muted mb-4">
                    {{ __('You haven\'t made any document requests yet.') }}
                </p>
                <a href="{{ route('opac.document-requests.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i>
                    {{ __('Create Your First Request') }}
                </a>
            </div>
        </div>
    @endif

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <i class="bi bi-search text-primary display-6"></i>
                    <h5 class="card-title mt-2">{{ __('Search Catalog') }}</h5>
                    <p class="card-text">{{ __('Browse our collection before requesting.') }}</p>
                    <a href="{{ route('opac.search') }}" class="btn btn-primary">
                        {{ __('Search Now') }}
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-success">
                <div class="card-body text-center">
                    <i class="bi bi-chat-square-dots text-success display-6"></i>
                    <h5 class="card-title mt-2">{{ __('Need Help?') }}</h5>
                    <p class="card-text">{{ __('Contact us for assistance with your requests.') }}</p>
                    <a href="{{ route('opac.feedback.create') }}" class="btn btn-success">
                        {{ __('Get Help') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.table th {
    border-top: none;
    font-weight: 600;
}

.badge {
    font-size: 0.75em;
}

.btn-group .btn {
    border-radius: 0.375rem;
    margin-right: 2px;
}

.card.border-primary {
    border-width: 2px;
}

.card.border-success {
    border-width: 2px;
}
</style>
@endpush
