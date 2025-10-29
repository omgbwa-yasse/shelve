@extends('opac.layouts.app')

@section('title', $record->name . ' - OPAC')

@section('content')
<div class="container my-5">
    <div class="row">
        <div class="col-md-8">
            <!-- Document principal -->
            <div class="opac-record-detail">
                <h1 class="opac-record-title">{{ $record->name }}</h1>

                <!-- Métadonnées principales -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        @if($record->activity)
                            <div class="opac-field-label">{{ __('Category') }}</div>
                            <div class="opac-field-value">
                                <span class="opac-badge">{{ $record->activity->name }}</span>
                            </div>
                        @endif

                        @if($record->date_exact)
                            <div class="opac-field-label">{{ __('Date') }}</div>
                            <div class="opac-field-value">{{ $record->date_exact->format('Y-m-d') }}</div>
                        @endif

                        @if($record->authors->isNotEmpty())
                            <div class="opac-field-label">{{ __('Authors') }}</div>
                            <div class="opac-field-value">
                                @foreach($record->authors as $author)
                                    <span class="badge bg-light text-dark me-1">{{ $author->name }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        @if($record->level)
                            <div class="opac-field-label">{{ __('Level') }}</div>
                            <div class="opac-field-value">{{ $record->level->name }}</div>
                        @endif

                        @if($record->support)
                            <div class="opac-field-label">{{ __('Support') }}</div>
                            <div class="opac-field-value">{{ $record->support->name }}</div>
                        @endif

                        @if($record->status)
                            <div class="opac-field-label">{{ __('Status') }}</div>
                            <div class="opac-field-value">{{ $record->status->name }}</div>
                        @endif
                    </div>
                </div>

                <!-- Description complète -->
                @if($record->content)
                    <div class="mb-4">
                        <div class="opac-field-label">{{ __('Content') }}</div>
                        <div class="opac-field-value">
                            {!! nl2br(e($record->content)) !!}
                        </div>
                    </div>
                @endif

                @if($record->description)
                    <div class="mb-4">
                        <div class="opac-field-label">{{ __('Description') }}</div>
                        <div class="opac-field-value">
                            {!! nl2br(e($record->description)) !!}
                        </div>
                    </div>
                @endif

                <!-- Informations techniques -->
                @if($config->show_full_record_details)
                    <div class="row mb-4">
                        <div class="col-md-6">
                            @if($record->width)
                                <div class="opac-field-label">{{ __('Width') }}</div>
                                <div class="opac-field-value">{{ $record->width }}</div>
                            @endif

                            @if($record->width_description)
                                <div class="opac-field-label">{{ __('Width Description') }}</div>
                                <div class="opac-field-value">{{ $record->width_description }}</div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if($record->date_start || $record->date_end)
                                <div class="opac-field-label">{{ __('Date Range') }}</div>
                                <div class="opac-field-value">
                                    @if($record->date_start)
                                        {{ __('From') }}: {{ $record->date_start->format('Y-m-d') }}
                                    @endif
                                    @if($record->date_end)
                                        <br>{{ __('To') }}: {{ $record->date_end->format('Y-m-d') }}
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Documents liés -->
            @if($record->children->isNotEmpty())
                <div class="opac-card mt-4">
                    <div class="opac-card-header">
                        <i class="fas fa-sitemap me-2"></i>{{ __('Related Documents') }}
                    </div>
                    <div class="card-body">
                        @foreach($record->children as $child)
                            <div class="mb-2">
                                <a href="{{ route('opac.show', $child->id) }}" class="text-decoration-none">
                                    <i class="fas fa-file me-2"></i>{{ $child->name }}
                                </a>
                                @if($child->date_exact)
                                    <small class="text-muted ms-2">{{ $child->date_exact->format('Y-m-d') }}</small>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-4">
            <!-- Sidebar avec actions -->
            <div class="opac-card">
                <div class="opac-card-header">
                    <i class="fas fa-tools me-2"></i>{{ __('Actions') }}
                </div>
                <div class="card-body">
                    <!-- Retour à la recherche -->
                    <div class="d-grid gap-2 mb-3">
                        <a href="{{ route('opac.search') }}" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i>{{ __('Back to Search') }}
                        </a>

                        @if($record->activity)
                            <a href="{{ route('opac.browse', ['activity' => $record->activity->id]) }}" class="btn btn-outline-success">
                                <i class="fas fa-folder me-2"></i>{{ __('Browse Category') }}
                            </a>
                        @endif
                    </div>

                    <!-- Informations de référence -->
                    <hr>
                    <div class="small text-muted">
                        <div class="mb-2">
                            <strong>{{ __('Record ID') }}:</strong> {{ $record->id }}
                        </div>
                        <div class="mb-2">
                            <strong>{{ __('Added on') }}:</strong> {{ $record->created_at->format('Y-m-d') }}
                        </div>
                        @if($record->updated_at != $record->created_at)
                            <div class="mb-2">
                                <strong>{{ __('Last updated') }}:</strong> {{ $record->updated_at->format('Y-m-d') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Pièces jointes -->
            @if($config->show_attachments && $record->attachments->isNotEmpty())
                <div class="opac-card mt-4">
                    <div class="opac-card-header">
                        <i class="fas fa-paperclip me-2"></i>{{ __('Attachments') }} ({{ $record->attachments->count() }})
                    </div>
                    <div class="card-body">
                        @foreach($record->attachments as $attachment)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <i class="fas fa-file me-2"></i>
                                    <span class="text-truncate">{{ $attachment->name }}</span>
                                    @if($attachment->size)
                                        <small class="text-muted d-block">{{ number_format($attachment->size / 1024, 1) }} KB</small>
                                    @endif
                                </div>
                                @if($config->allow_downloads)
                                    <a href="{{ route('opac.download', [$record->id, $attachment->id]) }}"
                                       class="btn btn-sm btn-outline-primary"
                                       title="{{ __('Download') }}">
                                        <i class="fas fa-download"></i>
                                    </a>
                                @endif
                            </div>
                            @if(!$loop->last)<hr class="my-2">@endif
                        @endforeach

                        @if(!$config->allow_downloads)
                            <div class="alert alert-info small mt-3 mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                {{ __('Downloads are not available for public access. Contact us for more information.') }}
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Document parent -->
            @if($record->parent)
                <div class="opac-card mt-4">
                    <div class="opac-card-header">
                        <i class="fas fa-level-up-alt me-2"></i>{{ __('Parent Document') }}
                    </div>
                    <div class="card-body">
                        <a href="{{ route('opac.show', $record->parent->id) }}" class="text-decoration-none">
                            <i class="fas fa-file me-2"></i>{{ $record->parent->name }}
                        </a>
                        @if($record->parent->date_exact)
                            <div class="small text-muted mt-1">
                                {{ $record->parent->date_exact->format('Y-m-d') }}
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
});
</script>
@endpush
