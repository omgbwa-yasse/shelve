@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        {{-- Breadcrumb + Messages --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ session('records.back_url', route('records.index')) }}">{{ __('records') }}</a>
                    </li>
                    <li class="breadcrumb-item active">{{ $record->code }}</li>
                </ol>
            </nav>
            <div class="d-flex gap-2 flex-wrap">
                <div class="btn-group">
                    <a href="{{ session('records.back_url', route('records.index')) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('back') }}">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <a href="{{ isset($prevId) ? route('records.show', $prevId) : '#' }}" class="btn btn-sm btn-outline-secondary {{ isset($prevId) ? '' : 'disabled' }}" title="{{ __('previous') }}">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                    <a href="{{ isset($nextId) ? route('records.show', $nextId) : '#' }}" class="btn btn-sm btn-outline-secondary {{ isset($nextId) ? '' : 'disabled' }}" title="{{ __('next') }}">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
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

                {{-- (Déplacé) Boutons MCP maintenant dans la section "Intelligence artificielle" plus bas --}}
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


        {{-- Intelligence artificielle Section (global, above Lifecycle) --}}
        @php
            $aiPrompts = [];
            if (Schema::hasTable('prompts')) {
                $promptQuery = DB::table('prompts')->select('id','title')
                    ->whereIn('title', ['record_reformulate','record_summarize','assign_thesaurus','assign_activity']);
                if (Schema::hasColumn('prompts', 'is_system')) {
                    $promptQuery->where('is_system', true);
                }
                $aiPrompts = $promptQuery->pluck('id','title');
            }
            $thesaurusLabels = isset($record) && $record->thesaurusConcepts ? $record->thesaurusConcepts->pluck('preferred_label')->filter()->values()->all() : [];
            $activityCandidates = isset($record) && $record->activity ? [ (string)($record->activity->name ?? '') ] : [];
        @endphp
        <div class="card mb-3">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-cpu me-2"></i>{{ __('artificial_intelligence') ?? 'Intelligence artificielle' }}
                </h5>
                <fieldset class="btn-group btn-group-sm">
                    <legend class="visually-hidden">AI actions</legend>
                    <button type="button" class="btn btn-outline-primary ai-action-btn"
                            data-action="reformulate_title"
                            data-prompt-id="{{ $aiPrompts['record_reformulate'] ?? '' }}">
                        {{ __('reformulate_title') ?? 'Reformuler titre' }}
                    </button>
                    <button type="button" class="btn btn-outline-secondary ai-action-btn"
                            data-action="summarize"
                            data-prompt-id="{{ $aiPrompts['record_summarize'] ?? '' }}">
                        {{ __('summarize') ?? 'Résumer' }}
                    </button>
                    <button type="button" class="btn btn-outline-success ai-action-btn"
                            data-action="assign_thesaurus"
                            data-prompt-id="{{ $aiPrompts['assign_thesaurus'] ?? '' }}">
                        {{ __('index_thesaurus') ?? 'Indexer thésaurus' }}
                    </button>
                    <button type="button" class="btn btn-outline-info ai-action-btn"
                            data-action="assign_activity"
                            data-prompt-id="{{ $aiPrompts['assign_activity'] ?? '' }}">
                        {{ __('index_activities') ?? 'Indexer activités' }}
                    </button>
                </fieldset>
            </div>
            <div class="card-body">
                <output id="aiStatus" class="d-none">
                    <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                    <span class="ms-2">{{ __('processing') ?? 'Traitement en cours…' }}</span>
                </output>
                <div id="aiError" class="alert alert-danger alert-permanent d-none mt-2"></div>
                <div id="aiResult" class="mt-2 d-none">
                    <h6 class="fw-semibold">{{ __('ai_output') ?? 'Résultat AI' }}</h6>
                    <pre class="bg-light p-2 rounded" style="white-space: pre-wrap; word-wrap: break-word;"></pre>
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

                                {{-- Intelligence artificielle Section removed here; now displayed globally above Lifecycle --}}

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
                                             class="img-fluid h-100 w-100" style="object-fit: cover;" alt="{{ $attachment->name ?? 'Attachment' }}">
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

