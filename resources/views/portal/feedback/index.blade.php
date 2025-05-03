<x-portal-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Feedback') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold">Feedback List</h3>
                        <a href="{{ route('portal.feedback.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            New Feedback
                        </a>
                    </div>

                    <div class="grid grid-cols-1 gap-6">
                        @foreach($feedback as $item)
                            <div class="bg-white rounded-lg shadow-md p-6">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900">{{ $item->subject }}</h4>
                                        <p class="text-sm text-gray-500">
                                            From: {{ $item->user->name }} ({{ $item->user->email }})
                                        </p>
                                    </div>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $item->status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                           ($item->status === 'in_progress' ? 'bg-blue-100 text-blue-800' :
                                           ($item->status === 'resolved' ? 'bg-green-100 text-green-800' :
                                           'bg-gray-100 text-gray-800')) }}">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </div>

                                <div class="prose max-w-none mb-4">
                                    {{ $item->message }}
                                </div>

                                <div class="flex justify-between items-center text-sm text-gray-500">
                                    <span>Submitted: {{ $item->created_at->format('M d, Y H:i') }}</span>
                                    <div class="flex space-x-3">
                                        <a href="{{ route('portal.feedback.show', $item) }}" class="text-blue-600 hover:text-blue-900">View Details</a>
                                        @if($item->status === 'pending')
                                            <form action="{{ route('portal.feedback.update-status', $item) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="in_progress">
                                                <button type="submit" class="text-blue-600 hover:text-blue-900">Mark In Progress</button>
                                            </form>
                                        @endif
                                        @if($item->status === 'in_progress')
                                            <form action="{{ route('portal.feedback.update-status', $item) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="resolved">
                                                <button type="submit" class="text-green-600 hover:text-green-900">Mark Resolved</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>

                                @if($item->response)
                                    <div class="mt-4 pt-4 border-t border-gray-200">
                                        <h5 class="text-sm font-semibold text-gray-900 mb-2">Response:</h5>
                                        <p class="text-sm text-gray-600">{{ $item->response }}</p>
                                        <p class="text-xs text-gray-500 mt-2">
                                            Responded: {{ $item->responded_at->format('M d, Y H:i') }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        {{ $feedback->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-portal-layout>
