@extends('opac.layouts.app')

@section('title', __('Feedback') . ' - OPAC')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="opac-card">
                <div class="opac-card-header text-center">
                    <i class="fas fa-comment-dots me-2"></i>{{ __('Send Feedback') }}
                </div>
                <div class="card-body p-4">
                    <p class="text-muted mb-4">
                        {{ __('We value your feedback! Help us improve our services by sharing your thoughts, suggestions, or reporting issues.') }}
                    </p>

                    <form method="POST" action="{{ route('opac.feedback.store') }}">
                        @csrf

                        <!-- Name (for non-authenticated users) -->
                        @guest('public')
                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('Your Name') }}</label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   value="{{ old('name') }}"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Email (for non-authenticated users) -->
                        <div class="mb-3">
                            <label for="email" class="form-label">{{ __('Your Email') }}</label>
                            <input type="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   id="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   required>
                            @error('email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        @endguest

                        <!-- Category -->
                        <div class="mb-3">
                            <label for="category" class="form-label">{{ __('Feedback Category') }}</label>
                            <select class="form-select @error('category') is-invalid @enderror"
                                    id="category"
                                    name="category"
                                    required>
                                <option value="">{{ __('Select a category') }}</option>
                                <option value="suggestion" {{ old('category') == 'suggestion' ? 'selected' : '' }}>{{ __('Suggestion') }}</option>
                                <option value="bug_report" {{ old('category') == 'bug_report' ? 'selected' : '' }}>{{ __('Bug Report') }}</option>
                                <option value="content_issue" {{ old('category') == 'content_issue' ? 'selected' : '' }}>{{ __('Content Issue') }}</option>
                                <option value="search_problem" {{ old('category') == 'search_problem' ? 'selected' : '' }}>{{ __('Search Problem') }}</option>
                                <option value="general" {{ old('category') == 'general' ? 'selected' : '' }}>{{ __('General Feedback') }}</option>
                                <option value="other" {{ old('category') == 'other' ? 'selected' : '' }}>{{ __('Other') }}</option>
                            </select>
                            @error('category')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Subject -->
                        <div class="mb-3">
                            <label for="subject" class="form-label">{{ __('Subject') }}</label>
                            <input type="text"
                                   class="form-control @error('subject') is-invalid @enderror"
                                   id="subject"
                                   name="subject"
                                   value="{{ old('subject') }}"
                                   placeholder="{{ __('Brief description of your feedback') }}"
                                   required>
                            @error('subject')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Message -->
                        <div class="mb-3">
                            <label for="message" class="form-label">{{ __('Message') }}</label>
                            <textarea class="form-control @error('message') is-invalid @enderror"
                                      id="message"
                                      name="message"
                                      rows="5"
                                      placeholder="{{ __('Please provide details about your feedback...') }}"
                                      required>{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Priority (optional) -->
                        <div class="mb-3">
                            <label for="priority" class="form-label">{{ __('Priority') }} <small class="text-muted">({{ __('optional') }})</small></label>
                            <select class="form-select" id="priority" name="priority">
                                <option value="">{{ __('Select priority level') }}</option>
                                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>{{ __('Low') }}</option>
                                <option value="normal" {{ old('priority') == 'normal' ? 'selected' : '' }}>{{ __('Normal') }}</option>
                                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>{{ __('High') }}</option>
                                <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>{{ __('Urgent') }}</option>
                            </select>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid">
                            <button type="submit" class="btn opac-search-btn">
                                <i class="fas fa-paper-plane me-2"></i>{{ __('Send Feedback') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="opac-card mt-4">
                <div class="card-body text-center">
                    <h6 class="mb-3">{{ __('Other Ways to Contact Us') }}</h6>
                    <div class="row text-center">
                        <div class="col-md-4 mb-2">
                            <i class="fas fa-phone text-primary mb-2 d-block"></i>
                            <small class="text-muted">{{ __('Phone Support') }}</small>
                        </div>
                        <div class="col-md-4 mb-2">
                            <i class="fas fa-envelope text-primary mb-2 d-block"></i>
                            <small class="text-muted">{{ __('Email Support') }}</small>
                        </div>
                        <div class="col-md-4 mb-2">
                            <i class="fas fa-map-marker-alt text-primary mb-2 d-block"></i>
                            <small class="text-muted">{{ __('Visit Us') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
