<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Digital Folders') }}
            </h2>
            <a href="{{ route('folders.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Folder
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Flash Messages --}}
            <x-flash-messages />

            {{-- View Mode Toggle & Filters --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm mb-6 p-4">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                    {{-- View Mode Toggle --}}
                    <div class="flex space-x-2">
                        <a href="{{ route('folders.index', ['view' => 'tree'] + request()->except('view')) }}"
                           class="px-4 py-2 rounded-md {{ $viewMode === 'tree' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }} transition-colors">
                            <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                            </svg>
                            Tree View
                        </a>
                        <a href="{{ route('folders.index', ['view' => 'list'] + request()->except('view')) }}"
                           class="px-4 py-2 rounded-md {{ $viewMode === 'list' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }} transition-colors">
                            <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                            </svg>
                            List View
                        </a>
                    </div>

                    {{-- Search Form --}}
                    <form method="GET" action="{{ route('folders.index') }}" class="flex-1 md:max-w-md">
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Search folders..."
                                   class="w-full px-4 py-2 pl-10 pr-4 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                            <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Tree View --}}
            @if($viewMode === 'tree')
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <div id="folder-tree" class="min-h-[400px]"></div>
                </div>
            @else
                {{-- List View --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Parent</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Documents</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($folders as $folder)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                            </svg>
                                            <a href="{{ route('folders.show', $folder->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline font-medium">
                                                {{ $folder->name }}
                                            </a>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $folder->code }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        @if($folder->parent)
                                            <a href="{{ route('folders.show', $folder->parent->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                                {{ $folder->parent->name }}
                                            </a>
                                        @else
                                            <span class="text-gray-400">Root</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full text-xs">
                                            {{ $folder->documents_count }} docs
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-full text-xs font-medium">
                                            {{ ucfirst($folder->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2">
                                            <a href="{{ route('folders.show', $folder->id) }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300" title="View">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </a>
                                            <a href="{{ route('folders.edit', $folder->id) }}" class="text-yellow-600 dark:text-yellow-400 hover:text-yellow-800 dark:hover:text-yellow-300" title="Edit">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </a>
                                            <form method="POST" action="{{ route('folders.destroy', $folder->id) }}" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this folder?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300" title="Delete">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                        </svg>
                                        <p class="text-lg font-medium">No folders found</p>
                                        <p class="text-sm mt-2">Get started by creating a new folder</p>
                                        <a href="{{ route('folders.create') }}" class="inline-flex items-center mt-4 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition-colors">
                                            Create Folder
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{-- Pagination --}}
                    @if($viewMode === 'list' && $folders->hasPages())
                        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                            {{ $folders->links() }}
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- JS for Tree View --}}
    @push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.15/jstree.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.15/themes/default/style.min.css" />

    <script>
        $(document).ready(function() {
            @if($viewMode === 'tree')
                $('#folder-tree').jstree({
                    'core': {
                        'data': {
                            'url': '{{ route("folders.tree") }}',
                            'dataType': 'json'
                        },
                        'check_callback': true,
                        'themes': {
                            'responsive': true,
                            'dots': true
                        }
                    },
                    'plugins': ['dnd', 'contextmenu', 'search'],
                    'contextmenu': {
                        'items': function($node) {
                            return {
                                'view': {
                                    'label': 'View',
                                    'action': function(obj) {
                                        window.location.href = '/folders/' + $node.id;
                                    }
                                },
                                'edit': {
                                    'label': 'Edit',
                                    'action': function(obj) {
                                        window.location.href = '/folders/' + $node.id + '/edit';
                                    }
                                },
                                'create': {
                                    'label': 'Create Subfolder',
                                    'action': function(obj) {
                                        window.location.href = '/folders/create?parent_id=' + $node.id;
                                    }
                                },
                                'delete': {
                                    'label': 'Delete',
                                    'action': function(obj) {
                                        if(confirm('Are you sure you want to delete this folder?')) {
                                            // Delete via AJAX
                                            fetch('/folders/' + $node.id, {
                                                method: 'DELETE',
                                                headers: {
                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                    'Accept': 'application/json'
                                                }
                                            }).then(() => location.reload());
                                        }
                                    }
                                }
                            };
                        }
                    }
                });

                // Handle drag & drop
                $('#folder-tree').on('move_node.jstree', function(e, data) {
                    $.ajax({
                        url: '/folders/' + data.node.id + '/move',
                        method: 'POST',
                        data: {
                            parent_id: data.parent === '#' ? null : data.parent,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            console.log('Folder moved successfully');
                        },
                        error: function(xhr) {
                            alert('Error moving folder: ' + xhr.responseJSON.message);
                            location.reload();
                        }
                    });
                });
            @endif
        });
    </script>
    @endpush
</x-app-layout>
