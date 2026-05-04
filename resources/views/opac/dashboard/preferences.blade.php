@extends('opac.layouts.app')

@section('title', __('Preferences') . ' - OPAC')

@section('content')
<div class="container my-5" style="max-width:700px;">
    <div class="mb-4 d-flex align-items-center gap-3">
        <a href="{{ route('opac.dashboard') }}" class="btn btn-sm btn-opac-outline">
            <i class="fas fa-arrow-left me-1"></i>{{ __('Dashboard') }}
        </a>
        <h1 class="h3 mb-0">{{ __('My Preferences') }}</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <div class="opac-card">
        <div class="opac-card-header"><i class="fas fa-sliders-h"></i> {{ __('Settings') }}</div>
        <div class="card-body opac-card-body">
            <form method="POST" action="{{ route('opac.dashboard.preferences.update') }}">
                @csrf @method('PUT')

                <h6 class="fw-bold mb-3 border-bottom pb-2">{{ __('Notifications') }}</h6>
                <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="email_notifications" id="email_notifications" value="1"
                        {{ ($user->preferences['email_notifications'] ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="email_notifications">{{ __('Email notifications') }}</label>
                </div>
                <div class="mb-4 form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="newsletter_subscription" id="newsletter_subscription" value="1"
                        {{ ($user->preferences['newsletter_subscription'] ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="newsletter_subscription">{{ __('Newsletter subscription') }}</label>
                </div>

                <h6 class="fw-bold mb-3 border-bottom pb-2">{{ __('Search & Display') }}</h6>
                <div class="mb-3">
                    <label class="form-label">{{ __('Items per page') }}</label>
                    <select name="items_per_page" class="form-select">
                        @foreach(['10','20','50','100'] as $n)
                            <option value="{{ $n }}" {{ ($user->preferences['items_per_page'] ?? '20') == $n ? 'selected' : '' }}>{{ $n }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Default sort') }}</label>
                    <select name="default_sort" class="form-select">
                        <option value="relevance" {{ ($user->preferences['default_sort'] ?? 'relevance') == 'relevance' ? 'selected' : '' }}>{{ __('Relevance') }}</option>
                        <option value="title"     {{ ($user->preferences['default_sort'] ?? '') == 'title' ? 'selected' : '' }}>{{ __('Title') }}</option>
                        <option value="date_desc" {{ ($user->preferences['default_sort'] ?? '') == 'date_desc' ? 'selected' : '' }}>{{ __('Date (newest)') }}</option>
                        <option value="date_asc"  {{ ($user->preferences['default_sort'] ?? '') == 'date_asc' ? 'selected' : '' }}>{{ __('Date (oldest)') }}</option>
                    </select>
                </div>
                <div class="mb-4 form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="save_search_history" id="save_search_history" value="1"
                        {{ ($user->preferences['save_search_history'] ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="save_search_history">{{ __('Save search history') }}</label>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-opac-primary">
                        <i class="fas fa-save me-2"></i>{{ __('Save Preferences') }}
                    </button>
                    <a href="{{ route('opac.dashboard') }}" class="btn btn-opac-outline">{{ __('Cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
