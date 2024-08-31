@extends('layouts.app')

<style>
    .card-header .btn-link {
        text-decoration: none;
        color: inherit;
    }
    .card-header .btn-link:hover {
        text-decoration: none;
    }
    .badge-pill {
        font-size: 0.9em;
    }
    .accordion .card {
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        margin-bottom: 1rem;
    }
    .accordion .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
    .accordion .card-body {
        padding: 1rem;
    }
    .accordion .btn-link {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        text-align: left;
    }
    .accordion .btn-link .icon {
        margin-right: 0.5rem;
    }
    .accordion .btn-link .text {
        flex-grow: 1;
    }
    .accordion .btn-link .badge {
        margin-left: auto;
    }
</style>

<script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Typologie de Courrier</h1>
            <a href="{{ route('mail-typology.create') }}" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Créer une nouvelle typologie
            </a>
        </div>

        <div class="accordion" id="typologieAccordion">
            @foreach ($mailTypologies as $mailTypology)
                <div class="card">
                    <div class="card-header" id="heading{{ $mailTypology->id }}">
                        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse{{ $mailTypology->id }}" aria-expanded="true" aria-controls="collapse{{ $mailTypology->id }}">
                            <div class="icon">
                                <i class="bi bi-envelope-fill text-primary"></i>
                            </div>
                            <div class="text">
                                <strong>{{ $mailTypology->name }}</strong>
                                <small class="text-muted d-block">{{ $mailTypology->class->name ?? 'NAN' }}</small>
                            </div>
                            <span class="badge badge-primary badge-pill">
                                {{ $mailTypology->mails->count() ?? 'NAN' }}
                            </span>
                        </button>
                    </div>

                    <div id="collapse{{ $mailTypology->id }}" class="collapse" aria-labelledby="heading{{ $mailTypology->id }}" data-parent="#typologieAccordion">
                        <div class="card-body">
                            <p>{{ $mailTypology->description }}</p>
                            <div class="mt-3">
                                <a href="{{ route('mail-typology.show', $mailTypology->id) }}" class="btn btn-info btn-sm">
                                    <i class="bi bi-eye"></i> Voir détails
                                </a>
                                <a href="{{ route('mails.sort', ['categ' => 'typology', 'id' => $mailTypology->id]) }}" class="btn btn-secondary btn-sm">
                                    <i class="bi bi-envelope"></i> Voir les mails
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection

@push('styles')

@endpush

@push('scripts')
@endpush
