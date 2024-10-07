@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <nav aria-label="breadcrumb" class="mb-2">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('records.index') }}">Records</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $record->code }}</li>
            </ol>
        </nav>
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

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
            <div class="">
                <div class="card text-start">
                    <div class="card-body">
                        <h4 class="card-title">
                           <strong>{{ $record->name }} [{{ $record->level->name }}]</strong>
                        </h4>
                        <div class="container mt-1">
                            <div class="row">
                                <div class="col">
                                    <div class="d-flex flex-column">
                                        @if ($record->parent)
                                        <div class="p-2">
                                        Dans : <a href="{{ route('records.show', $record->parent) }}">
                                            [{{ $record->parent->level->name ??'' }}]  {{ $record->parent->name ??'' }} /
                                                @foreach ( $record->parent->authors as $author )
                                                    {{ $author->name ??'' }}
                                                @endforeach
                                        -
                                                @if($record->parent->date_start != NULL && $record->parent->date_end != NULL)
                                                    {{ $record->parent->date_start }} du {{ $record->parent->date_end  }}
                                                @elseif($record->parent->date_exact != NULL && $record->parent->date_end == NULL)
                                                    {{ $record->parent->date_start }}
                                                @elseif($record->parent->date_exact != NULL && $record->parent->date_start == NULL)
                                                    {{ $record->parent->date_exact }}
                                                @endif

                                            </a>
                                        </div>

                                        @endif
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

                <div class="d-grid gap-3">
                    @if ($record->level->has_child == true)
                        <a href="{{ route('record-child.create', $record->id) }}" class="btn btn-outline-success">
                            <i class="bi bi-plus-circle me-2"></i> Ajouter un fiche fille
                        </a>
                    @endif
                </div>

                <div class="card shadow-sm mb-4 mt-4">
                    <div class="card-body">
                        <h4 class="card-title mb-3">
                            <i class="bi bi-archive me-2"></i>Boites d'archives
                        </h4>

                        <div class="d-grid">
                            @foreach ($record->recordContainers as $recordContainer)
                                <div class="list-group">
                                    <div class="list-group-item list-group-item-action">
                                        {{ $recordContainer->container->code }} - {{ $recordContainer->description }}
                                        <form action="{{ route('record-container-remove')}}?r_id={{ $recordContainer->record_id }}&c_id={{ $recordContainer->container_id }}" method="post">
                                            @csrf
                                            <button class="btn btn-primary float-right" >Retirer de la boite</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>


                <div class="row justify-content-center mt-3 mb-3">
                    <div class="col-md-12">
                        <form action="{{ route('record-container-insert')}}?r_id={{ $record->id }}" method="post" class="row g-4 align-items-center">
                            @csrf
                            <div class="col-md-3">
                                <input type="text" name="code" class="form-control" placeholder="Code: B12453">
                            </div>
                            <div class="col">
                                <textarea name="description" class="form-control" placeholder="Description"></textarea>
                            </div>
                            <div class="col-auto">
                                <input type="submit" class="btn btn-primary" value="Insert">
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h4 class="mb-0">
                            <i class="bi bi-paperclip me-2"></i>Attachments
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row" id="attachmentsList">
                            @forelse($record->attachments as $attachment)
                                <div class="col-md-4 col-lg-3 mb-4">
                                    <div class="card h-100 shadow-sm">
                                        <div class="card-img-top" style="height: 200px; overflow: hidden;">
                                            @if($attachment->thumbnail_path)
                                                <img src="{{ asset('storage/' . $attachment->thumbnail_path) }}" alt="Thumbnail" class="img-fluid" style="width: 100%; object-fit: cover; object-position: top;">
                                            @else
                                                <div class="d-flex align-items-center justify-content-center h-100 bg-light">
                                                    <i class="bi bi-file-earmark-pdf fs-1 text-secondary"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="card-body p-3">
                                            <h6 class="card-title text-truncate mb-2" title="{{ $attachment->name }}">{{ $attachment->name }}</h6>
                                            <a href="{{ route('records.attachments.show', [$record, $attachment]) }}" class="btn btn-outline-primary btn-sm w-100">
                                                <i class="bi bi-download me-1"></i> Voir le fichier
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <p class="text-muted mb-0">No attachments found.</p>
                                </div>
                            @endforelse
                        </div>
                        <div class="d-grid mt-3">
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