@push('scripts')
<script>
    (function(){
        const buttons = document.querySelectorAll('.ai-action-btn');
        const statusEl = document.getElementById('aiStatus');
        const resultWrap = document.getElementById('aiResult');
        const resultPre = resultWrap ? resultWrap.querySelector('pre') : null;
        const errorEl = document.getElementById('aiError');
        const recordId = {{ (int)($record->id ?? 0) }};
        const contextBase = {
            title: @json($record->name ?? ''),
            text: @json($record->content ?? ''),
            pref_labels: @json($thesaurusLabels ?? []),
            candidates: @json($activityCandidates ?? []),
            slip_records: []
        };
    console.log('[AI:init] record/show', { recordId, hasContent: !!contextBase.text, titleLen: (contextBase.title||'').length, prefLabels: contextBase.pref_labels, candidates: contextBase.candidates });

    // --- Auth/CSRF helpers for Sanctum/Session ---
    function getCookie(name){
        const match = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([.$?*|{}()\[\]\\\/\+^])/g,'\\$1') + '=([^;]*)'));
        return match ? decodeURIComponent(match[1]) : '';
    }
    async function ensureCsrfCookie(){
        // If no XSRF-TOKEN cookie, ask Sanctum to set it
        const hasXsrf = !!getCookie('XSRF-TOKEN');
        if(!hasXsrf){
            console.log('[AI] ensureCsrfCookie: XSRF cookie missing, requesting /sanctum/csrf-cookie');
            try{
                await fetch('/sanctum/csrf-cookie', { credentials: 'same-origin' });
                console.log('[AI] ensureCsrfCookie: cookie request done');
            }catch(e){ console.warn('[AI] ensureCsrfCookie error', e); /* ignore, will fail later gracefully */ }
        } else {
            console.log('[AI] ensureCsrfCookie: XSRF cookie present');
        }
    }
    function buildAuthHeaders(){
        const xsrf = getCookie('XSRF-TOKEN');
        const metaCsrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const headers = {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };
        // Prefer Sanctum XSRF header when cookie exists, else fall back to meta CSRF
        if(xsrf){ headers['X-XSRF-TOKEN'] = xsrf; }
        else if(metaCsrf){ headers['X-CSRF-TOKEN'] = metaCsrf; }
        console.log('[AI] buildAuthHeaders: using', { hasXsrf: !!xsrf, hasMeta: !!metaCsrf, keys: Object.keys(headers) });
        return headers;
    }

    // Modal state for AI review
    const aiModalEl = document.getElementById('aiReviewModal');
    const aiModalBody = document.getElementById('aiReviewBody');
    const aiModalTitle = document.getElementById('aiReviewTitle');
    const aiSaveBtn = document.getElementById('aiReviewSaveBtn');
    let currentAi = { action: null, output: '', parsed: null };

        function setBusy(busy){
            console.log('[AI] setBusy', { busy });
            buttons.forEach(b => b.disabled = !!busy);
            statusEl && statusEl.classList.toggle('d-none', !busy);
        }
        function showError(msg){
            console.error('[AI] ERROR:', msg);
            if(!errorEl) return;
            // Hide previous result when showing an error
            if(resultWrap){ resultWrap.classList.add('d-none'); }
            errorEl.textContent = (msg && msg.toString()) || 'Erreur AI';
            errorEl.classList.remove('d-none');
        }
        function clearError(){ if(errorEl) errorEl.classList.add('d-none'); }
        function showResult(text){
            console.log('[AI] showResult', { length: (text||'').length, preview: (text||'').slice(0, 200) });
            if(resultWrap && resultPre){ resultPre.textContent = text || ''; resultWrap.classList.remove('d-none'); }
        }

        function uniqStrings(arr){ const s=new Set(); const out=[]; arr.forEach(v=>{ const t=(v||'').toString().trim(); if(t && !s.has(t)){ s.add(t); out.push(t); } }); return out; }
        function parseList(text){
            const raw = (text||'').replace(/\r/g,'').split(/\n|,|;|\t|\u2022|\*/);
            return uniqStrings(raw).slice(0, 30);
        }
        function openAiReviewModal(action, output){
            console.log('[AI] openAiReviewModal', { action, outputLen: (output||'').length });
            if(!aiModalEl || !aiModalBody || !aiModalTitle){ return; }
            currentAi = { action, output, parsed: null };
            let html = '';
            switch(action){
                case 'reformulate_title': {
                    const first = (output||'').split(/\n/)[0].trim().replace(/^"|"$/g,'');
                    currentAi.parsed = { title: first };
                    aiModalTitle.textContent = '{{ __('reformulate_title') ?? 'Reformuler le titre' }}';
                    html = `
                        <label for="aiTitleInput" class="form-label">{{ __('new_title') ?? 'Nouveau titre' }}</label>
                        <input id="aiTitleInput" type="text" class="form-control" value="${first.replaceAll('"','&quot;')}">
                        <small class="text-muted d-block mt-2">{{ __('you_can_edit_before_saving') ?? 'Vous pouvez modifier avant d\'enregistrer.' }}</small>
                    `;
                    break;
                }
                case 'summarize': {
                    currentAi.parsed = { summary: output||'' };
                    aiModalTitle.textContent = '{{ __('summary') ?? 'Résumé' }}';
                    html = `
                        <label for="aiSummaryInput" class="form-label">{{ __('generated_summary') ?? 'Résumé généré' }}</label>
                        <textarea id="aiSummaryInput" class="form-control" rows="8">${(output||'').replaceAll('<','&lt;')}</textarea>
                        <small class="text-muted d-block mt-2">{{ __('edit_before_save') ?? 'Modifiez si besoin avant d\'enregistrer.' }}</small>
                    `;
                    break;
                }
                case 'assign_thesaurus': {
                    const items = parseList(output);
                    currentAi.parsed = { labels: items };
                    aiModalTitle.textContent = '{{ __('index_thesaurus') ?? 'Indexer thésaurus' }}';
                    html = `
                        <div class="mb-2">{{ __('select_keywords_to_apply') ?? 'Sélectionnez les mots-clés à appliquer' }}</div>
                        <div class="d-flex flex-wrap gap-2">
                            ${items.map((k,i)=>`<div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="kw_${i}" value="${k.replaceAll('"','&quot;')}" checked>
                                <label class="form-check-label" for="kw_${i}">${k}</label>
                            </div>`).join('')}
                        </div>
                    `;
                    break;
                }
                case 'assign_activity': {
                    const items = parseList(output).slice(0, 5);
                    currentAi.parsed = { activities: items };
                    aiModalTitle.textContent = '{{ __('index_activities') ?? 'Indexer activités' }}';
                    html = `
                        <div class="mb-2">{{ __('choose_activity') ?? 'Choisissez l\'activité' }}</div>
                        <div class="d-flex flex-column gap-2">
                            ${items.map((k,i)=>`<div class="form-check">
                                <input class="form-check-input" type="radio" name="aiActivity" id="act_${i}" value="${k.replaceAll('"','&quot;')}" ${i===0?'checked':''}>
                                <label class="form-check-label" for="act_${i}">${k}</label>
                            </div>`).join('')}
                        </div>
                    `;
                    break;
                }
                default: {
                    aiModalTitle.textContent = 'AI';
                    html = `<pre class="bg-light p-2 rounded" style="white-space: pre-wrap;">${(output||'').replaceAll('<','&lt;')}</pre>`;
                }
            }
            aiModalBody.innerHTML = html;
            try{ new bootstrap.Modal(aiModalEl).show(); }catch(e){ console.warn('Modal error', e); }
        }

    async function saveAiReview(){
            console.log('[AI] saveAiReview:start', { action: currentAi?.action });
            if(!currentAi || !currentAi.action){ return; }
            let url = '';
            let payload = {};
            switch(currentAi.action){
                case 'reformulate_title': {
                    const val = document.getElementById('aiTitleInput')?.value?.trim();
                    if(!val){ showError('Titre vide'); return; }
                    url = `/api/records/${recordId}/ai/title`;
                    payload = { title: val };
                    break;
                }
                case 'summarize': {
                    const val = document.getElementById('aiSummaryInput')?.value?.trim();
                    if(!val){ showError('Résumé vide'); return; }
                    url = `/api/records/${recordId}/ai/summary`;
                    payload = { summary: val };
                    break;
                }
                case 'assign_thesaurus': {
                    const checks = aiModalBody.querySelectorAll('input.form-check-input[type="checkbox"]:checked');
                    const labels = Array.from(checks).map(c=>c.value).filter(Boolean);
                    if(labels.length===0){ showError('Sélectionnez au moins un mot-clé'); return; }
                    url = `/api/records/${recordId}/ai/thesaurus`;
                    payload = { concepts: labels.map(l=>({ preferred_label: l })) };
                    break;
                }
                case 'assign_activity': {
                    const sel = aiModalBody.querySelector('input[name="aiActivity"]:checked');
                    if(!sel){ showError('Choisissez une activité'); return; }
                    url = `/api/records/${recordId}/ai/activity`;
                    payload = { activity_name: sel.value };
                    break;
                }
                default:
                    return;
            }
            console.log('[AI] saveAiReview:request', { url, payload });
            try{
                aiSaveBtn.disabled = true;
                await ensureCsrfCookie();
                const controller = new AbortController();
                const timer = setTimeout(() => controller.abort(), 20000);
                const resp = await fetch(url, {
                    method: 'POST',
                    headers: buildAuthHeaders(),
                    credentials: 'same-origin',
                    body: JSON.stringify(payload),
                    signal: controller.signal
                });
                clearTimeout(timer);
                let data;
                try { data = await resp.json(); }
                catch(_){ data = { message: (await resp.text()).slice(0,500) }; }
                console.log('[AI] saveAiReview:response', { status: resp.status, ok: resp.ok, data });
                if(resp.status === 401){
                    throw new Error('Non autorisé (401). Veuillez vous reconnecter.');
                }
                if(!resp.ok || data?.status==='error'){
                    throw new Error(data?.message || 'Erreur sauvegarde');
                }
                // Close modal and show toast
                try{ bootstrap.Modal.getInstance(aiModalEl)?.hide(); }catch(e){}
                showResult(currentAi.output);
                // Optionally refresh to show applied changes
                setTimeout(()=>{ window.location.reload(); }, 800);
            }catch(e){
                console.error('[AI] saveAiReview:error', e);
                if (e.name === 'AbortError') {
                    showError('La sauvegarde a expiré. Réessayez.');
                } else {
                    showError(e.message || 'Erreur');
                }
            }finally{
                console.log('[AI] saveAiReview:finally');
                aiSaveBtn.disabled = false;
            }
        }

        async function runAction(btn){
            const action = btn.getAttribute('data-action');
            const promptId = btn.getAttribute('data-prompt-id');
            console.log('[AI] runAction:click', { action, promptId });
            clearError(); showResult('');
            if(!promptId){ showError('Prompt introuvable pour cette action.'); return; }
            setBusy(true);
            try{
                const body = {
                    action: action,
                    entity: 'record',
                    entity_ids: [recordId],
                    context: contextBase
                };
                console.log('[AI] runAction:requestBody', body);
                await ensureCsrfCookie();
                const controller = new AbortController();
                const timer = setTimeout(() => controller.abort(), 25000);
                const url = `/api/prompts/${promptId}/actions`;
                console.log('[AI] runAction:fetch', { url });
                const resp = await fetch(url, {
                    method: 'POST',
                    headers: buildAuthHeaders(),
                    credentials: 'same-origin',
                    body: JSON.stringify(body),
                    signal: controller.signal
                });
                clearTimeout(timer);
                let data;
                try { data = await resp.json(); }
                catch(_){ data = { message: (await resp.text()).slice(0,500) }; }
                console.log('[AI] runAction:response', { status: resp.status, ok: resp.ok, data });
                if(resp.status === 401){
                    throw new Error('Non autorisé (401). Votre session a peut-être expiré. Connectez-vous puis réessayez.');
                }
                if(!resp.ok){
                    const status = `${resp.status} ${resp.statusText}`;
                    const msg = data?.message || data || 'Erreur AI';
                    throw new Error(`HTTP ${status} - ${msg}`);
                }
                const outText = data?.output || '';
                showResult(outText);
                openAiReviewModal(action, outText);
            }catch(e){
                console.error('[AI] runAction:error', e);
                if (e.name === 'AbortError') {
                    showError('La requête AI a expiré. Vérifiez le fournisseur et réessayez.');
                } else {
                    showError(e.message || 'Erreur AI');
                }
            }finally{
                console.log('[AI] runAction:finally');
                setBusy(false);
            }
        }

        // Disable AI buttons if no prompt ids resolved
        (function initAIActions(){
            const noPrompt = Array.from(buttons).every(b => !b.getAttribute('data-prompt-id'));
            if(noPrompt){
                buttons.forEach(b => { b.disabled = true; b.title = 'Prompts non disponibles'; });
                showError('Prompts non disponibles. Vérifiez la base de données ou exécutez le seeder AI.');
                console.warn('[AI] initAIActions: no prompts found on buttons');
            }
            console.log('[AI] initAIActions: binding click handlers', { count: buttons.length });
            buttons.forEach(btn => btn.addEventListener('click', () => runAction(btn)));
        })();

        if(aiSaveBtn){
            console.log('[AI] binding saveAiReview click');
            aiSaveBtn.addEventListener('click', saveAiReview);
        }
    })();
