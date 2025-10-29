@extends('layouts.app')

@section('title', __('OPAC Configuration'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">{{ __('OPAC Configuration') }}</h1>
                <div>
                    <a href="{{ route('admin.opac.preview') }}" class="btn btn-outline-primary" target="_blank">
                        <i class="fas fa-eye me-1"></i>{{ __('Preview OPAC') }}
                    </a>
                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="fas fa-upload me-1"></i>{{ __('Import Config') }}
                    </button>
                    <a href="{{ route('admin.opac.export') }}" class="btn btn-outline-success">
                        <i class="fas fa-download me-1"></i>{{ __('Export Config') }}
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.opac.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Navigation Tabs -->
                <ul class="nav nav-tabs mb-4" id="opacTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button">
                            <i class="fas fa-cog me-1"></i>{{ __('General Settings') }}
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="display-tab" data-bs-toggle="tab" data-bs-target="#display" type="button">
                            <i class="fas fa-eye me-1"></i>{{ __('Display Settings') }}
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="fields-tab" data-bs-toggle="tab" data-bs-target="#fields" type="button">
                            <i class="fas fa-list me-1"></i>{{ __('Field Configuration') }}
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="customization-tab" data-bs-toggle="tab" data-bs-target="#customization" type="button">
                            <i class="fas fa-paint-brush me-1"></i>{{ __('Customization') }}
                        </button>
                    </li>
                </ul>

                <div class="tab-content">
                    <!-- General Settings Tab -->
                    <div class="tab-pane fade show active" id="general" role="tabpanel">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">{{ __('Basic Configuration') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="site_name" class="form-label">{{ __('Site Name') }}</label>
                                            <input type="text" class="form-control @error('site_name') is-invalid @enderror"
                                                   id="site_name" name="site_name" value="{{ old('site_name', $config->site_name) }}" required>
                                            @error('site_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="site_description" class="form-label">{{ __('Site Description') }}</label>
                                            <textarea class="form-control @error('site_description') is-invalid @enderror"
                                                      id="site_description" name="site_description" rows="3">{{ old('site_description', $config->site_description) }}</textarea>
                                            @error('site_description')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="contact_email" class="form-label">{{ __('Contact Email') }}</label>
                                            <input type="email" class="form-control @error('contact_email') is-invalid @enderror"
                                                   id="contact_email" name="contact_email" value="{{ old('contact_email', $config->contact_email) }}">
                                            @error('contact_email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="items_per_page" class="form-label">{{ __('Items per Page') }}</label>
                                            <select class="form-select @error('items_per_page') is-invalid @enderror"
                                                    id="items_per_page" name="items_per_page">
                                                @foreach([10, 20, 30, 50, 100] as $value)
                                                    <option value="{{ $value }}"
                                                        {{ old('items_per_page', $config->items_per_page) == $value ? 'selected' : '' }}>
                                                        {{ $value }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('items_per_page')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="logo" class="form-label">{{ __('Logo') }}</label>
                                            @if($config->logo_path)
                                                <div class="mb-2">
                                                    <img src="{{ Storage::url($config->logo_path) }}" alt="Current Logo"
                                                         class="img-thumbnail" style="max-height: 100px;">
                                                </div>
                                            @endif
                                            <input type="file" class="form-control @error('logo') is-invalid @enderror"
                                                   id="logo" name="logo" accept="image/*">
                                            @error('logo')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">{{ __('Upload a new logo (max 2MB). Leave empty to keep current logo.') }}</div>
                                        </div>

                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="mb-0">{{ __('System Settings') }}</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-check mb-3">
                                                    <input class="form-check-input" type="checkbox" id="is_enabled"
                                                           name="is_enabled" value="1" {{ $config->is_enabled ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="is_enabled">
                                                        {{ __('Enable OPAC') }}
                                                    </label>
                                                    <div class="form-text">{{ __('When disabled, OPAC will show maintenance message') }}</div>
                                                </div>

                                                <div class="form-check mb-3">
                                                    <input class="form-check-input" type="checkbox" id="allow_downloads"
                                                           name="allow_downloads" value="1" {{ $config->allow_downloads ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="allow_downloads">
                                                        {{ __('Allow Downloads') }}
                                                    </label>
                                                </div>

                                                <div class="form-check mb-3">
                                                    <input class="form-check-input" type="checkbox" id="require_login_for_downloads"
                                                           name="require_login_for_downloads" value="1" {{ $config->require_login_for_downloads ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="require_login_for_downloads">
                                                        {{ __('Require Login for Downloads') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Display Settings Tab -->
                    <div class="tab-pane fade" id="display" role="tabpanel">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">{{ __('Display Configuration') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-4">
                                    <label for="footer_text" class="form-label">{{ __('Footer Text') }}</label>
                                    <textarea class="form-control @error('footer_text') is-invalid @enderror"
                                              id="footer_text" name="footer_text" rows="4"
                                              placeholder="{{ __('Custom footer text for OPAC pages') }}">{{ old('footer_text', $config->footer_text) }}</textarea>
                                    @error('footer_text')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">{{ __('Allowed File Types for Download') }}</label>
                                    <div class="row">
                                        @php
                                            $fileTypes = ['pdf', 'doc', 'docx', 'txt', 'rtf', 'odt', 'jpg', 'jpeg', 'png', 'gif', 'tiff', 'mp3', 'mp4', 'avi', 'zip', 'rar'];
                                            $allowedTypes = $config->allowed_file_types ?? [];
                                        @endphp
                                        @foreach($fileTypes as $type)
                                            <div class="col-md-3 col-sm-4 col-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                           name="allowed_file_types[]" value="{{ $type }}"
                                                           id="file_type_{{ $type }}"
                                                           {{ in_array($type, $allowedTypes) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="file_type_{{ $type }}">
                                                        .{{ $type }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fields Configuration Tab -->
                    <div class="tab-pane fade" id="fields" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">{{ __('Visible Record Fields') }}</h5>
                                    </div>
                                    <div class="card-body">
                                        @php
                                            $recordFields = [
                                                'title' => __('Title'),
                                                'description' => __('Description'),
                                                'date_creation' => __('Creation Date'),
                                                'date_debut' => __('Start Date'),
                                                'date_fin' => __('End Date'),
                                                'authors' => __('Authors'),
                                                'cote' => __('Reference Code'),
                                                'producteur' => __('Producer'),
                                                'service_versant' => __('Transferring Service'),
                                                'langue' => __('Language'),
                                                'support' => __('Support'),
                                                'format' => __('Format'),
                                                'statut' => __('Status')
                                            ];
                                            $visibleRecordFields = $config->visible_record_fields ?? [];
                                        @endphp
                                        @foreach($recordFields as $field => $label)
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox"
                                                       name="visible_record_fields[]" value="{{ $field }}"
                                                       id="record_field_{{ $field }}"
                                                       {{ in_array($field, $visibleRecordFields) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="record_field_{{ $field }}">
                                                    {{ $label }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">{{ __('Visible Activity Fields') }}</h5>
                                    </div>
                                    <div class="card-body">
                                        @php
                                            $activityFields = [
                                                'name' => __('Name'),
                                                'description' => __('Description'),
                                                'date_debut' => __('Start Date'),
                                                'date_fin' => __('End Date'),
                                                'niveau' => __('Level'),
                                                'processus' => __('Process'),
                                                'organisation_id' => __('Organisation')
                                            ];
                                            $visibleActivityFields = $config->visible_activity_fields ?? [];
                                        @endphp
                                        @foreach($activityFields as $field => $label)
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox"
                                                       name="visible_activity_fields[]" value="{{ $field }}"
                                                       id="activity_field_{{ $field }}"
                                                       {{ in_array($field, $visibleActivityFields) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="activity_field_{{ $field }}">
                                                    {{ $label }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h5 class="mb-0">{{ __('Searchable Fields') }}</h5>
                                    </div>
                                    <div class="card-body">
                                        @php
                                            $searchFields = [
                                                'title' => __('Title'),
                                                'description' => __('Description'),
                                                'content' => __('Content'),
                                                'authors' => __('Authors'),
                                                'cote' => __('Reference Code'),
                                                'producteur' => __('Producer')
                                            ];
                                            $searchableFields = $config->searchable_fields ?? [];
                                        @endphp
                                        @foreach($searchFields as $field => $label)
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox"
                                                       name="searchable_fields[]" value="{{ $field }}"
                                                       id="search_field_{{ $field }}"
                                                       {{ in_array($field, $searchableFields) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="search_field_{{ $field }}">
                                                    {{ $label }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customization Tab -->
                    <div class="tab-pane fade" id="customization" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">{{ __('Custom CSS') }}</h5>
                                    </div>
                                    <div class="card-body">
                                        <textarea class="form-control font-monospace @error('custom_css') is-invalid @enderror"
                                                  id="custom_css" name="custom_css" rows="15"
                                                  placeholder="/* {{ __('Add your custom CSS here') }} */">{{ old('custom_css', $config->custom_css) }}</textarea>
                                        @error('custom_css')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">{{ __('Custom styles for OPAC appearance') }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">{{ __('Custom JavaScript') }}</h5>
                                    </div>
                                    <div class="card-body">
                                        <textarea class="form-control font-monospace @error('custom_js') is-invalid @enderror"
                                                  id="custom_js" name="custom_js" rows="15"
                                                  placeholder="// {{ __('Add your custom JavaScript here') }}">{{ old('custom_js', $config->custom_js) }}</textarea>
                                        @error('custom_js')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">{{ __('Custom scripts for OPAC functionality') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#resetModal">
                        <i class="fas fa-undo me-1"></i>{{ __('Reset to Defaults') }}
                    </button>
                    <div>
                        <a href="{{ url()->previous() }}" class="btn btn-secondary me-2">
                            {{ __('Cancel') }}
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>{{ __('Save Configuration') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Import Configuration Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.opac.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Import Configuration') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="config_file" class="form-label">{{ __('Configuration File') }}</label>
                        <input type="file" class="form-control" id="config_file" name="config_file"
                               accept=".json" required>
                        <div class="form-text">{{ __('Select a JSON configuration file exported from this system') }}</div>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        {{ __('Importing will replace current configuration. Make sure to export current settings first.') }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Import') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reset Configuration Modal -->
<div class="modal fade" id="resetModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.opac.reset') }}">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title text-danger">{{ __('Reset Configuration') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        <strong>{{ __('Warning!') }}</strong>
                        {{ __('This action will reset all OPAC configuration to default values and cannot be undone.') }}
                    </div>
                    <p>{{ __('Are you sure you want to proceed?') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-danger">{{ __('Reset Configuration') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips if Bootstrap is available
    if (typeof bootstrap !== 'undefined') {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // Form validation enhancement
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            if (!isValid) {
                e.preventDefault();
                const firstInvalid = form.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.focus();
                }
            }
        });
    }

    // Auto-save indication
    let saveTimeout;
    const inputs = document.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            clearTimeout(saveTimeout);
            const saveBtn = document.querySelector('button[type="submit"]');
            if (saveBtn) {
                saveBtn.innerHTML = '<i class="fas fa-save me-1"></i>{{ __("Save Configuration") }} *';
                saveTimeout = setTimeout(() => {
                    saveBtn.innerHTML = '<i class="fas fa-save me-1"></i>{{ __("Save Configuration") }}';
                }, 2000);
            }
        });
    });
});
</script>
@endpush
