@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        {{-- Breadcrumb + Messages --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('records.index') }}">{{ __('records') }}</a></li>
                    <li class="breadcrumb-item active">{{ $record->code }}</li>
                </ol>
            </nav>
            <div class="btn-group">
                <a href="{{ route('records.showFull', $record) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-eye-fill"></i> {{ __('detailed_view') ?? 'Detailed View' }}
                </a>
                <a href="{{ route('records.edit', $record) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil"></i> {{ __('edit_sheet') }}
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                    <i class="bi bi-trash"></i> {{ __('delete_sheet') }}
                </button>
            </div>
        </div>

        @if (session('success') || session('error'))
            <div class="alert alert-{{ session('success') ? 'success' : 'danger' }} alert-dismissible fade show">
                {{ session('success') ?? session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Main Content Card --}}
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h4 class="mb-0">{{ $record->name }} [{{ $record->level->name }}]</h4>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    {{-- Parent Record --}}
                    @if ($record->parent)
                        <div class="col-12">
                            <strong>{{ __('in') }}:</strong>
                            <a href="{{ route('records.show', $record->parent) }}">
                                [{{ $record->parent->level->name ?? '' }}] {{ $record->parent->name ?? '' }} /
                                @foreach($record->parent->authors as $author)
                                    {{ $author->name ?? '' }}
                                @endforeach
                                -
                                @if($record->parent->date_start != NULL && $record->parent->date_end != NULL)
                                    {{ $record->parent->date_start }} {{ __('to') }} {{ $record->parent->date_end }}
                                @elseif($record->parent->date_exact != NULL && $record->parent->date_end == NULL)
                                    {{ $record->parent->date_start }}
                                @elseif($record->parent->date_exact != NULL && $record->parent->date_start == NULL)
                                    {{ $record->parent->date_exact }}
                                @endif
                            </a>
                        </div>
                    @endif

                    {{-- Essential Information --}}
                    <div class="col-md-6">
                        <dl class="row mb-0">
                            <dt class="col-sm-3">{{ __('code') }}</dt>
                            <dd class="col-sm-9">{{ $record->code }}</dd>

                            <dt class="col-sm-3">{{ __('title_analysis') }}</dt>
                            <dd class="col-sm-9">{{ $record->name }}</dd>

                            <dt class="col-sm-3">{{ __('dates') }}</dt>
                            <dd class="col-sm-9">
                                @if($record->date_exact == NULL)
                                    @if($record->date_end == NULL)
                                        {{ $record->date_start ?? 'N/A' }}
                                    @else
                                        {{ $record->date_start ?? 'N/A' }} {{ __('to') }} {{ $record->date_end ?? 'N/A' }}
                                    @endif
                                @else
                                    {{ $record->date_exact ?? 'N/A' }}
                                @endif
                            </dd>

                            <dt class="col-sm-3">{{ __('producers') }}</dt>
                            <dd class="col-sm-9">
                                @foreach($record->authors as $author)
                                    <a href="{{ route('records.sort')}}?categ=authors&id={{ $author->id }}">
                                        {{ $author->name }}
                                    </a>
                                    @if(!$loop->last)
                                        ;
                                    @endif
                                @endforeach
                                @if($record->authors->isEmpty())
                                    N/A
                                @endif
                            </dd>
                        </dl>
                    </div>

                    <div class="col-md-6">
                        <dl class="row mb-0">
                            <dt class="col-sm-3">{{ __('support') }}</dt>
                            <dd class="col-sm-9">{{ $record->support->name ?? 'N/A' }}</dd>

                            <dt class="col-sm-3">{{ __('status') }}</dt>
                            <dd class="col-sm-9">{{ $record->status->name ?? 'N/A' }}</dd>

                            <dt class="col-sm-3">{{ __('container') }}</dt>
                            <dd class="col-sm-9">
                                @if($record->containers->isNotEmpty())
                                    @foreach($record->containers as $container)
                                        <a href="{{ route('records.sort')}}?categ=container&id={{ $container->id }}">
                                            {{ $container->code }} - {{ $container->name }}
                                        </a>
                                        @if(!$loop->last), @endif
                                    @endforeach
                                @else
                                    {{ __('not_containerized') }}
                                @endif
                            </dd>

                            <dt class="col-sm-3">{{ __('created_by') }}</dt>
                            <dd class="col-sm-9">{{ $record->user->name ?? 'N/A' }}</dd>
                        </dl>
                    </div>

                    {{-- Dimensions --}}
                    <div class="col-12">
                        <dl class="row mb-0">
                            <dt class="col-sm-2">{{ __('width') }}</dt>
                            <dd class="col-sm-4">{{ $record->width ? $record->width . ' cm' : 'N/A' }}</dd>

                            <dt class="col-sm-2">{{ __('width_description') }}</dt>
                            <dd class="col-sm-4">{{ $record->width_description ?? 'N/A' }}</dd>
                        </dl>
                    </div>

                    {{-- Terms and Activity --}}
                    <div class="col-12">
                        <dl class="row mb-0">
                            <dt class="col-sm-2">{{ __('terms') }}</dt>
                            <dd class="col-sm-10">
                                @foreach($record->thesaurusConcepts as $index => $concept)
                                    <a href="{{ route('records.sort')}}?categ=concept&id={{ $concept->id ?? 'N/A' }}">
                                        {{ $concept->preferred_label ?? 'N/A' }}
                                    </a>
                                    @if(!$loop->last)
                                        {{ " ; " }}
                                    @endif
                                @endforeach
                            </dd>

                            <dt class="col-sm-2">{{ __('activity') }}</dt>
                            <dd class="col-sm-10">
                                <a href="{{ route('records.sort')}}?categ=activity&id={{ $record->activity->id ?? 'N/A' }}">
                                    {{ $record->activity->name ?? 'N/A' }}
                                </a>
                            </dd>
                        </dl>
                    </div>

                    {{-- Content & Notes --}}
                    <div class="col-12">
                        <dl class="row mb-0">
                            <dt class="col-sm-2">{{ __('content') }}</dt>
                            <dd class="col-sm-10">{{ $record->content ?? 'N/A' }}</dd>

                            <dt class="col-sm-2">{{ __('note') }}</dt>
                            <dd class="col-sm-10">{{ $record->note ?? 'N/A' }}</dd>

                            <dt class="col-sm-2">{{ __('archivist_note') }}</dt>
                            <dd class="col-sm-10">{{ $record->archivist_note ?? 'N/A' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        {{-- Lifecycle Section --}}
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="bi bi-clock-history me-2"></i>{{ __('lifecycle') ?? 'Cycle de vie' }}
                </h5>
            </div>
            <div class="card-body">
                @php
                    // Calcul de la date de référence (date_exact en priorité, sinon date_end)
                    $referenceDate = $record->date_exact ?? $record->date_end;

                    // Conversion de la date selon le format si nécessaire
                    if ($referenceDate && !$record->date_exact) {
                        try {
                            switch ($record->date_format) {
                                case 'Y':
                                    $referenceDate = $referenceDate . '-12-31';
                                    break;
                                case 'M':
                                    $referenceDate = str_replace('/', '-', $referenceDate) . '-01';
                                    break;
                                case 'D':
                                    $referenceDate = str_replace('/', '-', $referenceDate);
                                    break;
                            }
                            $referenceDateObj = new DateTime($referenceDate);
                        } catch (Exception $e) {
                            $referenceDateObj = null;
                        }
                    } elseif ($referenceDate) {
                        try {
                            $referenceDateObj = new DateTime($referenceDate);
                        } catch (Exception $e) {
                            $referenceDateObj = null;
                        }
                    } else {
                        $referenceDateObj = null;
                    }

                    // Calcul des délais pour le bureau (communicabilité)
                    $communicabilityData = null;
                    $bureauExpired = false;
                    if ($record->activity && $record->activity->communicability && $referenceDateObj) {
                        $communicability = $record->activity->communicability;
                        $bureauEndDate = clone $referenceDateObj;
                        $bureauEndDate->add(new DateInterval('P' . $communicability->duration . 'Y'));
                        $bureauExpired = new DateTime() > $bureauEndDate;
                        $communicabilityData = [
                            'duration' => $communicability->duration,
                            'end_date' => $bureauEndDate,
                            'expired' => $bureauExpired
                        ];
                    }

                    // Calcul des délais pour la salle d'archives (rétention la plus longue)
                    $retentionData = null;
                    $archiveExpired = false;
                    if ($record->activity && $record->activity->retentions->isNotEmpty() && $referenceDateObj) {
                        $longestRetention = $record->activity->retentions->sortByDesc('duration')->first();
                        $archiveEndDate = clone $referenceDateObj;
                        $archiveEndDate->add(new DateInterval('P' . $longestRetention->duration . 'Y'));
                        $archiveExpired = new DateTime() > $archiveEndDate;
                        $retentionData = [
                            'duration' => $longestRetention->duration,
                            'end_date' => $archiveEndDate,
                            'expired' => $archiveExpired,
                            'sort' => $longestRetention->sort
                        ];
                    }
                @endphp

                <div class="row g-3">
                    {{-- Bureau Section --}}
                    <div class="col-md-6">
                        <div class="card h-100 {{ $bureauExpired ? 'border-warning' : 'border-success' }}">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="bi bi-building me-2"></i>{{ __('office_period') ?? 'Délai dans le bureau' }}
                                </h6>
                            </div>
                            <div class="card-body">
                                @if($communicabilityData)
                                    <dl class="row mb-0">
                                        <dt class="col-sm-5">{{ __('reference_date') ?? 'Date de référence' }}</dt>
                                        <dd class="col-sm-7">
                                            {{ $referenceDateObj ? $referenceDateObj->format('d/m/Y') : 'N/A' }}
                                            @if($record->date_exact)
                                                <small class="text-muted">(date exacte)</small>
                                            @else
                                                <small class="text-muted">(date fin)</small>
                                            @endif
                                        </dd>

                                        <dt class="col-sm-5">{{ __('lifecycle_communicability') ?? 'Communicabilité' }}</dt>
                                        <dd class="col-sm-7">{{ $communicabilityData['duration'] }} {{ __('years') ?? 'ans' }}</dd>

                                        <dt class="col-sm-5">{{ __('lifecycle_end_date') ?? 'Date de fin' }}</dt>
                                        <dd class="col-sm-7">{{ $communicabilityData['end_date']->format('d/m/Y') }}</dd>

                                        <dt class="col-sm-5">{{ __('lifecycle_status') ?? 'Statut' }}</dt>
                                        <dd class="col-sm-7">
                                            @if($communicabilityData['expired'])
                                                <span class="badge bg-warning">{{ __('expired') ?? 'Expiré' }}</span>
                                            @else
                                                <span class="badge bg-success">{{ __('active') ?? 'Actif' }}</span>
                                            @endif
                                        </dd>
                                    </dl>
                                @else
                                    <div class="text-muted">
                                        <i class="bi bi-info-circle me-2"></i>
                                        {{ __('no_communicability_data') ?? 'Aucune donnée de communicabilité disponible' }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Archive Section --}}
                    <div class="col-md-6">
                        <div class="card h-100 {{ $archiveExpired ? 'border-danger' : 'border-info' }}">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="bi bi-archive me-2"></i>{{ __('archive_period') ?? 'Salle d\'archives' }}
                                </h6>
                            </div>
                            <div class="card-body">
                                @if($retentionData)
                                    <dl class="row mb-0">
                                        <dt class="col-sm-5">{{ __('reference_date') ?? 'Date de référence' }}</dt>
                                        <dd class="col-sm-7">
                                            {{ $referenceDateObj ? $referenceDateObj->format('d/m/Y') : 'N/A' }}
                                            @if($record->date_exact)
                                                <small class="text-muted">(date exacte)</small>
                                            @else
                                                <small class="text-muted">(date fin)</small>
                                            @endif
                                        </dd>

                                        <dt class="col-sm-5">{{ __('retention_duration') ?? 'Durée légale' }}</dt>
                                        <dd class="col-sm-7">{{ $retentionData['duration'] }} {{ __('years') ?? 'ans' }}</dd>

                                        <dt class="col-sm-5">{{ __('lifecycle_end_date') ?? 'Date de fin' }}</dt>
                                        <dd class="col-sm-7">{{ $retentionData['end_date']->format('d/m/Y') }}</dd>

                                        <dt class="col-sm-5">{{ __('final_sort') ?? 'Sort final' }}</dt>
                                        <dd class="col-sm-7">
                                            @switch($retentionData['sort']->code)
                                                @case('C')
                                                    <span class="badge bg-primary">
                                                        {{ __('conservation') ?? 'Conservation' }}
                                                    </span>
                                                    @break
                                                @case('T')
                                                    <span class="badge bg-warning">
                                                        {{ __('sorting') ?? 'Tri' }}
                                                    </span>
                                                    @break
                                                @case('E')
                                                    <span class="badge bg-danger">
                                                        {{ __('elimination') ?? 'Élimination' }}
                                                    </span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ $retentionData['sort']->name }}</span>
                                            @endswitch
                                        </dd>

                                        <dt class="col-sm-5">{{ __('lifecycle_status') ?? 'Statut' }}</dt>
                                        <dd class="col-sm-7">
                                            @if($retentionData['expired'])
                                                <span class="badge bg-danger">{{ __('expired') ?? 'Expiré' }}</span>
                                            @else
                                                <span class="badge bg-info">{{ __('active') ?? 'Actif' }}</span>
                                            @endif
                                        </dd>
                                    </dl>
                                @else
                                    <div class="text-muted">
                                        <i class="bi bi-info-circle me-2"></i>
                                        {{ __('no_retention_data') ?? 'Aucune donnée de rétention disponible' }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Summary Section --}}
                @if($communicabilityData || $retentionData)
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6 class="alert-heading">
                                    <i class="bi bi-info-circle me-2"></i>{{ __('lifecycle_summary') ?? 'Résumé du cycle de vie' }}
                                </h6>
                                <p class="mb-0">
                                    @if($communicabilityData && $retentionData)
                                        @if($bureauExpired && $archiveExpired)
                                            {{ __('document_ready_for_final_action') ?? 'Ce document a dépassé tous les délais et est prêt pour le sort final' }}
                                            ({{ $retentionData['sort']->name }}).
                                        @elseif($bureauExpired && !$archiveExpired)
                                            {{ __('document_ready_for_archive') ?? 'Ce document peut être transféré en salle d\'archives' }}.
                                        @else
                                            {{ __('document_in_office_period') ?? 'Ce document est encore dans sa période de bureau' }}.
                                        @endif
                                    @else
                                        {{ __('incomplete_lifecycle_data') ?? 'Données de cycle de vie incomplètes' }}.
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>


        <div class="card mb-3">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="bi bi-archive me-2"></i>{{ __('archive_boxes') }}
                </h5>
            </div>
            <div class="card-body">
                <div class="list-group mb-3">
                    @foreach ($record->recordContainers as $recordContainer)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ $recordContainer->container->code }} - {{ $recordContainer->description }}</span>
                            <form action="{{ route('record-container-remove')}}?r_id={{ $recordContainer->record_id }}&c_id={{ $recordContainer->container_id }}"
                                  method="post" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-outline-danger">{{ __('remove_from_box') }}</button>
                            </form>
                        </div>
                    @endforeach
                </div>

                <form action="{{ route('record-container-insert')}}?r_id={{ $record->id }}" method="post" class="row g-2">
                    @csrf
                    <div class="col-md-3">
                        <input type="text" name="code" class="form-control form-control-sm"
                               placeholder="{{ __('code') }}: B12453">
                    </div>
                    <div class="col">
                        <input type="text" name="description" class="form-control form-control-sm"
                               placeholder="{{ __('title_analysis') }}">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-primary">{{ __('insert') }}</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Child Records --}}
        @if($record->children->isNotEmpty() || $record->level->has_child)
            <div class="card mb-3">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('child_records') }}</h5>
                    @if($record->level->has_child)
                        <a href="{{ route('record-child.create', $record->id) }}" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-plus-circle me-1"></i>{{ __('add_child_sheet') }}
                        </a>
                    @endif
                </div>
                @if($record->children->isNotEmpty())
                    <div class="list-group list-group-flush">
                        @foreach($record->children as $child)
                            <a href="{{ route('records.show', $child) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $child->code }}</strong>
                                    <span class="text-muted ms-2">{{ $child->name }}</span>
                                </div>
                                <span class="badge bg-primary rounded-pill">{{ $child->level->name ?? 'N/A' }}</span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="card-body text-muted">
                        {{ __('no_child_records') }}
                    </div>
                @endif
            </div>
        @endif

        {{-- Attachments Section --}}
        <div class="card mb-3">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-paperclip me-2"></i>{{ __('attachments') }}</h5>
                <a href="{{ route('records.attachments.create', $record) }}" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-plus-circle me-1"></i>{{ __('add_file') }}
                </a>
            </div>
            <div class="card-body">
                <div class="row row-cols-1 row-cols-md-4 g-3" id="attachmentsList">
                    @forelse($record->attachments as $attachment)
                        <div class="col">
                            <div class="card h-100 shadow-sm">
                                <div class="card-img-top bg-light" style="height: 140px;">
                                    @if($attachment->thumbnail_path)
                                        <img src="{{ asset('storage/' . $attachment->thumbnail_path) }}"
                                             class="img-fluid h-100 w-100" style="object-fit: cover;">
                                    @else
                                        <div class="d-flex align-items-center justify-content-center h-100">
                                            <i class="bi bi-file-earmark-pdf fs-1 text-secondary"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-body p-2">
                                    <p class="card-text small text-truncate mb-2" title="{{ $attachment->name }}">
                                        {{ $attachment->name }}
                                    </p>
                                    <a href="{{ route('records.attachments.show', [$record, $attachment]) }}"
                                       class="btn btn-sm btn-outline-primary w-100">
                                        <i class="bi bi-download me-1"></i>{{ __('view_file') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-muted">
                            {{ __('no_attachments') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Back Button --}}
        <a href="{{ route('records.index') }}" class="btn btn-outline-secondary w-100">
            <i class="bi bi-arrow-left me-2"></i>{{ __('back_to_home') }}
        </a>
    </div>

    {{-- Delete Modal --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('confirm_deletion') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">{{ __('delete_confirmation') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                        {{ __('cancel') }}
                    </button>
                    <form action="{{ route('records.destroy', $record) }}" method="POST" class="d-inline" id="delete-record-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">
                            {{ __('delete') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });

            // Handle delete form submission
            document.getElementById('delete-record-form').addEventListener('submit', function(e) {
                if (!confirm("{{ __('delete_confirmation') }}")) {
                    e.preventDefault();
                }
            });

            // Gestion des alertes auto-disparition
            const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.classList.remove('show');
                    setTimeout(() => alert.remove(), 150);
                }, 5000);
            });

            // Gestion du bouton de reformulation de titre
            const btnReformulate = document.getElementById('btn-reformulate');
            const intelligenceResult = document.getElementById('intelligence-result');

            btnReformulate.addEventListener('click', async function() {
                const recordTitle = "{{ $record->name }}";
                const recordId = {{ $record->id }};

                // Fonction de reformulation désactivée
                intelligenceResult.innerHTML = `
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>{{ __('feature_unavailable') ?? 'Cette fonctionnalité n\'est pas disponible actuellement' }}
                    </div>
                `;
                // No try-catch block needed anymore
                // Function body ends here
                console.log('Reformulation feature disabled');
                // The following is left for compatibility
                intelligenceResult.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>Une erreur est survenue lors de la reformulation du titre
                        </div>
                    `;
                }
            });





        });
    </script>
@endpush


