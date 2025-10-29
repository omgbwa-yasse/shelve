@extends('opac.layouts.app')

@section('title', __('New Document Request'))

@section('content')
<div class="container py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">
            <i class="bi bi-file-earmark-plus text-primary"></i>
            {{ __('New Document Request') }}
        </h1>
        <a href="{{ route('opac.document-requests.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
            {{ __('Back to My Requests') }}
        </a>
    </div>

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

    <form action="{{ route('opac.document-requests.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row">
            <div class="col-lg-8">
                <!-- Document Information -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-book"></i>
                            {{ __('Document Information') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="document_type" class="form-label">{{ __('Document Type') }} <span class="text-danger">*</span></label>
                                <select class="form-select" id="document_type" name="document_type" required>
                                    <option value="">{{ __('Select type...') }}</option>
                                    <option value="book" {{ old('document_type') == 'book' ? 'selected' : '' }}>{{ __('Book') }}</option>
                                    <option value="article" {{ old('document_type') == 'article' ? 'selected' : '' }}>{{ __('Article') }}</option>
                                    <option value="thesis" {{ old('document_type') == 'thesis' ? 'selected' : '' }}>{{ __('Thesis') }}</option>
                                    <option value="report" {{ old('document_type') == 'report' ? 'selected' : '' }}>{{ __('Report') }}</option>
                                    <option value="multimedia" {{ old('document_type') == 'multimedia' ? 'selected' : '' }}>{{ __('Multimedia') }}</option>
                                    <option value="other" {{ old('document_type') == 'other' ? 'selected' : '' }}>{{ __('Other') }}</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="urgency" class="form-label">{{ __('Priority Level') }}</label>
                                <select class="form-select" id="urgency" name="urgency">
                                    <option value="normal" {{ old('urgency', 'normal') == 'normal' ? 'selected' : '' }}>{{ __('Normal') }}</option>
                                    <option value="high" {{ old('urgency') == 'high' ? 'selected' : '' }}>{{ __('High') }}</option>
                                    <option value="urgent" {{ old('urgency') == 'urgent' ? 'selected' : '' }}>{{ __('Urgent') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="title" class="form-label">{{ __('Title') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title"
                                   value="{{ old('title') }}" required
                                   placeholder="{{ __('Enter the title of the document...') }}">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="author" class="form-label">{{ __('Author') }}</label>
                                <input type="text" class="form-control" id="author" name="author"
                                       value="{{ old('author') }}"
                                       placeholder="{{ __('Author name...') }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="publication_year" class="form-label">{{ __('Publication Year') }}</label>
                                <input type="number" class="form-control" id="publication_year"
                                       name="publication_year" value="{{ old('publication_year') }}"
                                       min="1900" max="{{ date('Y') + 1 }}"
                                       placeholder="{{ __('e.g., 2023') }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="publisher" class="form-label">{{ __('Publisher') }}</label>
                                <input type="text" class="form-control" id="publisher" name="publisher"
                                       value="{{ old('publisher') }}"
                                       placeholder="{{ __('Publisher name...') }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="isbn" class="form-label">{{ __('ISBN/ISSN') }}</label>
                                <input type="text" class="form-control" id="isbn" name="isbn"
                                       value="{{ old('isbn') }}"
                                       placeholder="{{ __('ISBN or ISSN number...') }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">{{ __('Additional Description') }}</label>
                            <textarea class="form-control" id="description" name="description"
                                      rows="3" placeholder="{{ __('Provide any additional details about the document...') }}">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Request Details -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-clipboard-check"></i>
                            {{ __('Request Details') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="purpose" class="form-label">{{ __('Purpose of Request') }} <span class="text-danger">*</span></label>
                            <select class="form-select" id="purpose" name="purpose" required>
                                <option value="">{{ __('Select purpose...') }}</option>
                                <option value="research" {{ old('purpose') == 'research' ? 'selected' : '' }}>{{ __('Research') }}</option>
                                <option value="education" {{ old('purpose') == 'education' ? 'selected' : '' }}>{{ __('Education') }}</option>
                                <option value="personal" {{ old('purpose') == 'personal' ? 'selected' : '' }}>{{ __('Personal Interest') }}</option>
                                <option value="professional" {{ old('purpose') == 'professional' ? 'selected' : '' }}>{{ __('Professional') }}</option>
                                <option value="other" {{ old('purpose') == 'other' ? 'selected' : '' }}>{{ __('Other') }}</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">{{ __('Notes for Librarian') }}</label>
                            <textarea class="form-control" id="notes" name="notes"
                                      rows="3" placeholder="{{ __('Any special instructions or additional information...') }}">{{ old('notes') }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="needed_date" class="form-label">{{ __('Date Needed By') }}</label>
                                <input type="date" class="form-control" id="needed_date"
                                       name="needed_date" value="{{ old('needed_date') }}"
                                       min="{{ date('Y-m-d') }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="delivery_method" class="form-label">{{ __('Preferred Delivery Method') }}</label>
                                <select class="form-select" id="delivery_method" name="delivery_method">
                                    <option value="pickup" {{ old('delivery_method', 'pickup') == 'pickup' ? 'selected' : '' }}>{{ __('Library Pickup') }}</option>
                                    <option value="email" {{ old('delivery_method') == 'email' ? 'selected' : '' }}>{{ __('Email (if digital)') }}</option>
                                    <option value="mail" {{ old('delivery_method') == 'mail' ? 'selected' : '' }}>{{ __('Postal Mail') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="attachments" class="form-label">{{ __('Attachments') }}</label>
                            <input type="file" class="form-control" id="attachments"
                                   name="attachments[]" multiple
                                   accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif">
                            <div class="form-text">
                                {{ __('You can attach related documents (PDF, DOC, images). Max 5MB per file.') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('opac.document-requests.index') }}" class="btn btn-secondary">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i>
                        {{ __('Submit Request') }}
                    </button>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Help Information -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-info-circle"></i>
                            {{ __('Request Guidelines') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success"></i>
                                {{ __('Provide as much detail as possible') }}
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success"></i>
                                {{ __('Check our catalog first') }}
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success"></i>
                                {{ __('Allow 3-5 business days') }}
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success"></i>
                                {{ __('Include ISBN when available') }}
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Quick Search -->
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">
                            <i class="bi bi-search"></i>
                            {{ __('Search First') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            {{ __('Before submitting a request, search our catalog to see if the item is already available.') }}
                        </p>
                        <a href="{{ route('opac.search') }}" class="btn btn-warning w-100" target="_blank">
                            <i class="bi bi-search"></i>
                            {{ __('Search Catalog') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-fill current date if urgent priority is selected
    const urgencySelect = document.getElementById('urgency');
    const neededDateInput = document.getElementById('needed_date');

    urgencySelect.addEventListener('change', function() {
        if (this.value === 'urgent' && !neededDateInput.value) {
            const today = new Date();
            const nextWeek = new Date(today.setDate(today.getDate() + 7));
            neededDateInput.value = nextWeek.toISOString().split('T')[0];
        }
    });

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

    // Form validation enhancement
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const title = document.getElementById('title').value.trim();
        const documentType = document.getElementById('document_type').value;
        const purpose = document.getElementById('purpose').value;

        if (!title || !documentType || !purpose) {
            e.preventDefault();
            alert('{{ __("Please fill in all required fields.") }}');
        }
    });
});
</script>
@endpush
