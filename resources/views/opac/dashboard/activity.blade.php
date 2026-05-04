@extends('opac.layouts.app')

@section('title', __('My Activity') . ' - OPAC')

@section('content')
<div class="container my-5">
    <div class="mb-4 d-flex align-items-center gap-3">
        <a href="{{ route('opac.dashboard') }}" class="btn btn-sm btn-opac-outline">
            <i class="fas fa-arrow-left me-1"></i>{{ __('Dashboard') }}
        </a>
        <h1 class="h3 mb-0">{{ __('My Activity') }}</h1>
    </div>

    <div class="row g-4">
        {{-- Document Requests --}}
        <div class="col-12">
            <div class="opac-card">
                <div class="opac-card-header"><i class="fas fa-file-alt"></i> {{ __('Document Requests') }}</div>
                <div class="card-body opac-card-body p-0">
                    @forelse($activities['requests'] as $req)
                        <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
                            <div>
                                <div class="fw-semibold">{{ Str::limit($req->reason, 60) }}</div>
                                <div class="small text-muted">{{ ucfirst($req->request_type) }} &bull; {{ $req->created_at->format('d/m/Y') }}</div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-{{ match($req->status) {
                                    'pending'   => 'warning',
                                    'approved'  => 'success',
                                    'rejected'  => 'danger',
                                    'cancelled' => 'secondary',
                                    default     => 'secondary'
                                } }} text-dark">{{ ucfirst($req->status) }}</span>
                                <a href="{{ route('opac.document-requests.show', $req) }}" class="btn btn-sm btn-opac-outline">
                                    {{ __('View') }}
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-file-alt fa-3x mb-3 d-block opacity-25"></i>
                            {{ __('No document requests yet') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Feedback --}}
        <div class="col-12">
            <div class="opac-card">
                <div class="opac-card-header"><i class="fas fa-comment-dots"></i> {{ __('My Feedback') }}</div>
                <div class="card-body opac-card-body p-0">
                    @forelse($activities['feedback'] as $fb)
                        <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
                            <div>
                                <div class="fw-semibold">{{ Str::limit($fb->subject ?? $fb->title ?? '—', 60) }}</div>
                                <div class="small text-muted">{{ ucfirst($fb->type ?? '') }} &bull; {{ $fb->created_at->format('d/m/Y') }}</div>
                            </div>
                            <span class="badge bg-{{ match($fb->status ?? '') {
                                'pending'  => 'warning',
                                'resolved' => 'success',
                                default    => 'secondary'
                            } }} text-dark">{{ ucfirst($fb->status ?? 'pending') }}</span>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-comment-dots fa-3x mb-3 d-block opacity-25"></i>
                            {{ __('No feedback submitted yet') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
