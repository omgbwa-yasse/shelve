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

                <div class="card text-start">
                    <div class="card-body">

                        <h4 class="card-title">
                            {{ $record->code ?? '' }} - {{ $record->name ?? '' }}
                            ({{ $record->level->name ?? '' }}) de {{ $record->parent->name ?? '' }}
                        </h4>
                        <p class="card-text">
                            {{ $record->authors->isEmpty() ? 'N/A' : $record->authors->map(fn($author) => "{$author->name}")->implode(' ') }}
                        </p>

                        <p class="card-text">
                            {{ $record->date_format ?? '' }} - {{ $record->date_start ?? '' }} - {{ $record->date_end ?? '' }} -  {{ $record->date_exact ?? '' }} <br>
                            {{ $record->width ? $record->width . ' cm' : 'N/A' }}
                        </p>
                        <p class="card-text">
                            {{ $record->width_description ?? 'N/A' }}
                            {{ $record->status->name ?? '' }}
                            {{ $record->content ?? 'N/A' }}
                            {{ $record->archivist_note ?? 'N/A' }}
                        </p>
                        <p class="card-text">
                            {{ $record->support->name ?? '' }}
                            {{ $record->container->name ?? '' }}

                            {{ $record->user->name ?? '' }}
                            {{ $record->terms->isEmpty() ? 'N/A' : $record->terms->map(fn($term) => "{$term->name}")->implode(' ') ?? '' }}
                            {{ $record->activity->name ?? '' }}
                            {{ $record->note ?? '' }}
                        </p>
                    </div>
                </div>

                    <div>
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
