@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h1 class="h2 mb-0"><i class="bi bi-file-earmark-text me-2"></i>Fiche descriptive</h1>
            </div>
        </div>

        <div class="card mb-4 shadow">
            <div class="card-body">
                <h3 class="card-title h3 mb-3 text-primary"><i class="bi bi-bookmark-star me-2"></i>{{ $record->code }} : {{ $record->name }}</h3>
                <div class="row">
                    <div class="col-md-4">
                        <p class="card-text mb-2"><i class="bi bi-layers me-2"></i><strong>Niveau :</strong> {{ $record->level->name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-4">
                        <p class="card-text mb-2"><i class="bi bi-calendar-range me-2"></i><strong>Date :</strong> {{ $record->date_start ?? 'N/A' }} - {{ $record->date_end ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-4">
                        <p class="card-text mb-2"><i class="bi bi-rulers me-2"></i><strong>Epaisseur :</strong> {{ $record->width ? $record->width . ' cm' : 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="accordion shadow" id="recordDetailsAccordion">
                    @php
                        $sectionIcons = [
                            'Identification' => 'bi-fingerprint',
                            'Contexte' => 'bi-diagram-3',
                            'Contenu' => 'bi-journal-text',
                            'Condition d\'accès' => 'bi-lock',
                            'Sources complémentaires' => 'bi-link-45deg',
                            'Notes' => 'bi-pencil-square',
                            'Contrôle de description' => 'bi-list-check',
                            'Indexation' => 'bi-tags',
                        ];
                        // Your existing $sections array
                    @endphp
                    @php
                        $sections = [
                            'Identification' => [
                                'cote' => $record->code,
                                'Intitulé' => $record->name,
                                'Date Format' => $record->date_format ?? 'N/A',
                                'Date de début' => $record->date_start ?? 'N/A',
                                'Date fin' => $record->date_end ?? 'N/A',
                                'Date Exact' => $record->date_exact ?? 'N/A',
                                'Niveau de description' => $record->level->name ?? 'N/A',
                                'Epaisseur' => $record->width ? $record->width . ' cm' : 'N/A',
                                'Description importance matériel' => $record->width_description ?? 'N/A',
                            ],
                            'Contexte' => [
                                'Biographical History' => $record->biographical_history ?? 'N/A',
                                'Archival History' => $record->archival_history ?? 'N/A',
                                'Acquisition Source' => $record->acquisition_source ?? 'N/A',
                                'Authors' => $record->authors->isEmpty() ? 'N/A' : $record->authors->map(fn($author) => "<span class='badge bg-secondary'>{$author->name}</span>")->implode(' '),
                            ],
                            'Contenu' => [
                                'Content' => $record->content ?? 'N/A',
                                'Appraisal' => $record->appraisal ?? 'N/A',
                                'Accrual' => $record->accrual ?? 'N/A',
                                'Arrangement' => $record->arrangement ?? 'N/A',
                            ],
                            'Condition d\'accès' => [
                                'Access Conditions' => $record->access_conditions ?? 'N/A',
                                'Reproduction Conditions' => $record->reproduction_conditions ?? 'N/A',
                                'Language Material' => $record->language_material ?? 'N/A',
                                'Characteristic' => $record->characteristic ?? 'N/A',
                                'Finding Aids' => $record->finding_aids ?? 'N/A',
                            ],
                            'Sources complémentaires' => [
                                'Location Original' => $record->location_original ?? 'N/A',
                                'Location Copy' => $record->location_copy ?? 'N/A',
                                'Related Unit' => $record->related_unit ?? 'N/A',
                                'Publication Note' => $record->publication_note ?? 'N/A',
                            ],
                            'Notes' => [
                                'Note' => $record->note ?? 'N/A',
                                'Archivist Note' => $record->archivist_note ?? 'N/A',
                            ],
                            'Contrôle de description' => [
                                'Règle et convention' => $record->rule_convention ?? 'N/A',
                                'Status' => $record->status->name ?? 'N/A',
                                'Support' => $record->support->name ?? 'N/A',
                                'Classe' => $record->activity->name ?? 'N/A',
                            ],
                            'Indexation' => [
                                'Fiche parent' => $record->parent->name ?? 'N/A',
                                'Boite de conservation' => $record->container->name ?? 'N/A',
        //                        'Versement de provenance' => $record->accession->name ?? 'N/A',
                                'Créé par' => $record->user->name ?? 'N/A',
                                'Vedettes' => $record->terms->isEmpty() ? 'N/A' : $record->terms->map(fn($term) => "<span class='badge bg-secondary'>{$term->name}</span>")->implode(' '),
                            ],
                        ];
                    @endphp
                    @foreach($sections as $section => $details)
                        <div class="accordion-item">
                            <h3 class="accordion-header" id="{{ Str::slug($section) }}Heading">
                                <button class="accordion-button @if(!$loop->first) collapsed @endif" type="button" data-bs-toggle="collapse" data-bs-target="#{{ Str::slug($section) }}Collapse" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="{{ Str::slug($section) }}Collapse">
                                    <i class="bi {{ $sectionIcons[$section] ?? 'bi-info-circle' }} me-2"></i> {{ $section }}
                                </button>
                            </h3>
                            <div id="{{ Str::slug($section) }}Collapse" class="accordion-collapse collapse @if($loop->first) show @endif" aria-labelledby="{{ Str::slug($section) }}Heading" data-bs-parent="#recordDetailsAccordion">
                                <div class="accordion-body">
                                    <dl class="row mb-0">
                                        @foreach($details as $label => $value)
                                            <dt class="col-sm-4 mb-2"><i class="bi bi-arrow-right-short me-1"></i>{{ $label }}</dt>
                                            <dd class="col-sm-8 mb-2">{!! $value !!}</dd>
                                        @endforeach
                                    </dl>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-body">
                        <h4 class="card-title mb-3"><i class="bi bi-gear me-2"></i>Actions</h4>
                        <div class="d-grid gap-2">

                            <a href="{{ route('records.edit', $record) }}" class="btn btn-warning">
                                <i class="bi bi-pencil me-2"></i> Modifier
                            </a>
                            <a href="{{ route('records.index') }}" class="btn btn-primary">
                                <i class="bi bi-box me-2"></i> Insérer dans une boîte
                            </a>
                            <a href="{{ route('records.index') }}" class="btn btn-success">
                                <i class="bi bi-plus-circle me-2"></i> Ajouter une notice fille
                            </a>
                            <form action="{{ route('records.destroy', $record) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette fiche ?')">
                                    <i class="bi bi-trash me-2"></i> Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <a href="{{ route('records.index') }}" class="btn btn-outline-secondary mt-3 w-100">
                    <i class="bi bi-arrow-left me-2"></i> Retour à la liste
                </a>
                <ul>
                    @foreach($record->attachments as $attachment)
                        <li>{{ $attachment->file_path }} - {{ $attachment->description }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <style>
        .accordion-button:not(.collapsed) {
            background-color: #f8f9fa;
            color: #0178d4;
        }
        .accordion-button:focus {
            box-shadow: none;
        }
        .card-title {
            border-bottom: 2px solid #0178d4;
            padding-bottom: 0.5rem;
        }
        .btn i {
            font-size: 1.1em;
        }
        dt {
            font-weight: 600;
            color: #495057;
        }
        dd {
            color: #212529;
        }
    </style>
@endsection
