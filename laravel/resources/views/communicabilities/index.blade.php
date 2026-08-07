@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Communicabilities</h1>
        <a href="{{ route('communicabilities.create') }}" class="btn btn-primary mb-3">Create New Communicability</a>
        <table class="table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Duration (année) </th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($communicabilities as $communicability)
                    <tr>
                        <td>{{ $communicability->code }}</td>
                        <td>{{ $communicability->name }}</td>
                        <td>{{ $communicability->duration }}</td>
                        <td>
                            <a href="{{ route('communicabilities.show', $communicability->id) }}" class="btn btn-info">Paramètres</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Pagination -->
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item {{ $communicabilities->onFirstPage() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $communicabilities->previousPageUrl() }}" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                @foreach ($communicabilities->getUrlRange(1, $communicabilities->lastPage()) as $page => $url)
                    <li class="page-item {{ $page == $communicabilities->currentPage() ? 'active' : '' }}">
                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                    </li>
                @endforeach
                <li class="page-item {{ $communicabilities->hasMorePages() ? '' : 'disabled' }}">
                    <a class="page-link" href="{{ $communicabilities->nextPageUrl() }}" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
@endsection
