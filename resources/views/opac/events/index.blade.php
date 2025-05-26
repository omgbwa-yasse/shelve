<x-portal-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Events') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold">Events List</h3>
                        <a href="{{ route('portal.events.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Add Event
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($events as $event)
                            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                                @if($event->image)
                                    <img src="{{ asset('storage/' . $event->image) }}" alt="{{ $event->title }}" class="w-full h-48 object-cover">
                                @endif
                                <div class="p-4">
                                    <h4 class="font-bold text-lg mb-2">{{ $event->title }}</h4>
                                    <div class="text-sm text-gray-600 mb-2">
                                        <i class="fas fa-calendar-alt mr-2"></i>
                                        {{ $event->start_date->format('M d, Y') }}
                                        @if($event->end_date)
                                            - {{ $event->end_date->format('M d, Y') }}
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-600 mb-2">
                                        <i class="fas fa-clock mr-2"></i>
                                        {{ $event->start_time->format('h:i A') }}
                                        @if($event->end_time)
                                            - {{ $event->end_time->format('h:i A') }}
                                        @endif
                                    </div>
                                    <p class="text-gray-600 text-sm mb-4">{{ Str::limit($event->description, 100) }}</p>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-500">{{ $event->location }}</span>
                                        <div class="space-x-2">
                                            <a href="{{ route('portal.events.edit', $event) }}" class="text-blue-500 hover:text-blue-700">Edit</a>
                                            <form action="{{ route('portal.events.destroy', $event) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700" onclick="return confirm('Are you sure?')">Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        {{ $events->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-portal-layout>
