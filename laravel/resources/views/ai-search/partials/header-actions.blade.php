{{-- Actions en haut à droite de l'interface --}}
<div class="text-end">
    <a href="{{ route('ai-search.documentation') }}" class="btn btn-outline-info me-2" target="_blank">
        <i class="bi bi-book me-1"></i>{{ __('Documentation') }}
    </a>
    @if(config('app.debug'))
    <a href="{{ route('ai-search.test.interface') }}" class="btn btn-outline-warning me-2" target="_blank">
        <i class="bi bi-check-square me-1"></i>{{ __('Tests') }}
    </a>
    @endif
    <button class="btn btn-outline-secondary" id="clearChat">
        <i class="bi bi-trash me-1"></i>{{ __('Clear Chat') }}
    </button>
</div>
