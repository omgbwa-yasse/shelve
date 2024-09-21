@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('records.index') }}">Records</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $record->code }}</li>
            </ol>
        </nav>

        <div class="row mb-4">
            <div class="col-md-8">
                <h1 class="h2 mb-0 d-flex align-items-center">
                    <i class="bi bi-file-earmark-text me-2 text-primary"></i>
                    {{ $record->code }}: {{ $record->name }}
                </h1>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="btn-group" role="group">
                    <a href="{{ route('records.edit', $record) }}" class="btn btn-outline-primary">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h3 class="card-title h4 mb-3">Quick Information</h3>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-layers fs-4 text-muted me-2"></i>
                                    <div>
                                        <small class="text-muted d-block">Level</small>
                                        <strong>{{ $record->level->name ?? 'N/A' }}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-calendar-range fs-4 text-muted me-2"></i>
                                    <div>
                                        <small class="text-muted d-block">Date Range</small>
                                        <strong>{{ $record->date_start ?? 'N/A' }} - {{ $record->date_end ?? 'N/A' }}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-rulers fs-4 text-muted me-2"></i>
                                    <div>
                                        <small class="text-muted d-block">Width</small>
                                        <strong>{{ $record->width ? $record->width . ' cm' : 'N/A' }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="accordion shadow-sm" id="recordDetailsAccordion">
                    @php
                        $sectionIcons = [
                            'Identification' => 'bi-fingerprint',
                            'Context' => 'bi-diagram-3',
                            'Content' => 'bi-journal-text',
                            'Access Conditions' => 'bi-lock',
                            'Related Sources' => 'bi-link-45deg',
                            'Notes' => 'bi-pencil-square',
                            'Description Control' => 'bi-list-check',
                            'Indexing' => 'bi-tags',
                        ];

                        $sections = [
                            'Identification' => [
                                'Code' => $record->code,
                                'Title' => $record->name,
                                'Date Format' => $record->date_format ?? 'N/A',
                                'Start Date' => $record->date_start ?? 'N/A',
                                'End Date' => $record->date_end ?? 'N/A',
                                'Exact Date' => $record->date_exact ?? 'N/A',
                                'Description Level' => $record->level->name ?? 'N/A',
                                'Width' => $record->width ? $record->width . ' cm' : 'N/A',
                                'Material Importance Description' => $record->width_description ?? 'N/A',
                            ],
                            'Context' => [
                                'Biographical History' => $record->biographical_history ?? 'N/A',
                                'Archival History' => $record->archival_history ?? 'N/A',
                                'Acquisition Source' => $record->acquisition_source ?? 'N/A',
                                'Authors' => $record->authors->isEmpty() ? 'N/A' : $record->authors->map(fn($author) => "<span class='badge bg-secondary'>{$author->name}</span>")->implode(' '),
                            ],
                            'Content' => [
                                'Content' => $record->content ?? 'N/A',
                                'Appraisal' => $record->appraisal ?? 'N/A',
                                'Accrual' => $record->accrual ?? 'N/A',
                                'Arrangement' => $record->arrangement ?? 'N/A',
                            ],
                            'Access Conditions' => [
                                'Access Conditions' => $record->access_conditions ?? 'N/A',
                                'Reproduction Conditions' => $record->reproduction_conditions ?? 'N/A',
                                'Language Material' => $record->language_material ?? 'N/A',
                                'Characteristic' => $record->characteristic ?? 'N/A',
                                'Finding Aids' => $record->finding_aids ?? 'N/A',
                            ],
                            'Related Sources' => [
                                'Original Location' => $record->location_original ?? 'N/A',
                                'Copy Location' => $record->location_copy ?? 'N/A',
                                'Related Unit' => $record->related_unit ?? 'N/A',
                                'Publication Note' => $record->publication_note ?? 'N/A',
                            ],
                            'Notes' => [
                                'Note' => $record->note ?? 'N/A',
                                'Archivist Note' => $record->archivist_note ?? 'N/A',
                            ],
                            'Description Control' => [
                                'Rules and Conventions' => $record->rule_convention ?? 'N/A',
                                'Status' => $record->status->name ?? 'N/A',
                                'Support' => $record->support->name ?? 'N/A',
                                'Class' => $record->activity->name ?? 'N/A',
                            ],
                            'Indexing' => [
                                'Parent Record' => $record->parent->name ?? 'N/A',
                                'Conservation Box' => $record->container->name ?? 'N/A',
                                'Created By' => $record->user->name ?? 'N/A',
                                'Terms' => $record->terms->isEmpty() ? 'N/A' : $record->terms->map(fn($term) => "<span class='badge bg-secondary'>{$term->name}</span>")->implode(' '),
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
                                            <dt class="col-sm-4 mb-2">{{ $label }}</dt>
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
                <div class="card shadow-sm mt-4">
                    <div class="card-body">
                        <h4 class="card-title mb-3">
                            <i class="bi bi-diagram-3 me-2"></i>Child Records
                        </h4>
                        @if($record->children->isNotEmpty())
                            <div class="list-group">
                                @foreach($record->children as $child)
                                    <a href="{{ route('records.show', $child) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        {{ $child->code }}: {{ $child->name }}
                                        <span class="badge bg-primary rounded-pill">{{ $child->level->name ?? 'N/A' }}</span>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">No child records found.</p>
                        @endif
                    </div>
                </div>
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h4 class="card-title mb-3"><i class="bi bi-gear me-2"></i>Actions</h4>
                        <div class="d-grid gap-2">
                            <a href="{{ route('records.edit', $record) }}" class="btn btn-outline-primary">
                                <i class="bi bi-pencil me-2"></i> Modify
                            </a>
                            <a href="{{ route('records.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-box me-2"></i> Insert into a box
                            </a>
                            <a href="{{ route('record-child.create', $record) }}" class="btn btn-outline-success">
                                <i class="bi bi-plus-circle me-2"></i> Add a child record
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h4 class="card-title mb-3">
                            <i class="bi bi-paperclip me-2"></i>Attachments
                        </h4>
                        <ul class="list-group list-group-flush mb-3" id="attachmentsList">
                            @forelse($record->attachments as $attachment)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>{{ $attachment->name }}</span>
                                    <a href="{{ route('records.attachments.show', [$record, $attachment]) }}" class="btn btn-sm btn-outline-primary" >
                                        <i class="bi bi-download"></i>
                                    </a>

                                </li>
                            @empty
                                <li class="list-group-item text-muted">No attachments found.</li>
                            @endforelse
                        </ul>
                        <div class="d-grid">
                            <a href="{{ route('records.attachments.create', $record) }}" class="btn btn-outline-success">
                                <i class="bi bi-plus-circle me-2"></i>Add New Attachment
                            </a>
                        </div>
                    </div>
                </div>

                <a href="{{ route('records.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-arrow-left me-2"></i> Back to list
                </a>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this record?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="{{ route('records.destroy', $record) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        .accordion-button:not(.collapsed) {
            background-color: #f8f9fa;
            color: #0d6efd;
        }
        .accordion-button:focus {
            box-shadow: none;
            border-color: rgba(0,0,0,.125);
        }
        .card-title {
            border-bottom: 2px solid #0d6efd;
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
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });

            // Add animation to the attachments list
            const attachmentsList = document.getElementById('attachmentsList');
            if (attachmentsList) {
                attachmentsList.addEventListener('click', function(e) {
                    if (e.target.tagName === 'A') {
                        e.target.classList.add('btn-primary');
                        e.target.classList.remove('btn-outline-primary');
                        setTimeout(() => {
                            e.target.classList.remove('btn-primary');
                            e.target.classList.add('btn-outline-primary');
                        }, 300);
                    }
                });
            }

            // Smooth scrolling for accordion
            document.querySelectorAll('.accordion-button').forEach(button => {
                button.addEventListener('click', function() {
                    setTimeout(() => {
                        this.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 350);
                });
            });
        });
    </script>
@endpush
