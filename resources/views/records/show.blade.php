@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <nav aria-label="breadcrumb" class="mb-2">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('records.index') }}">Records</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $record->code }}</li>
            </ol>
        </nav>

        <div class="row mb-1">
            <div class="col-md-8">
                <h1 class="h2 mb-0 d-flex align-items-center">
                    Fiche descriptive
                </h1>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="btn-group" role="group">
                    <a href="{{ route('records.edit', $record) }}" class="btn btn-outline-primary">
                        <i class="bi bi-pencil"></i> Modifier la fiche
                    </a>
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                        <i class="bi bi-trash"></i> Supprimer la fiche
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card text-start">
                    <div class="card-body">
                        <h4 class="card-title">
                           <strong>{{ $record->name }}</strong>
                        </h4>
                        <div class="container mt-1">
                            <div class="row">
                                <div class="col">
                                    <div class="d-flex flex-column">
                                        <div class="p-2">
                                            Cote : {{ $record->code }}
                                        </div>
                                        <div class="p-2">
                                            Intitulé / analyse : {{ $record->name }}
                                        </div>
                                        <div class="p-2"> Dates :
                                            @if($record->date_exact == NULL)
                                                @if($record->date_end == NULL)
                                                    {{ $record->date_start ?? 'N/A' }}
                                                @else
                                                    {{ $record->date_start ?? 'N/A' }} à {{ $record->date_end ?? 'N/A' }}
                                                @endif
                                            @else
                                                {{ $record->date_exact ?? 'N/A' }}
                                            @endif
                                        </div>
                                        <div class="p-2">
                                            Producteurs :
                                            <a href="{{ route('records.sort')}}?categ=authors&id={{ $record->authors->pluck('id')->join('')}}">
                                                 {{ $record->authors->isEmpty() ? 'N/A' : $record->authors->map(fn($author) => "{$author->name}")->implode(' ') }}
                                            </a>
                                        </div>
                                        <div class="p-2 mt-0">
                                            Content : {{ $record->content ?? 'N/A' }}
                                        </div>

                                        <div class="d-flex mt-0">
                                            <div class="p-2">
                                                Support : {{ $record->support->name ?? 'N/A' }}
                                            </div>
                                            <div class="p-2">
                                                Status : {{ $record->status->name ?? 'N/A' }}
                                            </div>
                                            <div class="p-2">
                                                Container : <a href="{{ route('records.sort')}}?categ=container&id={{ $record->container->id ?? 'none' }}">{{ $record->container->name ?? 'Non conditionné' }}</a>
                                            </div>
                                            <div class="p-2">
                                                Created By : {{ $record->user->name ?? 'N/A' }}
                                            </div>
                                        </div>
                                        <div class="d-flex">
                                            <div class="p-2">
                                                Width : {{ $record->width ? $record->width . ' cm' : 'N/A' }}
                                            </div>
                                            <div class="p-2">
                                                Width Description :{{ $record->width_description ?? 'N/A' }}
                                            </div>
                                        </div>
                                        <div class="p-2">
                                            Terms :
                                            @foreach($record->terms as $index => $term)
                                                <a href="{{ route('records.sort')}}?categ=term&id={{ $term->id ?? 'N/A' }}"> {{ $term->name ?? 'N/A' }} </a>
                                                @if(!$loop->last)
                                                    {{ " ; " }}
                                                @endif
                                            @endforeach
                                        </div>
                                        <div class="p-2">
                                            Activity : <a href="{{ route('records.sort')}}?categ=activity&id={{ $record->activity->id ?? 'N/A' }}">{{ $record->activity->name ?? 'N/A' }}</a>
                                        </div>
                                        <div class="p-2">
                                            Note : {{ $record->note ?? 'N/A' }}
                                        </div>
                                        <div class="p-2">
                                            Archivist Note : {{ $record->archivist_note ?? 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
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
                        <i class="bi bi-pencil me-2"></i> Modifier
                    </a>
                    <a href="{{ route('records.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-box me-2"></i> Inserer dans une boîte
                    </a>
                    <a href="{{ route('record-child.create', $record->id) }}" class="btn btn-outline-success">
                        <i class="bi bi-plus-circle me-2"></i> Ajouter un fiche fille
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
                                    <a href="{{ route('records.attachments.show', [$record, $attachment]) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-download"></i> Voir le fichier
                                    </a>
                                </li>
                            @empty
                                <li class="list-group-item text-muted">No attachments found.</li>
                            @endforelse
                        </ul>
                        <div class="d-grid">
                            <a href="{{ route('records.attachments.create', $record) }}" class="btn btn-outline-success">
                                <i class="bi bi-plus-circle me-2"></i> Ajouter un fichier
                            </a>
                        </div>
                    </div>
                </div>

                <a href="{{ route('records.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-arrow-left me-2"></i> Retour à l'accueil
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
