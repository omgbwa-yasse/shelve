@extends('layouts.opac')

@section('title', __('Send Feedback'))

@section('content')
<div class="container py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2">
                <i class="bi bi-chat-square-heart text-primary"></i>
                {{ __('Send Feedback') }}
            </h1>
            <p class="text-muted mb-0">{{ __('Your opinion matters to us. Help us improve our services.') }}</p>
        </div>

        @auth('public')
            <a href="{{ route('opac.feedback.my-feedback') }}" class="btn btn-outline-primary">
                <i class="bi bi-clock-history"></i>
                {{ __('My Feedback History') }}
            </a>
        @endauth
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>{{ __('Please correct the following errors:') }}</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <form action="{{ route('opac.feedback.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Feedback Type -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-tag"></i>
                            {{ __('Feedback Type') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label">{{ __('Category') }} <span class="text-danger">*</span></label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">{{ __('Select category...') }}</option>
                                    <option value="suggestion" {{ old('category') == 'suggestion' ? 'selected' : '' }}>
                                        {{ __('Suggestion') }}
                                    </option>
                                    <option value="complaint" {{ old('category') == 'complaint' ? 'selected' : '' }}>
                                        {{ __('Complaint') }}
                                    </option>
                                    <option value="compliment" {{ old('category') == 'compliment' ? 'selected' : '' }}>
                                        {{ __('Compliment') }}
                                    </option>
                                    <option value="technical_issue" {{ old('category') == 'technical_issue' ? 'selected' : '' }}>
                                        {{ __('Technical Issue') }}
                                    </option>
                                    <option value="service_request" {{ old('category') == 'service_request' ? 'selected' : '' }}>
                                        {{ __('Service Request') }}
                                    </option>
                                    <option value="other" {{ old('category') == 'other' ? 'selected' : '' }}>
                                        {{ __('Other') }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="priority" class="form-label">{{ __('Priority') }}</label>
                                <select class="form-select" id="priority" name="priority">
                                    <option value="low" {{ old('priority', 'low') == 'low' ? 'selected' : '' }}>
                                        {{ __('Low') }}
                                    </option>
                                    <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>
                                        {{ __('Medium') }}
                                    </option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>
                                        {{ __('High') }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="subject" class="form-label">{{ __('Subject') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="subject" name="subject"
                                   value="{{ old('subject') }}" required
                                   placeholder="{{ __('Brief summary of your feedback...') }}">
                        </div>
                    </div>
                </div>

                <!-- Feedback Content -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-chat-text"></i>
                            {{ __('Your Feedback') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="message" class="form-label">{{ __('Message') }} <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="message" name="message"
                                      rows="6" required
                                      placeholder="{{ __('Please provide detailed information about your feedback...') }}">{{ old('message') }}</textarea>
                            <div class="form-text">
                                {{ __('Minimum 10 characters required. Please be as specific as possible.') }}
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="location" class="form-label">{{ __('Related Service/Location') }}</label>
                                <select class="form-select" id="location" name="location">
                                    <option value="">{{ __('Select if applicable...') }}</option>
                                    <option value="circulation" {{ old('location') == 'circulation' ? 'selected' : '' }}>
                                        {{ __('Circulation Desk') }}
                                    </option>
                                    <option value="reference" {{ old('location') == 'reference' ? 'selected' : '' }}>
                                        {{ __('Reference Section') }}
                                    </option>
                                    <option value="reading_room" {{ old('location') == 'reading_room' ? 'selected' : '' }}>
                                        {{ __('Reading Room') }}
                                    </option>
                                    <option value="online_catalog" {{ old('location') == 'online_catalog' ? 'selected' : '' }}>
                                        {{ __('Online Catalog') }}
                                    </option>
                                    <option value="website" {{ old('location') == 'website' ? 'selected' : '' }}>
                                        {{ __('Website') }}
                                    </option>
                                    <option value="other" {{ old('location') == 'other' ? 'selected' : '' }}>
                                        {{ __('Other') }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="rating" class="form-label">{{ __('Overall Rating') }}</label>
                                <div class="rating-container">
                                    <div class="d-flex align-items-center">
                                        <div class="star-rating" data-rating="{{ old('rating', 0) }}">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="bi bi-star star" data-value="{{ $i }}"></i>
                                            @endfor
                                        </div>
                                        <span class="ms-2 rating-text">{{ __('Click to rate') }}</span>
                                    </div>
                                    <input type="hidden" name="rating" id="rating-input" value="{{ old('rating') }}">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="attachments" class="form-label">{{ __('Attachments') }}</label>
                            <input type="file" class="form-control" id="attachments"
                                   name="attachments[]" multiple
                                   accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif">
                            <div class="form-text">
                                {{ __('Optional: Attach screenshots or documents to support your feedback. Max 5MB per file.') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                @guest('public')
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-person-circle"></i>
                            {{ __('Contact Information') }}
                            <small>({{ __('Optional') }})</small>
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">
                            {{ __('Provide your contact information if you would like a response to your feedback.') }}
                        </p>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="contact_name" class="form-label">{{ __('Your Name') }}</label>
                                <input type="text" class="form-control" id="contact_name"
                                       name="contact_name" value="{{ old('contact_name') }}"
                                       placeholder="{{ __('Your full name...') }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="contact_email" class="form-label">{{ __('Email Address') }}</label>
                                <input type="email" class="form-control" id="contact_email"
                                       name="contact_email" value="{{ old('contact_email') }}"
                                       placeholder="{{ __('your.email@example.com') }}">
                            </div>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="wants_response"
                                   name="wants_response" value="1"
                                   {{ old('wants_response') ? 'checked' : '' }}>
                            <label class="form-check-label" for="wants_response">
                                {{ __('I would like to receive a response to this feedback') }}
                            </label>
                        </div>
                    </div>
                </div>
                @endguest

                <!-- Privacy Notice -->
                <div class="alert alert-info">
                    <i class="bi bi-shield-check"></i>
                    <strong>{{ __('Privacy Notice:') }}</strong>
                    {{ __('Your feedback will be reviewed by our team. Personal information will only be used to respond to your feedback if requested and will not be shared with third parties.') }}
                </div>

                <!-- Form Actions -->
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ url()->previous() }}" class="btn btn-secondary">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i>
                        {{ __('Send Feedback') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Tips -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="bi bi-lightbulb"></i>
                        {{ __('Tips for Effective Feedback') }}
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success"></i>
                            {{ __('Be specific about the issue') }}
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success"></i>
                            {{ __('Include relevant details') }}
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success"></i>
                            {{ __('Suggest improvements if possible') }}
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success"></i>
                            {{ __('Be respectful and constructive') }}
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Alternative Contact Methods -->
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-telephone"></i>
                        {{ __('Other Ways to Contact Us') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>{{ __('Phone:') }}</strong>
                        <br>
                        <span class="text-muted">+1 (555) 123-4567</span>
                    </div>
                    <div class="mb-3">
                        <strong>{{ __('Email:') }}</strong>
                        <br>
                        <span class="text-muted">feedback@library.com</span>
                    </div>
                    <div class="mb-0">
                        <strong>{{ __('Visit Us:') }}</strong>
                        <br>
                        <span class="text-muted">
                            Main Library<br>
                            123 Library Street<br>
                            City, State 12345
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Star rating functionality
    const stars = document.querySelectorAll('.star');
    const ratingInput = document.getElementById('rating-input');
    const ratingText = document.querySelector('.rating-text');

    const ratingTexts = {
        1: '{{ __("Poor") }}',
        2: '{{ __("Fair") }}',
        3: '{{ __("Good") }}',
        4: '{{ __("Very Good") }}',
        5: '{{ __("Excellent") }}'
    };

    // Initialize rating if there's an old value
    const initialRating = parseInt(ratingInput.value);
    if (initialRating > 0) {
        updateStars(initialRating);
        ratingText.textContent = ratingTexts[initialRating];
    }

    stars.forEach(star => {
        star.addEventListener('click', function() {
            const rating = parseInt(this.dataset.value);
            ratingInput.value = rating;
            updateStars(rating);
            ratingText.textContent = ratingTexts[rating];
        });

        star.addEventListener('mouseenter', function() {
            const rating = parseInt(this.dataset.value);
            highlightStars(rating);
        });
    });

    document.querySelector('.star-rating').addEventListener('mouseleave', function() {
        const currentRating = parseInt(ratingInput.value);
        updateStars(currentRating);
    });

    function updateStars(rating) {
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.remove('bi-star');
                star.classList.add('bi-star-fill', 'text-warning');
            } else {
                star.classList.remove('bi-star-fill', 'text-warning');
                star.classList.add('bi-star');
            }
        });
    }

    function highlightStars(rating) {
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.remove('bi-star');
                star.classList.add('bi-star-fill', 'text-warning');
            } else {
                star.classList.remove('bi-star-fill', 'text-warning');
                star.classList.add('bi-star');
            }
        });
    }

    // File upload validation
    const fileInput = document.getElementById('attachments');
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const files = Array.from(this.files);
            const maxSize = 5 * 1024 * 1024; // 5MB

            files.forEach(file => {
                if (file.size > maxSize) {
                    alert(`File "${file.name}" is too large. Maximum size is 5MB.`);
                    this.value = '';
                    return;
                }
            });
        });
    }

    // Form validation
    const form = document.querySelector('form');
    const messageTextarea = document.getElementById('message');

    form.addEventListener('submit', function(e) {
        const message = messageTextarea.value.trim();

        if (message.length < 10) {
            e.preventDefault();
            alert('{{ __("Please provide at least 10 characters in your message.") }}');
            messageTextarea.focus();
        }
    });

    // Character counter for message
    if (messageTextarea) {
        const counter = document.createElement('div');
        counter.className = 'form-text text-end';
        counter.id = 'message-counter';
        messageTextarea.parentNode.appendChild(counter);

        function updateCounter() {
            const length = messageTextarea.value.length;
            counter.textContent = `${length} {{ __('characters') }}`;

            if (length < 10) {
                counter.className = 'form-text text-end text-danger';
            } else {
                counter.className = 'form-text text-end text-muted';
            }
        }

        messageTextarea.addEventListener('input', updateCounter);
        updateCounter(); // Initial count
    }
});
</script>
@endpush

@push('styles')
<style>
.star {
    font-size: 1.5rem;
    cursor: pointer;
    color: #ddd;
    transition: color 0.2s;
}

.star:hover {
    color: #ffc107;
}

.star.bi-star-fill {
    color: #ffc107;
}

.rating-container {
    user-select: none;
}

.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.alert-info {
    border-left: 4px solid #0dcaf0;
}
</style>
@endpush
