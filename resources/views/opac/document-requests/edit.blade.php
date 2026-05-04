@extends('opac.layouts.app')

@section('title', __('Edit Request') . ' - OPAC')

@section('content')
<div class="container my-5" style="max-width:700px;">
    <div class="mb-4 d-flex align-items-center gap-3">
        <a href="{{ route('opac.document-requests.show', $documentRequest) }}" class="btn btn-sm btn-opac-outline">
            <i class="fas fa-arrow-left me-1"></i>{{ __('Back') }}
        </a>
        <h1 class="h3 mb-0">{{ __('Edit Request') }}</h1>
    </div>

    <div class="opac-card">
        <div class="opac-card-header"><i class="fas fa-edit"></i> {{ __('Update Request Details') }}</div>
        <div class="card-body opac-card-body">
            <form method="POST" action="{{ route('opac.document-requests.update', $documentRequest) }}">
                @csrf @method('PUT')

                <div class="mb-3">
                    <label for="request_type" class="form-label fw-semibold">{{ __('Request Type') }} <span class="text-danger">*</span></label>
                    <select name="request_type" id="request_type" class="form-select @error('request_type') is-invalid @enderror" required>
                        <option value="consultation" {{ (old('request_type', $documentRequest->request_type) == 'consultation') ? 'selected' : '' }}>{{ __('Consultation (on-site)') }}</option>
                        <option value="copy"         {{ (old('request_type', $documentRequest->request_type) == 'copy')         ? 'selected' : '' }}>{{ __('Copy / Reproduction') }}</option>
                        <option value="loan"         {{ (old('request_type', $documentRequest->request_type) == 'loan')         ? 'selected' : '' }}>{{ __('Loan') }}</option>
                    </select>
                    @error('request_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label for="reason" class="form-label fw-semibold">{{ __('Description / Reason') }} <span class="text-danger">*</span></label>
                    <textarea name="reason" id="reason" rows="5" class="form-control @error('reason') is-invalid @enderror" required>{{ old('reason', $documentRequest->reason) }}</textarea>
                    @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-opac-primary">
                        <i class="fas fa-save me-2"></i>{{ __('Update Request') }}
                    </button>
                    <a href="{{ route('opac.document-requests.show', $documentRequest) }}" class="btn btn-opac-outline">{{ __('Cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
