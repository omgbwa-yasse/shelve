@extends('opac.layouts.app')

@section('title', __('Quick Actions'))

@section('content')
<div class="container py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="bi bi-lightning-charge me-2"></i>
                        {{ __('Quick Actions') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Frequently used actions for quick access') }}</p>
                </div>
                <a href="{{ route('opac.dashboard') }}" class="btn btn-outline-primary">
                    <i class="bi bi-house me-1"></i>
                    {{ __('Back to Dashboard') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Action Categories -->
    <div class="row">
        <!-- Search Actions -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-search me-2"></i>
                        {{ __('Search & Discovery') }}
                    </h5>
                </div>
                <div class="card-body d-flex flex-column">
                    <div class="d-grid gap-2 flex-grow-1">
                        <a href="{{ route('opac.search') }}" class="btn btn-outline-primary">
                            <i class="bi bi-search me-2"></i>
                            {{ __('Advanced Search') }}
                        </a>
                        <a href="{{ route('opac.records.index') }}" class="btn btn-outline-primary">
                            <i class="bi bi-collection me-2"></i>
                            {{ __('Browse All Documents') }}
                        </a>
                        <a href="{{ route('opac.search.history') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-clock-history me-2"></i>
                            {{ __('Search History') }}
                        </a>
                        <a href="{{ route('opac.browse') }}" class="btn btn-outline-info">
                            <i class="bi bi-grid-3x3-gap me-2"></i>
                            {{ __('Browse by Category') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Document Actions -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-file-earmark-text me-2"></i>
                        {{ __('Document Services') }}
                    </h5>
                </div>
                <div class="card-body d-flex flex-column">
                    <div class="d-grid gap-2 flex-grow-1">
                        <a href="{{ route('opac.document-requests.create') }}" class="btn btn-outline-success">
                            <i class="bi bi-file-earmark-plus me-2"></i>
                            {{ __('Request Document') }}
                        </a>
                        <a href="{{ route('opac.document-requests.index') }}" class="btn btn-outline-success">
                            <i class="bi bi-list-check me-2"></i>
                            {{ __('My Requests') }}
                        </a>
                        <a href="{{ route('opac.reservations') }}" class="btn btn-outline-warning">
                            <i class="bi bi-bookmark me-2"></i>
                            {{ __('My Reservations') }}
                        </a>
                        <button class="btn btn-outline-secondary" onclick="openReservationModal()">
                            <i class="bi bi-calendar-plus me-2"></i>
                            {{ __('New Reservation') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Actions -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-person-gear me-2"></i>
                        {{ __('Account Management') }}
                    </h5>
                </div>
                <div class="card-body d-flex flex-column">
                    <div class="d-grid gap-2 flex-grow-1">
                        <a href="{{ route('opac.profile') }}" class="btn btn-outline-info">
                            <i class="bi bi-person-circle me-2"></i>
                            {{ __('Update Profile') }}
                        </a>
                        <a href="{{ route('opac.dashboard.preferences') }}" class="btn btn-outline-info">
                            <i class="bi bi-gear me-2"></i>
                            {{ __('Preferences') }}
                        </a>
                        <a href="{{ route('opac.dashboard.activity') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-activity me-2"></i>
                            {{ __('Activity History') }}
                        </a>
                        <button class="btn btn-outline-danger" onclick="changePassword()">
                            <i class="bi bi-shield-lock me-2"></i>
                            {{ __('Change Password') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Communication Actions -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-chat-dots me-2"></i>
                        {{ __('Communication') }}
                    </h5>
                </div>
                <div class="card-body d-flex flex-column">
                    <div class="d-grid gap-2 flex-grow-1">
                        <a href="{{ route('opac.feedback.create') }}" class="btn btn-outline-warning">
                            <i class="bi bi-chat-square-dots me-2"></i>
                            {{ __('Send Feedback') }}
                        </a>
                        <a href="{{ route('opac.feedback.my-feedback') }}" class="btn btn-outline-warning">
                            <i class="bi bi-chat-square-text me-2"></i>
                            {{ __('My Feedback') }}
                        </a>
                        <button class="btn btn-outline-secondary" onclick="contactLibrarian()">
                            <i class="bi bi-person-badge me-2"></i>
                            {{ __('Contact Librarian') }}
                        </button>
                        <a href="{{ route('opac.help') }}" class="btn btn-outline-info">
                            <i class="bi bi-question-circle me-2"></i>
                            {{ __('Help & FAQ') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- News & Events -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-newspaper me-2"></i>
                        {{ __('News & Events') }}
                    </h5>
                </div>
                <div class="card-body d-flex flex-column">
                    <div class="d-grid gap-2 flex-grow-1">
                        <a href="{{ route('opac.news.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-newspaper me-2"></i>
                            {{ __('Latest News') }}
                        </a>
                        <a href="{{ route('opac.events.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-calendar-event me-2"></i>
                            {{ __('Upcoming Events') }}
                        </a>
                        <button class="btn btn-outline-info" onclick="subscribeNewsletter()">
                            <i class="bi bi-envelope-plus me-2"></i>
                            {{ __('Newsletter') }}
                        </button>
                        <a href="{{ route('opac.pages.index') }}" class="btn btn-outline-dark">
                            <i class="bi bi-file-text me-2"></i>
                            {{ __('Library Pages') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Tools -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-tools me-2"></i>
                        {{ __('Quick Tools') }}
                    </h5>
                </div>
                <div class="card-body d-flex flex-column">
                    <div class="d-grid gap-2 flex-grow-1">
                        <button class="btn btn-outline-dark" onclick="printPage()">
                            <i class="bi bi-printer me-2"></i>
                            {{ __('Print This Page') }}
                        </button>
                        <button class="btn btn-outline-dark" onclick="exportData()">
                            <i class="bi bi-download me-2"></i>
                            {{ __('Export My Data') }}
                        </button>
                        <button class="btn btn-outline-secondary" onclick="shareProfile()">
                            <i class="bi bi-share me-2"></i>
                            {{ __('Share Profile') }}
                        </button>
                        <button class="btn btn-outline-info" onclick="keyboardShortcuts()">
                            <i class="bi bi-keyboard me-2"></i>
                            {{ __('Keyboard Shortcuts') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Search Bar -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-lightning-fill me-2"></i>
                        {{ __('Quick Search') }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('opac.search') }}" method="GET" class="row g-3">
                        <div class="col-md-8">
                            <input type="text" name="q" class="form-control form-control-lg"
                                   placeholder="{{ __('Search documents, authors, subjects...') }}"
                                   value="{{ request('q') }}">
                        </div>
                        <div class="col-md-2">
                            <select name="type" class="form-select form-select-lg">
                                <option value="">{{ __('All Types') }}</option>
                                <option value="document">{{ __('Documents') }}</option>
                                <option value="author">{{ __('Authors') }}</option>
                                <option value="subject">{{ __('Subjects') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-search me-1"></i>
                                {{ __('Search') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<!-- Reservation Modal -->
<div class="modal fade" id="reservationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Quick Reservation') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="quickReservationForm">
                    <div class="mb-3">
                        <label for="documentCode" class="form-label">{{ __('Document Code') }}</label>
                        <input type="text" class="form-control" id="documentCode"
                               placeholder="{{ __('Enter document code...') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="reservationNotes" class="form-label">{{ __('Notes (Optional)') }}</label>
                        <textarea class="form-control" id="reservationNotes" rows="3"
                                  placeholder="{{ __('Add any special notes...') }}"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-primary" onclick="submitQuickReservation()">{{ __('Reserve') }}</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openReservationModal() {
    new bootstrap.Modal(document.getElementById('reservationModal')).show();
}

function submitQuickReservation() {
    const form = document.getElementById('quickReservationForm');
    const documentCode = document.getElementById('documentCode').value;
    const notes = document.getElementById('reservationNotes').value;

    if (!documentCode.trim()) {
        alert('{{ __("Please enter a document code") }}');
        return;
    }

    // Here you would submit the reservation via AJAX
    alert('{{ __("Reservation functionality coming soon!") }}');
    bootstrap.Modal.getInstance(document.getElementById('reservationModal')).hide();
}

function changePassword() {
    alert('{{ __("Password change functionality coming soon!") }}');
}

function contactLibrarian() {
    alert('{{ __("Contact librarian functionality coming soon!") }}');
}

function subscribeNewsletter() {
    alert('{{ __("Newsletter subscription functionality coming soon!") }}');
}

function printPage() {
    window.print();
}

function exportData() {
    alert('{{ __("Data export functionality coming soon!") }}');
}

function shareProfile() {
    if (navigator.share) {
        navigator.share({
            title: '{{ __("My Library Profile") }}',
            text: '{{ __("Check out my library profile!") }}',
            url: window.location.href
        });
    } else {
        // Fallback for browsers that don't support Web Share API
        const url = window.location.href;
        navigator.clipboard.writeText(url).then(() => {
            alert('{{ __("Profile URL copied to clipboard!") }}');
        });
    }
}

function keyboardShortcuts() {
    const shortcuts = `
    {{ __("Keyboard Shortcuts:") }}

    Ctrl + K - {{ __("Quick Search") }}
    Ctrl + D - {{ __("Dashboard") }}
    Ctrl + H - {{ __("Search History") }}
    Ctrl + R - {{ __("New Reservation") }}
    Ctrl + F - {{ __("Send Feedback") }}
    Esc - {{ __("Close Modals") }}
    `;
    alert(shortcuts);
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey) {
        switch(e.key) {
            case 'k':
                e.preventDefault();
                document.querySelector('input[name="q"]').focus();
                break;
            case 'd':
                e.preventDefault();
                window.location.href = '{{ route("opac.dashboard") }}';
                break;
            case 'h':
                e.preventDefault();
                window.location.href = '{{ route("opac.search.history") }}';
                break;
            case 'r':
                e.preventDefault();
                openReservationModal();
                break;
            case 'f':
                e.preventDefault();
                window.location.href = '{{ route("opac.feedback.create") }}';
                break;
        }
    }
});

$(document).ready(function() {
    // Auto-focus search input
    setTimeout(() => {
        document.querySelector('input[name="q"]').focus();
    }, 500);
});
</script>
@endpush

@push('styles')
<style>
.card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
}

.btn {
    transition: all 0.2s;
}

.btn:hover {
    transform: translateY(-1px);
}

.form-control-lg, .form-select-lg {
    height: calc(3.5rem + 2px);
}

@media (max-width: 768px) {
    .card-body .d-grid {
        gap: 1rem !important;
    }
}
</style>
@endpush
