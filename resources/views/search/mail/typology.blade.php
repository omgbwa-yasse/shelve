@extends('layouts.app')
@section('content')
    <div class="container">
        <h1>Typologie de Courrier</h1>

        <ul class="list-group">
            @foreach ($typologies as $typology)
                <li class="list-group-item d-flex justify-content-between align-items-center mt-4">
                    <div>
                        <strong>{{ $typology->name }}</strong>
                        <small class="text-muted d-block">{{ $typology->code ?? 'NAN' }} - {{ $typology->class->name ?? 'NAN' }}</small>
                    </div>
                    <div>
                        <a href="{{ route('mails.sort', ['typology_id' => $typology->id, 'type' => 'received']) }}" class="btn btn-sm btn-primary">Courrier Reçu</a>
                        <a href="{{ route('mails.sort', ['typology_id' => $typology->id, 'type' => 'send']) }}" class="btn btn-sm btn-secondary">Courrier Émis</a>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
    <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-center">
            <li class="page-item {{ $typologies->onFirstPage() ? 'disabled' : '' }}">
                <a class="page-link" href="{{ $typologies->previousPageUrl() }}" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
            @foreach ($typologies->getUrlRange(1, $typologies->lastPage()) as $page => $url)
                <li class="page-item {{ $page == $typologies->currentPage() ? 'active' : '' }}">
                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                </li>
            @endforeach
            <li class="page-item {{ $typologies->hasMorePages() ? '' : 'disabled' }}">
                <a class="page-link" href="{{ $typologies->nextPageUrl() }}" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
@endsection

