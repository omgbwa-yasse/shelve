@extends('opac.layouts.app')

@section('title', __('My Dashboard') . ' - OPAC')

@section('content')
<div class="container my-5">

    {{-- Header --}}
    <div class="mb-4">
        <h1 class="h3 mb-1">{{ __('Welcome') }}, {{ $user->name }}</h1>
        <p class="text-muted">{{ __('Your personal OPAC dashboard') }}</p>
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="opac-card text-center p-3">
                <div class="h2 mb-1 text-primary">{{ $stats['total_records'] }}</div>
                <small class="text-muted">{{ __('Records in catalog') }}</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="opac-card text-center p-3">
                <div class="h2 mb-1 text-success">{{ $stats['upcoming_events'] }}</div>
                <small class="text-muted">{{ __('Upcoming events') }}</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="opac-card text-center p-3">
                <div class="h2 mb-1 text-info">{{ $userStats['requests_count'] }}</div>
                <small class="text-muted">{{ __('My requests') }}</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="opac-card text-center p-3">
                <div class="h2 mb-1 text-warning">{{ $userStats['feedback_count'] }}</div>
                <small class="text-muted">{{ __('My feedback') }}</small>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Quick Actions --}}
        <div class="col-md-4">
            <div class="opac-card h-100">
                <div class="opac-card-header"><i class="fas fa-bolt"></i> {{ __('Quick Actions') }}</div>
                <div class="card-body opac-card-body d-grid gap-2">
                    <a href="{{ route('opac.search') }}" class="btn btn-opac-primary">
                        <i class="fas fa-search me-2"></i>{{ __('Search Catalog') }}
                    </a>
                    <a href="{{ route('opac.document-requests.create') }}" class="btn btn-opac-outline">
                        <i class="fas fa-file-alt me-2"></i>{{ __('New Document Request') }}
                    </a>
                    <a href="{{ route('opac.feedback.create') }}" class="btn btn-opac-outline">
                        <i class="fas fa-comment me-2"></i>{{ __('Send Feedback') }}
                    </a>
                    <a href="{{ route('opac.profile') }}" class="btn btn-opac-outline">
                        <i class="fas fa-user-edit me-2"></i>{{ __('Edit Profile') }}
                    </a>
                </div>
            </div>
        </div>

        {{-- My Recent Requests --}}
        <div class="col-md-4">
            <div class="opac-card h-100">
                <div class="opac-card-header"><i class="fas fa-file-alt"></i> {{ __('Recent Requests') }}</div>
                <div class="card-body opac-card-body p-0">
                    @forelse($myRequests as $req)
                        <div class="px-3 py-2 border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small fw-semibold">{{ Str::limit($req->reason, 40) }}</span>
                                <span class="badge bg-{{ match($req->status) {
                                    'pending'   => 'warning',
                                    'approved'  => 'success',
                                    'rejected'  => 'danger',
                                    default     => 'secondary'
                                } }} text-dark small">{{ ucfirst($req->status) }}</span>
                            </div>
                            <div class="text-muted" style="font-size:.78rem;">{{ $req->created_at->diffForHumans() }}</div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                            {{ __('No requests yet') }}
                        </div>
                    @endforelse
                    @if($myRequests->count())
                        <div class="p-2 text-center">
                            <a href="{{ route('opac.document-requests.index') }}" class="small text-primary">{{ __('View all') }}</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Latest News --}}
        <div class="col-md-4">
            <div class="opac-card h-100">
                <div class="opac-card-header"><i class="fas fa-newspaper"></i> {{ __('Latest News') }}</div>
                <div class="card-body opac-card-body p-0">
                    @forelse($recentNews as $news)
                        <div class="px-3 py-2 border-bottom">
                            <a href="{{ route('opac.news.show', $news) }}" class="small fw-semibold text-decoration-none text-dark">
                                {{ Str::limit($news->title, 50) }}
                            </a>
                            <div class="text-muted" style="font-size:.78rem;">{{ $news->published_at?->diffForHumans() }}</div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-newspaper fa-2x mb-2 d-block opacity-50"></i>
                            {{ __('No news available') }}
                        </div>
                    @endforelse
                    @if($recentNews->count())
                        <div class="p-2 text-center">
                            <a href="{{ route('opac.news.index') }}" class="small text-primary">{{ __('View all') }}</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Upcoming Events --}}
        @if($recentEvents->count())
        <div class="col-12">
            <div class="opac-card">
                <div class="opac-card-header"><i class="fas fa-calendar-alt"></i> {{ __('Upcoming Events') }}</div>
                <div class="card-body opac-card-body">
                    <div class="row g-3">
                        @foreach($recentEvents as $event)
                            <div class="col-md-4">
                                <div class="border rounded p-3">
                                    <div class="fw-semibold mb-1">{{ Str::limit($event->title, 50) }}</div>
                                    <div class="small text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        {{ $event->start_date?->format('d/m/Y') }}
                                    </div>
                                    <a href="{{ route('opac.events.show', $event) }}" class="btn btn-sm btn-opac-outline mt-2">
                                        {{ __('Details') }}
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
