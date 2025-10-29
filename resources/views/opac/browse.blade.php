@extends('opac.layouts.app')

@section('title', __('Browse Collections') . ' - OPAC')

@section('content')
<div class="container my-5">
    <div class="row">
        <div class="col-md-3">
            <!-- Navigation par catégories -->
            <div class="opac-card">
                <div class="opac-card-header">
                    <i class="fas fa-sitemap me-2"></i>{{ __('Categories') }}
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('opac.browse') }}"
                           class="list-group-item list-group-item-action {{ !request('activity') ? 'active' : '' }}">
                            <i class="fas fa-home me-2"></i>{{ __('All Collections') }}
                        </a>
                        @foreach($rootActivities as $activity)
                            <a href="{{ route('opac.browse', ['activity' => $activity->id]) }}"
                               class="list-group-item list-group-item-action {{ request('activity') == $activity->id ? 'active' : '' }}">
                                <i class="fas fa-folder me-2"></i>{{ $activity->name }}
                                @if($activity->children->count() > 0)
                                    <span class="badge bg-secondary float-end">{{ $activity->children->count() }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            @if($currentActivity)
                <!-- Affichage d'une catégorie spécifique -->
                <div class="mb-4">
                    <h2>{{ $currentActivity->name }}</h2>
                    @if($currentActivity->description)
                        <p class="text-muted">{{ $currentActivity->description }}</p>
                    @endif
                </div>

                <!-- Sous-catégories -->
                @if($subActivities->isNotEmpty())
                    <div class="mb-5">
                        <h4 class="mb-3">{{ __('Subcategories') }}</h4>
                        <div class="row">
                            @foreach($subActivities as $subActivity)
                                <div class="col-md-4 mb-3">
                                    <div class="opac-card">
                                        <div class="card-body text-center">
                                            <i class="fas fa-folder-open fa-2x text-primary mb-2"></i>
                                            <h6 class="card-title">{{ $subActivity->name }}</h6>
                                            @if($subActivity->description)
                                                <p class="card-text small text-muted">
                                                    {{ Str::limit($subActivity->description, 80) }}
                                                </p>
                                            @endif
                                            <a href="{{ route('opac.browse', ['activity' => $subActivity->id]) }}"
                                               class="btn btn-sm btn-outline-primary">
                                                {{ __('Browse') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Documents de cette catégorie -->
                @if($records->isNotEmpty())
                    <div class="mb-4">
                        <h4>{{ __('Documents') }} ({{ $records->total() }})</h4>
                    </div>

                    @foreach($records as $record)
                        <div class="opac-card mb-3">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-9">
                                        <h5 class="card-title">
                                            <a href="{{ route('opac.show', $record->id) }}" class="text-decoration-none">
                                                {{ $record->name }}
                                            </a>
                                        </h5>

                                        @if($record->authors->isNotEmpty())
                                            <p class="text-muted small mb-2">
                                                <i class="fas fa-user me-1"></i>
                                                {{ $record->authors->pluck('name')->implode(', ') }}
                                            </p>
                                        @endif

                                        @if($record->date_exact)
                                            <p class="text-muted small mb-2">
                                                <i class="fas fa-calendar me-1"></i>{{ $record->date_exact->format('Y-m-d') }}
                                            </p>
                                        @endif

                                        <p class="card-text">
                                            {{ Str::limit($record->content ?: $record->description, 150) }}
                                        </p>
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <a href="{{ route('opac.show', $record->id) }}" class="btn btn-outline-primary">
                                            {{ __('View Details') }}
                                        </a>
                                        @if($record->attachments->count() > 0)
                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-paperclip me-1"></i>
                                                    {{ $record->attachments->count() }} {{ __('attachment(s)') }}
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $records->appends(request()->query())->links() }}
                    </div>
                @elseif($subActivities->isEmpty())
                    <!-- Aucun document dans cette catégorie -->
                    <div class="opac-card text-center">
                        <div class="card-body py-5">
                            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                            <h4>{{ __('No documents found') }}</h4>
                            <p class="text-muted">{{ __('This category does not contain any documents yet.') }}</p>
                            <a href="{{ route('opac.browse') }}" class="btn btn-primary">
                                {{ __('Browse All Collections') }}
                            </a>
                        </div>
                    </div>
                @endif
            @else
                <!-- Vue d'ensemble de toutes les catégories -->
                <div class="mb-4">
                    <h2>{{ __('Browse Collections') }}</h2>
                    <p class="text-muted">{{ __('Explore our document collections organized by categories') }}</p>
                </div>

                <div class="row">
                    @foreach($rootActivities as $activity)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="opac-card h-100">
                                <div class="card-body">
                                    <div class="text-center mb-3">
                                        <i class="fas fa-folder fa-3x text-primary"></i>
                                    </div>
                                    <h5 class="card-title text-center">{{ $activity->name }}</h5>
                                    @if($activity->description)
                                        <p class="card-text text-center">
                                            {{ Str::limit($activity->description, 100) }}
                                        </p>
                                    @endif

                                    <div class="text-center mt-3">
                                        <!-- Statistiques rapides -->
                                        <div class="row text-center">
                                            <div class="col-6">
                                                <div class="small text-muted">{{ __('Documents') }}</div>
                                                <strong>{{ $activity->records->count() }}</strong>
                                            </div>
                                            <div class="col-6">
                                                <div class="small text-muted">{{ __('Subcategories') }}</div>
                                                <strong>{{ $activity->children->count() }}</strong>
                                            </div>
                                        </div>

                                        <a href="{{ route('opac.browse', ['activity' => $activity->id]) }}"
                                           class="btn btn-primary mt-3">
                                            {{ __('Explore Collection') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($rootActivities->isEmpty())
                    <!-- Aucune collection disponible -->
                    <div class="opac-card text-center">
                        <div class="card-body py-5">
                            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                            <h4>{{ __('No collections available') }}</h4>
                            <p class="text-muted">{{ __('Collections are being prepared and will be available soon.') }}</p>
                            <a href="{{ route('opac.search') }}" class="btn btn-primary">
                                {{ __('Try Searching Instead') }}
                            </a>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
