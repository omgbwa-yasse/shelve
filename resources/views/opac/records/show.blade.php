@extends('opac.layouts.app')

@section('title', $record->title)

@section('content')
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('opac.index') }}">{{ __('Home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('opac.records.index') }}">{{ __('Records') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $record->title }}</li>
        </ol>
    </nav>

    <!-- Record Details -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h1 class="h2 mb-3">{{ $record->title }}</h1>

                    @if($record->record->code)
                    <div class="mb-3">
                        <strong>{{ __('Reference Code') }}:</strong>
                        <span class="text-muted">{{ $record->record->code }}</span>
                    </div>
                    @endif

                    @if($record->formatted_date_range)
                    <div class="mb-3">
                        <strong>{{ __('Date') }}:</strong>
                        <span class="text-muted">{{ $record->formatted_date_range }}</span>
                    </div>
                    @endif

                    @if($record->content)
                    <div class="mb-4">
                        <h3 class="h5">{{ __('Description') }}</h3>
                        <div class="text-muted">{!! nl2br(e($record->content)) !!}</div>
                    </div>
                    @endif

                    @if($record->biographical_history)
                    <div class="mb-4">
                        <h3 class="h5">{{ __('Biographical History') }}</h3>
                        <div class="text-muted">{!! nl2br(e($record->biographical_history)) !!}</div>
                    </div>
                    @endif

                    @if($record->access_conditions)
                    <div class="mb-4">
                        <h3 class="h5">{{ __('Access Conditions') }}</h3>
                        <div class="text-muted">{!! nl2br(e($record->access_conditions)) !!}</div>
                    </div>
                    @endif

                    @if($record->language_material)
                    <div class="mb-3">
                        <strong>{{ __('Language') }}:</strong>
                        <span class="text-muted">{{ $record->language_material }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Record Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h3 class="h6 mb-0">{{ __('Record Information') }}</h3>
                </div>
                <div class="card-body">
                    @if($record->published_at)
                    <div class="mb-2">
                        <strong>{{ __('Published on') }}:</strong><br>
                        <small class="text-muted">{{ $record->published_at->format('d/m/Y H:i') }}</small>
                    </div>
                    @endif

                    @if($record->expires_at)
                    <div class="mb-2">
                        <strong>{{ __('Available until') }}:</strong><br>
                        <small class="text-muted">{{ $record->expires_at->format('d/m/Y') }}</small>
                    </div>
                    @endif

                    @if($record->publisher)
                    <div class="mb-2">
                        <strong>{{ __('Published by') }}:</strong><br>
                        <small class="text-muted">{{ $record->publisher->name }}</small>
                    </div>
                    @endif

                    @if($record->publication_notes)
                    <div class="mb-2">
                        <strong>{{ __('Notes') }}:</strong><br>
                        <small class="text-muted">{{ $record->publication_notes }}</small>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <a href="{{ route('opac.records.index') }}" class="btn btn-outline-primary w-100 mb-2">
                        <i class="bi bi-arrow-left me-2"></i>{{ __('Back to search') }}
                    </a>

                    @if($record->attachments && $record->attachments->count() > 0)
                    <div class="mt-3">
                        <h6>{{ __('Attachments') }}</h6>
                        @foreach($record->attachments as $attachment)
                        <a href="#" class="btn btn-sm btn-outline-secondary w-100 mb-1">
                            <i class="bi bi-file-earmark me-2"></i>{{ $attachment->name }}
                        </a>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Related Records -->
    @if($relatedRecords && $relatedRecords->count() > 0)
    <div class="row mt-5">
        <div class="col-12">
            <h3 class="h4 mb-4">{{ __('Related Records') }}</h3>
            <div class="row">
                @foreach($relatedRecords as $related)
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">{{ $related->title }}</h5>
                            <p class="card-text text-muted">
                                {{ Str::limit($related->content, 100) }}
                            </p>
                            <a href="{{ route('opac.records.show', $related->id) }}" class="btn btn-sm btn-outline-primary">
                                {{ __('View Details') }}
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Any JavaScript for record view functionality can go here
});
</script>
@endpush
