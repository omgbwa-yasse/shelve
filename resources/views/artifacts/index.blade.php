<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">{{ __('Artifacts') }}</h2>
            <a href="{{ route('artifacts.create') }}" class="btn btn-primary">
                <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                {{ __('New Artifact') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash-messages />

            {{-- Search and Filters --}}
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
                <form method="GET" action="{{ route('artifacts.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="{{ __('Search artifacts...') }}"
                                   class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                        </div>
                        <div>
                            <select name="view" onchange="this.form.submit()"
                                    class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                                <option value="gallery" @if($viewMode === 'gallery') selected @endif>{{ __('Gallery View') }}</option>
                                <option value="list" @if($viewMode === 'list') selected @endif>{{ __('List View') }}</option>
                            </select>
                        </div>
                        <div>
                            <button type="submit" class="w-full btn btn-primary">
                                {{ __('Search') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            @if($viewMode === 'gallery')
                {{-- Gallery View --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @forelse($artifacts as $artifact)
                        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden hover:shadow-lg transition">
                            <div class="aspect-w-16 aspect-h-9 bg-gray-200">
                                @if($artifact->primaryImage)
                                    <img src="{{ Storage::url($artifact->primaryImage->path) }}"
                                         alt="{{ $artifact->name }}"
                                         class="object-cover w-full h-48">
                                @else
                                    <div class="flex items-center justify-center h-48 bg-gray-100 dark:bg-gray-700">
                                        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="p-4">
                                <h3 class="font-semibold text-lg text-gray-800 dark:text-gray-200 mb-2">
                                    {{ $artifact->name }}
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 line-clamp-2">
                                    {{ $artifact->description }}
                                </p>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-gray-500">{{ $artifact->code }}</span>
                                    <a href="{{ route('artifacts.show', $artifact) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                        {{ __('View') }} →
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-12">
                            <p class="text-gray-500 dark:text-gray-400">{{ __('No artifacts found.') }}</p>
                        </div>
                    @endforelse
                </div>
            @else
                {{-- List View --}}
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ __('Code') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ __('Name') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ __('Category') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ __('Condition') }}
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ __('Actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($artifacts as $artifact)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $artifact->code }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('artifacts.show', $artifact) }}" class="text-blue-600 hover:underline dark:text-blue-400">
                                            {{ $artifact->name }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $artifact->category }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $artifact->condition ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('artifacts.show', $artifact) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 mr-3">
                                            {{ __('View') }}
                                        </a>
                                        <a href="{{ route('artifacts.edit', $artifact) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">
                                            {{ __('Edit') }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                        {{ __('No artifacts found.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $artifacts->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
