@extends('opac.layouts.app')

@section('title', __('Events') . ' - OPAC')

@section('content')
<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="mb-4">
                <h1 class="h3 mb-2">{{ __('Library Events') }}</h1>
                <p class="text-muted">{{ __('Discover upcoming events, workshops, and activities') }}</p>
            </div>

            <!-- Events placeholder -->
            <div class="opac-card text-center py-5">
                <div class="card-body">
                    <i class="fas fa-calendar fa-4x text-muted mb-4"></i>
                    <h4>{{ __('Events Calendar') }}</h4>
                    <p class="text-muted mb-4">
                        {{ __('This section will display upcoming library events, workshops, and special activities.') }}
                    </p>
                    <a href="{{ route('opac.search') }}" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>{{ __('Search Documents') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
