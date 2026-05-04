@extends('opac.layouts.app')

@section('title', __('Feedback Details') . ' - OPAC')

@section('content')
<div class="container my-5" style="max-width:700px;">
    <div class="mb-4 d-flex align-items-center gap-3">
        <a href="{{ route('opac.feedback.my-feedback') }}" class="btn btn-sm btn-opac-outline">
            <i class="fas fa-arrow-left me-1"></i>{{ __('My Feedback') }}
        </a>
        <h1 class="h3 mb-0">{{ __('Feedback Details') }}</h1>
    </div>

    <div class="opac-card mb-4">
        <div class="opac-card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-comment me-2"></i>{{ $feedback->subject ?? $feedback->title ?? __('Feedback') }}</span>
            <span class="badge bg-{{ match($feedback->status ?? '') {
                'resolved' => 'success',
                'pending'  => 'warning',
                default    => 'secondary'
            } }} text-dark fs-6">{{ ucfirst($feedback->status ?? 'pending') }}</span>
        </div>
        <div class="card-body opac-card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="text-muted small mb-1">{{ __('Type') }}</div>
                    <div class="fw-semibold">{{ ucfirst($feedback->type ?? '—') }}</div>
                </div>
                @if($feedback->rating)
                    <div class="col-md-6">
                        <div class="text-muted small mb-1">{{ __('Rating') }}</div>
                        <div class="fw-semibold">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star {{ $i <= $feedback->rating ? 'text-warning' : 'text-muted opacity-25' }}"></i>
                            @endfor
                        </div>
                    </div>
                @endif
                <div class="col-md-6">
                    <div class="text-muted small mb-1">{{ __('Submitted') }}</div>
                    <div class="fw-semibold">{{ $feedback->created_at->format('d/m/Y H:i') }}</div>
                </div>
                <div class="col-12">
                    <div class="text-muted small mb-1">{{ __('Message') }}</div>
                    <div class="p-3 bg-light rounded">{{ $feedback->content ?? $feedback->message ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Comments / Responses --}}
    @if($feedback->comments && $feedback->comments->count())
        <div class="opac-card">
            <div class="opac-card-header"><i class="fas fa-reply"></i> {{ __('Responses') }}</div>
            <div class="card-body opac-card-body p-0">
                @foreach($feedback->comments as $comment)
                    <div class="px-4 py-3 border-bottom">
                        <div class="fw-semibold small mb-1 text-muted">{{ $comment->created_at->format('d/m/Y H:i') }}</div>
                        <div>{{ $comment->content }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
