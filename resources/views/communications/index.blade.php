@extends('layouts.app')

@section('content')
    <div class="container-fluid ">
        <h1><i class="bi bi-file-earmark-spreadsheet"></i> {{ __('Communications') }} {{ $title ?? ''}}</h1>
        <a href="{{ route('transactions.create') }}" class="btn btn-primary mb-3">
            <i class="bi bi-plus-circle"></i> {{ __('Fill a form') }}
        </a>

        <div class="d-flex justify-content-between align-items-center bg-light p-3 mb-3">
            <div class="d-flex align-items-center">
                <a href="#" id="cartBtn" class="btn btn-light btn-sm me-2" data-bs-toggle="modal" data-bs-target="#dolliesModal">
                    <i class="bi bi-cart me-1"></i>
                    {{ __('Cart') }}
                </a>
                <a href="#" id="exportBtn" class="btn btn-light btn-sm me-2" data-route="{{ route('communications.export') }}">
                    <i class="bi bi-download me-1"></i>
                    {{ __('Export') }}
                </a>
                <a href="#" id="printBtn" class="btn btn-light btn-sm me-2" data-route="{{ route('communications.print') }}">
                    <i class="bi bi-printer me-1"></i>
                    {{ __('Print') }}
                </a>
            </div>
            <div class="d-flex align-items-center">
                <a href="#" id="checkAllBtn" class="btn btn-light btn-sm">
                    <i class="bi bi-check-square me-1"></i>
                    {{ __('Check all') }}
                </a>
            </div>
        </div>
        <div class="row">
            <div id="communicationsList" class="mb-4">
                @foreach ($communications as $communication)
                    <div class="mb-3" style="transition: all 0.3s ease; transform: translateZ(0);">
                        <div class="card-header bg-light d-flex align-items-center py-2" style="border-bottom: 1px solid rgba(0,0,0,0.125);">
                            <div class="form-check me-3">
                                <input class="form-check-input" type="checkbox" value="{{ $communication->id }}" id="communication-{{ $communication->id }}" name="selected_communication[]" />
                            </div>
                            <button class="btn btn-link btn-sm text-secondary text-decoration-none p-0 me-3" type="button" data-bs-toggle="collapse" data-bs-target="#details-{{ $communication->id }}" aria-expanded="false" aria-controls="details-{{ $communication->id }}">
                                <i class="bi bi-chevron-down fs-5"></i>
                            </button>
                            <h4 class="card-title flex-grow-1 m-0 text-primary" for="communication-{{ $communication->id }}">
                                <a href="{{ route('transactions.show', $communication->id ?? '') }}" class="text-decoration-none text-dark">
                                    <span class="fs-5 fw-semibold">{{ $communication->code ?? 'N/A' }}</span>
                                    <span class="fs-5"> : {{ $communication->name ?? 'N/A' }}</span>
                                    @if($communication->status)
                                        @switch($communication->status->id)
                                            @case(1)
                                                <span class="badge ms-2 bg-warning">
                                                    <i class="bi bi-clock"></i> [{{ $communication->status->name }}]
                                                </span>
                                                @break
                                            @case(2)
                                                <span class="badge ms-2 bg-success">
                                                    <i class="bi bi-check-circle"></i> [{{ $communication->status->name }}]
                                                </span>
                                                @break
                                            @case(3)
                                                <span class="badge ms-2 bg-danger">
                                                    <i class="bi bi-x-circle"></i> [{{ $communication->status->name }}]
                                                </span>
                                                @break
                                            @case(4)
                                                <span class="badge ms-2 bg-info">
                                                    <i class="bi bi-eye"></i> [{{ $communication->status->name }}]
                                                </span>
                                                @break
                                            @case(5)
                                                <span class="badge ms-2 bg-secondary">
                                                    <i class="bi bi-archive"></i> [{{ $communication->status->name }}]
                                                </span>
                                                @break
                                        @endswitch
                                    @endif
                                </a>
                            </h4>
                        </div>
                        <div class="collapse" id="details-{{ $communication->id }}">
                            <div class="card-body bg-white">
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <p class="mb-2"><i class="bi bi-card-text me-2 text-primary"></i><strong>{{ __('Content') }} :</strong> {{ $communication->content ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-2">
                                            <i class="bi bi-person-fill me-2 text-primary"></i><strong>{{ __('Requester') }} :</strong>
                                            <a href="{{ route('communications-sort') }}?user={{ $communication->user->id }}">{{ $communication->user->name ?? 'N/A' }}</a>
                                            (<a href="{{ route('communications-sort') }}?user_organisation={{ $communication->userOrganisation->id ?? '' }}">{{ $communication->userOrganisation->name ?? 'N/A' }}</a>)
                                        </p>
                                        <p class="mb-2">
                                            <i class="bi bi-person-badge-fill me-2 text-primary"></i><strong>{{ __('Operator') }} :</strong>
                                            <a href="{{ route('communications-sort') }}?operator={{ $communication->operator->id }}">{{ $communication->operator->name ?? 'N/A' }}</a>
                                            (<a href="{{ route('communications-sort') }}?operator_organisation={{ $communication->operatorOrganisation->id ?? '' }}">{{ $communication->operatorOrganisation->name ?? 'N/A' }}</a>)
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-2">
                                            <i class="bi bi-calendar-event me-2 text-primary"></i><strong>{{ __('Return date') }} :</strong> {{ $communication->return_date ?? 'N/A' }}
                                        </p>
                                        <p class="mb-2">
                                            <i class="bi bi-calendar-check me-2 text-primary"></i><strong>{{ __('Effective return date') }} :</strong> {{ $communication->return_effective ?? 'N/A' }}
                                        </p>
                                        <p class="mb-2">
                                            <i class="bi bi-info-circle-fill me-2 text-primary"></i><strong>{{ __('Status') }} :</strong>
                                            @if($communication->status)
                                                <a href="{{ route('communications-sort') }}?status={{ $communication->status->id }}">{{ $communication->status->name }}</a>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <footer class="bg-light py-3">
        <div class="container">
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <li class="page-item {{ $communications->currentPage() == 1 ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $communications->previousPageUrl() }}" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    @for ($i = 1; $i <= $communications->lastPage(); $i++)
                        <li class="page-item {{ $communications->currentPage() == $i ? 'active' : '' }}">
                            <a class="page-link" href="{{ $communications->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor
                    <li class="page-item {{ $communications->currentPage() == $communications->lastPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $communications->nextPageUrl() }}" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </footer>

    <!-- Modal pour les chariots (dollies) -->
    <div class="modal fade" id="dolliesModal" tabindex="-1" aria-labelledby="dolliesModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dolliesModalLabel">{{ __('Cart') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="dolliesList">
                        <p>{{ __('No cart loaded') }}</p>
                    </div>
                    <div id="dollyForm" style="display: none;">
                        <form id="createDollyForm" action="{{ route('dolly.create') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label">{{ __('Name') }}</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">{{ __('Description') }}</label>
                                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="category" class="form-label">{{ __('Categories') }}</label>
                                <select class="form-select" id="category" name="category" required>
                                    @foreach ($categories ?? ['communication'] as $category)
                                        <option value="{{ $category }}" {{ $category == 'communication' ? 'selected' : '' }}>
                                            {{ $category }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="d-flex justify-content-between">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-1"></i> {{ __('Add to cart') }}
                                </button>
                                <button type="button" class="btn btn-secondary" id="backToListBtn">
                                    <i class="bi bi-arrow-left-circle me-1"></i> {{ __('Back to list') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> {{ __('Close') }}
                    </button>
                    <button type="button" class="btn btn-primary" id="addDollyBtn">
                        <i class="bi bi-plus-circle me-1"></i> {{ __('New cart') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/dollies.js') }}"></script>
    <script src="{{ asset('js/communications.js') }}"></script>
@endpush
