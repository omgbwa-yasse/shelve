@extends('opac.layouts.app')

@section('title', __('Feedback Sent') . ' - OPAC')

@section('content')
<div class="container my-5" style="max-width:600px;">
    <div class="opac-card text-center p-5">
        <div class="mb-4">
            <span style="font-size:4rem; line-height:1;">✅</span>
        </div>
        <h2 class="mb-2">{{ __('Thank you!') }}</h2>
        <p class="text-muted mb-4">
            {{ __('Your feedback has been submitted successfully. We appreciate your input and will review it shortly.') }}
        </p>
        <div class="d-flex gap-2 justify-content-center flex-wrap">
            <a href="{{ route('opac.search') }}" class="btn btn-opac-primary">
                <i class="fas fa-search me-2"></i>{{ __('Search Catalog') }}
            </a>
            @auth('public')
                <a href="{{ route('opac.dashboard') }}" class="btn btn-opac-outline">
                    <i class="fas fa-home me-2"></i>{{ __('Dashboard') }}
                </a>
            @else
                <a href="{{ route('opac.index') }}" class="btn btn-opac-outline">
                    <i class="fas fa-home me-2"></i>{{ __('Home') }}
                </a>
            @endauth
        </div>
    </div>
</div>
@endsection
