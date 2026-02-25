<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Folder:') }} {{ $folder->name }}
        </h2>
    </x-slot>

    <div class="py-6" x-data="folderEditForm">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <form method="POST" action="{{ route('folders.update', $folder->id) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- Name --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name', $folder->name) }}" required
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
                                  class="mt-1 block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">{{ old('description', $folder->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Parent Folder --}}
                    <div>
                        <label for="parent_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Parent Folder
                        </label>
                        <select name="parent_id" id="parent_id"
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <option value="">-- Root Level --</option>
                            @foreach($allFolders as $availableFolder)
                                <option value="{{ $availableFolder->id }}"
                                        {{ (old('parent_id', $folder->parent_id) == $availableFolder->id) ? 'selected' : '' }}>
                                    {{ str_repeat('—', $availableFolder->depth ?? 0) }} {{ $availableFolder->name }} ({{ $availableFolder->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('parent_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Note: Cannot move folder into its own subfolders
                        </p>
                    </div>

                    {{-- Organization --}}
                    <div>
                        <label for="organization_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Organization <span class="text-red-500">*</span>
                        </label>
                        <select name="organization_id" id="organization_id" required
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <option value="">-- Select Organization --</option>
                            @foreach(\App\Models\Organisation::all() as $org)
                                <option value="{{ $org->id }}" {{ old('organization_id', $folder->organization_id) == $org->id ? 'selected' : '' }}>
                                    {{ $org->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('organization_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Type --}}
                    <div>
                        <label for="type_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Folder Type <span class="text-red-500">*</span>
                        </label>
                        <select name="type_id" id="type_id" required
                                @change="loadMetadata($event.target.value)"
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <option value="">-- Select Type --</option>
                            @foreach(\App\Models\RecordDigitalFolderType::all() as $type)
                                <option value="{{ $type->id }}" {{ old('type_id', $folder->type_id) == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }} - {{ $type->description }}
                                </option>
                            @endforeach
                        </select>
                        @error('type_id')
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
                            <form method="POST" action="{{ route('folders.destroy', $folder->id) }}" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this folder? This action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="px-4 py-2 border border-red-300 dark:border-red-600 rounded-md shadow-sm text-sm font-medium text-red-700 dark:text-red-400 bg-white dark:bg-gray-700 hover:bg-red-50 dark:hover:bg-red-900 transition-colors">
                                    Delete Folder
                                </button>
                            </form>
                        </div>
                        <div class="flex items-center space-x-3">
                            <a href="{{ route('folders.show', $folder->id) }}"
                               class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                                Cancel
                            </a>
                            <button type="submit"
                                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                Update Folder
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('folderEditForm', () => ({
                metadataFields: [],
                currentMetadata: @json($folder->metadata ?? []),

                init() {
                    // Load metadata for current type on page load
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
                        const response = await fetch(`/api/v1/metadata/folder-types/${typeId}`, {
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
