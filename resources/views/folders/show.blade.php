<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ $folder->name }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Code: {{ $folder->code }} | Status: {{ ucfirst($folder->status) }}
                </p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('folders.edit', $folder->id) }}"
                   class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-md transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit
                </a>
                <a href="{{ route('folders.create', ['parent_id' => $folder->id]) }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Subfolder
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Flash Messages --}}
            <x-flash-messages />

            {{-- Breadcrumb --}}
            @if($folder->parent)
                <nav class="flex bg-white dark:bg-gray-800 rounded-lg shadow-sm px-4 py-3" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ route('folders.index') }}" class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400">
                                Home
                            </a>
                        </li>
                        @if($folder->parent)
                            <li>
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <a href="{{ route('folders.show', $folder->parent->id) }}" class="ml-1 text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400">
                                        {{ $folder->parent->name }}
                                    </a>
                                </div>
                            </li>
                        @endif
                        <li aria-current="page">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-1 text-gray-500 dark:text-gray-400">{{ $folder->name }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Statistics Cards --}}
                <x-stat-card
                    title="Documents"
                    :value="$stats['total_documents']"
                    icon="file-text"
                    color="blue"
                    href="#documents"
                />
                <x-stat-card
                    title="Subfolders"
                    :value="$stats['total_subfolders']"
                    icon="folder"
                    color="green"
                    href="#subfolders"
                />
                <x-stat-card
                    title="Total Size"
                    :value="number_format($stats['total_size'] / 1024 / 1024, 2) . ' MB'"
                    icon="database"
                    color="purple"
                />
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Folder Details --}}
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Folder Information</h3>

                    <dl class="grid grid-cols-1 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $folder->name }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Code</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $folder->code }}</dd>
                        </div>

                        @if($folder->description)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $folder->description }}</dd>
                            </div>
                        @endif

                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Type</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ $folder->type->name ?? 'N/A' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Organization</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ $folder->organization->name ?? 'N/A' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Access Level</dt>
                            <dd class="mt-1">
                                <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded text-xs font-medium">
                                    {{ ucfirst($folder->access_level) }}
                                </span>
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created By</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ $folder->creator->name ?? 'N/A' }} on {{ $folder->created_at->format('M d, Y') }}
                            </dd>
                        </div>

                        {{-- Display Metadata --}}
                        @if($folder->type && $folder->type->metadataDefinitions->count() > 0)
                            <div class="col-span-full border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
                                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Metadata</h4>
                                @foreach($folder->type->metadataDefinitions as $definition)
                                    @if($definition->pivot->visible)
                                        <div class="mb-3">
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                                {{ $definition->label }}
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                @php
                                                    $value = $folder->getMetadataValue($definition->name);
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

                {{-- Folder Tree --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Subfolders</h3>

                    @if($tree->count() > 0)
                        <ul class="space-y-2">
                            @foreach($tree as $child)
                                <li>
                                    <a href="{{ route('folders.show', $child->id) }}"
                                       class="flex items-center text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                        </svg>
                                        {{ $child->name }}
                                        <span class="ml-auto text-gray-500 dark:text-gray-400 text-xs">
                                            ({{ $child->children->count() }} sub)
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">No subfolders yet</p>
                    @endif
                </div>
            </div>

            {{-- Documents Section --}}
            <div id="documents" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Documents</h3>
                    <a href="{{ route('documents.create', ['folder_id' => $folder->id]) }}"
                       class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                        + Add Document
                    </a>
                </div>

                @if($folder->documents->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Name</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Type</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Version</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Date</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($folder->documents as $doc)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $doc->name }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $doc->type->name ?? 'N/A' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">v{{ $doc->version }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $doc->created_at->format('M d, Y') }}</td>
                                        <td class="px-4 py-3 text-sm text-right">
                                            <a href="{{ route('documents.show', $doc->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline">View</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">No documents in this folder yet</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
