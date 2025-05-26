<x-portal-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Users') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold">Users List</h3>
                        <div class="flex space-x-4">
                            <form action="{{ route('portal.users.index') }}" method="GET" class="flex space-x-2">
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search users..." class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Search
                                </button>
                            </form>
                            <a href="{{ route('portal.users.create') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                Add User
                            </a>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Login</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($users as $user)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <img class="h-10 w-10 rounded-full" src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}">
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $user->name }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $user->email }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                {{ ucfirst($user->role) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $user->last_login_at ? $user->last_login_at->format('M d, Y H:i') : 'Never' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-3">
                                                <a href="{{ route('portal.users.edit', $user) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                                <a href="{{ route('portal.users.show', $user) }}" class="text-green-600 hover:text-green-900">View</a>
                                                @if($user->id !== auth()->id())
                                                    <form action="{{ route('portal.users.destroy', $user) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this user?')">Delete</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $users->links() }}
                    </div>

                    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-white rounded-lg shadow p-6">
                            <h4 class="text-lg font-semibold mb-4">User Statistics</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Total Users</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $totalUsers }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Active Users</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $activeUsers }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">New Users (Last 30 days)</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $newUsers }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow p-6">
                            <h4 class="text-lg font-semibold mb-4">Role Distribution</h4>
                            <div class="space-y-2">
                                @foreach($roleDistribution as $role)
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">{{ ucfirst($role->role) }}</span>
                                        <span class="text-sm font-medium text-gray-900">{{ $role->count }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow p-6">
                            <h4 class="text-lg font-semibold mb-4">Recent Activity</h4>
                            <div class="space-y-2">
                                @foreach($recentActivity as $activity)
                                    <div class="text-sm">
                                        <span class="text-gray-600">{{ $activity->user->name }}</span>
                                        <span class="text-gray-500">{{ $activity->description }}</span>
                                        <span class="text-gray-400 text-xs">{{ $activity->created_at->diffForHumans() }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-portal-layout>
