@props(['title', 'value', 'icon', 'color' => 'blue', 'trend' => null, 'href' => '#'])

<a href="{{ $href }}" class="block bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow duration-200">
    <div class="p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="p-3 rounded-lg bg-{{ $color }}-100 dark:bg-{{ $color }}-900/20">
                    {!! $icon !!}
                </div>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                        {{ $title }}
                    </dt>
                    <dd class="flex items-baseline">
                        <div class="text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ $value }}
                        </div>
                        @if($trend)
                            <div class="ml-2 flex items-baseline text-sm font-semibold {{ $trend > 0 ? 'text-green-600' : 'text-red-600' }}">
                                @if($trend > 0)
                                    <svg class="self-center flex-shrink-0 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                @else
                                    <svg class="self-center flex-shrink-0 h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                @endif
                                <span class="ml-1">
                                    {{ abs($trend) }}%
                                </span>
                            </div>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</a>
