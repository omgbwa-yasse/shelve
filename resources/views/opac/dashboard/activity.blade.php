@extends('opac.layouts.app')

@section('title', __('My Activity'))

@section('content')
<div class="container py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="bi bi-activity me-2"></i>
                        {{ __('My Activity') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Track all your library interactions and history') }}</p>
                </div>
                <a href="{{ route('opac.dashboard') }}" class="btn btn-outline-primary">
                    <i class="bi bi-house me-1"></i>
                    {{ __('Back to Dashboard') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Activity Tabs -->
    <div class="row">
        <div class="col-12">
            <ul class="nav nav-pills mb-4" id="activityTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="reservations-tab" data-bs-toggle="pill"
                            data-bs-target="#reservations" type="button" role="tab"
                            aria-controls="reservations" aria-selected="true">
                        <i class="bi bi-bookmark me-1"></i>
                        {{ __('Reservations') }}
                        <span class="badge bg-light text-dark ms-1">{{ $activities['reservations']->count() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="requests-tab" data-bs-toggle="pill"
                            data-bs-target="#requests" type="button" role="tab"
                            aria-controls="requests" aria-selected="false">
                        <i class="bi bi-file-earmark-text me-1"></i>
                        {{ __('Document Requests') }}
                        <span class="badge bg-light text-dark ms-1">{{ $activities['requests']->count() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="searches-tab" data-bs-toggle="pill"
                            data-bs-target="#searches" type="button" role="tab"
                            aria-controls="searches" aria-selected="false">
                        <i class="bi bi-search me-1"></i>
                        {{ __('Search History') }}
                        <span class="badge bg-light text-dark ms-1">{{ $activities['searches']->count() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="feedback-tab" data-bs-toggle="pill"
                            data-bs-target="#feedback" type="button" role="tab"
                            aria-controls="feedback" aria-selected="false">
                        <i class="bi bi-chat-square-dots me-1"></i>
                        {{ __('Feedback') }}
                        <span class="badge bg-light text-dark ms-1">{{ $activities['feedback']->count() }}</span>
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <!-- Tab Content -->
    <div class="row">
        <div class="col-12">
            <div class="tab-content" id="activityTabsContent">
                <!-- Reservations Tab -->
                <div class="tab-pane fade show active" id="reservations" role="tabpanel"
                     aria-labelledby="reservations-tab">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">{{ __('My Reservations') }}</h5>
                        </div>
                        <div class="card-body">
                            @if($activities['reservations']->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Document') }}</th>
                                                <th>{{ __('Status') }}</th>
                                                <th>{{ __('Reserved Date') }}</th>
                                                <th>{{ __('Due Date') }}</th>
                                                <th>{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($activities['reservations'] as $reservation)
                                            <tr>
                                                <td>
                                                    <strong>{{ $reservation->document_title ?? 'Document Title' }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $reservation->document_code ?? 'DOC-001' }}</small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-warning">{{ __('Active') }}</span>
                                                </td>
                                                <td>{{ $reservation->created_at->format('d/m/Y') }}</td>
                                                <td>{{ $reservation->due_date ?? 'N/A' }}</td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-danger">
                                                        {{ __('Cancel') }}
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="bi bi-bookmark display-1 text-muted mb-3"></i>
                                    <h5 class="text-muted">{{ __('No reservations yet') }}</h5>
                                    <p class="text-muted">{{ __('Your document reservations will appear here.') }}</p>
                                    <a href="{{ route('opac.records.index') }}" class="btn btn-primary">
                                        {{ __('Browse Documents') }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Document Requests Tab -->
                <div class="tab-pane fade" id="requests" role="tabpanel" aria-labelledby="requests-tab">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">{{ __('My Document Requests') }}</h5>
                        </div>
                        <div class="card-body">
                            @if($activities['requests']->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Request') }}</th>
                                                <th>{{ __('Status') }}</th>
                                                <th>{{ __('Submitted') }}</th>
                                                <th>{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($activities['requests'] as $request)
                                            <tr>
                                                <td>
                                                    <strong>{{ $request->title ?? 'Document Request' }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ Str::limit($request->description ?? 'Request description', 50) }}</small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">{{ __('Processing') }}</span>
                                                </td>
                                                <td>{{ $request->created_at->format('d/m/Y H:i') }}</td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary">
                                                        {{ __('View') }}
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="bi bi-file-earmark-text display-1 text-muted mb-3"></i>
                                    <h5 class="text-muted">{{ __('No document requests') }}</h5>
                                    <p class="text-muted">{{ __('Your document requests will appear here.') }}</p>
                                    <a href="{{ route('opac.document-requests.create') }}" class="btn btn-success">
                                        {{ __('Create Request') }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Search History Tab -->
                <div class="tab-pane fade" id="searches" role="tabpanel" aria-labelledby="searches-tab">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">{{ __('Recent Searches') }}</h5>
                            <a href="{{ route('opac.search.history') }}" class="btn btn-sm btn-outline-primary">
                                {{ __('View Full History') }}
                            </a>
                        </div>
                        <div class="card-body">
                            @if($activities['searches']->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($activities['searches']->take(10) as $search)
                                    <div class="list-group-item px-0">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">{{ $search->search_term }}</h6>
                                            <small>{{ $search->created_at->diffForHumans() }}</small>
                                        </div>
                                        <p class="mb-1 text-muted">
                                            {{ $search->results_count }} {{ __('results found') }}
                                        </p>
                                        <small class="text-muted">{{ $search->created_at->format('d/m/Y H:i') }}</small>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="bi bi-search display-1 text-muted mb-3"></i>
                                    <h5 class="text-muted">{{ __('No search history') }}</h5>
                                    <p class="text-muted">{{ __('Your search history will appear here.') }}</p>
                                    <a href="{{ route('opac.search') }}" class="btn btn-primary">
                                        {{ __('Start Searching') }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Feedback Tab -->
                <div class="tab-pane fade" id="feedback" role="tabpanel" aria-labelledby="feedback-tab">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">{{ __('My Feedback') }}</h5>
                        </div>
                        <div class="card-body">
                            @if($activities['feedback']->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($activities['feedback'] as $feedback)
                                    <div class="list-group-item px-0">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">{{ $feedback->subject ?? 'Feedback' }}</h6>
                                            <small>{{ $feedback->created_at->diffForHumans() }}</small>
                                        </div>
                                        <p class="mb-1">{{ Str::limit($feedback->message ?? 'Feedback message', 100) }}</p>
                                        <small class="text-muted">
                                            <span class="badge bg-secondary">{{ $feedback->status ?? 'Pending' }}</span>
                                        </small>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="bi bi-chat-square-dots display-1 text-muted mb-3"></i>
                                    <h5 class="text-muted">{{ __('No feedback submitted') }}</h5>
                                    <p class="text-muted">{{ __('Your feedback submissions will appear here.') }}</p>
                                    <a href="{{ route('opac.feedback.create') }}" class="btn btn-warning">
                                        {{ __('Send Feedback') }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Bootstrap tabs
    var triggerTabList = [].slice.call(document.querySelectorAll('#activityTabs button'))
    triggerTabList.forEach(function (triggerEl) {
        var tabTrigger = new bootstrap.Tab(triggerEl)

        triggerEl.addEventListener('click', function (event) {
            event.preventDefault()
            tabTrigger.show()
        })
    })
});
</script>
@endpush

@push('styles')
<style>
.nav-pills .nav-link {
    border-radius: 50px;
    margin-right: 10px;
}

.nav-pills .nav-link.active {
    background-color: #0d6efd;
}

.badge {
    font-size: 0.7em;
}

.table th {
    border-top: none;
    font-weight: 600;
}

.list-group-item:hover {
    background-color: #f8f9fa;
}
</style>
@endpush
