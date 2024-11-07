@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-8">
                <h2 class="text-3xl font-extrabold text-gray-900">
                    Import Slips
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Choose your file format and upload your data
                </p>
            </div>

            <!-- Card Container -->
            <div class="bg-white rounded-lg shadow-md p-8">
                <form id="importForm" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <!-- Error Messages -->
                    @if ($errors->any())
                        <div class="rounded-md bg-red-50 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">
                                        There were errors with your submission
                                    </h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        <ul class="list-disc pl-5 space-y-1">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Format Selection -->
                    <div class="space-y-2">
                        <label for="format" class="block text-sm font-medium text-gray-700">
                            Import Format
                        </label>
                        <select id="format" name="format" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded-md shadow-sm">
                            <option value="excel">Excel (.xlsx)</option>
                            <option value="ead">EAD (.xml)</option>
                            <option value="seda">SEDA (.xml)</option>
                        </select>
                    </div>

                    <!-- File Upload -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">
                            Upload File
                        </label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-indigo-500 transition-colors duration-200">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="file" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                        <span>Upload a file</span>
                                        <input id="file" name="file" type="file" class="sr-only" required>
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500" id="file-format-text">
                                    Excel or XML files supported
                                </p>
                            </div>
                        </div>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" id="selected-file">
                                No file selected
                            </p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-3 pt-6">
                        <a href="{{ route('slips.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Import Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('importForm');
            const formatSelect = document.getElementById('formatSelect');
            const fileInput = document.getElementById('file');
            const selectedFileText = document.getElementById('selected-file');
            const fileFormatText = document.getElementById('file-format-text');

            // Update form action on load and change
            function updateFormAction(format) {
                form.action = `/transferrings/slips/import/${format}`;

                // Update file format helper text
                if (format === 'excel') {
                    fileFormatText.textContent = 'Excel files only (.xlsx)';
                } else {
                    fileFormatText.textContent = 'XML files only (.xml)';
                }
            }

            // Initial action update
            updateFormAction(formatSelect ? formatSelect.value : 'excel');

            // Format change handler
            if (formatSelect) {
                formatSelect.addEventListener('change', function() {
                    updateFormAction(this.value);
                });
            }

            // File selection handler
            if (fileInput) {
                fileInput.addEventListener('change', function(e) {
                    if (e.target.files.length > 0) {
                        selectedFileText.textContent = `Selected file: ${e.target.files[0].name}`;
                    } else {
                        selectedFileText.textContent = 'No file selected';
                    }
                });
            }

            // Drag and drop handling
            const dropZone = document.querySelector('.border-dashed');
            if (dropZone) {
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    dropZone.addEventListener(eventName, preventDefaults, false);
                });

                function preventDefaults(e) {
                    e.preventDefault();
                    e.stopPropagation();
                }

                ['dragenter', 'dragover'].forEach(eventName => {
                    dropZone.addEventListener(eventName, highlight, false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    dropZone.addEventListener(eventName, unhighlight, false);
                });

                function highlight(e) {
                    dropZone.classList.add('border-indigo-500', 'bg-indigo-50');
                }

                function unhighlight(e) {
                    dropZone.classList.remove('border-indigo-500', 'bg-indigo-50');
                }

                dropZone.addEventListener('drop', handleDrop, false);

                function handleDrop(e) {
                    const dt = e.dataTransfer;
                    const files = dt.files;
                    fileInput.files = files;

                    if (files.length > 0) {
                        selectedFileText.textContent = `Selected file: ${files[0].name}`;
                    }
                }
            }
        });
    </script>
@endsection
