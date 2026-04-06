@extends('layouts.app')
@section('content')
<div class="container-fluid px-4 py-3">

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h5 class="mb-0 fw-semibold">Typologies de Courrier</h5>
            <small class="text-muted">{{ $typologies->total() }} typologie(s)</small>
        </div>
    </div>

    {{-- Grid compact --}}
    <div class="row g-2">
        @forelse ($typologies as $typology)
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2">
            <div class="card h-100 border shadow-sm typology-card">
                <div class="card-body p-2">
                    <div class="mb-1">
                        <span class="badge bg-secondary text-uppercase" style="font-size:.65rem;letter-spacing:.04em;">
                            {{ $typology->code ?? 'N/A' }}
                        </span>
                    </div>
                    <p class="mb-1 fw-semibold text-truncate" style="font-size:.82rem;" title="{{ $typology->name }}">
                        {{ $typology->name }}
                    </p>
                    @if($typology->activity ?? null)
                    <span class="badge bg-light text-dark border" style="font-size:.65rem;">
                        <i class="bi bi-tag me-1"></i>{{ $typology->activity->name }}
                    </span>
                    @endif
                </div>
                <div class="card-footer p-1 bg-transparent border-top d-flex gap-1">
                    <a href="{{ route('mails.sort', ['typology_id' => $typology->id, 'type' => 'received']) }}"
                       class="btn btn-outline-primary flex-fill text-center" style="font-size:.68rem;padding:.2rem .3rem;">
                        <i class="bi bi-inbox me-1"></i>Reçu
                    </a>
                    <a href="{{ route('mails.sort', ['typology_id' => $typology->id, 'type' => 'send']) }}"
                       class="btn btn-outline-success flex-fill text-center" style="font-size:.68rem;padding:.2rem .3rem;">
                        <i class="bi bi-send me-1"></i>Émis
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-light text-center text-muted py-4">
                <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                Aucune typologie trouvée.
            </div>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($typologies->lastPage() > 1)
    <div class="d-flex justify-content-center mt-3">
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item {{ $typologies->onFirstPage() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $typologies->previousPageUrl() }}">&laquo;</a>
                </li>
                @foreach ($typologies->getUrlRange(1, $typologies->lastPage()) as $page => $url)
                <li class="page-item {{ $page == $typologies->currentPage() ? 'active' : '' }}">
                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                </li>
                @endforeach
                <li class="page-item {{ $typologies->hasMorePages() ? '' : 'disabled' }}">
                    <a class="page-link" href="{{ $typologies->nextPageUrl() }}">&raquo;</a>
                </li>
            </ul>
        </nav>
    </div>
    @endif

</div>

<style>
.typology-card {
    transition: box-shadow .15s, transform .15s;
}
.typology-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,.1) !important;
    transform: translateY(-2px);
}
</style>
@endsection
