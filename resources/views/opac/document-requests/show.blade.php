@extends('opac.layouts.app')

@section('title', __('Request Details') . ' - OPAC')

@section('content')
<div class="container my-5" style="max-width:750px;">
    <div class="mb-4 d-flex align-items-center gap-3">
        <a href="{{ route('opac.document-requests.index') }}" class="btn btn-sm btn-opac-outline">
            <i class="fas fa-arrow-left me-1"></i>{{ __('My Requests') }}
        </a>
        <h1 class="h3 mb-0">{{ __('Request Details') }}</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mb-4"><i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}</div>
    @endif

    <div class="opac-card mb-4">
        <div class="opac-card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-file-alt me-2"></i>{{ __('Request #') }}{{ $documentRequest->id }}</span>
            <span class="badge bg-{{ match($documentRequest->status) {
                'pending'   => 'warning',
                'approved'  => 'success',
                'rejected'  => 'danger',
                'cancelled' => 'secondary',
                default     => 'secondary'
            } }} text-dark fs-6">{{ ucfirst($documentRequest->status) }}</span>
        </div>
        <div class="card-body opac-card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="text-muted small mb-1">{{ __('Type') }}</div>
                    <div class="fw-semibold">{{ ucfirst($documentRequest->request_type) }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small mb-1">{{ __('Submitted') }}</div>
                    <div class="fw-semibold">{{ $documentRequest->created_at->format('d/m/Y H:i') }}</div>
                </div>
                @if($documentRequest->processed_at)
                <div class="col-md-6">
                    <div class="text-muted small mb-1">{{ __('Processed') }}</div>
                    <div class="fw-semibold">{{ $documentRequest->processed_at->format('d/m/Y H:i') }}</div>
                </div>
                @endif
                <div class="col-12">
                    <div class="text-muted small mb-1">{{ __('Description / Reason') }}</div>
                    <div class="p-3 bg-light rounded">{{ $documentRequest->reason }}</div>
                </div>
                @if($documentRequest->admin_notes)
                <div class="col-12">
                    <div class="text-muted small mb-1">{{ __('Admin Notes') }}</div>
                    <div class="p-3 bg-light rounded border-start border-4 border-info">{{ $documentRequest->admin_notes }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Actions --}}
    @if($documentRequest->status === 'pending')
        <div class="d-flex gap-2">
            <a href="{{ route('opac.document-requests.edit', $documentRequest) }}" class="btn btn-opac-outline">
                <i class="fas fa-edit me-2"></i>{{ __('Edit') }}
            </a>
            <form method="POST" action="{{ route('opac.document-requests.cancel', $documentRequest) }}"
                  onsubmit="return confirm('{{ __('Cancel this request?') }}')">
                @csrf
                <button type="submit" class="btn btn-outline-danger">
                    <i class="fas fa-times me-2"></i>{{ __('Cancel Request') }}
                </button>
            </form>
        </div>
    @endif
</div>
@endsection
