@extends('layouts.app')

@section('content')
    <div class="container-fluid ">
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h1 class="">
                    <i class="bi bi-file-earmark-text"></i> Bordereau de versement
                </h1>
            </div>
            <div class="col-auto">
                <a href="{{ route('slips.create') }}" class="btn btn-primary btn-lg">
                    <i class="bi bi-plus-circle"></i> Nouveau bordereau
                </a>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center bg-light p-3 mb-3">
            <div class="d-flex align-items-center">
                <a href="#" id="cartBtn" class="btn btn-light btn-sm me-2" data-bs-toggle="modal" data-bs-target="#dolliesModal">
                    <i class="bi bi-cart me-1"></i>
                    Chariot
                </a>
                <a href="#" id="exportBtn" class="btn btn-light btn-sm me-2" data-route="{{ route('slips.export') }}">
                    <i class="bi bi-download me-1"></i>
                    Exporter
                </a>
                <a href="#" id="printBtn" class="btn btn-light btn-sm me-2" data-route="">
                    <i class="bi bi-printer me-1"></i>
                    Imprimer
                </a>
            </div>
            <div class="d-flex align-items-center">
                <a href="#" id="checkAllBtn" class="btn btn-light btn-sm">
                    <i class="bi bi-check-square me-1"></i>
                    Tout cocher
                </a>
            </div>
        </div>

        <div id="slipList">
            @foreach ($slips as $slip)
                <div class="card mb-3 shadow-sm hover-shadow transition">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="{{ $slip->id }}" id="slip-{{ $slip->id }}" name="selected_slip[]">
                                </div>
                            </div>
                            <div class="col-md-7">
                                <h5 class="card-title mb-2">
                                    <a href="{{ route('slips.show', $slip->id) }}" class="text-decoration-none text-dark" title="View">
                                        <strong>{{ $slip->code }} : {{ $slip->name }}</strong>
                                    </a>
                                </h5>
                                <p class="card-text mb-2">
                                    <i class="bi bi-info-circle text-muted"></i> {{ Str::limit($slip->description, 150) }}
                                </p>
                                <p class="card-text mb-0">
                                    <i class="bi bi-calendar-event text-muted"></i>
                                    <a href="{{ route('slips-sort')}}?categ=dates&date_exact={{ $slip->created_at->format('Y-m-d') }}" class="text-decoration-none">
                                        {{ $slip->created_at->format('d M Y') }}
                                    </a>
                                    <span class="mx-2">|</span>
                                    <i class="bi bi-building text-muted"></i>
                                    <a href="{{ route('slips-sort')}}?categ=user-organisation&id={{ $slip->userOrganisation->id }}" class="text-decoration-none">
                                        {{ $slip->userOrganisation->name }}
                                    </a>
                                </p>
                            </div>
                            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                @if($slip->is_received == FALSE && $slip->is_approved == FALSE && $slip->is_integrated == FALSE)
                                    <span class="badge bg-secondary">Projet</span>
                                @elseif($slip->is_received == TRUE && $slip->is_approved == FALSE && $slip->is_integrated == FALSE)
                                    <span class="badge bg-primary">Examen</span>
                                @elseif ($slip->is_received == TRUE && $slip->is_approved == TRUE && $slip->is_integrated == FALSE )
                                    <span class="badge bg-warning">Approuvé</span>
                                @elseif ($slip->is_received == TRUE && $slip->is_approved == TRUE && $slip->is_integrated == TRUE)
                                    <span class="badge bg-success">Intégré</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item {{ $slips->onFirstPage() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $slips->previousPageUrl() }}" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                @foreach ($slips->getUrlRange(1, $slips->lastPage()) as $page => $url)
                    <li class="page-item {{ $page == $slips->currentPage() ? 'active' : '' }}">
                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                    </li>
                @endforeach
                <li class="page-item {{ $slips->hasMorePages() ? '' : 'disabled' }}">
                    <a class="page-link" href="{{ $slips->nextPageUrl() }}" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Modal pour les chariots (dollies) -->
    <div class="modal fade" id="dolliesModal" tabindex="-1" aria-labelledby="dolliesModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dolliesModalLabel">Chariot</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="dolliesList">
                        <p>Aucun chariot chargé</p>
                    </div>
                    <div id="dollyForm" style="display: none;">
                        <form id="createDollyForm" action="{{ route('dolly.create') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label">Nom</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="category" class="form-label">Catégories</label>
                                <select class="form-select" id="category" name="category" required>
                                    @foreach ($categories ?? ['slip'] as $category)
                                        <option value="{{ $category }}" {{ $category == 'slip' ? 'selected' : '' }}>
                                            {{ $category }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="d-flex justify-content-between">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-1"></i> Ajouter au chariot
                                </button>
                                <button type="button" class="btn btn-secondary" id="backToListBtn">
                                    <i class="bi bi-arrow-left-circle me-1"></i> Retour à la liste
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Fermer
                    </button>
                    <button type="button" class="btn btn-primary" id="addDollyBtn">
                        <i class="bi bi-plus-circle me-1"></i> Nouveau chariot
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .hover-shadow:hover {
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
    .transition {
        transition: all 0.3s ease;
    }
</style>
@endpush

@push('scripts')
    <script src="{{ asset('js/dollies.js') }}"></script>
    <script src="{{ asset('js/slip.js') }}"></script>
@endpush