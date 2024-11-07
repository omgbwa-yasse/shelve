@extends('layouts.app')

@section('content')
    <div class=" ">
        <div class="row justify-content-center">
            <div class="">
                <div >
                    <!-- Card Header -->
                    <div class="card-header  text-white">
                        <h4 class="mb-0">Import Slips</h4>
                    </div>

                    <!-- Card Body -->
                    <div class="card-body">
                        <!-- Alert for Errors -->
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>Error!</strong> Please check the form and try again.
                                <ul class="mb-0 mt-2">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <!-- Success Message -->
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <!-- Import Form -->
                        <form id="importForm" method="POST" enctype="multipart/form-data">
                            @csrf

                            <!-- Format Selection -->
                            <div class="mb-4">
                                <label for="format" class="form-label">Import Format <span class="text-danger">*</span></label>
                                <select id="format" name="format" class="form-select" required>
                                    <option value="">Select a format...</option>
                                    <option value="excel">Excel (.xlsx)</option>
                                    <option value="ead">EAD (.xml)</option>
                                    <option value="seda">SEDA (.xml)</option>
                                </select>
                                <div class="form-text" id="format-help">
                                    Choose the format of your import file
                                </div>
                            </div>

                            <!-- File Upload -->
                            <div class="mb-4">
                                <label for="file" class="form-label">Upload File <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="file" class="form-control" id="file" name="file" required>
                                </div>
                                <div class="form-text" id="file-help">
                                    Supported formats: Excel (.xlsx) or XML (.xml)
                                </div>
                            </div>

                            <!-- Preview Area -->
                            <div class="mb-4">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted">Selected File</h6>
                                        <p class="card-text" id="selected-file">No file selected</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('slips.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle me-2"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-upload me-2"></i>Import Data
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Card Footer -->
                    <div class="card-footer bg-light">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Make sure your file matches the selected format before importing
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('importForm');
            const formatSelect = document.getElementById('format');
            const fileInput = document.getElementById('file');
            const selectedFileText = document.getElementById('selected-file');
            const fileHelp = document.getElementById('file-help');
            const formatHelp = document.getElementById('format-help');

            // Update form action when format changes
            function updateFormAction(format) {
                if (format) {
                    form.action = `/transferrings/slips/import/${format}`;

                    // Update help text based on format
                    if (format === 'excel') {
                        fileHelp.textContent = 'Please select an Excel file (.xlsx)';
                    } else {
                        fileHelp.textContent = 'Please select an XML file (.xml)';
                    }
                } else {
                    fileHelp.textContent = 'Please select a format first';
                }
            }

            // Handle format selection change
            formatSelect.addEventListener('change', function() {
                updateFormAction(this.value);

                // Clear file input if format changes
                fileInput.value = '';
                selectedFileText.textContent = 'No file selected';
            });

            // Handle file selection
            fileInput.addEventListener('change', function(e) {
                if (this.files.length > 0) {
                    const fileName = this.files[0].name;
                    selectedFileText.textContent = fileName;

                    // Validate file extension
                    const format = formatSelect.value;
                    const extension = fileName.split('.').pop().toLowerCase();

                    if ((format === 'excel' && extension !== 'xlsx') ||
                        ((format === 'ead' || format === 'seda') && extension !== 'xml')) {
                        alert('Please select a file with the correct format');
                        this.value = '';
                        selectedFileText.textContent = 'No file selected';
                    }
                } else {
                    selectedFileText.textContent = 'No file selected';
                }
            });

            // Form submission validation
            form.addEventListener('submit', function(e) {
                if (!formatSelect.value) {
                    e.preventDefault();
                    alert('Please select an import format');
                    formatSelect.focus();
                }
            });

            // Initial setup
            updateFormAction(formatSelect.value);
        });
    </script>
@endsection
