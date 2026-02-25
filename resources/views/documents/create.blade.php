<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create New Document') }}
        </h2>
    </x-slot>

    <div class="py-6" x-data="documentCreateForm">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    {{-- Name --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
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
                                  class="mt-1 block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Folder --}}
                    <div>
                        <label for="folder_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Folder <span class="text-red-500">*</span>
                        </label>
                        <select name="folder_id" id="folder_id" required
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <option value="">-- Select Folder --</option>
                            @foreach(\App\Models\RecordDigitalFolder::with('type')->get() as $folder)
                                <option value="{{ $folder->id }}"
                                        {{ (old('folder_id', request('folder_id')) == $folder->id) ? 'selected' : '' }}>
                                    {{ $folder->path }} ({{ $folder->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('folder_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Organization --}}
                    <div>
                        <label for="organisation_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Organization <span class="text-red-500">*</span>
                        </label>
                        <select name="organisation_id" id="organisation_id" required
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <option value="">-- Select Organization --</option>
                            @foreach(\App\Models\Organisation::all() as $org)
                                <option value="{{ $org->id }}" {{ old('organisation_id') == $org->id ? 'selected' : '' }}>
                                    {{ $org->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('organisation_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Type --}}
                    <div>
                        <label for="type_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Document Type <span class="text-red-500">*</span>
                        </label>
                        <select name="type_id" id="type_id" required
                                @change="loadMetadata($event.target.value)"
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <option value="">-- Select Type --</option>
                            @foreach(\App\Models\RecordDigitalDocumentType::all() as $type)
                                <option value="{{ $type->id }}" {{ old('type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }} - {{ $type->description }}
                                </option>
                            @endforeach
                        </select>
                        @error('type_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- File Upload --}}
                    <div>
                        <label for="file" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Upload File
                        </label>
                        <input type="file" name="file" id="file"
                               class="mt-1 block w-full text-sm text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-600 rounded-md cursor-pointer bg-white dark:bg-gray-700 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Maximum file size: 50MB</p>
                        @error('file')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Access Level --}}
                    <div>
                        <label for="access_level" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Access Level
                        </label>
                        <select name="access_level" id="access_level"
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <option value="public" {{ old('access_level') == 'public' ? 'selected' : '' }}>Public</option>
                            <option value="internal" {{ old('access_level', 'internal') == 'internal' ? 'selected' : '' }}>Internal</option>
                            <option value="confidential" {{ old('access_level') == 'confidential' ? 'selected' : '' }}>Confidential</option>
                            <option value="secret" {{ old('access_level') == 'secret' ? 'selected' : '' }}>Secret</option>
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
                            <option value="draft" {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="pending_approval" {{ old('status') == 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                            <option value="approved" {{ old('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Document Date --}}
                    <div>
                        <label for="document_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Document Date
                        </label>
                        <input type="date" name="document_date" id="document_date"
                               value="{{ old('document_date', date('Y-m-d')) }}"
                               class="mt-1 block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        @error('document_date')
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
                    <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('documents.index') }}"
                           class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                            Cancel
                        </a>
                        <button type="submit"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            Create Document
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('documentCreateForm', () => ({
                metadataFields: [],

                init() {
                    // Load metadata for pre-selected type on page load
                    const typeId = document.getElementById('type_id').value;
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
                                inputHTML = `<input type="text" name="${fieldName}" id="metadata_${field.name}" value="${field.default_value || ''}" ${required} ${readonly} class="${inputClass}">`;
                                break;
                            case 'long_text':
                                inputHTML = `<textarea name="${fieldName}" id="metadata_${field.name}" rows="3" ${required} ${readonly} class="${inputClass}">${field.default_value || ''}</textarea>`;
                                break;
                            case 'number':
                                inputHTML = `<input type="number" name="${fieldName}" id="metadata_${field.name}" value="${field.default_value || ''}" step="any" ${required} ${readonly} class="${inputClass}">`;
                                break;
                            case 'date':
                                inputHTML = `<input type="date" name="${fieldName}" id="metadata_${field.name}" value="${field.default_value || ''}" ${required} ${readonly} class="${inputClass}">`;
                                break;
                            case 'datetime':
                                inputHTML = `<input type="datetime-local" name="${fieldName}" id="metadata_${field.name}" value="${field.default_value || ''}" ${required} ${readonly} class="${inputClass}">`;
                                break;
                            case 'boolean':
                                const checked = field.default_value ? 'checked' : '';
                                inputHTML = `<div class="flex items-center">
                                    <input type="checkbox" name="${fieldName}" id="metadata_${field.name}" value="1" ${checked} ${readonly} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <input type="hidden" name="${fieldName}" value="0">
                                </div>`;
                                break;
                            case 'email':
                                inputHTML = `<input type="email" name="${fieldName}" id="metadata_${field.name}" value="${field.default_value || ''}" ${required} ${readonly} class="${inputClass}">`;
                                break;
                            case 'url':
                                inputHTML = `<input type="url" name="${fieldName}" id="metadata_${field.name}" value="${field.default_value || ''}" ${required} ${readonly} class="${inputClass}">`;
                                break;
                            case 'select':
                                if (field.reference_list) {
                                    let options = '<option value="">-- Select --</option>';
                                    field.reference_list.values.forEach(val => {
                                        const selected = val.value === field.default_value ? 'selected' : '';
                                        options += `<option value="${val.value}" ${selected}>${val.display_value}</option>`;
                                    });
                                    inputHTML = `<select name="${fieldName}" id="metadata_${field.name}" ${required} ${readonly} class="${inputClass}">${options}</select>`;
                                }
                                break;
                            case 'multi_select':
                                if (field.reference_list) {
                                    let options = '';
                                    field.reference_list.values.forEach(val => {
                                        options += `<option value="${val.value}">${val.display_value}</option>`;
                                    });
                                    inputHTML = `<select name="${fieldName}[]" id="metadata_${field.name}" multiple ${required} ${readonly} class="${inputClass}">${options}</select>`;
                                }
                                break;
                            case 'json':
                                inputHTML = `<textarea name="${fieldName}" id="metadata_${field.name}" rows="4" placeholder='{"key": "value"}' ${required} ${readonly} class="${inputClass} font-mono text-sm">${field.default_value || ''}</textarea>`;
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