</script>
@endpush
    </div>

    <!-- AI Review Modal -->
    <div class="modal fade" id="aiReviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="aiReviewTitle">AI</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="aiReviewBody"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('cancel') ?? 'Annuler' }}</button>
                    <button type="button" class="btn btn-primary" id="aiReviewSaveBtn">{{ __('save') ?? 'Enregistrer' }}</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Reformulation Modal --}}
    <div class="modal fade" id="reformulationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-magic me-2"></i>{{ __('title_reformulation') ?? 'Reformulation du titre' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="reformulationResult">
                        <!-- Le résultat de la reformulation sera affiché ici -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ __('close') ?? 'Fermer' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

{{-- Scripts JavaScript pour les fonctionnalités MCP/Mistral --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
    // S'assurer que jQuery est disponible pour les scripts legacy
    if (typeof $ === 'undefined' && typeof window.jQuery !== 'undefined') {
        window.$ = window.jQuery;
    }

            // Initialize tooltips
    if (typeof bootstrap !== 'undefined') {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
    }

            // Handle delete form submission
    const deleteForm = document.getElementById('delete-record-form');
    if (deleteForm) {
        deleteForm.addEventListener('submit', function(e) {
                if (!confirm("{{ __('delete_confirmation') }}")) {
                    e.preventDefault();
                }
            });
    }

            // Gestion des alertes auto-disparition
            const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.classList.remove('show');
                    setTimeout(() => alert.remove(), 150);
                }, 5000);
            });

    // Initialiser les boutons MCP
    function initializeMcpButtons() {
        // Initialiser les tooltips seulement si Bootstrap est disponible
        if (typeof bootstrap !== 'undefined') {
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            if (tooltipTriggerList.length > 0) {
                tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                    try {
                        new bootstrap.Tooltip(tooltipTriggerEl);
                    } catch (e) {
                        console.warn('Erreur initialisation tooltip:', e);
                    }
                });
            }
        }

        // Gestionnaire pour les boutons d'action MCP (seulement s'ils existent)
        const mcpActionBtns = document.querySelectorAll('.mcp-action-btn');
        if (mcpActionBtns.length > 0) {
            mcpActionBtns.forEach(button => {
                button.addEventListener('click', handleMcpActionWithMode);
            });
        }

        // Le mode IA est désormais global (Admin MCP). Aucun toggle dans la vue record.
    }

    // Démarrer l'initialisation MCP après un délai pour s'assurer que tout est chargé
    setTimeout(initializeMcpButtons, 100);
});

