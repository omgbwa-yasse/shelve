<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Document:') }} {{ $document->name }}
        </h2>
    </x-slot>

    <div class="py-6" x-data="documentEditForm">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <form method="POST" action="{{ route('documents.update', $document->id) }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- Name --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name', $document->name) }}" required
                               class="mt-1 block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Description
                        </label>
                        <textarea name="description" id="description" rows="3"
                                  class="mt-1 block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">{{ old('description', $document->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Current File --}}
                    @if($document->attachment)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Current File
                            </label>
                            <div class="mt-1 flex items-center space-x-2">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                                <span class="text-sm text-gray-900 dark:text-gray-100">{{ $document->attachment->name }}</span>
                                <span class="text-xs text-gray-500">({{ number_format($document->attachment->size / 1024, 2) }} KB)</span>
                            </div>
                        </div>
                    @endif

                    {{-- Type (Read-only for edit) --}}
                    <div>
                        <label for="type_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Document Type <span class="text-red-500">*</span>
                        </label>
                        <input type="hidden" name="type_id" value="{{ $document->type_id }}">
                        <input type="text" value="{{ $document->type->name }} - {{ $document->type->description }}" readonly
                               class="mt-1 block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-gray-100 dark:bg-gray-600 text-gray-900 dark:text-gray-100">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Document type cannot be changed after creation</p>
                    </div>

                    {{-- Access Level --}}
                    <div>
                        <label for="access_level" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Access Level
                        </label>
                        <select name="access_level" id="access_level"
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <option value="public" {{ old('access_level', $document->access_level) == 'public' ? 'selected' : '' }}>Public</option>
                            <option value="internal" {{ old('access_level', $document->access_level) == 'internal' ? 'selected' : '' }}>Internal</option>
                            <option value="confidential" {{ old('access_level', $document->access_level) == 'confidential' ? 'selected' : '' }}>Confidential</option>
                            <option value="secret" {{ old('access_level', $document->access_level) == 'secret' ? 'selected' : '' }}>Secret</option>
                        </select>
                        @error('access_level')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Status --}}
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Status
                        </label>
                        <select name="status" id="status"
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <option value="draft" {{ old('status', $document->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="pending_approval" {{ old('status', $document->status) == 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                            <option value="approved" {{ old('status', $document->status) == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ old('status', $document->status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="archived" {{ old('status', $document->status) == 'archived' ? 'selected' : '' }}>Archived</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Dynamic Metadata Fields --}}
                    <div x-show="metadataFields.length > 0" x-cloak>
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                                Metadata Fields
                            </h3>
                            <div id="metadata-container" class="space-y-4">
                                <!-- Metadata fields will be dynamically loaded here -->
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                        <div>
                            <form method="POST" action="{{ route('documents.destroy', $document->id) }}" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this document? This action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="px-4 py-2 border border-red-300 dark:border-red-600 rounded-md shadow-sm text-sm font-medium text-red-700 dark:text-red-400 bg-white dark:bg-gray-700 hover:bg-red-50 dark:hover:bg-red-900 transition-colors">
                                    Delete Document
                                </button>
                            </form>
                        </div>
                        <div class="flex items-center space-x-3">
                            <a href="{{ route('documents.show', $document->id) }}"
                               class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                                Cancel
                            </a>
                            <button type="submit"
                                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                Update Document
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('documentEditForm', () => ({
                metadataFields: [],
                currentMetadata: @json($document->metadata ?? []),

                init() {
                    // Load metadata for current type on page load
                    const typeId = {{ $document->type_id }};
                    if (typeId) {
                        this.loadMetadata(typeId);
                    }
                },

                async loadMetadata(typeId) {
                    if (!typeId) {
                        this.metadataFields = [];
                        document.getElementById('metadata-container').innerHTML = '';
                        return;
                    }

                    try {
                        const response = await fetch(`/api/v1/metadata/document-types/${typeId}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        if (!response.ok) {
                            throw new Error('Failed to load metadata');
                        }

                        const data = await response.json();
                        this.metadataFields = data.data;
                        this.renderMetadataFields();
                    } catch (error) {
                        console.error('Error loading metadata:', error);
                    }
                },

                renderMetadataFields() {
                    const container = document.getElementById('metadata-container');
                    container.innerHTML = '';

                    this.metadataFields.forEach(field => {
                        if (!field.visible) return;

                        const fieldDiv = document.createElement('div');
                        fieldDiv.className = 'metadata-field';

                        let inputHTML = '';
                        const fieldName = `metadata[${field.name}]`;
                        const currentValue = this.currentMetadata[field.name] || field.default_value || '';
                        const required = field.mandatory ? 'required' : '';
                        const readonly = field.readonly ? 'readonly' : '';

                        let label = `<label for="metadata_${field.name}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            ${field.label} ${field.mandatory ? '<span class="text-red-500">*</span>' : ''}
                        </label>`;

                        if (field.description) {
                            label += `<p class="mt-1 text-xs text-gray-500 dark:text-gray-400">${field.description}</p>`;
                        }

                        const inputClass = 'mt-1 block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100';

                        switch (field.data_type) {
                            case 'text':
                                inputHTML = `<input type="text" name="${fieldName}" id="metadata_${field.name}" value="${currentValue}" ${required} ${readonly} class="${inputClass}">`;
                                break;
                            case 'long_text':
                                inputHTML = `<textarea name="${fieldName}" id="metadata_${field.name}" rows="3" ${required} ${readonly} class="${inputClass}">${currentValue}</textarea>`;
                                break;
                            case 'number':
                                inputHTML = `<input type="number" name="${fieldName}" id="metadata_${field.name}" value="${currentValue}" step="any" ${required} ${readonly} class="${inputClass}">`;
                                break;
                            case 'date':
                                inputHTML = `<input type="date" name="${fieldName}" id="metadata_${field.name}" value="${currentValue}" ${required} ${readonly} class="${inputClass}">`;
                                break;
                            case 'datetime':
                                inputHTML = `<input type="datetime-local" name="${fieldName}" id="metadata_${field.name}" value="${currentValue}" ${required} ${readonly} class="${inputClass}">`;
                                break;
                            case 'boolean':
                                const checked = currentValue ? 'checked' : '';
                                inputHTML = `<div class="flex items-center">
                                    <input type="checkbox" name="${fieldName}" id="metadata_${field.name}" value="1" ${checked} ${readonly} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <input type="hidden" name="${fieldName}" value="0">
                                </div>`;
                                break;
                            case 'email':
                                inputHTML = `<input type="email" name="${fieldName}" id="metadata_${field.name}" value="${currentValue}" ${required} ${readonly} class="${inputClass}">`;
                                break;
                            case 'url':
                                inputHTML = `<input type="url" name="${fieldName}" id="metadata_${field.name}" value="${currentValue}" ${required} ${readonly} class="${inputClass}">`;
                                break;
                            case 'select':
                                if (field.reference_list) {
                                    let options = '<option value="">-- Select --</option>';
                                    field.reference_list.values.forEach(val => {
                                        const selected = val.value === currentValue ? 'selected' : '';
                                        options += `<option value="${val.value}" ${selected}>${val.display_value}</option>`;
                                    });
                                    inputHTML = `<select name="${fieldName}" id="metadata_${field.name}" ${required} ${readonly} class="${inputClass}">${options}</select>`;
                                }
                                break;
                            case 'multi_select':
                                if (field.reference_list) {
                                    let options = '';
                                    const selectedValues = Array.isArray(currentValue) ? currentValue : (currentValue ? [currentValue] : []);
                                    field.reference_list.values.forEach(val => {
                                        const selected = selectedValues.includes(val.value) ? 'selected' : '';
                                        options += `<option value="${val.value}" ${selected}>${val.display_value}</option>`;
                                    });
                                    inputHTML = `<select name="${fieldName}[]" id="metadata_${field.name}" multiple ${required} ${readonly} class="${inputClass}">${options}</select>`;
                                }
                                break;
                            case 'json':
                                const jsonValue = typeof currentValue === 'object' ? JSON.stringify(currentValue, null, 2) : currentValue;
                                inputHTML = `<textarea name="${fieldName}" id="metadata_${field.name}" rows="4" placeholder='{"key": "value"}' ${required} ${readonly} class="${inputClass} font-mono text-sm">${jsonValue}</textarea>`;
                                break;
                        }

                        fieldDiv.innerHTML = label + '<div class="mt-1">' + inputHTML + '</div>';
                        container.appendChild(fieldDiv);
                    });
                }
            }));
        });
    </script>
</x-app-layout>
