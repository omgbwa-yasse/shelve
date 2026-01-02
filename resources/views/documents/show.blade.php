<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ $document->name }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Code: {{ $document->code }} | Version: {{ $document->version }} | Status:
                    <span class="px-2 py-1 {{ $document->status == 'approved' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200' }} rounded text-xs font-medium">
                        {{ ucfirst(str_replace('_', ' ', $document->status)) }}
                    </span>
                </p>
            </div>
            <div class="flex space-x-2">
                @can('update', $document)
                    <a href="{{ route('documents.edit', $document->id) }}"
                       class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-md transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit
                    </a>
                @endcan
                @if($document->attachment)
                    <a href="{{ route('documents.download', $document->id) }}"
                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Download
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Flash Messages --}}
            <x-flash-messages />

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Document Details --}}
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Document Information</h3>

                    <dl class="grid grid-cols-1 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $document->name }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Code</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $document->code }}</dd>
                        </div>

                        @if($document->description)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $document->description }}</dd>
                            </div>
                        @endif

                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Type</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ $document->type->name ?? 'N/A' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Folder</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                <a href="{{ route('folders.show', $document->folder->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                    {{ $document->folder->path ?? 'N/A' }}
                                </a>
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Organization</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ $document->organisation->name ?? 'N/A' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Access Level</dt>
                            <dd class="mt-1">
                                <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded text-xs font-medium">
                                    {{ ucfirst($document->access_level) }}
                                </span>
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Document Date</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ $document->document_date ? $document->document_date->format('M d, Y') : 'N/A' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created By</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ $document->creator->name ?? 'N/A' }} on {{ $document->created_at->format('M d, Y') }}
                            </dd>
                        </div>

                        @if($document->attachment)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Attachment</dt>
                                <dd class="mt-1">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="text-sm text-gray-900 dark:text-gray-100">{{ $document->attachment->name }}</span>
                                        <span class="text-xs text-gray-500">({{ number_format($document->attachment->size / 1024, 2) }} KB)</span>
                                    </div>
                                </dd>
                            </div>
                        @endif

                        {{-- Display Metadata --}}
                        @if($document->type && $document->type->metadataDefinitions->count() > 0)
                            <div class="col-span-full border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
                                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Metadata</h4>
                                @foreach($document->type->metadataDefinitions as $definition)
                                    @if($definition->pivot->visible)
                                        <div class="mb-3">
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                                {{ $definition->label }}
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                @php
                                                    $value = $document->getMetadataValue($definition->name);
                                                @endphp
                                                @if($value !== null)
                                                    @if($definition->data_type === 'boolean')
                                                        <span class="px-2 py-1 {{ $value ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200' }} rounded text-xs font-medium">
                                                            {{ $value ? 'Yes' : 'No' }}
                                                        </span>
                                                    @elseif($definition->data_type === 'select' && $definition->referenceList)
                                                        @php
                                                            $refValue = $definition->referenceList->values->firstWhere('value', $value);
                                                        @endphp
                                                        {{ $refValue->display_value ?? $value }}
                                                    @elseif($definition->data_type === 'multi_select' && $definition->referenceList)
                                                        @php
                                                            $values = is_array($value) ? $value : json_decode($value, true);
                                                        @endphp
                                                        @if(is_array($values))
                                                            @foreach($values as $val)
                                                                @php
                                                                    $refValue = $definition->referenceList->values->firstWhere('value', $val);
                                                                @endphp
                                                                <span class="inline-block px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded text-xs font-medium mr-1 mb-1">
                                                                    {{ $refValue->display_value ?? $val }}
                                                                </span>
                                                            @endforeach
                                                        @endif
                                                    @elseif($definition->data_type === 'url')
                                                        <a href="{{ $value }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">
                                                            {{ $value }}
                                                        </a>
                                                    @elseif($definition->data_type === 'email')
                                                        <a href="mailto:{{ $value }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                                            {{ $value }}
                                                        </a>
                                                    @elseif($definition->data_type === 'json')
                                                        <pre class="bg-gray-100 dark:bg-gray-700 p-2 rounded text-xs overflow-x-auto">{{ is_array($value) ? json_encode($value, JSON_PRETTY_PRINT) : $value }}</pre>
                                                    @else
                                                        {{ $value }}
                                                    @endif
                                                @else
                                                    <span class="text-gray-400 dark:text-gray-500 italic">Not set</span>
                                                @endif
                                            </dd>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </dl>
                </div>

                {{-- Document Sidebar --}}
                <div class="space-y-6">
                    {{-- Version History --}}
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Version Information</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Current Version</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">v{{ $document->version }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Is Current</dt>
                                <dd class="mt-1">
                                    <span class="px-2 py-1 {{ $document->is_current_version ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200' }} rounded text-xs font-medium">
                                        {{ $document->is_current_version ? 'Yes' : 'No' }}
                                    </span>
                                </dd>
                            </div>
                            @if($document->parent_document_id)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Previous Version</dt>
                                    <dd class="mt-1 text-sm">
                                        <a href="{{ route('documents.show', $document->parent_document_id) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                            View Previous
                                        </a>
                                    </dd>
                                </div>
                            @endif
                        </dl>
                        @can('update', $document)
                            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <a href="{{ route('documents.versions.create', $document->id) }}"
                                   class="block text-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm transition-colors">
                                    Create New Version
                                </a>
                            </div>
                        @endcan
                    </div>

                    {{-- Workflow Actions --}}
                    @if($document->status === 'draft' || $document->status === 'pending_approval')
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Workflow Actions</h3>
                            <div class="space-y-2">
                                @if($document->status === 'draft')
                                    <form method="POST" action="{{ route('documents.submit', $document->id) }}">
                                        @csrf
                                        <button type="submit" class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md text-sm transition-colors">
                                            Submit for Approval
                                        </button>
                                    </form>
                                @endif
                                @if($document->status === 'pending_approval' && auth()->user()->can('approve', $document))
                                    <form method="POST" action="{{ route('documents.approve', $document->id) }}">
                                        @csrf
                                        <button type="submit" class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md text-sm transition-colors">
                                            Approve
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('documents.reject', $document->id) }}">
                                        @csrf
                                        <button type="submit" class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md text-sm transition-colors">
                                            Reject
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