// Fonction pour changer de mode global
// Mode global: plus de switch côté vue

// Gestionnaire principal pour les actions MCP
function handleMcpActionWithMode(event) {
    event.preventDefault();

    const button = event.currentTarget;
    const action = button.dataset.action;
    const recordId = button.dataset.recordId;
    const apiPrefix = button.dataset.apiPrefix || '/api/mcp';

    if (!recordId) {
        showMcpNotification('Erreur: ID du record manquant', 'error');
        return;
    }

    // Désactiver le bouton pendant le traitement
    setButtonState(button, 'processing');

    // Déterminer l'endpoint selon l'action et le mode
    let endpoint, method = 'POST', isPreview = action.includes('preview');

    switch(action) {
        case 'title':
        case 'title-preview':
            endpoint = `${apiPrefix}/records/${recordId}/title/preview`;
            break;
        case 'thesaurus':
        case 'thesaurus-suggest':
            endpoint = `${apiPrefix}/records/${recordId}/thesaurus/index`;
            break;
        case 'summary':
        case 'summary-preview':
            endpoint = `${apiPrefix}/records/${recordId}/summary/preview`;
            break;
        case 'all-preview':
            endpoint = `${apiPrefix}/records/${recordId}/preview`;
            break;
        case 'all-apply':
            endpoint = `${apiPrefix}/records/${recordId}/process`;
            break;
        default:
            setButtonState(button, 'error');
            showMcpNotification('Action inconnue: ' + action, 'error');
            return;
    }

    // Effectuer la requête
    fetch(endpoint, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            features: action.startsWith('all') ? ['title', 'thesaurus', 'summary'] : [action.split('-')[0]]
        })
    })
    .then(response => response.json())
                .then(data => {
        if (data.error) {
            throw new Error(data.message || 'Erreur inconnue');
        }

        setButtonState(button, 'success');

        // Message de succès personnalisé selon le mode
        const mode = apiPrefix.includes('mistral') ? 'Mistral' : 'MCP';
        showMcpNotification(`${mode}: ${data.message || 'Traitement réussi'}`, 'success');

        // Afficher les tokens utilisés si disponible (Mistral)
        if (data.tokens_used) {
            console.log(`Tokens utilisés (${mode}):`, data.tokens_used);
        }

        // TOUJOURS afficher l'aperçu pour validation
        showMcpPreviewWithValidation(data, mode, action, recordId, apiPrefix);
                })
                .catch(error => {
        setButtonState(button, 'error');
        const mode = apiPrefix.includes('mistral') ? 'Mistral' : 'MCP';
        showMcpNotification(`Erreur ${mode}: ${error.message}`, 'error');
        console.error(`Erreur ${mode}:`, error);
    });
}

