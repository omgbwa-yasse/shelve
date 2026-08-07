@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Chariot</h1>
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Type</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($dollies as $dolly)
            <tr>
                <td>{{ $dolly->name }}</td>
                <td>{{ $dolly->description }}</td>
                <td>
                    @switch($dolly->category ?? '')
                        @case('record')
                            Archives
                            @break
                        @case('mail')
                            Courrier
                            @break
                        @case('communication')
                            Communication des archives
                            @break
                        @case('room')
                            Salle d'archives
                            @break
                        @case('container')
                            Boites d'archives et chronos
                            @break
                        @case('shelf')
                            Etagère
                            @break
                        @case('slip_record')
                            Archives (versement)
                            @break
                    @endswitch

                </td>
                <td>
                    <a href="{{ route('dolly.show', $dolly) }}" class="btn btn-info">Afficher</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Pagination -->
    <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-center">
            <li class="page-item {{ $dollies->onFirstPage() ? 'disabled' : '' }}">
                <a class="page-link" href="{{ $dollies->previousPageUrl() }}" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
            @foreach ($dollies->getUrlRange(1, $dollies->lastPage()) as $page => $url)
                <li class="page-item {{ $page == $dollies->currentPage() ? 'active' : '' }}">
                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                </li>
            @endforeach
            <li class="page-item {{ $dollies->hasMorePages() ? '' : 'disabled' }}">
                <a class="page-link" href="{{ $dollies->nextPageUrl() }}" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
</div>
@endsection
