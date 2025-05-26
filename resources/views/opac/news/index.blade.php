<x-portal-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('News') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold">News List</h3>
                        <a href="{{ route('portal.news.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Add News
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($news as $item)
                            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                                @if($item->image)
                                    <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->title }}" class="w-full h-48 object-cover">
                                @endif
                                <div class="p-4">
                                    <h4 class="font-bold text-lg mb-2">{{ $item->title }}</h4>
                                    <p class="text-gray-600 text-sm mb-4">{{ Str::limit($item->content, 100) }}</p>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-500">{{ $item->created_at->format('M d, Y') }}</span>
                                        <div class="space-x-2">
                                            <a href="{{ route('portal.news.edit', $item) }}" class="text-blue-500 hover:text-blue-700">Edit</a>
                                            <form action="{{ route('portal.news.destroy', $item) }}" method="POST" class="inline">
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
                        {{ $news->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-portal-layout>