// Gestion des états des boutons
function setButtonState(button, state) {
    button.classList.remove('mcp-processing', 'mcp-success', 'mcp-error');

    const existingSpinner = button.querySelector('.spinner-border');
    if (existingSpinner) {
        existingSpinner.remove();
    }

    switch(state) {
        case 'processing':
            button.classList.add('mcp-processing');
            button.disabled = true;
            button.insertAdjacentHTML('afterbegin', '<span class="spinner-border spinner-border-sm me-1" role="status"></span>');
            break;
        case 'success':
            button.classList.add('mcp-success');
            button.disabled = false;
            setTimeout(() => {
                button.classList.remove('mcp-success');
            }, 3000);
            break;
        case 'error':
            button.classList.add('mcp-error');
            button.disabled = false;
            setTimeout(() => {
                button.classList.remove('mcp-error');
            }, 5000);
            break;
        default:
            button.disabled = false;
    }
}

// Afficher les notifications
function showMcpNotification(message, type = 'info') {
    if (typeof bootstrap === 'undefined') {
        console.log(`${type.toUpperCase()}: ${message}`);
        return;
    }

    const toastContainer = document.getElementById('toast-container') || createToastContainer();
    const toastId = 'toast-' + Date.now();
    const bgClass = type === 'success' ? 'bg-success' : type === 'error' ? 'bg-danger' : 'bg-info';

    const toastHtml = `
        <div id="${toastId}" class="toast ${bgClass} text-white" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header ${bgClass} text-white border-0">
                <i class="bi bi-robot me-2"></i>
                <strong class="me-auto">IA Processing</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">${message}</div>
        </div>
    `;

    toastContainer.insertAdjacentHTML('beforeend', toastHtml);

    try {
        const toast = new bootstrap.Toast(document.getElementById(toastId));
        toast.show();

        document.getElementById(toastId).addEventListener('hidden.bs.toast', function() {
            this.remove();
        });
    } catch (e) {
        console.warn('Erreur création toast:', e);
        console.log(`${type.toUpperCase()}: ${message}`);
    }
}

