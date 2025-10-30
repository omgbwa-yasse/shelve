@extends('opac.layouts.app')

@section('title', __('Pages') . ' - OPAC')

@section('content')
<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="mb-4">
                <h1 class="h3 mb-2">{{ __('Information Pages') }}</h1>
                <p class="text-muted">{{ __('Access important information and documentation') }}</p>
            </div>

            <!-- Pages placeholder -->
            <div class="opac-card text-center py-5">
                <div class="card-body">
                    <i class="fas fa-file-alt fa-4x text-muted mb-4"></i>
                    <h4>{{ __('Information Pages') }}</h4>
                    <p class="text-muted mb-4">
                        {{ __('This section will contain important pages such as policies, guides, and help documentation.') }}
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
