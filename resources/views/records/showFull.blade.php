@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        {{-- Back Button --}}
        <div class="mb-3">
            <a href="{{ route('records.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>{{ __('back_to_home') }}
            </a>
        </div>

        {{-- Breadcrumb + Messages --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('records.index') }}">{{ __('records') }}</a></li>
                    <li class="breadcrumb-item active">{{ $record->code }} - {{ __('detailed_view') }}</li>
                </ol>
            </nav>
            <div class="btn-group">
                <a href="{{ route('records.show', $record) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-eye"></i> {{ __('standard_view') }}
                </a>
                <a href="{{ route('records.edit', $record) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil"></i> {{ __('edit_sheet') }}
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                    <i class="bi bi-trash"></i> {{ __('delete_sheet') }}
                </button>
                <button type="button" id="btn-ai-enrich" class="btn btn-sm btn-outline-info">
                    <i class="bi bi-stars"></i> {{ __('ai_enrich') ?? 'AI Enrich' }}
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
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h4 class="mb-0">{{ $record->name }} [{{ $record->level->name }}]</h4>
                <span class="badge bg-primary">{{ __('full_details') }}</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    {{-- Parent Record --}}
                    @if ($record->parent)
                        <div class="col-12">
                            <strong>{{ __('in') }}:</strong>
                            <a href="{{ route('records.showFull', $record->parent) }}">
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

                    <div class="col-12">
                        <h5 class="border-bottom pb-2">{{ __('identification_area') }}</h5>
                    </div>

                    {{-- Identification Area --}}
                    <div class="col-md-6">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">{{ __('code') }}</dt>
                            <dd class="col-sm-8">{{ $record->code }}</dd>

                            <dt class="col-sm-4">{{ __('title_analysis') }}</dt>
                            <dd class="col-sm-8">{{ $record->name }}</dd>

                            <dt class="col-sm-4">{{ __('dates') }}</dt>
                            <dd class="col-sm-8">
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

                            <dt class="col-sm-4">{{ __('date_format') }}</dt>
                            <dd class="col-sm-8">
                                @switch($record->date_format)
                                    @case('1')
                                        {{ __('exact_date') }}
                                        @break
                                    @case('2')
                                        {{ __('start_date_only') }}
                                        @break
                                    @case('3')
                                        {{ __('start_end_date') }}
                                        @break
                                    @default
                                        N/A
                                @endswitch
                            </dd>

                            <dt class="col-sm-4">{{ __('level') }}</dt>
                            <dd class="col-sm-8">{{ $record->level->name ?? 'N/A' }}</dd>

                            <dt class="col-sm-4">{{ __('width') }}</dt>
                            <dd class="col-sm-8">{{ $record->width ? $record->width . ' cm' : 'N/A' }}</dd>

                            <dt class="col-sm-4">{{ __('width_description') }}</dt>
                            <dd class="col-sm-8">{{ $record->width_description ?? 'N/A' }}</dd>
                        </dl>
                    </div>

                    <div class="col-md-6">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">{{ __('producers') }}</dt>
                            <dd class="col-sm-8">
                                <a href="{{ route('records.sort')}}?categ=authors&id={{ $record->authors->pluck('id')->join('') }}">
                                    {{ $record->authors->isEmpty() ? 'N/A' : $record->authors->map(fn($author) => "{$author->name}")->implode(' ') }}
                                </a>
                            </dd>

                            <dt class="col-sm-4">{{ __('support') }}</dt>
                            <dd class="col-sm-8">{{ $record->support->name ?? 'N/A' }}</dd>

                            <dt class="col-sm-4">{{ __('status') }}</dt>
                            <dd class="col-sm-8">{{ $record->status->name ?? 'N/A' }}</dd>

                            <dt class="col-sm-4">{{ __('container') }}</dt>
                            <dd class="col-sm-8">
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

                            <dt class="col-sm-4">{{ __('created_by') }}</dt>
                            <dd class="col-sm-8">{{ $record->user->name ?? 'N/A' }}</dd>

                            <dt class="col-sm-4">{{ __('activity') }}</dt>
                            <dd class="col-sm-8">
                                <a href="{{ route('records.sort')}}?categ=activity&id={{ $record->activity->id ?? 'N/A' }}">
                                    {{ $record->activity->name ?? 'N/A' }}
                                </a>
                            </dd>

                            <dt class="col-sm-4">{{ __('terms') }}</dt>
                            <dd class="col-sm-8">
                                @foreach($record->thesaurusConcepts as $index => $concept)
                                    <a href="{{ route('records.sort')}}?categ=concept&id={{ $concept->id ?? 'N/A' }}">
                                        {{ $concept->preferred_label ?? 'N/A' }}
                                    </a>
                                    @if(!$loop->last)
                                        {{ " ; " }}
                                    @endif
                                @endforeach
                            </dd>
                        </dl>
                    </div>

                    <div class="col-12">
                        <h5 class="border-bottom pb-2">{{ __('context_area') }}</h5>
                    </div>

                    {{-- Context Area --}}
                    <div class="col-12">
                        <dl class="row mb-0">
                            <dt class="col-sm-3">{{ __('biographical_history') }}</dt>
                            <dd class="col-sm-9">{{ $record->biographical_history ?? 'N/A' }}</dd>

                            <dt class="col-sm-3">{{ __('archival_history') }}</dt>
                            <dd class="col-sm-9">{{ $record->archival_history ?? 'N/A' }}</dd>

                            <dt class="col-sm-3">{{ __('acquisition_source') }}</dt>
                            <dd class="col-sm-9">{{ $record->acquisition_source ?? 'N/A' }}</dd>
                        </dl>
                    </div>

                    <div class="col-12">
                        <h5 class="border-bottom pb-2">{{ __('content_structure_area') }}</h5>
                    </div>

                    {{-- Content and Structure Area --}}
                    <div class="col-12">
                        <dl class="row mb-0">
                            <dt class="col-sm-3">{{ __('content') }}</dt>
                            <dd class="col-sm-9">{{ $record->content ?? 'N/A' }}</dd>

                            <dt class="col-sm-3">{{ __('appraisal') }}</dt>
                            <dd class="col-sm-9">{{ $record->appraisal ?? 'N/A' }}</dd>

                            <dt class="col-sm-3">{{ __('accrual') }}</dt>
                            <dd class="col-sm-9">{{ $record->accrual ?? 'N/A' }}</dd>

                            <dt class="col-sm-3">{{ __('arrangement') }}</dt>
                            <dd class="col-sm-9">{{ $record->arrangement ?? 'N/A' }}</dd>
                        </dl>
                    </div>

                    <div class="col-12">
                        <h5 class="border-bottom pb-2">{{ __('access_use_area') }}</h5>
                    </div>

                    {{-- Access and Use Area --}}
                    <div class="col-12">
                        <dl class="row mb-0">
                            <dt class="col-sm-3">{{ __('access_conditions') }}</dt>
                            <dd class="col-sm-9">{{ $record->access_conditions ?? 'N/A' }}</dd>

                            <dt class="col-sm-3">{{ __('reproduction_conditions') }}</dt>
                            <dd class="col-sm-9">{{ $record->reproduction_conditions ?? 'N/A' }}</dd>

                            <dt class="col-sm-3">{{ __('language_material') }}</dt>
                            <dd class="col-sm-9">{{ $record->language_material ?? 'N/A' }}</dd>

                            <dt class="col-sm-3">{{ __('characteristic') }}</dt>
                            <dd class="col-sm-9">{{ $record->characteristic ?? 'N/A' }}</dd>

                            <dt class="col-sm-3">{{ __('finding_aids') }}</dt>
                            <dd class="col-sm-9">{{ $record->finding_aids ?? 'N/A' }}</dd>
                        </dl>
                    </div>

                    <div class="col-12">
                        <h5 class="border-bottom pb-2">{{ __('allied_materials_area') }}</h5>
                    </div>

                    {{-- Allied Materials Area --}}
                    <div class="col-12">
                        <dl class="row mb-0">
                            <dt class="col-sm-3">{{ __('location_original') }}</dt>
                            <dd class="col-sm-9">{{ $record->location_original ?? 'N/A' }}</dd>

                            <dt class="col-sm-3">{{ __('location_copy') }}</dt>
                            <dd class="col-sm-9">{{ $record->location_copy ?? 'N/A' }}</dd>

                            <dt class="col-sm-3">{{ __('related_unit') }}</dt>
                            <dd class="col-sm-9">{{ $record->related_unit ?? 'N/A' }}</dd>

                            <dt class="col-sm-3">{{ __('publication_note') }}</dt>
                            <dd class="col-sm-9">{{ $record->publication_note ?? 'N/A' }}</dd>
                        </dl>
                    </div>

                    <div class="col-12">
                        <h5 class="border-bottom pb-2">{{ __('notes_area') }}</h5>
                    </div>

                    {{-- Notes Area --}}
                    <div class="col-12">
                        <dl class="row mb-0">
                            <dt class="col-sm-3">{{ __('note') }}</dt>
                            <dd class="col-sm-9">{{ $record->note ?? 'N/A' }}</dd>

                            <dt class="col-sm-3">{{ __('archivist_note') }}</dt>
                            <dd class="col-sm-9">{{ $record->archivist_note ?? 'N/A' }}</dd>
                        </dl>
                    </div>

                    <div class="col-12">
                        <h5 class="border-bottom pb-2">{{ __('description_control_area') }}</h5>
                    </div>

                    {{-- Description Control Area --}}
                    <div class="col-12">
                        <dl class="row mb-0">
                            <dt class="col-sm-3">{{ __('rule_convention') }}</dt>
                            <dd class="col-sm-9">{{ $record->rule_convention ?? 'N/A' }}</dd>

                            <dt class="col-sm-3">{{ __('created_at') }}</dt>
                            <dd class="col-sm-9">{{ $record->created_at->format('Y-m-d H:i:s') }}</dd>

                            <dt class="col-sm-3">{{ __('updated_at') }}</dt>
                            <dd class="col-sm-9">{{ $record->updated_at->format('Y-m-d H:i:s') }}</dd>

                            <dt class="col-sm-3">{{ __('organisation') }}</dt>
                            <dd class="col-sm-9">{{ $record->organisation->name ?? 'N/A' }}</dd>
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

        {{-- Archive Boxes Section --}}
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
                            <a href="{{ route('records.showFull', $child) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
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
                                             class="img-fluid h-100 w-100" style="object-fit: cover;" alt="{{ $attachment->name }}">
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
                    <form action="{{ route('records.destroy', $record) }}" method="POST" class="d-inline">
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



                        // AI features removed

            // Extract Keywords
            document.getElementById('btn-extract-keywords').addEventListener('click', async () => {
                try {
                    showResults('Keyword Extraction', '<div class="text-center">Extracting keywords...</div>');
                    showLoading('ai-results-content', 'Analyzing record content for keywords...');

                    const response = await fetch(`${apiBaseUrl}/extract-keywords/${recordId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    if (!response.ok) throw new Error(`API returned status: ${response.status}`);

                    const data = await response.json();

                    let resultsHTML = `
                        <h6>Extracted Keywords by Category:</h6>
                        <div class="row g-3">
                    `;

                    for (const [category, keywords] of Object.entries(data.categorizedKeywords)) {
                        resultsHTML += `
                            <div class="col-md-4">
                                <div class="card h-100 border-info">
                                    <div class="card-header bg-info bg-opacity-10 small fw-bold">${category}</div>
                                    <div class="card-body p-2">
                                        ${keywords.map(keyword =>
                                            `<span class="badge bg-light text-dark border me-1 mb-1">${keyword}</span>`
                                        ).join('')}
                                    </div>
                                </div>
                            </div>
                        `;
                    }

                    resultsHTML += `</div>`;
                    showResults('Keyword Extraction Results', resultsHTML);
                } catch (error) {
                    handleApiError(error);
                }
            });

            // Suggest Terms
            document.getElementById('btn-suggest-terms').addEventListener('click', async () => {
                try {
                    showResults('Term Suggestions', '<div class="text-center">Generating term suggestions...</div>');
                    showLoading('ai-results-content', 'Analyzing record content for term suggestions...');

                    const response = await fetch(`${apiBaseUrl}/assign-terms/${recordId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    if (!response.ok) throw new Error(`API returned status: ${response.status}`);

                    const data = await response.json();

                    let resultsHTML = `
                        <div class="mb-3">
                            <div class="alert alert-info">
                                The following terms are suggested based on the record content:
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            ${data.suggestedTerms.map(term => `
                                <div class="border rounded p-2 bg-light">
                                    <div class="fw-bold small">${term.name}</div>
                                    <div class="text-muted xsmall">${term.confidence}% confidence</div>
                                    <button class="btn btn-xs btn-outline-primary mt-1 btn-apply-term"
                                            data-term-id="${term.id}" data-term-name="${term.name}">
                                        <i class="bi bi-plus-circle"></i> Apply
                                    </button>
                                </div>
                            `).join('')}
                        </div>
                    `;

                    showResults('Term Suggestion Results', resultsHTML);

                    // Add event listeners for apply term buttons
                    document.querySelectorAll('.btn-apply-term').forEach(button => {
                        button.addEventListener('click', async (e) => {
                            const termId = e.target.closest('button').getAttribute('data-term-id');
                            const termName = e.target.closest('button').getAttribute('data-term-name');

                            try {
                                const response = await fetch(`/api/records/${recordId}/add-term/${termId}`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                    }
                                });

                                if (!response.ok) throw new Error(`API returned status: ${response.status}`);

                                // Update UI to reflect the applied term
                                e.target.closest('button').innerHTML = '<i class="bi bi-check"></i> Applied';
                                e.target.closest('button').classList.remove('btn-outline-primary');
                                e.target.closest('button').classList.add('btn-success');
                                e.target.closest('button').disabled = true;
                            } catch (error) {
                                console.error('Error applying term:', error);
                                alert('Failed to apply term. Please try again.');
                            }
                        });
                    });
                } catch (error) {
                    handleApiError(error);
                }
            });

            // Validate Records
            document.getElementById('btn-validate-records').addEventListener('click', async () => {
                try {
                    showResults('Record Validation', '<div class="text-center">Validating record...</div>');
                    showLoading('ai-results-content', 'Checking record compliance...');

                    const response = await fetch(`${apiBaseUrl}/validate/${recordId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    if (!response.ok) throw new Error(`API returned status: ${response.status}`);

                    const data = await response.json();

                    const statusBadge = data.isValid
                        ? '<span class="badge bg-success">Valid</span>'
                        : '<span class="badge bg-danger">Invalid</span>';

                    let resultsHTML = `
                        <div class="alert ${data.isValid ? 'alert-success' : 'alert-danger'}">
                            <h6>Validation Status: ${statusBadge}</h6>
                            <p>${data.message}</p>
                        </div>

                        <h6>Validation Details:</h6>
                        <ul class="list-group mb-3">
                            ${data.validationResults.map(result => `
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    ${result.field}
                                    ${result.valid ?
                                        '<span class="badge bg-success"><i class="bi bi-check"></i></span>' :
                                        '<span class="badge bg-danger"><i class="bi bi-x"></i></span>'}
                                </li>
                                ${!result.valid ? `<li class="list-group-item list-group-item-danger small">${result.message}</li>` : ''}
                            `).join('')}
                        </ul>
                    `;

                    showResults('Record Validation Results', resultsHTML);
                } catch (error) {
                    handleApiError(error);
                }
            });

            // Suggest Classification
            document.getElementById('btn-suggest-classification').addEventListener('click', async () => {
                try {
                    showResults('Classification Suggestion', '<div class="text-center">Generating classification suggestions...</div>');
                    showLoading('ai-results-content', 'Analyzing record for classification...');

                    const response = await fetch(`${apiBaseUrl}/classify/${recordId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    if (!response.ok) throw new Error(`API returned status: ${response.status}`);

                    const data = await response.json();

                    let resultsHTML = `
                        <div class="alert alert-info mb-3">
                            Based on the content analysis, the following classifications are suggested:
                        </div>

                        <div class="list-group mb-3">
                            ${data.classifications.map((classification, index) => `
                                <div class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong>${classification.name}</strong>
                                        <span class="badge bg-primary">${classification.confidence}%</span>
                                    </div>
                                    <p class="small text-muted mb-0">${classification.description}</p>
                                </div>
                            `).join('')}
                        </div>

                        <div>
                            <h6>Classification Rationale:</h6>
                            <p>${data.rationale}</p>
                        </div>
                    `;

                    showResults('Classification Suggestions', resultsHTML);
                } catch (error) {
                    handleApiError(error);
                }
            });

            // Generate Report
            document.getElementById('btn-generate-report').addEventListener('click', async () => {
                try {
                    showResults('Report Generation', '<div class="text-center">Generating archive report...</div>');
                    showLoading('ai-results-content', 'Creating comprehensive report...');

                    const response = await fetch(`${apiBaseUrl}/report/${recordId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    if (!response.ok) throw new Error(`API returned status: ${response.status}`);

                    const data = await response.json();

                    let resultsHTML = `
                        <div class="alert alert-success mb-3">
                            <i class="bi bi-file-earmark-check me-2"></i>
                            Report generated successfully!
                        </div>

                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <strong>Report Summary</strong>
                            </div>
                            <div class="card-body">
                                <p>${data.summary}</p>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <a href="${data.reportUrl}" class="btn btn-outline-primary" target="_blank">
                                <i class="bi bi-download me-2"></i> Download Full Report
                            </a>
                        </div>
                    `;

                    showResults('Generated Report', resultsHTML);
                } catch (error) {
                    handleApiError(error);
                }
            });
        });
    </script>
@endpush