// Créer le conteneur de toast
function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '1055';
    document.body.appendChild(container);
    return container;
}

// Afficher l'aperçu avec validation obligatoire AVANT application
function showMcpPreviewWithValidation(data, mode, action, recordId, apiPrefix) {
    if (typeof bootstrap === 'undefined') {
        console.log('Bootstrap non disponible, affichage en console:', data);
        return;
    }

    let modal = document.getElementById('mcpPreviewModal');
    if (!modal) {
        modal = createPreviewModalWithValidation();
    }

    const modalTitle = modal.querySelector('.modal-title');
    modalTitle.innerHTML = `<i class="bi bi-exclamation-triangle text-warning me-2"></i>Validation requise - ${mode}`;

    const modalBody = modal.querySelector('.modal-body');
    let content = `
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Validation requise :</strong> Vérifiez les modifications avant de les appliquer
        </div>
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Deux options disponibles :</strong> Vous pouvez appliquer directement les modifications ou aller à la page d'édition pour plus de contrôle.
        </div>
    `;

    // Formater l'aperçu selon le type d'action
    if (action.includes('title')) {
        content += formatTitlePreviewShow(data);
    } else if (action.includes('thesaurus')) {
        content += formatThesaurusPreviewShow(data);
    } else if (action.includes('summary')) {
        content += formatSummaryPreviewShow(data);
    } else if (data.previews) {
        Object.entries(data.previews).forEach(([feature, preview]) => {
            content += formatPreviewContent(feature, preview);
        });
    }

    if (data.tokens_used) {
        content += `<div class="alert alert-info mt-3">
            <i class="bi bi-info-circle me-1"></i>
            <strong>Tokens utilisés :</strong> ${data.tokens_used}
        </div>`;
    }

    modalBody.innerHTML = content;

    // Stocker les données pour l'application
    modal.dataset.previewData = JSON.stringify(data);
    modal.dataset.mode = mode;
    modal.dataset.action = action;
    modal.dataset.recordId = recordId;
    modal.dataset.apiPrefix = apiPrefix;

    try {
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    } catch (e) {
        console.warn('Erreur création modal:', e);
        console.log('Aperçu des données:', data);
    }
}

// Formater l'aperçu spécifique pour le titre (vue Show)
function formatTitlePreviewShow(data) {
    if (data.preview && data.preview.suggested_title) {
        const currentTitle = document.querySelector('h4')?.textContent?.split(' [')[0] || 'Non défini';
        return `
            <div class="mb-3 border rounded p-3 bg-light">
                <h6 class="text-primary"><i class="bi bi-magic me-2"></i>Reformulation du titre</h6>
                <div class="row">
                    <div class="col-6">
                        <strong>Titre actuel :</strong><br>
                        <span class="text-muted">${currentTitle}</span>
                    </div>
                    <div class="col-6">
                        <strong>Titre suggéré :</strong><br>
                        <span class="text-success fw-bold">${data.preview.suggested_title}</span>
                    </div>
                </div>
            </div>
        `;
    }
    return '<div class="alert alert-warning">Aucune suggestion de titre reçue</div>';
}

// Formater l'aperçu spécifique pour le résumé (vue Show)
function formatSummaryPreviewShow(data) {
    if (data.preview && data.preview.suggested_summary) {
        return `
            <div class="mb-3 border rounded p-3 bg-light">
                <h6 class="text-primary"><i class="bi bi-file-text me-2"></i>Résumé ISAD(G) - Portée et contenu (3.3.1)</h6>
                                <div class="alert alert-success">
                    <strong>Résumé suggéré :</strong><br>
                    <div class="bg-white p-2 border rounded mt-2" style="max-height: 200px; overflow-y: auto;">
                        <span class="fw-bold">${data.preview.suggested_summary}</span>
                    </div>
                </div>
                                </div>
                            `;
    }
    return '<div class="alert alert-warning">Aucun résumé généré</div>';
}

// Formater l'aperçu spécifique pour l'indexation thésaurus (vue Show)
function formatThesaurusPreviewShow(data) {
    if (data.preview && data.preview.concepts && data.preview.concepts.length > 0) {
        let content = `
            <div class="mb-3 border rounded p-3 bg-light">
                <h6 class="text-primary"><i class="bi bi-tags me-2"></i>Indexation automatique</h6>
                <p><strong>Concepts trouvés :</strong> ${data.preview.concepts.length}</p>
                <div class="mb-3">
                    <strong>Mots-clés suggérés :</strong>
                    <div class="mt-2">
        `;

        data.preview.concepts.forEach(concept => {
            const weight = concept.weight ? Math.round(concept.weight * 100) : 'N/A';
            content += `
                <span class="badge bg-success me-2 mb-2 p-2">
                    ${concept.preferred_label}
                    <small>(${weight}%)</small>
                </span>
            `;
        });

        content += `
                    </div>
                </div>
                                </div>
                            `;
        return content;
    }
    return '<div class="alert alert-warning">Aucun concept trouvé dans le thésaurus</div>';
}

