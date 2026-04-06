@extends('layouts.app')
@section('content')
<div class="container-fluid px-4 py-3">

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h5 class="mb-0 fw-semibold">Typologies de Courrier</h5>
            <small class="text-muted">{{ $mailTypologies->total() }} typologie(s) au total</small>
        </div>
        <a href="{{ route('mail-typology.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Ajouter
        </a>
    </div>

    {{-- Grid --}}
    <div class="row g-2">
        @forelse ($mailTypologies as $mailTypology)
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2">
            <div class="card h-100 border shadow-sm typology-card">
                <div class="card-body p-2">
                    <div class="d-flex align-items-start justify-content-between gap-1 mb-1">
                        <span class="badge bg-secondary text-white text-uppercase" style="font-size:.65rem;letter-spacing:.04em;">
                            {{ $mailTypology->code ?? 'N/A' }}
                        </span>
                        <a href="{{ route('mail-typology.edit', $mailTypology->id) }}"
                           class="btn btn-outline-secondary btn-xs p-0 px-1" style="font-size:.7rem;line-height:1.4;">
                            <i class="bi bi-pencil"></i>
                        </a>
                    </div>
                    <p class="mb-0 fw-semibold text-truncate" style="font-size:.82rem;" title="{{ $mailTypology->name }}">
                        {{ $mailTypology->name }}
                    </p>
                    @if($mailTypology->description)
                    <p class="mb-1 text-muted text-truncate" style="font-size:.72rem;" title="{{ $mailTypology->description }}">
                        {{ $mailTypology->description }}
                    </p>
                    @endif
                    @if($mailTypology->activity)
                    <span class="badge bg-light text-dark border" style="font-size:.65rem;">
                        <i class="bi bi-tag me-1"></i>{{ $mailTypology->activity->name }}
                    </span>
                    @endif
                </div>
                <div class="card-footer p-1 bg-transparent border-top d-flex gap-1">
                    <a href="{{ route('mail-select-typologies', ['typology' => $mailTypology->id, 'type' => 'incoming']) }}"
                       class="btn btn-outline-primary btn-xs flex-fill text-center" style="font-size:.68rem;">
                        <i class="bi bi-inbox me-1"></i>Reçu
                    </a>
                    <a href="{{ route('mail-select-typologies', ['typology' => $mailTypology->id, 'type' => 'outgoing']) }}"
                       class="btn btn-outline-success btn-xs flex-fill text-center" style="font-size:.68rem;">
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
    @if($mailTypologies->lastPage() > 1)
    <div class="d-flex justify-content-center mt-3">
        <nav aria-label="Page navigation">
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item {{ $mailTypologies->onFirstPage() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $mailTypologies->previousPageUrl() }}">&laquo;</a>
                </li>
                @foreach ($mailTypologies->getUrlRange(1, $mailTypologies->lastPage()) as $page => $url)
                <li class="page-item {{ $page == $mailTypologies->currentPage() ? 'active' : '' }}">
                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                </li>
                @endforeach
                <li class="page-item {{ $mailTypologies->hasMorePages() ? '' : 'disabled' }}">
                    <a class="page-link" href="{{ $mailTypologies->nextPageUrl() }}">&raquo;</a>
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
    transform: translateY(-1px);
}
</style>
@endsection
