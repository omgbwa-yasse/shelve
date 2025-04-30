@extends('layouts.app')
@section('content')
    <div class="container">
        <h1>Typologie de Courrier</h1>
        <a href="{{ route('mail-typology.create') }}" class="btn btn-primary mb-3">Ajouter une typology</a>
        <ul class="list-group">
            @foreach ($mailTypologies as $mailTypology)
                <li class="list-group-item d-flex justify-content-between align-items-center mt-4">
                    <div>
                        <h5 class="mb-1">{{ $mailTypology->name }}</h5>
                        <p class="mb-1 text-muted">{{ $mailTypology->description }}</p>
                        <small class="text-muted">{{ $mailTypology->code ?? 'NAN' }} - {{ $mailTypology->class->name ?? 'NAN' }}</small>
                    </div>
                    <a href="{{ route('mail-typology.edit', $mailTypology->id) }}" class="btn btn-sm btn-outline-secondary">Modifier</a>
                </li>
            @endforeach
        </ul>
    </div>
    <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-center">
            <li class="page-item {{ $mailTypologies->onFirstPage() ? 'disabled' : '' }}">
                <a class="page-link" href="{{ $mailTypologies->previousPageUrl() }}" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
            @foreach ($mailTypologies->getUrlRange(1, $mailTypologies->lastPage()) as $page => $url)
                <li class="page-item {{ $page == $mailTypologies->currentPage() ? 'active' : '' }}">
                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                </li>
            @endforeach
            <li class="page-item {{ $mailTypologies->hasMorePages() ? '' : 'disabled' }}">
                <a class="page-link" href="{{ $mailTypologies->nextPageUrl() }}" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
@endsection