// Afficher l'aperçu des modifications avec fonctionnalité "Appliquer"
function showMcpPreview(data, mode = 'MCP') {
    if (typeof bootstrap === 'undefined') {
        console.log('Bootstrap non disponible, affichage en console:', data);
        return;
    }

    let modal = document.getElementById('mcpPreviewModal');
    if (!modal) {
        modal = createPreviewModal();
    }

    const modalTitle = modal.querySelector('.modal-title');
    modalTitle.innerHTML = `<i class="bi bi-robot me-2"></i>Aperçu ${mode}`;

    const modalBody = modal.querySelector('.modal-body');
    let content = `<h6>Aperçu des modifications (${mode}) :</h6>`;

    if (data.previews) {
        Object.entries(data.previews).forEach(([feature, preview]) => {
            content += formatPreviewContent(feature, preview);
        });
    } else if (data.preview) {
        content += formatPreviewContent('single', data.preview);
    }

    if (data.tokens_used) {
        content += `<div class="alert alert-info mt-3">
            <i class="bi bi-info-circle me-1"></i>
            <strong>Tokens utilisés :</strong> ${data.tokens_used}
        </div>`;
    }

    modalBody.innerHTML = content;

    // Stocker les données pour l'application
    modal.dataset.previewData = JSON.stringify(data);
    modal.dataset.mode = mode;

    try {
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    } catch (e) {
        console.warn('Erreur création modal:', e);
        console.log('Aperçu des données:', data);
    }
}

// Formater le contenu de l'aperçu
function formatPreviewContent(feature, preview) {
    let content = `<div class="mb-3 border rounded p-3">`;
    content += `<h6 class="text-primary">${feature.charAt(0).toUpperCase() + feature.slice(1)}</h6>`;

    if (typeof preview === 'object') {
        if (preview.original_title && preview.suggested_title) {
            content += `
                <div class="row">
                    <div class="col-6">
                        <strong>Actuel :</strong><br>
                        <span class="text-muted">${preview.original_title}</span>
                    </div>
                    <div class="col-6">
                        <strong>Suggéré :</strong><br>
                        <span class="text-success">${preview.suggested_title}</span>
                    </div>
                </div>`;
        } else if (preview.concepts_found !== undefined) {
            content += `<p><strong>Concepts trouvés :</strong> ${preview.concepts_found}</p>`;
            if (preview.concepts && preview.concepts.length > 0) {
                content += '<p><strong>Principaux concepts :</strong></p><ul>';
                preview.concepts.slice(0, 5).forEach(concept => {
                    const weight = concept.weight ? Math.round(concept.weight * 100) : 'N/A';
                    content += `<li>${concept.preferred_label} (${weight}%)</li>`;
                });
                content += '</ul>';
            }
        } else if (preview.current_summary && preview.suggested_summary) {
            content += `
                <div class="row">
                    <div class="col-6">
                        <strong>Résumé actuel :</strong><br>
                        <span class="text-muted">${preview.current_summary || 'Aucun'}</span>
                    </div>
                    <div class="col-6">
                        <strong>Résumé suggéré :</strong><br>
                        <span class="text-success">${preview.suggested_summary}</span>
                    </div>
                </div>`;
        } else {
            content += `<pre class="bg-light p-2 rounded">${JSON.stringify(preview, null, 2)}</pre>`;
        }
    } else {
        content += `<p class="bg-light p-2 rounded">${preview}</p>`;
    }

    content += '</div>';
    return content;
}

// Créer la modal d'aperçu avec validation
function createPreviewModalWithValidation() {
    const modalHtml = `
        <div class="modal fade" id="mcpPreviewModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title text-dark">
                            <i class="bi bi-exclamation-triangle me-2"></i>Aperçu des modifications
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i>Fermer
                        </button>
                        <a href="javascript:void(0)" class="btn btn-outline-primary" onclick="goToEditPage()">
                            <i class="bi bi-pencil me-1"></i>Aller à l'édition
                        </a>
                        <button type="button" class="btn btn-success" onclick="applyChangesDirectlyFromShow()">
                            <i class="bi bi-check-circle me-1"></i>Appliquer directement
                        </button>
                    </div>
                </div>
            </div>
                            </div>
                        `;

    document.body.insertAdjacentHTML('beforeend', modalHtml);
    return document.getElementById('mcpPreviewModal');
}

// Créer la modal d'aperçu (ancienne fonction - gardée pour compatibilité)
function createPreviewModal() {
    return createPreviewModalWithValidation();
}

// Aller à la page d'édition depuis la vue show
function goToEditPage() {
    const modal = document.getElementById('mcpPreviewModal');
    if (!modal) return;

    const recordId = modal.dataset.recordId;
    if (recordId) {
        window.location.href = `/records/${recordId}/edit`;
    } else {
        // Fallback : essayer de récupérer l'ID depuis l'URL actuelle
        const currentUrl = window.location.pathname;
        const editUrl = currentUrl.replace('/records/', '/records/').replace('show', 'edit');
        window.location.href = editUrl;
    }
}

