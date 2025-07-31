@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2 fw-bold text-dark mb-0">
                <i class="bi bi-list-ul me-3 text-primary"></i>
                {{ __('inventory') }} {{ $title ?? ''}}
            </h1>
        </div>

        <!-- Action Toolbar -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body py-3">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <button id="cartBtn" class="btn btn-outline-primary btn-sm d-flex align-items-center"
                            data-bs-toggle="modal" data-bs-target="#dolliesModal">
                        <i class="bi bi-cart me-2"></i>
                        {{ __('cart') }}
                    </button>
                    <button id="exportBtn" class="btn btn-outline-success btn-sm d-flex align-items-center">
                        <i class="bi bi-download me-2"></i>
                        {{ __('export') }}
                    </button>
                    <button id="printBtn" class="btn btn-outline-info btn-sm d-flex align-items-center">
                        <i class="bi bi-printer me-2"></i>
                        {{ __('print') }}
                    </button>
                    <button id="transferBtn" class="btn btn-outline-warning btn-sm d-flex align-items-center">
                        <i class="bi bi-arrow-repeat me-2"></i>
                        {{ __('transfer') }}
                    </button>
                    <button id="communicateBtn" class="btn btn-outline-secondary btn-sm d-flex align-items-center">
                        <i class="bi bi-envelope me-2"></i>
                        {{ __('communicate') }}
                    </button>
                    <div class="ms-auto">
                        <button id="checkAllBtn" class="btn btn-primary btn-sm d-flex align-items-center">
                            <i class="bi bi-check-square me-2"></i>
                            {{ __('checkAll') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Records List -->
        <div id="recordList" class="row g-2 mb-4">
            @foreach ($records as $record)
                <div class="col-12">
                    <div class="card border-0 shadow-sm record-card" style="transition: all 0.3s ease;">
                        <!-- Card Header -->
                        <div class="card-header bg-gradient bg-light border-0 d-flex align-items-center py-2">
                            <div class="form-check me-2">
                                <input class="form-check-input"
                                       type="checkbox"
                                       value="{{$record->id}}"
                                       id="record-{{$record->id}}"
                                       name="selected_record[]" />
                            </div>

                            <button class="btn btn-link btn-sm text-secondary text-decoration-none p-0 me-2 toggle-btn"
                                    type="button"
                                    data-target="details-{{$record->id}}">
                                <i class="bi bi-chevron-down"></i>
                            </button>

                            <div class="flex-grow-1">
                                <a href="{{ route('records.show', $record) }}"
                                   class="text-decoration-none">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="text-primary fw-bold">{{ $record->code }}</span>
                                        <span class="text-dark">{{ Str::limit($record->name, 60) }}</span>
                                        <span class="badge bg-primary-subtle text-primary">{{ $record->level->name }}</span>
                                        <span class="badge bg-success-subtle text-success">{{ $record->status->name ?? 'N/A' }}</span>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <!-- Collapsible Details -->
                        <div class="details-content" id="details-{{$record->id}}" style="display: none; overflow: hidden;">
                            <div class="card-body py-2">
                                @if($record->content)
                                    <div class="mb-2">
                                        <p class="text-muted mb-1 small content-text" id="content-{{$record->id}}">
                                            {{ Str::limit($record->content, 120) }}
                                        </p>
                                        @if (strlen($record->content) > 120)
                                            <a href="#" class="text-primary small content-toggle"
                                               data-target="content-{{$record->id}}"
                                               data-full-text="{{ $record->content }}">
                                                Voir plus...
                                            </a>
                                        @endif
                                    </div>
                                @endif

                                <!-- Compact Metadata -->
                                <div class="d-flex flex-wrap gap-2 small text-muted">
                                    <span><i class="bi bi-hdd-fill me-1 text-info"></i>{{ $record->support->name ?? 'N/A' }}</span>
                                    <span><i class="bi bi-activity me-1 text-warning"></i>{{ $record->activity->name ?? 'N/A' }}</span>
                                    <span><i class="bi bi-calendar-event me-1 text-danger"></i>{{ $record->date_start ?? 'N/A' }} - {{ $record->date_end ?? 'N/A' }}</span>
                                    <span><i class="bi bi-people-fill me-1 text-secondary"></i>{{ $record->authors->pluck('name')->join(', ') ?? 'N/A' }}</span>
                                </div>

                                <!-- Thesaurus Concepts -->
                                @if($record->thesaurusConcepts->isNotEmpty())
                                    <div class="mt-2">
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($record->thesaurusConcepts as $concept)
                                                <span class="badge bg-info-subtle text-info small">
                                                    {{ $concept->preferred_label ?? 'N/A' }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($records->hasPages())
            <div class="d-flex justify-content-center mb-5">
                <nav aria-label="{{ __('pagination') }}">
                    <ul class="pagination pagination-lg shadow-sm">
                        {{-- Previous Page Link --}}
                        @if ($records->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link border-0 bg-light">
                                    <i class="bi bi-chevron-left"></i>
                                </span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link border-0" href="{{ $records->previousPageUrl() }}">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                        @endif

                        {{-- Pagination Elements --}}
                        @php
                            $start = max($records->currentPage() - 2, 1);
                            $end = min($start + 4, $records->lastPage());
                            $start = max($end - 4, 1);
                        @endphp

                        @if($start > 1)
                            <li class="page-item">
                                <a class="page-link border-0" href="{{ $records->url(1) }}">1</a>
                            </li>
                            @if($start > 2)
                                <li class="page-item disabled">
                                    <span class="page-link border-0 bg-light">...</span>
                                </li>
                            @endif
                        @endif

                        @for ($i = $start; $i <= $end; $i++)
                            @if ($i == $records->currentPage())
                                <li class="page-item active">
                                    <span class="page-link border-0 bg-primary">{{ $i }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link border-0" href="{{ $records->url($i) }}">{{ $i }}</a>
                                </li>
                            @endif
                        @endfor

                        @if($end < $records->lastPage())
                            @if($end < $records->lastPage() - 1)
                                <li class="page-item disabled">
                                    <span class="page-link border-0 bg-light">...</span>
                                </li>
                            @endif
                            <li class="page-item">
                                <a class="page-link border-0" href="{{ $records->url($records->lastPage()) }}">
                                    {{ $records->lastPage() }}
                                </a>
                            </li>
                        @endif

                        {{-- Next Page Link --}}
                        @if ($records->hasMorePages())
                            <li class="page-item">
                                <a class="page-link border-0" href="{{ $records->nextPageUrl() }}">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <span class="page-link border-0 bg-light">
                                    <i class="bi bi-chevron-right"></i>
                                </span>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
        @endif
    </div>

    <!-- Modals (keeping original modals but with improved styling) -->

    <!-- Export Modal -->
    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold" id="exportModalLabel">
                        <i class="bi bi-download me-2"></i>{{ __('choosePrintFormat') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex flex-column gap-3">
                        <div class="form-check export-option">
                            <input class="form-check-input" type="radio" name="exportFormat" id="formatExcel" value="excel" checked>
                            <label class="form-check-label w-100 p-3 border rounded-3 bg-light" for="formatExcel">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-file-earmark-excel text-success me-3 fs-4"></i>
                                    <div>
                                        <div class="fw-semibold">Excel</div>
                                        <small class="text-muted">Export au format Excel (.xlsx)</small>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <div class="form-check export-option">
                            <input class="form-check-input" type="radio" name="exportFormat" id="formatEAD" value="ead">
                            <label class="form-check-label w-100 p-3 border rounded-3 bg-light" for="formatEAD">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-file-earmark-code text-info me-3 fs-4"></i>
                                    <div>
                                        <div class="fw-semibold">EAD</div>
                                        <small class="text-muted">Export au format EAD (Encoded Archival Description)</small>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <div class="form-check export-option">
                            <input class="form-check-input" type="radio" name="exportFormat" id="formatSEDA" value="seda">
                            <label class="form-check-label w-100 p-3 border rounded-3 bg-light" for="formatSEDA">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-file-earmark-zip text-warning me-3 fs-4"></i>
                                    <div>
                                        <div class="fw-semibold">SEDA</div>
                                        <small class="text-muted">Export au format SEDA (Standard d'échange de données pour l'archivage)</small>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>{{ __('cancel') }}
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmExport">
                        <i class="bi bi-download me-2"></i>{{ __('export') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Communication Modal -->
    <div class="modal fade" id="communicationModal" tabindex="-1" aria-labelledby="communicationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold" id="communicationModalLabel">
                        <i class="bi bi-envelope me-2"></i>{{ __('createNewCommunication') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('communications.transactions.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label fw-semibold">{{ __('code') }}</label>
                                    <input type="text" class="form-control form-control-lg" id="code" name="code" required placeholder="Entrez le code">
                                </div>
                                <div class="mb-3">
                                    <label for="name" class="form-label fw-semibold">{{ __('name') }}</label>
                                    <input type="text" class="form-control form-control-lg" id="name" name="name" required placeholder="Entrez le nom">
                                </div>
                                <div class="mb-3">
                                    <label for="gcontent" class="form-label fw-semibold">{{ __('content') }}</label>
                                    <textarea class="form-control" id="gcontent" name="gcontent" rows="4" placeholder="Décrivez le contenu"></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="user_id" class="form-label fw-semibold">{{ __('user') }}</label>
                                    <select class="form-select form-select-lg" id="user_id" name="user_id" required>
                                        <option value="">Sélectionnez un utilisateur</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="return_date" class="form-label fw-semibold">{{ __('returnDate') }}</label>
                                    <input type="date" class="form-control form-control-lg" id="return_date" name="return_date" required>
                                </div>
                                <div class="mb-3">
                                    <label for="user_organisation_id" class="form-label fw-semibold">{{ __('userOrganization') }}</label>
                                    <select class="form-select form-select-lg" id="user_organisation_id" name="user_organisation_id" required>
                                        <option value="">Sélectionnez une organisation</option>
                                        @foreach($organisations as $organisation)
                                            <option value="{{ $organisation->id }}">{{ $organisation->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="card border-0 bg-light">
                                    <div class="card-header bg-transparent border-0">
                                        <h6 class="mb-0 fw-semibold">{{ __('selectedRecords') }}</h6>
                                    </div>
                                    <div class="card-body" id="communicationSelectedRecords">
                                        <!-- Le contenu sera injecté dynamiquement -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-2"></i>{{ __('close') }}
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>{{ __('save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Transfer Modal -->
    <div class="modal fade" id="transferModal" tabindex="-1" aria-labelledby="transferModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold" id="transferModalLabel">
                        <i class="bi bi-arrow-left-right me-2"></i>{{ __('createNewSlip') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('slips.storetransfert') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label fw-semibold">{{ __('code') }}</label>
                                    <input type="text" class="form-control form-control-lg" id="code" name="code" required placeholder="Entrez le code">
                                </div>
                                <div class="mb-3">
                                    <label for="name" class="form-label fw-semibold">{{ __('name') }}</label>
                                    <input type="text" class="form-control form-control-lg" id="name" name="name" required placeholder="Entrez le nom">
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label fw-semibold">{{ __('description') }}</label>
                                    <textarea class="form-control" id="description" name="description" rows="4" placeholder="Décrivez le transfert"></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="officer_organisation_id" class="form-label fw-semibold">{{ __('officerOrganization') }}</label>
                                    <select class="form-select form-select-lg" id="officer_organisation_id" name="officer_organisation_id" required>
                                        <option value="">Sélectionnez une organisation</option>
                                        @foreach($organisations as $organisation)
                                            <option value="{{ $organisation->id }}">{{ $organisation->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="user_organisation_id" class="form-label fw-semibold">{{ __('userOrganization') }}</label>
                                    <select class="form-select form-select-lg" id="user_organisation_id" name="user_organisation_id" required>
                                        <option value="">Sélectionnez une organisation</option>
                                        @foreach($organisations as $organisation)
                                            <option value="{{ $organisation->id }}">{{ $organisation->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="user_id" class="form-label fw-semibold">{{ __('user') }}</label>
                                    <select class="form-select form-select-lg" id="user_id" name="user_id" required>
                                        <option value="">Sélectionnez un utilisateur</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="slip_status_id" class="form-label fw-semibold">{{ __('slipStatus') }}</label>
                                    <select class="form-select form-select-lg" id="slip_status_id" name="slip_status_id" required>
                                        <option value="">Sélectionnez un statut</option>
                                        @foreach($slipStatuses as $status)
                                            <option value="{{ $status->id }}">{{ $status->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="card border-0 bg-light">
                                    <div class="card-header bg-transparent border-0">
                                        <h6 class="mb-0 fw-semibold">{{ __('selectedRecords') }}</h6>
                                    </div>
                                    <div class="card-body" id="transferSelectedRecords">
                                        <!-- Le contenu sera injecté dynamiquement -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-2"></i>{{ __('cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>{{ __('save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Dollies Modal -->
    <div class="modal fade" id="dolliesModal" tabindex="-1" aria-labelledby="dolliesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold" id="dolliesModalLabel">
                        <i class="bi bi-cart me-2"></i>{{ __('cart') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="dolliesList">
                        <div class="text-center py-4">
                            <i class="bi bi-cart-x text-muted display-4"></i>
                            <p class="text-muted mt-2">{{ __('noCartLoaded') }}</p>
                        </div>
                    </div>
                    <div id="dollyForm" style="display: none;">
                        <form id="createDollyForm" action="{{ route('dolly.create') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label fw-semibold">{{ __('name') }}</label>
                                <input type="text" class="form-control form-control-lg" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label fw-semibold">{{ __('description') }}</label>
                                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="category" class="form-label fw-semibold">{{ __('categories') }}</label>
                                <select class="form-select form-select-lg" id="category" name="category" required>
                                    @foreach ($categories ?? ['record'] as $category)
                                        <option value="{{ $category }}" {{ $category == 'record' ? 'selected' : '' }}>
                                            {{ $category }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="d-flex justify-content-between gap-2">
                                <button type="submit" class="btn btn-primary flex-grow-1">
                                    <i class="bi bi-plus-circle me-2"></i>{{ __('addToCart') }}
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="backToListBtn">
                                    <i class="bi bi-arrow-left"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>{{ __('close') }}
                    </button>
                    <button type="button" class="btn btn-primary" id="addDollyBtn">
                        <i class="bi bi-plus-circle me-2"></i>{{ __('newCart') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        :root {
            --bs-primary: #0d6efd;
            --bs-success: #198754;
            --bs-info: #0dcaf0;
            --bs-warning: #ffc107;
            --bs-danger: #dc3545;
            --bs-secondary: #6c757d;
        }

        .record-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 8px;
            overflow: hidden;
        }

        .record-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.08) !important;
        }

        .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
            border-bottom: 1px solid rgba(0,0,0,0.05) !important;
        }

        .toggle-btn .bi-chevron-down {
            transition: transform 0.3s ease;
            font-size: 0.9rem;
        }

        .toggle-btn.expanded .bi-chevron-down {
            transform: rotate(180deg);
        }

        .details-content {
            transition: all 0.3s ease;
        }

        .form-check-input {
            width: 1rem;
            height: 1rem;
        }

        .badge {
            font-size: 0.65rem;
            font-weight: 500;
            padding: 0.25rem 0.5rem;
        }

        .metadata-item {
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
        }

        .metadata-item:last-child {
            border-bottom: none;
        }

        .pagination-lg .page-link {
            padding: 0.75rem 1rem;
            font-size: 1rem;
            border-radius: 8px;
            margin: 0 2px;
            transition: all 0.2s ease;
        }

        .pagination-lg .page-link:hover {
            background-color: var(--bs-primary);
            border-color: var(--bs-primary);
            color: white;
            transform: translateY(-1px);
        }

        .export-option label {
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .export-option input[type="radio"]:checked + label {
            background-color: var(--bs-primary) !important;
            color: white;
            border-color: var(--bs-primary) !important;
        }

        .export-option label:hover {
            background-color: var(--bs-light);
            border-color: var(--bs-primary);
        }

        .btn {
            transition: all 0.2s ease;
            border-radius: 8px;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .modal-content {
            border-radius: 16px;
        }

        .modal-header {
            border-radius: 16px 16px 0 0;
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.1);
        }

        .text-decoration-none:hover {
            text-decoration: underline !important;
        }

        @media (max-width: 768px) {
            .container-fluid {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .card-header {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 1rem;
            }

            .metadata-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.25rem;
            }

            .pagination-lg {
                font-size: 0.875rem;
            }

            .pagination-lg .page-link {
                padding: 0.5rem 0.75rem;
            }
        }

        .content-text {
            line-height: 1.6;
        }

        .content-toggle {
            font-size: 0.875rem;
            font-weight: 500;
        }

        .bg-gradient {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
        }
    </style>
@endsection

@push('scripts')
    <script src="{{ asset('js/dollies.js') }}"></script>
    <script src="{{ asset('js/records.js') }}"></script>

    <script>
        // Animation d'entrée pour les cartes
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.record-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';

                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });

            // Gestion des dropdown avec JS natif
            document.querySelectorAll('.toggle-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const targetId = this.dataset.target;
                    const targetElement = document.getElementById(targetId);
                    const isVisible = targetElement.style.display !== 'none';

                    if (isVisible) {
                        // Fermer
                        targetElement.style.height = targetElement.scrollHeight + 'px';
                        setTimeout(() => {
                            targetElement.style.height = '0px';
                            setTimeout(() => {
                                targetElement.style.display = 'none';
                                targetElement.style.height = '';
                            }, 300);
                        }, 10);
                        this.classList.remove('expanded');
                    } else {
                        // Ouvrir
                        targetElement.style.display = 'block';
                        targetElement.style.height = '0px';
                        const fullHeight = targetElement.scrollHeight + 'px';
                        setTimeout(() => {
                            targetElement.style.height = fullHeight;
                            setTimeout(() => {
                                targetElement.style.height = '';
                            }, 300);
                        }, 10);
                        this.classList.add('expanded');
                    }
                });
            });
        });

        // Amélioration de l'animation des checkboxes
        document.querySelectorAll('.form-check-input').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const card = this.closest('.record-card');
                if (this.checked) {
                    card.style.background = 'linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%)';
                    card.style.borderColor = 'var(--bs-primary)';
                } else {
                    card.style.background = '';
                    card.style.borderColor = '';
                }
            });
        });
    </script>
@endpush
