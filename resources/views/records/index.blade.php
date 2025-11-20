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
                <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                    <button id="cartBtn" class="btn btn-outline-primary btn-sm d-flex align-items-center"
                            data-bs-toggle="modal" data-bs-target="#dolliesModal">
                        <i class="bi bi-cart me-2"></i>
                        {{ __('cart') }}
                    </button>
                    <div class="d-flex align-items-center gap-1">
                        <label for="exportFormat" class="small text-muted mb-0">Format</label>
                        <select id="exportFormat" class="form-select form-select-sm" style="width:auto;">
                            <option value="">Choisir un format</option>
                            <option value="excel">Excel</option>
                            <option value="ead">EAD</option>
                            <option value="ead2002">EAD 2002 XML (Atom)</option>
                            <option value="dublincore">Dublin Core XML</option>
                            <option value="seda">SEDA</option>
                            <option value="pdf">PDF</option>
                        </select>
                    </div>

                    <button id="mosaicToggle" class="btn btn-outline-dark btn-sm d-flex align-items-center">
                        <i class="bi bi-grid-3x3-gap me-2"></i>Mosaïque
                    </button>

                    <div class="d-flex align-items-center gap-2">
                        <input type="text" id="keywordFilter" class="form-control form-control-sm" placeholder="Filtrer par mot-clé..." style="width: 200px;">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearKeywordFilter()" title="Effacer le filtre">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>

                    <div class="d-flex align-items-center gap-1">
                        <label for="typeFilter" class="small text-muted mb-0">Type</label>
                        <select id="typeFilter" class="form-select form-select-sm" style="width:auto;">
                            <option value="">Tous les types</option>
                            <option value="physical">Dossiers Physiques</option>
                            <option value="folder">Dossiers Numériques</option>
                            <option value="document">Documents Numériques</option>
                        </select>
                    </div>



                    </div>

                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <button id="checkAllBtn" class="btn btn-primary btn-sm d-flex align-items-center ms-0">
                            <i class="bi bi-check-square me-2"></i>
                            {{ __('checkAll') }}
                        </button>

                        <div class="d-flex align-items-center gap-2">
                            <span id="selectionCountBadge" class="badge bg-primary-subtle text-primary">
                                0 {{ __('selected') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Résumé résultats -->
        @if($records->total())
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-2 gap-2 small text-muted">
                <div>
                    {{ __('Résultats') }} {{ $records->firstItem() }}–{{ $records->lastItem() }} / {{ $records->total() }}
                    ({{ __('Page') }} {{ $records->currentPage() }} {{ __('sur') }} {{ $records->lastPage() }})
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span>{{ __('Affichés par page:') }} {{ $records->perPage() }}</span>
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
            </div>
        @endif

    <!-- Records List (catalog style) -->
    <ol id="recordList" class="record-catalog list-unstyled mb-4">
            @forelse($records as $record)
                @php
                    $index = ($records->currentPage()-1)*$records->perPage() + $loop->iteration;
                    $recordType = $record->record_type ?? 'physical';
                    $typeLabel = $record->type_label ?? 'Dossier Physique';

                    // Définir la couleur du badge selon le type
                    $badgeClass = match($recordType) {
                        'physical' => 'bg-primary',
                        'folder' => 'bg-success',
                        'document' => 'bg-warning text-dark',
                        default => 'bg-secondary'
                    };

                    // Route appropriée selon le type
                    $viewRoute = match($recordType) {
                        'folder' => route('folders.show', $record->id),
                        'document' => route('documents.show', $record->id),
                        default => route('records.show', $record->id)
                    };

                    $year = $record->date_exact ?? ($record->date_start ? (Str::substr($record->date_start,0,4)) : ($record->created_at ? $record->created_at->format('Y') : ''));
                    $authors = $record->authors ? $record->authors->pluck('name')->join(', ') : '';
                    // Keywords only available for physical records
                    $keywords = ($recordType === 'physical' && $record->keywords) ? $record->keywords->pluck('name')->implode(' ') : '';
                @endphp
                <li class="record-entry record-card position-relative mb-2 bg-light rounded" data-record-id="{{ $record->id }}" data-keywords="{{ $keywords }}" data-record-type="{{ $recordType }}">
                    <div class="d-flex align-items-start">
                        <div class="me-3" style="width:2.2rem;">
                            <div class="form-check">
                                <input class="form-check-input record-select" type="checkbox" value="{{ $record->id }}" id="select-{{ $record->id }}">
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <!-- Type Badge + Title line -->
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge {{ $badgeClass }} px-2 py-1">
                                    @if($recordType === 'physical')
                                        <i class="bi bi-archive me-1"></i>
                                    @elseif($recordType === 'folder')
                                        <i class="bi bi-folder me-1"></i>
                                    @else
                                        <i class="bi bi-file-earmark me-1"></i>
                                    @endif
                                    {{ $typeLabel }}
                                </span>
                                <h2 class="h6 fw-bold mb-0">
                                    <a href="{{ $viewRoute }}" class="text-decoration-none record-title">{{ $record->name }}</a>
                                    @if($year)<span class="text-muted fw-normal ms-1">({{ $year }})</span>@endif
                                </h2>
                            </div>
                            <!-- Authors / Responsibility -->
                            @if($authors)
                                <div class="small mb-1">
                                    @foreach($record->authors as $a)
                                        <a href="#" class="text-primary text-decoration-none me-1">{{ $a->name }}</a>@if(!$loop->last)<span class="text-muted">,</span>@endif
                                    @endforeach
                                </div>
                            @elseif($recordType === 'folder' && isset($record->creator))
                                <div class="small mb-1">
                                    <span class="text-muted">Créateur:</span> {{ $record->creator->name ?? 'N/A' }}
                                </div>
                            @elseif($recordType === 'document' && isset($record->creator))
                                <div class="small mb-1">
                                    <span class="text-muted">Créateur:</span> {{ $record->creator->name ?? 'N/A' }}
                                    @if(isset($record->current_version))
                                        <span class="badge bg-info ms-2">v{{ $record->current_version }}</span>
                                    @endif
                                </div>
                            @endif

                            <!-- Material / Type line (Physical only) -->
                            @if($recordType === 'physical')
                                <div class="small text-muted mb-1 d-flex flex-wrap gap-3">
                                    @if($record->support)
                                        <span><i class="bi bi-hdd-stack me-1"></i>{{ $record->support->name }}</span>
                                    @endif
                                    @if($record->level)
                                        <span><i class="bi bi-diagram-2 me-1"></i>{{ $record->level->name }}</span>
                                    @endif
                                    @if($record->activity)
                                        <span><i class="bi bi-activity me-1"></i>{{ $record->activity->name }}</span>
                                    @endif
                                    @if($record->status)
                                        <span><i class="bi bi-circle-fill me-1" style="font-size:0.5rem;"></i>{{ $record->status->name }}</span>
                                    @endif
                                </div>
                            @elseif($recordType === 'folder')
                                <div class="small text-muted mb-1">
                                    @if(isset($record->parent))
                                        <i class="bi bi-folder-symlink me-1"></i>
                                        <span>Chemin: {{ $record->parent->name ?? 'Racine' }}</span>
                                    @else
                                        <i class="bi bi-folder me-1"></i>
                                        <span>Dossier racine</span>
                                    @endif
                                </div>
                            @else
                                {{-- Document type --}}
                                <div class="small text-muted mb-1 d-flex flex-wrap gap-3">
                                    @if(isset($record->folder))
                                        <span><i class="bi bi-folder me-1"></i>{{ $record->folder->name }}</span>
                                    @endif
                                    @if(isset($record->mime_type))
                                        <span><i class="bi bi-file-earmark-code me-1"></i>{{ $record->mime_type }}</span>
                                    @endif
                                    @if(isset($record->file_size))
                                        <span><i class="bi bi-hdd me-1"></i>{{ number_format($record->file_size / 1024, 2) }} KB</span>
                                    @endif
                                </div>
                            @endif

                            <!-- Publication / archival details -->
                            @if($recordType === 'physical' && ($record->archival_history || $record->acquisition_source))
                                <div class="small text-muted mb-1">
                                    @if($record->archival_history)
                                        <span>{{ Str::limit(strip_tags($record->archival_history), 140) }}</span>
                                    @endif
                                    @if($record->acquisition_source)
                                        <span class="ms-2">{{ __('Source:') }} {{ Str::limit(strip_tags($record->acquisition_source), 60) }}</span>
                                    @endif
                                </div>
                            @elseif(in_array($recordType, ['folder', 'document']) && isset($record->description))
                                <div class="small text-muted mb-1">
                                    {{ Str::limit(strip_tags($record->description), 140) }}
                                </div>
                            @endif

                            <!-- Availability / location -->
                            <div class="small mb-2">
                                <span class="text-success fw-semibold">{{ __('Disponible') }}</span>
                                @if($recordType === 'physical' && $record->containers && $record->containers->isNotEmpty())
                                    <span class="text-muted ms-2">{{ __('Containers:') }} {{ $record->containers->pluck('code')->join(', ') }}</span>
                                @elseif($recordType === 'folder')
                                    <span class="text-muted ms-2">
                                        <i class="bi bi-files me-1"></i>
                                        {{ $record->documents_count ?? 0 }} document(s)
                                    </span>
                                @elseif($recordType === 'document' && isset($record->versions_count))
                                    <span class="text-muted ms-2">
                                        <i class="bi bi-clock-history me-1"></i>
                                        {{ $record->versions_count }} version(s)
                                    </span>
                                @endif
                            </div>
                            <!-- Keywords -->
                            @if($recordType === 'physical' && $record->keywords && $record->keywords->isNotEmpty())
                                <div class="small mb-2">
                                    <span class="text-muted me-2">{{ __('Mots-clés:') }}</span>
                                    @foreach($record->keywords as $keyword)
                                        <span class="badge bg-secondary me-1 keyword-badge"
                                              style="cursor: pointer;"
                                              onclick="filterByKeyword('{{ $keyword->name }}')"
                                              title="Cliquez pour filtrer par ce mot-clé">
                                            {{ $keyword->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                            <!-- Actions row (none for now) -->
                            <div class="record-actions small d-flex flex-wrap gap-3"></div>
                        </div>
                    </div>
                </li>
            @empty
                <li class="text-muted fst-italic py-4">{{ __('Aucun enregistrement trouvé') }}</li>
            @endforelse
        </ol>

        <!-- Mosaic Container (hidden by default) -->
        <div id="mosaicView" class="d-none mb-4">
            <div class="row g-3" id="mosaicGrid">
                @foreach($records as $record)
                    @php
                        // Récupérer les attachments selon le type de record
                        $attachments = collect();
                        if (method_exists($record, 'attachments') && $record->relationLoaded('attachments')) {
                            $attachments = $record->attachments;
                        }
                    @endphp
                    @foreach($attachments->take(12) as $attachment)
                        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                            <a href="{{ route('records.show',$record) }}" class="text-decoration-none d-block mosaic-item border rounded overflow-hidden bg-white h-100">
                                @php
                                    $thumb = $attachment->thumbnail_path && Storage::disk('public')->exists($attachment->thumbnail_path)
                                        ? asset('storage/'.$attachment->thumbnail_path)
                                        : null;
                                @endphp
                                <div class="ratio ratio-1x1 bg-light position-relative">
                                    @if($thumb)
                                        <img src="{{ $thumb }}" alt="{{ $attachment->name }}" class="w-100 h-100 object-fit-cover">
                                    @else
                                        <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                                            <i class="bi bi-file-earmark" style="font-size:1.8rem;"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="p-1 small text-truncate" title="{{ $record->name }}">{{ $record->name }}</div>
                            </a>
                        </div>
                    @endforeach
                @endforeach
            </div>
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
                            <input class="form-check-input" type="radio" name="exportFormat" id="formatEAD2002" value="ead2002">
                            <label class="form-check-label w-100 p-3 border rounded-3 bg-light" for="formatEAD2002">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-file-earmark-code text-secondary me-3 fs-4"></i>
                                    <div>
                                        <div class="fw-semibold">EAD 2002 XML (Atom)</div>
                                        <small class="text-muted">Export compatible AtoM (EAD 2002 XML)</small>
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
    /* Mosaic styles */
    .mosaic-item:hover { box-shadow:0 4px 12px rgba(0,0,0,0.12); transform:translateY(-2px); transition:.25s; }
    .mosaic-item img { object-fit:cover; }
    .record-entry { padding: 1.5rem 1rem; } /* +50% vertical vs p-3 (1rem) */

        /* Type Badge Styles */
        .badge {
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.35rem 0.65rem;
            border-radius: 0.35rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge.bg-primary {
            background-color: #0d6efd !important;
            box-shadow: 0 2px 4px rgba(13, 110, 253, 0.3);
        }

        .badge.bg-success {
            background-color: #198754 !important;
            box-shadow: 0 2px 4px rgba(25, 135, 84, 0.3);
        }

        .badge.bg-warning {
            background-color: #ffc107 !important;
            box-shadow: 0 2px 4px rgba(255, 193, 7, 0.3);
        }

        /* Record type specific styling */
        .record-entry[data-record-type="physical"] {
            border-left: 4px solid #0d6efd;
        }

        .record-entry[data-record-type="folder"] {
            border-left: 4px solid #198754;
        }

        .record-entry[data-record-type="document"] {
            border-left: 4px solid #ffc107;
        }

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

        /* Styles pour les vignettes d'attachments */
        .attachment-thumbnail {
            cursor: pointer;
            transition: all 0.2s ease;
            border-radius: 6px;
            overflow: hidden;
        }

        .attachment-thumbnail:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .attachment-thumbnail img {
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .attachment-thumbnail:hover img {
            filter: brightness(1.1);
        }

        .attachment-placeholder {
            transition: all 0.2s ease;
        }

        .attachment-thumbnail:hover .attachment-placeholder {
            background: #e9ecef !important;
            border-color: #adb5bd !important;
        }

        .attachment-more {
            transition: all 0.2s ease;
        }

        .attachment-more:hover {
            background: #e9ecef !important;
            border-color: #adb5bd !important;
        }

        .attachment-info {
            font-size: 0.7rem;
            line-height: 1.2;
        }
    </style>



    <!-- Modal pour afficher tous les attachments -->
    <div class="modal fade" id="attachmentsModal" tabindex="-1" aria-labelledby="attachmentsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="attachmentsModalLabel">
                        <i class="bi bi-paperclip me-2"></i>{{ __('attachments') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="attachmentsModalBody">
                    <!-- Le contenu sera chargé dynamiquement -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('close') }}</button>
                    <a href="#" id="viewRecordBtn" class="btn btn-primary">{{ __('view_record') ?? 'Voir le document' }}</a>
                </div>
            </div>
        </div>
    </div>

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

        // Toggle mosaïque / liste
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('mosaicToggle');
            const listEl = document.getElementById('recordList');
            const mosaicEl = document.getElementById('mosaicView');
            if (!toggleBtn || !listEl || !mosaicEl) return;

            const iconEl = toggleBtn.querySelector('i');
            const labelEl = toggleBtn.querySelector('.mosaic-label');

            // Restaurer l'état depuis localStorage
            const saved = localStorage.getItem('recordsViewMode');
            if (saved === 'mosaic') {
                listEl.classList.add('d-none');
                mosaicEl.classList.remove('d-none');
                if (iconEl) iconEl.className = 'bi bi-list-ul me-1';
                if (labelEl) labelEl.textContent = 'Liste';
                toggleBtn.classList.remove('btn-outline-dark');
                toggleBtn.classList.add('btn-dark');
            }

            toggleBtn.addEventListener('click', function() {
                const mosaicActive = !mosaicEl.classList.contains('d-none');
                if (mosaicActive) {
                    // Revenir à la liste
                    mosaicEl.classList.add('d-none');
                    listEl.classList.remove('d-none');
                    if (iconEl) iconEl.className = 'bi bi-grid-3x3-gap me-1';
                    if (labelEl) labelEl.textContent = 'Mosaïque';
                    toggleBtn.classList.remove('btn-dark');
                    toggleBtn.classList.add('btn-outline-dark');
                    localStorage.setItem('recordsViewMode', 'list');
                } else {
                    // Passer à la mosaïque
                    listEl.classList.add('d-none');
                    mosaicEl.classList.remove('d-none');
                    if (iconEl) iconEl.className = 'bi bi-list-ul me-1';
                    if (labelEl) labelEl.textContent = 'Liste';
                    toggleBtn.classList.remove('btn-outline-dark');
                    toggleBtn.classList.add('btn-dark');
                    localStorage.setItem('recordsViewMode', 'mosaic');
                }
            });
        });

        // Gestion du compteur de sélection et Tout cocher
        document.addEventListener('DOMContentLoaded', function() {
            const badge = document.getElementById('selectionCountBadge');
            const checkAllBtn = document.getElementById('checkAllBtn');
            const checkboxes = () => Array.from(document.querySelectorAll('.record-select'));

            function updateBadge() {
                const count = checkboxes().filter(cb => cb.checked).length;
                if (!badge) return;
                const baseLabel = badge.getAttribute('data-base') || badge.textContent.replace(/^\d+\s*/, '');
                badge.setAttribute('data-base', baseLabel);
                badge.textContent = `${count} ${baseLabel.trim()}`;
                // Style state
                if (count > 0) {
                    badge.classList.remove('bg-primary-subtle','text-primary');
                    badge.classList.add('bg-primary','text-white');
                } else {
                    badge.classList.add('bg-primary-subtle','text-primary');
                    badge.classList.remove('bg-primary','text-white');
                }
                // Bouton checkAll texte
                if (checkAllBtn) {
                    const allChecked = count === checkboxes().length && count > 0;
                    checkAllBtn.querySelector('i').className = allChecked ? 'bi bi-x-square me-2' : 'bi bi-check-square me-2';
                    checkAllBtn.lastChild.nodeValue = allChecked ? ' {{ __('uncheckAll') ?? 'Tout décocher' }}' : ' {{ __('checkAll') }}';
                }
            }

            function bindCheckboxEvents() {
                checkboxes().forEach(cb => {
                    if (cb.dataset._bound) return;
                    cb.addEventListener('change', () => {
                        const li = cb.closest('.record-entry');
                        if (li) {
                            if (cb.checked) {
                                li.classList.add('border','border-primary');
                            } else {
                                li.classList.remove('border','border-primary');
                            }
                        }
                        updateBadge();
                    });
                    cb.dataset._bound = '1';
                });
            }

            if (checkAllBtn) {
                checkAllBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const list = checkboxes();
                    const allChecked = list.length > 0 && list.every(cb => cb.checked);
                    list.forEach(cb => { cb.checked = !allChecked; });
                    bindCheckboxEvents();
                    updateBadge();
                });
            }

            // Initial
            bindCheckboxEvents();
            updateBadge();
        });

        // Export & Print handlers (avec select de format + désactivation dynamique)
        document.addEventListener('DOMContentLoaded', function() {
            const exportBtn = document.getElementById('exportBtn');
            const printBtn = document.getElementById('printBtn');
            const formatSelect = document.getElementById('exportFormat');

            function getSelectedIds() {
                return Array.from(document.querySelectorAll('.record-select:checked')).map(cb => cb.value);
            }

            function alertNoSelection() {
                // Simple feedback – pourrait être modale plus tard
                alert('Veuillez sélectionner au moins un document.');
            }

            if (exportBtn) {
                exportBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const ids = getSelectedIds();
                    if (!ids.length) { return alertNoSelection(); }
                    const format = formatSelect ? formatSelect.value : 'excel';
                    const url = new URL("{{ route('records.exportButton') }}", window.location.origin);
                    url.searchParams.set('records', ids.join(','));
                    url.searchParams.set('format', format);
                    window.location.href = url.toString();
                });
            }
            // Export automatique au changement de format
            if (formatSelect) {
                formatSelect.addEventListener('change', function() {
                    const ids = getSelectedIds();
                    if (!ids.length) return; // rien de sélectionné, ne pas lancer
                    if (exportBtn && exportBtn.disabled) return; // sécurité
                    const format = this.value || 'excel';
                    const url = new URL("{{ route('records.exportButton') }}", window.location.origin);
                    url.searchParams.set('records', ids.join(','));
                    url.searchParams.set('format', format);
                    window.location.href = url.toString();
                });
            }

            if (printBtn) {
                printBtn.addEventListener('click', async function(e) {
                    e.preventDefault();
                    const ids = getSelectedIds();
                    if (!ids.length) { return alertNoSelection(); }
                    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                    if (!tokenMeta) {
                        console.error('CSRF token meta manquant');
                        return alert('CSRF token manquant.');
                    }
                    const token = tokenMeta.getAttribute('content');
                    printBtn.classList.add('disabled');
                    const originalHtml = printBtn.innerHTML;
                    printBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>{{ __('print') }}';
                    try {
                        console.log('Envoi impression (records):', ids);
                        const formData = new FormData();
                        ids.forEach(id => formData.append('records[]', id));
                        formData.append('_token', token);
                        // Ne pas mettre mode=stream pour forcer le download côté controller (download par défaut)
                        const response = await fetch("{{ route('records.print') }}", {
                            method: 'POST',
                            body: formData,
                            headers: { 'Accept': 'application/pdf' }
                        });
                        if (!response.ok) throw new Error('HTTP '+response.status);
                        const blob = await response.blob();
                        if (blob.size === 0) throw new Error('PDF vide');
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        // Nom fichier cohérent
                        a.download = 'records_print_'+Date.now()+'.pdf';
                        document.body.appendChild(a);
                        a.click();
                        setTimeout(()=> {
                            window.URL.revokeObjectURL(url);
                            a.remove();
                        }, 2000);
                    } catch (err) {
                        console.error('Erreur génération PDF', err);
                        // Fallback: soumission classique (peut contourner fetch/CORS)
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = "{{ route('records.print') }}";
                        form.style.display = 'none';
                        const tokenInput = document.createElement('input');
                        tokenInput.type = 'hidden'; tokenInput.name = '_token'; tokenInput.value = token; form.appendChild(tokenInput);
                        ids.forEach(id => { const i=document.createElement('input'); i.type='hidden'; i.name='records[]'; i.value=id; form.appendChild(i); });
                        document.body.appendChild(form);
                        form.submit();
                        alert('Téléchargement via fallback. Si rien ne se passe, vérifiez les logs serveur.');
                    } finally {
                        printBtn.innerHTML = originalHtml;
                        printBtn.classList.remove('disabled');
                    }
                });
            }
            // Mise à jour activation boutons selon sélection
            function refreshActionButtons() {
                const any = document.querySelectorAll('.record-select:checked').length > 0;
                if (exportBtn) exportBtn.disabled = !any;
                if (printBtn) printBtn.disabled = !any;
            }
            document.addEventListener('change', function(e){
                if (e.target.classList && e.target.classList.contains('record-select')) {
                    refreshActionButtons();
                }
            });
            refreshActionButtons();
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

        // Mode IA global: plus de bascule côté index

        // Gestion des vignettes d'attachments
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle des vignettes
            const toggleBtn = document.getElementById('toggleThumbnailsBtn');
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    const thumbnails = document.querySelectorAll('.attachment-thumbnail, .attachment-more');
                    const isVisible = thumbnails.length > 0 && thumbnails[0].style.display !== 'none';

                    thumbnails.forEach(thumb => {
                        thumb.style.display = isVisible ? 'none' : 'block';
                    });

                    // Changer l'icône
                    const icon = this.querySelector('i');
                    if (isVisible) {
                        icon.className = 'bi bi-images';
                        this.title = '{{ __("show_thumbnails") ?? "Afficher vignettes" }}';
                    } else {
                        icon.className = 'bi bi-images-fill';
                        this.title = '{{ __("hide_thumbnails") ?? "Masquer vignettes" }}';
                    }
                });
            }

            // Filtre pour les records avec attachments
            const filterWithAttachmentsBtn = document.getElementById('filterWithAttachmentsBtn');
            if (filterWithAttachmentsBtn) {
                filterWithAttachmentsBtn.addEventListener('click', function() {
                    const records = document.querySelectorAll('.record-card');
                    const isFiltered = this.classList.contains('btn-info');

                    records.forEach(record => {
                        const hasAttachments = record.querySelector('.attachment-thumbnail, .attachment-more');
                        if (isFiltered) {
                            // Retirer le filtre
                            record.parentElement.style.display = '';
                            this.classList.remove('btn-info');
                            this.classList.add('btn-outline-info');
                            this.title = '{{ __("filter_with_attachments") ?? "Filtrer avec pièces jointes" }}';
                        } else {
                            // Appliquer le filtre
                            if (hasAttachments) {
                                record.parentElement.style.display = '';
                            } else {
                                record.parentElement.style.display = 'none';
                            }
                            this.classList.remove('btn-outline-info');
                            this.classList.add('btn-info');
                            this.title = '{{ __("show_all") ?? "Afficher tous" }}';
                        }
                    });
                });
            }

            // Ajouter des event listeners pour les vignettes
            document.querySelectorAll('.attachment-thumbnail').forEach(thumbnail => {
                thumbnail.addEventListener('click', function() {
                    const recordId = this.closest('.record-card').querySelector('input[type="checkbox"]').value;
                    const attachmentName = this.getAttribute('title');

                    // Ouvrir la page de détail du record pour voir les attachments
                    window.location.href = `/repositories/records/${recordId}#attachments`;
                });
            });

            // Ajouter des tooltips pour les vignettes
            document.querySelectorAll('.attachment-thumbnail').forEach(thumbnail => {
                const attachmentName = thumbnail.getAttribute('title');
                if (attachmentName) {
                    thumbnail.setAttribute('data-bs-toggle', 'tooltip');
                    thumbnail.setAttribute('data-bs-placement', 'top');
                    thumbnail.setAttribute('data-bs-title', attachmentName);
                }
            });

            // Initialiser les tooltips Bootstrap
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Gestion de la modal des attachments
            document.querySelectorAll('.attachment-more').forEach(moreBtn => {
                moreBtn.addEventListener('click', function() {
                    const recordId = this.getAttribute('data-record-id');
                    const attachmentCount = this.getAttribute('data-attachment-count');

                    // Charger les attachments via AJAX
                    fetch(`/repositories/records/${recordId}/attachments`)
                        .then(response => response.json())
                        .then(data => {
                            const modalBody = document.getElementById('attachmentsModalBody');
                            const viewRecordBtn = document.getElementById('viewRecordBtn');

                            let html = `
                                <div class="row g-3">
                                    <div class="col-12">
                                        <h6>Document: ${data.record.code} - ${data.record.name}</h6>
                                        <p class="text-muted">${attachmentCount} pièce(s) jointe(s)</p>
                                    </div>
                            `;

                            data.attachments.forEach(attachment => {
                                html += `
                                    <div class="col-md-3">
                                        <div class="card h-100">
                                            <div class="card-img-top bg-light" style="height: 200px;">
                                                ${attachment.thumbnail_path ?
                                                    `<img src="/storage/${attachment.thumbnail_path}" class="img-fluid h-100 w-100" style="object-fit: cover;" alt="${attachment.name}">` :
                                                    `<div class="d-flex align-items-center justify-content-center h-100">
                                                        <i class="bi bi-file-earmark-pdf fs-1 text-secondary"></i>
                                                    </div>`
                                                }
                                            </div>
                                            <div class="card-body p-3">
                                                <h6 class="card-title small">${attachment.name}</h6>
                                                <p class="card-text small text-muted">
                                                    ${attachment.size ? (attachment.size / 1024).toFixed(1) + ' KB' : 'N/A'}
                                                </p>
                                                <a href="/repositories/records/${recordId}/attachments/${attachment.id}" class="btn btn-sm btn-outline-primary w-100">
                                                    <i class="bi bi-download me-1"></i>{{ __('view_file') ?? 'Voir' }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });

                            html += '</div>';
                            modalBody.innerHTML = html;

                            // Mettre à jour le lien vers le record
                            viewRecordBtn.href = `/repositories/records/${recordId}`;

                            // Afficher la modal
                            const modal = new bootstrap.Modal(document.getElementById('attachmentsModal'));
                            modal.show();
                        })
                        .catch(error => {
                            console.error('Erreur lors du chargement des attachments:', error);
                            alert('Erreur lors du chargement des pièces jointes');
                        });
                });
            });
        });

        // Fonctions de filtrage par mots-clés
        function filterByKeyword(keyword) {
            // Redirection vers la page avec le filtre de mots-clés
            window.location.href = updateUrlParameter(window.location.href, 'keyword_filter', keyword);
        }

        function filterRecordsByKeywords() {
            const filterValue = document.getElementById('keywordFilter').value.trim();

            // Si le champ est vide, supprimer le paramètre keyword_filter de l'URL
            if (filterValue === '') {
                window.location.href = removeUrlParameter(window.location.href, 'keyword_filter');
            } else {
                // Sinon, rediriger avec le nouveau filtre
                window.location.href = updateUrlParameter(window.location.href, 'keyword_filter', filterValue);
            }
        }

        function clearKeywordFilter() {
            document.getElementById('keywordFilter').value = '';
            // Supprimer le paramètre keyword_filter de l'URL
            window.location.href = removeUrlParameter(window.location.href, 'keyword_filter');
        }

        // Fonctions utilitaires pour manipuler les paramètres d'URL
        function updateUrlParameter(url, param, paramVal) {
            let newAdditionalURL = "";
            let tempArray = url.split("?");
            let baseURL = tempArray[0];
            let additionalURL = tempArray[1];
            let temp = "";
            if (additionalURL) {
                tempArray = additionalURL.split("&");
                for (let i = 0; i < tempArray.length; i++) {
                    if (tempArray[i].split('=')[0] != param) {
                        newAdditionalURL += temp + tempArray[i];
                        temp = "&";
                    }
                }
            }
            let rowsTxt = temp + "" + param + "=" + paramVal;
            return baseURL + "?" + newAdditionalURL + rowsTxt;
        }

        function removeUrlParameter(url, parameter) {
            let urlparts = url.split('?');
            if (urlparts.length >= 2) {
                let prefix = encodeURIComponent(parameter) + '=';
                let pars = urlparts[1].split(/[&;]/g);

                for (let i = pars.length; i-- > 0;) {
                    if (pars[i].lastIndexOf(prefix, 0) !== -1) {
                        pars.splice(i, 1);
                    }
                }

                return urlparts[0] + (pars.length > 0 ? '?' + pars.join('&') : '');
            }
            return url;
        }

        // Initialiser le champ de filtre avec la valeur actuelle de l'URL
        document.addEventListener('DOMContentLoaded', function() {
            const filterInput = document.getElementById('keywordFilter');
            if (filterInput) {
                // Récupérer la valeur actuelle du paramètre keyword_filter depuis l'URL
                const urlParams = new URLSearchParams(window.location.search);
                const currentKeywordFilter = urlParams.get('keyword_filter');
                if (currentKeywordFilter) {
                    filterInput.value = currentKeywordFilter;
                }

                // Ajouter les événements pour le filtrage
                filterInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        filterRecordsByKeywords();
                    }
                });

                // Filtrer aussi quand l'utilisateur quitte le champ (blur)
                filterInput.addEventListener('blur', function() {
                    filterRecordsByKeywords();
                });
            }

            // Filtre par type de record
            const typeFilter = document.getElementById('typeFilter');
            if (typeFilter) {
                typeFilter.addEventListener('change', function() {
                    const selectedType = this.value;
                    const recordCards = document.querySelectorAll('.record-entry');

                    recordCards.forEach(card => {
                        const recordType = card.getAttribute('data-record-type');
                        if (selectedType === '' || recordType === selectedType) {
                            card.style.display = '';
                        } else {
                            card.style.display = 'none';
                        }
                    });

                    // Mettre à jour le compteur de résultats visibles
                    const visibleCount = Array.from(recordCards).filter(c => c.style.display !== 'none').length;
                    updateVisibleCountDisplay(visibleCount);
                });
            }
        });

        // Fonction pour mettre à jour l'affichage du nombre de résultats visibles
        function updateVisibleCountDisplay(count) {
            const summaryEl = document.querySelector('.d-flex.flex-column.flex-sm-row.justify-content-between');
            if (summaryEl) {
                const firstDiv = summaryEl.querySelector('div:first-child');
                if (firstDiv) {
                    const totalMatch = firstDiv.textContent.match(/\/\s*(\d+)/);
                    const total = totalMatch ? totalMatch[1] : count;

                    if (count < parseInt(total)) {
                        firstDiv.innerHTML = `{{ __('Résultats') }} ${count} / ${total} <span class="text-warning">(filtrés)</span>`;
                    }
                }
            }
        }
    </script>
@endpush