// Appliquer les changements directement depuis la vue show
function applyChangesDirectlyFromShow() {
    const modal = document.getElementById('mcpPreviewModal');
    if (!modal) return;

    const previewData = JSON.parse(modal.dataset.previewData || '{}');
    const mode = modal.dataset.mode || 'MCP';
    const action = modal.dataset.action || '';
    const recordId = modal.dataset.recordId;
    const apiPrefix = modal.dataset.apiPrefix || '/api/mcp';

    // DEBUG: Afficher les données exactes pour diagnostic
    console.log('Application directe depuis Show:', {
        action: action,
        previewData: previewData,
        recordId: recordId,
        apiPrefix: apiPrefix,
        timestamp: new Date().toISOString()
    });

    if (!recordId) {
        showMcpNotification('Erreur: ID du record introuvable', 'error');
        return;
    }

    // Fermer la modal
    const bsModal = bootstrap.Modal.getInstance(modal);
    if (bsModal) {
        bsModal.hide();
    }

    // Déterminer l'endpoint d'application selon l'action
    let endpoint;
    let requestData = {};

    if (action.includes('title') && previewData.preview?.suggested_title) {
        endpoint = `${apiPrefix}/records/${recordId}/title/reformulate`;
        requestData = {
            suggested_title: previewData.preview.suggested_title,
            apply_directly: true
        };
        console.log('Préparation application titre:', requestData);
    } else if (action.includes('summary') && previewData.preview?.suggested_summary) {
        endpoint = `${apiPrefix}/records/${recordId}/summary/generate`;
        requestData = {
            suggested_summary: previewData.preview.suggested_summary,
            apply_directly: true
        };
        console.log('Préparation application résumé:', requestData);
    } else if (action.includes('thesaurus') && previewData.preview?.concepts) {
        endpoint = `${apiPrefix}/records/${recordId}/thesaurus/index`;
        requestData = {
            concepts: previewData.preview.concepts,
            apply_directly: true
        };
        console.log('Préparation application thésaurus:', requestData);
    } else {
        console.error('Aucune donnée valide à appliquer:', {
            action: action,
            preview: previewData.preview
        });
        showMcpNotification('Aucune donnée à appliquer', 'error');
        return;
    }

    // Afficher le statut en cours
    showMcpNotification(`${mode}: Application des modifications en cours...`, 'info');

    // Faire l'appel API pour appliquer les changements
    fetch(endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
        console.log('Réponse de l\'API:', data);

        if (data.error) {
            throw new Error(data.message || 'Erreur inconnue');
        }

        showMcpNotification(`${mode}: Modifications appliquées avec succès!`, 'success');

        // Recharger la page pour voir les changements
        setTimeout(() => {
            window.location.reload();
        }, 1500);
    })
    .catch(error => {
        console.error(`Erreur ${mode}:`, error);
        showMcpNotification(`Erreur ${mode}: ${error.message}`, 'error');
    });
}

// Appliquer les changements de l'aperçu (FONCTIONNALITÉ MAINTENANT ACTIVE)
function applyPreviewChanges() {
    const modal = document.getElementById('mcpPreviewModal');
    if (!modal) return;

    const previewData = JSON.parse(modal.dataset.previewData || '{}');
    const mode = modal.dataset.mode || 'MCP';
    const apiPrefix = mode === 'Mistral' ? '/api/mistral-test' : '/api/mcp';

    // Récupérer l'ID du record depuis les boutons de la page
    const recordId = document.querySelector('.mcp-action-btn')?.dataset.recordId;

    if (!recordId) {
        showMcpNotification('Erreur: ID du record introuvable', 'error');
        return;
    }

    // Déterminer quelles fonctionnalités appliquer selon les données disponibles
    let features = [];
    if (previewData.previews) {
        features = Object.keys(previewData.previews);
    } else if (previewData.preview) {
        features = ['single'];
    }

    if (features.length === 0) {
        showMcpNotification('Aucune modification à appliquer', 'error');
        return;
    }

    // Fermer la modal
    const bsModal = bootstrap.Modal.getInstance(modal);
    if (bsModal) {
        bsModal.hide();
    }

    // Appliquer les modifications
    const endpoint = `${apiPrefix}/records/${recordId}/process`;

    showMcpNotification(`${mode}: Application des modifications en cours...`, 'info');

    fetch(endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            features: features
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            throw new Error(data.message || 'Erreur inconnue');
        }

        showMcpNotification(`${mode}: Modifications appliquées avec succès!`, 'success');

        // Recharger la page pour voir les changements
        setTimeout(() => {
            window.location.reload();
        }, 2000);
    })
    .catch(error => {
        showMcpNotification(`Erreur ${mode}: ${error.message}`, 'error');
        console.error(`Erreur ${mode}:`, error);
    });
}
    </script>


