@extends('opac.layouts.app')

@section('title', __('Preferences'))

@section('content')
<div class="container py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="bi bi-gear me-2"></i>
                        {{ __('Preferences') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Customize your library experience') }}</p>
                </div>
                <a href="{{ route('opac.dashboard') }}" class="btn btn-outline-primary">
                    <i class="bi bi-house me-1"></i>
                    {{ __('Back to Dashboard') }}
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Preferences Form -->
    <form action="{{ route('opac.dashboard.preferences.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Notifications Settings -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-bell me-2"></i>
                            {{ __('Notification Preferences') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   id="emailNotifications" name="email_notifications" value="1"
                                   {{ old('email_notifications', $user->preferences['email_notifications'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="emailNotifications">
                                <strong>{{ __('Email Notifications') }}</strong>
                                <br>
                                <small class="text-muted">{{ __('Receive notifications via email') }}</small>
                            </label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   id="smsNotifications" name="sms_notifications" value="1"
                                   {{ old('sms_notifications', $user->preferences['sms_notifications'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="smsNotifications">
                                <strong>{{ __('SMS Notifications') }}</strong>
                                <br>
                                <small class="text-muted">{{ __('Receive notifications via SMS') }}</small>
                            </label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   id="newsletterSubscription" name="newsletter_subscription" value="1"
                                   {{ old('newsletter_subscription', $user->preferences['newsletter_subscription'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="newsletterSubscription">
                                <strong>{{ __('Newsletter Subscription') }}</strong>
                                <br>
                                <small class="text-muted">{{ __('Receive library newsletter and updates') }}</small>
                            </label>
                        </div>

                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   id="eventReminders" name="event_reminders" value="1"
                                   {{ old('event_reminders', $user->preferences['event_reminders'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="eventReminders">
                                <strong>{{ __('Event Reminders') }}</strong>
                                <br>
                                <small class="text-muted">{{ __('Get reminded about upcoming events you\'ve registered for') }}</small>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Display Settings -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-display me-2"></i>
                            {{ __('Display Preferences') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="language" class="form-label">
                                <strong>{{ __('Language') }}</strong>
                            </label>
                            <select class="form-select" id="language" name="language">
                                <option value="fr" {{ old('language', $user->preferences['language'] ?? 'fr') == 'fr' ? 'selected' : '' }}>
                                    🇫🇷 Français
                                </option>
                                <option value="en" {{ old('language', $user->preferences['language'] ?? 'fr') == 'en' ? 'selected' : '' }}>
                                    🇺🇸 English
                                </option>
                            </select>
                            <div class="form-text">{{ __('Choose your preferred language for the interface') }}</div>
                        </div>

                        <div class="mb-3">
                            <label for="theme" class="form-label">
                                <strong>{{ __('Theme') }}</strong>
                            </label>
                            <select class="form-select" id="theme" name="theme">
                                <option value="auto" {{ old('theme', $user->preferences['theme'] ?? 'auto') == 'auto' ? 'selected' : '' }}>
                                    🌓 {{ __('Auto (System)') }}
                                </option>
                                <option value="light" {{ old('theme', $user->preferences['theme'] ?? 'auto') == 'light' ? 'selected' : '' }}>
                                    ☀️ {{ __('Light') }}
                                </option>
                                <option value="dark" {{ old('theme', $user->preferences['theme'] ?? 'auto') == 'dark' ? 'selected' : '' }}>
                                    🌙 {{ __('Dark') }}
                                </option>
                            </select>
                            <div class="form-text">{{ __('Choose your preferred color theme') }}</div>
                        </div>

                        <div class="mb-3">
                            <label for="timezone" class="form-label">
                                <strong>{{ __('Timezone') }}</strong>
                            </label>
                            <select class="form-select" id="timezone" name="timezone">
                                <option value="Europe/Paris" {{ old('timezone', $user->preferences['timezone'] ?? 'Europe/Paris') == 'Europe/Paris' ? 'selected' : '' }}>
                                    Europe/Paris (GMT+1)
                                </option>
                                <option value="UTC" {{ old('timezone', $user->preferences['timezone'] ?? 'Europe/Paris') == 'UTC' ? 'selected' : '' }}>
                                    UTC (GMT+0)
                                </option>
                                <option value="America/New_York" {{ old('timezone', $user->preferences['timezone'] ?? 'Europe/Paris') == 'America/New_York' ? 'selected' : '' }}>
                                    America/New_York (GMT-5)
                                </option>
                            </select>
                            <div class="form-text">{{ __('Your local timezone for accurate time displays') }}</div>
                        </div>

                        <div class="mb-0">
                            <label for="itemsPerPage" class="form-label">
                                <strong>{{ __('Items per Page') }}</strong>
                            </label>
                            <select class="form-select" id="itemsPerPage" name="items_per_page">
                                <option value="10" {{ old('items_per_page', $user->preferences['items_per_page'] ?? '20') == '10' ? 'selected' : '' }}>
                                    10
                                </option>
                                <option value="20" {{ old('items_per_page', $user->preferences['items_per_page'] ?? '20') == '20' ? 'selected' : '' }}>
                                    20
                                </option>
                                <option value="50" {{ old('items_per_page', $user->preferences['items_per_page'] ?? '20') == '50' ? 'selected' : '' }}>
                                    50
                                </option>
                                <option value="100" {{ old('items_per_page', $user->preferences['items_per_page'] ?? '20') == '100' ? 'selected' : '' }}>
                                    100
                                </option>
                            </select>
                            <div class="form-text">{{ __('Number of items to display per page in search results') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search Settings -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-search me-2"></i>
                            {{ __('Search Preferences') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   id="saveSearchHistory" name="save_search_history" value="1"
                                   {{ old('save_search_history', $user->preferences['save_search_history'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="saveSearchHistory">
                                <strong>{{ __('Save Search History') }}</strong>
                                <br>
                                <small class="text-muted">{{ __('Keep track of your previous searches') }}</small>
                            </label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   id="autoSuggestions" name="auto_suggestions" value="1"
                                   {{ old('auto_suggestions', $user->preferences['auto_suggestions'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="autoSuggestions">
                                <strong>{{ __('Auto Suggestions') }}</strong>
                                <br>
                                <small class="text-muted">{{ __('Show search suggestions as you type') }}</small>
                            </label>
                        </div>

                        <div class="mb-3">
                            <label for="defaultSort" class="form-label">
                                <strong>{{ __('Default Sort Order') }}</strong>
                            </label>
                            <select class="form-select" id="defaultSort" name="default_sort">
                                <option value="relevance" {{ old('default_sort', $user->preferences['default_sort'] ?? 'relevance') == 'relevance' ? 'selected' : '' }}>
                                    {{ __('Relevance') }}
                                </option>
                                <option value="title" {{ old('default_sort', $user->preferences['default_sort'] ?? 'relevance') == 'title' ? 'selected' : '' }}>
                                    {{ __('Title A-Z') }}
                                </option>
                                <option value="date_desc" {{ old('default_sort', $user->preferences['default_sort'] ?? 'relevance') == 'date_desc' ? 'selected' : '' }}>
                                    {{ __('Newest First') }}
                                </option>
                                <option value="date_asc" {{ old('default_sort', $user->preferences['default_sort'] ?? 'relevance') == 'date_asc' ? 'selected' : '' }}>
                                    {{ __('Oldest First') }}
                                </option>
                            </select>
                        </div>

                        <div class="mb-0">
                            <label for="searchResultsView" class="form-label">
                                <strong>{{ __('Default Results View') }}</strong>
                            </label>
                            <select class="form-select" id="searchResultsView" name="search_results_view">
                                <option value="list" {{ old('search_results_view', $user->preferences['search_results_view'] ?? 'list') == 'list' ? 'selected' : '' }}>
                                    📋 {{ __('List View') }}
                                </option>
                                <option value="grid" {{ old('search_results_view', $user->preferences['search_results_view'] ?? 'list') == 'grid' ? 'selected' : '' }}>
                                    🔲 {{ __('Grid View') }}
                                </option>
                                <option value="compact" {{ old('search_results_view', $user->preferences['search_results_view'] ?? 'list') == 'compact' ? 'selected' : '' }}>
                                    📄 {{ __('Compact View') }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Privacy Settings -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-shield-lock me-2"></i>
                            {{ __('Privacy & Security') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   id="profilePublic" name="profile_public" value="1"
                                   {{ old('profile_public', $user->preferences['profile_public'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="profilePublic">
                                <strong>{{ __('Public Profile') }}</strong>
                                <br>
                                <small class="text-muted">{{ __('Make your profile visible to other library users') }}</small>
                            </label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   id="showActivity" name="show_activity" value="1"
                                   {{ old('show_activity', $user->preferences['show_activity'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="showActivity">
                                <strong>{{ __('Show Activity') }}</strong>
                                <br>
                                <small class="text-muted">{{ __('Display your recent activity to other users') }}</small>
                            </label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   id="twoFactorAuth" name="two_factor_auth" value="1"
                                   {{ old('two_factor_auth', $user->preferences['two_factor_auth'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="twoFactorAuth">
                                <strong>{{ __('Two-Factor Authentication') }}</strong>
                                <br>
                                <small class="text-muted">{{ __('Enable 2FA for enhanced security') }}</small>
                            </label>
                        </div>

                        <hr>

                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-warning" onclick="exportUserData()">
                                <i class="bi bi-download me-1"></i>
                                {{ __('Download My Data') }}
                            </button>
                            <button type="button" class="btn btn-outline-danger" onclick="confirmDeleteAccount()">
                                <i class="bi bi-trash me-1"></i>
                                {{ __('Delete Account') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <button type="submit" class="btn btn-primary btn-lg px-5">
                            <i class="bi bi-check-lg me-2"></i>
                            {{ __('Save Preferences') }}
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-lg px-5 ms-3" onclick="resetToDefaults()">
                            <i class="bi bi-arrow-counterclockwise me-2"></i>
                            {{ __('Reset to Defaults') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function exportUserData() {
    if (confirm('{{ __("Do you want to download all your personal data?") }}')) {
        alert('{{ __("Data export functionality coming soon!") }}');
    }
}

function confirmDeleteAccount() {
    if (confirm('{{ __("Are you sure you want to delete your account? This action is irreversible.") }}')) {
        if (confirm('{{ __("This will permanently delete all your data. Are you absolutely sure?") }}')) {
            alert('{{ __("Account deletion functionality coming soon!") }}');
        }
    }
}

function resetToDefaults() {
    if (confirm('{{ __("Reset all preferences to default values?") }}')) {
        // Reset form to defaults
        document.getElementById('emailNotifications').checked = true;
        document.getElementById('smsNotifications').checked = false;
        document.getElementById('newsletterSubscription').checked = true;
        document.getElementById('eventReminders').checked = true;

        document.getElementById('language').value = 'fr';
        document.getElementById('theme').value = 'auto';
        document.getElementById('timezone').value = 'Europe/Paris';
        document.getElementById('itemsPerPage').value = '20';

        document.getElementById('saveSearchHistory').checked = true;
        document.getElementById('autoSuggestions').checked = true;
        document.getElementById('defaultSort').value = 'relevance';
        document.getElementById('searchResultsView').value = 'list';

        document.getElementById('profilePublic').checked = false;
        document.getElementById('showActivity').checked = false;
        document.getElementById('twoFactorAuth').checked = false;

        alert('{{ __("Preferences reset to defaults. Don\'t forget to save!") }}');
    }
}

// Theme preview
document.getElementById('theme').addEventListener('change', function() {
    const theme = this.value;
    const body = document.body;

    // Remove existing theme classes
    body.classList.remove('theme-light', 'theme-dark', 'theme-auto');

    if (theme !== 'auto') {
        body.classList.add('theme-' + theme);
    }
});

// Language change preview
document.getElementById('language').addEventListener('change', function() {
    const lang = this.value;
    // In a real app, you might show a preview or reload the page with new language
    console.log('Language changed to:', lang);
});
</script>
@endpush

@push('styles')
<style>
.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.form-switch .form-check-input {
    width: 2em;
    height: 1em;
}

.card-header {
    border-bottom: 2px solid rgba(255,255,255,0.2);
}

.form-check-label strong {
    color: #495057;
}

.theme-dark {
    background-color: #121212 !important;
    color: #ffffff !important;
}

.theme-dark .card {
    background-color: #1e1e1e !important;
    color: #ffffff !important;
}

.theme-dark .form-control,
.theme-dark .form-select {
    background-color: #2d2d2d !important;
    border-color: #404040 !important;
    color: #ffffff !important;
}

@media (max-width: 768px) {
    .btn-lg.px-5 {
        width: 100%;
        margin: 0.5rem 0 !important;
    }
}
</style>
@endpush
