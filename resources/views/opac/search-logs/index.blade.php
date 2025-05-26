<x-portal-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Search Logs') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold">Search Logs</h3>
                        <div class="flex space-x-4">
                            <form action="{{ route('portal.search-logs.index') }}" method="GET" class="flex space-x-2">
                                <input type="date" name="date" value="{{ request('date') }}" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Filter
                                </button>
                            </form>
                            <a href="{{ route('portal.search-logs.export') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                Export
                            </a>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Search Query</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Results Count</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($searchLogs as $log)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $log->user->name ?? 'Guest' }}</div>
                                            @if($log->user)
                                                <div class="text-sm text-gray-500">{{ $log->user->email }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900">{{ $log->query }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $log->category }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $log->results_count }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $log->created_at->format('M d, Y H:i:s') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $log->ip_address }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $searchLogs->links() }}
                    </div>

                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="bg-white rounded-lg shadow p-6">
                            <h4 class="text-lg font-semibold mb-4">Most Searched Terms</h4>
                            <div class="space-y-2">
                                @foreach($mostSearchedTerms as $term)
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">{{ $term->query }}</span>
                                        <span class="text-sm font-medium text-gray-900">{{ $term->count }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow p-6">
                            <h4 class="text-lg font-semibold mb-4">Search Categories</h4>
                            <div class="space-y-2">
                                @foreach($searchCategories as $category)
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">{{ $category->category }}</span>
                                        <span class="text-sm font-medium text-gray-900">{{ $category->count }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow p-6">
                            <h4 class="text-lg font-semibold mb-4">Search Activity</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Today</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $todaySearches }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">This Week</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $weekSearches }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">This Month</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $monthSearches }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-portal-layout>
