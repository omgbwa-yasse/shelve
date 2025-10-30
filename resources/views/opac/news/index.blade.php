@extends('opac.layouts.app')

@section('title', __('News') . ' - OPAC')

@section('content')
<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="mb-4">
                <h1 class="h3 mb-2">{{ __('Library News & Updates') }}</h1>
                <p class="text-muted">{{ __('Stay informed about the latest news, events, and announcements') }}</p>
            </div>

            <!-- News placeholder -->
            <div class="opac-card text-center py-5">
                <div class="card-body">
                    <i class="fas fa-newspaper fa-4x text-muted mb-4"></i>
                    <h4>{{ __('News Section') }}</h4>
                    <p class="text-muted mb-4">
                        {{ __('This section is being prepared and will contain the latest library news and updates.') }}
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
