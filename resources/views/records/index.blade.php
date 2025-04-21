@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1 class="mb-4"><i class="bi bi-list-ul me-2"></i>{{ __('inventory') }} {{ $title ?? ''}}</h1>
        <div class="d-flex justify-content-between align-items-center bg-light p-3 mb-3">
            <div class="d-flex align-items-center">
                <a href="#" id="cartBtn" class="btn btn-light btn-sm me-2" data-bs-toggle="modal" data-bs-target="#dolliesModal">
                    <i class="bi bi-cart me-1"></i>
                    {{ __('cart') }}
                </a>
                <a href="#" id="exportBtn" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-download me-1"></i>
                    {{ __('export') }}
                </a>
                <a href="#" id="printBtn" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-printer me-1"></i>
                    {{ __('print') }}
                </a>
                <a href="#" id="transferBtn" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-arrow-repeat me-1"></i>
                    {{ __('transfer') }}
                </a>
                <a href="#" id="communicateBtn" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-envelope me-1"></i>
                    {{ __('communicate') }}
                </a>
                <a href="#" id="checkAllBtn" class="btn btn-light btn-sm">
                    <i class="bi bi-check-square me-1"></i>
                    {{ __('checkAll') }}
                </a>
            </div>
        </div>

        <div id="recordList" class="mb-4">
            @foreach ($records as $record)
                <div class="mb-3" style="transition: all 0.3s ease; transform: translateZ(0);">
                    <div class="card-header bg-light d-flex align-items-center py-2" style="border-bottom: 1px solid rgba(0,0,0,0.125);">
                        <div class="form-check me-3">
                            <input class="form-check-input" type="checkbox" value="{{$record->id}}" id="record-{{$record->id}}" name="selected_record[]" />
                        </div>
                        <button class="btn btn-link btn-sm text-secondary text-decoration-none p-0 me-3" type="button" data-bs-toggle="collapse" data-bs-target="#details-{{$record->id}}" aria-expanded="false" aria-controls="details-{{$record->id}}">
                            <i class="bi bi-chevron-down fs-5"></i>
                        </button>
                        <h4 class="card-title flex-grow-1 m-0 text-primary" for="record-{{$record->id}}">
                            <a href="{{ route('records.show', $record) }}" class="text-decoration-none text-dark">
                                <span class="fs-5 fw-semibold">{{ $record->code }}</span>
                                <span class="fs-5"> : {{ $record->name }}</span>
                                <span class="badge bg-secondary ms-2">{{ $record->level->name }}</span>
                            </a>
                        </h4>
                    </div>
                    <div class="collapse" id="details-{{$record->id}}">
                        <div class="card-body bg-white">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <p class="mb-2"><i class="bi bi-card-text me-2 text-primary"></i><strong>{{ __('content') }} :</strong>
                                        <span class="content-text" id="content-{{$record->id}}">
                                    {{ Str::limit($record->content, 200) }}
                                </span>
                                        @if (strlen($record->content) > 200)
                                            <a href="#" class="text-primary content-toggle" data-target="content-{{$record->id}}" data-full-text="{{ $record->content }}">Voir plus</a>
                                        @endif
                                    </p>
                                </div>
                                <div class="">
                                    <p class="mb-2"><i class="bi bi-bar-chart-fill me-2 text-primary"></i><strong>{{ __('descriptionLevel') }} :</strong> <a href="{{ route('records.sort')}}?categ=level&id={{ $record->level->id ?? ''}}">{{ $record->level->name ?? 'N/A' }}</a>
                                   <i class="bi bi-flag-fill me-2 text-primary"></i><strong>{{ __('status') }} :</strong> <a href="{{route('records.sort')}}?categ=status&id={{ $record->status->id ?? 'N/A' }}">{{ $record->status->name ?? 'N/A' }}</a>
                                    <i class="bi bi-hdd-fill me-2 text-primary"></i><strong>{{ __('support') }} :</strong> <a href="{{ route('records.sort')}}?categ=support&id={{ $record->support->id ?? 'N/A' }}">{{ $record->support->name ?? 'N/A' }}</a>
                                    <i class="bi bi-activity me-2 text-primary"></i><strong>{{ __('activity') }} :</strong> <a href="{{ route('records.sort')}}?categ=activity&id={{ $record->activity->id ?? 'N/A' }}">{{ $record->activity->name ?? 'N/A' }}</a>
                                    <i class="bi bi-calendar-event me-2 text-primary"></i><strong>{{ __('dates') }} :</strong> <a href="{{ route('records.sort')}}?categ=dates&id=">{{ $record->date_start ?? 'N/A' }} - {{ $record->date_end ?? 'N/A' }}</a>
                                    <i class="bi bi-geo-alt-fill me-2 text-primary"></i><strong>{{ __('container') }} :</strong> <a href="{{ route('records.sort')}}?categ=container&id={{ $record->container->id ?? 'none' }}">{{ $record->container->name ?? __('notConditioned') }}</a>
                                        <br>  <i class="bi bi-people-fill me-2 text-primary"></i><strong>{{ __('producer') }} :</strong> <a href="{{ route('records.sort')}}?categ=authors&id={{ $record->authors->pluck('id')->join('') }}">{{ $record->authors->pluck('name')->join(', ') ?? 'N/A' }}</a>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <p class="mb-0">
                                        <strong>{{ __('headings') }} : </strong>
                                        @foreach($record->terms as $index => $term)
                                            <a href="{{ route('records.sort')}}?categ=term&id={{ $term->id ?? 'N/A' }}" class="badge bg-info text-decoration-none">{{ $term->name ?? 'N/A' }}</a>
                                            @if(!$loop->last)
                                                {{ " " }}
                                            @endif
                                        @endforeach
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <footer class="bg-light py-3">
        <div class="container">
            <nav aria-label="{{ __('pagination') }}">
                <ul class="pagination justify-content-center">
                    <li class="page-item {{ $records->currentPage() == 1 ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $records->previousPageUrl() }}" aria-label="{{ __('previous') }}">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    @for ($i = 1; $i <= $records->lastPage(); $i++)
                        <li class="page-item {{ $records->currentPage() == $i ? 'active' : '' }}">
                            <a class="page-link" href="{{ $records->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor
                    <li class="page-item {{ $records->currentPage() == $records->lastPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $records->nextPageUrl() }}" aria-label="{{ __('next') }}">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </footer>

    <!-- Modal d'export -->
    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="exportModalLabel">{{ __('choosePrintFormat') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex flex-column gap-3">
                        <div class="form-check p-3 border rounded hover-bg-light">
                            <input class="form-check-input" type="radio" name="exportFormat" id="formatExcel" value="excel" checked>
                            <label class="form-check-label w-100" for="formatExcel">
                                <i class="bi bi-file-earmark-excel me-2"></i>Excel
                                <small class="d-block text-muted mt-1">Export au format Excel (.xlsx)</small>
                            </label>
                        </div>
                        <div class="form-check p-3 border rounded hover-bg-light">
                            <input class="form-check-input" type="radio" name="exportFormat" id="formatEAD" value="ead">
                            <label class="form-check-label w-100" for="formatEAD">
                                <i class="bi bi-file-earmark-code me-2"></i>EAD
                                <small class="d-block text-muted mt-1">Export au format EAD (Encoded Archival Description)</small>
                            </label>
                        </div>
                        <div class="form-check p-3 border rounded hover-bg-light">
                            <input class="form-check-input" type="radio" name="exportFormat" id="formatSEDA" value="seda">
                            <label class="form-check-label w-100" for="formatSEDA">
                                <i class="bi bi-file-earmark-zip me-2"></i>SEDA
                                <small class="d-block text-muted mt-1">Export au format SEDA (Standard d'échange de données pour l'archivage)</small>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('cancel') }}</button>
                    <button type="button" class="btn btn-primary" id="confirmExport">
                        <i class="bi bi-download me-2"></i>{{ __('export') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de communication -->
    <div class="modal fade" id="communicationModal" tabindex="-1" aria-labelledby="communicationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="communicationModalLabel">
                        <i class="bi bi-envelope me-2"></i>{{ __('createNewCommunication') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('transactions.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label fw-bold">{{ __('code') }}</label>
                                    <input type="text" class="form-control" id="code" name="code" required placeholder="Entrez le code">
                                </div>
                                <div class="mb-3">
                                    <label for="name" class="form-label fw-bold">{{ __('name') }}</label>
                                    <input type="text" class="form-control" id="name" name="name" required placeholder="Entrez le nom">
                                </div>
                                <div class="mb-3">
                                    <label for="gcontent" class="form-label fw-bold">{{ __('content') }}</label>
                                    <textarea class="form-control" id="gcontent" name="gcontent" rows="4" placeholder="Décrivez le contenu"></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="user_id" class="form-label fw-bold">{{ __('user') }}</label>
                                    <select class="form-select" id="user_id" name="user_id" required>
                                        <option value="">Sélectionnez un utilisateur</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="return_date" class="form-label fw-bold">{{ __('returnDate') }}</label>
                                    <input type="date" class="form-control" id="return_date" name="return_date" required>
                                </div>
                                <div class="mb-3">
                                    <label for="user_organisation_id" class="form-label fw-bold">{{ __('userOrganization') }}</label>
                                    <select class="form-select" id="user_organisation_id" name="user_organisation_id" required>
                                        <option value="">Sélectionnez une organisation</option>
                                        @foreach($organisations as $organisation)
                                            <option value="{{ $organisation->id }}">{{ $organisation->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">{{ __('selectedRecords') }}</h6>
                                    </div>
                                    <div class="card-body" id="communicationSelectedRecords">
                                        <!-- Le contenu sera injecté dynamiquement -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('close') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>{{ __('save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de transfert -->
    <div class="modal fade" id="transferModal" tabindex="-1" aria-labelledby="transferModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="transferModalLabel">
                        <i class="bi bi-arrow-left-right me-2"></i>{{ __('createNewSlip') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('slips.storetransfert') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label fw-bold">{{ __('code') }}</label>
                                    <input type="text" class="form-control" id="code" name="code" required placeholder="Entrez le code">
                                </div>
                                <div class="mb-3">
                                    <label for="name" class="form-label fw-bold">{{ __('name') }}</label>
                                    <input type="text" class="form-control" id="name" name="name" required placeholder="Entrez le nom">
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label fw-bold">{{ __('description') }}</label>
                                    <textarea class="form-control" id="description" name="description" rows="4" placeholder="Décrivez le transfert"></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="officer_organisation_id" class="form-label fw-bold">{{ __('officerOrganization') }}</label>
                                    <select class="form-select" id="officer_organisation_id" name="officer_organisation_id" required>
                                        <option value="">Sélectionnez une organisation</option>
                                        @foreach($organisations as $organisation)
                                            <option value="{{ $organisation->id }}">{{ $organisation->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="user_organisation_id" class="form-label fw-bold">{{ __('userOrganization') }}</label>
                                    <select class="form-select" id="user_organisation_id" name="user_organisation_id" required>
                                        <option value="">Sélectionnez une organisation</option>
                                        @foreach($organisations as $organisation)
                                            <option value="{{ $organisation->id }}">{{ $organisation->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="user_id" class="form-label fw-bold">{{ __('user') }}</label>
                                    <select class="form-select" id="user_id" name="user_id" required>
                                        <option value="">Sélectionnez un utilisateur</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="slip_status_id" class="form-label fw-bold">{{ __('slipStatus') }}</label>
                                    <select class="form-select" id="slip_status_id" name="slip_status_id" required>
                                        <option value="">Sélectionnez un statut</option>
                                        @foreach($slipStatuses as $status)
                                            <option value="{{ $status->id }}">{{ $status->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">{{ __('selectedRecords') }}</h6>
                                    </div>
                                    <div class="card-body" id="transferSelectedRecords">
                                        <!-- Le contenu sera injecté dynamiquement -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('cancel') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>{{ __('save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal pour les chariots (dollies) -->
    <div class="modal fade" id="dolliesModal" tabindex="-1" aria-labelledby="dolliesModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dolliesModalLabel">{{ __('cart') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="dolliesList">
                        <p>{{ __('noCartLoaded') }}</p>
                    </div>
                    <div id="dollyForm" style="display: none;">
                        <form id="createDollyForm" action="{{ route('dolly.create') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label">{{ __('name') }}</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">{{ __('description') }}</label>
                                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="category" class="form-label">{{ __('categories') }}</label>
                                <select class="form-select" id="category" name="category" required>
                                    @foreach ($categories ?? ['record'] as $category)
                                        <option value="{{ $category }}" {{ $category == 'record' ? 'selected' : '' }}>
                                            {{ $category }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="d-flex justify-content-between">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-1"></i> {{ __('addToCart') }}
                                </button>
                                <button type="button" class="btn btn-secondary" id="backToListBtn">
                                    <i class="bi bi-arrow-left-circle me-1"></i> {{ __('backToList') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> {{ __('close') }}
                    </button>
                    <button type="button" class="btn btn-primary" id="addDollyBtn">
                        <i class="bi bi-plus-circle me-1"></i> {{ __('newCart') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .card-header {
            transition: background-color 0.2s ease;
        }

        .card-header:hover {
            background-color: #f8f9fa !important;
        }

        .bi {
            font-size: 0.9rem;
        }

        .badge {
            font-weight: 500;
        }

        .collapse {
            transition: all 0.3s ease-out;
        }

        .btn-link:focus {
            box-shadow: none;
        }

        .bi-chevron-down {
            transition: transform 0.3s ease;
        }

        [aria-expanded="true"] .bi-chevron-down {
            transform: rotate(180deg);
        }
    </style>
@endsection

@push('scripts')
    <script src="{{ asset('js/dollies.js') }}"></script>
    <script src="{{ asset('js/records.js') }}"></script>
@endpush